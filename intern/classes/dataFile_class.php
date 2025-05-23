<?php

class dataFile {
	
	private $spreadsheet;
	private $inData = [];
	private $header = [];
	private $rowCount = -1; // -1 = headline 0 = erste datenreihe
	private $rowPointer = 0;
	private $rememberMe = [];
	private $pg_pdo;
	private $bigData = false;
	
	public function __construct($filename, $headlineNumber = 0, $dbURL = null) {
		include_once('./vendor/autoload.php');

		include './intern/config.php';
		if (!empty($dbURL)) {
			$wwsserver = $dbURL;
		}
		$this->pg_pdo = new PDO($wwsserver, $wwsuser, $wwspass, null);

		if (substr($filename,-4) == '.csv') {
			if (DEBUG) { print ("CSV!"); }
			$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');
			$encoding = \PhpOffice\PhpSpreadsheet\Reader\Csv::guessEncoding($filename);
			if (DEBUG) { print ("Encoding: ".$encoding); }
			$reader->setInputEncoding($encoding);
			$reader->setDelimiter(';');
			$reader->setEnclosure('"');
			$reader->setSheetIndex(0);
			$this->spreadsheet = $reader->load($filename);
		} else {
			$this->spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filename);
		}
		$worksheet = $this->spreadsheet->getActiveSheet();
		
		
		
