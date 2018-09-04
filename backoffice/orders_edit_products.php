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
	$xType = getFORM("xType");
	$xOffset = getFORM("xOffset");
	if ($xOffset=="") { $xOffset = 0; }	
	$xDirectional = "";
	$xSelect = getFORM("xSelect");
	$xSectionID = getFORM("xSectionID");
	$xSort = getFORM("xSort");
	$xSortDir = getFORM("xSortDir");
	if ($xSortDir == "") {
		$xSortDir = "ASC";
	}
	switch ($xSort) {
		case "":
			$xSort="c";
		case "c":
			$sortBit = "order by code $xSortDir";
			break;
		case "n":
			$sortBit = "order by name $xSortDir";
			break;
	}
	$xDirectional = "&xSelect=$xSelect&xSectionID=$xSectionID";
	$xSortPart = "&xSort=$xSort&xSortDir=$xSortDir";
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

	$xType = "SEARCH";
	if ($xType=="SEARCH") {
		$xSearchString = getFORM("xSearchString");
		$xCategoryID = makeInteger(getFORM("xCategoryID"));
		$pageTitle = "Products Search: &quot;$xSearchString&quot;";
		if ($xCategoryID > 0) {
			$uResult = $dbA->query("select * from $tableProductsCategories order by name");
			$uCount = $dbA->count($uResult);
			for ($f = 0; $f < $uCount; $f++) {
				$uRecord = $dbA->fetch($uResult);
				if ($uRecord["categoryID"] == $xCategoryID) {
					$pageTitle .= " and Category '".$uRecord["name"]."'";
				}
			}
			$catAppend = " categories=$xCategoryID and ";
		} else {
			$catAppend = "";
		}
		$searchAppend = "&cartID=$cartID&xOrderID=$orderID&xType=SEARCH&xSearchString=$xSearchString&xCategoryID=$xCategoryID".$xDirectional;

		if (retrieveOption("stockWarningNotZero") == 0) {
			$scBit = "((scActionZero = 1 and scEnabled='Y' and scLevel > 0) or (scEnabled = 'N') or (scEnabled = 'Y' and scActionZero != 1))";
		} else {
			$scBit = "((scActionZero = 1 and scEnabled='Y' and scLevel > scWarningLevel) or (scEnabled = 'N') or (scEnabled = 'Y' and scActionZero != 1))";
		}
		if (retrieveOption("featureStockControl") == 1) {
			$stockControlClause = "$scBit and ($tableProducts.productID > 1) and (accTypes like '%;".$cartMain["accTypeID"].";%' or accTypes like '%;0;%') and (visible = 'Y') and ";
		} else {
			$stockControlClause = "($tableProducts.productID > 1) and (accTypes like '%;".$cartMain["accTypeID"].";%' or accTypes like '%;0;%') and (visible = 'Y') and ";
		}
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

		$theQuery = "select $tableProducts.* $advancedPricingSelect from $tableProducts $advancedPricingJoin where $stockControlClause visible = \"Y\" and (code like \"%$xSearchString%\" or name like \"%$xSearchString%\") group by $tableProducts.productID $sortBit";

		//$theQuery = "select * from $tableProducts where productID != 1 and $catAppend (code like \"%$xSearchString%\" or name like \"%$xSearchString%\") $sortBit";
	}
	if ($xType=="EXTRA") {
		$xSearchString = getFORM("xSearchString");
		$pageTitle = "Products Search: &quot;$xSearchString&quot;";
		$searchAppend = "&xType=SEARCH&xSearchString=$xSearchString".$xDirectional;
		$theQuery = "select * from $tableProducts where productID != 1 and (code like \"%$xSearchString%\" or name like \"%$xSearchString%\") $sortBit";
	}	
	$ordersperpage = 15;
	$result=$dbA->query($theQuery);
	$resultCount = $dbA->count($result);
	$theQuery .= " LIMIT $xOffset,$ordersperpage";
	$upperCount = $xOffset+$ordersperpage;
	$lowerCount = $xOffset+1;
	$middleButtons = "Viewing <b>$lowerCount - ".$upperCount."</b>&nbsp;";
	if ($xOffset-$ordersperpage > -1) {
		$pOffset = $xOffset - $ordersperpage;
		$previousButton = "<input type=\"button\" id=\"buttonPrev\" class=\"button-expand\" onClick=\"self.location.href='orders_edit_products.php?".userSessionGET().$searchAppend.$xSortPart."&xOffset=$pOffset'\" value=\"&lt; PREV\">";
		$previousButton .= "&nbsp;<input type=\"button\" id=\"buttonTop\" class=\"button-expand\" onClick=\"self.location.href='orders_edit_products.php?".userSessionGET().$searchAppend.$xSortPart."&xOffset=0'\" value=\"[TOP]\">";
	} else {
		$previousButton = "";
	}
	if ($xOffset+$ordersperpage < $resultCount) {
		$nOffset = $xOffset + $ordersperpage;
		$nextButton = "<input type=\"button\" id=\"buttonNext\" class=\"button-expand\" onClick=\"self.location.href='orders_edit_products.php?".userSessionGET().$searchAppend.$xSortPart."&xOffset=$nOffset'\" value=\"NEXT &gt;\">";
	} else {
		$nextButton = "";
	}
	$searchAppend .= "&xOffset=$xOffset";
	if ($previousButton=="" && $nextButton=="") {
		$navButtons = $middleButtons;
	}
	if ($previousButton=="" && $nextButton!="") {
		$navButtons = $middleButtons.$nextButton;
	}
	if ($previousButton!="" && $nextButton=="") {
		$navButtons = $middleButtons.$previousButton;
	}
	if ($previousButton!="" && $nextButton!="") {
		$navButtons = $middleButtons.$previousButton."&nbsp;".$nextButton;
	}	

	$myForm = new formElements;
