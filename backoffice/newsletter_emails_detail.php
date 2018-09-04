<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	
	$xType=getFORM("xType");
	if ($xType=="new") {
		$pageTitle = "Add New Email Address";
		$submitButton = "Insert Email Address";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>".hiddenReturnPOST();	
	}
	if ($xType=="edit") {
		$xRecipientID = getFORM("xRecipientID");
		$pageTitle = "Edit Existing Email Address";
		$submitButton = "Update Email Address";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xRecipientID' value='$xRecipientID'>".hiddenReturnPOST();
		$uResult = $dbA->query("select * from $tableNewsletter where recipientID=$xRecipientID");	
		$uRecord = $dbA->fetch($uResult);
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
<?php $myForm->createForm("detailsForm","newsletter_emails_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Email Address</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xEmailaddress",40,250,@getGENERIC("emailaddress",$uRecord),"email"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
<?php $myForm->closeForm("xEmailaddress"); ?>
</HTML>
<?php
	$dbA->close();
?>
