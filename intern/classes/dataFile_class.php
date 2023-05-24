<?php

class dataFile {
	
	private $spreadsheet;
	private $inData = [];
	private $header = [];
	private $rowCount = 0;
	private $rowPointer = 0;
	private $rememberMe = [];
	private $pg_pdo;
	
	
	public function __construct($filename) {
		include_once('./vendor/autoload.php');

		include './intern/config.php';
		$this->pg_pdo = new PDO($wwsserver, $wwsuser, $wwspass, null);
		
		
		$this->spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filename);
		$worksheet = $this->spreadsheet->getActiveSheet();
		
		foreach ($worksheet->getRowIterator() as $row) {
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(FALSE); 
			$ccount = 0; $withData = 0;
			foreach ($cellIterator as $cell) {
				if ($this->rowCount == 0) {
					$this->header[$ccount++] = $cell->getValue();
					$withData++;
				} else {
				    $cellValue = $cell->getFormattedValue();
					$this->inData[$this->rowCount][$this->header[$ccount++]] = $cellValue;
					if (!empty($cellValue) ) { $withData++; }
				}
			}
			if ($withData > 0) {
				$this->rowCount++;
			}
		}
		if ($withData > 0) {
			//array_pop($this->inData);
			
		}

	}
	
	public function rowCount() {
		return $this->rowCount;
	}
	
	public function sortData($groupColumn, $sortColumn, $dir = SORT_ASC) {
		
		$sortWith1  = array_column($this->inData, $groupColumn);
		$sortWith2  = array_column($this->inData, $sortColumn);
		array_multisort($sortWith1, $dir, $sortWith2, $dir, $this->inData);
		
	}
	
	public function getNextRow() {
		if (DEBUG) { print "row: ".$this->rowPointer; }
		if ($this->rowPointer < $this->rowCount) {
			return $this->inData[$this->rowPointer++];
		} else {
			return false;
		}
	} 
	
	
	public function resetPointer() {
		$this->rowPointer = 0;	
	}
	
	public function generateFac($dataSet, $row ) {
		$filledData = [];
		foreach ($dataSet as $key => $value) {
		    $value = rtrim($value);
		    if(substr($value,0,1) == '!') {
			    $filledData[$key] = $this->sqlValue(substr($value,1), $row);
			    $this->rememberMe[$key] = $filledData[$key];
			} elseif(substr($value,0,1) == '?') {
			    $filledData[$key] = $this->rememberMe[substr($value,1)];
			} elseif(substr($value,0,1) == '&') {
			    if (substr($value,1) == "STARTCOUNT") {
			        $this->rememberMe["COUNT"] = 0;
			    } elseif (substr($value,1) == "COUNT") {
			        $filledData[$key] = $this->rememberMe["COUNT"]++;
			    } elseif (substr($value,1,5) == "CALC(") {
			    	$calcStr = substr($value,6,-1);
			    	$calcStr = $this->replaceVars($calcStr, $row);
			    	$calcStr = $this->replaceRemembers($calcStr);
			    	$calcStr = "select ".preg_replace('/[^0-9a-z \.\+\-\*\/\(\)]/','',$calcStr);
			    	if (DEBUG) { print ("Berechne ".$calcStr."\n"); }
			    	try {
			    		$qry = $this->pg_pdo->prepare($calcStr);
			    		$qry->execute();
			    		$result = $qry->fetch( PDO::FETCH_NUM );
			    		if (DEBUG) {  print_r($qry->errorInfo()); print_r($result); }
			    	} catch (Exception $e) {
			    		if (DEBUG) { 
			    			print ("ERROR ".$e."\n"); 
			    		}
			    		
			    		$result[0] = '';
			    	}
			    	$filledData[$key] = $result[0];
			    }
			    
			} else {
				$value = $this->replaceVars($value, $row);
				$filledData[$key] = $value;
			}
		}
		return $filledData;
	}
	
	private function sqlValue($sql, $row) {
		$varNames = $this->getVarName($sql);
		$varCount = 0;
		if (!empty($varNames)) {
			foreach ($varNames as $variable) {
				$sql = str_replace('${'.$variable."}", ':savevar'.$varCount++, $sql);
			}
		}
		
		$qry = $this->pg_pdo->prepare($sql);
		
		if (!empty($varNames)) {
			$varCount = 0;
			foreach ($varNames as $variable) {
				$qry->bindValue(':savevar'.$varCount++, $row[$variable]);
			}
		}
		
		$qry->execute() or die (print_r($qry->errorInfo()));
		
		$result = $qry->fetch( PDO::FETCH_NUM );
		
		return $result[0];
	}

	private function getVarName($string) {
		$varNames = [];
		preg_match_all('/\${([^}]*)}/', $string,  $varNames);
		if ( !empty($varNames[1]) ) {
			return $varNames[1];
		} else {
			return null;
		}
		
	}
	
	private function replaceVars($string, $row) {
		if ($varNames = $this->getVarName($string)) {
			foreach ($varNames as $variable) {
				if (DEBUG) {
					print (' ersetze ${'.$variable.'} mit '.$row[$variable].' in '.$string."\n" );
				}
				$string = str_replace('${'.$variable."}", $row[$variable], $string);
			}
		}
		return $string;
	}
	
	private function replaceRemembers($string) {
		$rememberNames = [];
		preg_match_all('/\?([a-zA-Z0-9]*) /', $string,  $rememberNames);
		if ( !empty($rememberNames[1]) ) {
			foreach ($rememberNames[1] as $variable) {
				if(!empty($this->rememberMe[$variable])) {
					if (DEBUG) {
						print (' ersetze =?'.$variable.' mit '.$this->rememberMe[$variable].' in '.$string."\n" );
					}
					$string = str_replace('?'.$variable, $this->rememberMe[$variable], $string);
				}
			}
		}
		return $string;
	}
	
}

?>
