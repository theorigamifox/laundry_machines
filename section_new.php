<?php
	include("static/includeBase_front.php");
	
	if (retrieveOption("useRewriteURLs") == 0 || array_key_exists("xSec",$_GET)) {
		$xSec=makeInteger(getFORM("xSec"));
	} else {
		$xBits = explode(".php/",$_SERVER["REQUEST_URI"]);
		$xOptions = explode("/",@$xBits[1]);
		if (count($xOptions) < 2) {
			doRedirect(configureURL("index.php"));
		}
		$_GET["xSec"] = makeInteger($xOptions[0]);
		$xSec = $xOptions[0];
		if (count($xOptions) == 2) {
			$_GET["xPage"] = makeInteger($xOptions[1]);
		} else {
			$_GET["xPage"] = 1;
		}
	}
	if ($xSec != 0) {
		$thisTemplate = "section_new.html";
		$pageType = "section";
		
		dbConnect($dbA);
	
		$result = $dbA->query("select * from $tableSections where sectionID=$xSec");
		$count = $dbA->count($result);
		if ($count != 0) {
			$record = $dbA->fetch($result);
			
			if ((strpos($record["accTypes"],";".$cartMain["accTypeID"].";") === false && strpos($record["accTypes"],";0;") === false) || $record["visible"] == "N") {
				doRedirect(configureURL("index.php"));
			} else {
				$thisTemplate = $record["templateFile"];
			}
			addReportsPopularityRecord("S",$xSec);
			addToRecentlyViewed("section",$xSec);
		} else {
			doRedirect(configureURL("index.php"));
		}
		
		include("routines/cartOutputData.php");
		
		$tpl->showPage();
		$dbA->close();
	} else {
		doRedirect(configureURL("index.php"));
	}
?>