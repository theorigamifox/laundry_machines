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
		<td class="detail-title">General Options</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","reports_options_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Track Product Popularity?</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xReportsPopularProducts",retrieveOption("reportsPopularProducts"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Track Section Popularity?</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xReportsPopularSections",retrieveOption("reportsPopularSections"),"01"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Track Search Statistics?</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xReportsSearchStats",retrieveOption("reportsSearchStats"),"01"); ?></td>
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