<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$xType=getFORM("xType");
	$xSectionID=getFORM("xSectionID");
	$xFrom=getFORM("xFrom");
	dbConnect($dbA);
	$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"position,name");
	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	$catArray = $dbA->retrieveAllRecords($tableProductsCategories,"categoryID");
	$accTypes = $dbA->retrieveAllRecords($tableCustomersAccTypes,"accTypeID");
	$languages = $dbA->retrieveAllRecords($tableLanguages,"languageID");
	$flagArray = $dbA->retrieveAllRecords($tableProductsFlags,"flagID");
	$supplierArray = $dbA->retrieveAllRecords($tableSuppliers,"sup_company");
	
	$taxRates[] = array("name"=>"Zero Rate","value"=>0);
	$taxRates[] = array("name"=>"Standard Rate","value"=>1);
	$taxRates[] = array("name"=>"Second Rate","value"=>2);

	$scModeArray[] = array("text"=>"Leave Available (with purchase)","value"=>0);
	$scModeArray[] = array("text"=>"Leave Available (no purchase)","value"=>2);
	$scModeArray[] = array("text"=>"Hide","value"=>1);
	
	$digitalRegArray = array (
		array("text"=>"None","value"=>"0"),
		array("text"=>"Registration Code","value"=>"1"),
		array("text"=>"Registration Code and Name","value"=>"2")
		);
	
	$userInputArray = array (
		array("text"=>"Exclude","value"=>"0"),
		array("text"=>"Include","value"=>"1"),
		array("text"=>"Required","value"=>"2")
		);
		
	if ($xType=="new") {
		$divsHide = split(";",retrieveOption("prodDivsAdd"));
		$pageTitle = "Add New Product";
		$submitButton = "Insert Product";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>".hiddenReturnPOST();
		$sResult = $dbA->query("select * from $tableProducts where productID=1");	
		$sRecord = $dbA->fetch($sResult);
		$xProductID = 1;
	}
	if ($xType=="edit") {
		$divsHide = split(";",retrieveOption("prodDivsEdit"));
		$xProductID = getFORM("xProductID");
		$pageTitle = "Edit Existing Product (ProductID = $xProductID)";
		$submitButton = "Update Product";
		if ($xProductID == 1) {
			$pageTitle = "Edit Template Product";
			$submitButton = "Update Template Product";
		}
		$sResult = $dbA->query("select * from $tableProducts where productID=$xProductID");	
		$sRecord = $dbA->fetch($sResult);
		$xOrigCode = $sRecord["code"];
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xProductID' value='$xProductID'><input type='hidden' name='xOrigCode' value='$xOrigCode'>".hiddenReturnPOST();
	}
	if ($xType=="clone") {
		$divsHide = split(";",retrieveOption("prodDivsClone"));
		$xProductID = getFORM("xProductID");
		$pageTitle = "Clone Existing Product";
		$submitButton = "Add New Product";
		$sResult = $dbA->query("select * from $tableProducts where productID=$xProductID");	
		$sRecord = $dbA->fetch($sResult);
		$xOrigCode = $sRecord["code"];
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'><input type='hidden' name='xOrigCode' value='$xOrigCode'>".hiddenReturnPOST();
	}

	$noDouble = TRUE;
	for ($f = 0; $f < count($languages); $f++) {
		if ($languages[$f]["doubleByte"] == "Y") {
			$noDouble = FALSE;
		}
	}
	
	if ($noDouble) {
		if (is_array($sRecord)) {
			foreach ($sRecord as $key => $value) {
				$sRecord[$key] = htmlspecialchars($value);
			}
		}
	}
		
	$isVisible = $sRecord["visible"];
	if ($isVisible == "N") {
		$xIsVisibleYes = "";
		$xIsVisibleNo = "CHECKED";
	} else {
		$xIsVisibleYes = "CHECKED";
		$xIsVisibleNo = "";
	}		
	$cResult = $dbA->query("select * from $tableProductsTree where productID=$xProductID");
	$cCount = $dbA->count($cResult);
	$sectionList = "";
	if (makeInteger(getFORM("xSectionID")) > 0 && $xType != "edit") {
		$sectionList[] = makeInteger(getFORM("xSectionID"));
	} else {
		for ($f = 0; $f < $cCount; $f++) {
			$cRecord = $dbA->fetch($cResult);
			$sectionList[] = $cRecord["sectionID"];
		}
	}

	//let's grab all the advanced pricing shite here
	$xAdvancedPricingSend = "";
	$advArray = $dbA->retrieveAllRecordsFromQuery("select * from $tableAdvancedPricing where productID=$xProductID order by priceType,accTypeID");		
	$combArray = $dbA->retrieveAllRecordsFromQuery("select * from $tableCombinations where productID=$xProductID order by type");
	$languages = $dbA->retrieveAllRecords($tableLanguages,"languageID");	
	$myForm = new formElements;
	$recalculator = "";
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/product_detail.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script>
	<?php $myForm->outputCurrencyArray($currArray); ?>
	<?php $myForm->outputExtraFieldsArray($extraFieldsArray); ?>
	<?php $myForm->outputLanguagesArray($languages); ?>
	<?php $recalculator .= $myForm->createCurrencyRecalculate($currArray,"price","xPrice"); ?>
	<?php $recalculator .= $myForm->createCurrencyRecalculate($currArray,"rrp","xRRP"); ?>
	<?php $recalculator .= $myForm->createCurrencyRecalculate($currArray,"ooPrice","xOOPrice"); ?>
	<?php $recalculator .= $myForm->createCurrencyRecalculate($currArray,"apprice","xAPprice"); ?>

	function checkFields() {
		return true;
	}
	
	backTimes = -1;
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title"><?php print $pageTitle; ?></td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","products_product_process.php","","multipart"); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<input type="hidden" name="xSectionID" value="<?php print $xSectionID; ?>">
	<a name="divGeneralDetailsAnchor"></a>
	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-title" valign="top" colspan="2">General Details <A href="javascript:toggleDiv('divGeneralDetails');"><span id="divGeneralDetailsToggle">hide</span></a></td>
	</tr>
	</table>
