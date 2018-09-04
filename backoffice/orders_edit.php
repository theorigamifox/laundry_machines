<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("../routines/cartOperations.php");
	include("../routines/orderOperations.php");
	include("../routines/taxOperations.php");
	include("../routines/affiliateTracking.php");
	include("../routines/giftCerts.php");
	include("../routines/stockControl.php");
	include("../routines/emailOutput.php");
	include("../routines/tSys.php");
	$myForm = new formElements;
	include ("../routines/Xtea.php");
	$crypt = new Crypt_Xtea();	
	
	dbConnect($dbA);

	$orderID = makeInteger(getFORM("xOrderID"));
	if ($orderID > 0) {
		$showID = getFORM("xOrderID") + retrieveOption("orderNumberOffset");
		$pageTitle = "Edit Order: $showID";
		$submitButton = "Update Order";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xOrderID' value='$orderID'>".hiddenReturnPOST();
		$result = $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");	
		$oRecord = $dbA->fetch($result);
	} else {
		$pageTitle = "Create New Order";
		$submitButton = "Create Order";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>".hiddenReturnPOST();
		$oRecord["new"] = "YES";
		$extraFieldList = null;
	}

	$cartID = getFORM("cartID");

	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	$langArray = $dbA->retrieveAllRecords($tableLanguages,"languageID");
	$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"position,name");
	$accTypeArray = $dbA->retrieveAllRecords($tableCustomersAccTypes,"accTypeID");
	$flagArray = $dbA->retrieveAllRecords($tableProductsFlags,"flagID");

	$firstTime = 1;
	if ($cartID == "") {
		//This is where we load everything up
		$cartID = createNewCart();
		if ($orderID > 0) {
			$rArray = null;
			$orderInfoArray = null;
			$rArray[] = array("currencyID",$oRecord["currencyID"],"N");
			$rArray[] = array("accTypeID",$oRecord["accTypeID"],"N");
			$rArray[] = array("customerID",$oRecord["customerID"],"N");
			$rArray[] = array("currencyID",$oRecord["currencyID"],"N");
			$country = $oRecord["country"];
			$cResult = $dbA->query("select * from $tableCountries where name=\"$country\"");
			if ($dbA->count($cResult) > 0) {
				$cRecord = $dbA->fetch($cResult);
				$rArray[] = array("country",$cRecord["countryID"],"N");
				$orderInfoArray["country"] = $cRecord["countryID"];
			} else {
				$orderInfoArray["country"] = retrieveOption("defaultCountry");
			}
			$rArray[] = array("county",$oRecord["county"],"S");
			$country = $oRecord["deliveryCountry"];
			$cResult = $dbA->query("select * from $tableCountries where name=\"$country\"");
			if ($dbA->count($cResult) > 0) {
				$cRecord = $dbA->fetch($cResult);
				$orderInfoArray["deliveryCountry"] = $cRecord["countryID"];
			} else {
				$orderInfoArray["deliveryCountry"] = retrieveOption("defaultCountry");
			}
			$orderInfoArray["offerCode"] = $oRecord["offerCode"];
			$orderInfoArray["county"] = $oRecord["county"];
			$orderInfoArray["deliveryCounty"] = $oRecord["deliveryCounty"];
			$rArray[] = array("languageID",$oRecord["languageID"],"N");
			$rArray[] = array("affiliateID",$oRecord["affiliateID"],"N");
			$dbA->updateRecord($tableCarts,"cartID='$cartID'",$rArray,0);
			$orderInfoArray["shippingID"] = $oRecord["shippingID"];
			//we are editing an order here
			$extraFieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$orderID order by lineID,extraFieldID");
			$lResult = $dbA->query("select * from $tableOrdersLines where orderID=$orderID order by lineID");
			$lCount = $dbA->count($lResult);
			for ($f = 0; $f < $lCount; $f++) {
				$lRecord = $dbA->fetch($lResult);
				$extraFields = @$lRecord["extrafields"];
				if ($extraFields != "") {
					$extraFields = "<br>".$extraFields;
				}
				$allFields = "";
				$rArray = null;
				$rArray[] = array("cartID",$cartID,"S");
				$rArray[] = array("lineID",$lRecord["lineID"],"N");
				$rArray[] = array("productID",$lRecord["productID"],"N");
				$rArray[] = array("code",$lRecord["code"],"S");
				$rArray[] = array("qty",$lRecord["qty"],"N");
				$rArray[] = array("weight",$lRecord["weight"],"D");
				$rArray[] = array("supplierID",$lRecord["supplierID"],"N");
				$rArray[] = array("suppliercode",$lRecord["suppliercode"],"S");
				$rArray[] = array("isDigital",$lRecord["isDigital"],"YN");
				$pResult = $dbA->query("select * from $tableProducts where productID=".$lRecord["productID"]);
				if ($dbA->count($pResult) > 0) {
					$pRecord = $dbA->fetch($pResult);
					$rArray[] = array("taxrate",$pRecord["taxrate"],"N");
					$rArray[] = array("freeShipping",$pRecord["freeShipping"],"YN");
				} else {
					$rArray[] = array("taxrate",0,"N");
					$rArray[] = array("freeShipping","Y","YN");
				}
				$rArray[] = array("price".$oRecord["currencyID"],$lRecord["price"],"D");
				$rArray[] = array("ooPrice".$oRecord["currencyID"],$lRecord["ooprice"],"D");

				if (is_array($extraFieldsArray)) {
					for ($g = 0; $g < count($extraFieldsArray); $g++) {
						$thisField = "";
						switch ($extraFieldsArray[$g]["type"]) {
								case "SELECT":
								case "RADIOBUTTONS":
								case "USERINPUT":
									if (is_array($extraFieldList)) {
										for ($i = 0; $i < count($extraFieldList); $i++) {
											if ($lRecord["lineID"] == $extraFieldList[$i]["lineID"] && $extraFieldsArray[$g]["extraFieldID"] == $extraFieldList[$i]["extraFieldID"]) {
												if ($extraFieldsArray[$g]["type"] != "USERINPUT") {
													$rArray[] = array("extrafieldid".$extraFieldsArray[$g]["extraFieldID"],$extraFieldList[$i]["exvalID"],"N");
												} else {
													$rArray[] = array("extrafieldid".$extraFieldsArray[$g]["extraFieldID"],$extraFieldList[$i]["content"],"S");
												}
												$theContent = $extraFieldList[$i]["content"];
												break;
											}
										}
									}
									break;								
								case "CHECKBOXES":
									$optionArray = "";
									$theContent = "";
									if (is_array($extraFieldList)) {
										for ($i = 0; $i < count($extraFieldList); $i++) {
											if ($lRecord["lineID"] == $extraFieldList[$i]["lineID"] && $extraFieldsArray[$g]["extraFieldID"] == $extraFieldList[$i]["extraFieldID"]) {
												if ($extraFieldList[$i]["content"] != "") {
													if ($theContent == "") {
														$theContent = $extraFieldList[$i]["content"];
													} else {
														$theContent .= "|".$extraFieldList[$i]["content"];
													}
												}
											}
										}
									}
									if ($theContent != "") {
										$rArray[] = array("extrafieldid".$extraFieldsArray[$g]["extraFieldID"],$theContent,"S");
									}
									break;
						}	
					}
				}
				$dbA->insertRecord($tableCartsContents,$rArray,0);
			}
			//we should create the orderInfoArray here
			$cartResult = $dbA->query("select * from $tableCarts where cartID=\"$cartID\"");
			$cartMain = $dbA->fetch($cartResult);
			cartRetrieveCart(TRUE);
			$orderString = commitOrderInformation();
		} else {
			//this is a new order so nothing to add into the cart here
			$oRecord["status"] = "N";
			$oRecord["datetime"] = date("YmdHis");
			$oRecord["authInfo"] = "";
			$oRecord["shippingMethod"] = "";
			$oRecord["authInfo"] = "";
			$oRecord["ccNumber"] = "";
			$oRecord["ccCVV"] = "";
			$oRecord["paymentName"] = "";
			$oRecord["ccName"] = "";
			$oRecord["ccNumber"] = "";
			$oRecord["ccType"] = "";
			$oRecord["ccExpiryDate"] = "";
			$oRecord["ccStartDate"] = "";
			$oRecord["ccIssue"] = "";
			$cartID = createNewCart();
			//setup basic order array
			$cartResult = $dbA->query("select * from $tableCarts where cartID=\"$cartID\"");
			$cartMain = $dbA->fetch($cartResult);
			cartRetrieveCart(TRUE);
			$country = retrieveOption("defaultCountry");
			$cResult = $dbA->query("select * from $tableCountries where countryID=$country");
			if ($dbA->count($cResult) > 0) {
				$cRecord = $dbA->fetch($cResult);
				$oRecord["country"] = $cRecord["name"];
				$oRecord["deliveryCountry"] = $cRecord["name"];
				//$formArray["country"] = $cRecord["name"];
				//$formArray["deliveryCountry"] = $cRecord["name"];
			}
			$orderInfoArray["country"] = retrieveOption("defaultCountry");
			$orderInfoArray["deliveryCountry"] = retrieveOption("defaultCountry");
			$orderInfoArray["shippingID"] = checkShippingID(retrieveOption("defaultShipping"));
			$orderInfoArray["currencyID"] = getFORM("xCurrencyID");
			$cartMain["currencyID"] = getFORM("xCurrencyID");
			$dbA->query("update $tableCarts set currencyID=".getFORM("xCurrencyID")." where cartID='$cartID'");
			$orderString = commitOrderInformation();
		}
		$firstTime = 1;
	} else {
		$firstTime = 0;
		$cartResult = $dbA->query("select * from $tableCarts where cartID=\"$cartID\"");
		$cartMain = $dbA->fetch($cartResult);
		cartRetrieveCart(TRUE);
	}
	$orderInfoArray = retrieveOrderInformation();
	if ($firstTime == 1) {
		$orderInfoArray["shippingLock"] = "N";
		$orderInfoArray["discountLock"] = "N";
		$orderInfoArray["productsEdited"] = "N";
		$currentValues["shippingTotal"] = @$orderInfoArray["lockedShippingTotal"];
		$currentValues["discountTotal"] = @$orderInfoArray["lockedDiscountTotal"];
	} else {
		$currentValues["shippingTotal"] = @$orderInfoArray["lockedShippingTotal"];
		$currentValues["discountTotal"] = @$orderInfoArray["lockedDiscountTotal"];
	}
	$orderString = commitOrderInformation();
	$orderTotals = calculateOrderTotals(TRUE,$currentValues);

	$hiddenFields .= "<input type='hidden' name='cartID' value='$cartID'>";
	
	$xAction = getFORM("xAction");

	$linkBackLink = urldecode(getFORM("xReturn"));

	$formArray = null;
	foreach($_GET as $key => $value) {
		$formArray[$key] = $value;
	}
	foreach($_POST as $key => $value) {
		$formArray[$key] = $value;
	}

	switch ($xAction) {
		case "changecustomer":
			$custResult = $dbA->query("select * from $tableCustomers where email=\"".$formArray["xEmail"]."\"");
			if ($dbA->count($custResult) == 0) {
				$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='C' and visible=1 and internalOnly=0 and incOrdering=1 order by position,fieldID");
				for ($f = 0; $f < count($fieldList); $f++) {
					$formArray[$fieldList[$f]["fieldname"]] = "";
				}
			} else {
				$custRecord = $dbA->fetch($custResult);
				$dbA->query("update $tableCarts set customerID=".$custRecord["customerID"].", accTypeID=".$custRecord["accTypeID"]." where cartID='$cartID'");
				$cartMain["customerID"] = $custRecord["customerID"];
				$cartMain["accTypeID"] = $custRecord["accTypeID"];
				$orderInfoArray["country"] = $custRecord["country"];
				$orderInfoArray["productsEdited"] = "Y";
				$orderString = commitOrderInformation();
				$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='C' and visible=1 and internalOnly=0 and incOrdering=1 order by position,fieldID");
				$dbA->query("update $tableCarts set country=".$custRecord["country"].", customerID=".$custRecord["customerID"].", accTypeID=".$custRecord["accTypeID"]." where cartID='$cartID'");
				for ($f = 0; $f < count($fieldList); $f++) {
					$formArray[$fieldList[$f]["fieldname"]] = @$custRecord[$fieldList[$f]["fieldname"]];
				}
			}

			break;
		case "recalculatets":
			$country = @$formArray["country"];
			$county = @$formArray["county"];
			$deliveryCountry = @$formArray["deliveryCountry"];
			$deliveryCounty = @$formArray["deliveryCounty"];
			$cResult = $dbA->query("select * from $tableCountries where name=\"$country\"");
			if ($dbA->count($cResult) > 0) {
				$cRecord = $dbA->fetch($cResult);
				$rArray[] = array("country",$cRecord["countryID"],"N");
				$orderInfoArray["country"] = $cRecord["countryID"];
			} else {
				$orderInfoArray["country"] = retrieveOption("defaultCountry");
			}
			$cResult = $dbA->query("select * from $tableCountries where name=\"$deliveryCountry\"");
			if ($dbA->count($cResult) > 0) {
				$cRecord = $dbA->fetch($cResult);
				$rArray[] = array("country",$cRecord["countryID"],"N");
				$orderInfoArray["deliveryCountry"] = $cRecord["countryID"];
			} else {
				$orderInfoArray["deliveryCountry"] = retrieveOption("defaultCountry");
			}
			$orderInfoArray["county"] = $county;
			$orderInfoArray["deliveryCounty"] = $deliveryCounty;
			$orderInfoArray["productsEdited"] = "Y";
			$orderString = commitOrderInformation();
			break;
		case "insert":
			if (@$cartMain["customerID"] > 0) {
				//LOAD UP THE CUSTOMER DETAILS
				$custResult = $dbA->query("select * from $tableCustomers where customerID=".$cartMain["customerID"]);
				$customerMain = $dbA->fetch($custResult);
			}
			if (@$orderInfoArray["zeroTax"] == "Y") {
				$customerMain["taxExempt"] = "Y";
			}
			$rArray[] = array("status","N","S");
			$rArray[] = array("customerID",@$cartMain["customerID"],"N");
			$rArray[] = array("accTypeID",$cartMain["accTypeID"],"N");
			$rArray[] = array("orderNotes",getFORM("xOrderNotes"),"S");
			$rArray[] = array("email",@$formArray["xEmail"],"S");
			$rArray[] = array("ip",@$_SERVER["REMOTE_ADDR"],"S");
			$rArray[] = array("paymentID",getFORM("paymentID"),"S");
			$result = $dbA->query("select * from $tablePaymentOptions where paymentID=".getFORM("paymentID"));
			if ($dbA->count($result) > 0) {
				$payRecord = $dbA->fetch($result);
				$rArray[] = array("paymentName",$payRecord["name"],"S");
				$rArray[] = array("paymentNameNative",$payRecord["name"],"S");
			}
			srand((double)microtime()*1000000);
			$randID = rand();
			$rArray[] = array("randID",$randID,"S");
			$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='C' and visible=1 and internalOnly=0 and incOrdering=1 order by position,fieldID");
			for ($f = 0; $f < count($fieldList); $f++) {
				$rArray[] = array($fieldList[$f]["fieldname"],getFORM($fieldList[$f]["fieldname"]),"S");
			}
			$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='D' and visible=1 and internalOnly=0 and incOrdering=1 order by position,fieldID");
			for ($f = 0; $f < count($fieldList); $f++) {
				$rArray[] = array($fieldList[$f]["fieldname"],getFORM($fieldList[$f]["fieldname"]),"S");
			}
			$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='O' and visible=1 order by position,fieldID");
			for ($f = 0; $f < count($fieldList); $f++) {
				$rArray[] = array($fieldList[$f]["fieldname"],getFORM($fieldList[$f]["fieldname"]),"S");
			}
			$checkingString="01234567890 ";
			if (@$formArray["ccNumber"] != "") {
				@$formArray["ccNumber"] = base64_encode($crypt->encrypt($formArray["ccNumber"], $teaEncryptionKey));
			}
			if (@$formArray["ccCVV"] != "") {
				$invalidChar = false;
				for ($g = 0; $g < strlen($formArray["ccCVV"]); $g++) {
					$charFound = false;
					for ($h = 0; $h < strlen($checkingString); $h++) {
						if (substr($formArray["ccCVV"],$g,1) == substr($checkingString,$h,1)) {
							$charFound = true;
						}
					}
					if ($charFound == false) {
						$invalidChar = true;
					}
				}
				if ($invalidChar) {
					@$formArray["ccCVV"] = base64_encode($crypt->decrypt($formArray["ccCVV"], $teaEncryptionKey));
				}		
			}
			if ($formArray["paymentID"] == 1) {
				$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='CC'");
				for ($f = 0; $f < count($fieldList); $f++) {
					$rArray[] = array($fieldList[$f]["fieldname"],@$formArray[$fieldList[$f]["fieldname"]],"S");
				}
			}
			if (@$formArray["ccNumber"] != "") {
				$orderArray["ccNumber"] = $crypt->decrypt(base64_decode($formArray["ccNumber"]), $teaEncryptionKey);
			}
			$goodsTotal = $orderTotals["goodsTotal"];
			$shippingTotal = $orderTotals["shippingTotal"];
			$taxTotal = $orderTotals["taxTotal"];
			$shippingTotalGoods = $orderTotals["shippingTotalGoods"];
			$shippingTotalWeight = $orderTotals["shippingTotalWeight"];
			$shippingTotalQty = $orderTotals["shippingTotalQty"];		
			$discountAmount = $orderTotals["discountAmount"];
			
			$orderTotal = $goodsTotal+$shippingTotal+$taxTotal-$discountAmount;
			$rArray[] = array("currencyID",$cartMain["currencyID"],"N");
			$rArray[] = array("datetime",date("YmdHis"),"S");
			$rArray[] = array("goodsTotal",$goodsTotal,"D");
			$rArray[] = array("discountTotal",$discountAmount,"D");
			$rArray[] = array("shippingTotal",$shippingTotal,"D");
			$rArray[] = array("taxTotal",$taxTotal,"D");
			$rArray[] = array("giftCertTotal",$orderTotals["giftCertTotal"],"D");
			$rArray[] = array("languageID",$cartMain["languageID"],"N");
			$shippingID = makeInteger($orderInfoArray["shippingID"]);
			$result = $dbA->query("select * from $tableShippingTypes where shippingID=$shippingID");
			if ($dbA->count($result) > 0) {
				$shipRecord = $dbA->fetch($result);
				$rArray[] = array("shippingID",$shipRecord["shippingID"],"S");
				$rArray[] = array("shippingMethod",$shipRecord["name"],"S");
				$rArray[] = array("shippingMethodNative",$shipRecord["name"],"S");
			}
			$rArray[] = array("offerCode",@$orderInfoArray["offerCode"],"S");
			$dbA->insertRecord($tableOrdersHeaders,$rArray,0);
		
			$orderID = $dbA->lastID();
			//ADDING NEW ITEMS IN HERE
			for ($f = 0; $f < count($cartMain["products"]); $f++) {
				if ($cartMain["products"][$f]["lineID"] == 0) {

					$zArray = "";
					$zArray[] = array("orderID",$orderID,"N");
					$zArray[] = array("productID",$cartMain["products"][$f]["productID"],"N");
					$zArray[] = array("code",$cartMain["products"][$f]["code"],"S");
					$zArray[] = array("name",$cartMain["products"][$f]["name"],"S");
					$zArray[] = array("nameNative",$cartMain["products"][$f]["name"],"S");
					$zArray[] = array("qty",$cartMain["products"][$f]["qty"],"N");
					$zArray[] = array("weight",$cartMain["products"][$f]["weight"],"D");
					$zArray[] = array("isDigital",$cartMain["products"][$f]["isDigital"],"YN");
					$zArray[] = array("digitalFile",$cartMain["products"][$f]["digitalFile"],"S");
					$zArray[] = array("digitalReg",$cartMain["products"][$f]["digitalReg"],"N");
					$zArray[] = array("price",$cartMain["products"][$f]["price".$cartMain["currencyID"]],"D");
					$theTax = calculateTax($cartMain["products"][$f]["price".$cartMain["currencyID"]],$cartMain["products"][$f]);
					$zArray[] = array("taxamount",$theTax,"D");
					$zArray[] = array("ooprice",$cartMain["products"][$f]["ooPrice".$cartMain["currencyID"]],"D");
					$theOOTax = calculateTax($cartMain["products"][$f]["ooPrice".$cartMain["currencyID"]],$cartMain["products"][$f]);
					$zArray[] = array("ootaxamount",$theOOTax,"D");
					$zArray[] = array("supplierID",$cartMain["products"][$f]["supplierID"],"N");
					$zArray[] = array("suppliercode",$cartMain["products"][$f]["suppliercode"],"S");
					
					$dbA->insertRecord($tableOrdersLines,$zArray,0);
					$lineID = $dbA->lastID();
					$stockFields = "";
					if (is_array($extraFieldsArray)) {
						for ($g = 0; $g < count($extraFieldsArray); $g++) {
							$thisEFID = @$cartContents[$f]["extrafieldid".$extraFieldsArray[$g]["extraFieldID"]];
							if ($extraFieldsArray[$g]["type"] == "USERINPUT") {
								if ($thisEFID != "") {
											$eArray = null;
											$eArray[] = array("orderID",$orderID,"N");
											$eArray[] = array("lineID",$lineID,"N");
											$eArray[] = array("extraFieldID",$extraFieldsArray[$g]["extraFieldID"],"N");
											$eArray[] = array("extraFieldName",$extraFieldsArray[$g]["name"],"S");
											$eArray[] = array("extraFieldTitle",$extraFieldsArray[$g]["title"],"S");
											$eArray[] = array("exvalID",0,"N");
											$eArray[] = array("content",$thisEFID,"S");
											$eArray[] = array("contentNative",$thisEFID,"S");
											$dbA->insertRecord($tableOrdersExtraFields,$eArray,0);					
											//exit;
								}
							} else {
								$thisEFContent = @$cartContents[$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]];
								
								$thisEFContentNative = @$cartContents[$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]];
								if (!empty($thisEFContent)) {
									$splitsID = explode("|",$thisEFID);
									$splitsContent = explode("|",$thisEFContent);
									$splitsContentNative = explode("|",$thisEFContentNative);
									for ($h = 0; $h < count($splitsID); $h++) {
										if (!empty($splitsID)) {
											$eArray = null;
											$eArray[] = array("orderID",$orderID,"N");
											$eArray[] = array("lineID",$lineID,"N");
											$eArray[] = array("extraFieldID",$extraFieldsArray[$g]["extraFieldID"],"N");
											$eArray[] = array("extraFieldName",$extraFieldsArray[$g]["name"],"S");
											$eArray[] = array("extraFieldTitle",$extraFieldsArray[$g]["title"],"S");
											$eArray[] = array("exvalID",$splitsID[$h],"N");
											$eArray[] = array("content",$splitsContent[$h],"S");
											$eArray[] = array("contentNative",$splitsContentNative[$h],"S");
											$dbA->insertRecord($tableOrdersExtraFields,$eArray,0);
											$stockFields[] = array("extraFieldID"=>$extraFieldsArray[$g]["extraFieldID"],"exvalID"=>$splitsID[$h],"content"=>$splitsContent[$h]);
										}
									}
								}
							}
						}
					}
				}
				if (retrieveOption("stockDeductMode") == 0 || (retrieveOption("stockDeductMode") == 1 && $currentStatus == "P")) {
					alterStock($cartMain["products"][$f]["productID"],$cartMain["products"][$f]["qty"],$stockFields);
				}
				$groupedResult = $dbA->query("select $tableProducts.*,$tableProductsGrouped.qty from $tableProducts,$tableProductsGrouped where $tableProducts.productID=$tableProductsGrouped.groupedID and $tableProductsGrouped.productID=".$cartMain["products"][$f]["productID"]." order by $tableProductsGrouped.position");
				if ($dbA->count($groupedResult) > 0) {
					//we have some grouped products here
					for ($h = 0; $h < $dbA->count($groupedResult); $h++) {
						$groupedRecord = $dbA->fetch($groupedResult);
						$xArray = null;
						$xArray[] = array("orderID",$orderID,"N");
						$xArray[] = array("lineID",$lineID,"N");
						$xArray[] = array("productID",$groupedRecord["productID"],"N");
						$xArray[] = array("code",$groupedRecord["code"],"S");
						$xArray[] = array("name",$groupedRecord["name"],"S");
						$xArray[] = array("nameNative",$groupedRecord["name"],"S");
						$xArray[] = array("qty",$groupedRecord["qty"],"N");
						$dbA->insertRecord($tableOrdersLinesGrouped,$xArray,0);
						if (retrieveOption("stockDeductMode") == 0 || (retrieveOption("stockDeductMode") == 1 && $currentStatus == "P")) {
							alterStock($groupedRecord["productID"],$cartMain["products"][$f]["qty"]*$groupedRecord["qty"],"");
						}
					}
				}	
			}
			$cartID = getFORM("cartID");
			$dbA->query("delete from $tableCarts where cartID='$cartID'");
			$dbA->query("delete from $tableCartsContents where cartID='$cartID'");
			//$inOrderProcessing = false;
			$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
			$orderArray = $dbA->fetch($result);
			$orderArray["ordernumber"] = $orderArray["orderID"]+retrieveOption("orderNumberOffset");
			$orderArray["orderdate"] = formatDate($orderArray["datetime"]);
			$orderArray["ordertime"] = formatTime(substr($orderArray["datetime"],-6));
			$orderProducts = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersLines where orderID=$orderID order by lineID");
			
			$orderArray["products"] = $orderProducts;
			$extraFieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$orderID order by lineID,extraFieldID");
			
			$result = $dbA->query("select * from $tablePaymentOptions where paymentID=".$orderArray["paymentID"]);
			$goSendEmail = false;
			if ($dbA->count($result) > 0) {
				$paymentArray = $dbA->fetch($result);
				if ($paymentArray["custConfirmation"] == 1) {
					$goSendEmail = true;
				}
			} else {
				$goSendEmail = true;
			}
			sendConfirmationEmails($orderID,0,1);
			if (retrieveOption("suppliersEnabled") == 1 && (retrieveOption("suppliersEmailTiming") == 1 || (retrieveOption("suppliersEmailTiming") == 2 && $orderArray["status"] == "P"))) {
				include("../routines/supplierRoutines.php");
				sendSupplierEmails($orderID);
			}


			doRedirect("orders.php?".userSessionGET());
			break;
		case "update":
			//THIS IS WHERE WE SAVE THE ORDER BACK AGAIN AFTER EDITING
			if (@$cartMain["customerID"] > 0) {
				//LOAD UP THE CUSTOMER DETAILS
				$custResult = $dbA->query("select * from $tableCustomers where customerID=".$cartMain["customerID"]);
				$customerMain = $dbA->fetch($custResult);
			}
			if (@$orderInfoArray["zeroTax"] == "Y") {
				$customerMain["taxExempt"] = "Y";
			}
			$lResult = $dbA->query("select * from $tableOrdersLines where orderID=$orderID order by lineID");
			$lCount = $dbA->count($lResult);
			for ($g = 0; $g < $lCount; $g++) {
				$lRecord = $dbA->fetch($lResult);
				$found = false;
				for ($f = 0; $f < count($cartMain["products"]); $f++) {
					if ($lRecord["lineID"] == $cartMain["products"][$f]["lineID"]) {
						$found = true;
						$rArray = null;
						$rArray[] = array("price",$cartMain["products"][$f]["price".$cartMain["currencyID"]],"D");
						$rArray[] = array("ooprice",$cartMain["products"][$f]["ooPrice".$cartMain["currencyID"]],"D");
						$rArray[] = array("qty",$cartMain["products"][$f]["qty"],"N");
						$dbA->updateRecord($tableOrdersLines,"lineID=".$lRecord["lineID"],$rArray,0);
					}
				}
				if (!$found) {
					$dbA->query("delete from $tableOrdersLines where lineID=".$lRecord["lineID"]);
				}
			}
			//ADDING NEW ITEMS IN HERE
			for ($f = 0; $f < count($cartMain["products"]); $f++) {
				if ($cartMain["products"][$f]["lineID"] == 0) {

					$zArray = "";
					$zArray[] = array("orderID",$orderID,"N");
					$zArray[] = array("productID",$cartMain["products"][$f]["productID"],"N");
					$zArray[] = array("code",$cartMain["products"][$f]["code"],"S");
					$zArray[] = array("name",$cartMain["products"][$f]["name"],"S");
					$zArray[] = array("nameNative",$cartMain["products"][$f]["name"],"S");
					$zArray[] = array("qty",$cartMain["products"][$f]["qty"],"N");
					$zArray[] = array("weight",$cartMain["products"][$f]["weight"],"D");
					$zArray[] = array("isDigital",$cartMain["products"][$f]["isDigital"],"YN");
					$zArray[] = array("digitalFile",$cartMain["products"][$f]["digitalFile"],"S");
					$zArray[] = array("digitalReg",$cartMain["products"][$f]["digitalReg"],"N");
					$zArray[] = array("price",$cartMain["products"][$f]["price".$cartMain["currencyID"]],"D");
					$theTax = calculateTax($cartMain["products"][$f]["price".$cartMain["currencyID"]],$cartMain["products"][$f]);
					$zArray[] = array("taxamount",$theTax,"D");
					$zArray[] = array("ooprice",$cartMain["products"][$f]["ooPrice".$cartMain["currencyID"]],"D");
					$theOOTax = calculateTax($cartMain["products"][$f]["ooPrice".$cartMain["currencyID"]],$cartMain["products"][$f]);
					$zArray[] = array("ootaxamount",$theOOTax,"D");
					$zArray[] = array("supplierID",$cartMain["products"][$f]["supplierID"],"N");
					$zArray[] = array("suppliercode",$cartMain["products"][$f]["suppliercode"],"S");
					
					$dbA->insertRecord($tableOrdersLines,$zArray,0);
					$lineID = $dbA->lastID();
					$stockFields = "";
					if (is_array($extraFieldsArray)) {
						for ($g = 0; $g < count($extraFieldsArray); $g++) {
							$thisEFID = @$cartContents[$f]["extrafieldid".$extraFieldsArray[$g]["extraFieldID"]];
							if ($extraFieldsArray[$g]["type"] == "USERINPUT") {
								if ($thisEFID != "") {
											$eArray = null;
											$eArray[] = array("orderID",$orderID,"N");
											$eArray[] = array("lineID",$lineID,"N");
											$eArray[] = array("extraFieldID",$extraFieldsArray[$g]["extraFieldID"],"N");
											$eArray[] = array("extraFieldName",$extraFieldsArray[$g]["name"],"S");
											$eArray[] = array("extraFieldTitle",$extraFieldsArray[$g]["title"],"S");
											$eArray[] = array("exvalID",0,"N");
											$eArray[] = array("content",$thisEFID,"S");
											$eArray[] = array("contentNative",$thisEFID,"S");
											$dbA->insertRecord($tableOrdersExtraFields,$eArray,0);					
											//exit;
								}
							} else {
								$thisEFContent = @$cartContents[$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]];
								
								$thisEFContentNative = @$cartContents[$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]];
								if (!empty($thisEFContent)) {
									$splitsID = explode("|",$thisEFID);
									$splitsContent = explode("|",$thisEFContent);
									$splitsContentNative = explode("|",$thisEFContentNative);
									for ($h = 0; $h < count($splitsID); $h++) {
										if (!empty($splitsID)) {
											$eArray = null;
											$eArray[] = array("orderID",$orderID,"N");
											$eArray[] = array("lineID",$lineID,"N");
											$eArray[] = array("extraFieldID",$extraFieldsArray[$g]["extraFieldID"],"N");
											$eArray[] = array("extraFieldName",$extraFieldsArray[$g]["name"],"S");
											$eArray[] = array("extraFieldTitle",$extraFieldsArray[$g]["title"],"S");
											$eArray[] = array("exvalID",$splitsID[$h],"N");
											$eArray[] = array("content",$splitsContent[$h],"S");
											$eArray[] = array("contentNative",$splitsContentNative[$h],"S");
											$dbA->insertRecord($tableOrdersExtraFields,$eArray,0);
											$stockFields[] = array("extraFieldID"=>$extraFieldsArray[$g]["extraFieldID"],"exvalID"=>$splitsID[$h],"content"=>$splitsContent[$h]);
										}
									}
								}
							}
						}
					}
				}
				$groupedResult = $dbA->query("select $tableProducts.*,$tableProductsGrouped.qty from $tableProducts,$tableProductsGrouped where $tableProducts.productID=$tableProductsGrouped.groupedID and $tableProductsGrouped.productID=".$cartMain["products"][$f]["productID"]." order by $tableProductsGrouped.position");
				if ($dbA->count($groupedResult) > 0) {
					//we have some grouped products here
					for ($h = 0; $h < $dbA->count($groupedResult); $h++) {
						$groupedRecord = $dbA->fetch($groupedResult);
						$xArray = null;
						$xArray[] = array("orderID",$orderID,"N");
						$xArray[] = array("lineID",$lineID,"N");
						$xArray[] = array("productID",$groupedRecord["productID"],"N");
						$xArray[] = array("code",$groupedRecord["code"],"S");
						$xArray[] = array("name",$groupedRecord["name"],"S");
						$xArray[] = array("nameNative",$groupedRecord["name"],"S");
						$xArray[] = array("qty",$groupedRecord["qty"],"N");
						$dbA->insertRecord($tableOrdersLinesGrouped,$xArray,0);
						if (retrieveOption("stockDeductMode") == 0 || (retrieveOption("stockDeductMode") == 1 && $currentStatus == "P")) {
							alterStock($groupedRecord["productID"],$cartMain["products"][$f]["qty"]*$groupedRecord["qty"],"");
						}
					}
				}
			}
			$rArray = null;
			$goodsTotal = $orderTotals["goodsTotal"];
			$shippingTotal = $orderTotals["shippingTotal"];
			$taxTotal = $orderTotals["taxTotal"];
			$shippingTotalGoods = $orderTotals["shippingTotalGoods"];
			$shippingTotalWeight = $orderTotals["shippingTotalWeight"];
			$shippingTotalQty = $orderTotals["shippingTotalQty"];		
			$discountAmount = $orderTotals["discountAmount"];

			//UPDATE ORDER HEADERS

			$ccNumber = getFORM("ccNumber");
			if ($ccNumber != "") {
				$ccNumber = base64_encode($crypt->encrypt($ccNumber, $teaEncryptionKey));
			}
			$rArray[] = array("offerCode",$orderInfoArray["offerCode"],"S");
			$rArray[] = array("customerID",$cartMain["customerID"],"S");
			$rArray[] = array("accTypeID",$cartMain["accTypeID"],"N");
			$rArray[] = array("orderNotes",getFORM("xOrderNotes"),"S");
			$rArray[] = array("email",getFORM("xEmail"),"S");
			$rArray[] = array("paymentID",getFORM("paymentID"),"S");
			$result = $dbA->query("select * from $tablePaymentOptions where paymentID=".getFORM("paymentID"));
			if ($dbA->count($result) > 0) {
				$payRecord = $dbA->fetch($result);
				$rArray[] = array("paymentName",$payRecord["name"],"S");
				$rArray[] = array("paymentNameNative",$payRecord["name"],"S");
			}
			$checkingString="01234567890 ";
			if (@$formArray["ccNumber"] != "") {
				@$formArray["ccNumber"] = base64_encode($crypt->encrypt($formArray["ccNumber"], $teaEncryptionKey));
			}
			if (@$formArray["ccCVV"] != "") {
				$invalidChar = false;
				for ($g = 0; $g < strlen($formArray["ccCVV"]); $g++) {
					$charFound = false;
					for ($h = 0; $h < strlen($checkingString); $h++) {
						if (substr($formArray["ccCVV"],$g,1) == substr($checkingString,$h,1)) {
							$charFound = true;
						}
					}
					if ($charFound == false) {
						$invalidChar = true;
					}
				}
				if ($invalidChar) {
					@$formArray["ccCVV"] = base64_encode($crypt->decrypt($formArray["ccCVV"], $teaEncryptionKey));
				}		
			}
			if ($formArray["paymentID"] == 1) {
				$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='CC'");
				for ($f = 0; $f < count($fieldList); $f++) {
					$rArray[] = array($fieldList[$f]["fieldname"],@$formArray[$fieldList[$f]["fieldname"]],"S");
				}
			}
			if (@$formArray["ccNumber"] != "") {
				$orderArray["ccNumber"] = $crypt->decrypt(base64_decode($formArray["ccNumber"]), $teaEncryptionKey);
			}


			$shippingID = makeInteger($orderInfoArray["shippingID"]);
			$result = $dbA->query("select * from $tableShippingTypes where shippingID=$shippingID");
			if ($dbA->count($result) > 0) {
				$shipRecord = $dbA->fetch($result);
				$rArray[] = array("shippingID",$shipRecord["shippingID"],"S");
				$rArray[] = array("shippingMethod",$shipRecord["name"],"S");
				$rArray[] = array("shippingMethodNative",$shipRecord["name"],"S");
			}
			$rArray[] = array("ccName",getFORM("ccName"),"S");
			$rArray[] = array("ccNumber",$ccNumber,"S");
			$rArray[] = array("ccType",getFORM("ccType"),"S");
			$rArray[] = array("ccExpiryDate",getFORM("ccExpiryDate"),"S");
			$rArray[] = array("ccStartDate",getFORM("ccStartDate"),"S");
			$rArray[] = array("ccIssue",getFORM("ccIssue"),"S");
			$rArray[] = array("ccCVV",getFORM("ccCVV"),"S");
			$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='C' and visible=1 and internalOnly=0 and incOrdering=1 order by position,fieldID");
			for ($f = 0; $f < count($fieldList); $f++) {
				$rArray[] = array($fieldList[$f]["fieldname"],getFORM($fieldList[$f]["fieldname"]),"S");
			}
			$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='D' and visible=1 and internalOnly=0 and incOrdering=1 order by position,fieldID");
			for ($f = 0; $f < count($fieldList); $f++) {
				$rArray[] = array($fieldList[$f]["fieldname"],getFORM($fieldList[$f]["fieldname"]),"S");
			}
			$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='O' and visible=1 order by position,fieldID");
			for ($f = 0; $f < count($fieldList); $f++) {
				$rArray[] = array($fieldList[$f]["fieldname"],getFORM($fieldList[$f]["fieldname"]),"S");
			}
			$rArray[] = array("goodsTotal",$goodsTotal,"D");
			$rArray[] = array("discountTotal",$discountAmount,"D");
			$rArray[] = array("shippingTotal",$shippingTotal,"D");
			$rArray[] = array("taxTotal",$taxTotal,"D");
			$rArray[] = array("giftCertTotal",$orderTotals["giftCertTotal"],"D");
			$dbA->updateRecord($tableOrdersHeaders,"orderID=$orderID",$rArray,0);
				
			$cartID = getFORM("cartID");
			$dbA->query("delete from $tableCarts where cartID='$cartID'");
			$dbA->query("delete from $tableCartsContents where cartID='$cartID'");
			doRedirect("orders.php?".userSessionGET());
			break;
		case "insert":
			break;
	}

	
	if (@$oRecord["authInfo"] != "") {
		$extraFields = $oRecord["authInfo"];

		$nameValues = split("&",$extraFields);
		$splitExtraFields = "";
		for ($f = 0; $f < count($nameValues); $f++) {
			$thisCode = split("=",$nameValues[$f]);
			$splitExtraFields[] = array($thisCode[0],$thisCode[1]);
		}
	}

	$checkingString="01234567890 ";
	if (@$oRecord["ccNumber"] != "") {
		$ccEnc = isValidCard($oRecord["ccNumber"]);
		$myCounter = 0;
		while ($ccEnc && $myCounter < 20) {
			$oRecord["ccNumber"] = $crypt->decrypt(base64_decode($oRecord["ccNumber"]), $teaEncryptionKey);
			$ccEnc = isValidCard($oRecord["ccNumber"]);
			$myCounter++;
		}
	}
	
	if (@$oRecord["ccCVV"] != "") {
		$ccEnc = isValidCard($oRecord["ccCVV"]);
		$myCounter = 0;
		while ($ccEnc && $myCounter < 20) {
			$oRecord["ccCVV"] = $crypt->decrypt(base64_decode($oRecord["ccCVV"]), $teaEncryptionKey);
			$ccEnc = isValidCard($oRecord["ccCVV"]);
			$myCounter++;
		}		
	}

	$customerText = "";
	if ($cartMain["customerID"] > 0) {
		$customerText .="Account Holder (ID = ".$cartMain["customerID"].")";
	} else {
		$customerText .="Non-Account Holder";
	}
	for ($f = 0; $f < count($accTypeArray); $f++) {
		if ($accTypeArray[$f]["accTypeID"] == $cartMain["accTypeID"]) {
			$customerText .= ", Account Type = ".$accTypeArray[$f]["name"];
		}
	}
