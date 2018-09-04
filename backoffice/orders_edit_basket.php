<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("../routines/cartOperations.php");
	include("../routines/orderOperations.php");
	include("../routines/taxOperations.php");
	include("../routines/affiliateTracking.php");
	include("../routines/giftCerts.php");
	include("../routines/stockControl.php");
	$myForm = new formElements;
	
	dbConnect($dbA);

	$firstTime = makeInteger(getFORM("firstTime"));
	$xAction = getFORM("xAction");

	if ($firstTime != 1) { $firstTime = 0; }

	$orderID = getFORM("xOrderID");
	$showID = getFORM("xOrderID") + retrieveOption("orderNumberOffset");

	$cartID = getFORM("cartID");

	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	$langArray = $dbA->retrieveAllRecords($tableLanguages,"languageID");
	$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"position,name");
	$accTypeArray = $dbA->retrieveAllRecords($tableCustomersAccTypes,"accTypeID");
	$flagArray = $dbA->retrieveAllRecords($tableProductsFlags,"flagID");

	$result = $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
	
	$oRecord = $dbA->fetch($result);

	$hiddenFields = "<input type='hidden' name='xAction' value='updatebasket'><input type='hidden' name='xOrderID' value='$orderID'><input type='hidden' name='cartID' value='$cartID'>".hiddenReturnPOST();
	
	$cartMain = null;
	$cartProducts = null;

	$cartResult = $dbA->query("select * from $tableCarts where cartID=\"$cartID\"");
	$cartMain = $dbA->fetch($cartResult);
	cartRetrieveCart(TRUE);
	$currencyID = $cartMain["currencyID"];

	if (@$cartMain["customerID"] > 0) {
		//LOAD UP THE CUSTOMER DETAILS
		$custResult = $dbA->query("select * from $tableCustomers where customerID=".$cartMain["customerID"]);
		$customerMain = $dbA->fetch($custResult);
	}

	$orderInfoArray = retrieveOrderInformation();

	if ($xAction == "changeshipping" && getFORM("xShippingLock") != "Y") {
		$newShippingID = makeInteger(getFORM("xShippingMethod"));
		$oldShippingID = $orderInfoArray["shippingID"];
		if ($newShippingID > 0) {
			$orderInfoArray["shippingID"] = checkShippingID($newShippingID);
			if ($orderInfoArray["shippingID"] != $oldShippingID) {
				$orderInfoArray["productsEdited"] = "Y";
			}
		}
		$orderString = commitOrderInformation();
	}

	$xZeroTax = getFORM("xZeroTax");
	if ($xZeroTax == "Y") {
		$orderInfoArray["zeroTax"] = "Y";
	} else {
		$orderInfoArray["zeroTax"] = "N";
	}

	if ($orderInfoArray["zeroTax"] == "Y") {
		$customerMain["taxExempt"] = "Y";
	}
	$xShippingLock = getFORM("xShippingLock");
	if ($xShippingLock == "") { $xShippingLock = "N"; }
	$orderInfoArray["shippingLock"] = $xShippingLock;
	$xDiscountLock = getFORM("xDiscountLock");
	if ($xDiscountLock == "") { $xDiscountLock = "N"; }
	$orderInfoArray["discountLock"] = $xDiscountLock;
	$orderInfoArray["lockedDiscountTotal"] = makeDecimal(getFORM("xDiscount"));
	$orderInfoArray["lockedShippingTotal"] = makeDecimal(getFORM("xShipping"));
	$currentValues["shippingTotal"] = makeDecimal(getFORM("xShipping"));
	$currentValues["discountTotal"] = makeDecimal(getFORM("xDiscount"));
	$orderString = commitOrderInformation();
	cartRetrieveCart(TRUE);
	$formArray = null;
	foreach($_GET as $key => $value) {
		$formArray[$key] = $value;
	}
	foreach($_POST as $key => $value) {
		$formArray[$key] = $value;
	}

	if ($xAction == "updatebasket") {
		$orderInfoArray["productsEdited"] = "Y";
		//This is where we work out the changes to make
		$changed = FALSE;
		$oldCartMain = $cartMain;
		for ($f = 0; $f < count($oldCartMain["products"]); $f++) {
			if (getFORM("xRemove".$oldCartMain["products"][$f]["uniqueID"]) == "Y") {
				cartRemoveItem($oldCartMain["products"][$f]["uniqueID"]);
				$changed = TRUE;
			}
		}
		$oldCartMain = $cartMain;
		cartUpdateItems($formArray,TRUE);	

		//PROCESS ANY PRICE LOCKS HERE
		for ($f = 0; $f < count($cartMain["products"]); $f++) {
			if (getFORM("pricelock".$cartMain["products"][$f]["uniqueID"]) == "Y") {
				$dbA->query("update $tableCartsContents set price".$cartMain["currencyID"]." = ".makeDecimal(getFORM("xPrice".$cartMain["products"][$f]["uniqueID"]))." where uniqueID=".$cartMain["products"][$f]["uniqueID"]);
				$dbA->query("update $tableCartsContents set ooPrice".$cartMain["currencyID"]." = ".makeDecimal(getFORM("xOOPrice".$cartMain["products"][$f]["uniqueID"]))." where uniqueID=".$cartMain["products"][$f]["uniqueID"]);
				//cartRemoveItem($cartMain["products"][$f]["uniqueID"]);
				$changed = TRUE;
			}
		}
		cartRetrieveCart(TRUE);
	}
	if ($xAction == "addproduct") {
		cartAddItem(getFORM("xProd"),getFORM("xQty"),$formArray);
		$cartResult = $dbA->query("select * from $tableCarts where cartID=\"$cartID\"");
		$cartMain = $dbA->fetch($cartResult);
		$orderInfoArray["productsEdited"] = "Y";
		$orderString = commitOrderInformation();
		cartRetrieveCart(TRUE);
	}
	if ($xAction == "addproductextended") {
		$extendedlist = getFORM("extendedlist");
		$bits = explode("&",$extendedlist);
		for ($f = 0; $f < count($bits); $f++) {
			$thisOne = explode("=",$bits[$f]);
			$formArray[$thisOne[0]] = urldecode($thisOne[1]);
		}
		cartAddItem(getFORM("xProd"),getFORM("xQty"),$formArray);
		$cartResult = $dbA->query("select * from $tableCarts where cartID=\"$cartID\"");
		$cartMain = $dbA->fetch($cartResult);
		$orderInfoArray["productsEdited"] = "Y";
		$orderString = commitOrderInformation();
		cartRetrieveCart(TRUE);
	}
	$orderTotals = calculateOrderTotals(TRUE,$currentValues);
	$discountCodeError = "";
	if ($xAction == "setoffercode" && getFORM("xDiscountLock") != "Y") {
		$dcReturn = offerCodeValid(getFORM("xOfferCode"),$orderTotals,TRUE);
		if ($dcReturn == "OK") {
			$orderInfoArray["offerCode"] = getFORM("xOfferCode");
			$orderInfoArray["productsEdited"] = "Y";
			$orderString = commitOrderInformation();
			$orderTotals = calculateOrderTotals(TRUE,$currentValues);
		} else {
			if (getFORM("xOfferCode") == "") {
				$orderInfoArray["offerCode"] = "";
				$orderInfoArray["productsEdited"] = "Y";
				$orderString = commitOrderInformation();
				$orderTotals = calculateOrderTotals(TRUE,$currentValues);
			} else {
				//FAILED, NEED ERROR CHECKING
				switch ($dcReturn) {
					case "CURRENCY":
						$discountCodeError = "Incorrect currency";
						break;
					case "EXPIRED":
						$discountCodeError = "Code has expired";
						break;
					case "BELOWVALUE":
						$discountCodeError = "Goods total not sufficient";
						break;
				}
			}
		}
	}

	if ($orderInfoArray["productsEdited"] == "N" && $orderInfoArray["shippingLock"] == "N") {
		$orderTotals["goodsTotal"] = $oRecord["goodsTotal"];
		if ($orderInfoArray["shippingLock"] == "Y") {
			$orderTotals["shippingTotal"] = $orderInfoArray["lockedShippingTotal"];
		} else {
			$orderTotals["shippingTotal"] = $oRecord["shippingTotal"];
		}
		$orderTotals["taxTotal"] = $oRecord["taxTotal"];
		$orderTotals["giftCertTotal"] = $oRecord["giftCertTotal"];
		if ($orderInfoArray["discountLock"] == "Y") {
			$orderTotals["discountAmount"] = $orderInfoArray["lockedDiscountTotal"];
		} else {
			$orderTotals["discountAmount"] = $oRecord["discountTotal"];
		}
	}

	$totalWeight = $orderTotals["shippingTotalWeight"];
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

	$atype = $cartMain["accTypeID"];
	if ($atype == 0) {
		$atype = -1;
	}
	$priceBit = "";
	$goodsCheckTotal = $orderTotals["goodsTotal"];
	if ($goodsCheckTotal == "") { $goodsCheckTotal = 0; }
	if ($totalWeight == "") { $totalWeight = 0; }
	if ($cartMain["currency"]["currencyID"] == 1 || $cartMain["currency"]["useexchangerate"] == "N") {
		$priceBit = "and ($tableShippingTypes.highprice".$cartMain["currency"]["currencyID"]." = 0 or $tableShippingTypes.highprice".$cartMain["currency"]["currencyID"]." > $goodsCheckTotal) and ($tableShippingTypes.lowprice".$cartMain["currency"]["currencyID"]." = 0 or $goodsCheckTotal > $tableShippingTypes.lowprice".$cartMain["currency"]["currencyID"].")";
	} else {
		if ($cartMain["currency"]["useexchangerate"] == "Y") {
			$goodsCheckTotalBase = calculatePriceInBase($goodsCheckTotal);
			$priceBit = "and ($tableShippingTypes.highprice1 = 0 or $tableShippingTypes.highprice1 > $goodsCheckTotalBase) and ($tableShippingTypes.lowprice1 = 0 or $goodsCheckTotalBase > $tableShippingTypes.lowprice1)";
		}
	}

	$shippingArray = $dbA->retrieveAllRecordsFromQuery("select $tableShippingTypes.* from $tableShippingTypes,$tableShippingRates where ($tableShippingTypes.weight = 0 or $tableShippingTypes.weight > $totalWeight) and ($tableShippingTypes.lowweight = 0 or $totalWeight > $tableShippingTypes.lowweight) $priceBit and $tableShippingRates.shippingID = $tableShippingTypes.shippingID and $tableShippingRates.zoneID = $zoneID and accTypeID=0 or accTypeID=".$atype." group by $tableShippingTypes.shippingID");

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
	}

	function changeShipping() {
		document.detailsForm.xAction.value = "changeshipping";
		document.detailsForm.submit();
	}

	function setOfferCode() {
		document.detailsForm.xAction.value = "setoffercode";
		document.detailsForm.submit();
	}

	var productSearchWindow = null;
	var productOptionsWindow = null;

	function openProductSearchWindow() {
		reload = true;
		if (productSearchWindow) {
			if (productSearchWindow.closed) {
				reload = true;
			} else {
				reload = false;
			}
		}
		if (reload) {
			productSearchWindow = window.open("orders_edit_products.php?xType=new&xOrderID=<?php print $orderID; ?>&<?php print userSessionGET(); ?>&cartID=<?php print $cartID; ?>","ordersProductSearchWindow","height=500,width=600,scrollbars=yes,status=yes,toolbar=no,menubar=no,location=no,resizable=yes");
		}
		productSearchWindow.focus();
	}

	function productSelect(prod) {
		reload = true;
		if (reload) {
			productOptionsWindow = window.open("orders_edit_products_options.php?xOrderID=<?php print $orderID; ?>&<?php print userSessionGET(); ?>&cartID=<?php print $cartID; ?>&xProd="+prod,"ordersProductSelectOptions","height=500,width=600,scrollbars=yes,status=yes,toolbar=no,menubar=no,location=no,resizable=yes");
		}
		productOptionsWindow.focus();
	}

	function cleanupWindows() {
		//if (productSearchWindow) {
		//	if (!productSearchWindow.closed) {
		//		productSearchWindow.close;
		//	}
		//}
		//if (productOptionsWindow) {
		//	if (!productOptionsWindow.closed) {
		//		productOptionsWindow.close;
		//	}
		//}
	}

	function productAdd(prod,qty) {
		document.detailsForm.xProd.value = prod;
		document.detailsForm.xQty.value = qty;
		document.detailsForm.xAction.value = "addproduct";
		document.detailsForm.submit();
	}

	function productAddExtended(prod,qty,extendedlist) {
		document.detailsForm.xProd.value = prod;
		document.detailsForm.xQty.value = qty;
		document.detailsForm.extendedlist.value = extendedlist;
		document.detailsForm.xAction.value = "addproductextended";
		document.detailsForm.submit();
	}

