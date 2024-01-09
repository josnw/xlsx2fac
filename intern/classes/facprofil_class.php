<?php

class facProfil {
	
	private $loop = [];
	private $agg = [];
	private $facArray = [];
	private $loopCount = 0;
	private $aggCount = 0;
	private $headline = 0;
	private $rule = null;
	private $dbURL = null;
	
	public function __construct($filename) {
		if (DEBUG) { print "Profile:".$filename."\n"; }
		$pfile = fopen($filename, "r");
		while ( $line = fgets($pfile, 4096)) {
			if (substr($line,0,5) == 'LOOP:') {
				//LOOP:${loop profil filename}[TAB]${row name for group}[TAB]${row name for sort}
				$this->loop[++$this->loopCount] = explode("\t",substr($line,5));
				$this->loop[$this->loopCount]['profilFile'] = $this->loop[$this->loopCount][0];
				$this->loop[$this->loopCount]['groupRow'] = $this->loop[$this->loopCount][1];
				$this->loop[$this->loopCount]['sortRow'] = $this->loop[$this->loopCount][2];
			} elseif ((substr($line,0,4) == 'AGG:') or (substr($line,0,4) == 'SUB:') ){
				//AGG:${aggretation profil filename}[TAB]${row name for group}
				$this->agg[++$this->aggCount] = explode("\t",substr($line,4));
				$this->agg[$this->aggCount]['profilFile'] = $this->agg[$this->aggCount][0];
				$this->agg[$this->aggCount]['aggTyp'] = substr($line,0,4);
				if (strpos($this->agg[$this->aggCount][1],":") !== false) {
					$tmpa = explode(":",$this->agg[$this->aggCount][1]);
					$this->agg[$this->aggCount]['groupRow'] = $tmpa[0];
					$this->agg[$this->aggCount]['groupRowLen'] = $tmpa[1];
				} else {
					$this->agg[$this->aggCount]['groupRow'] = $this->agg[$this->aggCount][1];
					$this->agg[$this->aggCount]['groupRowLen'] = null;
				}
				
			} elseif (substr($line,0,9) == 'HEADLINE:') {
				$this->headline = trim(substr($line,9,100));
			} elseif (substr($line,0,6) == 'DBURL:') {
				$this->dbURL = trim(substr($line,6,100));
			} elseif (substr($line,0,5) == 'RULE:') {
				$tmpa = explode(":",$line);
				$this->rule['key'] = $tmpa[1];
				$this->rule['op'] = $tmpa[2];
				$this->rule['value'] = $tmpa[3];
			} else {
				$row = explode(":",$line,2);
				if (empty($row[1])) { $row[1] = ''; }
				$this->facArray[$row[0]] = $row[1];
			}
		}
		if (DEBUG) { print "Loop Profil\n"; print_r($this->loop); print "Aggregation Profil\n"; print_r($this->agg); }
		
	}
	
	function getDataSet() {
		return $this->facArray;
	}
	
	function getLoopFile($index = 1) {
		if (!empty($this->loop[$index])) {
			return $this->loop[$index]['profilFile'];
		} else {
				return null;
		}
	}

	function getLoopGroupRow() {
		$index = 1;
		if (!empty($this->loop[$index]['groupRow'])) {
			return $this->loop[$index]['groupRow'];
		} else {
			return null;
		}
	}
	
	function getLoopSortRow() {
		$index = 1;
		return $this->loop[$index]['groupRow'];
	}
	
	function getLoopCount() {
		return $this->loopCount;
	}
	
	function getRuleKey() {
		if (is_array($this->rule)) {
			return $this->rule["key"];
		} else {
			return false;
		}
	}

	function checkRule($value) {
		if (is_array($this->rule)) {
			if (($this->rule["op"] == '=') and ($value == $this->rule["value"])) {
				return true;
			} elseif (($this->rule["op"] == '>') and ($value > $this->rule["value"])) {
				return true;
			} elseif (($this->rule["op"] == '<') and ($value < $this->rule["value"])) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}
	
	function getHeadLineNumber() {
		return $this->headline;
	}
	
	function getDbURL() {
		return $this->dbURL;
	}
	
	function getAggFile($index = 1) {
		if (!empty($this->agg[$index])) {
			return $this->agg[$index]['profilFile'];
		} else {
			return null;
		}
	}
	function getAggTyp($index = 1) {
		if (!empty($this->agg[$index])) {
			return $this->agg[$index]['aggTyp'];
		} else {
			return null;
		}
	}
	
	function getAggGroupRow() {
		$index = 1;
		if (!empty($this->agg[$index]['groupRow'])) {
			return $this->agg[$index]['groupRow'];
		} else {
			return null;
		}
	}
	
	function getAggGroupRowLen() {
		$index = 1;
		return $this->agg[$index]['groupRowLen'];
	}
	
	function getAggCount() {
		return $this->aggCount;
	}
}


?>