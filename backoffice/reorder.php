<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$xType = getFORM("xType");
	
	dbConnect($dbA);

	$xSectionID=getFORM("xSectionID");
	if ($xSectionID != "") {
		$thisSectionID = $xSectionID;
		$reachedRoot = false;
		$thisSectionPath = "";
		while ($reachedRoot == false) {
			$sResult = $dbA->query("select * from $tableSections where sectionID=$thisSectionID");
			$sRecord = $dbA->fetch($sResult);
			if (strlen($thisSectionPath) > 0) {
				$thisSectionPath = "<a href=\"sections_structure.php?xSectionID=".$sRecord["sectionID"]."&".userSessionGET()."\">".$sRecord["title"]."</a> <font class=\"boldtext\"><b>&gt;</b></font> ".$thisSectionPath;
			} else {
				$thisSectionPath = "<a href=\"sections_structure.php?xSectionID=".$sRecord["sectionID"]."&".userSessionGET()."\">".$sRecord["title"]."</a>";
			}
			$thisSectionID = $sRecord["parent"];
			if ($thisSectionID == 0) {
				$reachedRoot = true;
			}
		}
	}
	$xExtra = "";
	switch ($xType) {
		case "products":
			$xExtra = "products";
			$theQuery = "select * from $tableProducts,$tableProductsTree where $tableProducts.productID = $tableProductsTree.productID and $tableProducts.productID != -1 and sectionID=$xSectionID order by position,name";
			$theField = "name";
			$theProcessor = "products_product_process.php";
			$boxTitle = "Products";
			$theid = "productID";
			break;	
		case "newproducts":
			$xExtra = "newproducts";
			$theQuery = "select * from $tableProducts,$tableProductsOptions where $tableProducts.productID = $tableProductsOptions.productID and $tableProducts.productID != -1 and $tableProductsOptions.type='N' order by $tableProductsOptions.position,name";
			$theField = "name";
			$theProcessor = "products_product_process.php";
			$boxTitle = "Products";
			$theid = "productID";
			$thisSectionPath = "New Products";
			break;
		case "topproducts":
			$xExtra = "topproducts";
			$theQuery = "select * from $tableProducts,$tableProductsOptions where $tableProducts.productID = $tableProductsOptions.productID and $tableProducts.productID != -1 and $tableProductsOptions.type='T' order by $tableProductsOptions.position,name";
			$theField = "name";
			$theProcessor = "products_product_process.php";
			$boxTitle = "Products";
			$theid = "productID";
			$thisSectionPath = "Top Products";
			break;		
		case "specialoffers":
			$xExtra = "specialoffers";
			$theQuery = "select * from $tableProducts,$tableProductsOptions where $tableProducts.productID = $tableProductsOptions.productID and $tableProducts.productID != -1 and $tableProductsOptions.type='S' order by $tableProductsOptions.position,name";
			$theField = "name";
			$theProcessor = "products_product_process.php";
			$boxTitle = "Products";
			$theid = "productID";
			$thisSectionPath = "Special Offers";
			break;					
		case "sections":
			$theQuery = "select * from $tableSections where parent=$xSectionID order by position,title";
			$theField = "title";
			$theProcessor = "sections_process.php";
			$boxTitle = "Sub Sections";
			$theid = "sectionID";
			break;		
		case "extrafields":
			$theQuery = "select * from $tableExtraFields order by position,name";
			$theField = "title";
			$theProcessor = "general_extrafields_process.php";
			$boxTitle = "Extra Fields";
			$theid = "extraFieldID";
			$thisSectionPath = "Extra Fields";
			break;
		case "paymentoptions":
			$theQuery = "select * from $tablePaymentOptions order by position,paymentID";
			$theField = "name";
			$theProcessor = "payment_options_process.php";
			$boxTitle = "Payment Options";
			$theid = "paymentID";
			$thisSectionPath = "Payment Options";
			break;	
		case "countries":
			$theQuery = "select * from $tableCountries where visible='Y' order by position,name";
			$theField = "name";
			$theProcessor = "countries_process.php";
			$boxTitle = "Available Countries";
			$theid = "countryID";
			$thisSectionPath = "Available Countries";
			break;						
		case "fields";
			$xFType = getFORM("xFType");
			$theQuery = "select * from $tableCustomerFields where type='$xFType' order by position,titleText";
			$theField = "titleText";
			$theProcessor = "customers_fields_process.php";
			switch ($xFType) {
				case "C":
					$boxTitle = "Customer Fields";
					break;
				case "D":
					$boxTitle = "Address/Delivery Fields";
					break;
				case "O":
					$boxTitle = "Extra Order Fields";
					break;	
				case "F":
					$boxTitle = "Contact Form Fields";
					break;		
				case "CC":
					$boxTitle = "Contact Form Fields";
					break;		
				case "G":
					$boxTitle = "Gift Certificate Fields";
					break;		
				case "AF":
					$boxTitle = "Affiliate Account Fields";
					break;	
				case "SU":
					$boxTitle = "Supplier Fields";
					break;										
			}
			$theid = "fieldID";
			$thisSectionPath = $boxTitle;
			$xExtra = $xFType;
			break;
		case "shippingtypes":
			$theQuery = "select * from $tableShippingTypes order by position,name";
			$theField = "name";
			$theProcessor = "shipping_types_process.php";
			$boxTitle = "Shipping Types";
			$theid = "shippingID";
			$thisSectionPath = "Shipping Types";
			break;	
		case "news":
			$theQuery = "select * from $tableNews order by position,datetime DESC";
			$theField = "title";
			$theProcessor = "general_news_process.php";
			$boxTitle = "News Items";
			$theid = "newsID";
			$thisSectionPath = "News Items";
			break;					
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
	function mover(theBox,direction) {
		currentSelected = theBox.selectedIndex;
		totalItems = theBox.length;
		if (currentSelected == -1) {
			rc=alert("Please select an item to move first.");
			return false;
		}
		if (direction=="up" && currentSelected == 0) { return false; }
		if (direction=="down" && currentSelected == totalItems-1) { return false; }
		if (direction=="up") {
			changeWith = currentSelected - 1;
		}
		if (direction=="down") {
			changeWith = currentSelected + 1;
		}
		optionValue = theBox.options[changeWith].value;
		optionText = theBox.options[changeWith].text;
		theBox.options[changeWith].value = theBox.options[currentSelected].value;
		theBox.options[changeWith].text = theBox.options[currentSelected].text;
		theBox.options[currentSelected].value = optionValue;
		theBox.options[currentSelected].text = optionText;
		theBox.selectedIndex = changeWith;
	}
	
	function moveTop(theBox) {
		currentSelected = theBox.selectedIndex;
		for (f = currentSelected; f > 0; f++) {
			theResponse = mover(theBox,"up");
			if (theResponse == false) { break; }
		}
	}
	
	function moveBottom(theBox) {
		currentSelected = theBox.selectedIndex;
		for (f = currentSelected; f < theBox.length; f++) {
			theResponse = mover(theBox,"down");
			if (theResponse == false) { break; }
		}
	}
	
	function checkFields() {
		theBox = document.detailsForm.xReordering;
		newOrder = "";
		for (f = 0; f < theBox.length; f++) {
			newOrder = newOrder+theBox.options[f].value+";";
		}
		document.detailsForm.xNewOrder.value = newOrder;
		return true;
	}

	function sortOptions(theBox) {
		totalItems = theBox.length;
		for (i = totalItems - 1; i > 0; i--) {
			for (j = 0; j < i; j++) {
				if (theBox.options[j].text > theBox.options[j+1].text) {
					optionValue = theBox.options[j].value;
					optionText = theBox.options[j].text;
					theBox.options[j].value = theBox.options[j+1].value;
					theBox.options[j].text = theBox.options[j+1].text;
					theBox.options[j+1].value = optionValue;
					theBox.options[j+1].text = optionText;
				}
			}
		}
	}
  	
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Sort / Reorder: <?php print $thisSectionPath; ?></td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list">
<?php $myForm->createForm("detailsForm",$theProcessor,"",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xSectionID" value="<?php print $xSectionID; ?>">
<input type="hidden" name="xNewOrder" value="">
<input type="hidden" name="xAction" value="reorder">
<input type="hidden" name="xExtra" value="<?php print $xExtra; ?>">
	<tr>
		<td class="table-list-title" colspan="2"><font class="boldtext"><?php print $boxTitle; ?></font>
		</td>
	</tr>
	<tr>
		<td class="table-list-entry1">
			<select name="xReordering" size="20" class="form-inputbox">
<?php
	$ssResult = $dbA->query($theQuery);
	$ssCount = $dbA->count($ssResult);
	for ($f = 0; $f < $ssCount; $f++) {
		$ssRecord = $dbA->fetch($ssResult);
?>
	<option value="<?php print $ssRecord[$theid]; ?>"><?php print $ssRecord[$theField]; ?></option>
<?php
	}
	$dbA->close();
?>
			</select>
		</td>
		<td class="table-list-entry1">
			<center>
			<input type="button" name="buttonMoveTop" class="button-reorder" onClick="moveTop(document.detailsForm.xReordering);" value="Move Top">
			<p>
			<input type="button" name="buttonMoveUp" class="button-reorder" onClick="mover(document.detailsForm.xReordering,'up');" value="Move Up">
			<p>
			<input type="button" name="buttonMoveDown" class="button-reorder" onClick="mover(document.detailsForm.xReordering,'down');" value="Move Down">
			<p>
			<input type="button" name="buttonMoveBottom" class="button-reorder" onClick="moveBottom(document.detailsForm.xReordering);" value="Move Bottom">
			<p>
			<input type="button" name="buttonSort" class="button-reorder" onClick="sortOptions(document.detailsForm.xReordering)" value="Sort ABC">
		</td>
	</tr>
	<tr>
		<td colspan="2" class="table-list-title" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit","Update Order"); ?></td>
	</tr>
	<?php $myForm->closeForm("xReordering"); ?>	
</table>
</center>
</BODY>
</HTML>

