<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
		
	$recordType = "Newsletter Email Address";
	$linkBackLink = urldecode(getFORM("xReturn"));
	
	$tableName = $tableNewsletter;
	
	$xAction = getFORM("xAction");
	$xType = getFORM("xType");

	if ($xAction == "insert") {
		$xEmailaddress = getFORM("xEmailaddress");
		if ($dbA->doesRecordExist($tableName,"emailaddress",$xEmailaddress) && $xEmail != "") {
			setupProcessMessage($recordType,$xEmailaddress,"error_duplicate_add","BACK","");
		} else {
			$rArray[] = array("emailaddress",$xEmailaddress,"S");
			$dbA->insertRecord($tableName,$rArray);
			userLogActionAdd($recordType,$xEmailaddress);		
			doRedirect("$linkBackLink&".userSessionGET());
		}
	}
	if ($xAction == "delete") {
		$xRecipientID = getFORM("xRecipientID");
		if (!$dbA->doesIDExist($tableName,"recipientID",$xRecipientID,$uRecord)) {
			setupProcessMessage($recordType,$xRecipientID,"error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"recipientID",$xRecipientID);
			userLogActionDelete($recordType,$uRecord["emailaddress"]);
			doRedirect("$linkBackLink&".userSessionGET());
		}
	}	
	if ($xAction == "update") {
		$xRecipientID = getFORM("xRecipientID");
		$xEmailaddress = chop(getFORM("xEmailaddress"));
		if (!$dbA->doesIDExist($tableName,"recipientID",$xRecipientID,$uRecord)) {
			setupProcessMessage($recordType,$xEmailaddress,"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableName,"recipientID",$xRecipientID,"emailaddress",$xEmailaddress)) {
				setupProcessMessage($recordType,getFORM("xEmailaddress"),"error_duplicate_update","BACK","");				
			}
			$rArray[] = array("emailaddress",$xEmailaddress,"S");
			$dbA->updateRecord($tableName,"recipientID=$xRecipientID",$rArray);
			userLogActionUpdate($recordType,$xEmailaddress);
			doRedirect("$linkBackLink&".userSessionGET());
		}		
	}	
?>