</script>
<BODY class="detail-body" onUnload="cleanupWindows();">
<?php $myForm->createForm("detailsForm","orders_edit_basket.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<input type="hidden" name="xProd" value="">
<input type="hidden" name="xQty" value="">
<input type="hidden" name="extendedlist" value="">
<centeR>
<p>
<table width="98% cellpadding="2" cellspacing="0" class="table-list">
<tr>
	<td align="left"><font class="boldtext"><input type="checkbox" name="xZeroTax" value="Y" <?php if ($xZeroTax == "Y") { echo "CHECKED"; } ?>> Zero Tax Charges</font></td>
	<td align="right"><input type="button" class="button-grey" value="Add Products" onClick="openProductSearchWindow();">&nbsp;
	<input type="button" value="Update Order Lines" class="button-save" onClick="document.detailsForm.submit();">
	</td>
</tr>
</table>
<p>
<table width="98%" cellpadding="2" cellspacing="0" class="table-outline-white">
<tr>
	<td class="table-white-nocenter-s" align="center">Remove</td>
	<td class="table-white-nocenter-s" align="left">Product Code</td>
	<td class="table-white-nocenter-s" align="left">Product Details</td>
	<td class="table-white-nocenter-s" align="right">Quantity</td>
	<td class="table-white-nocenter-s" align="right">Price Each</td>
	<td class="table-white-nocenter-s" align="right">Total Cost</td>
</tr>
<?php
	for ($f = 0; $f < count($cartMain["products"]); $f++) {
		$giftCert = "";
		if ($cartMain["products"][$f]["code"]=="GIFTCERT") {
			//this is a gift certificate
			$result = $dbA->query("select * from $tableGiftCertificates where orderID=$orderID");
			if ($dbA->count($result) > 0) {
				$gRecord = $dbA->fetch($result);
				switch ($gRecord["type"]) {
					case "E":
						$gType = "Email to ".$gRecord["emailaddress"];
						break;
					case "P":
						$gType = "Postal to delivery address below";
						break;
				}
				switch ($gRecord["status"]) {
					case "N":
						$gStatus = "Not Activated";
						break;
					case "A":
						$gStatus = "Activated";
						break;
				}
				$giftCert .= "<BR>";
				$giftCert .= "Certificate: ".$gRecord["certSerial"]."<BR>";
				$giftCert .= "Status: ".$gStatus."<BR>";
				$giftCert .= "From: ".$gRecord["fromname"]."<BR>";
				$giftCert .= "To: ".$gRecord["toname"]."<BR>";
				$giftCert .= $gType."<BR>";
				$giftCert .= "<BR>Message:<BR>".eregi_replace("\r\n","<BR>",$gRecord["message"])."<BR>";
			}
		}
		$fieldDisplay = "";
		if (is_array($extraFieldsArray)) {
			for ($g = 0; $g < count($extraFieldsArray); $g++) {
				$thisExtraField = "";
				switch ($extraFieldsArray[$g]["type"]) {
					case "USERINPUT":
						if ($cartMain["products"][$f]["extrafieldid".$extraFieldsArray[$g]["extraFieldID"]] != "") {
							$fieldDisplay .= "<br>".$extraFieldsArray[$g]["title"].": ".$cartMain["products"][$f]["extrafieldid".$extraFieldsArray[$g]["extraFieldID"]];
						}
						break;
					case "SELECT":
					case "RADIOBUTTONS":
						if ($cartMain["products"][$f]["extrafieldid".$extraFieldsArray[$g]["extraFieldID"]] > 0) {
							if (array_key_exists("extrafield".$extraFieldsArray[$g]["extraFieldID"],$cartMain["products"][$f])) {
								$fieldDisplay .= "<br>".$extraFieldsArray[$g]["title"].": ".$cartMain["products"][$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]];
							} else {
								$fieldDisplay .= "<br>".$extraFieldsArray[$g]["title"].": "."Option no longer exists - see order details screen";
								$cartMain["products"][$f]["invalidOption"] = "Y";
							}
						}
						break;								
					case "CHECKBOXES":
						$splitBits = @$cartMain["products"][$f]["extrafieldid".$extraFieldsArray[$g]["extraFieldID"]];
						//$optionsSplit = explode("|",$cartMain["products"][$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]]);
						$optionsSplit = explode("|",$splitBits);
						$optionArray = "";
						if (is_array($optionsSplit)) {
							for ($h = 0; $h < count($optionsSplit); $h++) {
								if (chop($optionsSplit[$h]) != "") {
									$optionArray[]=array("option"=>$optionsSplit[$h]);
								}
							}
							if (is_array($optionArray)) {
								$fieldDisplay .= "<BR>".$extraFieldsArray[$g]["title"].": ";
								for ($h = 0; $h < count($optionArray); $h++) {
									if ($h != 0) {
										$fieldDisplay .= ", ";
									}
									$fieldDisplay .= $optionArray[$h]["option"];
								}
							}
						}
						$thisExtraField["name"] = $extraFieldsArray[$g]["name"];
						$thisExtraField["title"] = $extraFieldsArray[$g]["title"];
						$thisExtraField["type"] = $extraFieldsArray[$g]["type"];
						$thisExtraField["content"] = @$cartMain["products"][$f]["extrafieldid".$extraFieldsArray[$g]["extraFieldID"]];
						$thisExtraField["options"] = $optionArray;
											
						$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["name"] = $extraFieldsArray[$g]["name"];
						$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["title"] = $extraFieldsArray[$g]["title"];
						$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["type"] = $extraFieldsArray[$g]["type"];
						$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["content"] = @$splitBits;
						$cartMain["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["options"] = $optionArray;
						$allExtraFields[] = $thisExtraField;
						break;
				}
			}		
		}
?>
		<tr>
			<td class="table-white-nocenter-light-s" align="center" valign="top"><input type="checkbox" name="xRemove<?php print $cartMain["products"][$f]["uniqueID"]; ?>" value="Y"></td>
			<td class="table-white-nocenter-light-s" align="left" valign="top"><?php print $cartMain["products"][$f]["code"]; ?></td>
			<td class="table-white-nocenter-light-s" align="left" valign="top"><b><?php print $cartMain["products"][$f]["name"]; ?></b><?php print $fieldDisplay; ?><?php print $giftCert; ?>
			<?php
				if ($cartMain["products"][$f]["limitedStock"] == "Y") {
					?><br><font class="text-error"><b>Limited Stock</b></font><?php
				}
			?>
			</td>
			<td class="table-white-nocenter-light-s" align="right" valign="top"><?php $myForm->createText("qty".$cartMain["products"][$f]["uniqueID"],5,5,$cartMain["products"][$f]["qty"],"integer"); ?></td>
			<td class="table-white-nocenter-light-s" align="right" valign="top">
				<?php
					if ($cartMain["products"][$f]["code"] == "GIFTCERT" || @$cartMain["products"][$f]["invalidOption"] == "Y") {
				?>
					<input type="hidden" name="pricelock<?php print $cartMain["products"][$f]["uniqueID"]; ?>" value="Y">
				<?php
					} else {
				?>
					<input type="checkbox" name="pricelock<?php print $cartMain["products"][$f]["uniqueID"]; ?>" value="Y" <?php if (getFORM("pricelock".$cartMain["products"][$f]["uniqueID"]) == "Y") { echo "CHECKED"; } ?>>
				<?php
					}
				?>
				<?php 	$myForm->createText("xPrice".$cartMain["products"][$f]["uniqueID"],8,15,$cartMain["products"][$f]["price".$cartMain["currencyID"]],"decimal");
				?><?php if ($cartMain["products"][$f]["ooPrice".$cartMain["currencyID"]] != 0) { ?><br>+ One-Off <?php $myForm->createText("xOOPrice".$cartMain["products"][$f]["uniqueID"],8,15,$cartMain["products"][$f]["ooPrice".$cartMain["currencyID"]],"decimal");
					?>
				<?php } ?>
			</td>
			<td class="table-white-nocenter-light-s" align="right" valign="top"><?php print priceFormat((roundWithoutCalcPrice($cartMain["products"][$f]["price".$cartMain["currencyID"]])*$cartMain["products"][$f]["qty"])+$cartMain["products"][$f]["ooPrice".$cartMain["currencyID"]],$cartMain["currencyID"]); ?></td>
		</tr>
<?php
	}
?>
<tr>
	<td class="table-white-nocenter-s" colspan="5" align="left">Goods Total</td>
	<td class="table-white-nocenter-s" align="right"><?php print priceFormat($orderTotals["goodsTotal"],$currencyID); ?></td>
</tr>
<tr>
	<td class="table-white-nocenter-s" colspan="5" align="left">Shipping Total 
	<select class="form-inputbox" name="xShippingMethod">
	<?php 
		if (is_array($shippingArray)) {
			for ($h = 0; $h < count($shippingArray); $h++) {
				?><option value="<?php print $shippingArray[$h]["shippingID"]; ?>" <?php if($shippingArray[$h]["shippingID"] == $orderInfoArray["shippingID"]) { echo "SELECTED"; } ?>><?php print $shippingArray[$h]["name"]; ?></option><?php
			}
		}
		//$myForm->createText("xShippingMethod",40,150,$oRecord["shippingMethod"],"general"); 
	?>
	</select>
	<input type="button" name="xChangeShipping" class="button-grey" value="Re-Calculate Shipping" onClick="changeShipping();">
	</td>
	<td class="table-white-nocenter-s" align="right"><input type="checkbox" name="xShippingLock" value="Y" <?php if ($xShippingLock == "Y") { echo "CHECKED"; } ?>><?php $myForm->createText("xShipping",8,15,$orderTotals["shippingTotal"],"decimal"); ?></td>
</tr>
<tr>
	<td class="table-white-nocenter-s" colspan="5" align="left">Tax Total</td>
	<td class="table-white-nocenter-s" align="right"><?php print priceFormat($orderTotals["taxTotal"],$currencyID); ?></td>
</tr>
<?php
	if ($orderTotals["giftCertTotal"] > 0) {
?>
<tr>
	<td class="table-white-nocenter-s" colspan="5" align="left">Gift Certificate Total</td>
	<td class="table-white-nocenter-s" align="right">-<?php print priceFormat($orderTotals["giftCertTotal"],$currencyID); ?></td>
</tr>
<?php
	}
?>
<tr>
	<td class="table-white-nocenter-s" colspan="5" align="left">Discount Total
		<?php $myForm->createText("xOfferCode",15,40,@$orderInfoArray["offerCode"],"general"); ?>
		<input type="button" name="xSetOfferCode" class="button-grey" value="Set Offer Code" onClick="setOfferCode();">
		<?php if ($discountCodeError != "") { ?>
			<font class="text-error"><?php print $discountCodeError; ?></font>
		<?php } ?>
	</td>
	<td class="table-white-nocenter-s" align="right"><input type="checkbox" name="xDiscountLock" value="Y" <?php if ($xDiscountLock == "Y") { echo "CHECKED"; } ?>>-<?php $myForm->createText("xDiscount",8,15,$orderTotals["discountAmount"],"decimal"); ?></td>
</tr>
<tr>
	<td class="table-white-nocenter-s" colspan="5" align="left">Order Total</td>
	<td class="table-white-nocenter-s" align="right"><?php print priceFormat($orderTotals["goodsTotal"]+$orderTotals["shippingTotal"]+$orderTotals["taxTotal"]-$orderTotals["discountAmount"]-$orderTotals["giftCertTotal"],$currencyID); ?></td>
</tr>
</table>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();

	function calculatePriceInBase($thePrice) {
		global $currArray,$cartID,$cartMain;
		if ($thePrice > 0) {
			$thePrice = $thePrice / $cartMain["currency"]["exchangerate"];
		}
		return $thePrice;
	}
?>