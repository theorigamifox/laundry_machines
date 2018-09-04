<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$xType=getFORM("xType");
	
	dbConnect($dbA);
	
	if ($xType=="new") {
		$pageTitle = "Add New Snippet";
		$submitButton = "Insert Snippet";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>";
	}
	$xUseexchangerate = "N";
	if ($xType=="edit") {
		$xSnippetID = getFORM("xSnippetID");
		$pageTitle = "Edit Existing Snippet";
		$submitButton = "Update Snippet";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xSnippetID' value='$xSnippetID'>";
		$uResult = $dbA->query("select * from $tableSnippets where snippetID=$xSnippetID");	
		$uRecord = $dbA->fetch($uResult);
	}

	
	$myForm = new formElements;
	
	$languages = $dbA->retrieveAllRecords($tableLanguages,"languageID");
	$dbA->close();
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
<?php $myForm->createForm("detailsForm","general_snippets_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xName",30,30,@getGENERIC("name",$uRecord),"alpha-numeric"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Title</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTitle",80,250,@getGENERIC("title",$uRecord),""); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Content</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createTextArea("xContent",60,10,@getGENERIC("content",$uRecord),""); ?></td>
	</tr>
<?php
	for ($f = 0; $f < count($languages); $f++) {
		$thisLanguage = $languages[$f]["languageID"];
		if ($thisLanguage != 1) {
?>
	<tr>
		<td class="table-list-title" valign="top" colspan="2">Language: <?php print $languages[$f]["name"]; ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Title</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTitle".$thisLanguage,80,250,@getGENERIC("title".$thisLanguage,$uRecord),""); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Content</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createTextArea("xContent".$thisLanguage,60,10,@getGENERIC("content".$thisLanguage,$uRecord),""); ?></td>
	</tr>
<?php
		}
	}
?>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
</HTML>