		if ($this->spreadsheet->getActiveSheet()->getHighestDataRow() > 5000) {
			if (DEBUG) { print ("Bigdata File:".$this->spreadsheet->getActiveSheet()->getHighestDataRow()."\n"); }
			$this->rowCount = $this->spreadsheet->getActiveSheet()->getHighestDataRow();
			$rowIterator = $worksheet->getRowIterator();
			$rowIterator->seek($headlineNumber+1); //beginnt mit 0 zu zählen
			$firstRow = $rowIterator->current();
			$cellIterator = $firstRow->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(FALSE);
			$ccount = 0; $withData = 0;
			foreach ($cellIterator as $cell) {
					$this->header[$ccount++] = $cell->getValue();
			}
			$this->rowPointer = $headlineNumber+2;
			$this->bigData = true;
		} else {
			if (DEBUG) { print ("MAX Row:".$this->spreadsheet->getActiveSheet()->getHighestDataRow()."\n"); }
			$startsearch = 0;
			foreach ($worksheet->getRowIterator() as $row) {
				if ($startsearch++ < $headlineNumber) {
					continue;
				}
				if ($row->isEmpty()) { // Ignore empty rows
					continue;
				}
				if (DEBUG) { print ";".$this->rowCount; }
				$cellIterator = $row->getCellIterator();
				$cellIterator->setIterateOnlyExistingCells(FALSE); 
				$ccount = 0; $withData = 0;
				foreach ($cellIterator as $cell) {
					if ($this->rowCount == -1) { // -1 = headline 0 = erste datenreihe
						if (DEBUG) { print ":"; }
						$this->header[$ccount++] = $cell->getValue();
						$withData++;
					} else {
						if (DEBUG) { print "."; }
						//$cell = $worksheet->getCell($cell);
						if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
							$value = $cell->getValue();
							
							if ($value == floor($value)) {
								//$cellValue = date("d.m.Y",PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($value));
								$cellValue = PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format("d.m.Y");
							} else {
								//$cellValue = date("d.m.Y H:i",PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($value));
								$cellValue = PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format("d.m.Y H:i");
							}
							
						} else {
						    $cellValue = $cell->getFormattedValue();
						}
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
		if (DEBUG) {
			print "HEADER\n";
			print_r($this->header);
		}
	}
	
	public function rowCount() {
		return $this->rowCount;
	}
	
	public function sortData($groupColumn, $sortColumn, $dir = SORT_ASC) {
		if (($this->bigData) or ($groupColumn == "NOSORT") or ($sortColumn == "NOSORT")) { 
			if (DEBUG) { print "BigData or NOSORT Profile"; }
			return false; 
		}
		// all values from colum $groupcolumn
		$sortWith1  = array_column($this->inData, $groupColumn);
		// all values from colum $sortcolumn
		$sortWith2  = array_column($this->inData, $sortColumn);
		//sort $groupcolum and $than $sortcolumn
		if ((count($sortWith1) == 0) or (count($sortWith2) == 0)) {
			if (DEBUG) { print "at least one Sort colum is empty!"; }
			return false;
		}
		if (DEBUG) { print_r($sortWith1); print "<br>"; print_r($sortWith1);}
		array_multisort($sortWith1, $dir, $sortWith2, $dir, $this->inData);
		return true;
	}
	
	public function getNextRow() {
		$localInData = [];
		if (($this->bigData) and ($this->rowPointer < $this->rowCount)) {
			$worksheet = $this->spreadsheet->getActiveSheet();
			$rowIterator = $worksheet->getRowIterator();
			$rowIterator->seek($this->rowPointer++); //beginnt mit 0 zu zählen
			$firstRow = $rowIterator->current();
			$cellIterator = $firstRow->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(FALSE);
			$ccount = 0;
			foreach ($cellIterator as $cell) {
				//$cell = $worksheet->getCell($cell);
				if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
					$value = $cell->getValue();
					if (is_integer($value)) {
						$cellValue = date("d.m.Y",PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($value));
					} else {
						$cellValue = date("d.m.Y H:i:s",PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($value));
					}
				} else {
					$cellValue = $cell->getFormattedValue();
				}
				$localInData[$this->header[$ccount++]] = $cellValue;
			}
			return $localInData;
		} elseif ($this->rowPointer < $this->rowCount) {
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
			    	(substr($value,6,1) == "!") ? $calcStr = substr($value,7,-1) : $calcStr = substr($value,6,-1);
			    	$calcStr = $this->replaceVars($calcStr, $row);
			    	$calcStr = $this->replaceRemembers($calcStr);
			    	$calcStr = "select ".preg_replace('/[^0-9a-z \.\+\-\*\/\(\)]/','',$calcStr);
			    	if (DEBUG) { print ("Berechne ".$calcStr."\n"); }
			    	try {
			    		$qry = $this->pg_pdo->prepare($calcStr);
			    		$qry->execute() or $this->Proto($qry->errorInfo());
			    		$result = $qry->fetch( PDO::FETCH_NUM );
			    		if (DEBUG) {  print_r($qry->errorInfo()); print_r($result); }
			    	} catch (Exception $e) {
			    		if (DEBUG) { 
			    			print ("ERROR ".$e->getMessage()."\n"); 
			    		} else {
			    			$this->Proto($e->getMessage());
			    		}
			    		
			    		$result[0] = '';
			    	}
			    	if (substr($value,6,1) == "!") {
			    		$this->rememberMe["CALC".$key] = $result[0];
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
		if (DEBUG) { print "  SQLValue: ".$sql."\n"; }
		$qry = $this->pg_pdo->prepare($sql);
		
		if (!empty($varNames)) {
			$varCount = 0;
			foreach ($varNames as $variable) {
				if (DEBUG) { print '    :savevar'.$varCount." -> ".$row[$variable]."\n"; }
				$qry->bindValue(':savevar'.$varCount++, $row[$variable]);
			}
		}
		$qry->execute() or die (print_r($qry->errorInfo()));
		
		$result = $qry->fetch( PDO::FETCH_NUM );
		
		if (DEBUG) { print "Result: ".var_dump($result); }
		
		if (is_array($result)) {
			return $result[0];
		} else {
			if (DEBUG) { print "SQl Error: ".$sql."\n"; }
			return null;
		}
	}

	private function getVarName($string) {
		$varNames = [];
		preg_match_all('/\${([^}]*)}/', $string,  $varNames);
		if ( !empty($varNames[1]) ) {
			return array_unique($varNames[1]);
		} else {
			return null;
		}
		
	}
	
	private function replaceVars($string, $row) {
		if ($varNames = $this->getVarName($string)) {
			foreach ($varNames as $variable) {
				$splitvar = null;
				preg_match_all('/(^[^|]*)|([0-9]*)$/', $variable,  $splitvar);
				if (is_numeric($splitvar[0][1])){
					$wert = substr($row[$splitvar[0][0]],0,$splitvar[0][01]);
					if (DEBUG) { print "SPLITVAR: ".$splitvar[0][0]." (".$splitvar[0][1].") = ".$wert."\n"; }
				} else {
					$wert = $row[$variable];
				}
				if (DEBUG) {
					print (' ersetze ${'.$variable.'} mit '.$wert.' in '.$string."\n" );
				}
				$string = str_replace('${'.$variable."}", $wert, $string);
			}
		}
		return $string;
	}
	
	private function replaceRemembers($string) {
		$rememberNames = [];
		preg_match_all('/\?([a-zA-Z§0-9]*) /', $string,  $rememberNames);
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
	
	private function Proto($logdata) {
		
		include './intern/autoload.php';
		include ("./intern/config.php");
		
		$log = new myfile("log/Protokoll".date("Y-m").".log","append");
		$log->writeLn(date("Y.m.d H:i")."\t".$_SESSION['user']."\t".$logdata);
		$log->close();
		
	}
}


?>
