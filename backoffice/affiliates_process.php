<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='AF' order by position");

		
	$recordType = "Affiliate";
	$linkBackLink = urldecode(getFORM("xReturn"));
	
	$xAction = getFORM("xAction");
	$xType = getFORM("xType");

	if ($xAction == "options") {
		updateOption("affiliatesActivated",getFORM("xAffiliatesActivated"));
		updateOption("affiliatesSignupModerated",getFORM("xAffiliatesSignupModerated"));
		updateOption("affiliatesAllow2Tier",getFORM("xAffiliatesAllow2Tier"));
		updateOption("affiliatesMinimumPayment",getFORM("xAffiliatesMinimumPayment"));
		updateOption("affiliatesCookieDays",getFORM("xAffiliatesCookieDays"));
		
		updateOption("affiliatesCreatePayment",getFORM("xAffiliatesCreatePayment"));
		updateOption("affiliatesCreatePaymentStatus",getFORM("xAffiliatesCreatePaymentStatus"));
		
		updateOption("affiliatesPaymentShipping",getFORM("xAffiliatesPaymentShipping"));
		updateOption("affiliatesPaymentTax",getFORM("xAffiliatesPaymentTax"));

		userLog("Updated Affiliate Settings");
		doRedirect("affiliates_settings.php?".userSessionGET());
	}
	if ($xAction == "insert") {
		$xUsername = getFORM("xAffUsername");
		$xPassword = md5(getFORM("xPassword"));
		if ($dbA->doesRecordExist($tableAffiliates,"username",$xUsername)) {
			setupProcessMessage($recordType,$xUsername,"error_duplicate_add","BACK","");
		} else {
			$rArray[] = array("username",$xUsername,"S");
			$rArray[] = array("password",$xPassword,"S");
			$rArray[] = array("date",date("Ymd"),"S");
			$rArray[] = array("status",getFORM("xStatus"),"S");
			$rArray[] = array("groupID",getFORM("xGroupID"),"N");
			for ($f = 0; $f < count($fieldList); $f++) {
				$rArray[] = array($fieldList[$f]["fieldname"],getFORM($fieldList[$f]["fieldname"]),"S");
			}
			$dbA->insertRecord($tableAffiliates,$rArray);
			userLogActionAdd($recordType,$xUsername);		
			doRedirect("affiliates_listing.php?xType=ABC&".userSessionGET());
		}
	}
	if ($xAction == "delete") {
		$xAffiliateID = getFORM("xAffiliateID");
		if (!$dbA->doesIDExist($tableAffiliates,"affiliateID",$xAffiliateID,$uRecord)) {
			setupProcessMessage($recordType,$xAffiliateID,"error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableAffiliates,"affiliateID",$xAffiliateID);
			userLogActionDelete($recordType,$uRecord["username"]);
			doRedirect("$linkBackLink&".userSessionGET());
		}
	}	
	if ($xAction == "update") {
		$xAffiliateID = getFORM("xAffiliateID");
		$xUsername = chop(getFORM("xAffUsername"));
		if (!$dbA->doesIDExist($tableAffiliates,"affiliateID",$xAffiliateID,$uRecord)) {
			setupProcessMessage($recordType,$xEmail,"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableAffiliates,"affiliateID",$xAffiliateID,"username",$xUsername)) {
				setupProcessMessage($recordType,getFORM("xAffUsername"),"error_duplicate_update","BACK","");				
			}
			$xPassword = getFORM("xPassword");
			if ($xPassword != "") {
				$rArray[] = array("password",md5($xPassword),"S");
			}
			$rArray[] = array("username",$xUsername,"S");
			$rArray[] = array("status",getFORM("xStatus"),"S");
			$rArray[] = array("groupID",getFORM("xGroupID"),"N");
			for ($f = 0; $f < count($fieldList); $f++) {
				$rArray[] = array($fieldList[$f]["fieldname"],getFORM($fieldList[$f]["fieldname"]),"S");
			}
			$dbA->updateRecord($tableAffiliates,"affiliateID=$xAffiliateID",$rArray);
			userLogActionUpdate($recordType,$xUsername);
			doRedirect("$linkBackLink&".userSessionGET());
		}		
	}
	if ($xAction == "accept") {
		$xAffiliateID = getFORM("xAffiliateID");
		$result = $dbA->query("update $tableAffiliates set status='L' where affiliateID=$xAffiliateID");
		$result = $dbA->query("select * from $tableAffiliates where affiliateID=$xAffiliateID");
		if ($dbA->count($result) == 1) {
			$affiliateMain = $dbA->fetch($result);
			include("../routines/tSys.php");
			include("../routines/emailOutput.php");
			@sendEmail($affiliateMain["aff_Email"],"","AFFACCEPTED");
			userLogActionUpdate($recordType,$affiliateMain["username"]);
		}
		doRedirect("$linkBackLink&".userSessionGET());
	}
	
	if ($xAction == "decline") {
		$xAffiliateID = getFORM("xAffiliateID");
		$result = $dbA->query("update $tableAffiliates set status='D' where affiliateID=$xAffiliateID");
		$result = $dbA->query("select * from $tableAffiliates where affiliateID=$xAffiliateID");
		if ($dbA->count($result) == 1) {
			$affiliateMain = $dbA->fetch($result);
			include("../routines/tSys.php");
			include("../routines/emailOutput.php");
			@sendEmail($affiliateMain["aff_Email"],"","AFFDECLINED");
			userLogActionUpdate($recordType,$affiliateMain["username"]);
		}
		doRedirect("$linkBackLink&".userSessionGET());
	}
?>