<div id="divGeneralDetails">
<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-title" valign="top" width="105">Code (SKU)</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCode",40,40,@getGENERIC("code",$sRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xName",80,250,@getGENERIC("name",$sRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Short Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xShortDescription",80,250,@getGENERIC("shortdescription",$sRecord),"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Full Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createTextArea("xDescription",60,7,@getGENERIC("description",$sRecord),""); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Search Keywords</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xKeywords",80,250,@getGENERIC("keywords",$sRecord),"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">META Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMetaDescription",80,250,@getGENERIC("metaDescription",$sRecord),"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">META Keywords</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMetaKeywords",80,250,@getGENERIC("metaKeywords",$sRecord),"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Thumbnail</td>
		<td class="table-list-entry1" valign="top">
			<?php $myForm->createImageEntry("xThumbnail",@$sRecord["thumbnail"],$jssShopImagesFileSystem."products/thumbnails","opener.jssDetails.document.detailsForm.xThumbnailPick.value"); ?>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Image</td>
		<td class="table-list-entry1" valign="top">
			<?php $myForm->createImageEntry("xImage",@$sRecord["mainimage"],$jssShopImagesFileSystem."products/normal","opener.jssDetails.document.detailsForm.xImagePick.value"); ?>	
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Price</td>
		<td class="table-list-entry1" valign="top">
			<?php $myForm->createPricingFields($currArray,@$sRecord,"price","xPrice"); ?>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Normal Price</td>
		<td class="table-list-entry1" valign="top">
			<?php $myForm->createPricingFields($currArray,@$sRecord,"rrp","xRRP"); ?>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">One-Off Price</td>
		<td class="table-list-entry1" valign="top">
			<?php $myForm->createPricingFields($currArray,@$sRecord,"ooPrice","xOOPrice"); ?> (This price will be added to the total but will not be linked to quantity ordered)
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Orderable Quantity</td>
		<td class="table-list-entry1" valign="top">Minimum: <?php $myForm->createText("xMinQty",8,10,@getGENERIC("minQty",$sRecord),"integer"); ?>&nbsp;&nbsp;Maximum: <?php $myForm->createText("xMaxQty",8,10,@getGENERIC("maxQty",$sRecord),"integer"); ?> (Enter 0 for either to disable the limit)</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Weight</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xWeight",10,10,@getGENERIC("weight",$sRecord),"decimal"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top" width="105">Tax Rate</td>
		<td class="table-list-entry1" valign="top">
			<select name="xTaxRate" class="form-inputbox">
<?php		
			for ($f = 0; $f < count($taxRates); $f++) {
				$thisSelected = "";
				if ($taxRates[$f]["value"] == @$sRecord["taxrate"]) {
					$thisSelected = " SELECTED";
				}
?>
			<option value="<?php print $taxRates[$f]["value"]; ?>" <?php print $thisSelected; ?>><?php print $taxRates[$f]["name"]; ?></option>
<?php
			}
?>
			</select>
		</td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Free Shipping</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xFreeShipping",@getGENERIC("freeShipping",$sRecord),"YN"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Exclude Account Type Discounts</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xIgnoreDiscounts",@getGENERIC("ignoreDiscounts",$sRecord),"YN"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top" width="105">Supplier</td>
		<td class="table-list-entry1" valign="top">
			<select name="xSupplierID" class="form-inputbox">
<?php		
			if ($sRecord["supplierID"] == 0) {
?>
			<option value="0" SELECTED>None</option>
<?php } else { ?>
			<option value="0">None></option>
<?php
			}
			if (is_array($supplierArray)) {
				for ($f = 0; $f < count($supplierArray); $f++) {
					$thisSelected = "";
					if ($supplierArray[$f]["supplierID"] == @$sRecord["supplierID"]) {
						$thisSelected = " SELECTED";
					}
?>
			<option value="<?php print $supplierArray[$f]["supplierID"]; ?>" <?php print $thisSelected; ?>><?php print $supplierArray[$f]["sup_company"]; ?></option>
<?php
				}
			}
?>
			</select>&nbsp;&nbsp;&nbsp;Supplier's Product Code (if applicable): <?php $myForm->createText("xSuppliercode",30,40,@getGENERIC("suppliercode",$sRecord),"general"); ?>
	</tr>
		</td>
	</tr>
	</table>
</div>
	<a name="divExtraFieldsAnchor"></a>
	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-title" valign="top" colspan="2">Extra Fields <A href="javascript:toggleDiv('divExtraFields');"><span id="divExtraFieldsToggle">hide</span></a></td>
	</tr>
	</table>
	<div id="divExtraFields">
	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
<?php
		if (is_array($extraFieldsArray)) {
		for ($f = 0; $f < count($extraFieldsArray); $f++) {
?>
	<tr>
		<td class="table-list-title" valign="top" width="105"><?php print $extraFieldsArray[$f]["title"]; ?></td>
		<td class="table-list-entry1" valign="top">
<?php
			switch ($extraFieldsArray[$f]["type"]) {
				case "USERINPUT":
					$myForm->createSelect("xExtra".$extraFieldsArray[$f]["extraFieldID"],@getGENERIC("extrafield".$extraFieldsArray[$f]["extraFieldID"],$sRecord),"BOTH",$userInputArray);
					break;
				case "TEXT":
					$myForm->createText("xExtra".$extraFieldsArray[$f]["extraFieldID"],80,250,@getGENERIC("extrafield".$extraFieldsArray[$f]["extraFieldID"],$sRecord),"");		
					break;
				case "TEXTAREA":
					$myForm->createTextArea("xExtra".$extraFieldsArray[$f]["extraFieldID"],60,5,@getGENERIC("extrafield".$extraFieldsArray[$f]["extraFieldID"],$sRecord),"");
					break;
				case "SELECT":
				case "RADIOBUTTONS":
				case "CHECKBOXES":
?>
<table cellpadding="0" cellspacing="0" border="0">
<tr>
	<td valign="top">
		<table cellpadding="1" cellspacing="0" border="0">
			<tr>
				<td><font class="normaltext">New:</td>
				<td>
					<?php
						if ($xBrowserShort == "Firefox") { 
							$myForm->createText("xExtraAdd".$extraFieldsArray[$f]["extraFieldID"],20,200,"","captureExtraReturnFirefox;",0,"",$extraFieldsArray[$f]["extraFieldID"]);
						} else {
							$myForm->createText("xExtraAdd".$extraFieldsArray[$f]["extraFieldID"],20,200,"","captureExtraReturn(this,event,".$extraFieldsArray[$f]["extraFieldID"].");");
						}
					?>
				</td>
			</tr>
		
<?php
	for ($g = 0; $g < count($languages); $g++) {
		$thisLanguage = $languages[$g]["languageID"];
		if ($thisLanguage != 1) {
?> 
			<tr>
				<td><font class="normaltext"><?php print $languages[$g]["name"]; ?>:</td>
				<td>
					<?php
						if ($xBrowserShort == "Firefox") { 
							$myForm->createText("xExtraAdd".$extraFieldsArray[$f]["extraFieldID"]."_".$thisLanguage,20,200,"","captureExtraReturnFirefox;",0,"",$extraFieldsArray[$f]["extraFieldID"]);
						} else {
							$myForm->createText("xExtraAdd".$extraFieldsArray[$f]["extraFieldID"]."_".$thisLanguage,20,200,"","captureExtraReturn(this,event,".$extraFieldsArray[$f]["extraFieldID"].");");
						}
					?>
				</td>
			</tr>
<?php
		}
	}
?>		</table>
		<font class="normaltext"><Br><input type="checkbox" name="xExtraVisible<?php print $extraFieldsArray[$f]["extraFieldID"]; ?>" value="Y" CHECKED>Option Visible&nbsp;<br>Account Types: <select name="xExtraCustomer<?php print $extraFieldsArray[$f]["extraFieldID"]; ?>" class="form-inputbox"><option value="0">All</option>
						<?php
							for ($g = 0; $g < count($accTypes); $g++) {
						?>
							<option value="<?php print $accTypes[$g]["accTypeID"]; ?>"><?php print $accTypes[$g]["name"]; ?></option>
						<?php
							}
						?>
						</select>
	</td>
	<td>&nbsp;&nbsp;&nbsp;</td>
	<td valign="top">
		<center>
		<a href="javascript:extraMoveItem(<?php print $extraFieldsArray[$f]["extraFieldID"]; ?>,'up');"><img src="images/select_up.gif" border="0" width="15" height="15"><br>
		<img src="images/spacer.gif" border="0" width="15" height="3"><br>
		<a href="javascript:extraMoveItem(<?php print $extraFieldsArray[$f]["extraFieldID"]; ?>,'down');"><img src="images/select_down.gif" border="0" width="15" height="15"><br>
		<img src="images/spacer.gif" border="0" width="15" height="3"><br>
		<a href="javascript:extraDeleteItem(<?php print $extraFieldsArray[$f]["extraFieldID"]; ?>);"><img src="images/select_delete.gif" border="0" width="15" height="15">
		</center>
	</td>
	<td>&nbsp;</td>
	<td valign="top">
		<select name="xExtraSelect<?php print $extraFieldsArray[$f]["extraFieldID"]; ?>" size="5" class="form-inputbox" style="width: 250px" onChange="extraItemShow(<?php print $extraFieldsArray[$f]["extraFieldID"]; ?>);">
<?php
		$thisField = $extraFieldsArray[$f]["extraFieldID"];
		$efResult = $dbA->query("select $tableExtraFieldsValues.*, $tableExtraFieldsPrices.*, $tableExtraFieldsValues.exvalID from $tableExtraFieldsValues LEFT JOIN $tableExtraFieldsPrices on $tableExtraFieldsValues.exvalID = $tableExtraFieldsPrices.exvalID where productID=$xProductID and extraFieldID=$thisField order by position");
		$efCount = $dbA->count($efResult);
		$fullList = "";
		for ($g = 0; $g < $efCount; $g++) {
			$efRecord = $dbA->fetch($efResult);
			$efRecord["content"] = htmlspecialchars($efRecord["content"]);
			for ($h = 0; $h < count($currArray); $h++) {
				$efRecord["price".$currArray[$h]["currencyID"]] = calculatePrice4($efRecord["price1"],$efRecord["price".$currArray[$h]["currencyID"]],$currArray[$h]["currencyID"]);
			}
			$fieldVals[$thisField][] = $efRecord;
			$efFull = implodeQueryString($efRecord);
			$fullList = ($fullList != "") ? $fullList."|".$efFull : $fullList.$efFull;
			$customerDisplay = "All";
			for ($h = 0; $h < count($accTypes); $h++) {
				if ($efRecord["accTypeID"] == $accTypes[$h]["accTypeID"]) {
					$customerDisplay = $accTypes[$h]["name"];
				}
			}
			if ($efRecord["percent"] != 0) {
				$priceDisplay = " (".$efRecord["percent"]."%)";
			} else {
				if ($efRecord["price1"] != 0) {
					$priceDisplay = "";
					for ($h = 0; $h < count($currArray) ; $h++) {
						$thisPrice = calculatePriceFormat4($efRecord["price1"],$efRecord["price".$currArray[$h]["currencyID"]],$currArray[$h]["currencyID"]);
						if ($priceDisplay != "") {
							$priceDisplay .= " ";
						}
						$priceDisplay .= $thisPrice;
					}
					$priceDisplay = " (".$priceDisplay.")";
				} else {
					$priceDisplay = "";
				}
			}
			$optionColor = ($efRecord["visible"] == "Y") ? "#000000" : "#777777";
?>		
		<option value="<?php print $efFull; ?>" style="color :<?php print $optionColor; ?>;"><?php print $efRecord["content"]; ?> (<?php print $customerDisplay; ?>)<?php print $priceDisplay; ?></option>		
<?php
		}
?>
		</select>
		<input type="hidden" name="xExtra<?php print $extraFieldsArray[$f]["extraFieldID"]; ?>Send" value="<?php print $fullList; ?>">
		<input type="hidden" name="xExtra<?php print $extraFieldsArray[$f]["extraFieldID"]; ?>Delete" value="">
		<input type="hidden" name="xExtraID" value="">
	</td>
</tr>
<tr>
	<td colspan="5"><font class="normaltext">
			<p>Percent: <?php $myForm->createText("xExtraPercent".$extraFieldsArray[$f]["extraFieldID"],4,4,"0","integer"); ?>%&nbsp;<b>or</b><?php $myForm->createPricingFields($currArray,array(""),"extraprice".$extraFieldsArray[$f]["extraFieldID"],"xExtra".$extraFieldsArray[$f]["extraFieldID"]."Price",0); ?><br>
						<script><?php $recalculator .= $myForm->createCurrencyRecalculate($currArray,"extraprice".$extraFieldsArray[$f]["extraFieldID"],"xExtra".$extraFieldsArray[$f]["extraFieldID"]."Price"); ?></script>
		<input type="button" onClick="extraAddItem(<?php print $extraFieldsArray[$f]["extraFieldID"]; ?>,0);" name="xExtraAddButton<?php print $extraFieldsArray[$f]["extraFieldID"]; ?>" class="button-grey" value="Add">
		&nbsp;<input type="button"  onClick="extraClearItem(<?php print $extraFieldsArray[$f]["extraFieldID"]; ?>);" name="xExtraClearButton<?php print $extraFieldsArray[$f]["extraFieldID"]; ?>" class="button-grey" value="Clear">
		&nbsp;<input type="button" onClick="extraAddItem(<?php print $extraFieldsArray[$f]["extraFieldID"]; ?>,1);" id="xExtraApplyButton<?php print $extraFieldsArray[$f]["extraFieldID"]; ?>" class="button-grey" style="visibility:hidden;" value="Apply">
	</td>
</tr>
</table>
<?php
					break;		
				case "IMAGE":
					$myForm->createImageEntry("xExtra".$extraFieldsArray[$f]["extraFieldID"],@$sRecord["extrafield".$extraFieldsArray[$f]["extraFieldID"]],$jssShopImagesFileSystem."products/extras","opener.jssDetails.document.detailsForm.xExtra".$extraFieldsArray[$f]["extraFieldID"]."Pick.value");
					break;
			}
?>
		</td>
	</tr>
<?php
		}	
	}
?>

	</table>
</div>
<?php
	if (retrieveOption("downloadsActivate") == 1) {
?>
	<a name="divDigitalAnchor"></a>
	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-title" valign="top" colspan="2">Digital Download Options <A href="javascript:toggleDiv('divDigital');"><span id="divDigitalToggle">hide</span></a></td>
	</tr>
	</table>
<div id="divDigital">
	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-title" valign="top" width="105">Is Digital Product?</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xIsDigital",@getGENERIC("isDigital",$sRecord),"YN"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Digital File</td>
		<td class="table-list-entry1" valign="top">
			<?php $myForm->createDigitalEntry("xDigitalFile",@$sRecord["digitalFile"],retrieveOption("downloadsDirectory"),"opener.jssDetails.document.detailsForm.xDigitalFilePick.value"); ?>
		</td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top" width="105">Registration Details</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xDigitalReg",@getGeneric("digitalReg",$sRecord),"BOTH",$digitalRegArray); ?>
		</td>
	</tr>
	</table>
</div>
<?php
	}
