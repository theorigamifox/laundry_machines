<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
			
	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	
	$commTypeArray[] = array("value"=>"P","text"=>"Percent");
	$commTypeArray[] = array("value"=>"F","text"=>"Flat Rate (".$currArray[0]["code"].")");
	
	
	
	$xType=getFORM("xType");
	if ($xType=="new") {
		$pageTitle = "Add New Affiliate Group";
		$submitButton = "Insert Group";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>";

		$xLoginEnabledYes = "CHECKED";
		$xLoginEnabledNo = "";		
	}
	if ($xType=="edit") {
		$xGroupID = getFORM("xGroupID");
		$pageTitle = "Edit Existing Affiliate Group";
		$submitButton = "Update Group";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xGroupID' value='$xGroupID'>";
		$uResult = $dbA->query("select * from $tableAffiliatesGroups where groupID=$xGroupID");	
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
<?php $myForm->createForm("detailsForm","affiliates_groups_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xName",30,25,@getGENERIC("name",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Base Commission</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCommission",7,15,@getGENERIC("commission",$uRecord),"decimal"); ?>&nbsp;<?php $myForm->createSelect("xCommissionType",@$uRecord["commissionType"],"BOTH",$commTypeArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">2nd Tier Commission (if activated)</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCommission2",7,15,@getGENERIC("commission2",$uRecord),"decimal"); ?>&nbsp;<?php $myForm->createSelect("xCommissionType2",@$uRecord["commissionType2"],"BOTH",$commTypeArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
<?php $myForm->closeForm("xName"); ?>
</BODY>
</HTML>
