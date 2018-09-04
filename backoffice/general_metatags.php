<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$pageTitle = "Edit Meta Tag Details";
	$submitButton = "Update Meta Tag Details";
	$hiddenFields = "<input type='hidden' name='xAction' value='update'>";
	dbConnect($dbA);
	$uResult = $dbA->query("select * from $tableGeneral");	
	$uRecord = $dbA->fetch($uResult);

	$overrideAllMeta = retrieveOption("overrideAllMeta");
	if ($overrideAllMeta == 0) {
		$xOverrideAllMeta = "";
	} else {
		$xOverrideAllMeta = "CHECKED";
	}
	
	$dbA->close();
	
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
<?php $myForm->createForm("detailsForm","general_metatags_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Author Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMetaAuthor",60,250,@getGENERIC("metaAuthor",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMetaDescription",60,250,@getGENERIC("metaDescription",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Keywords</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMetaKeywords",60,250,@getGENERIC("metaKeywords",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top" colspan="2"><input type="checkbox" name="xOverrideAllMeta" value="1" <?php print $xOverrideAllMeta; ?>> Always use these, ignore products / sections Meta Tag settings</td>
	</tr>		
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
<?php $myForm->closeForm("xMetaAuthor"); ?>
</center>
</BODY>
</HTML>
