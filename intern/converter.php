
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
			$dataFile->sortData($facProfil->getLoopGroupRow(), $facProfil->getLoopSortRow());
			$facLoopProfil = [];
			for ($cnt = 1 ; $cnt <= $facProfil->getLoopCount(); $cnt++) {
				$facLoopProfil[$cnt] = new facProfil($facProfil->getLoopFile($cnt)); 
			}
		}
		
		$exportFacFile = new myFile($docpath.'FACTO.fac', "new");
		$exportFacFile->writeUTF8BOM();
		$rowCount = 0;
		$oldValue = null;
		while ($row = $dataFile->getNextRow()) {
			if (empty($facProfil->getLoopGroupRow()) or ($row[$facProfil->getLoopGroupRow()] <> $oldValue) ) {
				$exportFacFile->facData($dataFile->generateFac($facProfil->getDataSet(), $row));
				$oldValue = $row[$facProfil->getLoopGroupRow()];
			} 
			if ( ! empty($facProfil->getLoopGroupRow()) ) {
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
