<?php
	include("static/includeBase_front.php");

	$downloadRef = makeSafe(getFORM("xRef"));
	dbConnect($dbA);
	$result = $dbA->query("select * from $tableDigitalPurchases where downloadRef='$downloadRef'");
	if ($dbA->count($result) == 0) {
		$thisTemplate = "downloadproblem.html";
		$pageType = "downloadproblem";			
		include ("routines/cartOutputData.php");
		$tpl->showPage();
		$dbA->close();
		exit;
	}
	$downloadRecord = $dbA->fetch($result);
	$currentTime = time();
	$timeDifference = $currentTime - $downloadRecord["createtime"];
	$maxTimeDiff = retrieveOption("downloadsTime");
	if ($maxTimeDiff > 0) {
		$maxTimeDiff = $maxTimeDiff * 3600;
		if ($timeDifference > $maxTimeDiff) {
			$thisTemplate = "downloadproblem.html";
			$pageType = "downloadproblem";			
			include ("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;
		}
	}
	$downloadRecord["attempts"] = makeInteger($downloadRecord["attempts"]);
	$maxAttempts = retrieveOption("downloadsUses");
	if ($maxAttempts > 0) {
		if ($downloadRecord["attempts"] >= $maxAttempts) {
			$thisTemplate = "downloadproblem.html";
			$pageType = "downloadproblem";			
			include ("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;
		}
	}
	$rArray = null;
	$rArray[] = array("attempts",$downloadRecord["attempts"]+1,"N");
	$dbA->updateRecord($tableDigitalPurchases,"downloadID=".$downloadRecord["downloadID"],$rArray);
	$dbA->close();
	header("Cache-control: private"); 
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.$downloadRecord["digitalFile"]);
	header("Content-Length: ".filesize(retrieveOption("downloadsDirectory").$downloadRecord["digitalFile"]));
	$fp = fopen(retrieveOption("downloadsDirectory").$downloadRecord["digitalFile"], 'r');
	fpassthru($fp);
	fclose($fp);
?>