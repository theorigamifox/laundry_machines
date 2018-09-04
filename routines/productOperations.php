<?php
	function retrieveProducts($theQuery,&$uCount,$type="m",$follow=1,$inOrderEditing = FALSE) {
		global $dbA,$cartContents,$tableProducts,$tableExtraFields,$cartMain,$xSec,$xProd,$extraFieldsArray,$tableAssociated,$stockControlClause,$tableExtraFieldsValues,$tableReviews,$tableCombinations;
		global $xSelfReturn,$flagArray;
		global $xInpR;
		$customerAccType = $cartMain["accTypeID"];
		$ssResult = $dbA->query($theQuery);
		$uCount = $dbA->count($ssResult);
		$productsArray = "";
		for ($f = 0; $f < $uCount; $f++) {
			$uRecord = $dbA->fetch($ssResult);
			$thisProdID = $uRecord["productID"];
			$uRecord["inCart"] = "N";
			for ($g = 0; $g < count($cartContents); $g++ ) {
				if ($cartContents[$g]["productID"] == $uRecord["productID"]) {
					$uRecord["inCart"] = "Y";
				}
			}
			$uRecord["name"] = findCorrectLanguage($uRecord,"name");
			$uRecord["shortdescription"] = findCorrectLanguage($uRecord,"shortdescription");
			$uRecord["description"] = findCorrectLanguage($uRecord,"description");
			
			$newFlagArray = null;
			if (is_array($flagArray)) {
				for ($g = 0; $g < count($flagArray); $g++) {
					$newFlagArray[$flagArray[$g]["name"]] = $uRecord["flag".$flagArray[$g]["flagID"]];
				}
			}
			if (is_array($newFlagArray)) { $uRecord["flags"] = $newFlagArray; }
			if (retrieveOption("convertToBR") == 1) {
				$uRecord["description"] = str_replace("\r\n","<br>",$uRecord["description"]);
			}
			if (makeDecimal(@$uRecord["advPrice1"]) > 0) {
				$thePrice = calculatePrice($uRecord["advPrice1"],$uRecord["advPrice".$cartMain["currencyID"]],$cartMain["currencyID"]);
			} else {
				$thePrice = calculatePrice($uRecord["price1"],$uRecord["price".$cartMain["currencyID"]],$cartMain["currencyID"]);
			}
			$theOOPrice = calculatePrice($uRecord["ooPrice1"],$uRecord["ooPrice".$cartMain["currencyID"]],$cartMain["currencyID"]);
			$thePrice = processDefaultDiscount($cartMain["accTypeID"],$thePrice,$uRecord);
			$theOOPrice = processDefaultDiscount($cartMain["accTypeID"],$theOOPrice,$uRecord);
			$theRRP = calculatePrice($uRecord["rrp1"],$uRecord["rrp".$cartMain["currencyID"]],$cartMain["currencyID"]);
			$taxamount = calculateTax($thePrice,$uRecord);
			$taxOOamount = calculateTax($theOOPrice,$uRecord);
			$thePriceExTax = $thePrice;
			$thePriceIncTax = $thePrice + $taxamount;
			$thePriceTax = $taxamount;
			$theOOPriceExTax = $theOOPrice;
			$theOOPriceIncTax = $theOOPrice + $taxOOamount;
			$theOOPriceTax = $taxOOamount;
			$rrptaxamount = calculateTax($theRRP,$uRecord);
			$theRRPExTax = $theRRP;
			$theRRPIncTax = $theRRP + $rrptaxamount;
			$theRRPTax = $rrptaxamount;
			if (showPricesIncTax()) {
				$thePrice = $thePrice + $taxamount;
				$theOOPrice = $theOOPrice + $taxOOamount;
				$theRRP = $theRRP + $rrptaxamount;
			}
			if ($theRRP != 0) {
				$theRRPCalc = number_format($theRRP,$cartMain["currency"]["decimals"],".","");
				$thePriceCalc = number_format($thePrice,$cartMain["currency"]["decimals"],".","");
				$priceDifference = $theRRPCalc - $thePriceCalc;
				$pricePercent = ((100/$theRRPCalc) * $thePriceCalc)-100;
				$pricePercent = number_format($pricePercent,2,'.','')."%";
				if (substr($pricePercent,0,1) == "-") {
					$pricePercent = substr($pricePercent,1,strlen($pricePercent)-1);
				} else {
					$pricePercent = "+".$pricePercent;
				}
			} else {
				$priceDifference = 0;
				$pricePercent = "0%";
			}
			if ($type == "s") {
				$combArray = $dbA->retrieveAllRecordsFromQuery("select * from $tableCombinations where productID=$thisProdID and type='S'");
				$uRecord["price"] = formatPriceWithSpan($thePrice,$thisProdID,"price");
				$uRecord["priceextax"] = formatPriceWithSpan($thePriceExTax,$thisProdID,"priceExTax");
				$uRecord["priceinctax"] = formatPriceWithSpan($thePriceIncTax,$thisProdID,"priceIncTax");
				$uRecord["pricetax"] = formatPriceWithSpan($thePriceTax,$thisProdID,"priceTax");	
				//$uRecord["ooprice"] = formatWithoutCalcPrice($theOOPrice);
				//$uRecord["oopriceextax"] = formatWithoutCalcPrice($theOOPriceExTax);
				//$uRecord["oopriceinctax"] = formatWithoutCalcPrice($theOOPriceIncTax);
				//$uRecord["oopricetax"] = formatWithoutCalcPrice($theOOPriceTax);
				
				$uRecord["ooprice"] = formatPriceWithSpan($theOOPrice,$thisProdID,"ooprice");
				$uRecord["oopriceextax"] = formatPriceWithSpan($theOOPriceExTax,$thisProdID,"oopriceExTax");
				$uRecord["oopriceinctax"] = formatPriceWithSpan($theOOPriceIncTax,$thisProdID,"oopriceIncTax");
				$uRecord["oopricetax"] = formatPriceWithSpan($theOOPriceTax,$thisProdID,"oopriceTax");
				
				$uRecord["rrp"] = formatWithoutCalcPrice($theRRP);
				$uRecord["rrpextax"] = formatWithoutCalcPrice($theRRPExTax);
				$uRecord["rrpinctax"] = formatWithoutCalcPrice($theRRPIncTax);
				$uRecord["rrptax"] = formatWithoutCalcPrice($theRRPTax);	
				$uRecord["rrpDifference"] = formatWithoutCalcPrice($priceDifference);
				$uRecord["rrpPercent"] = $pricePercent;		
			} else {
				$uRecord["price"] = formatWithoutCalcPrice($thePrice);
				$uRecord["priceextax"] = formatWithoutCalcPrice($thePriceExTax);
				$uRecord["priceinctax"] = formatWithoutCalcPrice($thePriceIncTax);
				$uRecord["pricetax"] = formatWithoutCalcPrice($thePriceTax);
				$uRecord["ooprice"] = formatWithoutCalcPrice($theOOPrice);
				$uRecord["oopriceextax"] = formatWithoutCalcPrice($theOOPriceExTax);
				$uRecord["oopriceinctax"] = formatWithoutCalcPrice($theOOPriceIncTax);
				$uRecord["oopricetax"] = formatWithoutCalcPrice($theOOPriceTax);
				$uRecord["rrp"] = formatWithoutCalcPrice($theRRP);
				$uRecord["rrpextax"] = formatWithoutCalcPrice($theRRPExTax);
				$uRecord["rrpinctax"] = formatWithoutCalcPrice($theRRPIncTax);
				$uRecord["rrptax"] = formatWithoutCalcPrice($theRRPTax);
				$uRecord["rrpDifference"] = formatWithoutCalcPrice($priceDifference);
				$uRecord["rrpPercent"] = $pricePercent;
			}
			$uRecord["scNoBuy"] = "N";
			if (retrieveOption("featureStockControl") == 1 && $uRecord["scActionZero"] == 2 && $uRecord["scEnabled"] == "Y") {
				if (retrieveOption("stockWarningNotZero") == 1) {
					if ($uRecord["scLevel"] <= $uRecord["scWarningLevel"]) {
						$uRecord["scNoBuy"] = "Y";
					}
				} else {
					if ($uRecord["scLevel"] <= 0) {
						$uRecord["scNoBuy"] = "Y";
					}
				}
			}
			
			//how to output the extra fields
			$allExtraFields = "";
			if (is_array($extraFieldsArray)) {
				for ($g = 0; $g < count($extraFieldsArray); $g++) {
					$thisExtraField = "";
					switch ($extraFieldsArray[$g]["type"]) {
						case "USERINPUT":
							$thisExtraField["name"] = $extraFieldsArray[$g]["name"];
							$thisExtraField["title"] = findCorrectLanguage($extraFieldsArray[$g],"title");
							$thisExtraField["type"] = $extraFieldsArray[$g]["type"];
							if ($uRecord["extrafield".$extraFieldsArray[$g]["extraFieldID"]] > 0) {
								$thisExtraField["content"] = "Y";
							}
							if ($xInpR == $extraFieldsArray[$g]["extraFieldID"] && $type == "USERINPUT" && $uRecord["extrafield".$extraFieldsArray[$g]["extraFieldID"]] == 2) {
								$thisExtraField["error"] = "Y";
							}
							$thisExtraField["requirement"] = $uRecord["extrafield".$extraFieldsArray[$g]["extraFieldID"]];
							$thisExtraField["size"] = $extraFieldsArray[$g]["size"];
							$thisExtraField["maxlength"] = $extraFieldsArray[$g]["maxlength"];
							$uRecord["extra_".$extraFieldsArray[$g]["name"]] = $thisExtraField;
							break;
						case "TEXT":
						case "TEXTAREA":
						case "IMAGE":
							$thisExtraField["name"] = $extraFieldsArray[$g]["name"];
							$thisExtraField["title"] = findCorrectLanguage($extraFieldsArray[$g],"title");
							$thisExtraField["type"] = $extraFieldsArray[$g]["type"];
							if (retrieveOption("convertToBR") == 1) {
								$uRecord["extrafield".$extraFieldsArray[$g]["extraFieldID"]] = findCorrectLanguageExtraField($uRecord,"extrafield".$extraFieldsArray[$g]["extraFieldID"]);
								$thisExtraField["content"] = str_replace("\r\n","<br>",$uRecord["extrafield".$extraFieldsArray[$g]["extraFieldID"]]);
							} else {
								$uRecord["extrafield".$extraFieldsArray[$g]["extraFieldID"]] = findCorrectLanguageExtraField($uRecord,"extrafield".$extraFieldsArray[$g]["extraFieldID"]);
								$thisExtraField["content"] = $uRecord["extrafield".$extraFieldsArray[$g]["extraFieldID"]];
							}
							$thisExtraField["options"] = "";
							$uRecord["extra_".$extraFieldsArray[$g]["name"]] = $thisExtraField;
							break;
						case "CHECKBOXES":
						case "SELECT":
						case "RADIOBUTTONS":
							if ($type == "s") {
								$thisExtraFieldID = $extraFieldsArray[$g]["extraFieldID"];
								$efResult = $dbA->query("select * from $tableExtraFieldsValues where productID=$thisProdID and extraFieldID=$thisExtraFieldID and visible='Y' and (accTypeID = 0 or accTypeID = $customerAccType) order by position,exvalID");
								$efCount = $dbA->count($efResult);
								$optionArray = null;
								for ($h = 0; $h < $efCount; $h++) {
									$efRecord = $dbA->fetch($efResult);
									$efRecord["content"] = findCorrectLanguage($efRecord,"content");
									$showOption = true;
									for ($i = 0; $i < count($combArray); $i++) {
										$numberAttributes = 0;
										if (is_array($extraFieldsArray)) {
											for ($j = 0; $j < count($extraFieldsArray); $j++) {
												if ($extraFieldsArray[$j]["type"] == "CHECKBOXES" || $extraFieldsArray[$j]["type"] == "SELECT" || $extraFieldsArray[$j]["type"] == "RADIOBUTTONS") {
													if ($combArray[$i]["extrafield".$extraFieldsArray[$j]["extraFieldID"]] > 0) {
														$numberAttributes++;
													}
												}
											}
										}
										if ($numberAttributes == 1) {
											if ($combArray[$i]["extrafield".$extraFieldsArray[$g]["extraFieldID"]] == $efRecord["exvalID"]) {
												if (makeInteger($combArray[$i]["content"]) < 1 && $combArray[$i]["exclude"] == "Y") {
													$showOption = false;
												}
											}
										}
									}
									if ($showOption) {
										$optionArray[]=array("option"=>$efRecord["content"],"id"=>$efRecord["exvalID"]);
									}
								}
								$thisExtraField["name"] = $extraFieldsArray[$g]["name"];
								$thisExtraField["title"] = findCorrectLanguage($extraFieldsArray[$g],"title");
								$thisExtraField["type"] = $extraFieldsArray[$g]["type"];
								if ($efCount > 0) {
									$theContent = "Y";
								} else {
									$theContent = "";
								}
								$thisExtraField["content"] = $theContent;
								$thisExtraField["options"] = $optionArray;
								
								$uRecord["extra_".$extraFieldsArray[$g]["name"]] = $thisExtraField;
							}
							break;
					}
					$allExtraFields[] = $thisExtraField;
				}
				$uRecord["extrafields"] = $allExtraFields;
			}			
			//get associated Products
			if (!$inOrderEditing) {
				if ($follow == 0) {
					$uRecord["link"] = createProductLink($uRecord["productID"]);
				} else {
					$uRecord["link"] = createProductLink($uRecord["productID"],$xSec);
				}
			}
			$uRecord["qtyboxname"] = "qty".$uRecord["productID"];
			$uRecord["recalculateprice"] = "recalcPrice(".$uRecord["productID"].");";
			$uRecord["form"]["name"] = "productForm".$uRecord["productID"];
			if (!$inOrderEditing) {
				$uRecord["form"]["action"] = configureURL("cart.php?xCmd=add&xProd=".$uRecord["productID"]."&xFwd=$xSelfReturn");
				$uRecord["wishlist"]["link"] = configureURL("customer.php?xCmd=wladd&xProd=".$uRecord["productID"]);
				$uRecord["review"]["link"] = configureURL("customer.php?xCmd=revform&xProd=".$uRecord["productID"]);
				$uRecord["add"]["link"] = "javascript:document.productForm".$uRecord["productID"].".submit();";
				$uRecord["form"]["onsubmit"] = "return true;";
			}
			$productsArray[] = $uRecord;
		}		
		if ($type != "m") {
			return $productsArray[0];
		} else {
			return $productsArray;
		}
	}
	
	function createPricingArray($priceArray,$xProd,$xBasePrice,$xOOBasePrice,$productsArray) {
		global $cartMain,$extraFieldsArray;
		$pricingArray = "";
		$pricingArray .= "cDP = ".$cartMain["currency"]["decimals"].";\r\n";
		$pricingArray .= "cPreT = \"".$cartMain["currency"]["pretext"]."\";\r\n";
		$pricingArray .= "cMidT = \"".$cartMain["currency"]["middletext"]."\";\r\n";
		$pricingArray .= "cPostT = \"".$cartMain["currency"]["posttext"]."\";\r\n";
		
		$pricingArray .= "efcount = ".count($extraFieldsArray).";\r\n";
		$pricingArray .= "extrafields = new Array(efcount);\r\n"; 
		$pricingArray .= "extrafieldstype = new Array(efcount);\r\n"; 
		if (is_array($extraFieldsArray)) {
			for ($g = 0; $g < count($extraFieldsArray); $g++) {
				$pricingArray .= "extrafields[$g] = \"".$extraFieldsArray[$g]["name"]."\";";
				$pricingArray .= "extrafieldstype[$g] = \"".$extraFieldsArray[$g]["type"]."\";";
			}
		}
		$counter = count($priceArray);
		$theBasePrice = processDefaultDiscount($cartMain["accTypeID"],$xBasePrice,$productsArray);
		$theOOBasePrice = processDefaultDiscount($cartMain["accTypeID"],$xOOBasePrice,$productsArray);
		$theTax = calculateTaxUnrounded($theBasePrice,$productsArray);
		$theOOTax = calculateTaxUnrounded($theOOBasePrice,$productsArray);
		if (showPricesIncTax()) {
			$xBasePrice = $theBasePrice + $theTax;
			$xOOBasePrice = $theOOBasePrice + $theOOTax;
		} else {
			$xBasePrice = $theBasePrice;
			$xOOBasePrice = $theOOBasePrice;
		}
		$xBasePriceExTax = $theBasePrice;
		$xBasePriceIncTax = $theBasePrice + $theTax;
		$xBasePriceTax = $theTax;
		
		$xOOBasePriceExTax = $theOOBasePrice;
		$xOOBasePriceIncTax = $theOOBasePrice + $theOOTax;
		$xOOBasePriceTax = $theOOTax;
		
		$pricingArray .= "baseprice$xProd = ".$xBasePrice.";\r\n";
		$pricingArray .= "basepriceExTax$xProd = ".$xBasePriceExTax.";\r\n";
		$pricingArray .= "basepriceIncTax$xProd = ".$xBasePriceIncTax.";\r\n";
		$pricingArray .= "basepriceTax$xProd = ".$xBasePriceTax.";\r\n";
		$pricingArray .= "oobaseprice$xProd = ".$xOOBasePrice.";\r\n";
		$pricingArray .= "oobasepriceExTax$xProd = ".$xOOBasePriceExTax.";\r\n";
		$pricingArray .= "oobasepriceIncTax$xProd = ".$xOOBasePriceIncTax.";\r\n";
		$pricingArray .= "oobasepriceTax$xProd = ".$xOOBasePriceTax.";\r\n";
		$pricingArray .= "parray$xProd = new Array($counter);\r\n";

		for ($f = 0; $f < count($priceArray); $f++) {
			$pricingArray .= "parray$xProd"."[$f] = new Array();\r\n";
			foreach($priceArray[$f] as $k=>$v) {
				$pricingArray .= "parray$xProd"."[$f]"."[\"$k\"] = \"$v\";\r\n";
			}
		}
		return $pricingArray;
	}
	
	function retrieveAdvancedPricing($xProd,$xBasePrice,$xOOBasePrice,&$gotsome,&$productsArray,&$quantitytable,&$combinationstable,&$exclusionstable,&$oneofftable) {
		global $dbA,$cartMain,$tableProducts,$tableAdvancedPricing,$extraFieldsArray,$tableExtraFieldsValues,$tableExtraFieldsPrices,$tableCombinations;
		$customerAccType = $cartMain["accTypeID"];
		$advArray = $dbA->retrieveAllRecordsFromQuery("select * from $tableAdvancedPricing where (accTypeID = 0 or accTypeID = $customerAccType) and productID=$xProd order by priceType,qtyfrom,price1,percentage");		
		$priceArray = null;
		$origBasePrice = $xBasePrice;
		$origOOBasePrice = $xOOBasePrice;
		$xBasePrice = processDefaultDiscount($cartMain["accTypeID"],calculatePrice($productsArray["price1"],$productsArray["price".$cartMain["currency"]["currencyID"]],$cartMain["currency"]["currencyID"]),$productsArray);
		$xBasePriceOrig = $xBasePrice;
		if (showPricesIncTax()) {
			$xBasePrice = $xBasePrice + calculateTax($xBasePrice,$productsArray);
		}

		for ($f = 0; $f < count($advArray); $f++) {
			if ($advArray[$f]["priceType"] == 0) {
				$priceArray[]["priceType"] = 0;
				$thisPA = count($priceArray)-1;
				$priceArray[$thisPA]["percentage"] = $advArray[$f]["percentage"];
				$thisPrice = processDefaultDiscount($cartMain["accTypeID"],calculatePrice($advArray[$f]["price1"],$advArray[$f]["price".$cartMain["currency"]["currencyID"]],$cartMain["currency"]["currencyID"]),$productsArray);
				
				$thisPriceExTax = $thisPrice;
				$thisPriceIncTax = $thisPrice + calculateTaxUnrounded($thisPrice,$productsArray);
				$thisPriceTax = calculateTaxUnrounded($thisPrice,$productsArray);
				if (showPricesIncTax()) {
					$thisPrice = $thisPrice + calculateTaxUnrounded($thisPrice,$productsArray);
				}
				$priceArray[$thisPA]["price"] = $thisPrice;
				$priceArray[$thisPA]["priceExTax"] = $thisPriceExTax;
				$priceArray[$thisPA]["priceIncTax"] = $thisPriceIncTax;
				$priceArray[$thisPA]["priceTax"] = $thisPriceTax;
				$priceArray[$thisPA]["qtyfrom"] = $advArray[$f]["qtyfrom"];
				$priceArray[$thisPA]["qtyto"] = $advArray[$f]["qtyto"];
				
				$combFields = "";
				if (is_array($extraFieldsArray)) {
					for ($g = 0; $g < count($extraFieldsArray); $g++) {				
						$thisExtraValueID = $advArray[$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]];
						$priceArray[$thisPA][$extraFieldsArray[$g]["name"]] = $thisExtraValueID;
						if ($thisExtraValueID > 0) {
							$thisFieldText = "";
							for ($h = 0; $h < count(@$productsArray["extrafields"]); $h++) {
								if (@$extraFieldsArray[$g]["name"]==@$productsArray["extrafields"][$h]["name"]) {
									for ($i = 0; $i < count(@$productsArray["extrafields"][$h]["options"]); $i++) {
										if ($thisExtraValueID == $productsArray["extrafields"][$h]["options"][$i]["id"]) {
											$thisFieldText = $productsArray["extrafields"][$h]["options"][$i]["option"];
										}
									}
								}
							}
							$combFields[] = array("field"=>$extraFieldsArray[$g]["title"],"value"=>$thisFieldText);
						}
					}
				}

				if ($advArray[$f]["percentage"] <> 0) {
					$myPrice  = $advArray[$f]["percentage"]."%";
				} else {
					$additionalSign = ($advArray[$f]["price1"] >= 0) ? "" : "-";
					$thisPrice = processDefaultDiscount($cartMain["accTypeID"],calculatePrice(abs($advArray[$f]["price1"]),abs($advArray[$f]["price".$cartMain["currency"]["currencyID"]]),$cartMain["currency"]["currencyID"]),$productsArray);
					if (showPricesIncTax()) {
						$thisPrice = $thisPrice + calculateTaxUnrounded($thisPrice,$productsArray);
					}
					$myPrice = $additionalSign.formatWithoutCalcPrice($thisPrice);
				}
				$combinationstable["entries"][] = array("price"=>$myPrice,"fields"=>$combFields,"qtyfrom"=>$priceArray[$thisPA]["qtyfrom"],"qtyto"=>$priceArray[$thisPA]["qtyto"]);
			}
		}
		$combinationstable["available"] = (count($combinationstable["entries"]) > 0) ? "Y" : "N";
		
		$efResult = $dbA->query("select * from $tableExtraFieldsValues,$tableExtraFieldsPrices where $tableExtraFieldsValues.exvalID = $tableExtraFieldsPrices.exvalID and productID=$xProd and visible='Y' and (accTypeID = 0 or accTypeID = $customerAccType) order by extraFieldID,position,$tableExtraFieldsValues.exvalID");
		$efCount = $dbA->count($efResult);
		for ($f = 0; $f < $efCount; $f++) {
			$efRecord = $dbA->fetch($efResult);
			$priceArray[]["priceType"] = 1;
			$thisPA = count($priceArray)-1;
			$priceArray[$thisPA]["percentage"] = $efRecord["percent"];
			$thisPrice = processDefaultDiscount($cartMain["accTypeID"],calculatePrice($efRecord["price1"],$efRecord["price".$cartMain["currency"]["currencyID"]],$cartMain["currency"]["currencyID"]),$productsArray);

			$thisPriceExTax = $thisPrice;
			$thisPriceIncTax = $thisPrice + calculateTaxUnrounded($thisPrice,$productsArray);
			$thisPriceTax = calculateTaxUnrounded($thisPrice,$productsArray);
			if (showPricesIncTax()) {
				$thisPrice = $thisPrice + calculateTaxUnrounded($thisPrice,$productsArray);
			}
			$priceArray[$thisPA]["price"] = $thisPrice;
			$priceArray[$thisPA]["priceExTax"] = $thisPriceExTax;
			$priceArray[$thisPA]["priceIncTax"] = $thisPriceIncTax;
			$priceArray[$thisPA]["priceTax"] = $thisPriceTax;

			$priceArray[$thisPA]["qtyfrom"] = -1;
			$priceArray[$thisPA]["qtyto"] = -1;
			if (is_array($extraFieldsArray)) {
				for ($g = 0; $g < count($extraFieldsArray); $g++) {
					if ($extraFieldsArray[$g]["extraFieldID"] == $efRecord["extraFieldID"]) {
						$priceArray[$thisPA][$extraFieldsArray[$g]["name"]] = $efRecord["exvalID"];
						for ($i = 0; $i < count($productsArray["extrafields"][$g]["options"]); $i++) {
							if ($efRecord["exvalID"] == $productsArray["extrafields"][$g]["options"][$i]["id"]) {
								if ($efRecord["percent"] != 0) {
									$additionalSign = ($efRecord["percent"] > 0) ? "+" : "";
									$productsArray["extrafields"][$g]["options"][$i]["price"] = $additionalSign.$efRecord["percent"]."%";
									$productsArray["extra_".$productsArray["extrafields"][$g]["name"]][$g]["options"][$i]["price"] = $additionalSign.$efRecord["percent"]."%";
								} else {
									if ($efRecord["price1"] != 0) {
										$additionalSign = ($efRecord["price1"] > 0) ? "+" : "-";
	
										$thisPrice = processDefaultDiscount($cartMain["accTypeID"],calculatePrice(abs($efRecord["price1"]),abs($efRecord["price".$cartMain["currency"]["currencyID"]]),$cartMain["currency"]["currencyID"]),$productsArray);
										if (showPricesIncTax()) {
											$thisPrice = $thisPrice + calculateTaxUnrounded($thisPrice,$productsArray);
										}
										$productsArray["extrafields"][$g]["options"][$i]["price"] = $additionalSign.formatWithoutCalcPrice($thisPrice);
										$productsArray["extra_".$productsArray["extrafields"][$g]["name"]]["options"][$i]["price"] = $additionalSign.formatWithoutCalcPrice($thisPrice);
										//$productsArray["extrafields"][$g]["options"][$i]["price"] = $additionalSign.calculatePriceFormat(abs($efRecord["price1"]),abs($efRecord["price".$cartMain["currency"]["currencyID"]]),$cartMain["currency"]["currencyID"]);
									}
								}
								break;
							}
						}						
					} else {
						$priceArray[$thisPA][$extraFieldsArray[$g]["name"]] = "";
					}
				}
			}
		}

		for ($f = 0; $f < count($advArray); $f++) {
			if ($advArray[$f]["priceType"] == 2) {
				$priceArray[]["priceType"] = 2;
				$thisPA = count($priceArray)-1;
				$priceArray[$thisPA]["percentage"] = $advArray[$f]["percentage"];
				$thisPrice = processDefaultDiscount($cartMain["accTypeID"],calculatePrice($advArray[$f]["price1"],$advArray[$f]["price".$cartMain["currency"]["currencyID"]],$cartMain["currency"]["currencyID"]),$productsArray);
				$thisPriceExTax = $thisPrice;
				$thisPriceIncTax = $thisPrice + calculateTaxUnrounded($thisPrice,$productsArray);
				$thisPriceTax = calculateTaxUnrounded($thisPrice,$productsArray);
				if (showPricesIncTax()) {
					$thisPrice = $thisPrice + calculateTaxUnrounded($thisPrice,$productsArray);
				}
				$priceArray[$thisPA]["price"] = $thisPrice;
				$priceArray[$thisPA]["priceExTax"] = $thisPriceExTax;
				$priceArray[$thisPA]["priceIncTax"] = $thisPriceIncTax;
				$priceArray[$thisPA]["priceTax"] = $thisPriceTax;			
				$priceArray[$thisPA]["qtyfrom"] = $advArray[$f]["qtyfrom"];
				$priceArray[$thisPA]["qtyto"] = $advArray[$f]["qtyto"];
				if (is_array($extraFieldsArray)) {
					for ($g = 0; $g < count($extraFieldsArray); $g++) {
						$priceArray[$thisPA][$extraFieldsArray[$g]["name"]] = $advArray[$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]];
					}
				}

				if ($advArray[$f]["percentage"] <> 0) {
					$discount  = $advArray[$f]["percentage"]."%";

					$thisPrice = processDefaultDiscount($cartMain["accTypeID"],calculatePrice(abs($advArray[$f]["price1"]),abs($advArray[$f]["price".$cartMain["currency"]["currencyID"]]),$cartMain["currency"]["currencyID"]),$productsArray);

					$xPriceFromBase = $xBasePriceOrig * (1-($advArray[$f]["percentage"]/100));
					$bpPriceExTax = $xPriceFromBase;
					$bpPriceTax = calculateTaxUnrounded($xPriceFromBase,$productsArray);
					
					if (showPricesIncTax()) {
						$xPriceFromBase = $xPriceFromBase + $bpPriceTax;
					}
					
					$bpPriceIncTax = $bpPriceExTax + $bpPriceTax;
					$xPriceFromBase = formatWithoutCalcPrice($xPriceFromBase);
					$xPFBTax = formatWithoutCalcPrice($bpPriceTax);
					$xPFBExTax = formatWithoutCalcPrice($bpPriceExTax);
					$xPFBIncTax = formatWithoutCalcPrice($bpPriceIncTax);
					
					$discountTax = 0;
					$discountExTax = 0;
					$discountIncTax = 0;
				} else {
					$additionalSign = ($advArray[$f]["price1"] >= 0) ? "" : "-";

					$thisPrice = processDefaultDiscount($cartMain["accTypeID"],calculatePrice(abs($advArray[$f]["price1"]),abs($advArray[$f]["price".$cartMain["currency"]["currencyID"]]),$cartMain["currency"]["currencyID"]),$productsArray);
					$xPriceFromBase=$xBasePriceOrig-$thisPrice;
					$priceExTax = $thisPrice;
					$priceTax = calculateTaxUnrounded($thisPrice,$productsArray);
					
					$bpPriceExTax = $xPriceFromBase;
					$bpPriceTax = calculateTaxUnrounded($xPriceFromBase,$productsArray);
					
					if (showPricesIncTax()) {
						$thisPrice = $thisPrice + $priceTax;
						$xPriceFromBase = $xPriceFromBase + $bpPriceTax;
					}
					$priceIncTax = $priceExTax + $priceTax;
					$discount = $additionalSign.formatWithoutCalcPrice($thisPrice);
					$discountTax = $additionalSign.formatWithoutCalcPrice($priceTax);
					$discountExTax = $additionalSign.formatWithoutCalcPrice($priceExTax);
					$discountIncTax = $additionalSign.formatWithoutCalcPrice($priceIncTax);
					
					$bpPriceIncTax = $bpPriceExTax + $bpPriceTax;
					$xPriceFromBase = formatWithoutCalcPrice($xPriceFromBase);
					$xPFBTax = formatWithoutCalcPrice($bpPriceTax);
					$xPFBExTax = formatWithoutCalcPrice($bpPriceExTax);
					$xPFBIncTax = formatWithoutCalcPrice($bpPriceIncTax);
			 	}
				$quantitytable["entries"][] = array("from"=>$advArray[$f]["qtyfrom"],"to"=>$advArray[$f]["qtyto"],"discount"=>$discount,"discountTax"=>$discountTax,"discountExTax"=>$discountExTax,"discountIncTax"=>$discountIncTax,"priceDiscounted"=>$xPriceFromBase,"priceDiscountedTax"=>$xPFBTax,"priceDiscountedExTax"=>$xPFBExTax,"priceDiscountedIncTax"=>$xPFBIncTax);
			}
		}		
		$quantitytable["available"] = (count($quantitytable["entries"]) > 0) ? "Y" : "N";	

		for ($f = 0; $f < count($advArray); $f++) {
			if ($advArray[$f]["priceType"] == 4) {
				$priceArray[]["priceType"] = 4;
				$thisPA = count($priceArray)-1;
				$priceArray[$thisPA]["percentage"] = $advArray[$f]["percentage"];
				$thisPrice = processDefaultDiscount($cartMain["accTypeID"],calculatePrice($advArray[$f]["price1"],$advArray[$f]["price".$cartMain["currency"]["currencyID"]],$cartMain["currency"]["currencyID"]),$productsArray);
				
				$thisPriceExTax = $thisPrice;
				$thisPriceIncTax = $thisPrice + calculateTaxUnrounded($thisPrice,$productsArray);
				$thisPriceTax = calculateTax($thisPrice,$productsArray);
				if (showPricesIncTax()) {
					$thisPrice = $thisPrice + calculateTaxUnrounded($thisPrice,$productsArray);
				}
				$priceArray[$thisPA]["price"] = $thisPrice;
				$priceArray[$thisPA]["priceExTax"] = $thisPriceExTax;
				$priceArray[$thisPA]["priceIncTax"] = $thisPriceIncTax;
				$priceArray[$thisPA]["priceTax"] = $thisPriceTax;
				$priceArray[$thisPA]["qtyfrom"] = $advArray[$f]["qtyfrom"];
				$priceArray[$thisPA]["qtyto"] = $advArray[$f]["qtyto"];
				
				$combFields = "";
				if (is_array($extraFieldsArray)) {
					for ($g = 0; $g < count($extraFieldsArray); $g++) {				
						$thisExtraValueID = $advArray[$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]];
						$priceArray[$thisPA][$extraFieldsArray[$g]["name"]] = $thisExtraValueID;
						if ($thisExtraValueID > 0) {
							$thisFieldText = "";
							for ($h = 0; $h < count(@$productsArray["extrafields"]); $h++) {
								if (@$extraFieldsArray[$g]["name"]==@$productsArray["extrafields"][$h]["name"]) {
									for ($i = 0; $i < count(@$productsArray["extrafields"][$h]["options"]); $i++) {
										if ($thisExtraValueID == $productsArray["extrafields"][$h]["options"][$i]["id"]) {
											$thisFieldText = $productsArray["extrafields"][$h]["options"][$i]["option"];
										}
									}
								}
							}
							$combFields[] = array("field"=>$extraFieldsArray[$g]["title"],"value"=>$thisFieldText);
						}
					}
				}

				if ($advArray[$f]["percentage"] <> 0) {
					$myPrice  = $advArray[$f]["percentage"]."%";
				} else {
					$additionalSign = ($advArray[$f]["price1"] >= 0) ? "" : "-";
					$thisPrice = processDefaultDiscount($cartMain["accTypeID"],calculatePrice(abs($advArray[$f]["price1"]),abs($advArray[$f]["price".$cartMain["currency"]["currencyID"]]),$cartMain["currency"]["currencyID"]),$productsArray);
					if (showPricesIncTax()) {
						$thisPrice = $thisPrice + calculateTaxUnrounded($thisPrice,$productsArray);
					}
					$myPrice = $additionalSign.formatWithoutCalcPrice($thisPrice);
				}
				$oneofftable["entries"][] = array("price"=>$myPrice,"fields"=>$combFields);
			}
		}
		$oneofftable["available"] = (count($oneofftable["entries"]) > 0) ? "Y" : "N";


		$advArray = $dbA->retrieveAllRecordsFromQuery("select * from $tableCombinations where type='E' and productID=$xProd order by combID");		

		for ($f = 0; $f < count($advArray); $f++) {
			$combFields = "";
			if (is_array($extraFieldsArray)) {
				for ($g = 0; $g < count($extraFieldsArray); $g++) {				
					$thisExtraValueID = makeInteger(@$advArray[$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]]);
					if ($thisExtraValueID > 0) {
						$thisFieldText = "";
						for ($h = 0; $h < count(@$productsArray["extrafields"]); $h++) {
							if (@$extraFieldsArray[$g]["name"]==@$productsArray["extrafields"][$h]["name"]) {
								for ($i = 0; $i < count(@$productsArray["extrafields"][$h]["options"]); $i++) {
									if ($thisExtraValueID == $productsArray["extrafields"][$h]["options"][$i]["id"]) {
										$thisFieldText = $productsArray["extrafields"][$h]["options"][$i]["option"];
									}
								}
							}
						}
						$combFields[] = array("field"=>$extraFieldsArray[$g]["title"],"value"=>$thisFieldText);
					}
				}
			}
			$exclusionstable["entries"][] = array("fields"=>$combFields);
		}
		$exclusionstable["available"] = (count($exclusionstable["entries"]) > 0) ? "Y" : "N";

		
		$gotsome = (count($priceArray) > 0) ? true : false;
		return "<Script language=\"JavaScript\">\r\n".createPricingArray($priceArray,$xProd,$origBasePrice,$origOOBasePrice,$productsArray)."</Script>\r\n";
	}
?>