?>
	<a name="divGroupAnchor"></a>
	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-title" valign="top" colspan="2"><a name="group"></a>Group Product Settings <A href="javascript:toggleDiv('divGroup');"><span id="divGroupToggle">hide</span></a></td>
	</tr>
	</table>
<div id="divGroup">
	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-title" valign="top" width="105">Group Product?</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xGroupedProduct",@getGENERIC("groupedProduct",$sRecord),"YN"); ?></td>
	</tr>	
	</table>
	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-entry1" valign="top" colspan="2">
			<table cellpadding="2" cellspacing="0" border="0">
				<tr>
<script>
						function searchGroup() {
							document.getElementById("groupSearch").src = "products_searchgroup.php?xSearchString="+document.detailsForm.xGroupSearchString.value+"&<?php print userSessionGET(); ?>";
							document.getElementById("groupSearch").style.display = "none";
							document.getElementById("groupSearch").style.display = "inline";
						}
</script>
					<td valign="top">
						<?php 
							if ($xBrowserShort == "Firefox") {
								$myForm->createText("xGroupSearchString",30,100,"","captureSearchGroupFirefox;");
							} else {
								$myForm->createText("xGroupSearchString",30,100,"","captureSearchGroup(this,event);");
							}
						?>
						&nbsp;<input type="button" name="buttonGroupSearch" class="button-grey" onClick="searchGroup();"  value="Search">
						<br>
						<iframe id="groupSearch" frameborder="0" STYLE="border:solid black 1px; width: 300px; height: 100px" src="blank.html"></iframe>
						<br><font class="normaltext">Qty: <?php $myForm->createText("xGroupQty",5,5,"1","captureSearchGroup(this,event);"); ?>
					</td>
					<td valign="top">
						<font class="boldtext">Currently Grouped:</font><br>
						<select name="xGroupProducts" size="8" class="form-inputbox" style="width: 220px;">