?>
<HTML>
<HEAD>
<TITLE>Order Editing: Product Search</TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript">
	function productAdd(prod,qty) {
		opener.productAdd(prod,qty);
	}

	function productSelect(prod) {
		opener.productSelect(prod);
	}
</script>
</HEAD>
<BODY class="detail-body">
<input type="hidden" name="selectedProducts" value="">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title"><?php print $pageTitle; ?></td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-outline">
	<form name="searchForm" action="orders_edit_products.php" method="POST">
	<?php print hiddenFromPOST(); ?>
	<?php userSessionPOST(); ?>
<input type="hidden" name="cartID" value="<?php print $cartID; ?>">
<input type="hidden" name="xOrderID" value="<?php print $orderID; ?>">
<input type="hidden" name="xProd" value="<?php print $xProd; ?>">
	<input type="hidden" name="xType" value="SEARCH">
	<tr>
		<td class="table-list-title" align="right" valign="top">Search:</td>
		<td class="table-list-entry1" valign="top"><font class="normaltext"><?php $myForm->createText("xSearchString",20,100,@$xSearchString,"general"); ?></td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSubmit("submit","Search"); ?></td>
	</tr>
	</form>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
	<td colspan="4" class="table-white-no-border" align="right">Total selected products: <b><?php print $resultCount; ?></b>, <?php print $navButtons; ?>&nbsp;</td>
	</tr>
	<tr>
		<td class="table-list-title">
			<?php
				if (($xSort == "c" && $xSortDir == "DESC") || ($xSort != "c")) {
			?>
					<a href="orders_edit_products.php?<?php print userSessionGET().$searchAppend; ?>&xOffset=<?php print $xOffset; ?>&xSort=c&xSortDir=ASC">Code</a>
			<?php } else { ?>
					<a href="orders_edit_products.php?<?php print userSessionGET().$searchAppend; ?>&xOffset=<?php print $xOffset; ?>&xSort=c&xSortDir=DESC">Code</a>
			<?php
				}
			?>
			<?php
				if ($xSort == "c") {
					if ($xSortDir == "ASC") {
						?><img src="images/sortup.gif" width="9" height="9" border="0"><?php
					} else {
						?><img src="images/sortdown.gif" width="9" height="9" border="0"><?php
					}
				}
			?>
		</td>
		<td class="table-list-title">
			<?php
				if (($xSort == "n" && $xSortDir == "DESC") || ($xSort != "n")) {
			?>
					<a href="orders_edit_products.php?<?php print userSessionGET().$searchAppend; ?>&xOffset=<?php print $xOffset; ?>&xSort=n&xSortDir=ASC">Name</a>
			<?php } else { ?>
					<a href="orders_edit_products.php?<?php print userSessionGET().$searchAppend; ?>&xOffset=<?php print $xOffset; ?>&xSort=n&xSortDir=DESC">Name</a>
			<?php
				}
			?>	
			<?php
				if ($xSort == "n") {
					if ($xSortDir == "ASC") {
						?><img src="images/sortup.gif" width="9" height="9" border="0"><?php
					} else {
						?><img src="images/sortdown.gif" width="9" height="9" border="0"><?php
					}
				}
			?>	
		</td>
		<td class="table-list-title" align="right">Price</td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	$pArray = retrieveProducts($theQuery,$uCount,"m",0,TRUE);
	//print_r ($pArray);
	$ssResult = $dbA->query($theQuery);
	$ssCount = $dbA->count($ssResult);
	for ($f = 0; $f < $uCount; $f++) {
?>
	<tr>
<?php
	if ($xSelect == "Y") {
?>
		<td class="table-list-entry1"><input type="checkbox" name="select<?php print $f; ?>" value="<?php print $pArray[$f]["productID"]; ?>" onClick="selectRefresh();"></td>
<?php
	}
?>
		<td class="table-list-entry1"><a href="javascript:productSelect(<?php print $pArray[$f]["productID"]; ?>);"><?php print $pArray[$f]["code"]; ?></a></td>
		<td class="table-list-entry1"><a href="javascript:productSelect(<?php print $pArray[$f]["productID"]; ?>);"><?php print $pArray[$f]["name"]; ?></a></td>
		<td class="table-list-entry1" align="right"><?php print $pArray[$f]["priceextax"]; ?> ex. tax 
		
		<?php
			if (retrieveOption("taxEnabled") == 1) {
		?>(<?php print $pArray[$f]["priceinctax"]; ?> inc. tax)</center></td><?php
			}
		?>
		<td class="table-list-entry1" align="right">
			<?php
				$allowAdd = TRUE;
				if (is_array($extraFieldsArray)) {
					for ($g = 0; $g < count($extraFieldsArray); $g++) {
						if ($extraFieldsArray[$g]["type"] == "USERINPUT") {
							if ($pArray[$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]] == 2) {
								$allowAdd = FALSE;
							}
						}
					}
				}
				if ($allowAdd) {
					$res = $dbA->query("select * from $tableExtraFieldsValues where productID=".$pArray[$f]["productID"]);
					if ($dbA->count($res) > 0) {
						$allowAdd = FALSE;
					}
				}
				if ($allowAdd) {
			?>
				<input type="button" name="basketAdd<?php print $f; ?>" class="button-edit" value="Add To Basket" onClick="productAdd(<?php print $pArray[$f]["productID"]; ?>,1);">
			<?php
				} else {
			?>
				<input type="button" name="basketAdd<?php print $f; ?>" class="button-edit" value="Select Options" onClick="productSelect(<?php print $pArray[$f]["productID"]; ?>);">
			<?php } ?>
		</td>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="3" class="table-list-title">Total Number of Products:</td>
		<td class="table-list-title" align="right"><?php print $uCount; ?></td>
	</tr>
</table>
</center>
</form>
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
?>