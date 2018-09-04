<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$xType=getFORM("xType");
	if ($xType=="new") {
		$pageTitle = "Add New Language";
		$submitButton = "Insert Language";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>";

		$xLoginEnabledYes = "CHECKED";
		$xLoginEnabledNo = "";		
	}
	$xUseexchangerate = "N";
	if ($xType=="edit") {
		$xLanguageID = getFORM("xLanguageID");
		$pageTitle = "Edit Existing Language";
		$submitButton = "Update Language";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xLanguageID' value='$xLanguageID'>";
		$dbA = new dbAccess();
		$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
		$uResult = $dbA->query("select * from $tableLanguages where languageID=$xLanguageID");	
		$uRecord = $dbA->fetch($uResult);
		$dbA->close();
	}
	
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
<?php $myForm->createForm("detailsForm","general_languages_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xName",30,50,@getGENERIC("name",$uRecord),"general"); ?></td>
	</tr>
<?php
	if (@$uRecord["languageID"] != 1) {	
?>
	<tr>
		<td class="table-list-title" valign="top">Visible</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xVisible",@getGENERIC("visible",$uRecord),"YN"); ?></td>
	</tr>				
<?php
	}
?>
	<tr>
		<td class="table-list-title" valign="top">Language Uses Double-Byte Encoding<br>(e.g. Japanese)</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xDoubleByte",@getGENERIC("doubleByte",$uRecord),"YN"); ?></td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
</HTML>