<?php
						$aResult = $dbA->query("select $tableProducts.productID,code,name,$tableProductsGrouped.qty from $tableProducts,$tableProductsGrouped where $tableProducts.productID=$tableProductsGrouped.groupedID and $tableProductsGrouped.productID=$xProductID order by $tableProductsGrouped.position");
						$aCount = $dbA->count($aResult);
						for ($f = 0; $f < $aCount; $f++) {
							$aRecord = $dbA->fetch($aResult);
							if ($aRecord["code"] != "") {
								$newText = $aRecord["code"]." : ".$aRecord["name"];
							} else {
								$newText = $aRecord["name"];
							}
							$newText .= " (".$aRecord["qty"].")";
?>
							<option value="<?php print $aRecord["productID"]; ?>:<?php print $aRecord["qty"]; ?>"><?php print $newText; ?></option>
<?php
						}
?>						
						</select>
						<input type="hidden" name="xGroupSend" value=""><input type="hidden" name="xGroupDelete" value="">
					</td>
					<td>
						<a href="javascript:groupMoveItem('up');"><img src="images/select_up.gif" border="0" width="15" height="15"><br>
						<img src="images/spacer.gif" border="0" width="15" height="3"><br>
						<a href="javascript:groupMoveItem('down');"><img src="images/select_down.gif" border="0" width="15" height="15"><br>
						<img src="images/spacer.gif" border="0" width="15" height="3"><br>
						<a href="javascript:groupDeleteItem();"><img src="images/select_delete.gif" border="0" width="15" height="15">
					</td>
				</tr>
			</table>
		</td>
	</tr>	
	</table>
</div>		



	<a name="divAdvancedPricingAnchor"></a>
	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-title" valign="top" colspan="2">Advanced Pricing / Combinations <A href="javascript:toggleDiv('divAdvancedPricing');"><span id="divAdvancedPricingToggle">hide</span></a></td>
	</tr>
	</table>	