?>
<HTML>
<HEAD>
<TITLE>Order Details</TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
</HEAD>
<script>
	function checkFields() {
		return true;
	}

	function changeCustomer() {
		document.detailsFormMain.xAction.value = "changecustomer";
		document.detailsFormMain.submit();
	}

	function recalculateTS() {
		document.detailsFormMain.xAction.value = "recalculatets";
		document.detailsFormMain.submit();
	}
</script>
<BODY class="detail-body">
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title"><?php print $pageTitle; ?></td>
	</tr>
</table>
<p>
<form name="detailsFormMain" method="POST" action="orders_edit.php">

<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<centeR>
<p>
<?php
	if (@$oRecord["status"] == "") {
		$oRecord["status"] = "N";
	}
	if ($oRecord["status"] != "D") {
?>
<iframe name="basketDetails" src="orders_edit_basket.php?xOrderID=<?php print $orderID; ?>&<?php print userSessionGET(); ?>&cartID=<?php print $cartID; ?>&firstTime=<?php print $firstTime; ?>" width="90%" height="250" frameborder="0" STYLE="border:solid black 1px"></iframe>
<?php
	} else {
?>
<font class="text-error"><b>You cannot edit the basket for this order as it has already been dispatched</b></font>
<?php
	}
?>
<p>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-grey-nocenter" valign="top" colspan="2">General Order Details</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Email Address</td>
		<td class="table-list-entry1" valign="top"><?php 
				if ($firstTime == 1) {
					$myForm->createText("xEmail",40,250,@getGENERIC("email",$oRecord),"email");
				} else {
					$myForm->createText("xEmail",40,250,@getGENERIC("xEmail",$formArray),"email");
				}
			?>
			<input type="button" name="buttonSetCustomer" class="button-grey" value="Set Customer" onClick="changeCustomer();">
			<br><?php print $customerText; ?>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Internal Order Notes</td>
		<td class="table-list-entry1" valign="top"><?php 
			if ($firstTime == 1) {
				$myForm->createTextArea("xOrderNotes",60,5,@getGENERIC("orderNotes",$oRecord),""); 
			} else {
				$myForm->createTextArea("xOrderNotes",60,5,@getGENERIC("xOrderNotes",$formArray),""); 
			}
		?></td>
	</tr>	
	<tr>
		<td class="table-grey-nocenter" valign="top" colspan="2">Customer Address Details</td>
	</tr>
