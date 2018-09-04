<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$xTemplate=getFORM("xTemplate");
	$pageTitle = "Edit Email Template: $xTemplate";
	$submitButton = "Update Template";
	$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xTemplate' value='$xTemplate'>";
	$dbA = new dbAccess();
	$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
	$uResult = $dbA->query("select * from $tableEmails where template=\"$xTemplate\"");	
	$uRecord = $dbA->fetch($uResult);
	$dbA->close();
	
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
<?php $myForm->createForm("detailsForm","emails_templates_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
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
<?php
	$outputEmail = false;
	switch ($xTemplate) {
		case "MERCHORDER":
		case "MERCHPAYCONF":
		case "MERCHPAYFAIL":
		case "STOCKWARN":
		case "STOCKZERO":
		case "CONTACTFORM":
		case "MERCHAFFNEW":
		case "MERCHACCOPEN":
		case "MERCHNEWSLETTER":
		case "MERCHREVIEW":
			$outputEmail = true;
	}
	if ($outputEmail == true) {
?>
	<tr>
		<td class="table-list-title" valign="top">Recipient Email<br>Address(es)</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xRecipient",80,250,@getGENERIC("recipient",$uRecord),"email"); ?></td>
	</tr>
<?php
	}
?>
	<tr>
		<td class="table-list-title" valign="top">From/Reply-To Email<br>Address</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xReplyTo",80,250,@getGENERIC("replyto",$uRecord),"email"); ?></td>
	</tr>
<tr>
		<td class="table-list-title" valign="top">Activated</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xActivated",@getGENERIC("activated",$uRecord),"YN"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
<?php
	if ($outputEmail == false) {
?>
<input type="hidden" name="xRecipient" value="">
<?php
	}
?>
<?php $myForm->closeForm("xSubject"); ?>
</center>
</BODY>
</HTML>
