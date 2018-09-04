<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$recordType = "Shipping Rates";
	$tableName = $tableShippingRates;
	$linkBackLink = "shipping_rates.php?xShippingID=".getFORM("xShippingID");

	$xAction=getFORM("xAction");
	if ($xAction == "insert") {
		$xShippingID = getFORM("xShippingID");
		$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
		$rArray[] = array("shippingID",$xShippingID,"N");
		if (getFORM("xAllOthers") == 1) {
			$rArray[] = array("sfrom",-1,"D");
			$rArray[] = array("sto",-1,"D");
		} else {
			$rArray[] = array("sfrom",getFORM("xFrom"),"D");
			$rArray[] = array("sto",getFORM("xTo"),"D");
		}
		$rArray[] = array("zoneID",getFORM("xZoneID"),"N");
		for ($f = 0; $f < count($currArray); $f++) {
			if ($currArray[$f]["useexchangerate"] != "Y") {
				$rArray[] = array("price".$currArray[$f]["currencyID"],getFORM("xPrice".$currArray[$f]["currencyID"]),"D");
			}
		}
		$dbA->insertRecord($tableName,$rArray,0);
		$xRateID = $dbA->lastID();
		userLogActionAdd($recordType,$xRateID);
		doRedirect("$linkBackLink&".userSessionGET());
	}
	if ($xAction == "delete") {
		$xRateID = getFORM("xRateID");
		$xShippingID = getFORM("xShippingID");
		if (!$dbA->doesIDExist($tableName,"rateID",$xRateID,$uRecord)) {
			setupProcessMessage($recordType,$xRateID,"error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"rateID",$xRateID);
			userLogActionDelete($recordType,$xRateID);
			doRedirect("$linkBackLink&".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
		$xShippingID = getFORM("xShippingID");
		$xRateID = getFORM("xRateID");

		$rArray[] = array("shippingID",$xShippingID,"N");
		if (getFORM("xAllOthers") == 1) {
			$rArray[] = array("sfrom",-1,"D");
			$rArray[] = array("sto",-1,"D");
		} else {
			$rArray[] = array("sfrom",getFORM("xFrom"),"D");
			$rArray[] = array("sto",getFORM("xTo"),"D");
		}
		$rArray[] = array("zoneID",getFORM("xZoneID"),"N");
		for ($f = 0; $f < count($currArray); $f++) {
			if ($currArray[$f]["useexchangerate"] != "Y") {
				$rArray[] = array("price".$currArray[$f]["currencyID"],getFORM("xPrice".$currArray[$f]["currencyID"]),"D");
			}
		}

		$dbA->updateRecord($tableName,"rateID=$xRateID",$rArray,0);
		userLogActionUpdate($recordType,$xRateID);
		doRedirect("$linkBackLink&".userSessionGET());
	}
?>