<?php
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='C' and visible=1 and internalOnly=0 and incOrdering=1 order by position,fieldID");
		if ($firstTime == 1) {
			outputFields($fieldList,$oRecord);
		} else {
			outputFields($fieldList,$formArray);
		}
?>
	<tr>
		<td class="table-grey-nocenter" valign="top" colspan="2">Delivery Address Details</td>
	</tr>
<?php
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='D' and visible=1 and internalOnly=0 and incOrdering=1 order by position,fieldID");
		if ($firstTime == 1) {
			outputFields($fieldList,$oRecord);
		} else {
			outputFields($fieldList,$formArray);
		}
?>
	<tr>
		<td class="table-grey-nocenter" valign="top" colspan="2">Extra Order Field Details</td>
	</tr>
<?php
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='O' and visible=1 order by position,fieldID");
		if ($firstTime == 1) {
			outputFields($fieldList,$oRecord);
		} else {
			outputFields($fieldList,$formArray);
		}
?>
		<tr>
				<td class="table-grey-nocenter" valign="top" colspan="2">Payment Details</td>
		</tr>
		<tr>
			<td class="table-list-title" valign="top">Payment Method</td>
			<td class="table-list-entry1" valign="top">
				<select name="paymentID" class="form-inputbox">

<?php
			$result = $dbA->query("select * from $tablePaymentOptions where enabled='Y' and (accTypes like '%;0;%' or accTypes like '%;".$cartMain["accTypeID"].";%') order by position,name");

			$count = $dbA->count($result);
			$showCCFields = FALSE;
			for ($f = 0; $f < $count; $f++) {
				$pArray = $dbA->fetch($result);
				if ($firstTime == 1) {
					$currentPaymentID = $oRecord["paymentID"];
				} else {
					$currentPaymentID = @$formArray["paymentID"];
				}
				if ($pArray["type"] == "CC") {
					$ccResult = $dbA->query("select * from $tableCCProcessing where gateway='".$pArray["gateway"]."'");
					$ccRecord = @$dbA->fetch($ccResult);
					if ($ccRecord["askCC"] == "Y") {
						$showCCFields = TRUE;
					}
				}
?>
<option value="<?php print $pArray["paymentID"]; ?>" <?php if ($pArray["paymentID"] == $currentPaymentID) { echo "SELECTED"; } ?>><?php print $pArray["name"]; ?></option>
<?php
			}
