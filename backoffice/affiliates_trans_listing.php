<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	$xType = getFORM("xType");
	$xOffset = getFORM("xOffset");
	if ($xOffset=="") { $xOffset = 0; }	
	$xDirectional = "";
	dbConnect($dbA);	
	if ($xType=="DATE") {
		$pageTitle = "Transactions By Date";
		$searchAppend = "&xType=DATE".$xDirectional;
		$theQuery = "select $tableAffiliatesTrans.*,$tableAffiliates.username,$tableAffiliates.aff_Company from $tableAffiliatesTrans,$tableAffiliates where $tableAffiliates.affiliateID = $tableAffiliatesTrans.affiliateID order by datetime DESC";
	}
	if ($xType=="NOTAUTH") {
		$pageTitle = "Un-Authorized Transactions";
		$searchAppend = "&xType=NOTAUTH".$xDirectional;
		$theQuery = "select $tableAffiliatesTrans.*,$tableAffiliates.username,$tableAffiliates.aff_Company from $tableAffiliatesTrans,$tableAffiliates where $tableAffiliates.affiliateID = $tableAffiliatesTrans.affiliateID and $tableAffiliatesTrans.status='0' order by date DESC";
	}
	if ($xType=="SEARCH") {
		$xSearchString = getFORM("xSearchString");
		$xGroupID = makeInteger(getFORM("xGroupID"));
		$xStatus = getFORM("xStatus");
		$extraAccSelect = "";
		$extraDesc = "";
		if ($xGroupID != 0) {
			$extraAccSelect = " and groupID = $xGroupID ";
			$uResult = $dbA->query("select * from $tableAffiliatesGroups where groupID=$xGroupID");
			if ($dbA->count($uResult) != 0) {
				$uRecord = $dbA->fetch($uResult);
				$extraDesc = " And Account Type = ".$uRecord["name"];
			} else {
				$extraDesc = "";
			}
		}
		if ($xStatus != "X") {
			$extraAccSelect .= " and status='$xStatus' ";
			$extraDesc .= " And Status = $xStatus";
		}
		$pageTitle = "Customer Search: &quot;$xSearchString&quot; $extraDesc";
		$searchAppend = "&xType=SEARCH&xSearchString=$xSearchString".$xDirectional;
		$theQuery = "select * from $tableAffiliates where (aff_Company like \"%$xSearchString%\" or aff_Email like \"%$xSearchString%\") $extraAccSelect order by aff_Company";
	}
	$ordersperpage = 30;
	$result=$dbA->query($theQuery);
	$resultCount = $dbA->count($result);
	$theQuery .= " LIMIT $xOffset,$ordersperpage";
	$upperCount = $xOffset+$ordersperpage;
	$lowerCount = $xOffset+1;
	$middleButtons = "Viewing <b>$lowerCount - ".$upperCount."</b>&nbsp;";
	if ($xOffset-$ordersperpage > -1) {
		$pOffset = $xOffset - $ordersperpage;
		$previousButton = "<input type=\"button\" id=\"buttonPrev\" class=\"button-expand\" onClick=\"self.location.href='affiliates_trans_listing.php?".userSessionGET().$searchAppend."&xOffset=$pOffset'\" value=\"&lt; PREV\">";
		$previousButton .= "&nbsp;<input type=\"button\" id=\"buttonTop\" class=\"button-expand\" onClick=\"self.location.href='affiliates_trans_listing.php?".userSessionGET().$searchAppend."&xOffset=0'\" value=\"[TOP]\">";
	} else {
		$previousButton = "";
	}
	if ($xOffset+$ordersperpage < $resultCount) {
		$nOffset = $xOffset + $ordersperpage;
		$nextButton = "<input type=\"button\" id=\"buttonNext\" class=\"button-expand\" onClick=\"self.location.href='affiliates_trans_listing.php?".userSessionGET().$searchAppend."&xOffset=$nOffset'\" value=\"NEXT &gt;\">";
	} else {
		$nextButton = "";
	}
	$searchAppend .= "&xOffset=$xOffset";
	if ($previousButton=="" && $nextButton=="") {
		$navButtons = $middleButtons;
	}
	if ($previousButton=="" && $nextButton!="") {
		$navButtons = $middleButtons.$nextButton;
	}
	if ($previousButton!="" && $nextButton=="") {
		$navButtons = $middleButtons.$previousButton;
	}
	if ($previousButton!="" && $nextButton!="") {
		$navButtons = $middleButtons.$previousButton."&nbsp;".$nextButton;
	}	

	$myForm = new formElements;
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript">
	function goDelete(transID) {
		if (confirm("Are you sure you wish to delete this transaction?")) {
			self.location.href="affiliates_trans_process.php?xAction=delete&xTransID="+transID+"&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>";
		}
	}
	
	function setAuthorized(transID) {
		if (confirm("Are you sure you wish to authorize this transaction?")) {
			self.location.href="affiliates_trans_process.php?xAction=auth&xTransID="+transID+"&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>";
		}
	}
