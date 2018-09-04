<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$xFile=getFORM("xFile");
	$fileBits=explode(".",$xFile);
	$extend = "";
	$xFile = str_replace("../","",$xFile);
	if (strpos($xFile,$jssShopFileSystem) === FALSE) {
		echo "Invalid file specified";
		exit;
	}
	if ($fileBits[count($fileBits)-1] == "html") {
		$extend = ".html";
	}
	if ($fileBits[count($fileBits)-1] == "css") {
		$extend = ".css";
	}
	if ($extend == "") {
		echo "Invalid file specified";
		exit;
	}
	$xStartDir=getFORM("xStartDir");
	$pageTitle = "Edit Template: $xFile";
	$submitButton = "Update Template";
	$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xFile' value='$xFile'><input type='hidden' name='xStartDir' value='$xStartDir'>";
	if (!is_readable($xFile)) {
		echo "<b>Template cannot be read - please check permissions on the template directory</b>";
		exit;
	}
	$fd = fopen ($xFile, "r"); 
	$xFileContent = fread ($fd, filesize ($xFile)); 
	fclose ($fd); 
	$myForm = new formElements;
	
	$xFileContent = eregi_replace("<","&lt;",$xFileContent);
	$xFileContent = eregi_replace(">","&gt;",$xFileContent);
	$xFileContent = eregi_replace('"',"&quot;",$xFileContent);
	
	$xFileContent = eregi_replace("&nbsp;","&amp;nbsp;",$xFileContent);
	
	dbConnect($dbA);
	if (retrieveOption("templatesEditWordWrap") == 1) {
		$wordWrap = "Soft";
	} else {
		$wordWrap = "Off";
	}
	$dbA->close();
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
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

function goSaveAs() {
	if (document.detailsForm.xSaveAs.value == "") {
		alert("Please enter a name for this template");
	} else {
		document.detailsForm.submit();
	}
}

function goSave() {
	document.detailsForm.xSaveAs.value = "";
	document.detailsForm.submit();
}
</script>
<script>
	function checkFields() {
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
<?php $myForm->createForm("detailsForm","templates_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xExtend" value="<?php print $extend; ?>">
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-entry1" valign="top" colspan="2"><textarea name="xContent" cols="80" rows="35" class="form-inputbox" onFocusIn="this.style.borderColor='#FF0000'" onFocusOut="this.style.borderColor='#000000'"  wrap='<?php print $wordWrap; ?>'><?php print $xFileContent; ?></textarea></td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="1" align="right"><?php $myForm->createText("xSaveAs",20,40,"","alpha-numeric"); ?><B><?php print $extend; ?></b>&nbsp<button name="buttonSaveAs" class="button-expand" onClick="goSaveAs();">Save As</button></td>
		<td class="table-list-entry0" colspan="1" align="right"><button name="buttonSave" class="button-expand" onClick="goSave();">Save</button></td>
	</tr>	
</table>
<?php
	if (getFORM("xRefresh") == "YES") {
?>
		<Script>parent.refreshList();</script>
<?php
	}
?>
<?php $myForm->closeForm("xContent"); ?>
</center>
</BODY>
</HTML>
