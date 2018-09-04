<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$recordType = "News Item";
	$tableName = $tableNews;
	$linkBackLink = "general_news.php";

	$languages = $dbA->retrieveAllRecords($tableLanguages,"languageID");
	
	$xAction=getFORM("xAction");
	if ($xAction == "insert") {
		$xTitle = getFORM("xTitle");
		if ($dbA->doesRecordExist($tableSnippets,"title",$xTitle)) {
			setupProcessMessage($recordType,$xTitle,"error_duplicate_add","BACK","");
		} else {		
			$rArray[] = array("title",getFORM("xTitle"),"S");
			$rArray[] = array("content",getFORM("xContent"),"S");
			for ($f = 0; $f < count($languages); $f++) {
				$thisLanguage = $languages[$f]["languageID"];
				if ($thisLanguage != 1) {
					$rArray[] = array("title".$thisLanguage,getFORM("xTitle".$thisLanguage),"S");			
					$rArray[] = array("content".$thisLanguage,getFORM("xContent".$thisLanguage),"S");	
				}
			}	
			$rArray[] = array("datetime",date("YmdHis"),"S");
			$rArray[] = array("postedBy",getFORM("xUsername"),"S");
			$rArray[] = array("position",0,"N");
			$dbA->insertRecord($tableName,$rArray,0);
			userLogActionAdd($recordType,$xTitle);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "delete") {
		$xNewsID = getFORM("xNewsID");
		if (!$dbA->doesIDExist($tableName,"newsID",$xNewsID,$uRecord)) {
			setupProcessMessage($recordType,getFORM("xTitle"),"error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"newsID",$xNewsID);
			userLogActionDelete($recordType,$uRecord["title"]);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$xNewsID = getFORM("xNewsID");
		$xTitle = getFORM("xTitle");
		if (!$dbA->doesIDExist($tableName,"newsID",$xNewsID,$uRecord)) {
			setupProcessMessage($recordType,$xTitle,"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableName,"newsID",$xNewsID,"title",$xTitle)) {
				setupProcessMessage($recordType,$xTitle,"error_duplicate_update","BACK","");					
			}
			$rArray[] = array("title",getFORM("xTitle"),"S");
			$rArray[] = array("content",getFORM("xContent"),"S");
			for ($f = 0; $f < count($languages); $f++) {
				$thisLanguage = $languages[$f]["languageID"];
				if ($thisLanguage != 1) {
					$rArray[] = array("title".$thisLanguage,getFORM("xTitle".$thisLanguage),"S");			
					$rArray[] = array("content".$thisLanguage,getFORM("xContent".$thisLanguage),"S");	
				}
			}
			if (getFORM("xResetDate") == "Y") {
				$rArray[] = array("datetime",date("YmdHis"),"S");
			}
			$dbA->updateRecord($tableName,"newsID=$xNewsID",$rArray,0);
			userLogActionUpdate($recordType,$xTitle);
			doRedirect("$linkBackLink?".userSessionGET());
		}		
	}
	if ($xAction == "reorder") {
		$xNewOrder = getFORM("xNewOrder");
		$newOrderBits = split(";",$xNewOrder);
		for ($f = 0; $f < count($newOrderBits)-1; $f++) {
			$g = $f+1;
			$dbA->query("update $tableNews set position=$g where newsID=$newOrderBits[$f]");
		}
		userLogAction("Sorted","News Items","All");
		$linkBackLink = "general_news.php?".userSessionGET();
		doRedirect("$linkBackLink?".userSessionGET());
	}		
?>
