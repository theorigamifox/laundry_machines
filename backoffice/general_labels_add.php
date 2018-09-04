<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$xType=getFORM("xType");
	
	dbConnect($dbA);

	$pageTitle = "Add Label For: $xType";
	$submitButton = "Insert Label";
	$hiddenFields = "<input type='hidden' name='xAction' value='insert'><input type='hidden' name='xType' value='$xType'>";
	
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
</tr>
	<tr>
		<td class="table-list-title" valign="top"><?php $myForm->createText("xName",30,40,"","alpha-numeric"); ?></td>
		<td class="table-list-entry1" valign="top">
			<table cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td><font class="normaltext"><?php print $defaultLanguage; ?>:&nbsp;</font></td>
					<td><font class="normaltext"><?php $myForm->createText("xContent",60,250,"",""); ?></font></td>
				</tr>
				<?php
					for ($g = 0; $g < count($languages); $g++) {
						if ($languages[$g]["languageID"] != 1) {
				?>
				<tr>
					<td><font class="normaltext"><?php print $languages[$g]["name"]; ?>:&nbsp;</font></td>
					<td><font class="normaltext"><?php $myForm->createText("xContent"."_".$languages[$g]["languageID"],60,250,"",""); ?></font></td>
				</tr>				
				<?php
						}
					}
				?>
			</table>
		</td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
</HTML>
