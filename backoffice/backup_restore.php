<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$pageTitle = "Restore SQL Database";
	$submitButton ="Restore Data";

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
		return true;
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
<?php $myForm->createForm("detailsForm","backup_restore_process.php","","multipart"); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="gorestore">
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">SQL Backup File (Upload From Computer)</td>
		<td class="table-list-entry1" valign="top">
			<input type="file" name="xSQLFile" size="50" class="form-inputbox" accept="image/jpeg,image/gif"  onFocusIn="this.style.borderColor='#FF0000'" onFocusOut="this.style.borderColor='#000000'" onKeyPress="return false;" onKeyDown="return false;">
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top" colspan="2"><Center>OR</center></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">SQL Backup File (Load From Server)</td>
		<td class="table-list-entry1" valign="top">
	<?php
		$myPath = realpath("index.php");
		$myPath = str_replace("index.php","",$myPath);
		$myPath = str_replace("\\","/",$myPath);
	?>
			<input type="text" name="xSQLFileLocal" value="<?php print $myPath; ?>" size="50" class="form-inputbox" onFocusIn="this.style.borderColor='#FF0000'" onFocusOut="this.style.borderColor='#000000'">
		</td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right">Restoring data from an SQL backup will overwrite all existing data.<br>Only use this option if you are sure you need to and, if unsure, make another<br>backup of the data on the system before proceeding.</td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
<?php $myForm->closeForm("xSQLFile"); ?>
</center>
</BODY>
</HTML>
