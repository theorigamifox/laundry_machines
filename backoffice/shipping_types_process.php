<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$recordType = "Shipping Types";
	$tableName = $tableShippingTypes;
	$linkBackLink = "shipping_types.php";

	$xAction=getFORM("xAction");
	
	$languages = $dbA->retrieveAllRecords($tableLanguages,"languageID");
	
	if ($xAction == "insert") {
		$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
		$xName = getFORM("xName");
		if ($dbA->doesRecordExist($tableName,"name",$xName)) {
			setupProcessMessage($recordType,$xName,"error_duplicate_add","BACK","");
		} else {
			$rArray[] = array("name",getFORM("xName"),"S");
			$rArray[] = array("fmType",getFORM("xFmType"),"S");
			$rArray[] = array("calcType",getFORM("xCalcType"),"S");
			$rArray[] = array("position",9999,"N");
			$rArray[] = array("accTypeID",getFORM("xAccTypeID"),"N");
			$rArray[] = array("weight",getFORM("xWeight"),"D");
			$rArray[] = array("lowweight",getFORM("xLowWeight"),"D");
			$rArray[] = array("rounding",getFORM("xRounding"),"YN");
			$rArray[] = array("taxable",getFORM("xTaxable"),"YN");
			for ($f = 0; $f < count($currArray); $f++) {
				if ($currArray[$f]["useexchangerate"] != "Y") {
					$rArray[] = array("baseprice".$currArray[$f]["currencyID"],getFORM("xBasePrice".$currArray[$f]["currencyID"]),"D");
					$rArray[] = array("lowprice".$currArray[$f]["currencyID"],getFORM("xLowprice".$currArray[$f]["currencyID"]),"D");
					$rArray[] = array("highprice".$currArray[$f]["currencyID"],getFORM("xHighprice".$currArray[$f]["currencyID"]),"D");
				}
			}
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
		$xShippingID = getFORM("xShippingID");
		if (!$dbA->doesIDExist($tableName,"shippingID",$xShippingID,$uRecord)) {
			setupProcessMessage($recordType,$xShippingID,"error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"shippingID",$xShippingID);
			$dbA->query("delete from $tableShippingRates where shippingID=$xShippingID");
			userLogActionDelete($recordType,$uRecord["name"]);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
		$xShippingID = getFORM("xShippingID");
		$xName = getFORM("xName");
		if (!$dbA->doesIDExist($tableName,"shippingID",$xShippingID,$uRecord)) {
			setupProcessMessage($recordType,$xName,"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableName,"shippingID",$xShippingID,"name",$xName)) {
				setupProcessMessage($recordType,$xName,"error_duplicate_update","BACK","");					
			}
			$rArray[] = array("name",getFORM("xName"),"S");
			$rArray[] = array("fmType",getFORM("xFmType"),"S");
			$rArray[] = array("calcType",getFORM("xCalcType"),"S");
			$rArray[] = array("accTypeID",getFORM("xAccTypeID"),"N");
			$rArray[] = array("weight",getFORM("xWeight"),"D");
			$rArray[] = array("lowweight",getFORM("xLowWeight"),"D");
			$rArray[] = array("rounding",getFORM("xRounding"),"YN");
			$rArray[] = array("taxable",getFORM("xTaxable"),"YN");
			for ($f = 0; $f < count($currArray); $f++) {
				if ($currArray[$f]["useexchangerate"] != "Y") {
					$rArray[] = array("baseprice".$currArray[$f]["currencyID"],getFORM("xBasePrice".$currArray[$f]["currencyID"]),"D");
					$rArray[] = array("lowprice".$currArray[$f]["currencyID"],getFORM("xLowprice".$currArray[$f]["currencyID"]),"D");
					$rArray[] = array("highprice".$currArray[$f]["currencyID"],getFORM("xHighprice".$currArray[$f]["currencyID"]),"D");
				}
			}
			for ($f = 0; $f < count($languages); $f++) {
				$thisLanguage = $languages[$f]["languageID"];
				if ($thisLanguage != 1) {
					$rArray[] = array("name".$thisLanguage,getFORM("xName".$thisLanguage),"S");			
				}
			}
			$dbA->updateRecord($tableName,"shippingID=$xShippingID",$rArray,0);
			userLogActionUpdate($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}		
	}
	if ($xAction == "reorder") {
		$xNewOrder = getFORM("xNewOrder");
		$newOrderBits = split(";",$xNewOrder);
		for ($f = 0; $f < count($newOrderBits)-1; $f++) {
			$g = $f+1;
			$dbA->query("update $tableShippingTypes set position=$g where shippingID=$newOrderBits[$f]");
		}
		userLogAction("Sorted","Shipping Types","All");
		$linkBackLink = "shipping_types.php?".userSessionGET();
		doRedirect("$linkBackLink?".userSessionGET());
	}	
?>
