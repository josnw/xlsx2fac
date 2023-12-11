
<?php
 include_once './intern/autoload.php';
 include ("./intern/config.php");
 
 $konverterName = 'Konvertiere Tabelle zu Fac';
 
 
 
 include('./intern/views/converter_view.php');
 
 if (DEBUG) { print "<pre>"; }
 
 if (isset($_POST["convert"]) or (isset($argv) and in_array("/csvfile", $argv))) {

	if (isset($_FILES['uploadFile']['tmp_name']) and (is_uploaded_file($_FILES['uploadFile']['tmp_name'])))  {

		$uploadFile = new myFile($docpath.'IMPORT_'.uniqid().".csv", "newUpload");
		$uploadName = $uploadFile->moveUploaded($_FILES['uploadFile']['tmp_name']);

		
		$dataFile = new dataFile($uploadName);
		$facProfil = new facProfil($profiles[$_POST["profilName"]]);
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
		while ($row = $dataFile->getNextRow()) {
			$newnewValue = substr($row[$facProfil->getAggGroupRow()],0,$facProfil->getAggGroupRowLen());
			if (empty($facProfil->getLoopGroupRow()) or ($row[$facProfil->getLoopGroupRow()] <> $oldValue) ) {
				if (DEBUG) { print "NEW GROUPVALUE ".$row[$facProfil->getLoopGroupRow()]."\n"; }
				$exportFacFile->facData($dataFile->generateFac($facProfil->getDataSet(), $row));
				$oldValue = $row[$facProfil->getLoopGroupRow()];
				$oldoldValue = $newnewValue;
			}
			if (!empty($facProfil->getAggGroupRow()) and ($newnewValue <> $oldoldValue) ) {
				if (DEBUG) { print "NEW AGG GROUP VALUE ".$row[$facProfil->getAggGroupRow()]."\n"; }
				for ($cnt = 1 ; $cnt <= $facProfil->getAggCount(); $cnt++) {
					$exportFacFile->facData($dataFile->generateFac($facAggProfil[$cnt]->getDataSet(), $row));
				}
				$oldoldValue = $newnewValue;
			}
			if ( ! empty($facProfil->getLoopGroupRow()) ) {
				if (DEBUG) { print "NEW LOOP LINE ".$row[$facProfil->getLoopGroupRow()]."\n"; }
				for ($cnt = 1 ; $cnt <= $facProfil->getLoopCount(); $cnt++) {
					$exportFacFile->facData($dataFile->generateFac($facLoopProfil[$cnt]->getDataSet(), $row));
				}
			}
			$rowCount++;
		}
		$filename = $exportFacFile->getCheckedName();
		$exportfile = $docpath.$filename;


	}

	if (DEBUG) { print "</pre>"; }
	
	
	unlink($uploadFile->getCheckedPathName());
	
	include('./intern/views/converter_result_view.php');
 }
 ?>
