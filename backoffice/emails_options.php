<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$pageTitle = "Email Options";
	$submitButton = "Update Email Options";
	$hiddenFields = "<input type='hidden' name='xAction' value='update'>";
	dbConnect($dbA);

	$xEmailMerchTo = retrieveOption("emailMerchTo");
	$xEmailCustomerFrom = retrieveOption("emailCustomerFrom");
	$xEmailMerchFromCustomer = retrieveOption("emailMerchFromCustomer");
	if ($xEmailMerchFromCustomer == 0) {
		$xEmailMerchFromCustomer = "";
	} else {
		$xEmailMerchFromCustomer = "CHECKED";
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
<?php $myForm->createForm("detailsForm","emails_options_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-entry0" valign="top" colspan="2"><center><B>Emails To Merchant</b></center></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Default Send To Email Address(es)</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xEmailMerchTo",60,250,$xEmailMerchTo,"general"); ?><br>
			<!--<input type="checkbox" name="xEmailMerchFromCustomer" value="1" <?php print $xEmailMerchFromCustomer; ?>> Use customer's email address as From address-->
		</td>
	</tr>
	<tr>
		<td class="table-list-entry0" valign="top" colspan="2"><center><B>Other Options</b></center></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Default From Email Address</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xEmailCustomerFrom",60,250,$xEmailCustomerFrom,"email"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Send Payment Success/Failure Emails</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xSendMerchPaymentEmail",retrieveOption("sendMerchPaymentEmail"),"01"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
<?php $myForm->closeForm("xEmailMerchTo"); ?>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>
