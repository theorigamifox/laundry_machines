<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$recordType = "Affiliate Group";
	$tableName = $tableAffiliatesGroups;
	$linkBackLink = "affiliates_groups.php";

	$xAction=getFORM("xAction");
	if ($xAction == "insert") {
		$xName = getFORM("xName");
		if ($dbA->doesRecordExist($tableName,"name",$xName)) {
			setupProcessMessage($recordType,$xName,"error_duplicate_add","BACK","");
		} else {
			$rArray[] = array("name",$xName,"S");			
			$rArray[] = array("commission",getFORM("xCommission"),"D");
			$rArray[] = array("commissionType",getFORM("xCommissionType"),"S");
			$rArray[] = array("commission2",getFORM("xCommission2"),"D");
			$rArray[] = array("commissionType2",getFORM("xCommissionType2"),"S");
			$dbA->insertRecord($tableName,$rArray);
			userLogActionAdd($recordType,$xName);		
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "delete") {
		$xGroupID = getFORM("xGroupID");
		if (!$dbA->doesIDExist($tableName,"groupID",$xGroupID,$uRecord)) {
			setupProcessMessage($recordType,"","error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"groupID",$xGroupID);
			userLogActionDelete($recordType,$uRecord["name"]);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$xGroupID = getFORM("xGroupID");
		$xName = getFORM("xName");
		if (!$dbA->doesIDExist($tableName,"groupID",$xGroupID,$uRecord)) {
			setupProcessMessage($recordType,getFORM("xName"),"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableName,"groupID",$xGroupID,"name",$xName)) {
				setupProcessMessage($recordType,getFORM("xName"),"error_duplicate_update","BACK","");				
			}
			$rArray[] = array("name",getFORM("xName"),"S");
			$rArray[] = array("commission",getFORM("xCommission"),"D");
			$rArray[] = array("commissionType",getFORM("xCommissionType"),"S");
			$rArray[] = array("commission2",getFORM("xCommission2"),"D");
			$rArray[] = array("commissionType2",getFORM("xCommissionType2"),"S");
			$dbA->updateRecord($tableName,"groupID=$xGroupID",$rArray);
			userLogActionUpdate($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}		
	
	}
?>
