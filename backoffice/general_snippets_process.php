<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$recordType = "Snippet";
	$tableName = $tableSnippets;
	$linkBackLink = "general_snippets.php";

	$languages = $dbA->retrieveAllRecords($tableLanguages,"languageID");
	
	$xAction=getFORM("xAction");
	if ($xAction == "insert") {
		$xName = getFORM("xName");
		if ($dbA->doesRecordExist($tableSnippets,"name",$xName)) {
			setupProcessMessage($recordType,$xName,"error_duplicate_add","BACK","");
		} else {		
			$rArray[] = array("name",getFORM("xName"),"S");
			$rArray[] = array("title",getFORM("xTitle"),"S");
			$rArray[] = array("content",getFORM("xContent"),"S");
			for ($f = 0; $f < count($languages); $f++) {
				$thisLanguage = $languages[$f]["languageID"];
				if ($thisLanguage != 1) {
					$rArray[] = array("title".$thisLanguage,getFORM("xTitle".$thisLanguage),"S");			
					$rArray[] = array("content".$thisLanguage,getFORM("xContent".$thisLanguage),"S");	
				}
			}	
			$dbA->insertRecord($tableName,$rArray);
			userLogActionAdd($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "delete") {
		$xSnippetID = getFORM("xSnippetID");
		if (!$dbA->doesIDExist($tableName,"snippetID",$xSnippetID,$uRecord)) {
			setupProcessMessage($recordType,getFORM($xName),"error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"snippetID",$xSnippetID);
			userLogActionDelete($recordType,$uRecord["name"]);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$xSnippetID = getFORM("xSnippetID");
		$xName = getFORM("xName");
		if (!$dbA->doesIDExist($tableName,"snippetID",$xSnippetID,$uRecord)) {
			setupProcessMessage($recordType,$xName,"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableName,"snippetID",$xSnippetID,"name",$xName)) {
				setupProcessMessage($recordType,$xName,"error_duplicate_update","BACK","");					
			}
			$rArray[] = array("name",getFORM("xName"),"S");
			$rArray[] = array("title",getFORM("xTitle"),"S");
			$rArray[] = array("content",getFORM("xContent"),"S");
			for ($f = 0; $f < count($languages); $f++) {
				$thisLanguage = $languages[$f]["languageID"];
				if ($thisLanguage != 1) {
					$rArray[] = array("title".$thisLanguage,getFORM("xTitle".$thisLanguage),"S");			
					$rArray[] = array("content".$thisLanguage,getFORM("xContent".$thisLanguage),"S");	
				}
			}
			$dbA->updateRecord($tableName,"snippetID=$xSnippetID",$rArray,0);
			userLogActionUpdate($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}		
	}
?>
