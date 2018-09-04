<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$recordType = "Extra Field";
	$tableName = $tableExtraFields;
	$linkBackLink = "general_extrafields.php";

	$languages = $dbA->retrieveAllRecords($tableLanguages,"languageID");

	$xAction=getFORM("xAction");
	if ($xAction == "insert") {
		$xName = getFORM("xName");
		if ($dbA->doesRecordExist($tableName,"name",$xName)) {
			setupProcessMessage($recordType,$xName,"error_duplicate_add","BACK","");
		} else {
			$rArray[] = array("type",getFORM("xFieldType"),"S");			
			$rArray[] = array("name",getFORM("xName"),"S");
			$rArray[] = array("title",getFORM("xTitle"),"S");
			if (getFORM("xFieldType") == "USERINPUT") {
				$rArray[] = array("size",getFORM("xSize"),"N");
				$rArray[] = array("maxlength",getFORM("xMaxLength"),"N");
			}
			for ($f = 0; $f < count($languages); $f++) {
				$thisLanguage = $languages[$f]["languageID"];
				if ($thisLanguage != 1) {
					$rArray[] = array("title".$thisLanguage,getFORM("xTitle".$thisLanguage),"S");			
				}
			}			
			$dbA->insertRecord($tableName,$rArray,0);	
			$extraFieldID = $dbA->lastID();
			switch (getFORM("xFieldType")) {
				case "CHECKBOXES":
					//$dbA->query("alter table $tableProducts add column extrafield$extraFieldID char(250) not null");
					//$dbA->query("alter table $tableCartsContents add column extrafield$extraFieldID TEXT not null");
					$dbA->query("alter table $tableCartsContents add column extrafieldid".$extraFieldID." TEXT not null");
					$dbA->query("alter table $tableCombinations add column extrafield".$extraFieldID." INT not null");
					break;
				case "TEXTAREA":
					$dbA->query("alter table $tableProducts add column extrafield$extraFieldID TEXT not null");
					for ($f = 0; $f < count($languages); $f++) {
						$thisLanguage=$languages[$f]["languageID"];
						if ($thisLanguage != 1) {
							$dbA->query("alter table $tableProducts add column extrafield$extraFieldID"."_".$thisLanguage." TEXT not null");
						}
					}
							
					//$dbA->query("alter table $tableCartsContents add column extrafield$extraFieldID TEXT not null");
					//$dbA->query("alter table $tableCartsContents add column extrafieldid".$extraFieldID." varchar(250) not null");
					//$dbA->query("alter table $tableCombinations add column extrafield".$extraFieldID." INT not null");
					break;
				case "TEXT":
					$dbA->query("alter table $tableProducts add column extrafield$extraFieldID varchar(250) not null");
					for ($f = 0; $f < count($languages); $f++) {
						$thisLanguage=$languages[$f]["languageID"];
						if ($thisLanguage != 1) {
							$dbA->query("alter table $tableProducts add column extrafield$extraFieldID"."_".$thisLanguage." varchar(250) not null");
						}
					}
					
					//$dbA->query("alter table $tableCartsContents add column extrafieldid".$extraFieldID." varchar(250) not null");
					//$dbA->query("alter table $tableCombinations add column extrafield".$extraFieldID." INT not null");
					break;					
				case "SELECT":
					//$dbA->query("alter table $tableProducts add column extrafield$extraFieldID varchar(250) not null");
					$dbA->query("alter table $tableCartsContents add column extrafieldid".$extraFieldID." INT not null");
					$dbA->query("alter table $tableCombinations add column extrafield".$extraFieldID." INT not null");
					break;	
				case "RADIOBUTTONS":
					//$dbA->query("alter table $tableProducts add column extrafield$extraFieldID varchar(250) not null");
					$dbA->query("alter table $tableCartsContents add column extrafieldid".$extraFieldID." INT not null");
					$dbA->query("alter table $tableCombinations add column extrafield".$extraFieldID." INT not null");
					break;	
				case "IMAGE":
					$dbA->query("alter table $tableProducts add column extrafield$extraFieldID varchar(250) not null");
					//$dbA->query("alter table $tableCartsContents add column extrafield$extraFieldID varchar(250) not null");
					//$dbA->query("alter table $tableCartsContents add column extrafieldid".$extraFieldID." INT not null");
					//$dbA->query("alter table $tableCombinations add column extrafield".$extraFieldID." INT not null");
				case "USERINPUT":
					$dbA->query("alter table $tableProducts add column extrafield$extraFieldID INT not null");
					$dbA->query("alter table $tableCartsContents add column extrafieldid".$extraFieldID." TEXT not null");
					break;	
			}
			$dbA->query("alter table $tableAdvancedPricing add column extrafield".$extraFieldID." INT not null");
			userLogActionAdd($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "delete") {
		$xExtraFieldID = getFORM("xExtraFieldID");
		if (!$dbA->doesIDExist($tableName,"extraFieldID",$xExtraFieldID,$uRecord)) {
			setupProcessMessage($recordType,getFORM($xCode),"error_existance","BACK","");
		} else {
			$xExtraFieldID = $uRecord["extraFieldID"];
			$dbA->deleteRecord($tableName,"extraFieldID",$xExtraFieldID);
			$dbA->query("alter table $tableProducts drop column extrafield$xExtraFieldID");
			for ($f = 0; $f < count($languages); $f++) {
				$thisLanguage=$languages[$f]["languageID"];
				if ($thisLanguage != 1) {
					$dbA->query("alter table $tableProducts drop column extrafield$xExtraFieldID"."_".$thisLanguage);
				}
			}
			$dbA->query("alter table $tableCartsContents drop column extrafield$xExtraFieldID");
			$dbA->query("alter table $tableCartsContents drop column extrafieldid$xExtraFieldID");
			$dbA->query("alter table $tableAdvancedPricing drop column extrafield$xExtraFieldID");
			$dbA->query("alter table $tableCombinations drop column extrafield$xExtraFieldID");
			$dbA->query("delete from $tableExtraFieldsValues where extraFieldID=$xExtraFieldID");
			userLogActionDelete($recordType,$uRecord["name"]);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$xExtraFieldID = getFORM("xExtraFieldID");
		$xName = getFORM("xName");
		if (!$dbA->doesIDExist($tableName,"extraFieldID",$xExtraFieldID,$uRecord)) {
			setupProcessMessage($recordType,getFORM("xName"),"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableName,"extraFieldID",$xExtraFieldID,"name",$xName)) {
				setupProcessMessage($recordType,getFORM("xName"),"error_duplicate_update","BACK","");					
			}
			$rArray[] = array("name",getFORM("xName"),"S");
			$rArray[] = array("title",getFORM("xTitle"),"S");
			if (getFORM("xFieldType") == "USERINPUT") {
				$rArray[] = array("size",getFORM("xSize"),"N");
				$rArray[] = array("maxlength",getFORM("xMaxLength"),"N");
			}
			for ($f = 0; $f < count($languages); $f++) {
				$thisLanguage = $languages[$f]["languageID"];
				if ($thisLanguage != 1) {
					$rArray[] = array("title".$thisLanguage,getFORM("xTitle".$thisLanguage),"S");			
				}
			}				
			$dbA->updateRecord($tableName,"extraFieldID=$xExtraFieldID",$rArray,0);
			userLogActionUpdate($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}		
	}
	if ($xAction == "reorder") {
		$xNewOrder = getFORM("xNewOrder");
		$newOrderBits = split(";",$xNewOrder);
		for ($f = 0; $f < count($newOrderBits)-1; $f++) {
			$g = $f+1;
			$dbA->query("update $tableExtraFields set position=$g where extraFieldID=$newOrderBits[$f]");
		}
		userLogAction("Sorted","Extra Fields","All");
		doRedirect("$linkBackLink?".userSessionGET());
	}
?>
