<?php

class facProfil {
	
	private $loop = [];
	private $facArray = [];
	private $loopCount = 0;
	
	public function __construct($filename) {
		if (DEBUG) { print "Profile:".$filename."\n"; }
		$pfile = fopen($filename, "r");
		while ( $line = fgets($pfile, 4096)) {
			if (substr($line,0,5) !== 'LOOP:') {
				$row = explode(":",$line,2);
				if (empty($row[1])) { $row[1] = ''; }
				$this->facArray[$row[0]] = $row[1]; 
			} else {
				//LOOP:${loop profil filename}[TAB]${row name for group}[TAB]${row name for sort}
				
				$this->loop[++$this->loopCount] = explode("\t",substr($line,5));
				$this->loop[$this->loopCount]['profilFile'] = $this->loop[$this->loopCount][0];
				$this->loop[$this->loopCount]['groupRow'] = $this->loop[$this->loopCount][1];
				$this->loop[$this->loopCount]['sortRow'] = $this->loop[$this->loopCount][2];
				if (DEBUG) { print "Loop Profil\n"; print_r($this->loop); }
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
}


?>