<div id="divAdvancedPricing">
	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-entry1" valign="top">
			<div id="divComboBoxes">
				<font class="normaltext"><input type="radio" name="radio1" value="QtyDisc" CHECKED onClick="showPane('xQtyDiscSelect');"> <b>Qty Discounts</b>&nbsp;&nbsp;<input type="radio" name="radio1" value="PriceComb" onClick="showPane('xPriceCombSelect');"> <b>Base Pricing Combinations</b>&nbsp;&nbsp;<input type="radio" name="radio1" value="AttComb" onClick="showPane('xAttCombSelect');"> <b>Attribute Combinations</b>
				<br><input type="radio" name="radio1" value="OneOffPrice" onClick="showPane('xOneOffSelect');"> <b>One-Off Prices</b>
				<table cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td>
						<select name="xQtyDiscSelect" id="xQtyDiscSelect" size="5" class="form-inputbox" style="width:530px; visibility:visible; display:inline;" onChange="apShow();">
							<?php
								$fullList = "";
								for ($f = 0; $f < count($advArray); $f++) {
									if ($advArray[$f]["priceType"] == 2) {
										$thisString = "";
										if ($advArray[$f]["accTypeID"] == 0) {
											$customerType = "All";
										} else {
											for ($g = 0; $g < count($accTypes); $g++) {
												if ($advArray[$f]["accTypeID"] == $accTypes[$g]["accTypeID"]) {
													$customerType = $accTypes[$g]["name"];
												}
											}
										}
										$thisString = "Customer: $customerType, Qty: ".$advArray[$f]["qtyfrom"]."-".$advArray[$f]["qtyto"].", ";
										if ($advArray[$f]["percentage"] != 0) {
											$thisString .= "Percent: ".$advArray[$f]["percentage"]."%";
										} else {
											for ($g = 0; $g < count($currArray); $g++) {
												$thisPrice = calculatePriceFormat4($advArray[$f]["price1"],$advArray[$f]["price".$currArray[$g]["currencyID"]],$currArray[$g]["currencyID"]);
												$thisString .= $thisPrice." ";
											}
										}
										for ($h = 0; $h < count($currArray); $h++) {
											$advArray[$f]["price".$currArray[$h]["currencyID"]] = calculatePrice4($advArray[$f]["price1"],$advArray[$f]["price".$currArray[$h]["currencyID"]],$currArray[$h]["currencyID"]);
										}
										$thisValue = implodeQueryString($advArray[$f]);
										$fullList = ($fullList != "") ? $fullList."|".$thisValue : $fullList.$thisValue;
										?>
										<option value="<?php print $thisValue; ?>"><?php print $thisString; ?></option>
										<?php
									}
								}
							?>
						</select>
						<input type="hidden" name="xQtyDiscSend" id="xQtyDiscSend" value="<?php print $fullList; ?>"><input type="hidden" name="xQtyDiscDelete"  id="xQtyDiscDelete" value="">
						<select name="xPriceCombSelect" id="xPriceCombSelect" size="5" class="form-inputbox" style="width:530px; visibility:hidden; display:none;" onChange="apShow();">
							<?php
								$fullList = "";
								for ($f = 0; $f < count($advArray); $f++) {
									if ($advArray[$f]["priceType"] == 0 && $advArray[$f]["price1"] != "") {
										$thisString = "";
										if ($advArray[$f]["accTypeID"] == 0) {
											$customerType = "All";
										} else {
											for ($g = 0; $g < count($accTypes); $g++) {
												if ($advArray[$f]["accTypeID"] == $accTypes[$g]["accTypeID"]) {
													$customerType = $accTypes[$g]["name"];
												}
											}
										}
										$extraFieldsBits = "";
										for ($g = 0; $g < count($extraFieldsArray); $g++) {
											switch ($extraFieldsArray[$g]["type"]) {
												case "SELECT":
												case "CHECKBOXES":
												case "RADIOBUTTONS":
													if ($advArray[$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]] == 0) {
														$extraFieldsBits .= $extraFieldsArray[$g]["name"].": All, ";
														$advArray[$f]["textrafield".$extraFieldsArray[$g]["extraFieldID"]] = "All";
													} else {
														for ($h = 0; $h < count($fieldVals[$extraFieldsArray[$g]["extraFieldID"]]); $h++) {
															if ($fieldVals[$extraFieldsArray[$g]["extraFieldID"]][$h]["exvalID"] == $advArray[$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]]) {
																$extraFieldsBits .= $extraFieldsArray[$g]["name"].": ".$fieldVals[$extraFieldsArray[$g]["extraFieldID"]][$h]["content"].", ";
																$advArray[$f]["textrafield".$extraFieldsArray[$g]["extraFieldID"]] = $fieldVals[$extraFieldsArray[$g]["extraFieldID"]][$h]["content"];
															}
														}
													}
											}
										}
										$thisString = "Customer: $customerType, $extraFieldsBits, Qty: ".$advArray[$f]["qtyfrom"]."-".$advArray[$f]["qtyto"].", ";
										if ($advArray[$f]["percentage"] != 0) {
											$thisString .= "Percent: ".$advArray[$f]["percentage"]."%";
										} else {
											for ($g = 0; $g < count($currArray); $g++) {
												$thisPrice = calculatePriceFormat4($advArray[$f]["price1"],$advArray[$f]["price".$currArray[$g]["currencyID"]],$currArray[$g]["currencyID"]);
												$thisString .= $thisPrice." ";
											}
										}
										for ($h = 0; $h < count($currArray); $h++) {
											$advArray[$f]["price".$currArray[$h]["currencyID"]] = calculatePrice4($advArray[$f]["price1"],$advArray[$f]["price".$currArray[$h]["currencyID"]],$currArray[$h]["currencyID"]);
										}										
										$thisValue = implodeQueryString($advArray[$f]);
										$fullList = ($fullList != "") ? $fullList."|".$thisValue : $fullList.$thisValue;
										?>
										<option value="<?php print $thisValue; ?>"><?php print $thisString; ?></option>
										<?php
									}
								}
							?>
						</select>
						<input type="hidden" name="xPriceCombSend" id="xPriceCombSend" value="<?php print $fullList; ?>"><input type="hidden" name="xPriceCombDelete" id="xPriceCombDelete" value="">
						<select name="xAttCombSelect" id="xAttCombSelect" size="5" class="form-inputbox" style="width:530px; visibility:hidden; display:none;" onChange="apShow();">
							<?php
								$fullList = "";
								for ($f = 0; $f < count($combArray); $f++) {
									$thisString = "";
									$extraFieldsBits = "";
									for ($g = 0; $g < count($extraFieldsArray); $g++) {
										switch ($extraFieldsArray[$g]["type"]) {
											case "SELECT":
											case "CHECKBOXES":
											case "RADIOBUTTONS":
												if ($combArray[$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]] == 0) {
													$extraFieldsBits .= $extraFieldsArray[$g]["name"].": All, ";
													$combArray[$f]["textrafield".$extraFieldsArray[$g]["extraFieldID"]] = "All";
												} else {
													for ($h = 0; $h < count($fieldVals[$extraFieldsArray[$g]["extraFieldID"]]); $h++) {
														if ($fieldVals[$extraFieldsArray[$g]["extraFieldID"]][$h]["exvalID"] == $combArray[$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]]) {
															$extraFieldsBits .= $extraFieldsArray[$g]["name"].": ".$fieldVals[$extraFieldsArray[$g]["extraFieldID"]][$h]["content"].", ";
															$combArray[$f]["textrafield".$extraFieldsArray[$g]["extraFieldID"]] = $fieldVals[$extraFieldsArray[$g]["extraFieldID"]][$h]["content"];
														}
													}
												}
										}
									}
									if ($combArray[$f]["type"] == "W") {
										$thisString = "Weight=".$combArray[$f]["content"].", $extraFieldsBits";
									}
									if ($combArray[$f]["type"] == "C") {
										$thisString = "Product Code=".$combArray[$f]["content"].", $extraFieldsBits";
									}
									if ($combArray[$f]["type"] == "E") {
										$thisString = "Exclude, $extraFieldsBits";
									}
									if ($combArray[$f]["type"] == "q") {
										$thisString = "Min Qty=".$combArray[$f]["content"].", $extraFieldsBits";
									}
									if ($combArray[$f]["type"] == "Q") {
										$thisString = "Max Qty=".$combArray[$f]["content"].", $extraFieldsBits";
									}
									if ($combArray[$f]["type"] == "S") {
										if ($combArray[$f]["exclude"] == "Y") {
											$excludeBit = " (X)";
										} else {
											$excludeBit = "";
										}
										$thisString = "Stock=".$combArray[$f]["content"].$excludeBit.", $extraFieldsBits";
									}
									if ($combArray[$f]["type"] == "U") {
										$thisString = "Supplier Code=".$combArray[$f]["content"].", $extraFieldsBits";
									}
									$thisValue = implodeQueryString($combArray[$f]);
									$fullList = ($fullList != "") ? $fullList."|".$thisValue : $fullList.$thisValue;
									?>
									<option value="<?php print $thisValue; ?>"><?php print $thisString; ?></option>
									<?php
								}
							?>
						</select>
						<input type="hidden" name="xAttCombSend" id="xAttCombSend" value="<?php print $fullList; ?>"><input type="hidden" name="xAttCombDelete" id="xAttCombDelete" value="">

						<select name="xOneOffSelect" id="xOneOffSelect" size="5" class="form-inputbox" style="width:530px; visibility:hidden; display:none;" onChange="apShow();">
							<?php
								$fullList = "";
								for ($f = 0; $f < count($advArray); $f++) {
									if ($advArray[$f]["priceType"] == 4 && $advArray[$f]["price1"] != "") {
										$thisString = "";
										if ($advArray[$f]["accTypeID"] == 0) {
											$customerType = "All";
										} else {
											for ($g = 0; $g < count($accTypes); $g++) {
												if ($advArray[$f]["accTypeID"] == $accTypes[$g]["accTypeID"]) {
													$customerType = $accTypes[$g]["name"];
												}
											}
										}
										$extraFieldsBits = "";
										for ($g = 0; $g < count($extraFieldsArray); $g++) {
											switch ($extraFieldsArray[$g]["type"]) {
												case "SELECT":
												case "CHECKBOXES":
												case "RADIOBUTTONS":
													if ($advArray[$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]] == 0) {
														$extraFieldsBits .= $extraFieldsArray[$g]["name"].": All, ";
														$advArray[$f]["textrafield".$extraFieldsArray[$g]["extraFieldID"]] = "All";
													} else {
														for ($h = 0; $h < count($fieldVals[$extraFieldsArray[$g]["extraFieldID"]]); $h++) {
															if ($fieldVals[$extraFieldsArray[$g]["extraFieldID"]][$h]["exvalID"] == $advArray[$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]]) {
																$extraFieldsBits .= $extraFieldsArray[$g]["name"].": ".$fieldVals[$extraFieldsArray[$g]["extraFieldID"]][$h]["content"].", ";
																$advArray[$f]["textrafield".$extraFieldsArray[$g]["extraFieldID"]] = $fieldVals[$extraFieldsArray[$g]["extraFieldID"]][$h]["content"];
															}
														}
													}
											}
										}
										$thisString = "Customer: $customerType, $extraFieldsBits";
										if ($advArray[$f]["percentage"] != 0) {
											$thisString .= "Percent: ".$advArray[$f]["percentage"]."%";
										} else {
											for ($g = 0; $g < count($currArray); $g++) {
												$thisPrice = calculatePriceFormat4($advArray[$f]["price1"],$advArray[$f]["price".$currArray[$g]["currencyID"]],$currArray[$g]["currencyID"]);
												$thisString .= $thisPrice." ";
											}
										}
										for ($h = 0; $h < count($currArray); $h++) {
											$advArray[$f]["price".$currArray[$h]["currencyID"]] = calculatePrice4($advArray[$f]["price1"],$advArray[$f]["price".$currArray[$h]["currencyID"]],$currArray[$h]["currencyID"]);
										}										
										$thisValue = implodeQueryString($advArray[$f]);
										$fullList = ($fullList != "") ? $fullList."|".$thisValue : $fullList.$thisValue;
										?>
										<option value="<?php print $thisValue; ?>"><?php print $thisString; ?></option>
										<?php
									}
								}
							?>
						</select>
						<input type="hidden" name="xOneOffSend" id="xOneOffSend" value="<?php print $fullList; ?>"><input type="hidden" name="xOneOffDelete" id="xOneOffDelete" value="">


					</td>
					<td valign="top">
					&nbsp;<a href="javascript:apDelete();"><img src="images/select_delete.gif" border="0" width="15" height="15">
					</td>
				</tr>
				</table>
				</div>
				<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td colspan="2"><img src="images/pix.gif" width="100" height="5" border="0"></td>
				</tr>
				<tr>
					<td valign="top" colspan="2">
						<table cellpadding="2" cellspacing="0" border="0">
							<tr>
								<td><font class="normaltext">
									<div id="xAPcustomer">
									Customer: <select name="xAPcustomerbox" id="xAPcustomerbox" class="form-inputbox"><option value="0">All</option>
									<?php
										for ($f = 0; $f < count($accTypes); $f++) {
									?>
										<option value="<?php print $accTypes[$f]["accTypeID"]; ?>"><?php print $accTypes[$f]["name"]; ?></option>
									<?php
										}
									?>
									</select>
									</div>
								</td>
								<td><font class="normaltext">
									<div id="xAPqty">
									From: <?php $myForm->createText("xAPfrom",7,10,"0","integer"); ?>&nbsp;&nbsp;To: <?php $myForm->createText("xAPto",7,10,"0","integer"); ?>
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<div id="xAPprices">
									<table cellpadding="2" cellspacing="0" border="0">
										<tr>
											<td><font class="normaltext">
												Percent: <?php $myForm->createText("xAPpercent",4,4,"0","integer"); ?>% <b>OR</b>
											</td>
											<td valign="top"><font class="normaltext">
												<?php $myForm->createPricingFields($currArray,array(""),"apprice","xAPprice",0); ?>
											</td>
										</tr>
									</table>
									</div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td valign="top" colspan="2">
						<div id="xAPextrafields" name="xAPextrafields" style="visibility: hidden;">
						<table cellpadding="0" cellspacing="0" border="0">

