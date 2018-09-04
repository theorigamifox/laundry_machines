<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$recordType = "Special Discounts";
	$tableName = $tableDiscounts;
	$linkBackLink = "customers_discounts.php";

	$xAction=getFORM("xAction");
	if ($xAction == "insert") {
		$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
		$rArray[] = array("percent",getFORM("xPercent"),"N");
		$rArray[] = array("name",getFORM("xName"),"S");
		$rArray[] = array("type",getFORM("xDType"),"S");
		$rArray[] = array("accTypes",getFORM("xAccTypes"),"S");
		if (getFORM("xTrigger") == "GOODS") {
			for ($f = 0; $f < count($currArray); $f++) {
				if ($currArray[$f]["useexchangerate"] != "Y") {
					$rArray[] = array("compvalue".$currArray[$f]["currencyID"],getFORM("xCompvalue".$currArray[$f]["currencyID"]),"D");
				}
			}
		} else {
			$rArray[] = array("qty",getFORM("xQty"),"N");
		}
		$dbA->insertRecord($tableName,$rArray,0);
		userLogActionAdd($recordType,getFORM("xName"));
		doRedirect("$linkBackLink?".userSessionGET());
	}
	if ($xAction == "delete") {
		$xDiscountID = getFORM("xDiscountID");
		if (!$dbA->doesIDExist($tableName,"discountID",$xDiscountID,$uRecord)) {
			setupProcessMessage($recordType,$xRateID,"error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"discountID",$xDiscountID);
			userLogActionDelete($recordType,$uRecord["name"]);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
		$xDiscountID = getFORM("xDiscountID");

		$rArray[] = array("percent",getFORM("xPercent"),"N");
		$rArray[] = array("name",getFORM("xName"),"S");
		$rArray[] = array("type",getFORM("xDType"),"S");
		$rArray[] = array("accTypes",getFORM("xAccTypes"),"S");
		if (getFORM("xTrigger") == "GOODS") {
			for ($f = 0; $f < count($currArray); $f++) {
				if ($currArray[$f]["useexchangerate"] != "Y") {
					$rArray[] = array("compvalue".$currArray[$f]["currencyID"],getFORM("xCompvalue".$currArray[$f]["currencyID"]),"D");
				}
			}
			$rArray[] = array("qty",0,"N");
		} else {
			$rArray[] = array("qty",getFORM("xQty"),"N");
			for ($f = 0; $f < count($currArray); $f++) {
				if ($currArray[$f]["useexchangerate"] != "Y") {
					$rArray[] = array("compvalue".$currArray[$f]["currencyID"],0,"D");
				}
			}
		}

		$dbA->updateRecord($tableName,"discountID=$xDiscountID",$rArray,0);
		userLogActionUpdate($recordType,getFORM("xName"));
		doRedirect("$linkBackLink?".userSessionGET());
	}
?>
