<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$xType=getFORM("xType");
	
	dbConnect($dbA);
	
	if ($xType=="new") {
		$pageTitle = "Add New News Items";
		$submitButton = "Insert News Item";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>";
	}
	if ($xType=="edit") {
		$xNewsID = getFORM("xNewsID");
		$pageTitle = "Edit Existing News Item";
		$submitButton = "Update News Item";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xNewsID' value='$xNewsID'>";
		$uResult = $dbA->query("select * from $tableNews where newsID=$xNewsID");	
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
<?php $myForm->createForm("detailsForm","general_news_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Title</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTitle",80,250,eregi_replace('"',"&quot;",@getGENERIC("title",$uRecord)),""); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Content</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createTextArea("xContent",60,15,@getGENERIC("content",$uRecord),""); ?></td>
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
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTitle".$thisLanguage,80,250,eregi_replace('"',"&quot;",@getGENERIC("title".$thisLanguage,$uRecord)),""); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Content</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createTextArea("xContent".$thisLanguage,60,15,@getGENERIC("content".$thisLanguage,$uRecord),""); ?></td>
	</tr>
<?php
		}
	}
?>	
<?php
	if ($xType == "edit") {
?>
	<tr>
		<td class="table-list-title" valign="top" colspan="2"><input type="checkbox" name="xResetDate" value="Y"> Reset Date Of This News Item To Now</td>
	</tr>
<?php
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