?>
				</select>
			</td>
		</tr>

<?php
		if ($showCCFields) {
			if ($firstTime == 1) {
				outputSpecificField("ccName",@$oRecord["ccName"],"Name On Card",40,250,"general");
				outputSpecificField("ccNumber",@$oRecord["ccNumber"],"Credit Card Number",40,30,"general");
				outputSpecificField("ccType",@$oRecord["ccType"],"Credit Card Type",30,200,"general");
				outputSpecificField("ccExpiryDate",@$oRecord["ccExpiryDate"],"Expiry Date",10,5,"general");
				outputSpecificField("ccStartDate",@$oRecord["ccStartDate"],"Start Date",10,5,"general");
				outputSpecificField("ccIssue",@$oRecord["ccIssue"],"Issue Number",10,3,"general");
				outputSpecificField("ccCVV",@$oRecord["ccCVV"],"CVV",10,5,"general");
			} else {
				outputSpecificField("ccName",@$formArray["ccName"],"Name On Card",40,250,"general");
				outputSpecificField("ccNumber",@$formArray["ccNumber"],"Credit Card Number",40,30,"general");
				outputSpecificField("ccType",@$formArray["ccType"],"Credit Card Type",30,200,"general");
				outputSpecificField("ccExpiryDate",@$formArray["ccExpiryDate"],"Expiry Date",10,5,"general");
				outputSpecificField("ccStartDate",@$formArray["ccStartDate"],"Start Date",10,5,"general");
				outputSpecificField("ccIssue",@$formArray["ccIssue"],"Issue Number",10,3,"general");
				outputSpecificField("ccCVV",@$formArray["ccCVV"],"CVV",10,5,"general");
			}
		}
