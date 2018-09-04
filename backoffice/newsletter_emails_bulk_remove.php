<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$pageTitle = "Bulk Remove Emails From File";
	$submitButton ="Remove Emails";

	$myForm = new formElements;
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
</HEAD>
<Script>
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
<?php $myForm->createForm("detailsForm","newsletter_emails_bulk_remove_process.php","","multipart"); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="removeemails">
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Email File</td>
		<td class="table-list-entry1" valign="top">
			<input type="file" name="xEmailFile" size="50" class="form-inputbox" accept="image/jpeg,image/gif"  onFocusIn="this.style.borderColor='#FF0000'" onFocusOut="this.style.borderColor='#000000'" onKeyPress="return false;" onKeyDown="return false;">
		</td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right">The file must be formatted with a single email address on each line. It can be surrounded<br>by &quot; characters as these will be automatically stripped.</td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
<?php $myForm->closeForm("xEmailFile"); ?>
</center>
</BODY>
</HTML>
