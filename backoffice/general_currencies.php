<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	$liveArray[] = array("text"=>"Off","value"=>"off");
	$liveArray[] = array("text"=>"Worldpay","value"=>"Worldpay");
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript">
	function goDelete(currencyID) {
		if (confirm("Are you sure you wish to delete this currency?")) {
			self.location.href="general_currencies_process.php?xAction=delete&xCurrencyID="+currencyID+"&<?php print userSessionGET(); ?>";
		}
	}
</script>
<script>
	function checkFields() {
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Currency Settings</td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title">Code</td>
		<td class="table-list-title">Name</td>
		<td class="table-list-title">Use Exchange Rate?</td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	$currencySelectArray = null;
	$dbA = new dbAccess();
	$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
	$uResult = $dbA->query("select * from $tableCurrencies order by currencyID");
	$uCount = $dbA->count($uResult);
	for ($f = 0; $f < $uCount; $f++) {
		$uRecord = $dbA->fetch($uResult);
		$currencySelectArray[] = array("text"=>$uRecord["code"],"value"=>$uRecord["currencyID"]);
?>
	<tr>
		<td class="table-list-entry1"><a href="general_currencies_detail.php?xType=edit&xCurrencyID=<?php print $uRecord["currencyID"]; ?>&<?php print userSessionGET(); ?>"><?php print $uRecord["code"]; ?></a></td>
		<td class="table-list-entry1"><a href="general_currencies_detail.php?xType=edit&xCurrencyID=<?php print $uRecord["currencyID"]; ?>&<?php print userSessionGET(); ?>"><?php print $uRecord["name"]; ?></a></td>
		<td class="table-list-entry1"><center><?php print $uRecord["useexchangerate"]; ?></center></td>
		<td class="table-list-entry1" align="right">
			<button id="buttonEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='general_currencies_detail.php?xType=edit&xCurrencyID=<?php print $uRecord["currencyID"]; ?>&<?php print userSessionGET(); ?>';">Edit</button><?php if ($uRecord["currencyID"] != 1) { ?>&nbsp;<button id="buttonEdit<?php print $f; ?>" class="button-delete" onClick="goDelete(<?php print $uRecord["currencyID"]; ?>);">Delete</button><?php } ?></td>
	</tr>
<?php
	}
?>
	<tr>
		<td colspan="3" class="table-list-title">Total Currencies:</td>
		<td class="table-list-title" align="right"><?php print $uCount; ?></td>
	</tr>
</table>
<p>
<button id="buttonSectionsEdit" class="button-expand" onClick="self.location.href='general_currencies_detail.php?xType=new&<?php print userSessionGET(); ?>'">Add New Currency</button>
<?php
	$myForm = new formElements;
?>
<p>
<?php $myForm->createForm("detailsForm","general_currencies_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type='hidden' name='xAction' value='default'>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Default Currency</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xCurrencySelect",retrieveOption("defaultCurrency"),"BOTH",$currencySelectArray); ?></td>
		<td class="table-list-entry0" align="right"><?php $myForm->createSubmit("submit","Change Default"); ?></td>
	</tr>
</table>
</form>
<p>
<?php $myForm->createForm("detailsForm","general_currencies_service.php",""); ?>
<?php userSessionPOST(); ?>
<input type='hidden' name='xAction' value='default'>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Live Exchange Rate Service</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xLiveService",retrieveOption("currencyLiveRates"),"BOTH",$liveArray); ?></td>
		<td class="table-list-entry0" align="right"><?php $myForm->createSubmit("submit","Change Service"); ?></td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="3" align="right">If you do not have an exchange rate service account with one of the above<br>companies you should choose 'Off'.</td>
	</tr>
</table>
</form>
</center>
</BODY>
</HTML>
<?php	$dbA->close(); ?>