<?php
		$thisCol = 1;
		if (is_array($extraFieldsArray)) {
			for ($f = 0; $f < count($extraFieldsArray); $f++) {
				switch ($extraFieldsArray[$f]["type"]) {
					case "SELECT":
					case "RADIOBUTTONS":
					case "CHECKBOXES":
						if ($thisCol == 1) {
							echo "<tr>";
						}				
	?>
						<td><font class="normaltext"><?php print $extraFieldsArray[$f]["name"]; ?>:</font><br>
						<select name="xAPef<?php print $extraFieldsArray[$f]["extraFieldID"]; ?>" id="xAPef<?php print $extraFieldsArray[$f]["extraFieldID"]; ?>" class="form-inputbox" style="width:150px;"><option value="0">Any</option>
	<?php
						for ($g = 0; $g < count($fieldVals[$extraFieldsArray[$f]["extraFieldID"]]); $g++) {	
	?>
						<option value="<?php print $fieldVals[$extraFieldsArray[$f]["extraFieldID"]][$g]["exvalID"]; ?>"><?php print $fieldVals[$extraFieldsArray[$f]["extraFieldID"]][$g]["content"]; ?></option>					
	<?php
						}				
	?>
						</select></td>
	<?php						
						if ($thisCol == 3) {
							echo "</tr>";
							$thisCol = 1;
						} else {
							$thisCol ++;
						}
						break;
				}
			}
		}
		if ($thisCol != 1) { echo "</tr>"; }
?>			
						</table>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2"><img src="images/pix.gif" width="100" height="5" border="0"></td>
				</tr>				
				<tr>
					<td valign="top" colspan="2"><font class="normaltext">
						<div id="xAPattributes" style="visibility: hidden;">
						Attribute: <select name="xAPattribute" id="xAPattribute" class="form-inputbox" onChange="attributeStockDiv();"><option value="W">Weight</option><option value="C">Product Code</option><option value="S">Stock</option><option value="q">Min Qty</option><option value="Q">Max Qty</option><option value="E">Exclude</option><option value="U">Supplier Code</option></select>
						&nbsp;&nbsp;&nbsp;Value: <?php $myForm->createText("xAPcontent",10,30,"","general"); ?><span id="xAPStockExclude" style="visibility:hidden;">&nbsp;<input type="checkbox" name="xAPStockExcludeZero"> Exclude option on Zero stock</span>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2"><img src="images/pix.gif" width="100" height="5" border="0"></td>
				</tr>				
				<tr>
					<td colspan="2">
						<input type="button" onClick="apAdd(0);" name="xAPAddButton" class="button-grey" value="Add">&nbsp;<input type="button" onClick="apClear();" name="xAPClearButton" class="button-grey" value="Clear">&nbsp;<input type="button" onClick="apAdd(1);" id="xAPApplyButton" class="button-grey" style="visibility: hidden; " value="Apply">
					</td>
				</tr>
			</table>
		</td>
	<tr>
		<td class="table-list-entry1" valign="top">Quantities for the product with different options should be combined for any quantity discounts above: <?php $myForm->createYesNo("xCombineQty",@getGENERIC("combineQty",$sRecord),"YN"); ?></td>
	</tr>		
	</tr>
	</table>
</div>
	<a name="divStockControlAnchor"></a>
	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-title" valign="top" colspan="2">Stock Control <A href="javascript:toggleDiv('divStockControl');"><span id="divStockControlToggle">hide</span></a></td>
	</tr>
	</table>	
<div id="divStockControl">
	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-title" valign="top" width="105">Enabled?</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xScEnabled",@getGENERIC("scEnabled",$sRecord),"YN"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Stock Level</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xScLevel",10,8,@getGENERIC("scLevel",$sRecord),"integer"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Warning Level</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xScWarningLevel",10,8,@getGENERIC("scWarningLevel",$sRecord),"integer"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top" width="105">Action on Zero?</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xScActionZero",@getGeneric("scActionZero",$sRecord),"BOTH",$scModeArray); ?>
		</td>
	</tr>	
	</table>
