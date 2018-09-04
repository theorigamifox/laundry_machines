<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$dbA = new dbAccess();
	$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
	
	$myForm = new formElements;
	
	$compileModeArray = array (
			array("Run templates uncompiled","0"),
			array("Compile if compiled version doesn't exist","1"),
			array("Force compilation each time (useful when developing)","2")
			);
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
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Template Settings</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","general_options_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<input type="hidden" name="xType" value="templates">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Compile Mode</td>
		<td class="table-list-entry1" valign="top">
			<select name="xTemplateCompileMode" class="form-inputbox">
			<?php
				for ($f = 0; $f < count($compileModeArray); $f++) {
					$templateCompileMode = retrieveOption("templateCompileMode");
					if ($compileModeArray[$f][1] == $templateCompileMode) {
						$thisSelected = "SELECTED";
					} else {
						$thisSelected = "";
					}
			?>
				<option value="<?php print $compileModeArray[$f][1]; ?>" <?php print $thisSelected; ?>><?php print $compileModeArray[$f][0]; ?></option>
			<?php
				}
			?>
		</select>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Word Wrap When Editing</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xTemplatesEditWordWrap",retrieveOption("templatesEditWordWrap"),"01"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Allow xTFC Command On Front-End</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xTemplateAllowTFC",retrieveOption("templateAllowTFC"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Allow xRTU Command On Front-End</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xTemplateAllowRTU",retrieveOption("templateAllowRTU"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Convert Line Breaks To &lt;BR&gt;<br>on Snippets</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xSnippetsConvertToBR",retrieveOption("snippetsConvertToBR"),"01"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit","Update Settings"); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>
