<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

		
	$recordType = "Affiliate Transaction";
	$linkBackLink = urldecode(getFORM("xReturn"));
	
	$xAction = getFORM("xAction");
	$xType = getFORM("xType");

	if ($xAction == "insert") {
		$rArray[] = array("affiliateID",getFORM("xAffiliateID"),"S");
		$rArray[] = array("reference",getFORM("xReference"),"S");
		$rArray[] = array("type",getFORM("xType"),"S");
		$rArray[] = array("status",getFORM("xStatus"),"S");
		$rArray[] = array("amount",makeDecimal(getFORM("xAmount")),"D");
		$rArray[] = array("datetime",date("YmdHis"),"S");
		$dbA->insertRecord($tableAffiliatesTrans,$rArray);
		userLogActionAdd($recordType,$xUsername);		
		doRedirect("affiliates_trans_listing.php?xType=DATE&".userSessionGET());
	}
	if ($xAction == "delete") {
		$xTransID = getFORM("xTransID");
		if (!$dbA->doesIDExist($tableAffiliatesTrans,"transID",$xTransID,$uRecord)) {
			setupProcessMessage($recordType,$xTransID,"error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableAffiliatesTrans,"transID",$xTransID);
			userLogActionDelete($recordType,$uRecord["transID"]);
			doRedirect("$linkBackLink&".userSessionGET());
		}
	}	
	if ($xAction == "auth") {
		$xTransID = getFORM("xTransID");
		if (!$dbA->doesIDExist($tableAffiliatesTrans,"transID",$xTransID,$uRecord)) {
			setupProcessMessage($recordType,$xTransID,"error_existance","BACK","");
		} else {
			$dbA->query("update $tableAffiliatesTrans set status='1' where transID=$xTransID");
			userLogActionUpdate($recordType,$uRecord["transID"]);
			doRedirect("$linkBackLink&".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$xTransID = getFORM("xTransID");
		if (!$dbA->doesIDExist($tableAffiliatesTrans,"transID",$xTransID,$uRecord)) {
			setupProcessMessage($recordType,$xTransID,"error_existance","BACK","");	
		} else {
			$rArray[] = array("reference",getFORM("xReference"),"S");
			$rArray[] = array("type",getFORM("xType"),"S");
			$rArray[] = array("status",getFORM("xStatus"),"S");
			$rArray[] = array("amount",makeDecimal(getFORM("xAmount")),"D");
			$dbA->updateRecord($tableAffiliatesTrans,"transID=$xTransID",$rArray);
			userLogActionUpdate($recordType,$xTransID);
			doRedirect("$linkBackLink&".userSessionGET());
		}		
	}	
	if ($xAction == "payment") {
		$xAffiliateID = getFORM("xAffiliateID");
		$rArray[] = array("affiliateID",getFORM("xAffiliateID"),"S");
		$rArray[] = array("reference","Payment","S");
		$rArray[] = array("type","P","S");
		$rArray[] = array("status","1","S");
		$rArray[] = array("amount",makeDecimal(getFORM("xAmount")),"D");
		$rArray[] = array("datetime",date("YmdHis"),"S");
		$dbA->insertRecord($tableAffiliatesTrans,$rArray);	
		$result = $dbA->query("select * from $tableAffiliates where affiliateID=$xAffiliateID");
		if ($dbA->count($result) == 1) {
			$affiliateMain = $dbA->fetch($result);
			include("../routines/tSys.php");
			include("../routines/emailOutput.php");
			$affiliateMain["payment"]["reference"] = "Payment";
			$affiliateMain["payment"]["amount"] = makeDecimal(getFORM("xAmount"));
			sendEmail($affiliateMain["aff_Email"],"","AFFPAYMENT");
		}	
		userLogActionUpdate("Affiliate Payment",$xAffiliateID." paid");
		doRedirect("$linkBackLink&".userSessionGET());
	}
?>
