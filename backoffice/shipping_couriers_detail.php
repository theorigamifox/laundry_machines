<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$xType=getFORM("xType");
	if ($xType=="new") {
		$pageTitle = "Add New Courier";
		$submitButton = "Insert Courier";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>";

		$xLoginEnabledYes = "CHECKED";
		$xLoginEnabledNo = "";		
	}
	$xUseexchangerate = "N";
	if ($xType=="edit") {
		$xCourierID = getFORM("xCourierID");
		$pageTitle = "Edit Existing Courier";
		$submitButton = "Update Courier";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xCourierID' value='$xCourierID'>";
		dbConnect($dbA);
		$uResult = $dbA->query("select * from $tableCouriers where courierID=$xCourierID");	
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
<?php $myForm->createForm("detailsForm","shipping_couriers_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xName",60,250,@getGENERIC("name",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Contact Information</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createTextArea("xContactInfo",50,10,@getGENERIC("contactInfo",$uRecord),""); ?></td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
</HTML>
