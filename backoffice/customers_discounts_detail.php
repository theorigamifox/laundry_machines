<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	$xType=getFORM("xType");
	if ($xType=="new") {
		$pageTitle = "Add New Discount";
		$submitButton = "Insert Discount";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>";
		$uRecord["accTypes"] = ";0;";
	}
	$xUseexchangerate = "N";
	if ($xType=="edit") {
		$xDiscountID = getFORM("xDiscountID");
		$pageTitle = "Edit Existing Discount";
		$submitButton = "Update Discount";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xDiscountID' value='$xDiscountID'>";
		$uResult = $dbA->query("select * from $tableDiscounts where discountID=$xDiscountID");	
		$uRecord = $dbA->fetch($uResult);
	}
	
	$myForm = new formElements;
	
	$accTypeArray = $dbA->retrieveAllRecords($tableCustomersAccTypes,"accTypeID");
	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
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
	<?php $recalculator = $myForm->createCurrencyRecalculate($currArray,"price","xPrice"); ?>

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
	
</script>
<script>
<?php $myForm->outputCurrencyArray($currArray); ?>
<?php $recalculator = $myForm->createCurrencyRecalculate($currArray,"compvalue","xCompvalue"); ?>
</script>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title"><?php print $pageTitle; ?></td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","customers_discounts_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xName",30,100,@getGENERIC("name",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Type</td>
		<td class="table-list-entry1" valign="top"><select name="xDType" class="form-inputbox">
			<option value="G" <?php if (@$uRecord["type"]=="G") { echo "SELECTED"; } ?>>Goods Total Discount</option>
			<option value="S" <?php if (@$uRecord["type"]=="S") { echo "SELECTED"; } ?>>Shipping Discount</option>
			</select></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Trigger</td>
		<td class="table-list-entry1" valign="top">
			<table border="0">
			<tr>
				<td><input type="radio" name="xTrigger" value="GOODS" <?php if ($xType == "new" || @$uRecord["compvalue1"] > 0) { ?>CHECKED<?php } ?>></td>
				<td><font class="boldtext">Goods Total &gt;</font></td>
				<td><?php $myForm->createPricingFields($currArray,@$uRecord,"compvalue","xCompvalue"); ?></td>
			</tr>
			<tr
				<td><input type="radio" name="xTrigger" value="QTY" <?php if (@$uRecord["qty"] > 0) { ?>CHECKED<?php } ?>></td>
				<td><font class="boldtext">Quantity &gt;</font></td>
				<td><?php $myForm->createText("xQty",5,10,makeInteger(@getGENERIC("qty",$uRecord)),"integer"); ?></td>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Discount</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xPercent",5,10,makeDecimal(@getGENERIC("percent",$uRecord)),"decimal"); ?>%</td>
	</tr>		
	<tr>
		<td class="table-list-title" valign="top">Account Types</td>
		<td class="table-list-entry1" valign="top">
			<select name="xAccTypeSelect" class="form-inputbox" size="5" MULTIPLE onChange="recalcAcc();">
<?php
				$currentValue = @getGENERIC("accTypes",$uRecord);
				$accSplit = split(";",$currentValue);
					$thisSelected = "";
					for ($h = 0; $h < count($accSplit); $h++) {
						if ($accSplit[$h] == "0") {
							$thisSelected = "SELECTED";
						}
					}
?>			
			<option value="0" <?php print $thisSelected; ?>>All</option>
			<?php
				for ($g= 0 ; $g < count($accTypeArray); $g++) {
					$thisSelected = "";
					for ($h = 0; $h < count($accSplit); $h++) {
						if ($accSplit[$h] == $accTypeArray[$g]["accTypeID"]) {
							$thisSelected = "SELECTED";
						}
					}
					?> <option value="<?php print $accTypeArray[$g]["accTypeID"]; ?>" <?php print $thisSelected; ?>><?php print $accTypeArray[$g]["name"]; ?></option> <?php
				}
			?>
			</select><input type="hidden" name="xAccTypes" value="<?php print @$uRecord["accTypes"]; ?>">
			<script>
				function recalcAcc() {
					newList = ";";
					for (f = 0; f < document.detailsForm.xAccTypeSelect.options.length; f++) {
						if (document.detailsForm.xAccTypeSelect.options[f].selected == true) {
							newList = newList + document.detailsForm.xAccTypeSelect.options[f].value+";";
						}
					}
					document.detailsForm.xAccTypes.value = newList;
				}
			</script>
		</td>
	</tr>		
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
<script language="JavaScript"><?php print $recalculator; ?></script>
</BODY>
</HTML>
<?php
	$dbA->close();
?>