<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	
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
		<td class="detail-title">Order Admin Settings</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","general_options_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<input type="hidden" name="xType" value="orderadmin">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Activate Dispatch Functionality</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xOrderAdminActivateDispatch",retrieveOption("orderAdminActivateDispatch"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Allow Partial Dispatches</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xOrderAdminDispatchPartial",retrieveOption("orderAdminDispatchPartial"),"01"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Email Customer On Dispatch</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xOrderAdminEmailDispatch",retrieveOption("orderAdminEmailDispatch"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Send Copy Of Dispatch Email</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xOrderAdminDispatchCopy",retrieveOption("orderAdminDispatchCopy"),"01"); ?>
		<br>Email Address: <?php $myForm->createText("xOrderAdminDispatchCopyAddress",40,250,retrieveOption("orderAdminDispatchCopyAddress"),"email"); ?>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Activate Dispatch Tracking</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xOrderAdminDispatchTracking",retrieveOption("orderAdminDispatchTracking"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Activate Receipt</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xOrderAdminActivateReceipt",retrieveOption("orderAdminActivateReceipt"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Show CC Number In Groups<br>Of 4 Numbers With Spaces</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xOrdersSpaceCC",retrieveOption("ordersSpaceCC"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Clear CC Details on PAID</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xOrderAdminClearCC",retrieveOption("orderAdminClearCC"),"01"); ?></td>
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
