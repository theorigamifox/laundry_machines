<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$recordType = "Language";
	$tableName = $tableLanguages;
	$linkBackButton = "LANGUAGE SETTINGS";
	$linkBackLink = "general_languages.php";
	
	$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"position,name");

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
			$rArray[] = array("visible",getFORM("xVisible"),"YN");
			$rArray[] = array("doubleByte",getFORM("xDoubleByte"),"YN");
			$dbA->insertRecord($tableName,$rArray);
			$languageID = $dbA->lastID();
			$dbA->query("alter table $tableSections add column title$languageID varchar(250) not null");
			$dbA->query("alter table $tableSections add column shortDescription$languageID varchar(250) not null");
			$dbA->query("alter table $tableSections add column fullDescription$languageID text not null");
			$dbA->query("alter table $tableSections add column metaDescription$languageID varchar(250) not null");
			$dbA->query("alter table $tableSections add column metaKeywords$languageID varchar(250) not null");
			$dbA->query("alter table $tableProducts add column name$languageID varchar(250) not null");
			$dbA->query("alter table $tableProducts add column shortdescription$languageID varchar(250) not null");
			$dbA->query("alter table $tableProducts add column description$languageID text not null");
			$dbA->query("alter table $tableProducts add column metaDescription$languageID varchar(250) not null");
			$dbA->query("alter table $tableProducts add column metaKeywords$languageID varchar(250) not null");
			$dbA->query("alter table $tableSnippets add column title$languageID varchar(250) not null");
			$dbA->query("alter table $tableSnippets add column content$languageID text not null");
			$dbA->query("alter table $tableCustomerFields add column titleText$languageID varchar(250) not null");
			$dbA->query("alter table $tableCustomerFields add column validationmessage$languageID varchar(250) not null");
			$dbA->query("alter table $tablePaymentOptions add column name$languageID varchar(250) not null");
			$dbA->query("alter table $tableShippingTypes add column name$languageID varchar(250) not null");
			$dbA->query("alter table $tableExtraFieldsValues add column content$languageID varchar(250) not null");
			$dbA->query("alter table $tableLabels add column content$languageID char(250) not null");
			$dbA->query("alter table $tableNews add column title$languageID varchar(250) not null");
			$dbA->query("alter table $tableNews add column content$languageID text not null");
			if (is_array($extraFieldsArray)) {
				for ($f = 0; $f < count($extraFieldsArray); $f++) {
					switch ($extraFieldsArray[$f]["type"]) {
						case "TEXT":
							$dbA->query("alter table $tableProducts add column extrafield".$extraFieldsArray[$f]["extraFieldID"]."_".$languageID." varchar(250) not null");
							break;
						case "TEXTAREA":
							$dbA->query("alter table $tableProducts add column extrafield".$extraFieldsArray[$f]["extraFieldID"]."_".$languageID." text not null");
							break;
					}
				}
			}
			$dbA->query("alter table $tableExtraFields add column title$languageID varchar(250) not null");
			userLogActionAdd($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "delete") {
		$xLanguageID = getFORM("xLanguageID");
		if (!$dbA->doesIDExist($tableName,"languageID",$xLanguageID,$uRecord)) {
			setupProcessMessage($recordType,$xLanguageID,"error_existance","BACK","");
		} else {
			$languageID = $uRecord["languageID"];
			$dbA->deleteRecord($tableName,"languageID",$xLanguageID);
			$dbA->query("alter table $tableSections drop column title$languageID");
			$dbA->query("alter table $tableSections drop column shortDescription$languageID");
			$dbA->query("alter table $tableSections drop column fullDescription$languageID");
			$dbA->query("alter table $tableSections drop column metaDescription$languageID");
			$dbA->query("alter table $tableSections drop column metaKeywords$languageID");
			$dbA->query("alter table $tableProducts drop column name$languageID");
			$dbA->query("alter table $tableProducts drop column shortdescription$languageID");
			$dbA->query("alter table $tableProducts drop column description$languageID");
			$dbA->query("alter table $tableProducts drop column metaDescription$languageID");
			$dbA->query("alter table $tableProducts drop column metaKeywords$languageID");
			$dbA->query("alter table $tableSnippets drop column title$languageID");
			$dbA->query("alter table $tableSnippets drop column content$languageID");
			$dbA->query("alter table $tableCustomerFields drop column titleText$languageID");
			$dbA->query("alter table $tableCustomerFields drop column validationmessage$languageID");
			$dbA->query("alter table $tablePaymentOptions drop column name$languageID");
			$dbA->query("alter table $tableShippingTypes drop column name$languageID");
			$dbA->query("alter table $tableExtraFieldsValues drop column content$languageID");
			$dbA->query("alter table $tableLabels drop column content$languageID");
			$dbA->query("alter table $tableNews drop column title$languageID");
			$dbA->query("alter table $tableNews drop column content$languageID");
			if (is_array($extraFieldsArray)) {
				for ($f = 0; $f < count($extraFieldsArray); $f++) {
					switch ($extraFieldsArray[$f]["type"]) {
						case "TEXT":
							$dbA->query("alter table $tableProducts drop column extrafield".$extraFieldsArray[$f]["extraFieldID"]."_".$languageID);
							break;
						case "TEXTAREA":
							$dbA->query("alter table $tableProducts drop column extrafield".$extraFieldsArray[$f]["extraFieldID"]."_".$languageID);
							break;
					}
				}
			}
			$dbA->query("alter table $tableExtraFields drop column title$languageID");
			userLogActionDelete($recordType,$uRecord["name"]);
			if ($xLanguageID == retrieveOption("defaultLanguage")) {
				updateOption("defaultLanguage",1);
			}			
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$xLanguageID = getFORM("xLanguageID");
		$xName = getFORM("xName");
		if (!$dbA->doesIDExist($tableName,"languageID",$xLanguageID,$uRecord)) {
			setupProcessMessage($recordType,getFORM("xName"),"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableName,"languageID",$xLanguageID,"name",$xName)) {
				setupProcessMessage($recordType,getFORM("xName"),"error_duplicate_update","BACK","");					
			}
			$rArray[] = array("name",getFORM("xName"),"S");
			if ($xLanguageID != 1) {
				$rArray[] = array("visible",getFORM("xVisible"),"YN");
			}
			$rArray[] = array("doubleByte",getFORM("xDoubleByte"),"YN");
			$dbA->updateRecord($tableName,"languageID=$xLanguageID",$rArray,0);
			userLogActionUpdate($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}		
	}
?>
