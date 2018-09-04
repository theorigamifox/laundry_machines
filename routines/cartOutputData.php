<?php
	if (!isset($thisTemplate)) { echo "Not available"; exit; }
	$timeStart = microtime();
	$xProd = (getFORM("xProd") == "") ? 0 : makeInteger(getFORM("xProd"));
	if (!isset($xSec)) { $xSec = (getFORM("xSec") == "") ? 0 : makeInteger(getFORM("xSec")); }
	$xSearch = (getFORM("xSearch") == "") ? "" : makeSafe(getFORM("xSearch"));
	$xPriceFrom = (getFORM("xPriceFrom") == "") ? 0 : makeDecimal(getFORM("xPriceFrom"));
	$xPriceTo = (getFORM("xPriceTo") == "") ? 0 : makeDecimal(getFORM("xPriceTo"));
	$xSort = (getFORM("xSort") == "") ? "0" : makeSafe(getFORM("xSort"));
	$xPage = (getFORM("xPage") == "") ? 1 : makeInteger(getFORM("xPage"));
	
	$pageType = (!is_string(@$pageType)) ? "normal" : $pageType;
	
	if (@$inOrderProcessing == true) {
		$taxRates = retrieveTaxRates(@$orderInfoArray["country"],@$orderInfoArray["county"],@$orderInfoArray["deliveryCountry"],@$orderInfoArray["deliveryCounty"]);
	} else {
		$taxRates = retrieveTaxRates($cartMain["country"],$cartMain["county"],"","");
	}
	
	$xSearch = str_replace('"',"",$xSearch);
	if (array_key_exists("REQUEST_URI",$_SERVER)) {
		$xPageName = @$_SERVER["REQUEST_URI"];
		$xBits =  explode("?",$xPageName);
		if (strstr($xPageName,".php") !== FALSE) {
			$xPageName2 = $xPageName;
		} else {
			$xPageName = "index.php";
			$xPageName2 = $xPageName;
		}
	} else {
		$xPageName = @$_SERVER["SCRIPT_NAME"];
		$xPageName2 = $xPageName;
	}
	$xQueryString =  @$_SERVER["QUERY_STRING"];
	$xURISplit = explode("/",$xPageName);
	for ($f = 0; $f < count($xURISplit); $f++) {
		if (strstr($xURISplit[$f],".php") !== FALSE) {
			$xPageName = $xURISplit[$f];
			$xPageName2 = $xPageName;	
		}  else {
			$xPageName = "index.php";
			$xPageName2 = $xPageName;
		}
	}
	if ($xPageName == "checkout.php") {
		$xPageName = "index.php";
		$xQueryString = "";
	}
	$xQuerySplit = split("&",$xQueryString);
	$xNewQuery = "";
	if ($xQueryString != "") {
		for ($f = 0; $f < count($xQuerySplit); $f++) {
			$xQV = split("=",$xQuerySplit[$f]);
			if (@$xQV[0] != "jssCart" && @$xQV[0] != "xSearch" && (@$xQV[0] != "xCmd" && @$xQV[1] != "cc") && (@$xQV[0] != "xCur") && (@$xQV[0] != "xLang")) {
				if ($xNewQuery != "") {
					$xNewQuery .= "&".@$xQV[0]."=".@$xQV[1];
				} else {
					$xNewQuery = @$xQV[0]."=".@$xQV[1];
				}
			}
		}
	}

	if ($xQueryString == "") {
		$xBits = explode(".php/",@$_SERVER["REQUEST_URI"]);
		if (@$xBits[1] != "" && retrieveOption("useRewriteURLs") == 1) {
			$xSelfReturn = urlencode($xPageName."/".$xBits[1]);
		} else {
			$xSelfReturn = urlencode($xPageName);
		}
	} else {
		$pageBit = explode("?",$xPageName);
		$xSelfReturn = urlencode($pageBit[0]."?".$xQueryString);
	}
	
	if ($xNewQuery != "") {
		$xPageName = $xPageName . "?xSearch=$xSearch&".$xNewQuery;
	} else {
		$xPageName = $xPageName . "?xSearch=$xSearch".$xNewQuery;
	}

	if ($pageType == "product" && retrieveOption("useRewriteURLs") == 1) {
		$xPageName = "product.php?xSearch=$xSearch&xProd=".$xProd;
		$xSelfReturn = urlencode($xPageName);
	}
	if ($pageType == "section"  && retrieveOption("useRewriteURLs") == 1) {
		$xPageName = "section.php?xSearch=$xSearch&xSec=".$xSec;
		$xSelfReturn = urlencode($xPageName);
	}
	if ($pageType == "search"  && retrieveOption("useRewriteURLs") == 1) {
		$xPage = makeInteger(getFORM("xPage"));
		if ($xPage == 0) { $xPage = 1; }
		$xPageName = "search.php?xSearch=$xSearch&xPage=".$xPage;
		$xSelfReturn = urlencode($xPageName);
	}
	
	//$xURISplit = explode("/",$xPageName);
	//$xPageName = $xURISplit[count($xURISplit)-1]."?xSec=$xSec&xProd=$xProd&xSearch=$xSearch&xPage=$xPage&xPriceFrom=$xPriceFrom&xPriceTo=$xPriceTo&xSort=$xSort&";
	
	switch ($cartMain["templateForceCompile"]) {
		case 0:
			$templateMode = retrieveOption("templateCompileMode");
			break;
		case 1:
			$templateMode = 2;
			break;
		case 2:
			$templateMode = 0;
			break;
	}
	//$templateMode = ($cartMain["templateForceCompile"] == 1 && retrieveOption("templateAllowTFC") == 1) ? 2 : retrieveOption("templateCompileMode");

	$tpl = (retrieveOption("shopAvailable") == 0) ? new tSys($cartMain["templateSet"],"unavailable.html",$requiredVars,$templateMode) : new tSys($cartMain["templateSet"],$thisTemplate,$requiredVars,$templateMode);

	if (retrieveOption("stockWarningNotZero") == 0) {
		$scBit = "((scActionZero = 1 and scEnabled='Y' and scLevel > 0) or (scEnabled = 'N') or (scEnabled = 'Y' and scActionZero != 1))";
		//$scBit = "((scEnabled='Y' and scLevel<1 and scActionZero=0) or (scEnabled != 'Y') or (scLevel > 0))";
	} else {
		$scBit = "((scActionZero = 1 and scEnabled='Y' and scLevel > scWarningLevel) or (scEnabled = 'N') or (scEnabled = 'Y' and scActionZero != 1))";
		//$scBit = "((scEnabled='Y' and scLevel<=scWarningLevel and scActionZero=0) or (scEnabled != 'Y') or (scLevel > scWarningLevel))";
	}
	if (retrieveOption("featureStockControl") == 1) {
		$stockControlClause = "$scBit and ($tableProducts.productID > 1) and (accTypes like '%;".$cartMain["accTypeID"].";%' or accTypes like '%;0;%') and (visible = 'Y') and ";
	} else {
		$stockControlClause = "($tableProducts.productID > 1) and (accTypes like '%;".$cartMain["accTypeID"].";%' or accTypes like '%;0;%') and (visible = 'Y') and ";
	}
	//$stockControlClause = (retrieveOption("featureStockControl") == 1) ? "((scEnabled='Y' and scLevel<1 and scActionZero=0) or (scEnabled != 'Y') or (scLevel > 0)) and ($tableProducts.productID > 1) and (accTypes like '%;".$cartMain["accTypeID"].";%' or accTypes like '%;0;%') and (visible = 'Y') and " : "($tableProducts.productID > 1) and (accTypes like '%;".$cartMain["accTypeID"].";%' or accTypes like '%;0;%') and (visible = 'Y') and ";
		
	$sqlPriceSelect = "$tableAdvancedPricing.price1 as advPrice1";
	for ($f=0; $f < count($currArray); $f++) {
		if ($f != 0) {
			$sqlPriceSelect .= ", $tableAdvancedPricing.price".$currArray[$f]["currencyID"]." as advPrice".$currArray[$f]["currencyID"];
		}
	}
	$extraFieldsSelect = "";
	if (is_array($extraFieldsArray)) {
		for ($f=0; $f < count($extraFieldsArray); $f++) {
			switch ($extraFieldsArray[$f]["type"]) {
				case "SELECT":
				case "RADIOBUTTONS":
				case "CHECKBOXES":
					$extraFieldsSelect .= " and $tableAdvancedPricing.extrafield".$extraFieldsArray[$f]["extraFieldID"]." = 0";
					break;
			}
		}
	}
	$advancedPricingJoin = " LEFT JOIN $tableAdvancedPricing ON ($tableProducts.productID=$tableAdvancedPricing.productID and $tableAdvancedPricing.priceType=0 and ($tableAdvancedPricing.accTypeID=0 or $tableAdvancedPricing.accTypeID=".$cartMain["accTypeID"].") $extraFieldsSelect)";
	$advancedPricingSelect = ",$sqlPriceSelect";
		
	for ($z = 0; $z < count($requiredVars); $z++) {
		switch ($requiredVars[$z]) {
			case "section":
				switch (@$pageType) {
					case "normal":
					case "product":
					case "productreviews":
					case "section":
						if ($xSec == 0 && $xProd != 0) {
							$result = $dbA->query("select * from $tableProductsTree where productID = $xProd");
							if ($dbA->count($result) != 0) {
								$thisrecord = $dbA->fetch($result);
								$xSec = $thisrecord["sectionID"];
								if ($xSec == 1) {
									if ($dbA->count($result) != 1) {
										$thisRecord = $dbA->fetch($result);
										$xSec = $thisRecord["sectionID"];
									}
								}
							}
						}
						$secRecord = retrieveSections("select * from $tableSections where sectionID=$xSec","s");
						if (is_array($secRecord)) {
							if (in_array("section.totalproducts",$requiredVars)) {
								$sResult = $dbA->query("select * from $tableProductsTree where sectionID=".$xSec);
								$secRecord["totalproducts"] = $dbA->count($sResult);
							}
							//if ($secRecord["visible"]=="N") { doRedirect(configureURL("index.php")); }
							$secRecord["path"] = generateFullSectionPath($xSec,$rootsectionID);
							$secRecord["rootsectionID"] = $rootsectionID;
							$tpl->addVariable("section",$secRecord);
							if (retrieveOption("overrideAllMeta") == 0 && findCorrectLanguage($secRecord,"metaKeywords") != "") {
								$metaArray = null;
								$metaArray["keywords"] = findCorrectLanguage($secRecord,"metaKeywords");
								$metaArray["description"] = findCorrectLanguage($secRecord,"metaDescription");
								$tpl->addVariable("meta",$metaArray);
							}							
						}
						break;
					default:
						$thisPath[] = getRootSection();
						$daSection["path"] = $thisPath;
						$tpl->addVariable("section",$daSection);					
				}
				break;
			case "giftcertificate":
				$orderInfoArray["form"]["name"] = "giftcertForm";
				$orderInfoArray["form"]["action"] = configureURL("giftcert.php?xCmd=login");
				$orderInfoArray["form"]["onsubmit"] = "";
				$result = $dbA->query("select * from $tableCustomerFields where type='G' order by position,fieldID");
				$count = $dbA->count($result);
				for ($f = 0; $f < $count; $f++) {
					$fRecord = $dbA->fetch($result);
					$fRecord["titleText"] = findCorrectLanguage($fRecord,"titleText");
					$fRecord["validationmessage"] = findCorrectLanguage($fRecord,"validationmessage");
					if ($fRecord["fieldname"] == "certCurrency") {
						$options = null;
						for ($g = 0; $g < count($currArray); $g++) {
							if ($currArray[$g]["checkout"] == "Y") {
								$options[] = array("name"=>$currArray[$g]["name"],"value"=>$currArray[$g]["currencyID"]);
							}
						}
						$fRecord["selected"] = @$orderInfoArray["certCurrency"];
					} else {
						$optionsSplit = explode(";",$fRecord["contentvalues"]);
						$options = null;
						for ($g = 0; $g < count($optionsSplit); $g++) {
							if (chop($optionsSplit[$g]) != "") {
								$options[] = array("name"=>$optionsSplit[$g],"value"=>$optionsSplit[$g]);
							}
						}
						$fRecord["selected"] = @$orderInfoArray[$fRecord["fieldname"]];
					}
					$fRecord["options"] = $options;

					if ($fRecord["fieldtype"]=="CHECKBOX") {
						if (@$orderInfoArray[$fRecord["fieldname"]] != "") {
							$fRecord["checked"] = "CHECKED";
						}
					}
					$fRecord["error"] = @$orderInfoArray[$fRecord["fieldname"]."_error"];
					$fRecord["content"] = @$orderInfoArray[$fRecord["fieldname"]];
					$orderInfoArray["details"]["fields"][] = $fRecord;
					$orderInfoArray["details"]["field"][$fRecord["fieldname"]] = $fRecord;
				}							
				$result = $dbA->query("select * from $tableCustomerFields where type='D' and visible=1 and internalOnly=0 and incOrdering=1 order by position,fieldID");
				$count = $dbA->count($result);
				for ($f = 0; $f < $count; $f++) {
					$fRecord = $dbA->fetch($result);
					$fRecord["titleText"] = findCorrectLanguage($fRecord,"titleText");
					$fRecord["validationmessage"] = findCorrectLanguage($fRecord,"validationmessage");
					if ($fRecord["fieldname"] == "deliveryCountry") {
						//read in the country list here
						$countryResult = $dbA->query("select * from $tableCountries where visible='Y' order by position,name");
						$countryCount = $dbA->count($countryResult);
						$options = null;
						for ($g = 0; $g < $countryCount; $g++) {
							$countryRecord = $dbA->fetch($countryResult);
							$options[] = array("name"=>$countryRecord["name"],"value"=>$countryRecord["countryID"]);
						}
											
						if (@array_key_exists("countryID",$customerMain) && @$gcMain["countryID"] != "") {
							$fRecord["selected"] = $customerMain["countryID"];
						} else {
							$fRecord["selected"] = retrieveOption("defaultCountry");
						}							
					} else {
						$optionsSplit = explode(";",$fRecord["contentvalues"]);
						$options = null;
						for ($g = 0; $g < count($optionsSplit); $g++) {
							if (chop($optionsSplit[$g]) != "") {
								$options[] = array("name"=>$optionsSplit[$g],"value"=>$optionsSplit[$g]);
							}
						}
						$fRecord["selected"] = @$orderInfoArray[$fRecord["fieldname"]];
					}
					$fRecord["options"] = $options;

					if ($fRecord["fieldtype"]=="CHECKBOX") {
						if (@$orderInfoArray[$fRecord["fieldname"]] != "") {
							$fRecord["checked"] = "CHECKED";
						}
					}
					$fRecord["error"] = @$orderInfoArray[$fRecord["fieldname"]."_error"];
					$fRecord["content"] = @$orderInfoArray[$fRecord["fieldname"]];
					$orderInfoArray["address"]["fields"][] = $fRecord;
					$orderInfoArray["address"]["field"][$fRecord["fieldname"]] = $fRecord;
				}
				$orderInfoArray["details"]["field"]["messageFormatted"]["content"] = eregi_replace("\r\n","<BR>",@$orderInfoArray["details"]["field"]["message"]["content"]);
				$orderInfoArray["details"]["field"]["amount"]["content"] = formatWithoutCalcPriceInCurrency(@$orderInfoArray["details"]["field"]["certValue"]["content"],@$orderInfoArray["certCurrency"]);
				$tpl->addVariable("giftcertificate",$orderInfoArray);	
				break;				
			case "contactform":
				$contactform["form"]["name"] = "contactForm";
				$contactform["form"]["action"] = configureURL("contact.php?xCmd=send");
				$contactform["form"]["onsubmit"] = "";			
				$result = $dbA->query("select * from $tableCustomerFields where type='F' and visible=1 order by position,fieldID");
				$count = $dbA->count($result);
				for ($f = 0; $f < $count; $f++) {
					$fRecord = $dbA->fetch($result);
					$fRecord["titleText"] = findCorrectLanguage($fRecord,"titleText");
					$fRecord["validationmessage"] = findCorrectLanguage($fRecord,"validationmessage");
					if ($fRecord["fieldtype"]=="SELECT") {
						$optionsSplit = explode(";",$fRecord["contentvalues"]);
						$options = null;
						for ($g = 0; $g < count($optionsSplit); $g++) {
							if (chop($optionsSplit[$g]) != "") {
								$options[] = array("name"=>$optionsSplit[$g],"value"=>$optionsSplit[$g]);
							}
						}
						$fRecord["options"] = $options;
					}
					if ($fRecord["fieldtype"]=="CHECKBOX") {
						if (@$customerMain[$fRecord["fieldname"]] != "") {
							$fRecord["checked"] = "CHECKED";
						}
					}
					$fRecord["error"] = @$contactform[$fRecord["fieldname"]."_error"];
					$fRecord["content"] = @$contactform[$fRecord["fieldname"]];
					$contactform["fields"][] = $fRecord;
					$contactform["field"][$fRecord["fieldname"]] = $fRecord;
				}
				$tpl->addVariable("contactform",$contactform);	
				break;	
			case "orderlist":
				$theQuery="select * from $tableOrdersHeaders where customerID=".$customerMain["customerID"]." order by datetime DESC";
				$result = $dbA->query($theQuery);
				$count = $dbA->count($result);
				$orderArray = "";
				$orderArray["total"] = $count;
				for ($f=0; $f < $count; $f++) {
					$record = $dbA->fetch($result);
					$record["ordernumber"] = $record["orderID"]+retrieveOption("orderNumberOffset");
					$record["orderdate"] = formatDate($record["datetime"]);
					$record["ordertime"] = formatTime(substr($record["datetime"],-6));
					$goodsTotal = $record["goodsTotal"];
					$shippingTotal = $record["shippingTotal"];
					$taxTotal = $record["taxTotal"];
					$discountTotal = $record["discountTotal"];
					$giftCertTotal = $record["giftCertTotal"];
					$orderTotal = $goodsTotal+$shippingTotal+$taxTotal-$discountTotal-$giftCertTotal;
					$record["totals"]["discount"] = formatWithoutCalcPriceInCurrency($discountTotal,$record["currencyID"]);
					$record["totals"]["goods"] = formatWithoutCalcPriceInCurrency($goodsTotal,$record["currencyID"]);
					$record["totals"]["shipping"] = formatWithoutCalcPriceInCurrency($shippingTotal,$record["currencyID"]);
					$record["totals"]["tax"] = formatWithoutCalcPriceInCurrency($taxTotal,$record["currencyID"]);
					$record["totals"]["order"] = formatWithoutCalcPriceInCurrency($orderTotal,$record["currencyID"]);
					$record["totals"]["giftcertificates"] = formatWithoutCalcPriceInCurrency($giftCertTotal,$record["currencyID"]);
					$record["viewlink"] = configureURL("customer.php?xCmd=vieworder&xOid=".$record["ordernumber"]);
					$orderArray["orders"][] = $record;
				}
				$tpl->addVariable("orderlist",$orderArray);
				break;
			case "order":
				$allowShippingAddress = retrieveOption("allowShippingAddress");
				if ($allowShippingAddress == 1) {
					$accID = $cartMain["accTypeID"];
					for ($f = 0; $f < count($accTypeArray); $f++) {
						if ($accID == $accTypeArray[$f]["accTypeID"]) {
							if ($accTypeArray[$f]["allowShippingAddress"] == "N") {
								$optionsArray["allowShippingAddress"] = 0;
							}
						}
					}
				}
				for ($f = 0; $f < count($orderArray["products"]); $f++) {
					$theQty = $orderArray["products"][$f]["qty"];
					$thePrice = $orderArray["products"][$f]["price"];
					$thePriceRounded = roundWithoutCalcDisplay($orderArray["products"][$f]["price"],$orderArray["currencyID"]);
					$theOOPrice = $orderArray["products"][$f]["ooprice"];
					$orderArray["products"][$f]["name"] = $orderArray["products"][$f]["nameNative"];
					$orderArray["products"][$f]["price"] = formatWithoutCalcPriceInCurrency($thePrice,$orderArray["currencyID"]);
					$orderArray["products"][$f]["ooPrice1"] = $theOOPrice;
					$orderArray["products"][$f]["ooprice"] = formatWithoutCalcPriceInCurrency($theOOPrice,$orderArray["currencyID"]);
					$orderArray["products"][$f]["total"] = formatWithoutCalcPriceInCurrency(($thePriceRounded*$theQty)+$theOOPrice,$orderArray["currencyID"]);
					$allExtraFields = "";
					for ($g = 0; $g < count($extraFieldsArray); $g++) {
						$thisExtraField = "";
						switch ($extraFieldsArray[$g]["type"]) {
							case "SELECT":
							case "RADIOBUTTONS":
								$theContent = "";
								for ($i = 0; $i < count($extraFieldList); $i++) {
									if ($orderArray["products"][$f]["lineID"] == $extraFieldList[$i]["lineID"] && $extraFieldsArray[$g]["extraFieldID"] == $extraFieldList[$i]["extraFieldID"]) {
										$theContent = $extraFieldList[$i]["contentNative"];
										break;
									}
								}
								$thisExtraField["name"] = $extraFieldsArray[$g]["name"];
								$thisExtraField["title"] = $extraFieldsArray[$g]["title"];
								$thisExtraField["type"] = $extraFieldsArray[$g]["type"];
								$thisExtraField["content"] = $theContent;
										
								$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["name"] = $extraFieldsArray[$g]["name"];
								$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["title"] = $extraFieldsArray[$g]["title"];
								$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["type"] = $extraFieldsArray[$g]["type"];
								$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["content"] = $theContent;
								$allExtraFields[] = $thisExtraField;
								break;		
							case "USERINPUT":
								$theContent = "";
								for ($i = 0; $i < count($extraFieldList); $i++) {
									if ($orderArray["products"][$f]["lineID"] == $extraFieldList[$i]["lineID"] && $extraFieldsArray[$g]["extraFieldID"] == $extraFieldList[$i]["extraFieldID"]) {
										$theContent = $extraFieldList[$i]["content"];
										break;
									}
								}
								$thisExtraField["name"] = $extraFieldsArray[$g]["name"];
								$thisExtraField["title"] = $extraFieldsArray[$g]["title"];
								$thisExtraField["type"] = $extraFieldsArray[$g]["type"];
								$thisExtraField["content"] = $theContent;
										
								$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["name"] = $extraFieldsArray[$g]["name"];
								$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["title"] = $extraFieldsArray[$g]["title"];
								$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["type"] = $extraFieldsArray[$g]["type"];
								$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["content"] = $theContent;
								$allExtraFields[] = $thisExtraField;
								break;							
							case "CHECKBOXES":
								$optionArray = "";
								$theContent = "";
								for ($i = 0; $i < count($extraFieldList); $i++) {
									if ($orderArray["products"][$f]["lineID"] == $extraFieldList[$i]["lineID"] && $extraFieldsArray[$g]["extraFieldID"] == $extraFieldList[$i]["extraFieldID"]) {
										if ($extraFieldList[$i]["content"] != "") {
											$optionArray[] = array("option"=>$extraFieldList[$i]["contentNative"]);
											$theContent = "Y";
										}
									}
								}
								$thisExtraField["name"] = $extraFieldsArray[$g]["name"];
								$thisExtraField["title"] = $extraFieldsArray[$g]["title"];
								$thisExtraField["type"] = $extraFieldsArray[$g]["type"];
								$thisExtraField["content"] = $theContent;
								$thisExtraField["options"] = $optionArray;
										
								$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["name"] = $extraFieldsArray[$g]["name"];
								$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["title"] = $extraFieldsArray[$g]["title"];
								$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["type"] = $extraFieldsArray[$g]["type"];
								$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["content"] = $theContent;
								$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["options"] = $optionArray;
								$allExtraFields[] = $thisExtraField;
								break;
						}
					}			
					if (is_array($allExtraFields)) {
						$orderArray["products"][$f]["extrafields"] = $allExtraFields;
					}					
				}
				$goodsTotal = $orderArray["goodsTotal"];
				$discountTotal = $orderArray["discountTotal"];
				$shippingTotal = $orderArray["shippingTotal"];
				$taxTotal = $orderArray["taxTotal"];
				$giftCertTotal = $orderArray["giftCertTotal"];
				$orderTotal = $goodsTotal+$shippingTotal+$taxTotal-$discountTotal-$giftCertTotal;
				$orderArray["totals"]["goods"] = formatWithoutCalcPriceInCurrency($goodsTotal,$orderArray["currencyID"]);
				if ($discountTotal > 0) {
					$orderArray["totals"]["isDiscount"] = "Y";
				} else {
					$orderArray["totals"]["isDiscount"] = "N";
				}
				if ($giftCertTotal > 0) {
					$orderArray["totals"]["isGiftCertificate"] = "Y";
				} else {
					$orderArray["totals"]["isGiftCertificate"] = "N";
				}
				$orderArray["totals"]["discount"] = formatWithoutCalcPriceInCurrency($discountTotal,$orderArray["currencyID"]);
				$orderArray["totals"]["shipping"] = formatWithoutCalcPriceInCurrency($shippingTotal,$orderArray["currencyID"]);
				$orderArray["totals"]["tax"] = formatWithoutCalcPriceInCurrency($taxTotal,$orderArray["currencyID"]);
				$orderArray["totals"]["order"] = formatWithoutCalcPriceInCurrency($orderTotal,$orderArray["currencyID"]);
				$orderArray["totals"]["giftcertificates"] = formatWithoutCalcPriceInCurrency($giftCertTotal,$orderArray["currencyID"]);
				if ($orderArray["status"] != "P") {
					$orderArray["resubmitpayment"] = configureURL("process.php?xF=Y&xOid=".$orderArray["ordernumber"]."&xRn=".$orderArray["randID"]);
				}
				$tpl->addVariable("order",$orderArray);
				break;
			case "sections":
				$xPage = makeInteger(getFORM("xPage"));
				if ($xPage > 1 && retrieveOption("sectionSubSectionsPlus") == 0) {
					$secArray = false;
				} else {
					$secArray = retrieveSections("select * from $tableSections where parent=$xSec and visible='Y' and (accTypes like '%;".$cartMain["accTypeID"].";%' or accTypes like '%;0;%') order by position,title","m");
					if (is_array($secArray)) {
						for ($f = 0; $f < count($secArray); $f++) {
							if (in_array("sections.totalproducts",$requiredVars)) {
								$sResult = $dbA->query("select * from $tableProductsTree where sectionID=".$uRecord["sectionID"]);
								$secArray[$f]["totalproducts"] = $dbA->count($sResult);
							}
							$secArray[$f] = changeKeysCase($secArray[$f]);
						}			
					}
				}
				if (is_array($secArray)) {
					$tpl->addVariable("sections",$secArray);			
				}
				break;
			case "rootsection":
				$secArray = retrieveSections("select * from $tableSections where parent=1 and visible='Y' and (accTypes like '%;".$cartMain["accTypeID"].";%' or accTypes like '%;0;%') order by position,title","m");
				if (is_array($secArray)) {
					for ($f = 0; $f < count($secArray); $f++) {
						if (in_array("rootsection.totalproducts",$requiredVars)) {
							$sResult = $dbA->query("select * from $tableProductsTree where sectionID=".$secArray[$f]["sectionID"]);
							$secArray[$f]["totalproducts"] = $dbA->count($sResult);
						}
						if (retrieveOption("rootSectionGetSubs") == 1) {
							$subSecs = retrieveSections("select * from $tableSections where parent=".$secArray[$f]["sectionID"]." and visible='Y' and (accTypes like '%;".$cartMain["accTypeID"].";%' or accTypes like '%;0;%') order by position,title","m");					
							if (is_array($subSecs)) {
								for ($g = 0; $g < count($subSecs); $g++) {
									if (in_array("rootsection.subsections.totalproducts",$requiredVars)) {
										$sResult = $dbA->query("select * from $tableProductsTree where sectionID=".$subSecs[$g]["sectionID"]);
										$subSecs[$g]["totalproducts"] = $dbA->count($sResult);
									}
									$subSecs[$g] = changeKeysCase($subSecs[$g]);
								}
							}
							if (is_array($subSecs)) {
								$secArray[$f]["subsections"] = $subSecs;
								$secArray[$f]["subcount"] = count($subSecs);
							} else {
								$secArray[$f]["subcount"] = 0;
							}
						} else {
							$secArray[$f]["subcount"] = 0;
						}
						$secArray[$f] = changeKeysCase($secArray[$f]);
					}	
				}		
				if (is_array($secArray)) {
					$tpl->addVariable("rootsection",$secArray);			
				}
				break;
			case "currencies":
				$newCurrArray = null;
				for ($f = 0; $f < count($currArray); $f++) {
					if ($currArray[$f]["visible"] == "Y") {
						$currArray[$f]["link"] = configureURL("$xPageName"."&currency=".$currArray[$f]["currencyID"]);
						$newCurrArray[] = $currArray[$f];
					}
				}
				$tpl->addVariable("currencies",$newCurrArray);
				break;
			case "languages":
				$newLangArray = null;
				for ($f = 0; $f < count($langArray); $f++) {
					if ($langArray[$f]["visible"] == "Y") {
						$langArray[$f]["link"] = configureURL("$xPageName"."&language=".$langArray[$f]["languageID"]);
						$newLangArray[] = $langArray[$f];
					}
				}
				$tpl->addVariable("languages",$newLangArray);
				break;				
			case "currency":
				break;
			case "product":
				$theQuery = "select * from $tableProducts where productID=$xProd";
				$productsArray = retrieveProducts($theQuery,$counter,"s");
				if ($productsArray["visible"]=="N" && $productsArray["allowDirect"]=="N") {
					doRedirect(configureURL("index.php"));
				}
				if ($counter != 0) {
					if ($pageType == "product") {
						//ok, we can grab any additional pricing as it's an individual product page
						$theBasePrice = calculatePrice($productsArray["price1"],$productsArray["price".$cartMain["currency"]["currencyID"]],$cartMain["currency"]["currencyID"]);
						$theOOBasePrice = calculatePrice($productsArray["ooPrice1"],$productsArray["ooPrice".$cartMain["currency"]["currencyID"]],$cartMain["currency"]["currencyID"]);
						$advPricing = retrieveAdvancedPricing($xProd,$theBasePrice,$theOOBasePrice,$gotsome,$productsArray,$quantityTable,$combinationsTable,$exclusionsTable,$oneoffTable);
						$productsArray["pricing"]["quantitytable"] = $quantityTable;
						$productsArray["pricing"]["combinationstable"] = $combinationsTable;
						$productsArray["exclusionstable"] = $exclusionsTable;
						$productsArray["pricing"]["oneofftable"] = $oneoffTable;
						if ($gotsome) {
							$tpl->convertText("</head>",$advPricing."</head>");
							$tpl->convertText("</body>","<script language=\"JavaScript\">recalcPrice($xProd);</script></body>");
						}
					}
					
					$tpl->addVariable("product",$productsArray);
					if (retrieveOption("overrideAllMeta") == 0 && findCorrectLanguage($productsArray,"metaKeywords") != "") {
						$metaArray = null;
						$metaArray["keywords"] = findCorrectLanguage($productsArray,"metaKeywords");
						$metaArray["description"] = findCorrectLanguage($productsArray,"metaDescription");
						$tpl->addVariable("meta",$metaArray);
					}
				}
				break;				
			case "products":
				$xPage = makeInteger(getFORM("xPage"));
				if ($xPage == 0) { $xPage = 1; }
				$productLimit = retrieveOption("sectionProductsPerPage");
				$xStart = ($xPage-1)*$productLimit;
				$theQuery = "select $tableProducts.* $advancedPricingSelect from $tableProductsTree,$tableProducts $advancedPricingJoin where $stockControlClause $tableProducts.productID = $tableProductsTree.productID and sectionID=$xSec and visible='Y' group by $tableProducts.productID order by position,name";			
				$result = $dbA->query($theQuery);
				$totalProducts = $dbA->count($result);
								
				$sectionPageArray["page"] = $xPage;
				$sectionPageArray["totalproducts"] = $totalProducts;
				$sectionPageArray["from"] = $xStart+1;
				if ($xStart+$productLimit > $totalProducts) {
					$sectionPageArray["to"] = $totalProducts;
				} else {
					$sectionPageArray["to"] = $xStart+$productLimit;
				}				
				
				if ($totalProducts > $xStart+$productLimit) {
					$sectionPageArray["nextlink"] = createSectionLink($xSec,($xPage+1));
				}
				if ($xStart > 0) {
					$sectionPageArray["previouslink"] = createSectionLink($xSec,($xPage-1));
				}
				$xPagesStart = 0;
				$pagesArray = "";
				$thisPage = 0;
				while ($xPagesStart < $totalProducts) {
					$thisPage++;
					$pagesArray[] = array("page"=>$thisPage,"link"=>createSectionLink($xSec,($thisPage)));
					$xPagesStart = $xPagesStart+$productLimit;
				}
				if (count($pagesArray) > 0) {
					$sectionPageArray["pages"] = $pagesArray;
				}

			
				if ($xSec == 1) {
					$theQuery = $theQuery;
					//$theQuery = "select * from $tableProducts,$tableProductsTree where $stockControlClause $tableProducts.productID = $tableProductsTree.productID and sectionID=$xSec and visible='Y' order by position,name";
				} else {
					$theQuery .= " limit $xStart,$productLimit";
					//$theQuery = "select * from $tableProducts,$tableProductsTree where $stockControlClause $tableProducts.productID = $tableProductsTree.productID and sectionID=$xSec and visible='Y' order by position,name limit $xStart,$productLimit";
				}
				$productsArray = retrieveProducts($theQuery,$counter,"m");
				if ($counter != 0) {
					$tpl->addVariable("products",$productsArray);
					$tpl->addVariable("sectionpages",$sectionPageArray);
				}
				break;
			case "newproducts":
				$theQuery = "select $tableProducts.* $advancedPricingSelect from $tableProductsOptions,$tableProducts $advancedPricingJoin where $stockControlClause $tableProducts.productID = $tableProductsOptions.productID and $tableProductsOptions.type='N' and visible='Y' group by $tableProducts.productID order by $tableProductsOptions.position,name limit ".makeInteger(retrieveOption("newProductsLimit"));
				$productsArray = retrieveProducts($theQuery,$counter,"m",0);
				if ($counter != 0) {
					$tpl->addVariable("newproducts",$productsArray);
				}
				break;	
			case "topproducts":
				$theQuery = "select $tableProducts.* $advancedPricingSelect  from $tableProductsOptions,$tableProducts $advancedPricingJoin where $stockControlClause $tableProducts.productID = $tableProductsOptions.productID and $tableProductsOptions.type='T' and visible='Y' group by $tableProducts.productID order by $tableProductsOptions.position,name limit ".makeInteger(retrieveOption("topProductsLimit"));
				$productsArray = retrieveProducts($theQuery,$counter,"m",0);
				if ($counter != 0) {
					$tpl->addVariable("topproducts",$productsArray);
				}
				break;	
			case "specialoffers":
				$theQuery = "select $tableProducts.* $advancedPricingSelect from $tableProductsOptions,$tableProducts $advancedPricingJoin where $stockControlClause $tableProducts.productID = $tableProductsOptions.productID and $tableProductsOptions.type='S' and visible='Y' group by $tableProducts.productID order by $tableProductsOptions.position,name limit ".makeInteger(retrieveOption("specialOffersLimit"));
				$productsArray = retrieveProducts($theQuery,$counter,"m",0);
				if ($counter != 0) {
					$tpl->addVariable("specialoffers",$productsArray);
				}
				break;										
			case "bestsellers":
				$timeLimit = makeInteger(retrieveOption("bestsellersTimeLimit"));
				if ($timeLimit == 0) {
					$timeClause = "";
				} else {
					$tYear = date("Y");
					$tMonth = date("m");
					$tDay = date("d");
					$timeDate = date("YmdHis",mktime(0,0,1,$tMonth,$tDay-($timeLimit-1),$tYear));
					$timeClause = " and $tableOrdersHeaders.datetime >= \"$timeDate\"";
				}
				switch (retrieveOption("bestsellersCalc")) {
					case "Q":
						$theQuery = "select $tableProducts.*,sum(qty) as totalqty $advancedPricingSelect from $tableOrdersLines,$tableOrdersHeaders,$tableProducts $advancedPricingJoin where $stockControlClause $tableOrdersLines.orderID = $tableOrdersHeaders.orderID and $tableProducts.productID = $tableOrdersLines.productID and $tableProducts.visible='Y' $timeClause group by $tableProducts.productID order by totalqty DESC limit ".makeInteger(retrieveOption("bestsellersLimit"));
						break;
					case "O":
						$theQuery = "select $tableProducts.*,count(*) as totalqty $advancedPricingSelect from $tableOrdersLines,$tableOrdersHeaders,$tableProducts $advancedPricingJoin where $stockControlClause $tableOrdersLines.orderID = $tableOrdersHeaders.orderID and $tableProducts.productID = $tableOrdersLines.productID and $tableProducts.visible='Y' $timeClause group by $tableProducts.productID order by totalqty DESC limit ".makeInteger(retrieveOption("bestsellersLimit"));
						break;
				}
				$productsArray = retrieveProducts($theQuery,$counter,"m",0);
				if ($counter != 0) {
					$tpl->addVariable("bestsellers",$productsArray);
				}
				break;	
			case "recentlyviewed":
				if (retrieveOption("recentViewActivated") == 1) {
					$productsMax = retrieveOption("recentViewProducts");
					$currentList = explode(";",$cartMain["productHistory"]);
					$rvClause = "";
					for ($f = 0; $f < count($currentList); $f++) {
						if (makeInteger($currentList[$f]) > 0) {
							if ($rvClause == "") {
								$rvClause = "($tableProducts.productID = ".$currentList[$f];
							} else {
								$rvClause .= " or $tableProducts.productID = ".$currentList[$f];
							}
						}
					}
					if ($rvClause != "") {
						$rvClause .= ")";
						$theQuery = "select $tableProducts.* $advancedPricingSelect from $tableProducts $advancedPricingJoin where $rvClause and $stockControlClause visible='Y' group by $tableProducts.productID";	
						$productArray = @retrieveProducts($theQuery,$counter,"m",0);
						if ($counter != 0) {
							$productArraySorted = null;
							for ($f = 0; $f < count($currentList); $f++) {
								for ($g = 0; $g < count($productArray); $g++) {
									if ($currentList[$f] == $productArray[$g]["productID"]) {
										$productArraySorted[] = $productArray[$g];
									}
								}
							}
							$rvArray["products"] = $productArraySorted;
						}											
					}

					$sectionsMax = retrieveOption("recentViewSections");
					$currentList = explode(";",$cartMain["sectionHistory"]);
					$rvClause = "";
					for ($f = 0; $f < count($currentList); $f++) {
						if (makeInteger($currentList[$f]) > 0) {
							if ($rvClause == "") {
								$rvClause = "(sectionID = ".$currentList[$f];
							} else {
								$rvClause .= " or sectionID = ".$currentList[$f];
							}
						}
					}
					if ($rvClause != "") {
						$rvClause .= ")";
						$sectionArray = retrieveSections("select * from $tableSections where $rvClause and (accTypes like '%;".$cartMain["accTypeID"].";%' or accTypes like '%;0;%') and visible='Y'","m");	
						if (is_array($sectionArray)) {
							for ($f = 0; $f < count($sectionArray); $f++) {
								$sectionArray[$f]["path"] = generateFullSectionPath($sectionArray[$f]["sectionID"],$rootsectionID);
							}
							$sectionArraySorted = null;
							for ($f = 0; $f < count($currentList); $f++) {
								for ($g = 0; $g < count($sectionArray); $g++) {
									if ($currentList[$f] == $sectionArray[$g]["sectionID"]) {
										$sectionArraySorted[] = $sectionArray[$g];
									}
								}
							}
							$rvArray["sections"] = $sectionArraySorted;
						}											
					}
					
					$tpl->addVariable("recentlyviewed",@$rvArray);
				}
				break;
			case "randomproducts":
				if (retrieveOption("showRandomProducts") == 1) {
					if (@$xSec <= 1 || @$xSec=="") {
						$theQuery = "select productID from $tableProducts where $stockControlClause visible='Y'";
					} else {
						$result = $dbA->query("select sectionID from $tableSections where parent=$xSec");
						$count = $dbA->count($result);
						$sectionBit = "";
						for ($f = 0; $f < $count; $f++) {
							$record = $dbA->fetch($result);
							if ($sectionBit == "") {
								$sectionBit = "( $tableProductsTree.sectionID=".$record["sectionID"]." ";
							} else {
								$sectionBit .= " or $tableProductsTree.sectionID=".$record["sectionID"]." ";
							}
						}
						if ($sectionBit != "") {
							$sectionBit = " or ". $sectionBit;
							$sectionBit .= ") ";
						}
						$theQuery = "select $tableProducts.productID from $tableProducts,$tableProductsTree where $tableProducts.productID = $tableProductsTree.productID and (sectionID=$xSec $sectionBit) and $stockControlClause visible='Y'";
					}
					$result = $dbA->query($theQuery);
					$count = $dbA->count($result);
					srand(seedRandomProducts()); 
					$randArray = null;
					$randClause = "";
					$randMax = makeInteger(retrieveOption("randomProductsMax"));
					if ($count > 0) {
						for ($f = 0; $f < $randMax; $f++) {
							$isUnique = false;
							$foundMe = false;
							$maxIterations = 0;
							$randVal = rand(0,$count-1);
							while ($isUnique == false && $count > $randMax) { 
								for ($g = 0; $g < count($randArray); $g++) {
									if ($randVal == $randArray[$g]) {
										$foundMe = true;
										break;
									}
								}
								if (!$foundMe) {
									$isUnique = true;
									break;
								}
								$maxIterations++;
								if ($maxIterations == 20) { $isUnique = true; }
								if ($isUnique == false) {
									$randVal = rand(0,$count-1);
								}
							}
							$randArray[] = $randVal;
							$rc = $dbA->seek($result,$randVal);
							$record = $dbA->fetch($result);
							if ($randClause == "") {
								$randClause = "($tableProducts.productID = ".$record["productID"]." ";
							} else {
								$randClause .= " or $tableProducts.productID = ".$record["productID"]." ";
							}
						}
						$randClause .= ") ";
						$theQuery = "select $tableProducts.* $advancedPricingSelect from $tableProducts $advancedPricingJoin where $randClause group by $tableProducts.productID";
						$productsArray = @retrieveProducts($theQuery,$counter,"m",0);
						if ($counter != 0) {
							$tpl->addVariable("randomproducts",$productsArray);
						}
					}
				} else {
					$tpl->addVariable("randomproducts",$productsArray);
				}
				break;										
			case "company":
			case "meta":
				$result = $dbA->query("select * from $tableGeneral");
				$uRecord = $dbA->fetch($result);
				$uRecord = changeKeysCase($uRecord);
				$tpl->addVariable("company",$uRecord);
				if (!is_array(@$metaArray) || retrieveOption("overrideAllMeta") == 1) {
					$metaArray = "";
					$metaArray["keywords"] = $uRecord["metakeywords"];
					$metaArray["description"] = $uRecord["metadescription"];
					$tpl->addVariable("meta",$metaArray);
				}
				break;
			case "newsletter":
				$newsletter["join"]["form"]["name"] = "newsletterJoin";
				$newsletter["join"]["form"]["action"] = configureURL("newsletter.php?xCmd=subscribe");
				$newsletter["join"]["form"]["onsubmit"] = "";
				$newsletter["join"]["form"]["emailaddress"] = "xEmailAddress";
				$newsletter["remove"]["link"] = configureURL("newsletter.php?xCmd=unsubscribe&xEmailAddress=".@$newsletter["emailaddress"]);
				$tpl->addVariable("newsletter",$newsletter);
				break;				
			case "customer":
				if (!isset($xFwd)) { $xFwd = ""; }
				$customerMain["login"]["form"]["name"] = "customerLogin";
				if (@$xFwd != "") {
					$xFwd=urlEncode($xFwd);
					$customerMain["login"]["form"]["action"] = configureURL("customer.php?xCmd=login&xFwd=$xFwd");
				} else {
					$customerMain["login"]["form"]["action"] = configureURL("customer.php?xCmd=login");
				}
				if (retrieveOption("customerAccounts") == 1) {
					$customerMain["allowed"] = "Y";
				} else {
					$customerMain["allowed"] = "N";
				}
				$customerMain["login"]["form"]["onsubmit"] = "";
				$customerMain["login"]["form"]["email"] = "xEmailAddress";
				$customerMain["login"]["form"]["password"] = "xCustPassword";
				
				$customerMain["forgotpassword"]["form"]["name"] = "forgotPassword";
				$customerMain["forgotpassword"]["form"]["action"] = configureURL("customer.php?xCmd=fgsend");
				$customerMain["forgotpassword"]["form"]["onsubmit"] = "";
				$customerMain["forgotpassword"]["form"]["email"] = "xEmailAddress";
				if (@$emailError == "Y") {
					$customerMain["forgotpassword"]["error"] = "Y";
				}
				
				$customerMain["review"]["form"]["name"] = "customerReview";
				$customerMain["review"]["form"]["action"] = configureURL("customer.php?xCmd=revadd&xProd=$xProd");
				$customerMain["review"]["form"]["onsubmit"] = "";
				$customerMain["review"]["form"]["rating"] = "xRating";
				$customerMain["review"]["form"]["title"] = "xTitle";
				$customerMain["review"]["form"]["review"] = "xReview";
				$customerMain["review"]["form"]["displayname"] = "xDisplayName";
				
				$customerMain["registerlink"] = configureURL("customer.php?xCmd=register&xFwd=$xFwd");
				$customerMain["logoutlink"] = configureURL("customer.php?xCmd=logout");
				$customerMain["login"]["error"] = @$loginError;
				$customerMain["wishlistlink"]= configureURL("customer.php?xCmd=wlshow");
				$customerMain["homelink"]= configureURL("customer.php?xCmd=account");
				$customerMain["accountlink"]= configureURL("customer.php?xCmd=acshow");
				$customerMain["addresseslink"]= configureURL("customer.php?xCmd=adshow");
				$customerMain["forgottenlink"]= configureURL("customer.php?xCmd=fpshow");
				$customerMain["orderslink"]= configureURL("customer.php?xCmd=orders");
				
				$countryID = @$customerMain["country"];
				$customerMain["country"] = @retrieveCountry($countryID);
				$customerMain["countryID"] = $countryID;
				
				if ($pageType == "customeraddresses") {
					$result = $dbA->query("select * from $tableCustomersAddresses where customerID = $jssCustomer order by deliveryName");
					$aCount = $dbA->count($result);
					$addresses = null;
					for ($f = 0; $f < $aCount; $f++) {
						$aRecord = $dbA->fetch($result);
						$aRecord["editlink"] = configureURL("customer.php?xCmd=adedit&xAid=".$aRecord["addressID"]);
						$aRecord["deletelink"] = configureURL("customer.php?xCmd=addelete&xAid=".$aRecord["addressID"]);
						$addresses[] = $aRecord;
					}
	
					$customerMain["addresses"] = $addresses;
					$customerMain["addressaddlink"] = configureURL("customer.php?xCmd=adadd");
				}
				if ($pageType == "customeraddressesedit" || $pageType == "customeraddressesadd") {
					$customerMain["error"] = @$addressRecord["error"];
					$customerMain["address"]["form"]["name"] = "addressDetails";
					if ($pageType == "customeraddressesedit") {
						$customerMain["address"]["form"]["action"] = configureURL("customer.php?xCmd=adupdate&xAid=$xAid");
						$customerMain["address"]["form"]["type"] = "ADD";
					} else {
						$customerMain["address"]["form"]["action"] = configureURL("customer.php?xCmd=adcreate");
						$customerMain["address"]["form"]["type"] = "EDIT";
					}
					$customerMain["address"]["form"]["onsubmit"] = "addressDetails";
					$result = $dbA->query("select * from $tableCustomerFields where type='D' and visible=1 and internalOnly=0 order by position,fieldID");
					$count = $dbA->count($result);
					$deliveryFields = null;
					for ($f = 0; $f < $count; $f++) {
						$fRecord = $dbA->fetch($result);
						$fRecord["titleText"] = findCorrectLanguage($fRecord,"titleText");
						$fRecord["validationmessage"] = findCorrectLanguage($fRecord,"validationmessage");
						if ($fRecord["fieldtype"]=="SELECT") {
							if ($fRecord["fieldname"] == "deliveryCountry") {
								//read in the country list here
								$countryResult = $dbA->query("select * from $tableCountries where visible='Y' order by position,name");
								$countryCount = $dbA->count($countryResult);
								$options = null;
								for ($g = 0; $g < $countryCount; $g++) {
									$countryRecord = $dbA->fetch($countryResult);
									$options[] = array("name"=>$countryRecord["name"],"value"=>$countryRecord["countryID"]);
								}
								if (@array_key_exists("deliveryCountry",$addressRecord)) {
									$fRecord["selected"] = $addressRecord["deliveryCountry"];
								} else {
									$fRecord["selected"] = retrieveOption("defaultCountry");
								}
							} else {
								$optionsSplit = explode(";",$fRecord["contentvalues"]);
								$options = null;
								for ($g = 0; $g < count($optionsSplit); $g++) {
									if (chop($optionsSplit[$g]) != "") {
										$options[] = array("name"=>$optionsSplit[$g],"value"=>$optionsSplit[$g]);
									}
								}
								$fRecord["selected"] = $addressRecord[$fRecord["fieldname"]];
							}						
							$fRecord["options"] = $options;
						}
						if ($fRecord["fieldtype"]=="CHECKBOX") {
							if (@$addressRecord[$fRecord["fieldname"]] != "") {
								$fRecord["checked"] = "CHECKED";
							}
						}						
						if (isset($addressRecord)) {
							$fRecord["content"] = @$addressRecord[$fRecord["fieldname"]];
						}
						$fRecord["error"] = @$addressRecord[$fRecord["fieldname"]."_error"];
						$customerMain["addressfield"][$fRecord["fieldname"]] = $fRecord;
						$customerMain["addressfields"][] = $fRecord;
					}
				}
				
				$customerMain["details"]["form"]["name"] = "customerDetails";
				if ($pageType == "customernew" || $pageType == "customernewe") {
					$customerMain["details"]["form"]["action"] = configureURL("customer.php?xCmd=create&xFwd=$xFwd");
				} else {
					$customerMain["details"]["form"]["action"] = configureURL("customer.php?xCmd=acupdate&xFwd=$xFwd");
				}
				$customerMain["details"]["form"]["onsubmit"] = "";
				$customerMain["details"]["form"]["email"] = "xEmailAddress";
				$customerMain["details"]["form"]["password"] = "xPassword";
				$customerMain["details"]["form"]["repeatpassword"] = "xRepeatPassword";
								
				$result = $dbA->query("select * from $tableCustomerFields where type='C' and visible=1 and internalOnly=0 order by position,fieldID");
				$count = $dbA->count($result);
				$customerFields = null;
				for ($f = 0; $f < $count; $f++) {
					$fRecord = $dbA->fetch($result);
					$fRecord["titleText"] = findCorrectLanguage($fRecord,"titleText");
					$fRecord["validationmessage"] = findCorrectLanguage($fRecord,"validationmessage");
					if ($fRecord["fieldtype"]=="SELECT") {
						if ($fRecord["fieldname"] == "country") {
							//read in the country list here
							$countryResult = $dbA->query("select * from $tableCountries where visible='Y' order by position,name");
							$countryCount = $dbA->count($countryResult);
							$options = null;
							for ($g = 0; $g < $countryCount; $g++) {
								$countryRecord = $dbA->fetch($countryResult);
								$options[] = array("name"=>$countryRecord["name"],"value"=>$countryRecord["countryID"]);
							}
												
							if (@array_key_exists("countryID",$customerMain) && @$customerMain["countryID"] != "") {
								$fRecord["selected"] = $customerMain["countryID"];
							} else {
								$fRecord["selected"] = retrieveOption("defaultCountry");
							}							
						} else {
							$optionsSplit = explode(";",$fRecord["contentvalues"]);
							$options = null;
							for ($g = 0; $g < count($optionsSplit); $g++) {
								if (chop($optionsSplit[$g]) != "") {
									$options[] = array("name"=>$optionsSplit[$g],"value"=>$optionsSplit[$g]);
								}
							}
							$fRecord["selected"] = @$customerMain[$fRecord["fieldname"]];
						}
						$fRecord["options"] = $options;
					}
					if ($fRecord["fieldtype"]=="CHECKBOX") {
						if (@$customerMain[$fRecord["fieldname"]] != "") {
							$fRecord["checked"] = "CHECKED";
						}
					}
					if ($pageType != "customernew") {
						$fRecord["content"] = stripslashes(@$customerMain[$fRecord["fieldname"]]);
					}
					$fRecord["error"] = @$customerMain[$fRecord["fieldname"]."_error"];
					$customerMain["fields"][] = $fRecord;
					$customerMain["field"][$fRecord["fieldname"]] = $fRecord;
				}
				$customerMain["field"]["newsletter"]["fieldname"]="xNewsletter";
				if (@$customerMain["email"] != "") {
					$newsResult = $dbA->query("select * from $tableNewsletter where emailaddress=\"".@$customerMain["email"]."\"");
					if ($dbA->count($newsResult) > 0) {
						$customerMain["field"]["newsletter"]["content"]="Y";
					} else {
						$customerMain["field"]["newsletter"]["content"]="N";
					}
				} else {
					$customerMain["field"]["newsletter"]["content"]="N";
				}
				$tpl->addVariable("customer",$customerMain);
				break;
			case "ordering":
				if (is_array($orderInfoArray)) {
					foreach ($orderInfoArray as $key => $value) {
						$orderInfoArray[$key] = stripslashes($value);
					}
				}
				if (@$orderingGiftCertificate == true) {
					$allowShippingAddress = retrieveOption("allowShippingAddress");
					if ($allowShippingAddress == 1) {
						$accID = $cartMain["accTypeID"];
						for ($f = 0; $f < count($accTypeArray); $f++) {
							if ($accID == $accTypeArray[$f]["accTypeID"]) {
								if ($accTypeArray[$f]["allowShippingAddress"] == "N") {
									$optionsArray["allowShippingAddress"] = 0;
								}
							}
						}
					}
					$ordering = null;
					$ordering["details"] = @$orderInfoArray;
					if (array_key_exists("ccDeclined",$orderInfoArray)) {
						$ordering["payment"]["ccDeclined"] = @$orderInfoArray["ccDeclined"];
					}
					if ($pageType=="checkoutstep1") {			
						$ordering["form"]["name"] = "checkoutForm";
						$ordering["form"]["action"] = configureURL("giftcert.php?xCmd=s2&xFrom=$xFrom");
					}
					if ($pageType=="checkoutstep2") {
					}
					if ($pageType=="checkoutstep3") {								
						$ordering["form"]["name"] = "checkoutForm";
						$ordering["form"]["action"] = configureURL("giftcert.php?xCmd=s4&xFrom=$xFrom");
						$ordering["form"]["paymentoption"] = "xPaymentID";
						$result = $dbA->query("select * from $tablePaymentOptions where enabled='Y' and (accTypes like '%;0;%' or accTypes like '%;".$cartMain["accTypeID"].";%') order by position,name");

						$count = $dbA->count($result);
						if (@$orderInfoArray["paymentID"] == "") {
							$ordering["payment"]["paymentID"] = 1;
						} else {
							$ordering["payment"]["paymentID"] = $orderInfoArray["paymentID"];
						}
						$paymentoptions = null;
						for ($f = 0; $f < $count; $f++) {
							$pArray = $dbA->fetch($result);
							$pArray["name"] = findCorrectLanguage($pArray,"name");
							if ($pArray["type"] == "CC") {
								$ccResult = $dbA->query("select * from $tableCCProcessing where gateway='".$pArray["gateway"]."'");
								$ccRecord = @$dbA->fetch($ccResult);
								if ($ccRecord["askCC"] == "Y") {
									//this is a credit card field
									$fResult = $dbA->query("select * from $tableCustomerFields where type='CC' and visible='1' order by position,fieldID");
									$fCount = $dbA->count($fResult);
									$cardFields = null;
									for ($g = 0; $g < $fCount; $g++) {
										$fRecord = $dbA->fetch($fResult);
										$fRecord["titleText"] = findCorrectLanguage($fRecord,"titleText");
										$fRecord["validationmessage"] = findCorrectLanguage($fRecord,"validationmessage");
										if ($fRecord["fieldtype"]=="SELECT") {
											$optionsSplit = explode(";",$fRecord["contentvalues"]);
											$options = null;
											for ($h = 0; $h < count($optionsSplit); $h++) {
												if (chop($optionsSplit[$h]) != "") {
													$options[] = array("name"=>$optionsSplit[$h]);
												}
											}
											$fRecord["options"] = $options;
										}
										$fRecord["content"] = @$orderInfoArray[$fRecord["fieldname"]];
										$fRecord["error"] = @$orderInfoArray[$fRecord["fieldname"]."_error"];
										$cardFields["fields"][] = $fRecord;
										$cardFields["field"][$fRecord["fieldname"]] = $fRecord;
									}
									$pArray["input"] = $cardFields;
								}
							}
							
							$paymentoptions[] = $pArray;
						}
						$ordering["payment"]["options"] = $paymentoptions;
					}
					if ($pageType=="checkoutstep4") {
						$ordering["form"]["name"] = "checkoutForm";
						$ordering["form"]["action"] = configureURL("giftcert.php?xCmd=s5&xFrom=$xFrom");
						$ordering["update"]["billing"] = configureURL("giftcert.php?xCmd=s1&xFrom=s4");
						$ordering["update"]["payment"] = configureURL("giftcert.php?xCmd=s3&xFrom=s4");
						$xPaymentID = @$orderInfoArray["paymentID"];
						if (makeInteger($xPaymentID) != 0) {
							$ordering["payment"]["paymentID"] = $xPaymentID;
							$result = $dbA->query("select * from $tablePaymentOptions where enabled='Y' and paymentID=$xPaymentID order by position,name");
							$count = $dbA->count($result);
							$paymentOption = $dbA->fetch($result);	
							$paymentOption["name"] = findCorrectLanguage($paymentOption,"name");
							$ordering["payment"]["option"] = $paymentOption;
						} else {
							$paymentOption["name"] = "None";
							$ordering["payment"]["option"] = $paymentOption;
						}
	
						$result = $dbA->query("select * from $tableCustomerFields where type='O' and visible=1 and internalOnly=0 order by position,fieldID");
						$count = $dbA->count($result);
						$extraFields = null;
						for ($f = 0; $f < $count; $f++) {
							$fRecord = $dbA->fetch($result);
							$fRecord["titleText"] = findCorrectLanguage($fRecord,"titleText");
							$fRecord["validationmessage"] = findCorrectLanguage($fRecord,"validationmessage");
							if ($fRecord["fieldtype"]=="SELECT") {
								$optionsSplit = explode(";",$fRecord["contentvalues"]);
								$options = null;
								for ($g = 0; $g < count($optionsSplit); $g++) {
									if (chop($optionsSplit[$g]) != "") {
										$options[] = array("name"=>$optionsSplit[$g],"value"=>$optionsSplit[$g]);
									}
								}
								$fRecord["selected"] = @$orderInfoArray[$fRecord["fieldname"]];
								$fRecord["options"] = $options;
							}
							if ($fRecord["fieldtype"]=="CHECKBOX") {
								if (@$orderInfoArray[$fRecord["fieldname"]] != "") {
									$fRecord["checked"] = "CHECKED";
								}
							}
							$fRecord["content"] = @$orderInfoArray[$fRecord["fieldname"]];			
							$fRecord["error"] = @$orderInfoArray[$fRecord["fieldname"]."_error"];
							$ordering["extra"]["fields"][] = $fRecord;
							$ordering["extra"]["field"][$fRecord["fieldname"]] = $fRecord;
						}						
					}
					$ordering["noaccount"] = configureURL("giftcert.php?xCmd=s1&xFrom=");
					if (retrieveOption("orderingForceAccount") == 1) {
						$ordering["forceaccount"] = "Y";
					} else {
						$ordering["forceaccount"] = "N";
					}
					$result = $dbA->query("select * from $tableCustomerFields where type='C' and visible=1 and internalOnly=0 and incOrdering=1 order by position,fieldID");
					$count = $dbA->count($result);
					$customerFields = null;
					for ($f = 0; $f < $count; $f++) {
						$fRecord = $dbA->fetch($result);
						$fRecord["titleText"] = findCorrectLanguage($fRecord,"titleText");
						$fRecord["validationmessage"] = findCorrectLanguage($fRecord,"validationmessage");
						if ($fRecord["fieldtype"]=="SELECT") {
							if ($fRecord["fieldname"]=="country") {
								$countryResult = $dbA->query("select * from $tableCountries where visible='Y' order by position,name");
								$countryCount = $dbA->count($countryResult);
								$options = null;
								for ($g = 0; $g < $countryCount; $g++) {
									$countryRecord = $dbA->fetch($countryResult);
									$options[] = array("name"=>$countryRecord["name"],"value"=>$countryRecord["countryID"]);
								}	
								if (@array_key_exists("country",$orderInfoArray) && @$orderInfoArray["country"] != "") {
									$fRecord["selected"] = $orderInfoArray["country"];
								} else {
									$fRecord["selected"] = retrieveOption("defaultCountry");
								}											
							} else {
								$optionsSplit = explode(";",$fRecord["contentvalues"]);
								$options = null;
								for ($g = 0; $g < count($optionsSplit); $g++) {
									if (chop($optionsSplit[$g]) != "") {
										$options[] = array("name"=>$optionsSplit[$g],"value"=>$optionsSplit[$g]);
									}
								}
								$fRecord["selected"] = @$customerMain[$fRecord["fieldname"]];
							}
							$fRecord["options"] = $options;
						}
						if ($fRecord["fieldname"] == "country") {
							$fRecord["content"] = retrieveCountry(@$orderInfoArray[$fRecord["fieldname"]]);
						} else {
							$fRecord["content"] = @$orderInfoArray[$fRecord["fieldname"]];
						}
						if ($fRecord["fieldtype"]=="CHECKBOX") {
							if (@$orderInfoArray[$fRecord["fieldname"]] != "") {
								$fRecord["checked"] = "CHECKED";
							}
						}					
						$fRecord["error"] = @$orderInfoArray[$fRecord["fieldname"]."_error"];
						$ordering["customer"]["fields"][] = $fRecord;
						$ordering["customer"]["field"][$fRecord["fieldname"]] = $fRecord;
					}
					$ordering["customer"]["field"]["email"]["fieldname"] = "xEmailAddress";	
					$ordering["customer"]["field"]["email"]["error"] = @$orderInfoArray["email_error"];	
	
					$ordering["customer"]["field"]["newsletter"]["fieldname"]="xNewsletter";
					if (@$orderInfoArray["newsletter"] == "Y") {
						$ordering["customer"]["field"]["newsletter"]["content"]="Y";
					} else {
						$ordering["customer"]["field"]["newsletter"]["content"]="N";
					}
	
					
					$ordering["details"]["country"] = retrieveCountry(@$ordering["details"]["country"]);
					$ordering["details"]["deliveryCountry"] = retrieveCountry(@$ordering["details"]["deliveryCountry"]);
					$tpl->addVariable("ordering",$ordering);
				} else {
					$allowShippingAddress = retrieveOption("allowShippingAddress");
					if ($allowShippingAddress == 1) {
						$accID = $cartMain["accTypeID"];
						for ($f = 0; $f < count($accTypeArray); $f++) {
							if ($accID == $accTypeArray[$f]["accTypeID"]) {
								if ($accTypeArray[$f]["allowShippingAddress"] == "N") {
									$optionsArray["allowShippingAddress"] = 0;
								}
							}
						}
					}
					$ordering = null;
					$ordering["details"] = @$orderInfoArray;
					if ($pageType=="checkoutstep1") {
						$ordering["shippingchange"]["form"]["name"] = "shippingChange";
						$ordering["shippingchange"]["form"]["action"] = configureURL("checkout.php?xExtra=shipping&xCmd=s1&xFrom=$xFrom");
						$ordering["shippingchange"]["form"]["onsubmit"] = "";
						
						$ordering["form"]["name"] = "checkoutForm";
						$ordering["form"]["action"] = configureURL("checkout.php?xCmd=s2&xFrom=$xFrom");
					}
					if ($pageType=="checkoutstep2") {
						$ordering["selectbillinglink"] = configureURL("checkout.php?xCmd=s3&xFrom=$xFrom&xType=select&xAid=0");
						$ordering["form"]["name"] = "checkoutForm";
						$ordering["form"]["action"] = configureURL("checkout.php?xCmd=s3&xFrom=$xFrom");
						$result = $dbA->query("select * from $tableCustomersAddresses where customerID = $jssCustomer order by deliveryName");
						$aCount = $dbA->count($result);
						$addresses = null;
						for ($f = 0; $f < $aCount; $f++) {
							$aRecord = $dbA->fetch($result);
							$aRecord["selectlink"] = configureURL("checkout.php?xCmd=s3&xFrom=$xFrom&xType=select&xAid=".$aRecord["addressID"]);
							$aRecord["deliveryCountry"] = retrieveCountry($aRecord["deliveryCountry"]);
							$addresses[] = $aRecord;
						}
						$ordering["addresses"] = $addresses;	
	
						$ordering["address"]["form"]["name"] = "addressDetails";
						$ordering["address"]["form"]["action"] = configureURL("checkout.php?xType=new&xCmd=s3&xFrom=$xFrom");
						$ordering["address"]["form"]["type"] = "ADD";
						$ordering["address"]["form"]["onsubmit"] = "addressDetails";
						
						$ordering["shippingchange"]["form"]["name"] = "shippingChange";
						$ordering["shippingchange"]["form"]["action"] = configureURL("checkout.php?xExtra=shipping&xCmd=s2&xFrom=$xFrom");
						$ordering["shippingchange"]["form"]["onsubmit"] = "";
						
						$result = $dbA->query("select * from $tableCustomerFields where type='D' and visible=1 and internalOnly=0 and incOrdering=1 order by position,fieldID");
						$count = $dbA->count($result);
						$deliveryFields = null;
						for ($f = 0; $f < $count; $f++) {
							$fRecord = $dbA->fetch($result);
							$fRecord["titleText"] = findCorrectLanguage($fRecord,"titleText");
							$fRecord["validationmessage"] = findCorrectLanguage($fRecord,"validationmessage");
							if ($fRecord["fieldtype"]=="SELECT") {
								if ($fRecord["fieldname"] == "deliveryCountry") {
									//read in the country list here
									$countryResult = $dbA->query("select * from $tableCountries where visible='Y' order by position,name");
									$countryCount = $dbA->count($countryResult);
									$options = null;
									for ($g = 0; $g < $countryCount; $g++) {
										$countryRecord = $dbA->fetch($countryResult);
										$options[] = array("name"=>$countryRecord["name"],"value"=>$countryRecord["countryID"]);
									}
									if (@array_key_exists("deliveryCountry",$orderInfoArray) && @$orderInfoArray["deliveryCountry"] != "") {
										$fRecord["selected"] = $orderInfoArray["deliveryCountry"];
									} else {
										$fRecord["selected"] = retrieveOption("defaultCountry");
									}									
								} else {
									$optionsSplit = explode(";",$fRecord["contentvalues"]);
									$options = null;
									for ($g = 0; $g < count($optionsSplit); $g++) {
										if (chop($optionsSplit[$g]) != "") {
											$options[] = array("name"=>$optionsSplit[$g],"value"=>$optionsSplit[$g]);
										}
									}
									$fRecord["selected"] = @$orderInfoArray[$fRecord["fieldname"]];
								}						
								$fRecord["options"] = $options;
							}
							if ($fRecord["fieldtype"]=="CHECKBOX") {
								if (@$orderInfoArray[$fRecord["fieldname"]] != "") {
									$fRecord["checked"] = "CHECKED";
								}
							}						
							if (isset($addressRecord)) {
								if (is_array($addressRecord)) {
									$fRecord["content"] = @$addressRecord[$fRecord["fieldname"]];
								}
							}
							$fRecord["error"] = @$orderInfoArray[$fRecord["fieldname"]."_error"];
							$fRecord["content"] = @$orderInfoArray[$fRecord["fieldname"]];
							$ordering["address"]["field"][$fRecord["fieldname"]] = $fRecord;
							$ordering["address"]["fields"][] = $fRecord;
						}	
						$orderng["address"]["field"]["deliveryCountryID"] = @$orderInfoArray["deliveryCountry"];							
					}
					if ($pageType=="checkoutstep3") {
	
						if (array_key_exists("ccDeclined",$orderInfoArray)) {
							$ordering["payment"]["ccDeclined"] = @$orderInfoArray["ccDeclined"];
						}
						$ordering["shippingchange"]["form"]["name"] = "shippingChange";
						$ordering["shippingchange"]["form"]["action"] = configureURL("checkout.php?xExtra=shipping&xCmd=s3&xFrom=$xFrom");
						$ordering["shippingchange"]["form"]["onsubmit"] = "addressDetails";
										
						$ordering["form"]["name"] = "checkoutForm";
						$ordering["form"]["action"] = configureURL("checkout.php?xCmd=s4&xFrom=$xFrom");
						$ordering["form"]["paymentoption"] = "xPaymentID";
						$result = $dbA->query("select * from $tablePaymentOptions where enabled='Y' and (accTypes like '%;0;%' or accTypes like '%;".$cartMain["accTypeID"].";%') order by position,name");
						$count = $dbA->count($result);
						if (@$orderInfoArray["paymentID"] == "") {
							$ordering["payment"]["paymentID"] = 1;
						} else {
							$ordering["payment"]["paymentID"] = $orderInfoArray["paymentID"];
						}
						$paymentoptions = null;
						for ($f = 0; $f < $count; $f++) {
							$pArray = $dbA->fetch($result);
							$pArray["name"] = findCorrectLanguage($pArray,"name");
							if ($pArray["type"] == "CC") {
								$ccResult = $dbA->query("select * from $tableCCProcessing where gateway='".$pArray["gateway"]."'");
								$ccRecord = @$dbA->fetch($ccResult);
								if ($ccRecord["askCC"] == "Y") {
									//this is a credit card field
									$fResult = $dbA->query("select * from $tableCustomerFields where type='CC' and visible='1' order by position,fieldID");
									$fCount = $dbA->count($fResult);
									$cardFields = null;
									for ($g = 0; $g < $fCount; $g++) {
										$fRecord = $dbA->fetch($fResult);
										$fRecord["titleText"] = findCorrectLanguage($fRecord,"titleText");
										$fRecord["validationmessage"] = findCorrectLanguage($fRecord,"validationmessage");
										if ($fRecord["fieldtype"]=="SELECT") {
											$optionsSplit = explode(";",$fRecord["contentvalues"]);
											$options = null;
											for ($h = 0; $h < count($optionsSplit); $h++) {
												if (chop($optionsSplit[$h]) != "") {
													$options[] = array("name"=>$optionsSplit[$h]);
												}
											}
											$fRecord["options"] = $options;
										}
										$fRecord["content"] = @$orderInfoArray[$fRecord["fieldname"]];
										$fRecord["error"] = @$orderInfoArray[$fRecord["fieldname"]."_error"];
										$cardFields["fields"][] = $fRecord;
										$cardFields["field"][$fRecord["fieldname"]] = $fRecord;
									}
									$pArray["input"] = $cardFields;
								}
							}
							
							$paymentoptions[] = $pArray;
						}
						$ordering["payment"]["options"] = $paymentoptions;
						$ordering["payment"]["giftCertificateError"] = @$orderInfoArray["giftCertificateError"];
						$ordering["payment"]["offerCodeError"] = @$orderInfoArray["offerCodeError"];
						$ordering["payment"]["giftCertField"] = "xGiftCertSerial";
					}
					if ($pageType=="checkoutstep4") {
						$ordering["shippingchange"]["form"]["name"] = "shippingChange";
						$ordering["shippingchange"]["form"]["action"] = configureURL("checkout.php?xExtra=shipping&xCmd=s4&xFrom=s4");
						$ordering["shippingchange"]["form"]["onsubmit"] = "addressDetails";
										
						$ordering["form"]["name"] = "checkoutForm";
						$ordering["form"]["action"] = configureURL("checkout.php?xCmd=s5&xFrom=$xFrom");
						$ordering["update"]["billing"] = configureURL("checkout.php?xCmd=s1&xFrom=s4");
						$ordering["update"]["shipping"] = configureURL("checkout.php?xCmd=s2&xFrom=s4");
						$ordering["update"]["payment"] = configureURL("checkout.php?xCmd=s3&xFrom=s4");
						$xPaymentID = @$orderInfoArray["paymentID"];
						if (makeInteger($xPaymentID) != 0) {
							$ordering["payment"]["paymentID"] = $xPaymentID;
							$result = $dbA->query("select * from $tablePaymentOptions where enabled='Y' and paymentID=$xPaymentID order by position,name");
							$count = $dbA->count($result);
							$paymentOption = $dbA->fetch($result);	
							$paymentOption["name"] = findCorrectLanguage($paymentOption,"name");
							$ordering["payment"]["option"] = $paymentOption;
						} else {
							$paymentOption["name"] = "None";
							$ordering["payment"]["option"] = $paymentOption;
						}
	
						$result = $dbA->query("select * from $tableCustomerFields where type='O' and visible=1 and internalOnly=0 order by position,fieldID");
						$count = $dbA->count($result);
						$extraFields = null;
						for ($f = 0; $f < $count; $f++) {
							$fRecord = $dbA->fetch($result);
							$fRecord["titleText"] = findCorrectLanguage($fRecord,"titleText");
							$fRecord["validationmessage"] = findCorrectLanguage($fRecord,"validationmessage");
							if ($fRecord["fieldtype"]=="SELECT") {
								$optionsSplit = explode(";",$fRecord["contentvalues"]);
								$options = null;
								for ($g = 0; $g < count($optionsSplit); $g++) {
									if (chop($optionsSplit[$g]) != "") {
										$options[] = array("name"=>$optionsSplit[$g],"value"=>$optionsSplit[$g]);
									}
								}
								$fRecord["selected"] = @$orderInfoArray[$fRecord["fieldname"]];
								$fRecord["options"] = $options;
							}
							if ($fRecord["fieldtype"]=="CHECKBOX") {
								if (@$orderInfoArray[$fRecord["fieldname"]] != "") {
									$fRecord["checked"] = "CHECKED";
								}
							}
							$fRecord["content"] = @$orderInfoArray[$fRecord["fieldname"]];			
							$fRecord["error"] = @$orderInfoArray[$fRecord["fieldname"]."_error"];
							$ordering["extra"]["fields"][] = $fRecord;
							$ordering["extra"]["field"][$fRecord["fieldname"]] = $fRecord;
						}					
					}
					if (@$orderingGiftCertificate == true) {
						$ordering["noaccount"] = configureURL("giftcert.php?xCmd=s1&xFrom=");
					} else {
						$ordering["noaccount"] = configureURL("checkout.php?xCmd=s1&xFrom=");
					}
					if (retrieveOption("orderingForceAccount") == 1) {
						$ordering["forceaccount"] = "Y";
					} else {
						$ordering["forceaccount"] = "N";
					}
					$result = $dbA->query("select * from $tableCustomerFields where type='C' and visible=1 and internalOnly=0 and incOrdering=1 order by position,fieldID");
					$count = $dbA->count($result);
					$customerFields = null;
					for ($f = 0; $f < $count; $f++) {
						$fRecord = $dbA->fetch($result);
						$fRecord["titleText"] = findCorrectLanguage($fRecord,"titleText");
						$fRecord["validationmessage"] = findCorrectLanguage($fRecord,"validationmessage");
						if ($fRecord["fieldtype"]=="SELECT") {
							if ($fRecord["fieldname"]=="country") {
								$countryResult = $dbA->query("select * from $tableCountries where visible='Y' order by position,name");
								$countryCount = $dbA->count($countryResult);
								$options = null;
								for ($g = 0; $g < $countryCount; $g++) {
									$countryRecord = $dbA->fetch($countryResult);
									$options[] = array("name"=>$countryRecord["name"],"value"=>$countryRecord["countryID"]);
								}	
								if (@array_key_exists("country",$orderInfoArray) && @$orderInfoArray["country"] != "") {
									$fRecord["selected"] = $orderInfoArray["country"];
								} else {
									$fRecord["selected"] = retrieveOption("defaultCountry");
								}											
							} else {
								$optionsSplit = explode(";",$fRecord["contentvalues"]);
								$options = null;
								for ($g = 0; $g < count($optionsSplit); $g++) {
									if (chop($optionsSplit[$g]) != "") {
										$options[] = array("name"=>$optionsSplit[$g],"value"=>$optionsSplit[$g]);
									}
								}
								$fRecord["selected"] = @$customerMain[$fRecord["fieldname"]];
							}
							$fRecord["options"] = $options;
						}
						if ($fRecord["fieldname"] == "country") {
							$fRecord["content"] = retrieveCountry(@$orderInfoArray[$fRecord["fieldname"]]);
						} else {
							$fRecord["content"] = @$orderInfoArray[$fRecord["fieldname"]];
						}
						if ($fRecord["fieldtype"]=="CHECKBOX") {
							if (@$orderInfoArray[$fRecord["fieldname"]] != "") {
								$fRecord["checked"] = "CHECKED";
							}
						}					
						$fRecord["error"] = @$orderInfoArray[$fRecord["fieldname"]."_error"];
						$ordering["customer"]["fields"][] = $fRecord;
						$ordering["customer"]["field"][$fRecord["fieldname"]] = $fRecord;
					}
					$ordering["customer"]["field"]["email"]["fieldname"] = "xEmailAddress";	
					$ordering["customer"]["field"]["email"]["error"] = @$orderInfoArray["email_error"];	
	
					$ordering["customer"]["field"]["newsletter"]["fieldname"]="xNewsletter";
					if (@$orderInfoArray["newsletter"] == "Y") {
						$ordering["customer"]["field"]["newsletter"]["content"]="Y";
					} else {
						$ordering["customer"]["field"]["newsletter"]["content"]="N";
					}
	
					
					$ordering["details"]["country"] = retrieveCountry(@$ordering["details"]["country"]);
					$ordering["details"]["deliveryCountry"] = retrieveCountry(@$ordering["details"]["deliveryCountry"]);
					$ordering["payment"]["offerCode"] = @$orderInfoArray["offerCode"];
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
					$totalWeight = 0;
					for ($f = 0; $f < count($cartMain["products"]); $f++) {
						$totalWeight = $totalWeight + ($cartMain["products"][$f]["qty"] * $cartMain["products"][$f]["weight"]);
					}
					$atype = $cartMain["accTypeID"];
					if ($atype == 0) {
						$atype = -1;
					}
					$priceBit = "";
					$orderTotals = calculateOrderTotals();
					$goodsCheckTotal = $orderTotals["goodsTotal"];
					if ($cartMain["currency"]["currencyID"] == 1 || $cartMain["currency"]["useexchangerate"] == "N") {
						$priceBit = "and ($tableShippingTypes.highprice".$cartMain["currency"]["currencyID"]." = 0 or $tableShippingTypes.highprice".$cartMain["currency"]["currencyID"]." > $goodsCheckTotal) and ($tableShippingTypes.lowprice".$cartMain["currency"]["currencyID"]." = 0 or $goodsCheckTotal > $tableShippingTypes.lowprice".$cartMain["currency"]["currencyID"].")";
					} else {
						if ($cartMain["currency"]["useexchangerate"] == "Y") {
							$goodsCheckTotalBase = calculatePriceInBase($goodsCheckTotal);
							$priceBit = "and ($tableShippingTypes.highprice1 = 0 or $tableShippingTypes.highprice1 > $goodsCheckTotalBase) and ($tableShippingTypes.lowprice1 = 0 or $goodsCheckTotalBase > $tableShippingTypes.lowprice1)";
						}
					}


					$shippingArray = $dbA->retrieveAllRecordsFromQuery("select $tableShippingTypes.* from $tableShippingTypes,$tableShippingRates where ($tableShippingTypes.weight = 0 or $tableShippingTypes.weight > $totalWeight) and ($tableShippingTypes.lowweight = 0 or $totalWeight > $tableShippingTypes.lowweight) $priceBit and $tableShippingRates.shippingID = $tableShippingTypes.shippingID and $tableShippingRates.zoneID = $zoneID and accTypeID=0 or accTypeID=".$atype." group by $tableShippingTypes.shippingID");
					$shippingID = @$orderInfoArray["shippingID"];
					if ($shippingID == "") {
						$shippingID = 1;
					}
					for ($f = 0; $f < count($shippingArray); $f++) {
						$shippingArray[$f]["name"] = findCorrectLanguage($shippingArray[$f],"name");
						if ($shippingArray[$f]["shippingID"] == $shippingID) {
							$shippingArray[$f]["selected"] = "Y";
						}
					}
					$ordering["shipping"]["types"] = $shippingArray;
					$tpl->addVariable("ordering",$ordering);
				}				
				break;
			case "browser":
				$browser["short"] = $xBrowserShort;
				$browser["long"] = $xBrowserLong;
				$tpl->addVariable("browser",$browser);
				break;
			case "shop":
				$shop["baseDir"] = findBaseDir($xPageName2);
				$shop["home"] = configureURL("index.php");
				$tpl->addVariable("shop",$shop);
				break;
			case "wishlist":
				$wishlist = null;
				$wishlistID = $customerMain["wishlistID"];
				$theQuery = "select $tableProducts.*,$tableWishlists.* $advancedPricingSelect from $tableWishlists,$tableProducts $advancedPricingJoin where $stockControlClause $tableProducts.productID = $tableWishlists.productID and $tableProducts.visible = 'Y' and $tableWishlists.wishlistID = '$wishlistID' group by $tableProducts.productID order by code,name";
				$productArray = retrieveProducts($theQuery,$counter,"m",0);
				if ($counter != 0) {
					for ($f = 0; $f < count($productArray); $f++) {
						$productArray[$f]["deletelink"] = configureURL("customer.php?xCmd=wldelete&xProd=".$productArray[$f]["productID"]);
						$productArray[$f]["qtyboxname"] = "qty".$productArray[$f]["uniqueID"];
						$productArray[$f]["commentboxname"] = "comment".$productArray[$f]["uniqueID"];
						$productArray[$f]["comment"] = str_replace("\"","&quot;",$productArray[$f]["comment"]);
						$productArray[$f]["addToBasketLink"] = configureURL("cart.php?xCmd=add&xProd=".$productArray[$f]["productID"]."&qty".$productArray[$f]["productID"]."=".$productArray[$f]["qty"]);
					}
					$wishlist["products"] = $productArray;
				}
				$wishlist["form"]["name"] = "wishlistForm";
				$wishlist["form"]["action"] = configureURL("customer.php?xCmd=wlupdate");
				$wishlist["emptylink"] = configureURL("customer.php?xCmd=wlclear");
				$wishlist["send"]["form"]["name"] = "wishlistSendForm";
				$wishlist["send"]["form"]["action"] = configureURL("customer.php?xCmd=wlsend");
				$wishlist["send"]["form"]["emaillist"] = "xEmailList";
				$tpl->addVariable("wishlist",$wishlist);
				break;
			case "cart":
				$cartMain["shippingEnabled"] = (retrieveOption("shippingEnabled") == 1) ? "Y" : "N";
				$cartMain["taxEnabled"] = (retrieveOption("taxEnabled") == 1) ? "Y" : "N";
				$cartMain["link"] = configureURL("cart.php");
				if (array_key_exists("products",$cartMain)) {
					$totalCost = 0;
					$totalPrice = 0;
					$totalTax = 0;
					$totalQty = 0;
					$shippingTotalGoods = 0;
					$shippingTotalWeight = 0;
					$shippingTotalQty = 0;
					$goodsTotal = 0;
					if (is_array($cartContents)) {
						for ($f = 0; $f < count($cartMain["products"]); $f++) {
							$cartMain["products"][$f]["name"] = findCorrectLanguage($cartMain["products"][$f],"name");
							$thePrice = $cartMain["products"][$f]["price".$cartMain["currencyID"]];
							$thePriceRounded = roundWithoutCalcPrice($thePrice);
							$theOOPrice = $cartMain["products"][$f]["ooPrice".$cartMain["currencyID"]];
							$theQty = $cartMain["products"][$f]["qty"];
							$goodsTotal = $goodsTotal + ($thePriceRounded * $theQty);
							$goodsTotal = $goodsTotal + $theOOPrice;
							if ($cartMain["products"][$f]["freeShipping"] != "Y") {
								$shippingTotalGoods = $shippingTotalGoods + ($thePriceRounded * $theQty);
								$shippingTotalGoods = $shippingTotalGoods + $theOOPrice;
								$shippingTotalWeight = $shippingTotalWeight + ($cartMain["products"][$f]["weight"] * $theQty);
								$shippingTotalQty = $shippingTotalQty + $theQty;
							}
							$theTax = calculateTax($thePrice,$cartMain["products"][$f]);
							$theOOTax = calculateTax($theOOPrice,$cartMain["products"][$f]);
							$totalPrice = $totalPrice + ($thePriceRounded*$theQty);
							$totalPrice = $totalPrice + $theOOPrice;
							$totalTax = $totalTax + ($theTax * $theQty);
							$totalTax = $totalTax + $theOOTax;
							$totalQty = $totalQty + $cartMain["products"][$f]["qty"];
							if (showPricesIncTax()) {
								$cartMain["products"][$f]["price"] = formatWithoutCalcPrice($thePriceRounded+$theTax);
								$cartMain["products"][$f]["ooprice"] = formatWithoutCalcPrice($theOOPrice+$theOOTax);
							} else {
								$cartMain["products"][$f]["price"] = formatWithoutCalcPrice($thePriceRounded);
								$cartMain["products"][$f]["ooprice"] = formatWithoutCalcPrice($theOOPrice);
							}
							$cartMain["products"][$f]["priceextax"] = formatWithoutCalcPrice($thePriceRounded);
							$cartMain["products"][$f]["priceeinctax"] = formatWithoutCalcPrice($thePriceRounded+$theTax);
							$cartMain["products"][$f]["pricetax"] = formatWithoutCalcPrice($theTax);
							
							$cartMain["products"][$f]["oopriceextax"] = formatWithoutCalcPrice($theOOPrice);
							$cartMain["products"][$f]["oopriceeinctax"] = formatWithoutCalcPrice($theOOPrice+$theOOTax);
							$cartMain["products"][$f]["oopricetax"] = formatWithoutCalcPrice($theOOTax);

							if (showPricesIncTax()) {
								$cartMain["products"][$f]["total"] = formatWithoutCalcPrice((($thePriceRounded+$theTax)*$theQty)+($theOOPrice+$theOOTax));
							} else {
								$cartMain["products"][$f]["total"] = formatWithoutCalcPrice(($thePriceRounded*$theQty)+($theOOPrice));
							}
							$cartMain["products"][$f]["totalextax"] = formatWithoutCalcPrice(($thePriceRounded*$theQty)+($theOOPrice));
							$cartMain["products"][$f]["totalinctax"] = formatWithoutCalcPrice((($thePriceRounded+$theTax)*$theQty)+($theOOPrice+$theOOTax));
							$cartMain["products"][$f]["totaltax"] = formatWithoutCalcPrice(($theTax*$theQty)+($theOOTax));

							$cartMain["products"][$f]["link"] = createProductLink($cartMain["products"][$f]["productID"]);
							$cartMain["products"][$f]["qtyboxname"] = "qty".$cartMain["products"][$f]["uniqueID"];
							$cartMain["products"][$f]["deletelink"] = configureURL("cart.php?xCmd=remove&xUnq=".$cartMain["products"][$f]["uniqueID"]);
	
							$allExtraFields = null;
							if (is_array($extraFieldsArray)) {
								for ($g = 0; $g < count($extraFieldsArray); $g++) {
									$thisExtraField = "";
									switch ($extraFieldsArray[$g]["type"]) {
										case "TEXT":
										case "TEXTAREA":
											$thisExtraField["name"] = $extraFieldsArray[$g]["name"];
											$thisExtraField["title"] = findCorrectLanguage($extraFieldsArray[$g],"title");
											$thisExtraField["type"] = $extraFieldsArray[$g]["type"];
											$thisExtraField["content"] = @findCorrectLanguageExtraField($cartMain["products"][$f],"extrafield".$extraFieldsArray[$g]["extraFieldID"]);
											
											
											$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["name"] = $extraFieldsArray[$g]["name"];
											$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["title"] = findCorrectLanguage($extraFieldsArray[$g],"title");
											$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["type"] = $extraFieldsArray[$g]["type"];
											$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["content"] = @findCorrectLanguageExtraField($cartMain["products"][$f],"extrafield".$extraFieldsArray[$g]["extraFieldID"]);
											
											$allExtraFields[] = $thisExtraField;
											break;
										case "USERINPUT":
											$thisExtraField["name"] = $extraFieldsArray[$g]["name"];
											$thisExtraField["title"] = findCorrectLanguage($extraFieldsArray[$g],"title");
											$thisExtraField["type"] = $extraFieldsArray[$g]["type"];
											$thisExtraField["content"] = $cartMain["products"][$f]["extrafieldid".$extraFieldsArray[$g]["extraFieldID"]];
											
											$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["name"] = $extraFieldsArray[$g]["name"];
											$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["title"] = findCorrectLanguage($extraFieldsArray[$g],"title");
											$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["type"] = $extraFieldsArray[$g]["type"];
											$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["content"] = $cartMain["products"][$f]["extrafieldid".$extraFieldsArray[$g]["extraFieldID"]];
											$allExtraFields[] = $thisExtraField;
											break;
										case "SELECT":
										case "RADIOBUTTONS":
											$thisExtraField["name"] = $extraFieldsArray[$g]["name"];
											$thisExtraField["title"] = findCorrectLanguage($extraFieldsArray[$g],"title");
											$thisExtraField["type"] = $extraFieldsArray[$g]["type"];
											$thisExtraField["content"] = @findCorrectLanguageExtraField($cartMain["products"][$f],"extrafield".$extraFieldsArray[$g]["extraFieldID"]);
											//$thisExtraField["content"] = $cartMain["products"][$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]];
											
											$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["name"] = $extraFieldsArray[$g]["name"];
											$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["title"] = findCorrectLanguage($extraFieldsArray[$g],"title");
											$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["type"] = $extraFieldsArray[$g]["type"];
											$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["content"] = $thisExtraField["content"];
											$allExtraFields[] = $thisExtraField;
											break;								
										case "CHECKBOXES":
											$splitBits = @findCorrectLanguageExtraField($cartMain["products"][$f],"extrafield".$extraFieldsArray[$g]["extraFieldID"]);
											//$optionsSplit = explode("|",$cartMain["products"][$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]]);
											$optionsSplit = explode("|",$splitBits);
											$optionArray = "";
											for ($h = 0; $h < count($optionsSplit); $h++) {
												if (chop($optionsSplit[$h]) != "") {
													$optionArray[]=array("option"=>$optionsSplit[$h]);
												}
											}
											$thisExtraField["name"] = $extraFieldsArray[$g]["name"];
											$thisExtraField["title"] = findCorrectLanguage($extraFieldsArray[$g],"title");
											$thisExtraField["type"] = $extraFieldsArray[$g]["type"];
											$thisExtraField["content"] = @$cartMain["products"][$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]];
											$thisExtraField["options"] = $optionArray;
											
											$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["name"] = $extraFieldsArray[$g]["name"];
											$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["title"] = findCorrectLanguage($extraFieldsArray[$g],"title");
											$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["type"] = $extraFieldsArray[$g]["type"];
											$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["content"] = @$splitBits;
											$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["options"] = $optionArray;
											$allExtraFields[] = $thisExtraField;
											break;
									}
								}		
							}
							if (is_array($allExtraFields)) {
								$cartMain["products"][$f]["extrafields"] = $allExtraFields;
							}
						}
						$cartMain["totals"]["products"] = count($cartMain["products"]);

						$discountPercent = calculateDiscount($totalPrice);
						if ($discountPercent > 0) {
							$discountAmount = $totalPrice * ($discountPercent/100);
							$discountTax = $totalTax * ($discountPercent/100);
							$cartMain["totals"]["isDiscount"] = "Y";
						} else {
							$discountAmount = 0;
							$discountTax = 0;
							$cartMain["totals"]["isDiscount"] = "N";
						}	
						
						if ($inCheckoutPhase && @$orderTotals["offerTotal"] > 0) {
							$discountAmount += @$orderTotals["offerTotal"];
							$cartMain["totals"]["isDiscount"] = "Y";
						}
						

						if (showPricesIncTax() && $inCheckoutPhase == false) {
							$cartMain["totals"]["goods"] = formatWithoutCalcPrice(($totalPrice+$totalTax));
							$cartMain["totals"]["discount"] = formatWithoutCalcPrice($discountAmount+$discountTax);
						} else {
							$cartMain["totals"]["goods"] = formatWithoutCalcPrice($totalPrice);
							$cartMain["totals"]["discount"] = formatWithoutCalcPrice($discountAmount);
						}

						$cartMain["totals"]["goodsextax"] = formatWithoutCalcPrice($totalPrice);
						$cartMain["totals"]["goodsinctax"] = formatWithoutCalcPrice(($totalPrice+$totalTax));
						$cartMain["totals"]["goodstax"] = formatWithoutCalcPrice($totalTax);
						
						$giftCertTotal = 0;
						
						if ($inCheckoutPhase && @$orderingGiftCertificate != true) {
							$totalShipping = roundWithoutCalcPrice(calculateShipping(@$orderInfoArray["shippingID"],$shippingTotalGoods,$shippingTotalWeight,$shippingTotalQty,$goodsTotal));
							$cartMain["totals"]["shipping"] = formatWithoutCalcPrice($totalShipping);
							if (retrieveOption("taxOnShipping") == 1) {
								$sResult = $dbA->query("select taxable from $tableShippingTypes where shippingID=".$orderInfoArray["shippingID"]);
								if ($dbA->count($sResult) > 0) {
									$sRecord = $dbA->fetch($sResult);
									if ($sRecord["taxable"] == "Y") {
										$totalTax = $totalTax + calculateTax($totalShipping,array("taxrate"=>1));
									}
								}
							}
							$giftCertTotal = makeDecimal(@$orderInfoArray["giftCertTotal"]);
							$cartMain["totals"]["giftcertificates"] = formatWithoutCalcPrice($giftCertTotal);
							if ($giftCertTotal > 0) {
								$cartMain["usingGiftCertificates"] = "Y";
								$giftCerts = split("\|",$orderInfoArray["giftCerts"]);
								for ($f = 0; $f < count($giftCerts); $f++) {
									if ($giftCerts[$f] != "") {
										$cartMain["giftcertificates"][] = array("certificate"=>$giftCerts[$f],"removelink"=>configureURL("checkout.php?xCmd=s3&xExtra=rgc&xGC=".$giftCerts[$f]));
									}
								}
							}
						}
						$cartMain["totals"]["tax"] = formatWithoutCalcPrice($totalTax-$discountTax);
						
						
						$orderTotal = $totalPrice+$totalTax+@$totalShipping-($discountAmount+$discountTax)-$giftCertTotal;
						if ($orderTotal <0) { $orderTotal = 0; }
						$cartMain["totals"]["order"] = formatWithoutCalcPrice($orderTotal);

						
						$cartMain["totals"]["qty"] = $totalQty;

					} else {
						$cartMain["products"] = null;
						$cartMain["totals"]["qty"] = 0;
						$cartMain["totals"]["products"] = 0;
						$cartMain["totals"]["goods"] = formatWithoutCalcPrice(0);
					}
				} else {
					$cartMain["products"] = null;
					$cartMain["totals"]["qty"] = 0;
					$cartMain["totals"]["products"] = 0;
					$cartMain["totals"]["goods"] = formatWithoutCalcPrice(0);
				}
				$cartMain["form"]["name"]="cartForm";
				if (@$checkoutNotAllowedError == "STOCK") {
					$cartMain["form"]["action"]= configureURL("cart.php?xCmd=update&xFwd=checkout");
				} else {
					$cartMain["form"]["action"]= configureURL("cart.php?xCmd=update");
				}
				$cartMain["form"]["onsubmit"]="";
				$cartMain["emptylink"] = configureURL("cart.php?xCmd=clear");
				$cartMain["checkouterror"] = @$checkoutNotAllowedError;
				$tpl->addVariable("cart",$cartMain);
				break;
			case "search":
				$productLimit = retrieveOption("searchProductsPerPage");
				$xStart = ($xPage-1)*$productLimit;
				$searchArray["form"]["name"] = "searchForm";
				$searchArray["form"]["action"] = configureURL("search.php");
				$searchArray["form"]["onsubmit"] = "";
				$searchArray["form"]["string"] = "xSearch";
				$searchArray["form"]["pricefrom"] = "xPriceFrom";
				$searchArray["form"]["priceto"] = "xPriceTo";
				$searchArray["form"]["sort"] = "xSort";
				$xSearch = str_replace("&amp;","&",$xSearch);
				$searchArray["query"] = @$xSearch;
				if ($pageType == "search") {
					$searchBits = explode(" ",chop(@$xSearch));
					$xSearchFields = explode(";",retrieveOption("searchFields"));
					$stringClause = "";
					for ($f = 0; $f < count($searchBits); $f++) {
						$thisClause = "";
						for ($g = 0; $g < count($xSearchFields)-1; $g++) {
							if (substr($xSearchFields[$g],0,10) == "extrafield") {
								//check the extra fields here to make sure it actually exists still!
								$thisField = @substr($xSearchFields[$g],10,strlen($xSearchFields[$g])-10);
								for ($h = 0; $h < count($extraFieldsArray); $h ++) {
									if ($extraFieldsArray[$h]["extraFieldID"] == $thisField) {
										if ($thisClause != "") {
											$thisClause .= " or ";
										}
										$thisClause .= "$tableProducts.".$xSearchFields[$g]." like \"%".$searchBits[$f]."%\"";
										if ($cartMain["languageID"] != 1) {
											$thisClause .= " or ".$xSearchFields[$g]."_".$cartMain["languageID"]." like \"%".$searchBits[$f]."%\"";
										}
										break;
									}
								}
							} else {
								if ($thisClause != "") {
									$thisClause .= " or ";
								}
								$thisClause .= $xSearchFields[$g]." like \"%".$searchBits[$f]."%\"";
							}
							if ($xSearchFields[$g] == "name" && $cartMain["languageID"] != 1) {
								$thisClause .= " or ".$xSearchFields[$g].$cartMain["languageID"]." like \"%".$searchBits[$f]."%\"";
							}
							if ($xSearchFields[$g] == "shortdescription" && $cartMain["languageID"] != 1) {
								$thisClause .= " or ".$xSearchFields[$g].$cartMain["languageID"]." like \"%".$searchBits[$f]."%\"";
							}
							if ($xSearchFields[$g] == "description" && $cartMain["languageID"] != 1) {
								$thisClause .= " or ".$xSearchFields[$g].$cartMain["languageID"]." like \"%".$searchBits[$f]."%\"";
							}
						}
						$thisClause = "(".$thisClause.")";
						if ($stringClause != "") {
							$stringClause .=" AND ";
						}
						$stringClause .= $thisClause;
					}
					$stringClause ="(".$stringClause.")";
					$whereClause = $stringClause;
					if ($xPriceFrom >= 0 && $xPriceTo > 0) {
						$defaultDiscount = returnDefaultDiscount($cartMain["accTypeID"]);
						if ($cartMain["currency"]["currencyID"] == 1) {
							$whereClause .= "and ($tableProducts.price1$defaultDiscount >= $xPriceFrom and $tableProducts.price1$defaultDiscount <=$xPriceTo)";
						}
						if ($cartMain["currency"]["useexchangerate"] == "N") {
							$whereClause .= "and ($tableProducts.price".$cartMain["currency"]["currencyID"]."$defaultDiscount >= $xPriceFrom and $tableProducts.price".$cartMain["currency"]["currencyID"]."$defaultDiscount <=$xPriceTo)";
						}
						if ($cartMain["currency"]["useexchangerate"] == "Y") {
							$nPriceFrom = calculatePriceInBase($xPriceFrom);
							$nPriceTo = calculatePriceInBase($xPriceTo);
							$whereClause .= "and ($tableProducts.price1$defaultDiscount >= $nPriceFrom and $tableProducts.price1$defaultDiscount <=$nPriceTo)";
						}
						$searchArray["priceFrom"] = formatWithoutCalcPrice($xPriceFrom);
						$searchArray["priceTo"] = formatWithoutCalcPrice($xPriceTo);
						$searchArray["pricerange"] = "Y";
					} else {
						$searchArray["pricerange"] = "N";
					}
					$result = $dbA->query("select * from $tableProducts where $stockControlClause visible = \"Y\" and $whereClause order by code");
					$totalProducts = $dbA->count($result);
					$actualTotalProducts = $totalProducts;
					if ($totalProducts == 1 && retrieveOption("searchSoloProductShow") == 1) {
						$thisRecord = $dbA->fetch($result);
						doRedirect(configureURL("product.php?xProd=".$thisRecord["productID"]));
						exit;
					}
					switch ($xSort) {
						case "code":
							$sortClause = "code";
							break;
						case "name":
							$sortClause = "name";
							break;
						case "pricelh":
							if ($cartMain["currency"]["useexchangerate"] == "Y") {
								$sortClause = "$tableProducts.price1";
							} else {
								$sortClause = "$tableProducts.price".$cartMain["currency"]["currencyID"];
							}
							break;
						case "pricehl":
							if ($cartMain["currency"]["useexchangerate"] == "Y") {
								$sortClause = "$tableProducts.price1 DESC";
							} else {
								$sortClause = "$tableProducts.price".$cartMain["currency"]["currencyID"]." DESC";
							}
							break;
						default:
							$sortClause = "name";
					}
					$theQuery = "select $tableProducts.* $advancedPricingSelect from $tableProducts $advancedPricingJoin where $stockControlClause visible = \"Y\" and $whereClause group by $tableProducts.productID order by $sortClause LIMIT $xStart,$productLimit";
					$productsArray = retrieveProducts($theQuery,$counter,"m",0);
					if ($counter != 0) {
						$searchArray["products"] = $productsArray;
					}
					$searchArray["includesections"] = retrieveOption("searchIncludeSections");
					if (retrieveOption("searchIncludeSections") == 1) {
						$secArray = retrieveSections("select * from $tableSections where title like \"%$xSearch%\" and visible='Y' and (accTypes like '%;0;' or accTypes like '%;".$cartMain["accTypeID"].";%') order by title limit ".retrieveOption("searchMaxSections"),"m");
						if (is_array($secArray)) {
							for ($f = 0; $f < count($secArray); $f++) {
								$secArray[$f] = changeKeysCase($secArray[$f]);
							}	
						}		
						if (is_array($secArray)) {
							$searchArray["sections"] = $secArray;
						}				
					}
					$searchArray["page"] = $xPage;
					$searchArray["totalproducts"] = $totalProducts;
					$searchArray["from"] = $xStart+1;
					if ($xStart+$productLimit > $totalProducts) {
						$searchArray["to"] = $totalProducts;
					} else {
						$searchArray["to"] = $xStart+$productLimit;
					}
					if ($totalProducts > $xStart+$productLimit) {
						$searchArray["nextlink"] = configureURL("search.php?xSearch=".$xSearch."&xPriceFrom=".$xPriceFrom."&xPriceTo=".$xPriceTo."&xSort=".$xSort."&xPage=".($xPage+1));
					}
					if ($xStart > 0) {
						$searchArray["previouslink"] = configureURL("search.php?xSearch=".$xSearch."&xPriceFrom=".$xPriceFrom."&xPriceTo=".$xPriceTo."&xSort=".$xSort."&xPage=".($xPage-1));
					}
					$xPagesStart = 0;
					$pagesArray = "";
					$thisPage = 0;
					while ($xPagesStart < $totalProducts) {
						$thisPage++;
						$pagesArray[] = array("page"=>$thisPage,"link"=>configureURL("search.php?xSearch=".$xSearch."&xPriceFrom=".$xPriceFrom."&xPriceTo=".$xPriceTo."&xSort=".$xSort."&xPage=".($thisPage)));
						$xPagesStart = $xPagesStart+$productLimit;
						//if ($thisPage > 10) { break; }
					}
					if (count($pagesArray) > 0) {
						$searchArray["pages"] = $pagesArray;
					}
					$searchArray["sortcodelink"] = configureURL("search.php?xSearch=".$xSearch."&xPriceFrom=".$xPriceFrom."&xPriceTo=".$xPriceTo."&xSort=code&xPage=".($xPage));
					$searchArray["sortnamelink"] = configureURL("search.php?xSearch=".$xSearch."&xPriceFrom=".$xPriceFrom."&xPriceTo=".$xPriceTo."&xSort=name&xPage=".($xPage));
					$searchArray["sortpricelhlink"] = configureURL("search.php?xSearch=".$xSearch."&xPriceFrom=".$xPriceFrom."&xPriceTo=".$xPriceTo."&xSort=pricelh&xPage=".($xPage));
					$searchArray["sortpricehllink"] = configureURL("search.php?xSearch=".$xSearch."&xPriceFrom=".$xPriceFrom."&xPriceTo=".$xPriceTo."&xSort=pricehl&xPage=".($xPage));
					if (getFORM("xPage") == "" && retrieveOption("reportsSearchStats") == 1) {
						//add the report search data here
						$theDate = date("Ymd");
						//$totalProducts = count(@$searchArray["products"]);
						$totalSections = count(@$searchArray["sections"]);
						if (!isset($actualTotalProducts)) { $actualTotalProducts = 0; }
						$dbA->query("insert into $tableReportsSearch (date,searchstring,productResults,sectionResults) VALUES(\"$theDate\",\"$xSearch\",$actualTotalProducts,$totalSections)");
					}
				}
				$tpl->addVariable("search",$searchArray);
				break;
			case "checkout":
				$checkoutArray["link"] = configureURL("cart.php?xCmd=checkout");
				$tpl->addVariable("checkout",$checkoutArray);
				break;
			case "advsearch":
				$checkoutArray["link"] = configureURL("advsearch.php");
				$tpl->addVariable("advsearch",$checkoutArray);
				break;	
			case "usersonline":
				if (retrieveOption("usersOnlineActivated") == 1) {
					$usersonlineArray["timelimit"] = makeInteger(retrieveOption("usersOnlineTimeFrame"));
					$timeFrame = makeInteger(retrieveOption("usersOnlineTimeFrame"))*60;
					$currentTime = time();
					$oldestTime = $currentTime - $timeFrame;
					$result = $dbA->query("select * from $tableCarts where createtime >= $oldestTime");
					$count = $dbA->count($result);
					$usersonlineArray["current"] = $count;
					if ($count > makeInteger(retrieveOption("usersOnlineMost"))) {
						updateOption("usersOnlineMost",$count);
						updateOption("usersOnlineMostDate",date("Ymd"));
						$usersonlineArray["most"] = $count;
						$usersonlineArray["mostdate"] = formatDate(date("Ymd"));
					} else {
						$usersonlineArray["most"] = makeInteger(retrieveOption("usersOnlineMost"));
						if (retrieveOption("usersOnlineMostDate") != "") {
							$usersonlineArray["mostdate"] = formatDate(retrieveOption("usersOnlineMostDate"));
						} else {
							$usersonlineArray["mostdate"] = "";
						}
					}
					$tpl->addVariable("usersonline",$usersonlineArray);
				}
				break;			
			case "self":
				break;
			case "reviews":
				if ($xProd > 0) {
					$reviews = null;
					$limitClause = ($pageType=="product") ? " LIMIT ".retrieveOption("customerReviewsLimit") : "";
					if (retrieveOption("reviewsEnabled") == 1) {
						$theQuery = "select count(*) as totalreviews, sum(rating) as totalrating from $tableReviews where productID=$xProd and visible='Y' group by productID";
						$rResult = $dbA->query($theQuery);
						if ($dbA->count($rResult) > 0) {
							$rRecord = $dbA->fetch($rResult);
							$reviews["total"] = $rRecord["totalreviews"];
							$reviews["averagerating"] = makeInteger($rRecord["totalrating"] / $rRecord["totalreviews"]);	
							$reviewArray = $dbA->retrieveAllRecordsFromQuery("select * from $tableReviews where productID=$xProd and visible='Y' order by reviewID DESC".$limitClause);
							for ($f = 0; $f < count($reviewArray); $f++) {
								$reviewArray[$f]["review"] = str_replace("\r\n","<br>",$reviewArray[$f]["review"]);
							}
							$reviews["content"] = $reviewArray;
						} else {
							$reviews["total"] = 0;
							$reviews["averagerating"] = 0;
							$reviews["content"] = null;
						}	
						$reviews["link"] = createProductLink($xProd,0,"rev");
						$reviews["enabled"] = "Y";
					} else {
						$reviews["total"] = 0;
						$reviews["averagerating"] = 0;
						$reviews["content"] = null;
						$reviews["enabled"] = "N";
					}
					$tpl->addVariable("reviews",$reviews);			
				}
				break;
			case "associated":
				$associatedArray = null;
				if ($pageType == "cart") {
					if (retrieveOption("cartAssociatedActivated") == 1) {
						$contentsSelect = "";
						for ($f = 0; $f < count($cartContents); $f++) {
							if ($contentsSelect == "") {
								$contentsSelect .= " $tableAssociated.productID = ".$cartContents[$f]["productID"];
							} else {
								$contentsSelect .= " or $tableAssociated.productID = ".$cartContents[$f]["productID"];
							}
						}
						if ($contentsSelect != "") {
							$contentsSelect  = "(".$contentsSelect.") and ";
							$sortOrder = retrieveOption("cartAssociatedOrder");
							if ($sortOrder == "random") {
								srand(seedRandomProducts());
								$randNum = rand(1,5);
								switch ($randNum) {
									case 1:
										$sortOrder = "code";
										break;
									case 2:
										$sortOrder = "name";
										break;
									case 3:
										$sortOrder = "pricelh";
										break;
									case 4:
										$sortOrder = "pricehl";
										break;
									case 5:
										$sortOrder = "position";
										break;
								}
							}
							switch ($sortOrder) {
								case "code":
									$sortClause = "$tableProducts.code";
									break;
								case "name":
									$sortClause = "$tableProducts.name";
									break;
								case "pricelh":
									$sortClause = "$tableProducts.price1";
									break;
								case "pricehl":
									$sortClause = "$tableProducts.price1 DESC";
									break;
								case "position":
									$sortClause = "$tableAssociated.position";
									break;
							}
							$theQuery = "select $tableProducts.* $advancedPricingSelect from $tableAssociated,$tableProducts $advancedPricingJoin where $stockControlClause $tableProducts.productID=$tableAssociated.assocID and $contentsSelect $tableProducts.visible=\"Y\" group by $tableProducts.productID order by $sortClause limit ".makeInteger(retrieveOption("cartAssociatedMax"));
							$associatedArray = retrieveProducts($theQuery,$aCount,"m",0);
							if (is_array($associatedArray)) {
								for ($f = 0; $f < count($associatedArray); $f++)
								$tpl->addVariable("associated",$associatedArray);
							}
						}	
					}
				} else {
					if ($xProd > 1) {
						$theQuery = "select $tableProducts.* $advancedPricingSelect from $tableAssociated,$tableProducts $advancedPricingJoin where $stockControlClause $tableProducts.productID=$tableAssociated.assocID and $tableAssociated.productID=$xProd and $tableProducts.visible=\"Y\" group by $tableProducts.productID order by $tableAssociated.position";
						$associatedArray = retrieveProducts($theQuery,$aCount,"m",0);
						if (is_array($associatedArray)) {
							for ($f = 0; $f < count($associatedArray); $f++)
							$tpl->addVariable("associated",$associatedArray);
						}				
					}
				}
				break;
			case "groupedproducts":
				$groupedArray = null;
				if ($xProd > 1) {
					$theQuery = "select $tableProducts.*,$tableProductsGrouped.qty $advancedPricingSelect from $tableProductsGrouped,$tableProducts $advancedPricingJoin where $stockControlClause $tableProducts.productID=$tableProductsGrouped.groupedID and $tableProductsGrouped.productID=$xProd and $tableProducts.visible=\"Y\" group by $tableProducts.productID order by $tableProductsGrouped.position";
					$groupedArray = retrieveProducts($theQuery,$aCount,"m",0);
					if (is_array($groupedArray)) {
						for ($f = 0; $f < count($groupedArray); $f++)
						$tpl->addVariable("groupedproducts",$groupedArray);
					}				
				}
				break;
			case "recommended":
				$recommendedArray = null;
				if ($xProd > 1) {
					$tempTabID = "z".$cartID;
					$dbA->query("CREATE TEMPORARY TABLE $tempTabID select $tableOrdersLines.orderID from $tableOrdersLines where productID=$xProd group by orderID");
					$theQuery = "select $tableProducts.*,count(*) as total $advancedPricingSelect from $tableOrdersLines,$tempTabID,$tableProducts $advancedPricingJoin where $stockControlClause $tableOrdersLines.orderID=$tempTabID.orderID and $tableOrdersLines.productID=$tableProducts.productID and $tableProducts.productID != $xProd group by $tableProducts.productID order by total DESC limit ".makeInteger(retrieveOption("recommendedLimit"));
					$recommendedArray = retrieveProducts($theQuery,$aCount,"m",0);
					if (is_array($recommendedArray)) {
						$tpl->addVariable("recommended",$recommendedArray);
					}
				}
				break;
			case "labels":
				$labelArray = "";
				$result = $dbA->query("select * from $tableLabels");
				$count = $dbA->count($result);
				for ($f = 0; $f < $count; $f++) {
					$record = $dbA->fetch($result);
					$labelArray[$record["type"]][$record["name"]] = findCorrectLanguage($record,"content");
				}
				$tpl->addVariable("labels",$labelArray);
				break;
			case "newstitles":
				$newsArray = null;
				$extraLanguage = "";
				if ($cartMain["languageID"] != 1) {
					$extraLanguage = ",title".$cartMain["languageID"];
				}
				$result = $dbA->query("select newsID,title,datetime$extraLanguage from $tableNews order by position,datetime DESC");
				$count = $dbA->count($result);
				for ($f = 0; $f < $count; $f++) {
					$record = $dbA->fetch($result);
					$record["date"] = formatDate($record["datetime"]);
					$record["title"] = findCorrectLanguage($record,"title");
					$record["time"] = formatTime(substr($record["datetime"],-6));
					$newsArray[] = $record;
				}
				if (is_array($newsArray)) {
					$tpl->addVariable("newstitles",$newsArray);
				}
				break;			
			case "news":
				$newsArray = null;
				$extraLanguage = "";
				if ($cartMain["languageID"] != 1) {
					$extraLanguage = ",title".$cartMain["languageID"].",content".$cartMain["languageID"];
				}
				$result = $dbA->query("select newsID,title,content,datetime$extraLanguage from $tableNews order by position,datetime DESC");
				$count = $dbA->count($result);
				for ($f = 0; $f < $count; $f++) {
					$record = $dbA->fetch($result);
					$record["date"] = formatDate($record["datetime"]);
					$record["time"] = formatTime(substr($record["datetime"],-6));
					$record["title"] = findCorrectLanguage($record,"title");
					$record["content"] = findCorrectLanguage($record,"content");
					if (retrieveOption("newsConvertToBR") == 1) {
						$record["content"] = eregi_replace("\r\n","<BR>",$record["content"]);
					}
					$newsArray[] = $record;
				}
				if (is_array($newsArray)) {
					$tpl->addVariable("news",$newsArray);
				}
				break;		
			case "affiliatesignup":
				$affArray = null;
				$affArray["link"] = configureURL("affiliates.php?xCmd=register");
				$tpl->addVariable("affiliatesignup",$affArray);
				break;	
			case "affiliatelogin":
				$affArray = null;
				$affArray["link"] = configureURL("affiliates.php");
				$tpl->addVariable("affiliatelogin",$affArray);
				break;	
			case "affiliate":
				$affiliateMain["login"]["form"]["name"] = "affiliateLogin";
				$affiliateMain["login"]["form"]["action"] = configureURL("affiliates.php?xCmd=login");
				$affiliateMain["login"]["form"]["onsubmit"] = "";
				$affiliateMain["login"]["form"]["username"] = "xUsername";
				$affiliateMain["login"]["form"]["password"] = "xPassword";
				$affiliateMain["login"]["error"] = @$loginError;
				
				if ($affiliateMain["loggedin"] == "Y") {
					$affiliateMain["logoutlink"] = configureURL("affiliates.php?xCmd=logout");
					$affiliateMain["bannerslink"]= configureURL("affiliates.php?xCmd=banners");
					$affiliateMain["homelink"]= configureURL("affiliates.php?xCmd=account");
					$affiliateMain["statslink"]= configureURL("affiliates.php?xCmd=stats");
					$affiliateMain["saleslink"]= configureURL("affiliates.php?xCmd=sales");
					$affiliateMain["paymentslink"]= configureURL("affiliates.php?xCmd=payments");
					$affiliateMain["editlink"]= configureURL("affiliates.php?xCmd=acedit");
				}

				if ($pageType == "affiliatenew" || $pageType == "affiliatenewe" || $pageType == "affiliateedit" || $pageType == "affiliateedite") {
				
					if ($pageType == "affiliatenew" || $pageType == "affiliatenewe") {
						$affiliateMain["details"]["form"]["name"] = "affiliateDetails";
						$affiliateMain["details"]["form"]["action"] = configureURL("affiliates.php?xCmd=create");
						$affiliateMain["details"]["form"]["onsubmit"] = "";
						$affiliateMain["details"]["form"]["username"] = "xUsername";
						$affiliateMain["details"]["form"]["password"] = "xPassword";
						$affiliateMain["details"]["form"]["repeatpassword"] = "xRepeatPassword";
					} else {
						$affiliateMain["details"]["form"]["name"] = "affiliateDetails";
						$affiliateMain["details"]["form"]["action"] = configureURL("affiliates.php?xCmd=update&xUsername=".$affiliateMain["username"]);
						$affiliateMain["details"]["form"]["onsubmit"] = "";
						$affiliateMain["details"]["form"]["password"] = "xPassword";
						$affiliateMain["details"]["form"]["repeatpassword"] = "xRepeatPassword";
					}			
					$result = $dbA->query("select * from $tableCustomerFields where type='AF' and visible=1 and internalOnly=0 order by position,fieldID");
					$count = $dbA->count($result);
					$affiliateFields = null;
					for ($f = 0; $f < $count; $f++) {
						$fRecord = $dbA->fetch($result);
						$fRecord["titleText"] = findCorrectLanguage($fRecord,"titleText");
						$fRecord["validationmessage"] = findCorrectLanguage($fRecord,"validationmessage");
						if ($fRecord["fieldtype"]=="SELECT") {
							$optionsSplit = explode(";",$fRecord["contentvalues"]);
							$options = null;
							for ($g = 0; $g < count($optionsSplit); $g++) {
								if (chop($optionsSplit[$g]) != "") {
									$options[] = array("name"=>$optionsSplit[$g],"value"=>$optionsSplit[$g]);
								}
							}
							$fRecord["selected"] = @$affiliateMain[$fRecord["fieldname"]];
							$fRecord["options"] = $options;
						}
						if ($fRecord["fieldtype"]=="CHECKBOX") {
							if (@$affiliateMain[$fRecord["fieldname"]] != "") {
								$fRecord["checked"] = "CHECKED";
							}
						}
						if ($pageType != "affiliatenew") {
							$fRecord["content"] = @$affiliateMain[$fRecord["fieldname"]];
						}
						$fRecord["error"] = @$affiliateMain[$fRecord["fieldname"]."_error"];
						$affiliateMain["fields"][] = $fRecord;
						$affiliateMain["field"][$fRecord["fieldname"]] = $fRecord;
					}				
				
				
				}
				$tpl->addVariable("affiliate",$affiliateMain);
				break;				
			default:
				if (file_exists($jssShopFileSystem."routines/cartOutputExtra.php")) {
					include ($jssShopFileSystem."routines/cartOutputExtra.php");
				}
				if (substr($requiredVars[$z],0,8) == "snippet=") {
					$thisSnippet = explode("=",$requiredVars[$z]);
					$snippetName = @$thisSnippet[1];
					if ($snippetName != "") {
						$snipResult = $dbA->query("select * from $tableSnippets where name=\"$snippetName\"");
						if ($dbA->count($snipResult) > 0) {
							$snippet = $dbA->fetch($snipResult);
							$snippet["title"] = findCorrectLanguage($snippet,"title");
							$snippet["content"] = findCorrectLanguage($snippet,"content");
							if (retrieveOption("snippetsConvertToBR") == 1) {
								$snippet["content"] = str_replace("\r\n","<br>",$snippet["content"]);
							}
							$tpl->addVariable("$requiredVars[$z]",$snippet);
						}
					}
				}	
				if (substr($requiredVars[$z],0,8) == "product=") {
					$thisProduct = explode("=",$requiredVars[$z]);
					$productID = @$thisProduct[1];
					if ($productID != "") {
						$productID = makeInteger($productID);
						$theQuery = "select * from $tableProducts where productID=$productID";
						$productsArray = retrieveProducts($theQuery,$counter,"s");
						$tpl->addVariable("$requiredVars[$z]",$productsArray);
					}
				}	
				if (substr($requiredVars[$z],0,8) == "section=") {
					$thisSection = explode("=",$requiredVars[$z]);
					$sectionID = @$thisSection[1];
					if ($sectionID != "") {
						$sectionID = makeInteger($sectionID);
						$secRecord = retrieveSections("select * from $tableSections where sectionID=$sectionID","s");
						if (is_array($secRecord)) {
							$secRecord["path"] = generateFullSectionPath($xSec,$rootsectionID);
							$secRecord["rootsectionID"] = $rootsectionID;
							$theQuery = "select $tableProducts.* $advancedPricingSelect from $tableProductsTree,$tableProducts $advancedPricingJoin where $stockControlClause $tableProducts.productID = $tableProductsTree.productID and sectionID=$sectionID and visible='Y' group by $tableProducts.productID order by position,name";			
							$productsArray = retrieveProducts($theQuery,$counter,"m");
							if ($counter != 0) {
								$secRecord["products"] = $productsArray;
							}				
							$tpl->addVariable("$requiredVars[$z]",$secRecord);						
						}						
					}
				}			
		}
	}
	$tpl->addVariable("options",$optionsArray);
	$configURL = configureURL("page.php");
	$questionPos = strpos($configURL,"jssCart");
	if ($questionPos === false) {
		$tpl->convertText("page.php\?",$configURL."?");
	} else {
		$tpl->convertText("page.php\?",$configURL."&");
	}
	$configURL = configureURL("contact.php");
	$questionPos = strpos($configURL,"jssCart");
	if ($questionPos === false) {
		$tpl->convertText("contact.php\?",$configURL."?");
	} else {
		$tpl->convertText("contact.php\?",$configURL."&");
	}
	$tpl->convertText("contact.php\"",$configURL."\"");

	$configURL = configureURL("giftcert.php");
	$questionPos = strpos($configURL,"jssCart");
	if ($questionPos === false) {
		$tpl->convertText("giftcert.php\?",$configURL."?");
	} else {
		$tpl->convertText("giftcert.php\?",$configURL."&");
	}
	$tpl->convertText("giftcert.php\"",$configURL."\"");

	$timeEnd = microtime();

	//echo (getmicrotime($timeEnd)-getmicrotime($timeStart))*1000;
	
	//echo "<br>";
	//echo $dbA->showQueries();	
	
	function seedRandomProducts() { 
   		list($usec, $sec) = explode(' ', microtime()); 
   		return (float) $sec + ((float) $usec * 100000); 
	} 
?>
