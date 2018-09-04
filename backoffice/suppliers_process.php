<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='SU' order by position");
		
	$recordType = "Supplier";
	$linkBackLink = urldecode(getFORM("xReturn"));
	
	$tableName = $tableSuppliers;
	
	$xAction = getFORM("xAction");
	$xType = getFORM("xType");

	if ($xAction == "options") {
		updateOption("suppliersEnabled",getFORM("xSuppliersEnabled"));
		updateOption("suppliersEmailTiming",getFORM("xSuppliersEmailTiming"));
		userLog("Updated Supplier Settings");
		doRedirect($linkBackLink."&".userSessionGET());
	}
	if ($xAction == "insert") {
		$rArray[] = array("emailSupplier",getFORM("xEmailSupplier"),"YN");
		$rArray[] = array("emailID",makeInteger(getFORM("xEmailID")),"N");
		$rArray[] = array("notifyEmail",getFORM("xNotifyEmail"),"S");
		$rArray[] = array("accnum",getFORM("xAccnum"),"S");
		for ($f = 0; $f < count($fieldList); $f++) {
			$rArray[] = array($fieldList[$f]["fieldname"],getFORM($fieldList[$f]["fieldname"]),"S");
		}
		$dbA->insertRecord($tableSuppliers,$rArray);
		userLogActionAdd($recordType,$xUsername);		
		doRedirect("suppliers_listing.php?xType=ABC&".userSessionGET());
	}
	if ($xAction == "delete") {
		$xSupplierID = getFORM("xSupplierID");
		if (!$dbA->doesIDExist($tableSuppliers,"supplierID",$xSupplierID,$uRecord)) {
			setupProcessMessage($recordType,$xSupplierID,"error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableSuppliers,"supplierID",$xSupplierID);
			userLogActionDelete($recordType,@$uRecord["sup_company"]);
			doRedirect("$linkBackLink&".userSessionGET());
		}
	}	
	if ($xAction == "update") {
		$xSupplierID=getFORM("xSupplierID");
		$rArray[] = array("emailSupplier",getFORM("xEmailSupplier"),"YN");
		$rArray[] = array("emailID",makeInteger(getFORM("xEmailID")),"N");
		$rArray[] = array("notifyEmail",getFORM("xNotifyEmail"),"S");
		$rArray[] = array("accnum",getFORM("xAccnum"),"S");
		for ($f = 0; $f < count($fieldList); $f++) {
			$rArray[] = array($fieldList[$f]["fieldname"],getFORM($fieldList[$f]["fieldname"]),"S");
		}
		$dbA->updateRecord($tableSuppliers,"supplierID=$xSupplierID",$rArray);
		userLogActionUpdate($recordType,getFORM("sup_company"));
		doRedirect("$linkBackLink&".userSessionGET());
	}	
?>