</div>
	<a name="divProductOptionsAnchor"></a>
	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-title" valign="top" colspan="2">Product Options <A href="javascript:toggleDiv('divProductOptions');"><span id="divProductOptionsToggle">hide</span></a></td>
	</tr>
	</table>	
<div id="divProductOptions">
	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-title" valign="top" width="105">Product<br>Category</td>
		<td class="table-list-entry1" valign="top">
			<select name="xCategories" class="form-inputbox">
<?php		
			for ($f = 0; $f < count($catArray); $f++) {
				$thisSelected = "";
				if ($catArray[$f]["categoryID"] == @$sRecord["categories"]) {
					$thisSelected = " SELECTED";
				}
?>
			<option value="<?php print $catArray[$f]["categoryID"]; ?>" <?php print $thisSelected; ?>><?php print $catArray[$f]["name"]; ?></option>
<?php
			}
?>
			</select>
		</td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Show In<br>Section</td>
		<td class="table-list-entry1" valign="top">
		<?php 
			getSectionsList(0,$sectionArray);
		?>
			<select name="xSections" class="form-inputbox">
		<?php
			for ($f = 0; $f < count($sectionArray); $f++) {
		?>
				<option value="<?php print $sectionArray[$f][0]; ?>"><?php print $sectionArray[$f][1]; ?></option>
		<?php
			}
		?>
			</select>&nbsp;<input type="button" name="addsection" class="button-expand" onClick="addSection();" value="Add">
			<br><select name="xSelectedSections" size="5" class="form-inputbox">
		<?php
			
			if (is_array($sectionList)) {
				for ($f = 0; $f < count($sectionArray); $f++) {
					for ($g = 0; $g < count($sectionList); $g++) {
						if ($sectionArray[$f][0] == $sectionList[$g]) {
		?>
				<option value="<?php print $sectionArray[$f][0]; ?>"><?php print $sectionArray[$f][1]; ?></option>
		<?php
						}
					}
				}
			}
		?>			
			</select>&nbsp;<input type="button" name="removesection" class="button-expand" onClick="removeSection();" value="Remove"><input type="hidden" name="xSectionsSend" value=""><input type="hidden" name="xSectionsDelete" value="">
		</td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">New Product</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xNewProduct",@getGENERIC("newproduct",$sRecord),"YN"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Top Product</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xTopProduct",@getGENERIC("topproduct",$sRecord),"YN"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">On Special Offer</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xSpecialoffer",@getGENERIC("specialoffer",$sRecord),"YN"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Is Product Visible?</td>
		<td class="table-list-entry1" valign="top"><input type="radio" name="xIsVisible" value="N" <?php print $xIsVisibleNo; ?>> NO&nbsp;&nbsp;&nbsp;<input type="radio" name="xIsVisible" value="Y" <?php print $xIsVisibleYes; ?>> YES
		&nbsp;&nbsp;<b>If invisible still allow product to be shown with direct link:</b> <?php $myForm->createYesNo("xAllowDirect",@getGENERIC("allowDirect",$sRecord),"YN"); ?>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Available To<br>Customer Types</td>
		<td class="table-list-entry1" valign="top">
			<select name="xAccTypesSelect" class="form-inputbox" size="5" MULTIPLE onChange="recalculateAccTypes();">
<?php
				$selectedAccTypes = split(";",@getGENERIC("accTypes",$sRecord));
				$allSelected = "";
				for ($f = 0; $f < count($selectedAccTypes); $f++) {
					if ($selectedAccTypes[$f] == "0") {
						$allSelected = " SELECTED";
					}
				}
?>
				<option value="0" <?php print $allSelected; ?>>All</option>
<?php
				for ($f = 0; $f < count($accTypes); $f++) {
					$thisSelected = "";
					for ($g = 0; $g < count($selectedAccTypes); $g++) {
						if ($selectedAccTypes[$g] == $accTypes[$f]["accTypeID"]) {
							$thisSelected = "SELECTED";
						}
					}
?>
				<option value="<?php print $accTypes[$f]["accTypeID"]; ?>" <?php print $thisSelected; ?>><?php print $accTypes[$f]["name"]; ?></option>
<?php				
				}
?>
			</select>
			<input type="hidden" name="xAccTypes" value="<?php print @getGENERIC("accTypes",$sRecord); ?>">
			<script language="JavaScript">
				function recalculateAccTypes() {
					accTypes = ";";
					for (f = 0; f < document.detailsForm.xAccTypesSelect.options.length; f++) {
						if (document.detailsForm.xAccTypesSelect.options[f].selected == true) {
							accTypes = accTypes + document.detailsForm.xAccTypesSelect.options[f].value+";";
						}
					}
					document.detailsForm.xAccTypes.value = accTypes;
				}
			</script>
		</td>
	</tr>	
	<?php
		if (is_array($flagArray) != 0) {
	?>
	<tr>
		<td class="table-list-title" valign="top">Product Flags</td>
		<td class="table-list-entry1" valign="top">
			<table border="0" celpadding="2" cellspacing="0">
		<?php
			for ($f=0; $f < count($flagArray); $f++) {
				if ($sRecord["flag".$flagArray[$f]["flagID"]] == "Y") {
					$flagValue = "Y";
				} else {
					$flagValue = "N";
				}
		?>
			<tr>
				<td><font class="normaltext"><b><?php print $flagArray[$f]["description"]; ?>:</b></font></td>
				<td><font class="normaltext"><?php $myForm->createYesNo("xFlag".$flagArray[$f]["flagID"],$flagValue,"YN"); ?></font></td>
			</tr>
		<?php
			}
		?>
			</table>
		</td>
	</tr>
	<?php
		}
	?>
	</table>
</div>
	<a name="divAssociatedAnchor"></a>
	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-title" valign="top" colspan="2"><a name="assoc"></a>Associated Products <A href="javascript:toggleDiv('divAssociated');"><span id="divAssociatedToggle">hide</span></a></td>
	</tr>
	</table>
<div id="divAssociated">
	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-entry1" valign="top">
			<table cellpadding="2" cellspacing="0" border="0">
				<tr>
<script>
						function searchAssoc() {
							document.getElementById("assocSearch").src ="products_searchassoc.php?xSearchString="+document.detailsForm.xSearchString.value+"&<?php print userSessionGET(); ?>";
							document.getElementById("assocSearch").style.display = "none";
							document.getElementById("assocSearch").style.display = "inline";
						}
</script>
					<td valign="top">
						<?php 
							if ($xBrowserShort == "Firefox") {
								$myForm->createText("xSearchString",30,100,"","captureSearchAssocFirefox;");
							} else {
								$myForm->createText("xSearchString",30,100,"","captureSearchAssoc(this,event);");
							}
						?>
						&nbsp;<input type="button" name="buttonAssocSearch" class="button-grey" onClick="searchAssoc();" value="Search">
						<br>
						<iframe id="assocSearch" frameborder="0" STYLE="border:solid black 1px; width: 300px; height: 100px" src="blank.html"></iframe>
						<br><input type="checkbox" name="xAssociatedBi" value="" <?php if (retrieveOption("prodEditAssociatedLinkDefault")) { ?>CHECKED<?php } ?>><font class="normaltext">Create 2-way links for new selections
					</td>
					<!--</form>-->
					<td valign="top">
						<font class="boldtext">Currently Associated:</font><br>
						<select name="xAssociatedProducts" size="8" class="form-inputbox" style="width: 220px;">
