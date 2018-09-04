<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");
	include("../routines/emailOutput.php");
	include("../routines/tSys.php");	

	dbConnect($dbA);

	$recordType = "Offer Code";
	$linkBackLink = urldecode(getFORM("xReturn"));
	
	$tableName = $tableOfferCodes;
	
	$xAction = getFORM("xAction");
	$xType = getFORM("xType");
	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");

	if ($xAction == "insert") {
		$xCode = getFORM("xCode");
		if ($dbA->doesRecordExist($tableName,"code",$xCode) && $xCode != "") {
			setupProcessMessage($recordType,$xCoce,"error_duplicate_add","BACK","");
		} else {
			$rArray[] = array("code",$xCode,"S");
			$rArray[] = array("amount1",getFORM("xAmount"),"S");
			$rArray[] = array("currencyID",makeDecimal(getFORM("xCurrencyID")),"N");
			$rArray[] = array("multiple",getFORM("xMultiple"),"YN");
			$rArray[] = array("excludeShipping",getFORM("xExcludeShipping"),"YN");
			if (getFORM("xExpires") == "N") {
				$rArray[] = array("expiryDate","N","S");
			} else {
				$rArray[] = array("expiryDate",getFORM("xYear").getFORM("xMonth").getFORM("xDay"),"S");
			}
			for ($f = 0; $f < count($currArray); $f++) {
				if ($currArray[$f]["useexchangerate"] != "Y") {
					$rArray[] = array("level".$currArray[$f]["currencyID"],getFORM("xLevel".$currArray[$f]["currencyID"]),"D");
				}
			}
			$dbA->insertRecord($tableName,$rArray);
			userLogActionAdd($recordType,$xCode);
			doRedirect("offercodes_listing.php?xStatus=X&".userSessionGET());		
		}
	}
	if ($xAction == "delete") {
		$xOfferID = getFORM("xOfferID");
		$dbA->query("delete from $tableOfferCodes where offerID='$xOfferID'");;
		userLogActionDelete($recordType,$xCertSerial);
		doRedirect("$linkBackLink&".userSessionGET());
	}	
	if ($xAction == "update") {
		$xOfferID = getFORM("xOfferID");
		$xCode = getFORM("xCode");
		if (!$dbA->doesIDExist($tableName,"offerID",$xOfferID,$uRecord)) {
			setupProcessMessage($recordType,$xCode,"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableName,"offerID",$xOfferID,"code",$xCode) && $xCode != "") {
				setupProcessMessage($recordType,getFORM("xCode"),"error_duplicate_update","BACK","");				
			}
			$rArray[] = array("code",$xCode,"S");
			$rArray[] = array("amount1",getFORM("xAmount"),"S");
			if (getFORM("xExpires") == "N") {
				$rArray[] = array("expiryDate","N","S");
			} else {
				$rArray[] = array("expiryDate",getFORM("xYear").getFORM("xMonth").getFORM("xDay"),"S");
			}
			$rArray[] = array("multiple",getFORM("xMultiple"),"YN");
			$rArray[] = array("excludeShipping",getFORM("xExcludeShipping"),"YN");
			for ($f = 0; $f < count($currArray); $f++) {
				if ($currArray[$f]["useexchangerate"] != "Y") {
					$rArray[] = array("level".$currArray[$f]["currencyID"],getFORM("xLevel".$currArray[$f]["currencyID"]),"D");
				}
			}
			$dbA->updateRecord($tableName,"offerID='$xOfferID'",$rArray);
			userLogActionUpdate($recordType,$xCode);
			doRedirect("$linkBackLink&".userSessionGET());
		}
	}	
?>
