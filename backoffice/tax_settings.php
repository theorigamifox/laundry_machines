<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$dbA = new dbAccess();
	$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
	
	$myForm = new formElements;
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
		<td class="detail-title">Tax Settings</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","tax_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Tax Enabled</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xTaxEnabled",retrieveOption("taxEnabled"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Add Tax To Shipping</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xTaxOnShipping",retrieveOption("taxOnShipping"),"01"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Include Delivery Address In Tax Calculation</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xTaxIncludeDeliveryAddress",retrieveOption("taxIncludeDeliveryAddress"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Zero Tax If Delivery Address Not Taxable</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xTaxZeroDelNoTax",retrieveOption("taxZeroDelNoTax"),"01"); ?></td>
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
