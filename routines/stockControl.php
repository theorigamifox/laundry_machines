<?php
	$stockArray = "";
	
	function validateStockQty($productID,$qty,$stockFields="") {	
		global $dbA,$tableProducts,$tableExtraFields,$tableCombinations,$stockArray;
		if (retrieveOption("featureStockControl") == 0) { return $qty; }
		$result = $dbA->query("select * from $tableProducts where productID=$productID");
		if ($dbA->count($result) == 0) { return $qty; }
		$record = $dbA->fetch($result);
		if ($record["scEnabled"] != "Y") { return $qty; }
		$stockLevel = $record["scLevel"];
		$extraFieldValues = null;
		$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"position,name");
		$fullMatch = "";
		$orderPart = "ORDER BY productID";
		if (is_array($extraFieldsArray)) {
			for ($f = 0; $f < count($extraFieldsArray); $f++) {
				$thisValue = "";
				$matchString = "";
				switch ($extraFieldsArray[$f]["type"]) {
					case "CHECKBOXES":
					case "SELECT":
					case "RADIOBUTTONS":
						$thisValue = getFORM($extraFieldsArray[$f]["name"]);
						$matchString = "(extrafield".$extraFieldsArray[$f]["extraFieldID"]." = 0 ";
						for ($g = 0; $g < count($stockFields); $g++) {
							if ($stockFields[$g]["extraFieldID"] == $extraFieldsArray[$f]["extraFieldID"]) {
								$matchString .= " or extrafield".$extraFieldsArray[$f]["extraFieldID"]." = ".$stockFields[$g]["exvalID"];
							}
						}
						$matchString .= ")";
						$orderPart .= ", extrafield".$extraFieldsArray[$f]["extraFieldID"];
						break;
				}
				if ($fullMatch != "") {
					if ($matchString != "") { $fullMatch .= " AND ".$matchString; }
				} else {
					if ($matchString != "") { $fullMatch = $matchString; }
				}
			}
		}
		if ($fullMatch != "") { $fullMatch = " and ".$fullMatch; }
		$result = $dbA->query("select * from $tableCombinations where productID=$productID and type='S' $fullMatch $orderPart");
		$combCount = $dbA->count($result);
		for ($f = 0; $f < $combCount; $f++) {
			$combRecord = $dbA->fetch($result);
			switch ($combRecord["type"]) {
				case "S":
					$stockLevel = makeInteger($combRecord["content"]);
					break;
			}
		}
		if ($stockLevel < $qty && $record["scActionZero"] != 0) {
			$qty = $stockLevel;
		}
		return $qty;
	}	
	
	function alterStock($productID,$qty,$stockFields="") {
		global $dbA,$tableProducts,$tableExtraFields,$tableCombinations,$stockArray;
		if (retrieveOption("featureStockControl") == 0) { return; }
		$result = $dbA->query("select * from $tableProducts where productID=$productID");
		if ($dbA->count($result) == 0) {
			return;
		}
		$record = $dbA->fetch($result);
		if ($record["scEnabled"] != "Y") { return; }
		$result = $dbA->query("update $tableProducts set scLevel=scLevel-$qty where productID=$productID");
		$record["scLevel"] = $record["scLevel"] - $qty;
		if ($record["scLevel"] <= 0 || (retrieveOption("stockWarningNotZero") == 1 && $record["scLevel"] <= $record["scWarningLevel"])) {
			if (retrieveOption("stockZeroEmail") == 1) {
				//send the stock zero email
				$stockArray = $record;
				@sendEmail("COMPANY","","STOCKZERO");
			}
		} else {
			if ($record["scLevel"] <= $record["scWarningLevel"]) {
				if (retrieveOption("stockWarningEmail") == 1) {
					$stockArray = $record;
					@sendEmail("COMPANY","","STOCKWARN");
				}
			}
		}				


		$extraFieldValues = null;
		$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"position,name");
		$fullMatch = "";
		$orderPart = "ORDER BY productID";
		if (is_array($extraFieldsArray)) {
			for ($f = 0; $f < count($extraFieldsArray); $f++) {
				$thisValue = "";
				$matchString = "";
				switch ($extraFieldsArray[$f]["type"]) {
					case "CHECKBOXES":
					case "SELECT":
					case "RADIOBUTTONS":
						$thisValue = getFORM($extraFieldsArray[$f]["name"]);
						$matchString = "(extrafield".$extraFieldsArray[$f]["extraFieldID"]." = 0 ";
						for ($g = 0; $g < count($stockFields); $g++) {
							if ($stockFields[$g]["extraFieldID"] == $extraFieldsArray[$f]["extraFieldID"]) {
								$matchString .= " or extrafield".$extraFieldsArray[$f]["extraFieldID"]." = ".$stockFields[$g]["exvalID"];
							}
						}
						$matchString .= ")";
						$orderPart .= ", extrafield".$extraFieldsArray[$f]["extraFieldID"];
						break;
				}
				if ($fullMatch != "") {
					if ($matchString != "") { $fullMatch .= " AND ".$matchString; }
				} else {
					if ($matchString != "") { $fullMatch = $matchString; }
				}
			}
		}
		if ($fullMatch != "") { $fullMatch = " and ".$fullMatch; }
		$result = $dbA->query("select * from $tableCombinations where productID=$productID and type='S' $fullMatch $orderPart");
		$combCount = $dbA->count($result);
		for ($f = 0; $f < $combCount; $f++) {
			$combRecord = $dbA->fetch($result);
			switch ($combRecord["type"]) {
				case "S":
					$result = $dbA->query("update $tableCombinations set content=content-$qty where combID=".$combRecord["combID"]);
					break;
			}
		}
	}

	function loopAlterStock($orderID) {
		global $dbA,$tableOrdersLines,$tableOrdersExtraFields,$tableOrdersLinesGrouped;
		if (retrieveOption("stockDeductMode") == 1) {
			$result = $dbA->query("select * from $tableOrdersLines where orderID=$orderID order by lineID");	
			$count = $dbA->count($result);
			for ($f = 0; $f < $count; $f++) {
				$record = $dbA->fetch($result);
				$stockFields = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$orderID and lineID=".$record["lineID"]);
				alterStock($record["productID"],$record["qty"],$stockFields);
				$gresult = $dbA->query("select * from $tableOrdersLinesGrouped where orderID=$orderID and lineID=".$record["lineID"]);
				for ($ff = 0; $ff < $dbA->count($gresult); $ff++) {
					$grecord = $dbA->fetch($gresult);
					//$stockFields = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$orderID and lineID=".$record["lineID"]);
					alterStock($grecord["productID"],$record["qty"]*$grecord["qty"]);
				}
			}
		}
	}
	

	
	function checkoutStockCheck() {
		global $cartMain,$dbA,$tableCartsContents,$tableProductsGrouped,$tableProducts;
		$everythingOK = true;
		if (retrieveOption("featureStockControl") == 0) { return true; }
		for ($f = 0; $f < count($cartMain["products"]); $f++) {
			$scLevel = $cartMain["products"][$f]["scLevel"];
			$scEnabled = $cartMain["products"][$f]["scEnabled"];
			$scWarningLevel = $cartMain["products"][$f]["scWarningLevel"];
			$scActionZero = $cartMain["products"][$f]["scActionZero"];
			$groupedProduct = $cartMain["products"][$f]["groupedProduct"];
			$cartMain["products"][$f]["stockcheck"] = "ok";
			if ($scEnabled == "Y" && $scActionZero != 0) {
				if (retrieveOption("stockWarningNotZero") == 1) {
					$scCheck = $scWarningLevel;
				} else {
					$scCheck = 0;
				}
				if ($scLevel < ($scCheck+$cartMain["products"][$f]["qty"])) {
					if ($scLevel > $scCheck) {
						$cartMain["products"][$f]["stockcheck"] = "li";
						$cartMain["products"][$f]["qty"] = $scLevel-$scCheck;
					} else {
						$cartMain["products"][$f]["stockcheck"] = "na";
						$dbA->query("delete from $tableCartsContents where uniqueID=".$cartMain["products"][$f]["uniqueID"]);
					}
					$everythingOK = false;
				}
			}
			if ($groupedProduct == "Y" && $cartMain["products"][$f]["stockcheck"] != "na") {
				$gResult = $dbA->query("select $tableProducts.*,$tableProductsGrouped.qty from $tableProducts,$tableProductsGrouped where $tableProducts.productID = $tableProductsGrouped.groupedID and $tableProductsGrouped.productID=".$cartMain["products"][$f]["productID"]);
				$quantityLimit = $cartMain["products"][$f]["qty"];
				for ($g = 0; $g < $dbA->count($gResult); $g++) {
					$gRecord = $dbA->fetch($gResult);
					$scLevel = $gRecord["scLevel"];
					$scEnabled = $gRecord["scEnabled"];
					$scWarningLevel = $gRecord["scWarningLevel"];
					$scActionZero = $gRecord["scActionZero"];
					if ($scEnabled == "Y" && $scActionZero != 0 && $cartMain["products"][$f]["stockcheck"] != "na") {
						if (retrieveOption("stockWarningNotZero") == 1) {
							$scCheck = $scWarningLevel;
						} else {
							$scCheck = 0;
						}
						if ($scLevel < ($scCheck+($cartMain["products"][$f]["qty"]*$gRecord["qty"]))) {
							if ($scLevel > $scCheck) {
								$cartMain["products"][$f]["stockcheck"] = "li";
								//got to work out a limiting factor here?
								//$cartMain["products"][$f]["qty"] = $scLevel-$scCheck;
								$thisLimit = 0;
								for ($h = 1; $h <= $cartMain["products"][$f]["qty"]; $h++) {
									if ($scLevel >= ($scCheck+($h*$gRecord["qty"]))) {
										$thisLimit = $h;
									}
								}
								if ($thisLimit < $quantityLimit) {
									$quantityLimit = $thisLimit;
								}
							} else {
								$cartMain["products"][$f]["stockcheck"] = "na";
								$dbA->query("delete from $tableCartsContents where uniqueID=".$cartMain["products"][$f]["uniqueID"]);
							}
							$everythingOK = false;
						}
					}					
				}
				if ($cartMain["products"][$f]["stockcheck"] == "li") {
					$cartMain["products"][$f]["qty"] = $quantityLimit;
					echo $quantityLimit;
				}
			}
		}
		return $everythingOK;
	}
?>