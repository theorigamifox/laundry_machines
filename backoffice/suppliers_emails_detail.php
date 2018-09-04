<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$xEmailID=getFORM("xEmailID");
	$xType = getFORM("xType");
	if ($xType == "new") {
		$uRecord = null;
		$pageTitle = "Create New Supplier Email Template";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>";
		$submitButton = "Insert Template";
	}
	if ($xType == "edit") {
		dbConnect($dbA);
		$uResult = $dbA->query("select * from $tableSuppliersEmails where emailID=$xEmailID");
		$uRecord = $dbA->fetch($uResult);
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xEmailID' value='$xEmailID'>";
		$pageTitle = "Edit Supplier Email Template: ".$uRecord["name"];
		$submitButton = "Update Template";
		$dbA->close();
	}
	
	$myForm = new formElements;
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script>
	function checkFields() {
	}
</script>
<script language="JavaScript">
function insertText(openText,closeText) {
	if (document.templateForm.accFields.createTextRange) {
		caretPos = document.templateForm.accFields.createTextRange();
		selectedText = caretPos.text;
		newBit = openText + selectedText + closeText;
		rc=alert(newBit);
	}
}

function storePos(textEl) {
	if (textEl.createTextRange) {
		textEl.caretPos = document.selection.createRange().duplicate();
 	}
}

function insertAtPos (textEl, preText, postText) {
  	if (textEl.createTextRange && textEl.caretPos) {
    		var caretPos = textEl.caretPos;
		caretPos.text = preText + caretPos.text + postText;
   	} else {
   		textEl.value  = preText + postText;
 	}
}

function addAccountField() {
	accField = document.detailsForm.companyFields.options[document.detailsForm.companyFields.selectedIndex].value;
	insertAtPos(document.detailsForm.xMessage,accField,"");
	document.detailsForm.xMessage.focus();
}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title"><?php print $pageTitle; ?></td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","suppliers_emails_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Template Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xName",80,250,@getGENERIC("name",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Subject</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xSubject",80,250,@getGENERIC("subject",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Content<BR>(Text Version)</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createTextArea("xMessage",60,20,@getGENERIC("message",$uRecord),"general"," onClick=\"storePos(this);\" onKeyUp=\"storePos(this);\""); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Content<BR>(HTML Version)</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createTextArea("xMessageHTML",60,20,@getGENERIC("messageHTML",$uRecord),"general"," onClick=\"storePos(this);\" onKeyUp=\"storePos(this);\""); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">From/Reply-To Email<br>Address</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xReplyTo",80,250,@getGENERIC("replyto",$uRecord),"email"); ?></td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
<?php $myForm->closeForm("xName"); ?>
</center>
</BODY>
</HTML>
