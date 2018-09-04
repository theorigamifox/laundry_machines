<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$xType=getFORM("xType");
	if ($xType=="new") {
		$pageTitle = "Add New Currency";
		$submitButton = "Insert Currency";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>";

		$xLoginEnabledYes = "CHECKED";
		$xLoginEnabledNo = "";		
	}
	$xUseexchangerate = "N";
	if ($xType=="edit") {
		$xCurrencyID = getFORM("xCurrencyID");
		$pageTitle = "Edit Existing Currency";
		$submitButton = "Update Currency";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xCurrencyID' value='$xCurrencyID'>";
		$dbA = new dbAccess();
		$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
		$uResult = $dbA->query("select * from $tableCurrencies where currencyID=$xCurrencyID");	
		$uRecord = $dbA->fetch($uResult);
		$dbA->close();
		$xUseexchangerate = $uRecord["useexchangerate"];
	}
	
	if ($xUseexchangerate == "N") {
		$xUERcheck = "";
	} else {
		$xUERcheck = " CHECKED";
	}
	
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
		<td class="detail-title"><?php print $pageTitle; ?></td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","general_currencies_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">ISO Code</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCode",5,3,@getGENERIC("code",$uRecord),"alpha-numeric"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">ISO Number</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xIsonumber",5,3,@getGENERIC("isonumber",$uRecord),"alpha-numeric"); ?> e.g. 826 = GBP. Required for some gateways.</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xName",30,50,@getGENERIC("name",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Decimal Places</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xDecimals",3,1,@getGENERIC("decimals",$uRecord),"integer"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Pre-text</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xPretext",10,20,@getGENERIC("pretext",$uRecord),""); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Middle-text</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMiddletext",10,20,@getGENERIC("middletext",$uRecord),""); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Post-text</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xPosttext",10,20,@getGENERIC("posttext",$uRecord),""); ?></td>
	</tr>
<?php
	if (@$uRecord["currencyID"] != 1) {	
?>
	<tr>
		<td class="table-list-title" valign="top">Visible</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xVisible",@getGENERIC("visible",$uRecord),"YN"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Can Checkout In Currency</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xCheckout",@getGENERIC("checkout",$uRecord),"YN"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top" colspan="2"><input type="checkbox" value="Y" name="xUseexchangerate" <?php print $xUERcheck; ?>> Use Exchange Rate ?</td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Exchange Rate</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xExchangerate",8,15,@getGENERIC("exchangerate",$uRecord),"decimal"); ?></td>
	</tr>					
<?php
	}
?>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
</HTML>
