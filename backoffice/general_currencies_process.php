<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$recordType = "Currency";
	$tableName = $tableCurrencies;
	$linkBackButton = "CURRENCY SETTINGS";
	$linkBackLink = "general_currencies.php";

	$xAction=getFORM("xAction");
	if ($xAction == "default") {
		$xCurrencySelect = makeInteger(getFORM("xCurrencySelect"));
		if ($xCurrencySelect > 0) {
			updateOption("defaultCurrency",$xCurrencySelect);
			userLog("Updated Default Currency");
		}
		doRedirect("$linkBackLink?".userSessionGET());
	}
	if ($xAction == "insert") {
		$xCode = strtoupper(getFORM("xCode"));
		if ($dbA->doesRecordExist($tableName,"code",$xCode)) {
			setupProcessMessage($recordType,$xCode,"error_duplicate_add","BACK","");
		} else {
			$rArray[] = array("code",$xCode,"S");			
			$rArray[] = array("isonumber",getFORM("xIsonumber"),"S");
			$rArray[] = array("name",getFORM("xName"),"S");
			$rArray[] = array("decimals",getFORM("xDecimals"),"N");
			$rArray[] = array("pretext",getFORM("xPretext"),"S");
			$rArray[] = array("middletext",getFORM("xMiddletext"),"S");
			$rArray[] = array("posttext",getFORM("xPosttext"),"S");
			$rArray[] = array("useexchangerate",getFORM("xUseexchangerate"),"YN");
			$rArray[] = array("checkout",getFORM("xCheckout"),"YN");
			$rArray[] = array("exchangerate",getFORM("xExchangerate"),"D");
			$rArray[] = array("visible",getFORM("xVisible"),"YN");
			$dbA->insertRecord($tableName,$rArray);
			$currencyID = $dbA->lastID();
			$dbA->query("alter table $tableProducts add column price$currencyID float not null");
			$dbA->query("alter table $tableProducts add column ooPrice$currencyID float not null");
			$dbA->query("alter table $tableProducts add column rrp$currencyID float not null");
			$dbA->query("alter table $tableCartsContents add column price$currencyID float not null");
			$dbA->query("alter table $tableCartsContents add column ooPrice$currencyID float not null");
			$dbA->query("alter table $tableExtraFieldsPrices add column price$currencyID float not null");
			$dbA->query("alter table $tableAdvancedPricing add column price$currencyID float not null");
			$dbA->query("alter table $tableShippingTypes add column baseprice$currencyID float not null");
			$dbA->query("alter table $tableShippingTypes add column lowprice$currencyID float not null");
			$dbA->query("alter table $tableShippingTypes add column highprice$currencyID float not null");
			$dbA->query("alter table $tableShippingRates add column price$currencyID float not null");
			$dbA->query("alter table $tableDiscounts add column compvalue$currencyID float not null");
			$dbA->query("alter table $tableOfferCodes add column level$currencyID float not null");
			userLogActionAdd($recordType,$xCode);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "delete") {
		$xCurrencyID = getFORM("xCurrencyID");
		if (!$dbA->doesIDExist($tableName,"currencyID",$xCurrencyID,$uRecord)) {
			setupProcessMessage($recordType,$xCurrencyID,"error_existance","BACK","");
		} else {
			$currencyID = $uRecord["currencyID"];
			$dbA->deleteRecord($tableName,"currencyID",$xCurrencyID);
			$dbA->query("alter table $tableProducts drop column rrp$currencyID");
			$dbA->query("alter table $tableProducts drop column price$currencyID");
			$dbA->query("alter table $tableProducts drop column ooPrice$currencyID");
			$dbA->query("alter table $tableCartsContents drop column price$currencyID");
			$dbA->query("alter table $tableCartsContents drop column ooPrice$currencyID");
			$dbA->query("alter table $tableExtraFieldsPrices drop column price$currencyID");
			$dbA->query("alter table $tableAdvancedPricing drop column price$currencyID");
			$dbA->query("alter table $tableShippingTypes drop column baseprice$currencyID");
			$dbA->query("alter table $tableShippingTypes drop column lowprice$currencyID");
			$dbA->query("alter table $tableShippingTypes drop column highprice$currencyID");
			$dbA->query("alter table $tableShippingRates drop column price$currencyID");
			$dbA->query("alter table $tableDiscounts drop column compvalue$currencyID");
			$dbA->query("alter table $tableOfferCodes drop column level$currencyID");
			userLogActionDelete($recordType,$uRecord["code"]);
			if ($xCurrencyID == retrieveOption("defaultCurrency")) {
				updateOption("defaultCurrency",1);
			}
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$xCurrencyID = getFORM("xCurrencyID");
		$xCode = getFORM("xCode");
		if (!$dbA->doesIDExist($tableName,"currencyID",$xCurrencyID,$uRecord)) {
			setupProcessMessage($recordType,getFORM("xCode"),"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableName,"currencyID",$xCurrencyID,"code",$xCode)) {
				setupProcessMessage($recordType,getFORM("xCode"),"error_duplicate_update","BACK","");					
			}
			$rArray[] = array("code",$xCode,"S");	
			$rArray[] = array("isonumber",getFORM("xIsonumber"),"S");		
			$rArray[] = array("name",getFORM("xName"),"S");
			$rArray[] = array("decimals",getFORM("xDecimals"),"N");
			$rArray[] = array("pretext",getFORM("xPretext"),"S");
			$rArray[] = array("middletext",getFORM("xMiddletext"),"S");
			$rArray[] = array("posttext",getFORM("xPosttext"),"S");
			$rArray[] = array("useexchangerate",getFORM("xUseexchangerate"),"YN");
			if ($xCurrencyID != 1) {
				$rArray[] = array("checkout",getFORM("xCheckout"),"YN");
				$rArray[] = array("exchangerate",getFORM("xExchangerate"),"D");
				$rArray[] = array("visible",getFORM("xVisible"),"YN");
			}
			$dbA->updateRecord($tableName,"currencyID=$xCurrencyID",$rArray,0);
			userLogActionUpdate($recordType,$xCode);
			doRedirect("$linkBackLink?".userSessionGET());
		}		
	}
	
	if ($xAction == "liveservice") {
		$xLiveService = getFORM("xLiveService");
		switch ($xLiveService) {
			case "off":
				updateOption("currencyLiveRates",$xLiveService);
				updateOption("currencyLiveRatesInfo","");
				break;
			case "Worldpay":
				$ratesInfo = getFORM("xInstallationID")."|".getFORM("xInfoPswd");
				if (retrieveOption("currencyLiveRates") != $xLiveService) {
					updateOption("currencyLastCheck","");
				}
				updateOption("currencyLiveRates",$xLiveService);
				updateOption("currencyLiveRatesInfo",$ratesInfo);
				break;
		}
		userLogActionUpdate("Live Exchange Rate Service",$xLiveService);
		doRedirect("$linkBackLink?".userSessionGET());
	}
?>