?>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;
		<input type="button" name="goButton" value="<?php print $submitButton; ?>" onClick="document.detailsFormMain.submit();" class="button-save">
		</td>
	</tr>
</table>
</form>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>

<?php
	function outputFields($fieldList,$uRecord) {
		global $myForm,$dbA,$tableCountries,$oRecord;
		for ($f = 0; $f < count($fieldList); $f++) {
			if ($fieldList[$f]["fieldtype"] == "TEXT") {
				?><tr>
					<td class="table-list-title" valign="top"><?php print $fieldList[$f]["titleText"]; ?></td>
					<td class="table-list-entry1" valign="top"><?php $myForm->createText($fieldList[$f]["fieldname"],$fieldList[$f]["size"],$fieldList[$f]["maxlength"],@getGENERIC($fieldList[$f]["fieldname"],$uRecord),"general"); ?></td>
				</tr><?php
			}
			if ($fieldList[$f]["fieldtype"] == "TEXTAREA") {
				?><tr>
					<td class="table-list-title" valign="top"><?php print $fieldList[$f]["titleText"]; ?></td>
					<td class="table-list-entry1" valign="top"><?php $myForm->createTextArea($fieldList[$f]["fieldname"],$fieldList[$f]["cols"],$fieldList[$f]["rows"],@getGENERIC($fieldList[$f]["fieldname"],$uRecord),"general"); ?></td>
				</tr><?php
			}
			if ($fieldList[$f]["fieldtype"] == "CHECKBOX") {
				if (@$uRecord[$fieldList[$f]["fieldname"]] != "") {
					$thisChecked = "CHECKED";
				} else {
					$thisChecked = "";
				}
				?><tr>
					<td class="table-list-entry1" valign="top" colspan="2"><input type="checkbox" name="<?php print $fieldList[$f]["fieldname"]; ?>" value="Y" <?php print $thisChecked; ?>> <?php print $fieldList[$f]["titleText"]; ?></td>
				</tr><?php
			}
			if ($fieldList[$f]["fieldtype"] == "SELECT" && ($fieldList[$f]["fieldname"] != "country" && $fieldList[$f]["fieldname"] != "deliveryCountry")) {
				?><tr>
					<td class="table-list-title" valign="top"><?php print $fieldList[$f]["titleText"]; ?></td>
					<td class="table-list-entry1" valign="top"><select name="<?php print $fieldList[$f]["fieldname"]; ?>" class="form-inputbox">
					<?php
						$currentValue = @getGENERIC($fieldList[$f]["fieldname"],$uRecord);
						$contentBits = split(";",$fieldList[$f]["contentvalues"]);
						for ($g= 0 ; $g < count($contentBits); $g++) {
							if ($contentBits[$g] != "") {
								if ($currentValue == $contentBits[$g]) {
									$thisSelected = "SELECTED";
								} else {
									$thisSelected = "";
								}
								?> <option <?php print $thisSelected; ?>><?php print $contentBits[$g]; ?></option> <?php
							}
						}
						?></select>
					</td>
				</tr> <?php
			}
			if ($fieldList[$f]["fieldtype"] == "SELECT" && ($fieldList[$f]["fieldname"] == "country" || $fieldList[$f]["fieldname"] == "deliveryCountry")) {
				?><tr>
					<td class="table-list-title" valign="top"><?php print $fieldList[$f]["titleText"]; ?></td>
					<td class="table-list-entry1" valign="top"><select name="<?php print $fieldList[$f]["fieldname"]; ?>" class="form-inputbox">
					<?php
						$currentValue = @getGENERIC($fieldList[$f]["fieldname"],$uRecord);
						$result = $dbA->query("select * from $tableCountries where visible='Y' order by name");
						$count = $dbA->count($result);
						for ($g= 0 ; $g < $count; $g++) {
							$record = $dbA->fetch($result);
							if ($currentValue == $record["name"]) {
								$thisSelected = "SELECTED";
							} else {
								$thisSelected = "";
							}
							?> <option <?php print $thisSelected; ?>><?php print $record["name"]; ?></option> <?php
						}
					?>
					</select>
					<?php if ($oRecord["status"] != "D") { ?>
							<input type="button" name="buttonSetCustomer" class="button-grey" value="Re-Calculate Tax &amp; Shipping" onClick="recalculateTS()";>
					<?php } ?>
					</td>
				</tr><?php
			}
		}
	}
?>
<?php
	function outputSpecificField($fieldName,$fieldValue,$fieldTitle,$xSize,$xMaxLength,$xValidation) {
		global $myForm;
		//if ($fieldValue == "") { return; }
?>
		<tr>
			<td class="table-list-title" valign="top"><?php print $fieldTitle; ?></td>
			<td class="table-list-entry1" valign="top"><?php $myForm->createText($fieldName,$xSize,$xMaxLength,$fieldValue,$xValidation); ?></td>
		</tr>
<?php		
	}

	function calculatePriceInBase($thePrice) {
		global $currArray,$cartID,$cartMain;
		if ($thePrice > 0) {
			$thePrice = $thePrice / $cartMain["currency"]["exchangerate"];
		}
		return $thePrice;
	}
?>