
<?php
 include_once './intern/autoload.php';
 include ("./intern/config.php");
 
 $konverterName = 'Konvertiere Tabelle zu Fac';
 
 
 if (strtolower(php_sapi_name()) != 'cli') {
 	include('./intern/views/converter_view.php');
 }
 
 if (DEBUG) { print "<pre>"; }
 
 if (isset($_POST["convert"]) or (isset($argv) and in_array("/infile", $argv))) {

 	if (isset($_FILES['uploadFile']['tmp_name']) and (is_uploaded_file($_FILES['uploadFile']['tmp_name'])) )  {
		if (DEBUG) { print_r($_FILES); }
		$info = pathinfo($_FILES['uploadFile']['name']);
		$ext = $info["extension"];
		$uploadFile = new myFile($docpath.'IMPORT_'.uniqid().".".$ext, "newUpload");
		$uploadName = $uploadFile->moveUploaded($_FILES['uploadFile']['tmp_name']);
		if (DEBUG) { print $uploadName."\n"; }
 	} elseif ((strtolower(php_sapi_name()) == 'cli') and in_array("/profil", $argv)) {
			$uploadName = $argv[ array_search("/infile",$argv) + 1 ];
			$_POST["profilName"] = trim($argv[ array_search("/profil",$argv) + 1 ],'" ');
 	} else {
 		print "Fehler beim Upload oder fehlende Parameter /infile /profil \n";
 	}
	
	$timestart = time();
	$facProfil = new facProfil($profiles[$_POST["profilName"]]);
	$dataFile = new dataFile($uploadName, $facProfil->getHeadLineNumber(), $facProfil->getDbURL());
	$timeloopstart = time();
	if ( $facProfil->getLoopCount() > 0) {
		if ($facProfil->getLoopSortRow() != 'NOSORT') {
			$dataFile->sortData($facProfil->getLoopGroupRow(), $facProfil->getLoopSortRow());
		}
		$facLoopProfil = [];
		for ($cnt = 1 ; $cnt <= $facProfil->getLoopCount(); $cnt++) {
			if (DEBUG) { print "Read LOOP ".$facProfil->getLoopFile($cnt)."\n"; }
			$facLoopProfil[$cnt] = new facProfil($facProfil->getLoopFile($cnt)); 
		}
		$facAggProfil = [];
		for ($cnt = 1 ; $cnt <= $facProfil->getAggCount(); $cnt++) {
			if (DEBUG) { print "Read AGG ".$facProfil->getAggFile($cnt)."\n"; }
			$facAggProfil[$cnt] = new facProfil($facProfil->getAggFile($cnt));
		}
	}
	$exportFacFile = new myFile($docpath.'FACTO.fac', "new");
	$exportFacFile->writeUTF8BOM();
	$rowCount = 0;
	$oldValue = null;
	$oldoldValue = null;
	$oldsubValue = null;
	while ($row = $dataFile->getNextRow()) {
		if (DEBUG) { print "NEW ROW ".$rowCount."\n"; }
		$newnewValue = substr($row[$facProfil->getAggGroupRow()],0,$facProfil->getAggGroupRowLen());
		if (empty($facProfil->getLoopGroupRow()) or ($row[$facProfil->getLoopGroupRow()] <> $oldValue) ) {
			if (DEBUG) { print "NEW GROUPVALUE ".$row[$facProfil->getLoopGroupRow()]."\n"; }
			$exportFacFile->facData($dataFile->generateFac($facProfil->getDataSet(), $row));
			$oldValue = $row[$facProfil->getLoopGroupRow()];
			$oldoldValue = $newnewValue;
		}
		if (!empty($facProfil->getAggGroupRow()) and (($newnewValue <> $oldoldValue) or ($newnewValue <> $oldsubValue)) ) {
			if (DEBUG) { print "NEW AGG GROUP VALUE ".$row[$facProfil->getAggGroupRow()]."\n"; }
			for ($cnt = 1 ; $cnt <= $facProfil->getAggCount(); $cnt++) {
				if (DEBUG) { print "AGGTYP: ".$facProfil->getAggTyp($cnt)."\n"; }
				if ((($facProfil->getAggTyp($cnt) == "SUB:") and ($newnewValue <> $oldsubValue)) or 
					(($facProfil->getAggTyp($cnt) == "AGG:") and ($newnewValue <> $oldoldValue))) {
						var_dump($facAggProfil[$cnt]->checkRule($row[$rkey]));
						if (!($rkey = $facAggProfil[$cnt]->getRuleKey()) or ($facAggProfil[$cnt]->checkRule($row[$rkey]))) {
				       		$exportFacFile->facData($dataFile->generateFac($facAggProfil[$cnt]->getDataSet(), $row));
						}
				}
			}
			$oldsubValue = $newnewValue;
			$oldoldValue = $newnewValue;
		}
		
		if ( ! empty($facProfil->getLoopGroupRow()) ) {
			if (DEBUG) { print "NEW LOOP LINE ".$row[$facProfil->getLoopGroupRow()]."\n"; }
			for ($cnt = 1 ; $cnt <= $facProfil->getLoopCount(); $cnt++) {
				if (!($rkey = $facLoopProfil[$cnt]->getRuleKey()) or ($facLoopProfil[$cnt]->checkRule($row[$rkey]))) {
					$exportFacFile->facData($dataFile->generateFac($facLoopProfil[$cnt]->getDataSet(), $row));
				}
			}
		}
		$rowCount++;
	}
	$filename = $exportFacFile->getCheckedName();
	$exportfile = $docpath.$filename;
	
	if (DEBUG) { 
		print "Starte Einlesen:".date("H:i:s",$timestart)." Starte konvertieren:".date("H:i:s",$timeloopstart)." Ende:".date("H:i:s")."\n";
		print "</pre>"; 
	}
		
	if (!DEBUG) { unlink($uploadFile->getCheckedPathName()); }
	
	include('./intern/views/converter_result_view.php');
 }
 ?>