</script>
</HEAD>
<BODY class="detail-body">
<form name="selectCustomers">
<input type="hidden" name="selectedCustomers" value="">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title"><?php print $pageTitle; ?></td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td colspan="2" class="table-white-no-border" align="left">&nbsp;</td>
		<td colspan="5" class="table-white-no-border" align="right">Total selected transactions: <b><?php print $resultCount; ?></b>, <?php print $navButtons; ?>&nbsp;</td>
	</tr>
	<tr>
		<td class="table-list-title">Date</td>
		<td class="table-list-title">Affiliate</td>
		<td class="table-list-title">Reference</td>
		<td class="table-list-title">Type</td>
		<td class="table-list-title">Amount</td>
		<td class="table-list-title">Status</td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	$ssResult = $dbA->query($theQuery);
	$ssCount = $dbA->count($ssResult);
	for ($f = 0; $f < $ssCount; $f++) {
		$ssRecord = $dbA->fetch($ssResult);
		switch ($ssRecord["status"]) {
			case "0":
				$thisStatus = "UnAuth";
				$authButton = "<input type=\"button\" name=\"buttonAuthorized$f\" class=\"button-cyan\" onClick=\"setAuthorized(".$ssRecord["transID"].");\" value=\"Authorize\">&nbsp;";
				break;
			case "1":
				$authButton = "";
				$thisStatus = "Auth";
				break;
		}
		switch ($ssRecord["type"]) {
			case "C":
				$thisType = "Credit";
				break;
			case "D":
				$thisType = "Debit";
				break;
			case "P":
				$thisType = "Payment";
				break;
		}		
?>
	<tr>
		<td class="table-list-entry1"><?php print formatDate($ssRecord["datetime"]); ?> (<?php print formatTime(substr($ssRecord["datetime"],8,6)); ?>)</td>
		<td class="table-list-entry1"><?php print $ssRecord["aff_Company"]." (".$ssRecord["username"].")"; ?></td>
		<td class="table-list-entry1"><?php print $ssRecord["reference"]; ?></td>
		<td class="table-list-entry1"><?php print $thisType; ?></td>
		<td class="table-list-entry1"><?php print priceFormat($ssRecord["amount"],1); ?></td>
		<td class="table-list-entry1"><?php print $thisStatus; ?></td>
		<td class="table-list-entry1" align="right">
			<?php print $authButton; ?>
			<input type="button" name="buttonPEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='affiliates_trans_detail.php?xType=edit&xTransID=<?php print $ssRecord["transID"]; ?>&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>'" value="Edit">&nbsp;
			<input type="button" name="buttonPDelete<?php print $f; ?>" class="button-delete" onClick="javascript:goDelete(<?php print $ssRecord["transID"]; ?>);" value="Delete">
		</td>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="6" class="table-list-title">Total Number of Transactions:</td>
		<td class="table-list-title" align="right"><?php print $ssCount; ?></td>
	</tr>
</table>
</center>
<input type="hidden" name="customerCount" value="<?php print $ssCount; ?>">
</form>
</BODY>
</HTML>