<?php
						$aResult = $dbA->query("select $tableProducts.productID,code,name from $tableProducts,$tableAssociated where $tableProducts.productID=$tableAssociated.assocID and $tableAssociated.productID=$xProductID order by $tableAssociated.position");
						$aCount = $dbA->count($aResult);
						for ($f = 0; $f < $aCount; $f++) {
							$aRecord = $dbA->fetch($aResult);
							if ($aRecord["code"] != "") {
								$newText = $aRecord["code"]." : ".$aRecord["name"];
							} else {
								$newText = $aRecord["name"];
							}
?>
							<option value="<?php print $aRecord["productID"]; ?>:0"><?php print $newText; ?></option>
<?php
						}
?>						
						</select>
						<input type="hidden" name="xAssociatedSend" value=""><input type="hidden" name="xAssociatedDelete" value="">
					</td>
					<td>
						<a href="javascript:assocMoveItem('up');"><img src="images/select_up.gif" border="0" width="15" height="15"><br>
						<img src="images/spacer.gif" border="0" width="15" height="3"><br>
						<a href="javascript:assocMoveItem('down');"><img src="images/select_down.gif" border="0" width="15" height="15"><br>
						<img src="images/spacer.gif" border="0" width="15" height="3"><br>
						<a href="javascript:assocDeleteItem();"><img src="images/select_delete.gif" border="0" width="15" height="15">
					</td>
				</tr>
			</table>
		</td>
	</tr>	
	</table>
</div>		
	<a name="divMiscAnchor"></a>
	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-title" valign="top" colspan="2">Misc. Options <A href="javascript:toggleDiv('divMisc');"><span id="divMiscToggle">hide</span></a></td>
	</tr>
	</table>
<div id="divMisc">
	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-title" valign="top" width="105">Template For Product Page</td>
		<td class="table-list-entry1" valign="top">
			<select name="xTemplateFile" class="form-inputbox">
<?php
					$myDir = opendir("../templates");
					while (false !== ($file = readdir($myDir))) {
						if (substr($file,strlen($file)-5,5) == ".html" || substr($file,strlen($file)-4,4) == ".htm") {
							if ($file == @$sRecord["templateFile"]) {
								$thisSelected = "SELECTED";
							} else {
								$thisSelected = "";
							}
?>
				<option <?php print $thisSelected; ?>><?php print $file; ?></option>
<?php
						}
					}	
?>				
			</select>
		</td>
	</tr>	
	</table>
</div>


</div>
	<a name="divLanguagesAnchor"></a>
	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-title" valign="top" colspan="2"><a name="assoc"></a>Other Languages <A href="javascript:toggleDiv('divLanguages');"><span id="divLanguagesToggle">hide</span></a></td>
	</tr>
	</table>
<div id="divLanguages">
	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">

<?php
	for ($f = 0; $f < count($languages); $f++) {
		$thisLanguage = $languages[$f]["languageID"];
		if ($thisLanguage != 1) {
?>
	<tr>
		<td class="table-list-entry2" valign="top" colspan="2">Language: <?php print $languages[$f]["name"]; ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xName".$thisLanguage,80,250,@getGENERIC("name".$thisLanguage,$sRecord),""); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Short Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xShortDescription".$thisLanguage,80,250,@getGENERIC("shortdescription".$thisLanguage,$sRecord),"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Full Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createTextArea("xDescription".$thisLanguage,60,7,@getGENERIC("description".$thisLanguage,$sRecord),""); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">META Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMetaDescription".$thisLanguage,80,250,@getGENERIC("metaDescription".$thisLanguage,$sRecord),"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">META Keywords</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMetaKeywords".$thisLanguage,80,250,@getGENERIC("metaKeywords".$thisLanguage,$sRecord),"general"); ?></td>
	</tr>
<?php
		for ($g = 0; $g < count($extraFieldsArray); $g++) {
			switch ($extraFieldsArray[$g]["type"]) {
				case "TEXT":
?>
	<tr>
		<td class="table-list-title" valign="top" width="105"><?php print $extraFieldsArray[$g]["title"]; ?></td>
		<td class="table-list-entry1" valign="top">
<?php
					$myForm->createText("xExtra".$extraFieldsArray[$g]["extraFieldID"]."_".$thisLanguage,80,250,@getGENERIC("extrafield".$extraFieldsArray[$g]["extraFieldID"]."_".$thisLanguage,$sRecord),"");		
?>
		</td>
	</tr>
<?php
					break;
				case "TEXTAREA":
?>
	<tr>
		<td class="table-list-title" valign="top" width="105"><?php print $extraFieldsArray[$g]["title"]; ?></td>
		<td class="table-list-entry1" valign="top">
<?php
					$myForm->createTextArea("xExtra".$extraFieldsArray[$g]["extraFieldID"]."_".$thisLanguage,60,5,@getGENERIC("extrafield".$extraFieldsArray[$g]["extraFieldID"]."_".$thisLanguage,$sRecord),"");
?>
		</td>
	</tr>
<?php
					break;
			}
		}
?>
	
<?php
		}
	}
?>
	</table>
</div>

	<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><input type="button" name="goBack" value="&lt; Back" class="button-expand" onClick="self.history.go(backTimes);">&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
	</table>


	
<script language="JavaScript"><?php print $recalculator; ?></script>
<?php $myForm->closeForm("xCode"); ?>
<?php
	if ($xType=="clone" || $xProductID == 1) {
		if ($sRecord["thumbnail"] != "") {
			if (substr($sRecord["thumbnail"],0,7) == "http://") {
				$theThumbnail = $sRecord["thumbnail"];
			} else {
				$xThumb = split("/",$sRecord["thumbnail"]);
				$theThumbnail = @$xThumb[count($xThumb)-1];
			}
		} else {
			$theThumbnail = "";
		}
		if ($sRecord["mainimage"] != "") {	
			if (substr($sRecord["mainimage"],0,7) == "http://") {
				$theImage = $sRecord["mainimage"];
			} else {		
				$xImage = split("/",$sRecord["mainimage"]);
				$theImage = @$xImage[count($xImage)-1];
			}
		} else {
			$theImage = "";
		}
?>
	<script language="JavaScript">
		document.detailsForm.xThumbnailPick.value = "<?php print $theThumbnail; ?>";
		document.detailsForm.xImagePick.value = "<?php print $theImage; ?>";
	</script>
<?php
	}
?>
</center>
<script Language="JavaScript">
recalculateSections();
<?php
	for ($f = 0; $f < count($divsHide); $f++) {
		if (chop($divsHide[$f]) != "") {
			$divDo = true;
			if (chop($divsHide[$f]) == "divDigital" && retrieveOption("downloadsActivate") == 0) {
				$divDo = false;
			}
			if ($divDo) {
?>
toggleDiv('<?php print $divsHide[$f]; ?>');
<?php		
			}
		}
	}
?>
</script>
<?php	$dbA->close();	?>
</BODY>
</HTML>