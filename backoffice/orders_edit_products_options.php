<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("../routines/cartOperations.php");
	include("../routines/orderOperations.php");
	include("../routines/taxOperations.php");
	include("../routines/affiliateTracking.php");
	include("../routines/giftCerts.php");
	include("../routines/stockControl.php");
	include("../routines/productOperations.php");

	dbConnect($dbA);

	$orderID = getFORM("xOrderID");
	$showID = getFORM("xOrderID") + retrieveOption("orderNumberOffset");

	$cartID = getFORM("cartID");

	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	$langArray = $dbA->retrieveAllRecords($tableLanguages,"languageID");
	$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"position,name");
	$accTypeArray = $dbA->retrieveAllRecords($tableCustomersAccTypes,"accTypeID");
	$flagArray = $dbA->retrieveAllRecords($tableProductsFlags,"flagID");

	$cartResult = $dbA->query("select * from $tableCarts where cartID=\"$cartID\"");
	$cartMain = $dbA->fetch($cartResult);
	cartRetrieveCart(TRUE);
	$orderInfoArray = retrieveOrderInformation();

	if (@$cartMain["customerID"] > 0) {
		//LOAD UP THE CUSTOMER DETAILS
		$custResult = $dbA->query("select * from $tableCustomers where customerID=".$cartMain["customerID"]);
		$customerMain = $dbA->fetch($custResult);
	}

	$taxRates = retrieveTaxRates($orderInfoArray["country"],@$orderInfoArray["county"],@$orderInfoArray["deliveryCountry"],@$orderInfoArray["deliveryCounty"]);

	$xProd = getFORM("xProd");

	$theQuery = "select * from $tableProducts where productID=$xProd";
	$productsArray = retrieveProducts($theQuery,$counter,"s",1,TRUE);

	$theBasePrice = calculatePrice($productsArray["price1"],$productsArray["price".$cartMain["currency"]["currencyID"]],$cartMain["currency"]["currencyID"]);
	$theOOBasePrice = calculatePrice($productsArray["ooPrice1"],$productsArray["ooPrice".$cartMain["currency"]["currencyID"]],$cartMain["currency"]["currencyID"]);
	$advPricing = retrieveAdvancedPricing($xProd,$theBasePrice,$theOOBasePrice,$gotsome,$productsArray,$quantityTable,$combinationsTable,$exclusionsTable,$oneoffTable);
	$productsArray["pricing"]["quantitytable"] = $quantityTable;
	$productsArray["pricing"]["combinationstable"] = $combinationsTable;
	$productsArray["exclusionstable"] = $exclusionsTable;
	$productsArray["pricing"]["oneofftable"] = $oneoffTable;

	$myForm = new formElements;
?>
<HTML>
<HEAD>
<TITLE>Order Editing: Product Options: <?php print $productsArray["name"]; ?></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript1.2" src="../resources/jserver.js" type="text/javascript"></script>
<script language="JavaScript">
	function productAdd(prod,qty) {
		opener.productAdd(prod,qty);
	}

	function productSelect(prod) {
		opener.productSelect(prod);
	}
<?php
	if (getFORM("xAction") == "addtobasket") {
		$formArray = null;
		foreach($_GET as $key => $value) {
			$formArray[$key] = $value;
		}
		foreach($_POST as $key => $value) {
			$formArray[$key] = $value;
		}
		$formString = "";
		foreach($formArray as $key => $value) {
			if ($formString != "") {
				$formString .= "&";
			}
			$formString .= $key."=".urlencode($value);
		}
?>
	opener.productAddExtended(<?php print $xProd; ?>,<?php print makeInteger(getFORM("qty".$xProd)); ?>,"<?php print $formString; ?>");
<?php
	}
?>
</script>
<?php print $advPricing; ?>
</HEAD>
<BODY class="detail-body">
<form name="<?php print $productsArray["form"]["name"]; ?>" METHOD="POST" action="orders_edit_products_options.php">
<input type="hidden" name="cartID" value="<?php print $cartID; ?>">
<input type="hidden" name="xOrderID" value="<?php print $orderID; ?>">
<input type="hidden" name="xProd" value="<?php print $xProd; ?>">
<input type="hidden" name="xAction" value="addtobasket">
<?php userSessionPOST(); ?>
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title"><?php print $productsArray["name"]; ?> (<a href="<?php print $jssStoreWebDirHTTP; ?>product.php?xProd=<?php print $xProd; ?>" target="_new">Click to view product page in store</a>)</td>
	</tr>
