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
		<td class="detail-title">Recent View Settings</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","general_options_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<input type="hidden" name="xType" value="recentlyviewed">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Activate Recently Viewed Feature</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xRecentViewActivated",retrieveOption("recentViewActivated"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Product History To Store</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xRecentViewProducts",10,5,retrieveOption("recentViewProducts"),"integer"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Section History To Store</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xRecentViewSections",10,5,retrieveOption("recentViewSections"),"integer"); ?></td>
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
