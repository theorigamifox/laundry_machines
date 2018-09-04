<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$xType=getFORM("xType");
	
	dbConnect($dbA);

	$pageTitle = "Edit Labels For: $xType";
	$submitButton = "Update Labels";
	$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xType' value='$xType'>";
	$labelList = $dbA->retrieveAllRecordsFromQuery("select * from $tableLabels where type='$xType' order by name");	

	
	$myForm = new formElements;
	
	$languages = $dbA->retrieveAllRecords($tableLanguages,"languageID");
	$defaultLanguage="";
	for ($f = 0; $f < count($languages); $f++) {
		if ($languages[$f]["languageID"] == 1) {
			$defaultLanguage = $languages[$f]["name"];
		}
	}
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
<?php $myForm->createForm("detailsForm","general_labels_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
<tr>
		<td class="table-list-title" valign="top">Label Name</td>
		<td class="table-list-title" valign="top">Label Text</td>
		<td class="table-list-title" valign="top" align="center">Delete</td>
</tr>
<?php
	for ($f = 0; $f < count($labelList); $f++) {
?>
	<tr>
		<td class="table-list-title" valign="top"><?php print $labelList[$f]["name"]; ?></td>
		<td class="table-list-entry1" valign="top">
			<table cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td><font class="normaltext"><?php print $defaultLanguage; ?>:&nbsp;</font></td>
					<td><font class="normaltext"><?php $myForm->createText("xContent".$labelList[$f]["labelID"],60,250,eregi_replace('"',"&quot;",$labelList[$f]["content"]),""); ?></font></td>
				</tr>
				<?php
					for ($g = 0; $g < count($languages); $g++) {
						if ($languages[$g]["languageID"] != 1) {
				?>
				<tr>
					<td><font class="normaltext"><?php print $languages[$g]["name"]; ?>:&nbsp;</font></td>
					<td><font class="normaltext"><?php $myForm->createText("xContent".$labelList[$f]["labelID"]."_".$languages[$g]["languageID"],60,250,eregi_replace('"',"&quot;",$labelList[$f]["content".$languages[$g]["languageID"]]),""); ?></font></td>
				</tr>				
				<?php
						}
					}
				?>
			</table>
		</td>
		<td class="table-list-title" valign="top" align="center"><?php if (count($labelList) > 1) { ?><input type="checkbox" name="xDelete<?php print $labelList[$f]["labelID"]; ?>" value="Y"><?php } ?></td>
	</tr>
<?php
	}
?>
	<tr>
		<td class="table-list-entry0" colspan="3" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
</HTML>
