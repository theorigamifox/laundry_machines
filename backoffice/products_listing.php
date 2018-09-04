<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
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
	if ($xType=="ABC") {
		$pageTitle = "ABC Products Listing";
		$searchAppend = "&xType=ABC".$xDirectional;
		$theQuery = "select * from $tableProducts where productID != 1 $sortBit";
	}
	if ($xType=="INVISIBLE") {
		$pageTitle = "Invisible Products Listing";
		$searchAppend = "&xType=INVISIBLE".$xDirectional;
		$theQuery = "select * from $tableProducts where productID != 1 and visible=\"N\" $sortBit";
	}
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
		$searchAppend = "&xType=SEARCH&xSearchString=$xSearchString&xCategoryID=$xCategoryID".$xDirectional;
		$theQuery = "select * from $tableProducts where productID != 1 and $catAppend (code like \"%$xSearchString%\" or name like \"%$xSearchString%\") $sortBit";
	}
	if ($xType=="EXTRA") {
		$xSearchString = getFORM("xSearchString");
		$pageTitle = "Products Search: &quot;$xSearchString&quot;";
		$searchAppend = "&xType=SEARCH&xSearchString=$xSearchString".$xDirectional;
		$theQuery = "select * from $tableProducts where productID != 1 and (code like \"%$xSearchString%\" or name like \"%$xSearchString%\") $sortBit";
	}	
	$ordersperpage = retrieveOption("adminProdPerPage");
	$result=$dbA->query($theQuery);
	$resultCount = $dbA->count($result);
	$theQuery .= " LIMIT $xOffset,$ordersperpage";
	$upperCount = $xOffset+$ordersperpage;
	$lowerCount = $xOffset+1;
	$middleButtons = "Viewing <b>$lowerCount - ".$upperCount."</b>&nbsp;";
	if ($xOffset-$ordersperpage > -1) {
		$pOffset = $xOffset - $ordersperpage;
		$previousButton = "<input type=\"button\" id=\"buttonPrev\" class=\"button-expand\" onClick=\"self.location.href='products_listing.php?".userSessionGET().$searchAppend.$xSortPart."&xOffset=$pOffset'\" value=\"&lt; PREV\">";
		$previousButton .= "&nbsp;<input type=\"button\" id=\"buttonTop\" class=\"button-expand\" onClick=\"self.location.href='products_listing.php?".userSessionGET().$searchAppend.$xSortPart."&xOffset=0'\" value=\"[TOP]\">";
	} else {
		$previousButton = "";
	}
	if ($xOffset+$ordersperpage < $resultCount) {
		$nOffset = $xOffset + $ordersperpage;
		$nextButton = "<input type=\"button\" id=\"buttonNext\" class=\"button-expand\" onClick=\"self.location.href='products_listing.php?".userSessionGET().$searchAppend.$xSortPart."&xOffset=$nOffset'\" value=\"NEXT &gt;\">";
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
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript">
	function goDelete(productID) {
		if (confirm("Are you sure you wish to delete this product?")) {
			self.location.href="products_product_process.php?xAction=delete&xProductID="+productID+"&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>";
		}
	}

	function productsSelectAll() {
		if (document.selectProducts.selectAll.checked == true) {
			setStatus = true;
		} else {
			setStatus = false;
		}
		for (f = 0; f < document.selectProducts.productCount.value; f++) {
			document.selectProducts.elements["select"+f].checked = setStatus;
		}
		selectRefresh();
	}
	
	function selectRefresh() {
		selectList = "";
		for (f = 0; f < document.selectProducts.productCount.value; f++) {
			if (document.selectProducts.elements["select"+f].checked == true) {
				selectList = selectList + document.selectProducts.elements["select"+f].value+";";
			}
		}
		document.selectProducts.selectedProducts.value = selectList;
	}	
	
	function goSelectAll() {
		selectList = document.selectProducts.selectedProducts.value;
		if (selectList == "") {
			rc=alert("You need to select at least one product first");
		} else {
			self.location.href="products_product_process.php?<?php print userSessionGET(); ?>&<?php print $xDirectional; ?>&xProductList="+selectList+"&xAction=multistructure";
		}
	}	
</script>
</HEAD>
<BODY class="detail-body">
<form name="selectProducts">
<input type="hidden" name="selectedProducts" value="">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title"><?php print $pageTitle; ?></td>
	</tr>
</table>
<p>
<!--<table cellpadding="2" cellspacing="0" class="table-outline">
	<form name="searchForm" action="products_listing.php" method="POST">
	<?php print hiddenFromPOST(); ?>
	<?php userSessionPOST(); ?>
	<input type="hidden" name="xType" value="EXTRA">
	<tr>
		<td class="table-list-title" align="right" valign="top">Search:</td>
		<td class="table-list-entry1" valign="top"><font class="normaltext"><?php $myForm->createText("xSearchString",20,100,@$xSearchString,"general"); ?></td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSubmit("submit","Search"); ?></td>
	</tr>
	</form>
</table>-->
<p>
<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
<?php
	if ($xSelect == "Y") {
?>
		<td colspan="3" class="table-white-no-border" align="left">Checked products action: <input type="button" name="buttonAllSelect" class="button-select" onClick="goSelectAll();" value="Select"></td>
<?php
	} else {
?>
		<td colspan="2" class="table-white-no-border" align="left">&nbsp;</td>
<?php
	}
?>
		<td colspan="2" class="table-white-no-border" align="right">Total selected products: <b><?php print $resultCount; ?></b>, <?php print $navButtons; ?>&nbsp;</td>
	</tr>
	<tr>
<?php
	if ($xSelect == "Y") {
?>
		<td class="table-list-title"><input type="checkbox" name="selectAll" onClick="productsSelectAll();"></td>
<?php
	}
?>
		<td class="table-list-title">
			<?php
				if (($xSort == "c" && $xSortDir == "DESC") || ($xSort != "c")) {
			?>
					<a href="products_listing.php?<?php print userSessionGET().$searchAppend; ?>&xOffset=<?php print $xOffset; ?>&xSort=c&xSortDir=ASC">Code</a>
			<?php } else { ?>
					<a href="products_listing.php?<?php print userSessionGET().$searchAppend; ?>&xOffset=<?php print $xOffset; ?>&xSort=c&xSortDir=DESC">Code</a>
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
					<a href="products_listing.php?<?php print userSessionGET().$searchAppend; ?>&xOffset=<?php print $xOffset; ?>&xSort=n&xSortDir=ASC">Name</a>
			<?php } else { ?>
					<a href="products_listing.php?<?php print userSessionGET().$searchAppend; ?>&xOffset=<?php print $xOffset; ?>&xSort=n&xSortDir=DESC">Name</a>
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
		<td class="table-list-title"><center>Visible</center></td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	$ssResult = $dbA->query($theQuery);
	$ssCount = $dbA->count($ssResult);
	for ($f = 0; $f < $ssCount; $f++) {
		$ssRecord = $dbA->fetch($ssResult);
?>
	<tr>
<?php
	if ($xSelect == "Y") {
?>
		<td class="table-list-entry1"><input type="checkbox" name="select<?php print $f; ?>" value="<?php print $ssRecord["productID"]; ?>" onClick="selectRefresh();"></td>
<?php
	}
?>
		<td class="table-list-entry1"><a href="products_product_detail.php?xType=edit&xProductID=<?php print $ssRecord["productID"]; ?>&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>"><?php print $ssRecord["code"]; ?></a></td>
		<td class="table-list-entry1"><a href="products_product_detail.php?xType=edit&xProductID=<?php print $ssRecord["productID"]; ?>&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>"><?php print $ssRecord["name"]; ?></a></td>
		<td class="table-list-entry1"><center><?php print $ssRecord["visible"]; ?></center></td>
		<td class="table-list-entry1" align="right">
			<input type="button" name="buttonPEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='products_product_detail.php?xType=edit&xProductID=<?php print $ssRecord["productID"]; ?>&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>'" value="Edit">&nbsp;
			<input type="button" name="buttonPClone<?php print $f; ?>" class="button-clone" onClick="self.location.href='products_product_detail.php?xType=clone&xProductID=<?php print $ssRecord["productID"]; ?>&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>'" value="Clone">&nbsp;
			<input type="button" name="buttonPDelete<?php print $f; ?>" class="button-delete" onClick="javascript:goDelete(<?php print $ssRecord["productID"]; ?>);" value="Delete"><?php
	if ($xSelect == "Y") {
?>
&nbsp;<input type="button" name="buttonPSelect<?php print $f; ?>" class="button-select" onClick="self.location.href='products_product_process.php?<?php print userSessionGET(); ?>&<?php print $xDirectional; ?>&xProductID=<?php print $ssRecord["productID"]; ?>&xAction=structure'" value="Select">
			<!--<a href="products_product_process.php?<?php print userSessionGET(); ?>&<?php print $xDirectional; ?>&xProductID=<?php print $ssRecord["productID"]; ?>&xAction=structure"><img src="images/select.gif" width="36" height="15" border="0" alt="[select]"></a>-->
<?php
	}
?>
		</td>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
<?php
	if ($xSelect == "Y") {
?>
		<td colspan="4" class="table-list-title">Total Number of Products:</td>
<?php
	} else {
?>
		<td colspan="3" class="table-list-title">Total Number of Products:</td>
<?php
	}
?>
		<td class="table-list-title" align="right"><?php print $ssCount; ?></td>
	</tr>
</table>
</center>
<input type="hidden" name="productCount" value="<?php print $ssCount; ?>">
</form>
</BODY>
</HTML>
