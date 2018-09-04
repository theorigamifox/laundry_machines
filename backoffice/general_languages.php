<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript">
	function goDelete(languageID) {
		if (confirm("Are you sure you wish to delete this language?")) {
			self.location.href="general_languages_process.php?xAction=delete&xLanguageID="+languageID+"&<?php print userSessionGET(); ?>";
		}
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Language Settings</td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title">ID</td>
		<td class="table-list-title">Name</td>
		<td class="table-list-title">Visible</td>		
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	$languageSelectArray = null;
	$dbA = new dbAccess();
	$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
	$uResult = $dbA->query("select * from $tableLanguages order by languageID");
	$uCount = $dbA->count($uResult);
	for ($f = 0; $f < $uCount; $f++) {
		$uRecord = $dbA->fetch($uResult);
		$languageSelectArray[] = array("text"=>$uRecord["name"],"value"=>$uRecord["languageID"]);
?>
	<tr>
		<td class="table-list-entry1"><a href="general_languages_detail.php?xType=edit&xLanguageID=<?php print $uRecord["languageID"]; ?>&<?php print userSessionGET(); ?>"><?php print $uRecord["languageID"]; ?></a></td>
		<td class="table-list-entry1"><a href="general_languages_detail.php?xType=edit&xLanguageID=<?php print $uRecord["languageID"]; ?>&<?php print userSessionGET(); ?>"><?php print $uRecord["name"]; ?></a></td>
		<td class="table-list-entry1" align="center"><?php print $uRecord["visible"]; ?></td>
		<td class="table-list-entry1" align="right">
			<button id="buttonEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='general_languages_detail.php?xType=edit&xLanguageID=<?php print $uRecord["languageID"]; ?>&<?php print userSessionGET(); ?>';">Edit</button><?php if ($uRecord["languageID"] != 1) { ?>&nbsp;<button id="buttonEdit<?php print $f; ?>" class="button-delete" onClick="goDelete(<?php print $uRecord["languageID"]; ?>);">Delete</button><?php } ?></td>
	</tr>
<?php
	}
?>
	<tr>
		<td colspan="3" class="table-list-title">Total Languages:</td>
		<td class="table-list-title" align="right"><?php print $uCount; ?></td>
	</tr>
</table>
<p>
<button id="buttonSectionsEdit" class="button-expand" onClick="self.location.href='general_languages_detail.php?xType=new&<?php print userSessionGET(); ?>'">Add New Language</button>
<?php
	$myForm = new formElements;
?>
<p>
<?php $myForm->createForm("detailsForm","general_languages_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type='hidden' name='xAction' value='default'>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Default Language</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xLanguageSelect",retrieveOption("defaultLanguage"),"BOTH",$languageSelectArray); ?></td>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit","Change Default"); ?></td>
	</tr>
</table>
</form>
</center>
</BODY>
</HTML>
<?php	$dbA->close(); ?>
