<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	$xType=getFORM("xType");
	if ($xType=="new") {
		$pageTitle = "Add New Shipping Type";
		$submitButton = "Insert Shipping Type";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>";

		$xLoginEnabledYes = "CHECKED";
		$xLoginEnabledNo = "";		
	}
	$xUseexchangerate = "N";
	if ($xType=="edit") {
		$xShippingID = getFORM("xShippingID");
		$pageTitle = "Edit Existing Shipping Type";
		$submitButton = "Update Shipping Type";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xShippingID' value='$xShippingID'>";
		$uResult = $dbA->query("select * from $tableShippingTypes where shippingID=$xShippingID");	
		$uRecord = $dbA->fetch($uResult);
	}
	
	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	$accTypes = $dbA->retrieveAllRecords($tableCustomersAccTypes,"accTypeID");
	$languages = $dbA->retrieveAllRecords($tableLanguages,"languageID");
	
	$myForm = new formElements;
	
	if (@$uRecord["fmType"] == "M") {
		$fmMultiple = "SELECTED";
		$fmFlat = "";
	} else {
		$fmMultiple = "";
		$fmFlat = "SELECTED";
	}
	
	$ctQuantity = "";
	$ctWeight = "";
	$ctTotal = "";
	switch (@$uRecord["calcType"]) {
		case "Q":
			$ctQuantity = "SELECTED";
			break;
		case "T":
			$ctTotal = "SELECTED";
			break;
		case "W":
			$ctWeight = "SELECTED";
			break;
	}
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
</HEAD>
<script>
	<?php $myForm->outputCurrencyArray($currArray); ?>
	<?php $recalculator = $myForm->createCurrencyRecalculate($currArray,"baseprice","xBasePrice"); ?>

	function checkFields() {
		return true;
	}

    function presentValue(value,dp,pt,mt,at) {
    	if (value < 0) {
    		isMinus = "-";
    	} else {
    		isMinus = "";
    	}
    	value = eval(Math.abs(value));
        if(value<=0.9999) {
            newPounds='0';
        } else {
            newPounds=parseInt(value);
        }
        dec='1';
        for (var i=1; i<=dp;i++) {
            dec=dec+'0';
        }
        if (value>0) {
            newPence=Math.round((eval(value)+.000008 - newPounds)*(eval(dec)));
        } else {
            newPence=0;
        }
        compstring='9';
        for (var i=1; i <=dp-1;i++) {
            if (eval(newPence) <= eval(compstring)) newPence='0'+newPence;
            compstring=compstring+'9';
        }
        if (dp>0) {
            if (newPence==eval(dec)) { newPounds++; newPence=0; }
            newString=isMinus+pt+newPounds+mt+newPence+at;
        } else {
            newString=isMinus+pt+newPounds+at;
        }
        return (newString);
    }	
	
	<?php $recalculator .= $myForm->createCurrencyRecalculate($currArray,"lowprice","xLowprice"); ?>
	<?php $recalculator .= $myForm->createCurrencyRecalculate($currArray,"highprice","xHighprice"); ?>
</script>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title"><?php print $pageTitle; ?></td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","shipping_types_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xName",60,250,@getGENERIC("name",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Flat Rate / Multiplication</td>
		<td class="table-list-entry1" valign="top"><select name="xFmType" class="form-inputbox">
			<option value="F" <?php print $fmFlat; ?>>Flat Rate</option>
			<option value="M" <?php print $fmMultiple; ?>>Multiplication</option>
			</select>
		</td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Value To Use</td>
		<td class="table-list-entry1" valign="top"><select name="xCalcType" class="form-inputbox">
			<option value="Q" <?php print $ctQuantity; ?>>Quantity</option>
			<option value="T" <?php print $ctTotal; ?>>Goods Total</option>
			<option value="W" <?php print $ctWeight; ?>>Weight</option>
			</select>&nbsp;&nbsp;Round-Up Values <?php $myForm->createYesNo("xRounding",@getGENERIC("rounding",$uRecord),"YN"); ?>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Base Price</td>
		<td class="table-list-entry1" valign="top">
			<?php $myForm->createPricingFields($currArray,@$uRecord,"baseprice","xBasePrice"); ?>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Available To Customer Type</td>
		<td class="table-list-entry1" valign="top">
			<select name="xAccTypeID" class="form-inputbox">
<?php
				$selectedAccTypes = makeInteger(@getGENERIC("accTypeID",$uRecord));
				$allSelected = "";
				if ($selectedAccTypes == "-1") {
						$allSelected = " SELECTED";
				} else {
					$allSelected = "";
				}
?>
				<option value="-1" <?php print $allSelected; ?>>No Account</option>
<?php
				if ($selectedAccTypes == "0") {
					$allSelected = " SELECTED";
				} else {
					$allSelected = "";
				}
?>
				<option value="0" <?php print $allSelected; ?>>All</option>
<?php
				for ($f = 0; $f < count($accTypes); $f++) {
					$thisSelected = "";
					if ($selectedAccTypes == $accTypes[$f]["accTypeID"]) {
						$thisSelected = "SELECTED";
					}
?>
				<option value="<?php print $accTypes[$f]["accTypeID"]; ?>" <?php print $thisSelected; ?>><?php print $accTypes[$f]["name"]; ?></option>
<?php				
				}
?>
			</select>
		</td>
	</tr>		
	<tr>
		<td class="table-list-title" valign="top">Weight Limits</td>
		<td class="table-list-entry1" valign="top">Hide if above (0 = ignore): <?php $myForm->createText("xWeight",8,10,makeDecimal(@getGENERIC("weight",$uRecord)),"decimal"); ?>&nbsp;&nbsp;Hide if below (0 = ignore): <?php $myForm->createText("xLowWeight",8,10,makeDecimal(@getGENERIC("lowweight",$uRecord)),"decimal"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Goods Total Limits</td>
		<td class="table-list-entry1" valign="top">
			Hide if above (0 = ignore): <?php $myForm->createPricingFields($currArray,@$uRecord,"highprice","xHighprice"); ?>
			Hide if below (0 = ignore): <?php $myForm->createPricingFields($currArray,@$uRecord,"lowprice","xLowprice"); ?>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Shipping Value Taxable</td>
		<td class="table-list-entry1" valign="top">
			<?php $myForm->createYesNo("xTaxable",@getGENERIC("taxable",$uRecord),"YN"); ?>
		</td>
	</tr>
<?php
	for ($f = 0; $f < count($languages); $f++) {
		$thisLanguage = $languages[$f]["languageID"];
		if ($thisLanguage != 1) {
?>
	<tr>
		<td class="table-list-title" valign="top" colspan="2">Language: <?php print $languages[$f]["name"]; ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xName".$thisLanguage,60,250,@getGENERIC("name".$thisLanguage,$uRecord),"general"); ?></td>
	</tr>	
<?php
		}
	}
?>				
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
<script language="JavaScript"><?php print $recalculator; ?></script>
</BODY>
<?php
	$dbA->close();
?>
