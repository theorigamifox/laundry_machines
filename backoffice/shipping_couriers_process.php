<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$recordType = "Courier";
	$tableName = $tableCouriers;
	$linkBackButton = "COURIER SETTINGS";
	$linkBackLink = "shipping_couriers.php";

	$xAction=getFORM("xAction");
	if ($xAction == "insert") {
		$xName = getFORM("xName");
		if ($dbA->doesRecordExist($tableName,"name",$xName)) {
			setupProcessMessage($recordType,$xName,"error_duplicate_add","BACK","");
		} else {	
			$rArray[] = array("name",getFORM("xName"),"S");
			$rArray[] = array("contactInfo",getFORM("xContactInfo"),"S");
			$dbA->insertRecord($tableName,$rArray);
			userLogActionAdd($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "delete") {
		$xCourierID = getFORM("xCourierID");
		if (!$dbA->doesIDExist($tableName,"courierID",$xCourierID,$uRecord)) {
			setupProcessMessage($recordType,$xCourierID,"error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"courierID",$xCourierID);
			userLogActionDelete($recordType,$uRecord["name"]);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$xCourierID = getFORM("xCourierID");
		$xName = getFORM("xName");
		if (!$dbA->doesIDExist($tableName,"courierID",$xCourierID,$uRecord)) {
			setupProcessMessage($recordType,$xName,"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableName,"courierID",$xCourierID,"name",$xName)) {
				setupProcessMessage($recordType,$xName,"error_duplicate_update","BACK","");					
			}
			$rArray[] = array("name",$xName,"S");
			$rArray[] = array("contactInfo",getFORM("xContactInfo"),"S");
			$dbA->updateRecord($tableName,"courierID=$xCourierID",$rArray,0);
			userLogActionUpdate($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}		
	}
?>
