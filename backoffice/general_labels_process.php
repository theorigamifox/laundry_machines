<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$recordType = "Labels";
	$tableName = $tableLabels;
	$linkBackLink = "general_labels.php";

	$languages = $dbA->retrieveAllRecords($tableLanguages,"languageID");
	
	$xAction=getFORM("xAction");
	if ($xAction == "insert") {
		$xName = getFORM("xName");
		$xType = getFORM("xType");
		$rArray[] = array("name",getFORM("xName"),"S");
		$rArray[] = array("type",getFORM("xType"),"S");
		$rArray[] = array("content",getFORM("xContent"),"S");
		for ($f = 0; $f < count($languages); $f++) {
			$thisLanguage = $languages[$f]["languageID"];
			if ($thisLanguage != 1) {
				$rArray[] = array("content".$thisLanguage,getFORM("xContent_".$thisLanguage),"S");	
			}
		}	
		$dbA->insertRecord($tableName,$rArray);
		userLogActionAdd($recordType,$xName);
		doRedirect("$linkBackLink?".userSessionGET());
	}
	
	if ($xAction == "update") {
		$xType = getFORM("xType");
		$labelList = $dbA->retrieveAllRecordsFromQuery("select * from $tableLabels where type='$xType' order by name");	
		for ($f = 0; $f < count($labelList); $f++) {
			if (getFORM("xDelete".$labelList[$f]["labelID"]) == "Y") {
				$dbA->deleteRecord($tableName,"labelID",$labelList[$f]["labelID"]);
			} else {
				$rArray = "";
				$rArray[] = array("content",getFORM("xContent".$labelList[$f]["labelID"]),"S");
				for ($g = 0; $g < count($languages); $g++) {
					if ($languages[$g]["languageID"] != 1) {
						$rArray[] = array("content".$languages[$g]["languageID"],getFORM("xContent".$labelList[$f]["labelID"]."_".$languages[$g]["languageID"]),"S");
					}
				}
				$dbA->updateRecord($tableName,"labelID=".$labelList[$f]["labelID"],$rArray,0);
			}
		}
		userLogActionUpdate($recordType,$xType);
		doRedirect("$linkBackLink?".userSessionGET());
	}
?>
