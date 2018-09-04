<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$recordType = "User";
	$linkBackLink = urldecode(getFORM("xReturn"));
	
	$xAction = getFORM("xAction");
	$xType = getFORM("xType");

	if ($xAction == "options") {
		updateOption("taxEnabled",getFORM("xTaxEnabled"));
		updateOption("taxOnShipping",getFORM("xTaxOnShipping"));
		updateOption("taxIncludeDeliveryAddress",getFORM("xTaxIncludeDeliveryAddress"));
		updateOption("taxZeroDelNoTax",getFORM("xTaxZeroDelNoTax"));
		userLog("Updated Tax Settings");
		doRedirect($linkBackLink."&".userSessionGET());
	}
	if ($xAction == "countries") {
		$xTaxStandard = makeDecimal(getFORM("xTaxStandard"));
		$xTaxSecond = makeDecimal(getFORM("xTaxSecond"));
		$xCountryList = getFORM("xCountryList");
		$xCountryDeletedList = getFORM("xCountryDeletedList");
		$delList = split(";",$xCountryDeletedList);
		for ($f = 0; $f < count($delList); $f++) {
			if ($delList[$f] != "") {
				$dbA->query("update $tableCountries set taxstandard=0, taxsecond=0 where countryID=".$delList[$f]);
			}
		}
		$delList = split(";",$xCountryList);
		for ($f = 0; $f < count($delList); $f++) {
			if ($delList[$f] != "") {
				$dbA->query("update $tableCountries set taxstandard=$xTaxStandard, taxsecond=$xTaxSecond where countryID=".$delList[$f]);
			}
		}
		userLog("Updated Country Level Tax Settings");
		doRedirect($linkBackLink."&".userSessionGET());
	}
	if ($xAction == "counties") {
		$xTaxStandard = makeDecimal(getFORM("xTaxStandard"));
		$xTaxSecond = makeDecimal(getFORM("xTaxSecond"));		
		$xCountyList = getFORM("xCountyList");
		updateOption("taxCountiesStandard",$xTaxStandard);
		updateOption("taxCountiesSecond",$xTaxSecond);
		updateOption("taxCountiesList",$xCountyList);
		userLog("Updated County/State Level Tax Settings");
		doRedirect($linkBackLink."&".userSessionGET());
	}
?>