</table>
<p>
<table cellpadding="0" cellspacing="0" width="99%">
<tr>
	<td valign="top" width="49%">
<table cellpadding="2" cellspacing="0" class="table-list" width="100%">
	<tr>
		<td class="table-grey-nocenter" valign="top" colspan="2">Select Product Options</td>
	</tr>
	<tr>
		<td class="table-list-entry0" valign="top" colspan="2">
			<table border="0">
			<tr>
				<td><font class="normaltext"><b>&nbsp;</b></td>
				<td><font class="normaltext"><b>Excluding Tax</b></td>
				<td><font class="normaltext"><b>Including Tax</b></td>
			</tr>
			<tr>
				<td><font class="normaltext"><b>Normal Price:</b></td>
				<td><font class="text-error"><b><?php print $productsArray["price"]; ?></b></td>
				<td><font class="text-error"><b><?php print $productsArray["priceinctax"]; ?></b></td>
			</tr>
			<tr>
				<td><font class="normaltext"><b>One-Off Price:</b></td>
				<td><font class="text-error"><b><?php print $productsArray["ooprice"]; ?></b></td>
				<td><font class="text-error"><b><?php print $productsArray["oopriceinctax"]; ?></b></td>
			</tr>
			</table>
	</tr>
<?php
	if (@is_array(@$productsArray["extrafields"])) {
		for ($f = 0; $f < count($productsArray["extrafields"]); $f++) {
			if ($productsArray["extrafields"][$f]["type"] != "TEXT" && $productsArray["extrafields"][$f]["type"] != "TEXTAREA" && $productsArray["extrafields"][$f]["type"] != "IMAGE") {
			if (@$productsArray["extrafields"][$f]["content"] != "" || @makeInteger($productsArray["extrafields"][$f]["requirement"]) != 0) {
?>
<tr>
	<td class="table-list-title" valign="top"><?php print $productsArray["extrafields"][$f]["title"]; ?></td>
<?php
				switch($productsArray["extrafields"][$f]["type"]) {
					case "USERINPUT":
?>
<td class="table-list-entry1" valign="top">
<?php
						$myForm->createText($productsArray["extrafields"][$f]["name"],50,250,"","general");
?>
</td>
<?php
						break;
					case "SELECT":
?>
						<td class="table-list-entry1" valign="top">
						<select name="<?php print $productsArray["extrafields"][$f]["name"]; ?>" class="form-inputbox" onChange="<?php print $productsArray["recalculateprice"]; ?>">
<?php
						for ($g = 0; $g < count($productsArray["extrafields"][$f]["options"]); $g++) {
?>
<option value="<?php print $productsArray["extrafields"][$f]["options"][$g]["id"]; ?>"><?php print $productsArray["extrafields"][$f]["options"][$g]["option"]; ?>
<?php
	if (@$productsArray["extrafields"][$f]["options"][$g]["price"] != "") {
?>
&nbsp;(<?php print $productsArray["extrafields"][$f]["options"][$g]["price"]; ?>)
<?php
	}
?>
</option>

<?php
						}
?>
						</select>
						</td>
<?php
						break;
					case "CHECKBOXES":
?>
						<td class="table-list-entry1" valign="top">
<?php
						for ($g = 0; $g < count($productsArray["extrafields"][$f]["options"]); $g++) {
?>
<input type="checkbox" name="<?php print $productsArray["extrafields"][$f]["name"]; ?><?php print $g+1; ?>" value="<?php print $productsArray["extrafields"][$f]["options"][$g]["id"]; ?>" onClick="<?php print $productsArray["recalculateprice"]; ?>">
<?php print $productsArray["extrafields"][$f]["options"][$g]["option"]; ?>
<?php
	if (@$productsArray["extrafields"][$f]["options"][$g]["price"] != "") {
?>
&nbsp;(<?php print $productsArray["extrafields"][$f]["options"][$g]["price"]; ?>)
<?php
	}
?>
<br>

<?php
						}
?>
						</td>
<?php
						break;
					case "RADIOBUTTONS":
?>
						<td class="table-list-entry1" valign="top">
<?php
						for ($g = 0; $g < count($productsArray["extrafields"][$f]["options"]); $g++) {
?>
<input type="radio" name="<?php print $productsArray["extrafields"][$f]["name"]; ?>" value="<?php print $productsArray["extrafields"][$f]["options"][$g]["id"]; ?>" onClick="<?php print $productsArray["recalculateprice"]; ?>" <?php if ($g == 0) 						{ echo "CHECKED"; } ?>>
<?php print $productsArray["extrafields"][$f]["options"][$g]["option"]; ?>
<?php
	if (@$productsArray["extrafields"][$f]["options"][$g]["price"] != "") {
?>
&nbsp;(<?php print $productsArray["extrafields"][$f]["options"][$g]["price"]; ?>)
<?php
	}
?>
<br>

<?php
						}
?>
						</td>
<?php
						break;
				}
?>
</tr>
<?php
			}
			}
		}
	}
