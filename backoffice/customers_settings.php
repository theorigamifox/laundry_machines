<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$dbA = new dbAccess();
	$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
	
	$myForm = new formElements;
	
	$accTypes = null;
	$uResult = $dbA->query("select * from $tableCustomersAccTypes order by name");
	$uCount = $dbA->count($uResult);
	for ($f = 0; $f < $uCount; $f++) {
		$uRecord = $dbA->fetch($uResult);
		$accTypes[] = array("text"=>$uRecord["name"],"value"=>$uRecord["accTypeID"]);
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
	function checkFields() {
	}
</script>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">General Settings</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","customers_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Allow Customer Accounts</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xCustomerAccounts",retrieveOption("customerAccounts"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Automatically Login On Account Creation</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xAutoCustomerLogin",retrieveOption("autoCustomerLogin"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Take Customer to Account on Login</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xCustomerLoginGoAccount",retrieveOption("customerLoginGoAccount"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Allow Seperate Delivery Address</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xAllowShippingAddress",retrieveOption("allowShippingAddress"),"01"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Make County/DeliveryCounty Field A Select Box</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xFieldCountyAsSelect",retrieveOption("fieldCountyAsSelect"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">New Accounts Default Account Type</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xCustomerDefaultAccount",retrieveOption("customerDefaultAccount"),"BOTH",$accTypes); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Minimum Password Length</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMinPasswordLength",5,3,retrieveOption("minPasswordLength"),"decimal"); ?> characters</td>
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
