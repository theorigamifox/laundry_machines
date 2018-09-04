<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='C' order by position");
		
	$recordType = "Customer";
	$linkBackLink = urldecode(getFORM("xReturn"));
	
	$tableName = $tableCustomers;
	
	$xAction = getFORM("xAction");
	$xType = getFORM("xType");

	if ($xAction == "options") {
		updateOption("customerAccounts",getFORM("xCustomerAccounts"));
		updateOption("autoCustomerLogin",getFORM("xAutoCustomerLogin"));
		updateOption("customerLoginGoAccount",getFORM("xCustomerLoginGoAccount"));
		updateOption("allowShippingAddress",getFORM("xAllowShippingAddress"));
		updateOption("fieldCountyAsSelect",getFORM("xFieldCountyAsSelect"));
		updateOption("customerDefaultAccount",getFORM("xCustomerDefaultAccount"));
		updateOption("minPasswordLength",getFORM("xMinPasswordLength"));
		switch (getFORM("xFieldCountyAsSelect")) {
			case 1:
				$dbA->query("update $tableCustomerFields set fieldtype='SELECT' where fieldname='county'");
				$dbA->query("update $tableCustomerFields set fieldtype='SELECT' where fieldname='deliveryCounty'");
				break;
			case 0:
				$dbA->query("update $tableCustomerFields set fieldtype='TEXT' where fieldname='county'");
				$dbA->query("update $tableCustomerFields set fieldtype='TEXT' where fieldname='deliveryCounty'");
				break;
		}
		userLog("Updated Customer Accounts Settings");
		doRedirect($linkBackLink."&".userSessionGET());
	}
	if ($xAction == "insert") {
		$xEmail = getFORM("xEmail");
		if ($dbA->doesRecordExist($tableName,"email",$xEmail) && $xEmail != "") {
			setupProcessMessage($recordType,$xEmail,"error_duplicate_add","BACK","");
		} else {
			$rArray[] = array("email",$xEmail,"S");
			$rArray[] = array("accTypeID",getFORM("xAccTypeID"),"N");
			$rArray[] = array("password",md5(getFORM("xPassword")),"S");
			$rArray[] = array("taxExempt",getFORM("xTaxExempt"),"YN");	
			$rArray[] = array("date",date("Ymd"),"S");
			for ($f = 0; $f < count($fieldList); $f++) {
				$rArray[] = array($fieldList[$f]["fieldname"],getFORM($fieldList[$f]["fieldname"]),"S");
			}
			$dbA->insertRecord($tableName,$rArray);
			userLogActionAdd($recordType,$xEmail);		
			doRedirect("customers_listing.php?xType=ABC&".userSessionGET());
		}
	}
	if ($xAction == "delete") {
		$xCustomerID = getFORM("xCustomerID");
		if (!$dbA->doesIDExist($tableName,"customerID",$xCustomerID,$uRecord)) {
			setupProcessMessage($recordType,$xCustomerID,"error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"customerID",$xCustomerID);
			userLogActionDelete($recordType,$uRecord["email"]);
			doRedirect("$linkBackLink&".userSessionGET());
		}
	}	
	if ($xAction == "update") {
		$xCustomerID = getFORM("xCustomerID");
		$xEmail = chop(getFORM("xEmail"));
		if (!$dbA->doesIDExist($tableName,"customerID",$xCustomerID,$uRecord)) {
			setupProcessMessage($recordType,$xEmail,"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableName,"customerID",$xCustomerID,"email",$xEmail) && $xEmail != "") {
				setupProcessMessage($recordType,getFORM("xEmail"),"error_duplicate_update","BACK","");				
			}
			$xPassword = getFORM("xPassword");
			if ($xPassword != "") {
				$rArray[] = array("password",md5($xPassword),"S");
			}
			$rArray[] = array("email",$xEmail,"S");
			$rArray[] = array("accTypeID",getFORM("xAccTypeID"),"N");
			$rArray[] = array("taxExempt",getFORM("xTaxExempt"),"YN");	
			for ($f = 0; $f < count($fieldList); $f++) {
				$rArray[] = array($fieldList[$f]["fieldname"],getFORM($fieldList[$f]["fieldname"]),"S");
			}
			$dbA->updateRecord($tableName,"customerID=$xCustomerID",$rArray);
			userLogActionUpdate($recordType,$xEmail);
			doRedirect("$linkBackLink&".userSessionGET());
		}		
	}	
?>
