<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$recordType = "Product Flag";
	$tableName = $tableProductsFlags;
	$linkBackButton = "PRODUCT FLAG";
	$linkBackLink = "products_flags.php";

	$xAction=getFORM("xAction");
	if ($xAction == "insert") {
		$xName = getFORM("xName");
		if ($dbA->doesRecordExist($tableName,"name",$xName)) {
			setupProcessMessage($recordType,$xName,"error_duplicate_add","BACK","");
		} else {
			$rArray[] = array("name",$xName,"S");			
			$rArray[] = array("description",getFORM("xDescription"),"S");	
			$dbA->insertRecord($tableName,$rArray);
			$flagID = $dbA->lastID();
			userLogActionAdd($recordType,$xName);		
			$dbA->query("alter table $tableProducts add column flag$flagID char(1) default 'N'");
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "delete") {
		$xFlagID = getFORM("xFlagID");
		if (!$dbA->doesIDExist($tableName,"flagID",$xFlagID,$uRecord)) {
			setupProcessMessage($recordType,"","error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"flagID",$xFlagID);
			$dbA->query("alter table $tableProducts drop column flag$xFlagID");
			userLogActionDelete($recordType,$uRecord["name"]);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$xFlagID = getFORM("xFlagID");
		$xName = getFORM("xName");
		if (!$dbA->doesIDExist($tableName,"flagID",$xFlagID,$uRecord)) {
			setupProcessMessage($recordType,getFORM("xName"),"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableName,"flagID",$xFlagID,"name",$xName)) {
				setupProcessMessage($recordType,getFORM("xName"),"error_duplicate_update","BACK","");				
			}
			$rArray[] = array("name",getFORM("xName"),"S");
			$rArray[] = array("description",getFORM("xDescription"),"S");
			$dbA->updateRecord($tableName,"flagID=$xFlagID",$rArray);
			userLogActionUpdate($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}		
	
	}
?>
