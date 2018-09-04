<?php
function retrieveCountry($countryID) {
		global $dbA,$tableCountries;
		$countryID = makeInteger($countryID);
		if ($countryID != "") {
			$result = $dbA->query("select * from $tableCountries where countryID=$countryID");
			if ($dbA->count($result) != 1) {
				return false;
			} else {
				$record = $dbA->fetch($result);
				return $record["name"];
			}
		} else {
			return "";
		}
	}
	
	function retrieveTaxRecord($taxID) {
		global $dbA,$tableTaxRates;
		$result = $dbA->query("select * from $tableTaxRates where taxID=$taxID");
		if ($dbA->count($result) != 1) {
			return false;
		} else {
			$record = $dbA->fetch($result);
			return $record;
		}
	}
	
	function showPricesIncTax() {
		global $cartMain,$accTypeArray;
		for ($f = 0; $f < count($accTypeArray); $f++) {
			if ($cartMain["accTypeID"] == $accTypeArray[$f]["accTypeID"]) {
				if ($accTypeArray[$f]["priceIncTax"] == "Y") {
					return true;
				} else {
					return false;
				}
			}
		}
		return true;
	}
	
	function retrieveTaxRates($thisCountry,$thisCounty,$deliveryCountry="",$deliveryCounty="") {
		global $cartMain,$accTypeArray,$dbA,$tableCountries,$customerMain;
		$taxArray = null;
		$taxArray["countryTaxStandard"] = 0;
		$taxArray["countryTaxSecond"] = 0;		
		if (@$customerMain["taxExempt"] == "Y") {
			return $taxArray;
		}
		if ($thisCountry == "") {
			$thisCountry = retrieveOption("defaultCountry");
		}
		$result = $dbA->query("select * from $tableCountries where countryID = $thisCountry");
		if ($dbA->count($result) != 1) {
			$taxArray["countryTaxStandard"] = 0;
			$taxArray["countryTaxSecond"] = 0;
		} else {
			$record = $dbA->fetch($result);
			$taxArray["countryTaxStandard"] = $record["taxstandard"];
			$taxArray["countryTaxSecond"] = $record["taxsecond"];
		}
		if (retrieveOption("fieldCountyAsSelect") == 1) {
			//ok, so let's see if a state tax rate exists;
			$countyList = split(";",retrieveOption("taxCountiesList"));
			for ($f = 0; $f < count($countyList); $f++) {
				if ($countyList[$f] != "") {
					if ($thisCounty == $countyList[$f]) {
						$taxArray["countryTaxStandard"] = retrieveOption("taxCountiesStandard");
						$taxArray["countryTaxSecond"] = retrieveOption("taxCountiesSecond");
						return $taxArray;
					}
				}
			}
		}
		if (retrieveOption("taxIncludeDeliveryAddress") == 1) {
			$deliveryTaxable = true;
			//check the delivery parts as well
			if ($deliveryCountry != "") {
				$result = $dbA->query("select * from $tableCountries where countryID = $deliveryCountry");
				if ($dbA->count($result) != 1) {
					if ($taxArray["countryTaxStandard"] == 0) {
						$taxArray["countryTaxStandard"] = 0;
						$taxArray["countryTaxSecond"] = 0;
					}
				} else {
					$record = $dbA->fetch($result);
					if ($record["taxstandard"] > 0) {
						$taxArray["countryTaxStandard"] = $record["taxstandard"];
						$taxArray["countryTaxSecond"] = $record["taxsecond"];
						$deliveryTaxable = true;
					} else {
						$deliveryTaxable = false;
					}
				}			
			}
			if ($deliveryCounty != "") {
				if (retrieveOption("fieldCountyAsSelect") == 1) {
					//ok, so let's see if a state tax rate exists;
					$countyList = split(";",retrieveOption("taxCountiesList"));
					for ($f = 0; $f < count($countyList); $f++) {
						if ($countyList[$f] != "") {
							if ($deliveryCounty == $countyList[$f]) {
								$taxArray["countryTaxStandard"] = retrieveOption("taxCountiesStandard");
								$taxArray["countryTaxSecond"] = retrieveOption("taxCountiesSecond");
								$deliveryTaxable = true;
								return $taxArray;
							}
						}
					}
				}			
			}
			if (retrieveOption("taxZeroDelNoTax") == 1 && $deliveryTaxable != true) {
				$taxArray["countryTaxStandard"] = 0;
				$taxArray["countryTaxSecond"] = 0;
			}
		}
		return $taxArray;
	}
	
	function calculateTax($thePrice,$prodRec) {
		global $taxRates,$cartMain;
		$theTax = 0;
		if (retrieveOption("taxEnabled") == 0) {
			return 0;
		}
		switch ($prodRec["taxrate"]) {
			case 1:
				$theTax = ($thePrice / 100) * $taxRates["countryTaxStandard"];
				break;
			case 2:
				$theTax = ($thePrice / 100) * $taxRates["countryTaxSecond"];
				break;
		}
		$theTax = number_format(abs($theTax),2,".","");
		return $theTax;
	}

	function calculateTaxUnrounded($thePrice,$prodRec) {
		global $taxRates,$cartMain;
		$theTax = 0;
		if (retrieveOption("taxEnabled") == 0) {
			return 0;
		}
		switch ($prodRec["taxrate"]) {
			case 1:
				$theTax = ($thePrice / 100) * $taxRates["countryTaxStandard"];
				break;
			case 2:
				$theTax = ($thePrice / 100) * $taxRates["countryTaxSecond"];
				break;
		}
		//$theTax = number_format(abs($theTax),2,".","");
		$theTax = $theTax + 0.00001;
		return $theTax;
	}	
?>