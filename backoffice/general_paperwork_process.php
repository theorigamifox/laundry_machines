<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$recordType = "Paperwork Template";
	$tableName = $tablePaperwork;
	$linkBackButton = "EXTRA ORDER PAPERWORK";
	$linkBackLink = "general_paperwork.php";

	$xAction=getFORM("xAction");
	if ($xAction == "default") {
		$xLanguageSelect = makeInteger(getFORM("xLanguageSelect"));
		if ($xLanguageSelect > 0) {
			updateOption("defaultLanguage",$xLanguageSelect);
			userLog("Updated Default Language");
		}
		doRedirect("$linkBackLink?".userSessionGET());
	}	
	if ($xAction == "insert") {
		$xName = getFORM("xName");
		if ($dbA->doesRecordExist($tableName,"name",$xName)) {
			setupProcessMessage($recordType,$xName,"error_duplicate_add","BACK","");
		} else {
			$rArray[] = array("name",getFORM("xName"),"S");
			$rArray[] = array("templateFile",getFORM("xTemplateFile"),"S");
			$dbA->insertRecord($tableName,$rArray);
			userLogActionAdd($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "delete") {
		$xPaperworkID = getFORM("xPaperworkID");
		if (!$dbA->doesIDExist($tableName,"paperworkID",$xPaperworkID,$uRecord)) {
			setupProcessMessage($recordType,$xPaperworkID,"error_existance","BACK","");
		} else {
			$languageID = $uRecord["paperworkID"];
			$dbA->deleteRecord($tableName,"paperworkID",$xPaperworkID);
			userLogActionDelete($recordType,$uRecord["name"]);	
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$xPaperworkID = getFORM("xPaperworkID");
		$xName = getFORM("xName");
		if (!$dbA->doesIDExist($tableName,"paperworkID",$xPaperworkID,$uRecord)) {
			setupProcessMessage($recordType,getFORM("xName"),"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableName,"paperworkID",$xPaperworkID,"name",$xName)) {
				setupProcessMessage($recordType,getFORM("xName"),"error_duplicate_update","BACK","");					
			}
			$rArray[] = array("name",getFORM("xName"),"S");
			$rArray[] = array("templateFile",getFORM("xTemplateFile"),"S");
			$dbA->updateRecord($tableName,"paperworkID=$xPaperworkID",$rArray,0);
			userLogActionUpdate($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}		
	}
?>
