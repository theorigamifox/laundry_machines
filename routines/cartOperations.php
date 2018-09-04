<?php
	function getUniqueID(){ 
		mt_srand ((double) microtime() * 1000000); 
		return $unique_id = md5(uniqid(mt_rand(),1)); 
	}

	function createNewCart() {
		global $dbA,$tableCarts,$jssCustomer,$forceDefaultTemplates;
		$cartID = getUniqueID();
		$result = $dbA->query("select cartID from $tableCarts where cartID='$cartID'");
		while ($dbA->count($result) > 0) {
			$cartID = getUniqueID();
			$result = $dbA->query("select cartID from $tableCarts where cartID='$cartID'");
		}
		$rArray = "";
		$thisTime = time();
		$rArray[] = array("createtime",$thisTime,"N");			
		$rArray[] = array("cartID",$cartID,"S");			
		$rArray[] = array("currencyID",retrieveOption("defaultCurrency"),"N");
		$rArray[] = array("languageID",retrieveOption("defaultLanguage"),"N");
		$rArray[] = array("country",retrieveOption("defaultCountry"),"N");
		$rArray[] = array("date",date("Ymd"),"S");
		$xReferrer = @$_SERVER["HTTP_REFERER"];
		if ($xReferrer == "") {
			$xReferrer = "Direct/Unknown";
		}
		$rArray[] = array("referURL",$xReferrer,"S");
		if ($jssCustomer != "") {
			$rArray[] = array("customerID",$jssCustomer,"S");
		}
		if (retrieveOption("affiliatesActivated") == 1) {
			$rArray[] = array("affiliateID",affiliatesCart(),"S");
		}
		if (@$forceDefaultTemplates != "") {
			$rArray[] = array("templateSet",$forceDefaultTemplates,"S");	
		} else {
			$rArray[] = array("templateSet","templates/","S");	
		}
		$dbA->insertRecord($tableCarts,$rArray,0);
		return $cartID;
	}
	
	function clearCart() {
		global $cartID,$tableCarts,$tableCartsContents,$dbA,$cartContents,$cartMain;
		$dbA->query("delete from $tableCartsContents where cartID='$cartID'");
		$cartMain["products"] = null;
		$cartProducts = null;
	}

	function cartRetrieveCart($inOrderEditing = FALSE) {
		global $dbA,$cartID,$cartMain,$cartContents,$extraFieldsArray,$tableCartsContents,$tableProducts,$flagArray,$tableExtraFieldsValues,$langArray,$currArray;	

		$extraFieldsBit = "";

		$langBit = "";
		for ($f = 0; $f < count($langArray); $f++) {
			if ($langArray[$f]["languageID"] != 1) {
				$langBit .=", $tableProducts.name".$langArray[$f]["languageID"];
			}
		}

		if (is_array($extraFieldsArray)) {
			for ($f = 0; $f < count($extraFieldsArray); $f++) {
				if ($extraFieldsArray[$f]["type"] == "TEXT" || $extraFieldsArray[$f]["type"] == "TEXTAREA") {
					if ($cartMain["languageID"] == 1) {
						$extraFieldsBit .=", $tableProducts.extrafield".$extraFieldsArray[$f]["extraFieldID"];
					} else {
						$extraFieldsBit .=", $tableProducts.extrafield".$extraFieldsArray[$f]["extraFieldID"];
						$extraFieldsBit .=", $tableProducts.extrafield".$extraFieldsArray[$f]["extraFieldID"]. "_".$cartMain["languageID"];
					}
				}
			}
		}
		$flagBit = "";
		if (is_array($flagArray)) {
			for ($g = 0; $g < count($flagArray); $g++) {
				$flagBit .= ",flag".$flagArray[$g]["flagID"];
			}
		}
		
		if ($inOrderEditing) {
			$cartContents = $dbA->retrieveAllRecordsFromQuery("select $tableCartsContents.*,$tableProducts.ignoreDiscounts,$tableProducts.combineQty,$tableProducts.scEnabled,$tableProducts.scLevel,$tableProducts.scWarningLevel,$tableProducts.scActionZero,$tableProducts.groupedProduct,$tableProducts.digitalFile,$tableProducts.digitalReg,$tableProducts.thumbnail,$tableProducts.name,$tableProducts.shortdescription $langBit $extraFieldsBit $flagBit from $tableCartsContents LEFT JOIN $tableProducts on $tableCartsContents.productID=$tableProducts.productID where cartID=\"$cartID\" order by $tableProducts.".retrieveOption("cartSortOrder"));
			
		} else {
			$cartContents = $dbA->retrieveAllRecordsFromQuery("select $tableCartsContents.*,$tableProducts.ignoreDiscounts,$tableProducts.combineQty,$tableProducts.scEnabled,$tableProducts.scLevel,$tableProducts.scWarningLevel,$tableProducts.scActionZero,$tableProducts.groupedProduct,$tableProducts.digitalFile,$tableProducts.digitalReg,$tableProducts.thumbnail,$tableProducts.name,$tableProducts.shortdescription $langBit $extraFieldsBit $flagBit from $tableCartsContents,$tableProducts where cartID=\"$cartID\" and (accTypes like '%;".$cartMain["accTypeID"].";%' or accTypes like '%;0;%') and $tableCartsContents.productID=$tableProducts.productID order by $tableProducts.".retrieveOption("cartSortOrder"));
		}
		
		
		for ($x = 0; $x < count($cartContents); $x++) {
		
			$newFlagArray = null;
			if (is_array($flagArray)) {
				for ($g = 0; $g < count($flagArray); $g++) {
					$newFlagArray[$flagArray[$g]["name"]] = $cartContents[$x]["flag".$flagArray[$g]["flagID"]];
				}
			}
			if (is_array($newFlagArray)) { $cartContents[$x]["flags"] = $newFlagArray; }
		
		
			$extraVals = $dbA->retrieveAllRecordsFromQuery("select * from $tableExtraFieldsValues where productID=".$cartContents[$x]["productID"]);
			if (is_array($extraFieldsArray)) {
				for ($f = 0; $f < count($extraFieldsArray); $f++) {
					switch ($extraFieldsArray[$f]["type"]) {
						case "SELECT":
						case "RADIOBUTTONS":
							for ($z = 0; $z < count($extraVals); $z++) {
								if ($extraVals[$z]["exvalID"] == $cartContents[$x]["extrafieldid".$extraFieldsArray[$f]["extraFieldID"]]) {
									//found the option
									for ($y = 0; $y < count($langArray); $y++) {
										if ($langArray[$y]["languageID"] != 1) {
											$cartContents[$x]["extrafield".$extraFieldsArray[$f]["extraFieldID"]."_".$langArray[$y]["languageID"]] = $extraVals[$z]["content".$langArray[$y]["languageID"]];
										} else {
											$cartContents[$x]["extrafield".$extraFieldsArray[$f]["extraFieldID"]] = $extraVals[$z]["content"];
										}
									}
								}
							}
							break;
						case "CHECKBOXES":
							$optionsSplit = explode("|",$cartContents[$x]["extrafieldid".$extraFieldsArray[$f]["extraFieldID"]]);
							for ($w = 0; $w < count($optionsSplit); $w++ ) {
								for ($z = 0; $z < count($extraVals); $z++) {
									if ($extraVals[$z]["exvalID"] == $optionsSplit[$w]) {
										//found the option
										for ($y = 0; $y < count($langArray); $y++) {
											if ($langArray[$y]["languageID"] != 1) {
												if (!array_key_exists("extrafield".$extraFieldsArray[$f]["extraFieldID"]."_".$langArray[$y]["languageID"],$cartContents[$x])) {
													$cartContents[$x]["extrafield".$extraFieldsArray[$f]["extraFieldID"]."_".$langArray[$y]["languageID"]] = "";
												}
												$cartContents[$x]["extrafield".$extraFieldsArray[$f]["extraFieldID"]."_".$langArray[$y]["languageID"]] .= $extraVals[$z]["content".$langArray[$y]["languageID"]]."|";
											} else {
												if (!array_key_exists("extrafield".$extraFieldsArray[$f]["extraFieldID"],$cartContents[$x])) {
													$cartContents[$x]["extrafield".$extraFieldsArray[$f]["extraFieldID"]] = "";
												}
												@$cartContents[$x]["extrafield".$extraFieldsArray[$f]["extraFieldID"]] .= $extraVals[$z]["content"]."|";
											}
										}
									}
								}
							}
							break;						
					}
				}
			}
		}
		
		$cartContents = processCartDefaultDiscount($cartMain["accTypeID"],$cartContents);
		
		$cartMain["products"] = (is_array($cartContents)) ? $cartContents : null;

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

	function cartRemoveItem($unqID) {
		global $dbA,$tableCartsContents,$cartMain,$cartContents,$cartID;
		$result = $dbA->query("delete from $tableCartsContents where cartID='$cartID' and uniqueID=$unqID");
		for ($f = 0; $f < count($cartContents); $f++) {
			if ($cartContents[$f]["uniqueID"] == $unqID) {
				$cartContents = deleteKey($cartContents,$f); 
				break;
			}
		}
		if (is_array($cartContents)) {
			$cartMain["products"] = $cartContents;
		} else{
			$cartMain["products"] = null;
		}
		cartDoCombineCalc();
	}

	function cartUpdateItems($formArray,$inOrderEditing = FALSE) {
		global $dbA,$tableCartsContents,$extraFieldsArray,$cartContents,$cartMain,$cartID;
		for ($f = 0; $f<count($cartContents); $f++) {
			$thisUnique = $cartContents[$f]["uniqueID"];
			$thisQty = makeInteger(getGENERIC("qty".$thisUnique,$formArray));
			if (($thisQty != $cartContents[$f]["qty"] && $thisQty > 0)) {
				$stockFields = "";
				if (is_array($extraFieldsArray)) {
					for ($g = 0; $g < count($extraFieldsArray); $g++) {
						$thisEFID = @$cartContents[$f]["extrafieldid".$extraFieldsArray[$g]["extraFieldID"]];
						if ($extraFieldsArray[$g]["type"] == "USERINPUT") {
						} else {
							$thisEFContent = @$cartContents[$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]];
							if (!empty($thisEFContent)) {
								$splitsID = explode("|",$thisEFID);
								$splitsContent = explode("|",$thisEFContent);
								for ($h = 0; $h < count($splitsID); $h++) {
									if (!empty($splitsID)) {
										$stockFields[] = array("extraFieldID"=>$extraFieldsArray[$g]["extraFieldID"],"exvalID"=>$splitsID[$h],"content"=>$splitsContent[$h]);
									}
								}
							}
						}
					}
				}
				if ($cartContents[$f]["minQty"] > 0 && $thisQty < $cartContents[$f]["minQty"]) {
					$thisQty=$cartContents[$f]["minQty"];
				}
				if ($cartContents[$f]["maxQty"] > 0 && $thisQty > $cartContents[$f]["maxQty"]) {
					$thisQty=$cartContents[$f]["maxQty"];
				}
				$newQty = validateStockQty($cartContents[$f]["productID"],$thisQty,$stockFields);
				if ($inOrderEditing == TRUE) {
					if ($newQty < $cartContents[$f]["qty"] && $newQty == 0) {
						$newQty = $cartContents[$f]["qty"];
					}
				}
				if ($newQty < $thisQty) {
					$limitedStock = "Y";
				} else {
					$limitedStock = "N";
				}
				$doNotUpdate = false;
				if ($cartContents[$f]["minQty"] > 0 && $newQty < $cartContents[$f]["minQty"] && @$cartContents[$f]["error"] == "LIMITEDSTOCK") {
					$doNotUpdate = true;
				}
				if ($cartContents[$f]["maxQty"] > 0 && $newQty > $cartContents[$f]["maxQty"] && @$cartContents[$f]["error"] == "LIMITEDSTOCK") {
					$doNotUpdate = true;
				}
				if ($doNotUpdate == false) {
					$result = $dbA->query("update $tableCartsContents set qty=$newQty,limitedStock='$limitedStock' where uniqueID=$thisUnique");
					$cartContents[$f]["qty"]=$newQty;
					cartDoSingleCalc($f);
				}
			}
		}
		cartDoCombineCalc();
	}

	function cartAddItem($xProd,$xQty,$formArray) {
		global $dbA,$tableCartsContents,$tableProducts,$tableExtraFields,$tableCombinations,$cartMain,$cartID,$currArray,$cartContents;
		$validAdd = true;
		if ($xProd == -1) { $validAdd = false; }
		if ($xQty < 1) { $xQty = 1; }
		$pResult = $dbA->query("select * from $tableProducts where productID=$xProd");
		$pCount = $dbA->count($pResult);
		if ($pCount == 0) {
			$validAdd = false;
		} else {
			$pRecord = $dbA->fetch($pResult);
		}		
		$stockLevel = $pRecord["scLevel"];
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
						$checkString = "";
						$checkContent = "";
						$matchString = "(extrafield".$extraFieldsArray[$f]["extraFieldID"]." = 0";
						foreach($formArray as $k=>$v) {
							if (substr($k,0,strlen($extraFieldsArray[$f]["name"])) == $extraFieldsArray[$f]["name"]) {
								if ($v != "") {
									$checkString .= $v."|";
									$efValue = retrieveExtraFieldValue($v);
									$checkContent .= $efValue."|";
									$matchString .= " or extrafield".$extraFieldsArray[$f]["extraFieldID"]." = $v";
								}
							}
						}
						$matchString .=")";
						$orderPart .= ", extrafield".$extraFieldsArray[$f]["extraFieldID"];
						$extraFieldValues[] = array("id"=>$extraFieldsArray[$f]["extraFieldID"],"value"=>$checkString,"content"=>$checkContent);
						$thisValue = $checkString;
						break;
					case "SELECT":
					case "RADIOBUTTONS":
						$thisValue = getGENERIC($extraFieldsArray[$f]["name"],$formArray);
						$matchString = "(extrafield".$extraFieldsArray[$f]["extraFieldID"]." = 0 ";
						if ($thisValue != "") {
							$matchString .= "or extrafield".$extraFieldsArray[$f]["extraFieldID"]." = $thisValue";
						}
						$matchString .= ")";
						$orderPart .= ", extrafield".$extraFieldsArray[$f]["extraFieldID"];
						$theContent = retrieveExtraFieldValue(getGENERIC($extraFieldsArray[$f]["name"],$formArray));
						$extraFieldValues[] = array("id"=>$extraFieldsArray[$f]["extraFieldID"],"value"=>$thisValue,"content"=>$theContent);
						break;
				}
				switch ($extraFieldsArray[$f]["type"]) {
					case "USERINPUT":
						if ($pRecord["extrafield".$extraFieldsArray[$f]["extraFieldID"]] == 2 && makeSafe(getGENERIC($extraFieldsArray[$f]["name"],$formArray)) == "") {
							return array("error"=>"USERINPUT","xInpR"=>$extraFieldsArray[$f]["extraFieldID"]);
						}						
						break;
					case "SELECT":
					case "RADIOBUTTONS":
						if ($thisValue == "") {
							if (doExtraFieldValuesExist($xProd,$extraFieldsArray[$f]["extraFieldID"])) {
								return array("error"=>"EXTRAFIELDS");
							}
						}
				}
				if ($fullMatch != "") {
					if ($matchString != "") { $fullMatch .= " AND ".$matchString; }
				} else {
					if ($matchString != "") { $fullMatch = $matchString; }
				}
			}
		}
		$validAdd = true;
		if ($xProd == 0) {
			$validAdd = false;
		}
		if ($fullMatch == "") {
			$result = $dbA->query("select * from $tableCombinations where productID=$xProd $orderPart");
		} else {
			$result = $dbA->query("select * from $tableCombinations where productID=$xProd and $fullMatch $orderPart");
		}
		$combCount = $dbA->count($result);
		$newCode = "";
		$newWeight = "";
		$newMinQty = -1;
		$newMaxQty = -1;
		$itemExcluded = false;
		$newSupplierCode = "";
		for ($f = 0; $f < $combCount; $f++) {
			$combRecord = $dbA->fetch($result);
			switch ($combRecord["type"]) {
				case "C":
					$newCode = $combRecord["content"];
					break;
				case "W":
					$newWeight = makeDecimal($combRecord["content"]);
					break;
				case "E":
					//this item is excluded, can't add it to basket
					$validAdd = false;
					$itemExcluded = true;
					break;
				case "q":
					$newMinQty = makeInteger($combRecord["content"]);
					break;
				case "Q":
					$newMaxQty = makeInteger($combRecord["content"]);
					break;
				case "S":
					if (makeInteger($combRecord["content"]) < 1 && $combRecord["exclude"] == "Y") {
						$validAdd = false;
						$itemExcluded = true;
					} else {
						$stockLevel = makeInteger($combRecord["content"]);
					}
					break;
				case "U":
					$newSupplierCode = $combRecord["content"];
					break;
			}
		}
		$returnArray = null;
		if ($validAdd == false) {
			if ($itemExcluded == true) {
				$cartMain["error"] = "EXCLUDED";
				$cartMain["productcode"] = $pRecord["code"];
				$cartMain["productname"] = $pRecord["name"];
				$returnArray["thisTemplate"] = "cart.html";
				$returnArray["excluded"] = TRUE;
			} else {
				$returnArray["thisTemplate"] = "index.html";
				$returnArray["excluded"] = FALSE;
			}
		} else {
			$forceCartShow = false;
			$returnArray["pageType"] = "cart";
			$returnArray["thisTemplate"] = "cart.html";
			$rArray = null;
			$extraWhereClause = "";
			if (is_array($extraFieldValues)) {
				for ($f = 0; $f < count($extraFieldValues); $f++) {
					$extraWhereClause .= " and extrafieldid".$extraFieldValues[$f]["id"]."=\"".$extraFieldValues[$f]["value"]."\"";
				}
			}
			if (is_array($extraFieldsArray)) {
				for ($f = 0; $f < count($extraFieldsArray); $f++) {
					switch ($extraFieldsArray[$f]["type"]) {
						case "USERINPUT":
							$extraWhereClause .= " and extrafieldid".$extraFieldsArray[$f]["extraFieldID"]."=\"".makeSafe(getGENERIC($extraFieldsArray[$f]["name"],$formArray))."\"";
							break;
						}
					}				
				}
			$cResult = $dbA->query("select uniqueID,qty,minQty,maxQty from $tableCartsContents where cartID=\"$cartID\"$extraWhereClause and productID=$xProd");
			if ($dbA->count($cResult) == 0) {
				if (is_array($extraFieldsArray)) {
					for ($f = 0; $f < count($extraFieldsArray); $f++) {
						switch ($extraFieldsArray[$f]["type"]) {
							case "USERINPUT":
								if ($pRecord["extrafield".$extraFieldsArray[$f]["extraFieldID"]] > 0) {
									$rArray[] = array("extrafieldid".$extraFieldsArray[$f]["extraFieldID"],makeSafe(getGENERIC($extraFieldsArray[$f]["name"],$formArray)),"S");
								}
								break;
						}
					}
				}
				if ($newMinQty > -1) {
					$pRecord["minQty"] = $newMinQty;
				}
				if ($newMaxQty > -1) {
					$pRecord["maxQty"] = $newMaxQty;
				}
				if ($pRecord["minQty"] > 0 && $xQty < $pRecord["minQty"]) {
					$xQty=$pRecord["minQty"];
				}
				if ($pRecord["maxQty"] > 0 && $xQty > $pRecord["maxQty"]) {
					$xQty=$pRecord["maxQty"];
				}
				$doNotUpdate = false;
				if (retrieveOption("featureStockControl") == 1 && $pRecord["scEnabled"] == "Y" && ($pRecord["scActionZero"] != 0)) {
					//do a stock check to check amounts
					if ($xQty > $stockLevel) {
						if ($stockLevel < 0) {
							$doNotUpdate = true;
							$cartMain["error"] = "OUTOFSTOCK";
							$cartMain["productcode"] = $pRecord["code"];
							$cartMain["productname"] = $pRecord["name"];
							return array("error"=>"OUTOFSTOCK");
						} else {
							$cartMain["requiredqty"] = $xQty;
							$xQty = $stockLevel;
							$cartMain["error"] = "LIMITEDSTOCK";
							$cartMain["productcode"] = $pRecord["code"];
							$cartMain["productname"] = $pRecord["name"];
							$cartMain["actualqty"] = $stockLevel;
							$rArray[] = array("limitedStock","Y","S");
							$returnArray["advisory"] = "LIMITEDSTOCK";
						}
					}
				}
				if ($pRecord["minQty"] > 0 && $xQty < $pRecord["minQty"] && @$cartMain["error"] == "LIMITEDSTOCK") {
					$doNotUpdate = true;
				}
				if ($pRecord["maxQty"] > 0 && $xQty > $pRecord["maxQty"] && @$cartMain["error"] == "LIMITEDSTOCK") {
					$doNotUpdate = true;
				}
				if ($xQty == 0) {
					$cartMain["error"] = "OUTOFSTOCK";
					$cartMain["productcode"] = $pRecord["code"];
					$cartMain["productname"] = $pRecord["name"];
					return array("error"=>"OUTOFSTOCK");
				}
				if ($doNotUpdate == false) {
					$cartContents[]["cartID"] = $cartID;
					$thisCart = count($cartContents)-1;
					$rArray[] = array("cartID",$cartID,"S");			
					$rArray[] = array("productID",$xProd,"N");
					$rArray[] = array("qty",$xQty,"N");	
					$rArray[] = array("date",$cartMain["date"],"N");	
					$rArray[] = array("freeShipping",$pRecord["freeShipping"],"YN");
					$rArray[] = array("minQty",$pRecord["minQty"],"N");
					$rArray[] = array("maxQty",$pRecord["maxQty"],"N");
					$rArray[] = array("supplierID",$pRecord["supplierID"],"N");
					if ($newCode != "") {
						$rArray[] = array("code",$newCode,"S");	
						$cartContents[$thisCart]["code"] = $newCode;
					} else {
						$rArray[] = array("code",$pRecord["code"],"S");	
						$cartContents[$thisCart]["code"] = $pRecord["code"];
					}
					if ($newSupplierCode != "") {
						$rArray[] = array("suppliercode",$newSupplierCode,"S");	
						$cartContents[$thisCart]["supplier"] = $newCode;
					} else {
						$rArray[] = array("suppliercode",$pRecord["suppliercode"],"S");	
						$cartContents[$thisCart]["suppliercode"] = $pRecord["suppliercode"];
					}
					if ($newWeight != "") {
						$rArray[] = array("weight",$newWeight,"S");	
						$cartContents[$thisCart]["weight"] = $newWeight;
					} else {
						$rArray[] = array("weight",$pRecord["weight"],"S");	
						$cartContents[$thisCart]["weight"] = $pRecord["weight"];
					}
					$rArray[] = array("taxrate",$pRecord["taxrate"],"N");	
					$rArray[] = array("isDigital",$pRecord["isDigital"],"YN");	
					$cartContents[$thisCart]["isDigital"] = $pRecord["isDigital"];					
					$cartContents[$thisCart]["productID"] = $xProd;
					$cartContents[$thisCart]["name"] = $pRecord["name"];
					$cartContents[$thisCart]["thumbnail"] = $pRecord["thumbnail"];
					$cartContents[$thisCart]["combineQty"] = $pRecord["combineQty"];
					$cartContents[$thisCart]["qty"] = $xQty;
					for ($f = 0; $f < count($extraFieldValues); $f++) {
						$rArray[] = array("extrafieldid".$extraFieldValues[$f]["id"],$extraFieldValues[$f]["value"],"S");	
						$cartContents[$thisCart]["extrafield".$extraFieldValues[$f]["id"]] = $extraFieldValues[$f]["content"];
						$cartContents[$thisCart]["extrafieldid".$extraFieldValues[$f]["id"]] = $extraFieldValues[$f]["value"];
					}
					for ($f = 0; $f < count($currArray); $f++) {
						$thisPrice = calculatePrice($pRecord["price1"],$pRecord["price".$currArray[$f]["currencyID"]],$currArray[$f]["currencyID"]);
						$rArray[] = array("price".$currArray[$f]["currencyID"],$thisPrice,"D");	
						$cartContents[$thisCart]["price".$currArray[$f]["currencyID"]] = $thisPrice;
					}
					for ($f = 0; $f < count($currArray); $f++) {
						$thisPrice = calculatePrice($pRecord["ooPrice1"],$pRecord["ooPrice".$currArray[$f]["currencyID"]],$currArray[$f]["currencyID"]);
						$rArray[] = array("ooPrice".$currArray[$f]["currencyID"],$thisPrice,"D");	
						$cartContents[$thisCart]["ooPrice".$currArray[$f]["currencyID"]] = $thisPrice;
					}
					$dbA->insertRecord($tableCartsContents,$rArray,0);
					$cartContents[$thisCart]["uniqueID"] = $dbA->lastID();
					cartRecalculateAdvancedPricing($thisCart);
				} else {
					$cartMain["error"] = "STOCKQUANTITY";
					$cartMain["productcode"] = $pRecord["code"];
					$cartMain["productname"] = $pRecord["name"];
					return array("error"=>"STOCKQUANTITY");
				}
			} else {
				$cRecord = $dbA->fetch($cResult);
				$xUnique = $cRecord["uniqueID"];
				$thisCart = -1;
				for ($f = 0; $f < count($cartContents); $f++) {
					if ($cartContents[$f]["uniqueID"] == $xUnique) {
						$thisCart = $f;
					}
				}
				$oldQty = $cRecord["qty"];
				$xQty = $oldQty+$xQty;
				if ($cRecord["minQty"] > 0 && $xQty < $cRecord["minQty"]) {
					$xQty=$cRecord["minQty"];
				}
				if ($cRecord["maxQty"] > 0 && $xQty > $cRecord["maxQty"]) {
					$xQty=$cRecord["maxQty"];
				}
				if (retrieveOption("featureStockControl") == 1 && $pRecord["scEnabled"] == "Y" && $pRecord["scActionZero"] != 0) {
					//do a stock check to check amounts
					if ($xQty > $stockLevel) {
						if ($stockLevel < 0) {
							$doNotUpdate = true;
							$cartMain["error"] = "OUTOFSTOCK";
							$cartMain["productcode"] = $pRecord["code"];
							$cartMain["productname"] = $pRecord["name"];
							return array("error"=>"OUTOFSTOCK");
						} else {
							$cartMain["requiredqty"] = $xQty;
							$xQty = $stockLevel;
							$cartMain["error"] = "LIMITEDSTOCK";
							$cartMain["productcode"] = $pRecord["code"];
							$cartMain["productname"] = $pRecord["name"];
							$cartMain["actualqty"] = $stockLevel;
							$rArray[] = array("limitedStock","Y","S");
							$returnArray["advisory"] = "LIMITEDSTOCK";
						}
					}
				}
				$doNotUpdate = false;
				if ($cRecord["minQty"] > 0 && $xQty < $cRecord["minQty"] && @$cartMain["error"] == "LIMITEDSTOCK") {
					$doNotUpdate = true;
				}
				if ($cRecord["maxQty"] > 0 && $xQty > $cRecord["maxQty"] && @$cartMain["error"] == "LIMITEDSTOCK") {
					$doNotUpdate = true;
				}
				if ($doNotUpdate == false) {
					$rArray[] = array("qty",$xQty,"N");	
					$cartContents[$thisCart]["qty"] = $xQty;
					for ($f = 0; $f < count($currArray); $f++) {
						$thisPrice = calculatePrice($pRecord["price1"],$pRecord["price".$currArray[$f]["currencyID"]],$currArray[$f]["currencyID"]);
						$rArray[] = array("price".$currArray[$f]["currencyID"],$thisPrice,"D");	
						$cartContents[$thisCart]["price".$currArray[$f]["currencyID"]] = $thisPrice;
					}			
					$dbA->updateRecord($tableCartsContents,"uniqueID=$xUnique",$rArray,0);
					cartRecalculateAdvancedPricing($thisCart);
				}
			}
			for ($f = 0; $f<count($cartContents); $f++) {
				if ($cartContents[$f]["combineQty"] == "Y") {
					cartRecalculateAdvancedPricing($f);
				}
			}
			return $returnArray;
		}
	}

	function cartDoCombineCalc() {
		global $cartContents,$cartMain;
		for ($f = 0; $f<count($cartContents); $f++) {
			if ($cartContents[$f]["combineQty"] == "Y") {
				cartRecalculateAdvancedPricing($f);
			}
		}
	}

	function cartDoSingleCalc($f) {
		global $cartContents,$cartMain;
		cartRecalculateAdvancedPricing($f);
	}
	
	function cartHasProducts() {
		global $dbA,$tpl,$tableCartsContents,$cartID;
		$result = $dbA->query("select cartID from $tableCartsContents where cartID=\"$cartID\"");
		if ($dbA->count($result) == 0) {
			$pageType = "cart";
			$thisTemplate = "cart.html";
			doRedirect(configureURL("cart.php"));
			exit;
		}	
	}

	function cartGET_old($mymode=1) {
		global $cartID,$cartCookie;
		if ($cartCookie == true) {
			return "";
		} else {
			if ($mymode == 0) {
				return "&jssCart=$cartID";
			} else {
				return "?jssCart=$cartID";
			}
		}
	}

	function returnDefaultDiscount($accTypeID) {
		$defaultDiscount = getDefaultDiscount($accTypeID);
		if ($defaultDiscount == 0) {
			return "";
		} else {
			$discount = 1-($defaultDiscount/100);
			return "*".$discount;
		}
	}
	
	function processDefaultDiscount($accTypeID,$thePrice,$productArray) {
		$defaultDiscount = getDefaultDiscount($accTypeID);
		if ($defaultDiscount == 0 || $productArray["ignoreDiscounts"] == "Y") {
			return $thePrice;
		} else {
			$discount = 1-($defaultDiscount/100);
			return $thePrice*$discount;
		}		
	}
	
	function processCartDefaultDiscount($accTypeID,$cartContents) {
		global $currArray;
		$defaultDiscount = getDefaultDiscount($accTypeID);
		if ($defaultDiscount == 0) { return $cartContents; }
		for ($f =0; $f < count($cartContents); $f++) {
			if ($cartContents[$f]["lineID"] == 0) {
				for ($g = 0; $g < count($currArray); $g++) {
					$thisPrice = processDefaultDiscount($accTypeID,$cartContents[$f]["price".$currArray[$g]["currencyID"]],$cartContents[$f]);
					$thisPrice = number_format(abs($thisPrice),$currArray[$g]["decimals"],".","");
					$cartContents[$f]["price".$currArray[$g]["currencyID"]] = $thisPrice;
				}
			}
		}
		return $cartContents;
	}
	
	function getDefaultDiscount($accTypeID) {
		global $accTypeArray;
		$defaultDiscount = 0;
		for ($f = 0; $f < count($accTypeArray); $f++) {
			if ($accTypeID == $accTypeArray[$f]["accTypeID"]) {
				$defaultDiscount = $accTypeArray[$f]["defaultDiscount"];
			}
		}
		return $defaultDiscount;
	}	

	function cartRecalculateAdvancedPricing($thisCart) {
		global $dbA,$cartMain,$tableProducts,$tableAdvancedPricing,$extraFieldsArray,$currArray,$tableCartsContents,$cartID,$cartContents;
		$customerAccType = $cartMain["accTypeID"];
		if (is_array($cartContents)) {
			for ($f = $thisCart; $f <= $thisCart; $f++) {
				$thisProductID = $cartContents[$f]["productID"];
				$newPriceArr = null;
				$oneOffPriceArr = null;
				$result = $dbA->query("select * from $tableProducts where productID=$thisProductID");
				if ($dbA->count($result) > 0) {
					$record = $dbA->fetch($result);
				}
				for ($g = 0; $g < count($currArray); $g++) {
					$thisPrice = calculatePrice(@$record["price1"],@$record["price".$currArray[$g]["currencyID"]],$currArray[$g]["currencyID"]);
					$newPriceArr[] = array("currencyID"=>$currArray[$g]["currencyID"],"price"=>$thisPrice);
				}
				for ($g = 0; $g < count($currArray); $g++) {
					$thisPrice = calculatePrice(@$record["ooPrice1"],@$record["ooPrice".$currArray[$g]["currencyID"]],$currArray[$g]["currencyID"]);
					$oneOffPriceArr[] = array("currencyID"=>$currArray[$g]["currencyID"],"price"=>$thisPrice);
				}
				$advArray = null;
				$advArray = cartRetrieveAdvancedPricing($thisProductID);
				if (is_array($advArray)) {
					for ($g = 0; $g < count($advArray); $g++) {
						$applicable = false;
						$thisQty = $cartContents[$f]["qty"];
						if ($cartContents[$f]["combineQty"] == "Y") {
							//we should get the combined quantity here
							$totalQty = 0;
							for ($h = 0; $h < count($cartContents); $h++) {
								if ($cartContents[$f]["productID"] == $cartContents[$h]["productID"]) {
									$totalQty = $totalQty + $cartContents[$h]["qty"];
								}
							}
							$thisQty = $totalQty;
						}
						if ($advArray[$g]["qtyfrom"] != -1 && $advArray[$g]["qtyto"] != -1 && $advArray[$g]["qtyto"] != 0) {
							if ($thisQty >= $advArray[$g]["qtyfrom"] && $thisQty <= $advArray[$g]["qtyto"]) {
								$applicable = true;
							}
						} else {
							$applicable = true;
						}
						$thisapplic = true;
						$foundMatches = 0;
						if (is_array($extraFieldsArray)) {
							for ($h = 0; $h < count($extraFieldsArray); $h++) {
								if ($advArray[$g]["extrafield".$extraFieldsArray[$h]["extraFieldID"]] != "" && $advArray[$g]["extrafield".$extraFieldsArray[$h]["extraFieldID"]] != "0") {
									$splitCheck = explode(";",$advArray[$g]["extrafield".$extraFieldsArray[$h]["extraFieldID"]]);
									$splitapplic = false;
									for ($k = 0; $k < count($splitCheck); $k++) {
										$splitValues = explode("|",$cartContents[$f]["extrafieldid".$extraFieldsArray[$h]["extraFieldID"]]);
										for ($l = 0; $l < count($splitValues); $l++) {
											if ($splitCheck[$k] == $splitValues[$l] && $splitCheck[$k] != "" && $splitValues[$l] != "") {
												$splitapplic = true;
												if ($extraFieldsArray[$h]["type"] == "CHECKBOXES") {
													$foundMatches++;
												}
											}
										}
									}
									if ($splitapplic == true && $thisapplic == true) {
										$thisapplic = true;
									} else {
										$thisapplic = false;
									}
								}
							}
						}
						if ($thisapplic == true && $applicable == true) {
							$applicable = true;
						} else {
							$applicable = false;
						}
						if ($applicable == true) {
							if ($foundMatches == 0) { $foundMatches = 1; }
							for ($i = 0; $i < count($newPriceArr); $i++) {
								for ($m = 1; $m<=$foundMatches; $m++) {
									if ($advArray[$g]["priceType"] == 0) {
										if ($advArray[$g]["percentage"] > 0) {
											$newPriceArr[$i]["price"] = $newPriceArr[$i]["price"] + ($newPriceArr[$i]["price"] * (($advArray[$g]["percentage"]/100)));
										}
										if ($advArray[$g]["percentage"] < 0) {
											$newPriceArr[$i]["price"] = $newPriceArr[$i]["price"] - ($newPriceArr[$i]["price"] * (abs($advArray[$g]["percentage"])/100));
										}
										if ($advArray[$g]["percentage"] == 0) {
											$newPriceArr[$i]["price"] = calculatePrice($advArray[$g]["price1"],$advArray[$g]["price".$newPriceArr[$i]["currencyID"]],$newPriceArr[$i]["currencyID"]);
										}							
									}
									if ($advArray[$g]["priceType"] == 1) {
										if ($advArray[$g]["percentage"] > 0) {
											$newPriceArr[$i]["price"] = $newPriceArr[$i]["price"] + ($newPriceArr[$i]["price"] * (($advArray[$g]["percentage"]/100)));
										}
										if ($advArray[$g]["percentage"] < 0) {
											$newPriceArr[$i]["price"] = $newPriceArr[$i]["price"] - ($newPriceArr[$i]["price"] * (abs($advArray[$g]["percentage"])/100));
										}
										if ($advArray[$g]["percentage"] == 0) {
											$newPriceArr[$i]["price"] = $newPriceArr[$i]["price"] + calculatePrice($advArray[$g]["price1"],$advArray[$g]["price".$newPriceArr[$i]["currencyID"]],$newPriceArr[$i]["currencyID"]);
										}							
									}
									if ($advArray[$g]["priceType"] == 2) {
										if ($advArray[$g]["percentage"] > 0) {
											$newPriceArr[$i]["price"] = $newPriceArr[$i]["price"] - ($newPriceArr[$i]["price"] * (($advArray[$g]["percentage"]/100)));
										}
										if ($advArray[$g]["percentage"] < 0) {
											$newPriceArr[$i]["price"] = $newPriceArr[$i]["price"] - ($newPriceArr[$i]["price"] * (abs($advArray[$g]["percentage"])/100));
										}
										if ($advArray[$g]["percentage"] == 0) {
											$newPriceArr[$i]["price"] = $newPriceArr[$i]["price"] - calculatePrice($advArray[$g]["price1"],$advArray[$g]["price".$newPriceArr[$i]["currencyID"]],$newPriceArr[$i]["currencyID"]);
										}							
									}	
									if ($advArray[$g]["priceType"] == 4) {
										if ($advArray[$g]["percentage"] > 0) {
											$oneOffPriceArr[$i]["price"] = $oneOffPriceArr[$i]["price"] - ($oneOffPriceArr[$i]["price"] * (($advArray[$g]["percentage"]/100)));
										}
										if ($advArray[$g]["percentage"] < 0) {
											$oneOffPriceArr[$i]["price"] = $oneOffPriceArr[$i]["price"] - ($oneOffPriceArr[$i]["price"] * (abs($advArray[$g]["percentage"])/100));
										}
										if ($advArray[$g]["percentage"] == 0) {
											$oneOffPriceArr[$i]["price"] = calculatePrice($advArray[$g]["price1"],$advArray[$g]["price".$oneOffPriceArr[$i]["currencyID"]],$oneOffPriceArr[$i]["currencyID"]);
										}							
									}															
								}
							}
						}
					}
				}
				if ($newPriceArr[0]["price"] != $cartContents[$f]["price1"]) {
					for ($j = 0; $j < count($newPriceArr); $j++) {
						$cartContents[$f]["price".$newPriceArr[$j]["currencyID"]] = $newPriceArr[$j]["price"];
					}
					$rArray = null;
					for ($i = 0; $i < count($newPriceArr); $i++) {
						$rArray[] = array("price".$newPriceArr[$i]["currencyID"],$newPriceArr[$i]["price"],"D");
					}
					$dbA->updateRecord($tableCartsContents,"uniqueID=".$cartContents[$f]["uniqueID"],$rArray,0);
				}	
				if ($oneOffPriceArr[0]["price"] != $cartContents[$f]["ooPrice1"]) {
					for ($j = 0; $j < count($oneOffPriceArr); $j++) {
						$cartContents[$f]["ooPrice".$oneOffPriceArr[$j]["currencyID"]] = $oneOffPriceArr[$j]["price"];
					}
					$rArray = null;
					for ($i = 0; $i < count($oneOffPriceArr); $i++) {
						$rArray[] = array("ooPrice".$oneOffPriceArr[$i]["currencyID"],$oneOffPriceArr[$i]["price"],"D");
					}
					$dbA->updateRecord($tableCartsContents,"uniqueID=".$cartContents[$f]["uniqueID"],$rArray,0);
				}			
			}		
			$cartMain["products"]=$cartContents;
		} else {
			$cartContents = null;
			$cartMain["products"] = null;
		}
	}
	
	function cartRetrieveAdvancedPricing($xProd) {
		global $dbA,$cartMain,$tableProducts,$tableAdvancedPricing,$extraFieldsArray,$tableExtraFieldsValues,$tableExtraFieldsPrices,$currArray;
		$customerAccType = $cartMain["accTypeID"];
		$advArray = $dbA->retrieveAllRecordsFromQuery("select * from $tableAdvancedPricing where (accTypeID = 0 or accTypeID = $customerAccType) and productID=$xProd order by priceType,qtyfrom");		
		$priceArray = "";
		for ($f = 0; $f < count($advArray); $f++) {
			if ($advArray[$f]["priceType"] == 0) {
				$priceArray[]["priceType"] = 0;
				$thisPA = count($priceArray)-1;
				$priceArray[$thisPA]["percentage"] = $advArray[$f]["percentage"];
				for ($g = 0; $g < count($currArray); $g++) {
					$priceArray[$thisPA]["price".$currArray[$g]["currencyID"]] = $advArray[$f]["price".$currArray[$g]["currencyID"]];
				}
				$priceArray[$thisPA]["qtyfrom"] = $advArray[$f]["qtyfrom"];
				$priceArray[$thisPA]["qtyto"] = $advArray[$f]["qtyto"];
				
				if (is_array($extraFieldsArray)) {
					for ($g = 0; $g < count($extraFieldsArray); $g++) {
						$thisExtraValueID = $advArray[$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]];
						$priceArray[$thisPA]["extrafield".$extraFieldsArray[$g]["extraFieldID"]] = $thisExtraValueID;
					}
				}
			}
		}
		
		$efResult = $dbA->query("select * from $tableExtraFieldsValues,$tableExtraFieldsPrices where $tableExtraFieldsValues.exvalID = $tableExtraFieldsPrices.exvalID and productID=$xProd and visible='Y' and (accTypeID = 0 or accTypeID = $customerAccType) order by extraFieldID,position,$tableExtraFieldsValues.exvalID");
		$efCount = $dbA->count($efResult);
		for ($f = 0; $f < $efCount; $f++) {
			$efRecord = $dbA->fetch($efResult);
			$priceArray[]["priceType"] = 1;
			$thisPA = count($priceArray)-1;
			$priceArray[$thisPA]["percentage"] = $efRecord["percent"];
			for ($g = 0; $g < count($currArray); $g++) {
				$priceArray[$thisPA]["price".$currArray[$g]["currencyID"]] = $efRecord["price".$currArray[$g]["currencyID"]];
			}			
			$priceArray[$thisPA]["qtyfrom"] = -1;
			$priceArray[$thisPA]["qtyto"] = -1;
			if (is_array($extraFieldsArray)) {
				for ($g = 0; $g < count($extraFieldsArray); $g++) {
					if ($extraFieldsArray[$g]["extraFieldID"] == $efRecord["extraFieldID"]) {
						$priceArray[$thisPA]["extrafield".$extraFieldsArray[$g]["extraFieldID"]] = $efRecord["exvalID"];				
					} else {
						$priceArray[$thisPA]["extrafield".$extraFieldsArray[$g]["extraFieldID"]] = "";
					}
				}
			}
		}

		for ($f = 0; $f < count($advArray); $f++) {
			if ($advArray[$f]["priceType"] == 2) {
				$priceArray[]["priceType"] = 2;
				$thisPA = count($priceArray)-1;
				$priceArray[$thisPA]["percentage"] = $advArray[$f]["percentage"];
				for ($g = 0; $g < count($currArray); $g++) {
					$priceArray[$thisPA]["price".$currArray[$g]["currencyID"]] = $advArray[$f]["price".$currArray[$g]["currencyID"]];
				}				
				$priceArray[$thisPA]["qtyfrom"] = $advArray[$f]["qtyfrom"];
				$priceArray[$thisPA]["qtyto"] = $advArray[$f]["qtyto"];				
				if (is_array($extraFieldsArray)) {
					for ($g = 0; $g < count($extraFieldsArray); $g++) {
						$priceArray[$thisPA]["extrafield".$extraFieldsArray[$g]["extraFieldID"]] = $advArray[$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]];
					}
				}
			}
		}		
		
		for ($f = 0; $f < count($advArray); $f++) {
			if ($advArray[$f]["priceType"] == 4) {
				$priceArray[]["priceType"] = 4;
				$thisPA = count($priceArray)-1;
				$priceArray[$thisPA]["percentage"] = $advArray[$f]["percentage"];
				for ($g = 0; $g < count($currArray); $g++) {
					$priceArray[$thisPA]["price".$currArray[$g]["currencyID"]] = $advArray[$f]["price".$currArray[$g]["currencyID"]];
				}				
				$priceArray[$thisPA]["qtyfrom"] = -1;
				$priceArray[$thisPA]["qtyto"] = -1;				
				if (is_array($extraFieldsArray)) {
					for ($g = 0; $g < count($extraFieldsArray); $g++) {
						$priceArray[$thisPA]["extrafield".$extraFieldsArray[$g]["extraFieldID"]] = $advArray[$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]];
					}
				}
			}
		}
		return $priceArray;
	}

	function retrieveExtraFieldValue($exvalID) {
		global $dbA,$tableExtraFieldsValues;
		if ($exvalID == "") { return ""; }
		$result = $dbA->query("select * from $tableExtraFieldsValues where exvalID=$exvalID");
		if ($dbA->count($result) > 0) {
			$record = $dbA->fetch($result);
			return $record["content"];
		} else {
			return "";
		}
	}
	
	function doExtraFieldValuesExist($xProd,$xExtraField) {
		global $dbA,$tableExtraFieldsValues,$cartMain;
		$customerAccType = $cartMain["accTypeID"];
		$result = $dbA->query("select * from $tableExtraFieldsValues where productID=$xProd and extraFieldID=$xExtraField and visible='Y' and (accTypeID = 0 or accTypeID = $customerAccType) limit 1");
		if ($dbA->count($result) > 0) {
			return true;
		} else {
			return false;
		}
	}

	function deleteKey($array, $keyvalue){
		$newArray = array();
		for($i=0; $i<count($array); $i++){
			if ($i <> $keyvalue) {
				array_push($newArray, $array[$i]);
			}
		}
		return $newArray;
	}


	function calculateDiscount($totalGoods) {
			global $dbA,$tableDiscounts,$cartMain,$tableOfferCodes;
			//Sort out order total order codes
			$result = $dbA->query("select * from $tableDiscounts where type='G' and (accTypes=';0;' or accTypes=';".$cartMain["accTypeID"].";') order by compvalue1 desc");
			$count = $dbA->count($result);
			$totalQuantity = 0;
			if (is_array($cartMain["products"])) {
				for ($f = 0; $f < count($cartMain["products"]); $f++) {
					$totalQuantity = $totalQuantity + $cartMain["products"][$f]["qty"];
				}
			}
			if ($count == 0) { return 0; }
			for ($f = 0; $f < $count; $f++) {
				$record = $dbA->fetch($result);
				if ($record["compvalue1"] > 0) {
					//This is a goods total trigger
					$compvalue = calculatePrice($record["compvalue1"],$record["compvalue".$cartMain["currencyID"]],$cartMain["currencyID"]);
					if ($compvalue < $totalGoods) {
						//this one is accurate]
						//calculated with a percentage
						$discountPercent = $record["percent"]; //$totalGoods * ($record["percent"]/100);
						return $discountPercent;
					}
				} else {
					//This is a quantity total trigger
					$compvalue = $record["qty"];
					if ($totalQuantity > $compvalue) {
						//this one is accurate]
						//calculated with a percentage
						$discountPercent = $record["percent"]; //$totalGoods * ($record["percent"]/100);
						return $discountPercent;
					}
				}
			}
			return 0;
		}
		
		function calculateOfferCodeTotal($orderTotals) {
			global $dbA,$orderInfoArray,$cartMain,$tableOfferCodes;
			$totalAmount = $orderTotals["goodsTotal"] + @$orderTotals["taxTotal"];
			if (@$orderInfoArray["offerCode"] != "") {
				$result = $dbA->query("select * from $tableOfferCodes where code=\"".@$orderInfoArray["offerCode"]."\"");
				if ($dbA->count($result) == 0) { return FALSE; }
				$oRecord = $dbA->fetch($result);
				if ($oRecord["excludeShipping"] == "N") {
					$totalAmount = $totalAmount + @$orderTotals["shippingTotal"];
					$totalAmount = $totalAmount + @$orderTotals["shippingTax"];
				}
				if ($oRecord["currencyID"] == 0) {
					//This is a percentage
					return ($totalAmount/100) * $oRecord["amount1"];
				} else {
					//This is a flat amount
					//return calculatePrice($oRecord["amount1"],$oRecord["amount".$cartMain["currencyID"]],$cartMain["currencyID"]);
					return $oRecord["amount1"];
				}
			} else {
				return 0;
			}
		}	
		
		function offerCodeValid($offerCode,$orderTotals,$inOrderEditing = TRUE) {
			global $dbA,$cartMain,$tableCarts,$tableOfferCodes,$orderInfoArray,$tableOfferCodesTrans;
			$result = $dbA->query("select * from $tableOfferCodes where code='$offerCode'");
			if ($dbA->count($result) == 0) { return "INVALID"; }
			$oRecord = $dbA->fetch($result);
			if (!$inOrderEditing) {
				if ($oRecord["multiple"] == "N") {
					if (@$_COOKIE["offerCode"] != "") {
						$codesUsed = explode(";",@$_COOKIE["offerCode"]);
						for ($f = 0; $f < count($codesUsed); $f++) {
							if ($codesUsed[$f] == $offerCode) {
								return "USED";
							}
						}
					}
					$result = $dbA->query("select * from $tableOfferCodesTrans where code='$offerCode' and email='".@$orderInfoArray["email"]."'");
					if ($dbA->count($result) > 0) { return "USED"; }
				}
			}
			if ($cartMain["currencyID"] != $oRecord["currencyID"] && $oRecord["currencyID"] != 0) {
				return "CURRENCY";
			}
			if ($oRecord["expiryDate"] != "N") {
				$tDate = date("Ymd");
				if ($oRecord["expiryDate"] < $tDate) {
					return "EXPIRED";
				}
			}
			$goodsEquiv = calculatePrice($oRecord["level1"],$oRecord["level".$cartMain["currencyID"]],$cartMain["currencyID"]);
			if ($goodsEquiv > $orderTotals["goodsTotal"]) {
				return "BELOWVALUE";
			}
			return "OK";
		}
		
		function offerCodeAmount($offerCode,$orderTotals) {
			global $dbA,$cartMain,$tableCarts,$tableOfferCodes;
			$totalAmount = $orderTotals["goodsTotal"] + @$orderTotals["taxTotal"];
			$result = $dbA->query("select * from $tableOfferCodes where code='$offerCode'");
			if ($dbA->count($result) == 0) { return "INVALID"; }
			$oRecord = $dbA->fetch($result);
			if ($oRecord["excludeShipping"] == "N") {
				$totalAmount = $totalAmount + @$orderTotals["shippingTotal"];
				$totalAmount = $totalAmount + @$orderTotals["shippingTax"];
			}
			if ($oRecord["currencyID"] == 0) {
				//This is a percentage
				return ($totalAmount/100) * $oRecord["amount1"];
			} else {
				//This is a flat amount
				return calculatePrice($oRecord["amount1"],$oRecord["amount".$cartMain["currencyID"]],$cartMain["currencyID"]);
			}
			//return calculatePrice($oRecord["amount1"],$oRecord["amount".$cartMain["currencyID"]],$cartMain["currencyID"]);
		}
?>