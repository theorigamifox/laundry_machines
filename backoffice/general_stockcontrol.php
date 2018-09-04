<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$dbA = new dbAccess();
	$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
	
	$myForm = new formElements;

	$stockModeArray = array (
			array("When order is placed","0"),
			array("When order is paid","1"),
			array("When order is dispatched","2")
			);	
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
</HEAD>
<script>
	function checkFields() {
	}
</script>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Stock Control Settings</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","general_options_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<input type="hidden" name="xType" value="stockcontrol">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Global Enable Stock Control?</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xFeatureStockControl",retrieveOption("featureStockControl"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Deduct From Stock</td>
		<td class="table-list-entry1" valign="top">
			<select name="xStockDeductMode" class="form-inputbox">
			<?php
				for ($f = 0; $f < count($stockModeArray); $f++) {
					$stockDeductMode = retrieveOption("stockDeductMode");
					if ($stockModeArray[$f][1] == $stockDeductMode) {
						$thisSelected = "SELECTED";
					} else {
						$thisSelected = "";
					}
			?>
				<option value="<?php print $stockModeArray[$f][1]; ?>" <?php print $thisSelected; ?>><?php print $stockModeArray[$f][0]; ?></option>
			<?php
				}
			?>
		</select>
	</tr>	
	<!--<tr>
		<td class="table-list-title" valign="top">Limit Basket Qty To Stock Level</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xStockQtyLimit",retrieveOption("stockQtyLimit"),"01"); ?></td>
	</tr>-->
	<tr>
		<td class="table-list-title" valign="top">Send Warning Level Email</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xStockWarningEmail",retrieveOption("stockWarningEmail"),"01"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Send Zero Level Email</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xStockZeroEmail",retrieveOption("stockZeroEmail"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Use Warning Level Instead Of Zero For Stock Check</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xStockWarningNotZero",retrieveOption("stockWarningNotZero"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Force Stock Check At Checkout</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xStockCheckoutCheck",retrieveOption("stockCheckoutCheck"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Show Product Level Stock On Section Structure</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xStockShowSectionStructure",retrieveOption("stockShowSectionStructure"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit","Update Settings"); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>
