<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$recordType = "Supplier Email Template";
	$tableName = $tableSuppliersEmails;
	$linkBackButton = "SUPPLIER EMAIL TEMPLATES";
	$linkBackLink = "suppliers_emails.php";

	$xAction=getFORM("xAction");
	if ($xAction == "update") {
		$xEmailID=getFORM("xEmailID");
		$rArray[] = array("name",getFORM("xName"),"S");	
		$rArray[] = array("subject",getFORM("xSubject"),"S");			
		$rArray[] = array("message",getFORM("xMessage"),"S");		
		$rArray[] = array("messageHTML",getFORM("xMessageHTML"),"S");									
		$rArray[] = array("replyto",getFORM("xReplyTo"),"S");
		$dbA->updateRecord($tableName,"emailID=$xEmailID",$rArray);
		userLogActionUpdate($recordType,getFORM("xName"));
		doRedirect("$linkBackLink?".userSessionGET());
	}
	if ($xAction == "insert") {
		$rArray[] = array("name",getFORM("xName"),"S");	
		$rArray[] = array("subject",getFORM("xSubject"),"S");			
		$rArray[] = array("message",getFORM("xMessage"),"S");		
		$rArray[] = array("messageHTML",getFORM("xMessageHTML"),"S");									
		$rArray[] = array("replyto",getFORM("xReplyTo"),"S");
		$dbA->insertRecord($tableName,$rArray);
		userLogActionUpdate($recordType,getFORM("xName"));
		doRedirect("$linkBackLink?".userSessionGET());
	}
?>
