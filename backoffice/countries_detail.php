<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	
	$xType=getFORM("xType");
	if ($xType=="new") {
		$pageTitle = "Add New Country";
		$submitButton = "Insert Country";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>".hiddenReturnPOST();	
	}
	if ($xType=="edit") {
		$xCountryID = getFORM("xCountryID");
		$pageTitle = "Edit Existing Country";
		$submitButton = "Update Country";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xCountryID' value='$xCountryID'>".hiddenReturnPOST();
		$uResult = $dbA->query("select * from $tableCountries where countryID=$xCountryID");	
		$uRecord = $dbA->fetch($uResult);
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
<?php $myForm->createForm("detailsForm","countries_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xName",50,100,@getGENERIC("name",$uRecord),"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">ISO Code</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xIsocode",5,5,@getGENERIC("isocode",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">ISO Number</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xIsonumber",5,5,@getGENERIC("isonumber",$uRecord),"integer"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Visible</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xVisible",@getGENERIC("visible",$uRecord),"YN"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
<?php $myForm->closeForm("xEmailaddress"); ?>
</HTML>
<?php
	$dbA->close();
?>
