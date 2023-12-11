<?php

class facProfil {
	
	private $loop = [];
	private $agg = [];
	private $facArray = [];
	private $loopCount = 0;
	private $aggCount = 0;
	
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
				if (DEBUG) { print "Loop Profil\n"; print_r($this->loop); }
			} elseif (substr($line,0,4) == 'AGG:') {
				//AGG:${aggretation profil filename}[TAB]${row name for group}
				$this->agg[++$this->aggCount] = explode("\t",substr($line,4));
				$this->agg[$this->aggCount]['profilFile'] = $this->agg[$this->aggCount][0];
				if (strpos($this->agg[$this->aggCount][1],":") !== false) {
					$tmpa = explode(":",$this->agg[$this->aggCount][1]);
					$this->agg[$this->aggCount]['groupRow'] = $tmpa[0];
					$this->agg[$this->aggCount]['groupRowLen'] = $tmpa[1];
				} else {
					$this->agg[$this->aggCount]['groupRow'] = $this->agg[$this->aggCount][1];
					$this->agg[$this->aggCount]['groupRowLen'] = null;
				}
				if (DEBUG) { print "Aggregation Profil\n"; print_r($this->agg); }
				
			} else {
				$row = explode(":",$line,2);
				if (empty($row[1])) { $row[1] = ''; }
				$this->facArray[$row[0]] = $row[1];
			}
		}
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
		return $this->loop[$index]['groupRow'];
	}
	
	function getLoopSortRow() {
		$index = 1;
		return $this->loop[$index]['groupRow'];
	}
	
	function getLoopCount() {
		return $this->loopCount;
	}
	
	function getAggFile($index = 1) {
		if (!empty($this->agg[$index])) {
			return $this->agg[$index]['profilFile'];
		} else {
			return null;
		}
	}
	
	function getAggGroupRow() {
		$index = 1;
		return $this->agg[$index]['groupRow'];
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