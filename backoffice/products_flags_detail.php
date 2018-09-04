<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$xType=getFORM("xType");
	if ($xType=="new") {
		$pageTitle = "Add New Product Flag";
		$submitButton = "Insert Flag";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>";

		$xLoginEnabledYes = "CHECKED";
		$xLoginEnabledNo = "";		
	}
	if ($xType=="edit") {
		$xFlagID = getFORM("xFlagID");
		$pageTitle = "Edit Existing Product Flag";
		$submitButton = "Update Flag";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xFlagID' value='$xFlagID'>";
		$dbA = new dbAccess();
		$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
		$uResult = $dbA->query("select * from $tableProductsFlags where flagID=$xFlagID");	
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
<?php $myForm->createForm("detailsForm","products_flags_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xName",30,30,@getGENERIC("name",$uRecord),"alpha-numeric"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Descriptive Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xDescription",50,250,@getGENERIC("description",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
</HTML>
