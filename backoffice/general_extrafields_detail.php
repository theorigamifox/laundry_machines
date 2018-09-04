<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$xType=getFORM("xType");
	
	dbConnect($dbA);
	
	if ($xType=="new") {
		$pageTitle = "Add New Field";
		$submitButton = "Insert Field";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>";

		$xLoginEnabledYes = "CHECKED";
		$xLoginEnabledNo = "";
		$xFieldType = getFORM("xFieldType");		
	}
	$xUseexchangerate = "N";
	if ($xType=="edit") {
		$xExtraFieldID = getFORM("xExtraFieldID");
		$pageTitle = "Edit Existing Field";
		$submitButton = "Update Field";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xExtraFieldID' value='$xExtraFieldID'>";
		$uResult = $dbA->query("select * from $tableExtraFields where extraFieldID=$xExtraFieldID");	
		$uRecord = $dbA->fetch($uResult);
		$xFieldType = $uRecord["type"];
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
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title"><?php print $pageTitle; ?></td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","general_extrafields_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<input type="hidden" name="xFieldType" value="<?php print $xFieldType; ?>">
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Type</td>
		<td class="table-list-entry1" valign="top"><?php print $xFieldType; ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xName",20,20,@getGENERIC("name",$uRecord),"alpha-numeric"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Title</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTitle",50,250,@getGENERIC("title",$uRecord),"general"); ?></td>
	</tr>	
<?php
	if ($xFieldType == "USERINPUT") {
?>
	<tr>
		<td class="table-list-title" valign="top">Size</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xSize",5,10,makeInteger(@getGENERIC("size",$uRecord)),"integer"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Maximum Length</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMaxLength",5,10,makeInteger(@getGENERIC("maxlength",$uRecord)),"integer"); ?></td>
	</tr>
<?php
	}
?>
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
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTitle".$thisLanguage,50,250,@getGENERIC("title".$thisLanguage,$uRecord),"general"); ?></td>
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
