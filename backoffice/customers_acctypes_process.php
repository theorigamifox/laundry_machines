<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$recordType = "Customer Account Type";
	$tableName = $tableCustomersAccTypes;
	$linkBackButton = "CUSTOMER ACCOUNT TYPES";
	$linkBackLink = "customers_acctypes.php";

	$xAction=getFORM("xAction");
	if ($xAction == "insert") {
		$xName = getFORM("xName");
		if ($dbA->doesRecordExist($tableName,"name",$xName)) {
			setupProcessMessage($recordType,$xName,"error_duplicate_add","BACK","");
		} else {
			$rArray[] = array("name",$xName,"S");			
			$rArray[] = array("description",getFORM("xDescription"),"S");
			$rArray[] = array("priceIncTax",getFORM("xPriceIncTax"),"YN");
			$rArray[] = array("defaultDiscount",getFORM("xDefaultDiscount"),"D");
			$rArray[] = array("allowShippingAddress",getFORM("xAllowShippingAddress"),"YN");
			$dbA->insertRecord($tableName,$rArray);
			userLogActionAdd($recordType,$xName);		
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "delete") {
		$xAccTypeID = getFORM("xAccTypeID");
		if (!$dbA->doesIDExist($tableName,"accTypeID",$xAccTypeID,$uRecord)) {
			setupProcessMessage($recordType,"","error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"accTypeID",$xAccTypeID);
			userLogActionDelete($recordType,$uRecord["name"]);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$xAccTypeID = getFORM("xAccTypeID");
		$xName = getFORM("xName");
		if (!$dbA->doesIDExist($tableName,"accTypeID",$xAccTypeID,$uRecord)) {
			setupProcessMessage($recordType,getFORM("xName"),"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableName,"accTypeID",$xAccTypeID,"name",$xName)) {
				setupProcessMessage($recordType,getFORM("xName"),"error_duplicate_update","BACK","");				
			}
		
			$rArray[] = array("name",getFORM("xName"),"S");
			$rArray[] = array("description",getFORM("xDescription"),"S");
			$rArray[] = array("priceIncTax",getFORM("xPriceIncTax"),"YN");
			$rArray[] = array("defaultDiscount",getFORM("xDefaultDiscount"),"D");
			$rArray[] = array("allowShippingAddress",getFORM("xAllowShippingAddress"),"YN");
			$dbA->updateRecord($tableName,"accTypeID=$xAccTypeID",$rArray);
			userLogActionUpdate($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}		
	}
?>
