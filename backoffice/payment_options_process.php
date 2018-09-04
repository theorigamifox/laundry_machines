<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$recordType = "Payment Options";
	$tableName = $tablePaymentOptions;
	$linkBackButton = "PAYMENT OPTIONS";
	$linkBackLink = "payment_options.php";

	$xAction=getFORM("xAction");
	
	$languages = $dbA->retrieveAllRecords($tableLanguages,"languageID");
		
	if ($xAction == "insert") {
		$xName = getFORM("xName");
		if ($dbA->doesRecordExist($tableName,"name",$xName)) {
			setupProcessMessage($recordType,$xName,"error_duplicate_add","BACK","");
		} else {
			$rArray[] = array("name",getFORM("xName"),"S");
			$rArray[] = array("enabled",getFORM("xEnabled"),"YN");
			$rArray[] = array("type","OFFLINE","S");
			$rArray[] = array("position",9999,"S");
			$rArray[] = array("fulledit","Y","S");
			$rArray[] = array("accTypes",getFORM("xAccTypes"),"S");
			$rArray[] = array("custConfirmation",getFORM("xCustConfirmation"),"N");
			for ($f = 0; $f < count($languages); $f++) {
				$thisLanguage = $languages[$f]["languageID"];
				if ($thisLanguage != 1) {
					$rArray[] = array("name".$thisLanguage,getFORM("xName".$thisLanguage),"S");			
				}
			}
			$dbA->insertRecord($tableName,$rArray);
			userLogActionAdd($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "delete") {
		$xPaymentID = getFORM("xPaymentID");
		if (!$dbA->doesIDExist($tableName,"paymentID",$xPaymentID,$uRecord)) {
			setupProcessMessage($recordType,$xPaymentID,"error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"paymentID",$xPaymentID);
			userLogActionDelete($recordType,$uRecord["name"]);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$xPaymentID = getFORM("xPaymentID");
		$xName = getFORM("xName");
		if (!$dbA->doesIDExist($tableName,"paymentID",$xPaymentID,$uRecord)) {
			setupProcessMessage($recordType,$xName,"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableName,"paymentID",$xPaymentID,"name",$xName)) {
				setupProcessMessage($recordType,$xName,"error_duplicate_update","BACK","");					
			}
			$rArray[] = array("name",getFORM("xName"),"S");
			$rArray[] = array("enabled",getFORM("xEnabled"),"YN");
			$rArray[] = array("accTypes",getFORM("xAccTypes"),"S");
			$rArray[] = array("custConfirmation",getFORM("xCustConfirmation"),"N");
			for ($f = 0; $f < count($languages); $f++) {
				$thisLanguage = $languages[$f]["languageID"];
				if ($thisLanguage != 1) {
					$rArray[] = array("name".$thisLanguage,getFORM("xName".$thisLanguage),"S");			
				}
			}
			
			if ($uRecord["type"] == "CC") {
				$rArray[] = array("gateway",getFORM("xGateway"),"S");
			}

			$dbA->updateRecord($tableName,"paymentID=$xPaymentID",$rArray,0);
			userLogActionUpdate($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}		
	}
	if ($xAction == "reorder") {
		$xNewOrder = getFORM("xNewOrder");
		$newOrderBits = split(";",$xNewOrder);
		for ($f = 0; $f < count($newOrderBits)-1; $f++) {
			$g = $f+1;
			$dbA->query("update $tablePaymentOptions set position=$g where paymentID=$newOrderBits[$f]");
		}
		userLogAction("Sorted","Payment Options","All");
		doRedirect("$linkBackLink?".userSessionGET());
	}	
?>
