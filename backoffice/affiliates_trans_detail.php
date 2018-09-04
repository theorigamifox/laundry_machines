<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
			
	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	
	$typeArray[] = array("value"=>"C","text"=>"Credit");
	$typeArray[] = array("value"=>"D","text"=>"Debit");
	$typeArray[] = array("value"=>"P","text"=>"Payment");


	$statusArray[] = array("value"=>"0","text"=>"Un-Authorized");
	$statusArray[] = array("value"=>"1","text"=>"Authorized");
	
	
	$xType=getFORM("xType");
	if ($xType=="new") {
		$pageTitle = "Add New Transaction";
		$submitButton = "Insert Transaction";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>".hiddenReturnPOST();

		$xLoginEnabledYes = "CHECKED";
		$xLoginEnabledNo = "";		
	}
	if ($xType=="edit") {
		$xTransID = getFORM("xTransID");
		$pageTitle = "Edit Existing Transaction";
		$submitButton = "Update Transaction";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xTransID' value='$xTransID'>".hiddenReturnPOST();

		$theQuery = "select $tableAffiliatesTrans.*,$tableAffiliates.username,$tableAffiliates.aff_Company from $tableAffiliatesTrans,$tableAffiliates where $tableAffiliates.affiliateID = $tableAffiliatesTrans.affiliateID and transID=$xTransID order by date DESC";
		$uResult = $dbA->query($theQuery);	
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
<?php $myForm->createForm("detailsForm","affiliates_trans_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
<?php
	if ($xType=="edit") {
?>
	<tr>
		<td class="table-list-title" valign="top">Affiliate</td>
		<td class="table-list-entry1" valign="top"><?php print $uRecord["aff_Company"]; ?> (<?php print $uRecord["username"]; ?>)</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Transaction Date</td>
		<td class="table-list-entry1" valign="top"><?php print formatDate($uRecord["datetime"]); ?> (<?php print formatTime(substr($uRecord["datetime"],8,6)); ?>)</td>
	</tr>
<?php
	}
?>
<?php
	if ($xType=="new") {
		$affiliateArray = "";
		$result = $dbA->query("select * from $tableAffiliates order by aff_Company");
		$count = $dbA->count($result);
		for ($f = 0; $f < $count; $f++) {
			$aRecord = $dbA->fetch($result);
			$affiliateArray[] = array("value"=>$aRecord["affiliateID"],"text"=>$aRecord["aff_Company"]." (".$aRecord["username"].")");
		}
?>
		
	<tr>
		<td class="table-list-title" valign="top">Affiliate</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xAffiliateID",@getGENERIC("affiliateID",$uRecord),"BOTH",$affiliateArray); ?></td>
	</tr>
<?php
	}
?>
	<tr>
		<td class="table-list-title" valign="top">Reference</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xReference",40,250,@getGENERIC("reference",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Transaction Type</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xType",@getGENERIC("type",$uRecord),"BOTH",$typeArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Amount</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xAmount",7,15,@getGENERIC("amount",$uRecord),"decimal"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Transaction Status</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xStatus",@getGENERIC("status",$uRecord),"BOTH",$statusArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
<?php $myForm->closeForm("xReference"); ?>
</BODY>
</HTML>
<?
		$dbA->close();
?>