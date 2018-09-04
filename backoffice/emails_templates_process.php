<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$recordType = "Email Template";
	$tableName = $tableEmails;
	$linkBackButton = "EMAIL TEMPLATES";
	$linkBackLink = "emails_templates.php";

	$xAction=getFORM("xAction");
	if ($xAction == "update") {
		$xTemplate=getFORM("xTemplate");
		$rArray[] = array("subject",getFORM("xSubject"),"S");			
		$rArray[] = array("message",getFORM("xMessage"),"S");		
		$rArray[] = array("messageHTML",getFORM("xMessageHTML"),"S");									
		$rArray[] = array("recipient",getFORM("xRecipient"),"S");
		$rArray[] = array("replyto",getFORM("xReplyTo"),"S");
		$rArray[] = array("activated",getFORM("xActivated"),"YN");
		$dbA->updateRecord($tableName,"template=\"$xTemplate\"",$rArray);
		userLogActionUpdate($recordType,$xTemplate);
		doRedirect("$linkBackLink?".userSessionGET());
	}
?>
