<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$dbA = new dbAccess();
	$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
	
	$myForm = new formElements;
	
	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	
	$paymentArray[] = array("value"=>"PAID","text"=>"When Order Is Paid");
	$paymentArray[] = array("value"=>"PLACED","text"=>"When Order Is Placed");
	
	$statusArray[] = array("value"=>"AUTH","text"=>"Create Authorized Payments");
	$statusArray[] = array("value"=>"NOTAUTH","text"=>"Create Un-Authorized Payments");
	
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
<?php $myForm->createForm("detailsForm","affiliates_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Activate Affiliate System</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xAffiliatesActivated",retrieveOption("affiliatesActivated"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Affiliate Signup Moderated</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xAffiliatesSignupModerated",retrieveOption("affiliatesSignupModerated"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Activate 2nd Tier Feature</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xAffiliatesAllow2Tier",retrieveOption("affiliatesAllow2Tier"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Minimum Payment Amount</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xAffiliatesMinimumPayment",7,15,retrieveOption("affiliatesMinimumPayment"),"decimal"); ?> <?php print $currArray[0]["code"]; ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Life Span Of Affiliate Cookie</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xAffiliatesCookieDays",7,15,retrieveOption("affiliatesCookieDays"),"decimal"); ?> days (0 = first time only)</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Create Affiliate Commission</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xAffiliatesCreatePayment",retrieveOption("affiliatesCreatePayment"),"BOTH",$paymentArray); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Status Of Created Commission</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xAffiliatesCreatePaymentStatus",retrieveOption("affiliatesCreatePaymentStatus"),"BOTH",$statusArray); ?><br>Note: If a customer checks out in a currency that isn't<br>linked to the base currency by an exchange<br>rate the system will create an Un-Authorized<br>payment regardless of this setting.</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Include Shipping For Commission Calculation</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xAffiliatesPaymentShipping",retrieveOption("affiliatesPaymentShipping"),"01"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Include Tax For Commission Calculation</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xAffiliatesPaymentTax",retrieveOption("affiliatesPaymentTax"),"01"); ?></td>
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
