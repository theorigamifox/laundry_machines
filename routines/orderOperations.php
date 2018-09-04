<?php
	function retrieveOrderInformation() {
		global $cartMain,$crypt,$teaEncryptionKey;
		$orderInfo = $cartMain["orderInfo"];
		$orderPairs = split("&",$orderInfo);
		$orderArray = null;
		for ($f = 0; $f< count($orderPairs); $f++) {
			$keyValPair = split("=",$orderPairs[$f]);
			if (@$keyValPair[0] != "") {
				@$keyValPair[1] = urlDecode(@$keyValPair[1]);
				$orderArray[$keyValPair[0]] = $keyValPair[1];
			}
		}
		
		$ccEnc = isValidCard(@$orderArray["ccNumber"]);
		$myCounter = 0;
		while ($ccEnc && $myCounter < 20) {
			@$orderArray["ccNumber"] = $crypt->decrypt(base64_decode(@$orderArray["ccNumber"]), $teaEncryptionKey);
			$ccEnc = isValidCard(@$orderArray["ccNumber"]);
			$myCounter++;
		}
		
		$ccEnc = isValidCard(@$orderArray["ccCVV"]);
		$myCounter = 0;
		while ($ccEnc && $myCounter < 20) {
			@$orderArray["ccCVV"] = $crypt->decrypt(base64_decode(@$orderArray["ccCVV"]), $teaEncryptionKey);
			$ccEnc = isValidCard(@$orderArray["ccCVV"]);
			$myCounter++;
		}
				
		return $orderArray;
	}
	
	function commitOrderInformation() {
		global $cartMain,$orderInfoArray,$tableCarts,$dbA,$cartID,$crypt,$teaEncryptionKey;
		$orderString = "";
		if (is_array($orderInfoArray)) {
			if (@$orderInfoArray["ccNumber"] != "" && isValidCard(@$orderInfoArray["ccNumber"]) == false) {
				$orderInfoArray["ccNumber"] = base64_encode($crypt->encrypt($orderInfoArray["ccNumber"], $teaEncryptionKey));
			}
			if (@$orderInfoArray["ccCVV"] != "" && isValidCard(@$orderInfoArray["ccCVV"]) == false) {
				$orderInfoArray["ccCVV"] = base64_encode($crypt->encrypt($orderInfoArray["ccCVV"], $teaEncryptionKey));
			}
			foreach($orderInfoArray as $k=>$v) {
				if ($orderString != "") {
					$orderString .= "&";
				}
				$orderString .= $k."=".urlEncode($v);
			}
		}
		$cartMain["orderInfo"] = $orderString;
		$dbA->query("update $tableCarts set orderInfo=\"$orderString\" where cartID=\"$cartID\"");
		return $orderString;
	}
	
	function checkShippingID($shippingID) {
		global $dbA,$tableShippingTypes,$tableShippingRates,$cartMain,$orderInfoArray,$tableCountries,$tableDiscounts,$tableZones;
		$orderTotals = calculateOrderTotals();
		$goodsCheckTotal = $orderTotals["goodsTotal"];
		$shippingID = makeInteger($shippingID);
		if (retrieveOption("shippingEnabled") == "0") {
			return 0;
		}
		$totalWeight = 0;
		for ($f = 0; $f < count($cartMain["products"]); $f++) {
			$totalWeight = $totalWeight + ($cartMain["products"][$f]["qty"] * $cartMain["products"][$f]["weight"]);
		}
		$accTypeID = @$cartMain["accTypeID"];
		if ($shippingID > 0) {
			$shipResult = $dbA->query("select * from $tableShippingTypes where shippingID=$shippingID");
			if ($dbA->count($shipResult) != 1) {
				$shippingID = 0;
			} else {
				$shipRecord = $dbA->fetch($shipResult);
				$accTypeID = @$cartMain["accTypeID"];
				if ($accTypeID == 0) { $accTypeID = -1; }
				if ($shipRecord["accTypeID"] != 0 && $shipRecord["accTypeID"] != $accTypeID) {
					//this one isn't available for this customer type
					$shipResult = $dbA->query("select * from $tableShippingTypes where (accTypeID = 0 or accTypeID = $accTypeID) order by position,name");
					if ($dbA->count($shipResult) != 0) {
						$shipRecord = $dbA->fetch($shipResult);
						$shippingID = $shipRecord["shippingID"];
					} else {
						$shippingID = 0;
					}
				}
				if ($shipRecord["weight"] != 0 && $shipRecord["weight"] <= $totalWeight) {
					$shippingID = 0;
				}	
				if ($shipRecord["lowweight"] != 0 && $totalWeight < $shipRecord["lowweight"]) {
					$shippingID = 0;
				}		
				$lowprice = calculatePrice($shipRecord["lowprice1"],$shipRecord["lowprice".$cartMain["currencyID"]],$cartMain["currencyID"]);	
				$highprice = calculatePrice($shipRecord["highprice1"],$shipRecord["highprice".$cartMain["currencyID"]],$cartMain["currencyID"]);
				if ($highprice != 0 && $highprice <= $goodsCheckTotal) {
					$shippingID = 0;
				}
				if ($lowprice != 0 && $goodsCheckTotal < $lowprice) {
					$shippingID = 0;
				}
			}
		}
		$countryID = makeInteger(@$orderInfoArray["country"]);
		$deliveryCountryID = makeInteger(@$orderInfoArray["deliveryCountry"]);
		if ($deliveryCountryID != 0 && retrieveOption("allowShippingAddress") == 1) {
			$countryID = $deliveryCountryID;
		}	
		$result = $dbA->query("select * from $tableCountries where countryID=$countryID");
		if ($dbA->count($result) == 0) {
			return 0;
		}
		$countryRecord = $dbA->fetch($result);
		$zoneID = $countryRecord["zoneID"];	
			
		//check for a county zone
		if (retrieveOption("fieldCountyAsSelect") == 1) {
			$theCounty = @$orderInfoArray["county"];
			$theDeliveryCounty = @$orderInfoArray["deliveryCounty"];
			if ($theDeliveryCounty != "") {
				$theCounty = $theDeliveryCounty;
			}
	
			$countyList = $dbA->retrieveAllRecordsFromQuery("select * from $tableZones where countyList like \"%;".$theCounty.";%\"");
			if (count($countyList) > 0) {
				$zoneID = $countyList[0]["zoneID"];
			}
		}
		$priceBit = "";
		if ($cartMain["currency"]["currencyID"] == 1 || $cartMain["currency"]["useexchangerate"] == "N") {
			$priceBit = "and ($tableShippingTypes.highprice".$cartMain["currency"]["currencyID"]." = 0 or $tableShippingTypes.highprice".$cartMain["currency"]["currencyID"]." > $goodsCheckTotal) and ($tableShippingTypes.lowprice".$cartMain["currency"]["currencyID"]." = 0 or $goodsCheckTotal > $tableShippingTypes.lowprice".$cartMain["currency"]["currencyID"].")";
		} else {
			if ($cartMain["currency"]["useexchangerate"] == "Y") {
				$goodsCheckTotalBase = calculatePriceInBase($goodsCheckTotal);
				$priceBit = "and ($tableShippingTypes.highprice1 = 0 or $tableShippingTypes.highprice1 > $goodsCheckTotalBase) and ($tableShippingTypes.lowprice1 = 0 or $goodsCheckTotalBase > $tableShippingTypes.lowprice1)";
			}
		}

		$shippingArray = $dbA->retrieveAllRecordsFromQuery("select $tableShippingRates.*,$tableShippingTypes.weight from $tableShippingRates,$tableShippingTypes where (accTypeID = 0 or accTypeID = $accTypeID) $priceBit and ($tableShippingTypes.weight = 0 or $tableShippingTypes.weight > $totalWeight) and ($tableShippingTypes.lowweight = 0 or $totalWeight > $tableShippingTypes.lowweight) and $tableShippingRates.shippingID=$tableShippingTypes.shippingID and  $tableShippingTypes.shippingID=$shippingID and $tableShippingRates.zoneID=$zoneID order by $tableShippingRates.sfrom");
		if (count($shippingArray) == 0) {
			//this ID isn't available for this country so we need to get one that is!
			$shippingArray = $dbA->retrieveAllRecordsFromQuery("select $tableShippingRates.*,$tableShippingTypes.weight from $tableShippingRates,$tableShippingTypes where (accTypeID = 0 or accTypeID = $accTypeID) $priceBit and ($tableShippingTypes.weight = 0 or $tableShippingTypes.weight > $totalWeight) and ($tableShippingTypes.lowweight = 0 or $totalWeight > $tableShippingTypes.lowweight) and $tableShippingRates.shippingID=$tableShippingTypes.shippingID and $tableShippingRates.zoneID=$zoneID order by $tableShippingRates.sfrom");
			if (count($shippingArray) == 0) {
				return 0;
			} else {
				return $shippingArray[0]["shippingID"];
			}
		} else {
			return $shippingID;
		}
		return 0;
	}
	
	function calculateShipping($shippingID,$shippingTotalGoods,$shippingTotalWeight,$shippingTotalQuantity,$goodsTotal=0) {
		global $dbA,$tableShippingTypes,$tableShippingRates,$cartMain,$orderInfoArray,$tableCountries,$tableDiscounts,$tableZones;
		if (retrieveOption("shippingEnabled") == "0") {
			return 0;
		}
		if ($shippingID == 0 || $shippingID == "") {
			return 0;
		}
		$totalQuantity = 0;
		

		// WEST63RD MODIFICATION
		$surchargeflag = 0;
		// END WEST63RD MODIFICATION


		if (is_array($cartMain["products"])) {
			for ($f = 0; $f < count($cartMain["products"]); $f++) {
				
				// WEST63RD MODIFICATION
				
				// CHECK FOR SURCHARGE
				$SurchargeEnabled = $cartMain["products"][$f]["extrafield2"];

				
				// IF THERE IS SOMETHING IN THE SURCHARGE FIELD, SET FLAG TO 1
				if($SurchargeEnabled != "") {
					$surchargeflag = 1;
				}
				// END CHECK FOR SURCHARGE

				// END WEST63RD MODIFICATION

				
				$totalQuantity = $totalQuantity + $cartMain["products"][$f]["qty"];
			}
		}
		$result = $dbA->query("select * from $tableDiscounts where type='S' and (accTypes=';0;' or accTypes=';".$cartMain["accTypeID"].";') order by compvalue1 desc");
		$count = $dbA->count($result);
		$discountPercent = 0;
		if ($count > 0) {
			for ($f = 0; $f < $count; $f++) {
				$record = $dbA->fetch($result);
				if ($record["compvalue1"] > 0) {
					//This is a goods total trigger
					$compvalue = calculatePrice($record["compvalue1"],$record["compvalue".$cartMain["currencyID"]],$cartMain["currencyID"]);
					if ($compvalue < $goodsTotal) {
						//this one is accurate
						//calculated with a percentage
						$discountPercent = $record["percent"];
					}
				} else {
					//This is a quantity total trigger
					$compvalue = $record["qty"];
					if ($totalQuantity > $compvalue) {
						//this one is accurate
						//calculated with a percentage
						$discountPercent = $record["percent"];
					}
				}
			}
		}

		$result = $dbA->query("select * from $tableShippingTypes where shippingID = $shippingID");
		if ($dbA->count($result) > 0) {
			$shippingType = $dbA->fetch($result);
		} else {
			return 0;
		}
		$totalForShipping = 0;
		switch ($shippingType["calcType"]) {
			case "T":
				$totalForShipping = $shippingTotalGoods;
				break;
			case "W":
				$totalForShipping = $shippingTotalWeight;
				break;
			case "Q":
				$totalForShipping = $shippingTotalQuantity;
				break;
		}
		if ($shippingType["rounding"] == "Y") {
			$spareTotal = intval($totalForShipping);
			if ($spareTotal < $totalForShipping) {
				$spareTotal++;
			}
			$totalForShipping = $spareTotal;
		}
		$fmType = $shippingType["fmType"];
		$baseprice = calculatePrice($shippingType["baseprice1"],$shippingType["baseprice".$cartMain["currencyID"]],$cartMain["currencyID"]);
		//got to work out the zone ID here.
		$countryID = makeInteger(@$orderInfoArray["country"]);
		$deliveryCountryID = makeInteger(@$orderInfoArray["deliveryCountry"]);
		if ($deliveryCountryID != 0) {
			$countryID = $deliveryCountryID;
		}
		$result = $dbA->query("select * from $tableCountries where countryID=$countryID");
		if ($dbA->count($result) == 0) {
			return 0;
		}
		$countryRecord = $dbA->fetch($result);
		$zoneID = $countryRecord["zoneID"];
		
		//check for a county zone
		if (retrieveOption("fieldCountyAsSelect") == 1) {
			$theCounty = @$orderInfoArray["county"];
			$theDeliveryCounty = @$orderInfoArray["deliveryCounty"];
			if ($theDeliveryCounty != "") {
				$theCounty = $theDeliveryCounty;
			}
	
			$countyList = $dbA->retrieveAllRecordsFromQuery("select * from $tableZones where countyList like \"%;".$theCounty.";%\"");
			if (count($countyList) > 0) {
				$zoneID = $countyList[0]["zoneID"];
			}
		}
		$shippingArray = $dbA->retrieveAllRecordsFromQuery("select * from $tableShippingRates where shippingID=$shippingID and zoneID=$zoneID order by $tableShippingRates.sfrom");
		$activeRow = -1;
		for ($f = 0; $f < count($shippingArray); $f++) {
			if (($totalForShipping >= $shippingArray[$f]["sfrom"] && $totalForShipping <= $shippingArray[$f]["sto"]) || $shippingArray[$f]["sfrom"] == -1) { // -1 = all/others
				$activeRow = $f;
			}
		}
		if ($activeRow != -1 && $totalForShipping != 0) {
			$thisPrice = calculatePriceNoFormat($shippingArray[$activeRow]["price1"],$shippingArray[$activeRow]["price".$cartMain["currencyID"]],$cartMain["currencyID"]);
			if ($fmType == "F") {
				$baseprice = $baseprice + $thisPrice;
			} else {
				//some sort of calculation needs to be done here?
				$baseprice = $baseprice + ($thisPrice * $totalForShipping);
			}
		}
		if ($discountPercent > 100) {
			$discountPercent = 100;
		}
		if ($discountPercent > 0) {
			return $baseprice * (1-($discountPercent/100));
		} else {

//******* WEST63RD MODIFICATION *******//
			if ($surchargeflag == 1) 
			{
				$baseprice += 5.45;
			}
//******* WEST63RD MODIFICATION *******//
			
			return $baseprice;
		}
	}

		function calculateOrderTotals($inOrderEditing = FALSE,$currentValues = FALSE) {
			global $orderInfoArray,$cartMain,$taxRates,$tableShippingTypes,$dbA;
			$goodsTotal = 0;
			$shippingTotal = 0;
			$taxTotal = 0;
			$shippingTotalGoods = 0;
			$shippingTotalWeight = 0;
			$shippingTotalQty = 0;
			$taxRates = retrieveTaxRates($orderInfoArray["country"],@$orderInfoArray["county"],@$orderInfoArray["deliveryCountry"],@$orderInfoArray["deliveryCounty"]);	
			for ($f = 0; $f < count(@$cartMain["products"]); $f++) {
				$thePrice = $cartMain["products"][$f]["price".$cartMain["currencyID"]];
				$thePriceRounded = roundWithoutCalcPrice($thePrice);
				$theOOPrice = $cartMain["products"][$f]["ooPrice".$cartMain["currencyID"]];
				$theQty = $cartMain["products"][$f]["qty"];
				$goodsTotal = $goodsTotal + ($thePriceRounded * $theQty);
				$goodsTotal = $goodsTotal + $theOOPrice;
				if ($cartMain["products"][$f]["freeShipping"] == "N") {
					$shippingTotalGoods = $shippingTotalGoods + ($thePriceRounded * $theQty);
					$shippingTotalGoods = $shippingTotalGoods + $theOOPrice;
					$shippingTotalWeight = $shippingTotalWeight + ($cartMain["products"][$f]["weight"]*$theQty);
					$shippingTotalQty = $shippingTotalQty + $theQty;
				}
				$theTax = calculateTax($thePrice,$cartMain["products"][$f]);
				$theOOTax = calculateTax($theOOPrice,$cartMain["products"][$f]);
				$taxTotal = $taxTotal + ($theTax*$theQty);
				$taxTotal = $taxTotal + $theOOTax;
			}
			$shippingTotal = roundWithoutCalcPrice(calculateShipping(@$orderInfoArray["shippingID"],$shippingTotalGoods,$shippingTotalWeight,$shippingTotalQty,$goodsTotal));
			if ($inOrderEditing) {
				if (is_array($currentValues)) {
					if (@$orderInfoArray["shippingLock"] == "Y") {
						$shippingTotal = @$currentValues["shippingTotal"];
					}
				}
			}
	
			$discountPercent = calculateDiscount($goodsTotal);
	
			if ($discountPercent > 0) {
				$discountAmount = $goodsTotal * ($discountPercent/100);
				$discountTax = $taxTotal * ($discountPercent/100);
				$cartMain["totals"]["isDiscount"] = "Y";
			} else {
				$discountAmount = 0;
				$discountTax = 0;
				$cartMain["totals"]["isDiscount"] = "N";
			}	
			
			$taxTotal = $taxTotal - $discountTax;
			$shippingTax = 0;
			if (retrieveOption("taxOnShipping") == 1) {
				if (@$orderInfoArray["shippingID"] != "") {
					$sResult = $dbA->query("select taxable from $tableShippingTypes where shippingID=".@$orderInfoArray["shippingID"]);
					if ($dbA->count($sResult) > 0) {
						$sRecord = $dbA->fetch($sResult);
						if ($sRecord["taxable"] == "Y") {
							$shippingTax = calculateTax($shippingTotal,array("taxrate"=>1));
							$taxTotal = $taxTotal + $shippingTax;
						}
					}
				}
			}
			$taxTotal = number_format($taxTotal,2,".","");
			$orderTotals["goodsTotal"] = $goodsTotal;
			$orderTotals["shippingTotal"] = $shippingTotal;
			$orderTotals["shippingTax"] = $shippingTotal;
			$orderTotals["taxTotal"] = $taxTotal;
			$orderTotals["shippingTotalGoods"] = $shippingTotalGoods;
			$orderTotals["shippingTotalWeight"] = $shippingTotalWeight;
			$orderTotals["shippingTotalQty"] = $shippingTotalQty;
			$orderTotals["discountAmount"] = $discountAmount;
			if ($inOrderEditing) {
				if (is_array($currentValues)) {
					if (@$orderInfoArray["discountLock"] == "Y") {
						$orderTotals["discountAmount"] = @$currentValues["discountTotal"];
					}
				}
			}
			$orderTotals["orderTotal"] = ($goodsTotal+$shippingTotal+$taxTotal)-$discountAmount;
			//calculate offer codes here
			$orderTotals["offerTotal"] = roundWithoutCalcPrice(calculateOfferCodeTotal($orderTotals));
			if ($orderTotals["offerTotal"] > $orderTotals["orderTotal"]) {
				$orderTotals["offerTotal"] = $orderTotals["orderTotal"];
			}
			if ($inOrderEditing) {
				if (is_array($currentValues)) {
					if (@$orderInfoArray["discountLock"] == "Y") {
						$orderTotals["offerTotal"] = 0;
					}
				}
			}
			$orderTotals["discountAmount"] += $orderTotals["offerTotal"];
			if ($inOrderEditing) {
				if (is_array($currentValues)) {
					if (@$orderInfoArray["discountLock"] == "Y") {
						$shippingTotal = @$currentValues["shippingTotal"];
					}
				}
			}
			$giftCertTotal = calculateGiftCertTotal(@$orderInfoArray["giftCerts"]);	
			if ($giftCertTotal > $orderTotals["orderTotal"]) {
				$giftCertTotal = $orderTotals["orderTotal"];
			}
			$orderTotals["giftCertTotal"] = $giftCertTotal;
			return $orderTotals;
		}
		
		function checkCheckoutCurrency() {
			global $cartMain,$dbA,$currArray,$tableCarts;
			if ($cartMain["currency"]["checkout"] == "N") {
				$dbA->query("update $tableCarts set currencyID=1");
				$cartMain["currencyID"] = 1;
				for ($f = 0; $f < count($currArray); $f++) {
					if ($cartMain["currencyID"] == $currArray[$f]["currencyID"]) {
						$cartMain["currency"]["currencyID"] = $currArray[$f]["currencyID"];
						$cartMain["currency"]["code"] = $currArray[$f]["code"];
						$cartMain["currency"]["name"] = $currArray[$f]["name"];
						$cartMain["currency"]["decimals"] = $currArray[$f]["decimals"];
						$cartMain["currency"]["pretext"] = $currArray[$f]["pretext"];
						$cartMain["currency"]["middletext"] = $currArray[$f]["middletext"];
						$cartMain["currency"]["posttext"] = $currArray[$f]["posttext"];
						$cartMain["currency"]["useexchangerate"] = $currArray[$f]["useexchangerate"];
						$cartMain["currency"]["exchangerate"] = $currArray[$f]["exchangerate"];
						$cartMain["currency"]["checkout"] = $currArray[$f]["checkout"];
					}
				}			
			}		
		}
?>