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
		<td class="detail-title">Checkout Settings</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","general_options_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<input type="hidden" name="xType" value="checkout">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Force Customer Account Creation</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xOrderingForceAccount",retrieveOption("orderingForceAccount"),"01"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Minimum Order Value (Base Currency)</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMinimumOrderValue",10,5,retrieveOption("minimumOrderValue"),"decimal"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Skip Payment Option Step If Only<br>One Payment Type Available</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xOrderingSkipPayment",retrieveOption("orderingSkipPayment"),"01"); ?><br>
		Note: If you are taking credit card details directly, this step will not<br>be skipped even if you only have
		one payent option.</td>
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