?>
<tr>
	<td class="table-list-title" valign="top">Quantity</td>
	<td class="table-list-entry1" valign="top">
		<?php
			$myForm->createText($productsArray["qtyboxname"],5,10,"1","integer",0,$productsArray["recalculateprice"]);
		?>
	</td>
</tr>
<tr>
		<td class="table-list-entry0" colspan="2" align="right">
		<input type="submit" name="goButton" value="Add To Basket" class="button-save">
		</td>
	</tr>
</table>
<p>

<table cellpadding="2" cellspacing="0" class="table-list" width="100%">
	<tr>
		<td class="table-grey-nocenter" valign="top" colspan="2">Product Statistics</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Minimum Order Quantity</td>
		<td class="table-list-entry1" valign="top" align="right"><?php print $productsArray["minQty"]; ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Maximum Order Quantity</td>
		<td class="table-list-entry1" valign="top" align="right"><?php print $productsArray["maxQty"]; ?></td>
	</tr>
	<?php
		if ($productsArray["scEnabled"] == "Y") {
	?>
	<tr>
		<td class="table-list-title" valign="top">Main Stock Level</td>
		<td class="table-list-entry1" valign="top" align="right"><?php print $productsArray["scLevel"]; ?></td>
	</tr>
	<?php
		}
	?>
</table>
<Script><?php print $productsArray["recalculateprice"]; ?></script>
</center>
</form>
</td>
<td width="2%"></td>
<td width="49%" valign="top">

<?php
	if ($productsArray["pricing"]["combinationstable"]["available"] == "Y") {
?>
	<p>
	<table cellpadding="2" cellspacing="0" class="table-list" width="100%">
	<tr>
		<td class="table-grey-nocenter" valign="top" colspan="2">Base Price Combinations</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Combination</td>
		<td class="table-list-title" valign="top" align="right">Price</td>
	</tr>
<?php
	for ($f = 0; $f < count($productsArray["pricing"]["combinationstable"]["entries"]); $f++) {
?>
	<tr>
		<td class="table-list-entry1" valign="top">
<?php
		for ($g = 0; $g < count($productsArray["pricing"]["combinationstable"]["entries"][$f]["fields"]); $g++) {
?>
		<?php print $productsArray["pricing"]["combinationstable"]["entries"][$f]["fields"][$g]["field"]; ?>: <?php print $productsArray["pricing"]["combinationstable"]["entries"][$f]["fields"][$g]["value"]; ?><br>
<?php
		}
?>
<?php
	if ($productsArray["pricing"]["combinationstable"]["entries"][$f]["qtyfrom"] > 0 || $productsArray["pricing"]["combinationstable"]["entries"][$f]["qtyto"] > 0 ) {
?>
Quantity: <?php print $productsArray["pricing"]["combinationstable"]["entries"][$f]["qtyfrom"]; ?> - <?php print $productsArray["pricing"]["combinationstable"]["entries"][$f]["qtyto"]; ?>
<?php
	}
?>
		</td>
		<td class="table-list-entry1" valign="top" align="right"><?php print $productsArray["pricing"]["combinationstable"]["entries"][$f]["price"]; ?></td>
	</tr>
<?php
	}
?>
	</table>
<?php
	}
?>

