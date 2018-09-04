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
		<td class="detail-title">Digital Products Settings</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","general_options_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<input type="hidden" name="xType" value="digitalproducts">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Activate Digital Products Support</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xDownloadsActivate",retrieveOption("downloadsActivate"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Download Key Valid Time In Hours</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xDownloadsTime",10,5,retrieveOption("downloadsTime"),"integer"); ?><br>(0 = no time limit on download key)</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Download Key Number Uses</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xDownloadsUses",10,5,retrieveOption("downloadsUses"),"integer"); ?><br>(0 = no maximum number of uses on download key)</td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Download Files Directory</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xDownloadsDirectory",60,400,retrieveOption("downloadsDirectory"),"general"); ?><br>It is important that this directory be protected from direct web access!</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Activate Instant Dispatch</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xDownloadsAllowInstant",retrieveOption("downloadsAllowInstant"),"01"); ?><br>Digital products that do not require registration details will be dispatched<br>when order is PAID if this and 'Allow Partial Dispatching' are set to YES</td>
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