<?php
	if ($productsArray["pricing"]["quantitytable"]["available"] == "Y") {
?>
	<p>
	<table cellpadding="2" cellspacing="0" class="table-list" width="100%">
	<tr>
		<td class="table-grey-nocenter" valign="top" colspan="2">Quantity Discounts</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Combination</td>
		<td class="table-list-title" valign="top" align="right">Discount</td>
	</tr>
<?php
	for ($f = 0; $f < count($productsArray["pricing"]["quantitytable"]["entries"]); $f++) {
?>
	<tr>
		<td class="table-list-entry1" valign="top">
<?php
	if ($productsArray["pricing"]["quantitytable"]["entries"][$f]["to"] != 99999) {
?>
Quantity: <?php print $productsArray["pricing"]["quantitytable"]["entries"][$f]["from"]; ?> - <?php print $productsArray["pricing"]["quantitytable"]["entries"][$f]["to"]; ?>
<?php
	} else {
?>
Quantity: <?php print $productsArray["pricing"]["quantitytable"]["entries"][$f]["from"]; ?> +
<?php
	}
?>
		</td>
		<td class="table-list-entry1" valign="top" align="right"><?php print $productsArray["pricing"]["quantitytable"]["entries"][$f]["discount"]; ?></td>
	</tr>
<?php
	}
?>
	</table>
<?php
	}
?>
	

	</td>
</tr>
</table>
</BODY>
</HTML>
<?php
	function findCorrectLanguage($theRecord,$theField) {
		global $dbA,$cartMain;
		if ($cartMain["languageID"] == 1) {
			return $theRecord[$theField];
		}
		if (array_key_exists($theField.$cartMain["languageID"],$theRecord)) {
			if (chop(@$theRecord[$theField.$cartMain["languageID"]]) == "") {
				return $theRecord[$theField];
			} else {
				return $theRecord[$theField.$cartMain["languageID"]];
			}
		} else {
			return $theRecord[$theField];
		}
	}

	function findCorrectLanguageExtraField($theRecord,$theField) {
		global $dbA,$cartMain;
		if ($cartMain["languageID"] == 1) {
			return $theRecord[$theField];
		}
		if (array_key_exists($theField."_".$cartMain["languageID"],$theRecord)) {
			if (chop(@$theRecord[$theField."_".$cartMain["languageID"]]) == "") {
				return $theRecord[$theField];
			} else {
				return $theRecord[$theField."_".$cartMain["languageID"]];
			}
		} else {
			return $theRecord[$theField];
		}
	}

	function formatWithoutCalcPrice($thePrice) {
		global $currArray,$cartID,$cartMain;
		$additionalSign = ($thePrice >= 0) ? "" : "-";
		return $additionalSign.$cartMain["currency"]["pretext"].number_format(abs($thePrice),$cartMain["currency"]["decimals"],$cartMain["currency"]["middletext"],"").$cartMain["currency"]["posttext"];
	}


	function formatPriceWithSpan($thePrice,$xProd,$spanName) {
		global $currArray,$cartID,$cartMain,$xBrowserLong,$xBrowserShort;
		$xBrowserShort = strtoupper($xBrowserShort);
		$additionalSign = ($thePrice >= 0) ? "" : "-";
		if ($xBrowserLong == "NS4") {
			return "<span id=\"".$spanName."Layer$xProd\" name=\"".$spanName."Layer$xProd\">".$additionalSign.$cartMain["currency"]["pretext"].number_format(abs($thePrice),$cartMain["currency"]["decimals"],$cartMain["currency"]["middletext"],"").$cartMain["currency"]["posttext"]."</span>";
		} else {
			if ($xBrowserShort == "FIREFOX" || $xBrowserShort == "SAFARI") {
				return "<LAYER id=\"".$spanName."Layer$xProd\" name=\"".$spanName."Layer$xProd\" style=\"position:relative;\"><span id=\"".$spanName."Span$xProd\">".$additionalSign.$cartMain["currency"]["pretext"].number_format(abs($thePrice),$cartMain["currency"]["decimals"],$cartMain["currency"]["middletext"],"").$cartMain["currency"]["posttext"]."</span></layer>";
			} else {
				return "<LAYER id=\"".$spanName."Layer$xProd\" name=\"".$spanName."Layer$xProd\" style=\"position:absolute;\"><span id=\"".$spanName."Span$xProd\">".$additionalSign.$cartMain["currency"]["pretext"].number_format(abs($thePrice),$cartMain["currency"]["decimals"],$cartMain["currency"]["middletext"],"").$cartMain["currency"]["posttext"]."</span></layer>";
			}
		}
	}	
?>