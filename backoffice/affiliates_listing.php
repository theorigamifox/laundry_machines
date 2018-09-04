<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	$xType = getFORM("xType");
	$xOffset = getFORM("xOffset");
	if ($xOffset=="") { $xOffset = 0; }	
	$xDirectional = "";
	dbConnect($dbA);	
	if ($xType=="ABC") {
		$pageTitle = "ABC Affiliates Listing";
		$searchAppend = "&xType=ABC".$xDirectional;
		$theQuery = "select * from $tableAffiliates order by aff_Company";
	}
	if ($xType=="DATE") {
		$pageTitle = "Date Affiliates Listing";
		$searchAppend = "&xType=DATE".$xDirectional;
		$theQuery = "select * from $tableAffiliates order by date DESC";
	}
	if ($xType=="NEW") {
		$pageTitle = "New Affiliates Listing";
		$searchAppend = "&xType=NEW".$xDirectional;
		$theQuery = "select * from $tableAffiliates where status='N' order by date DESC";
	}
	if ($xType=="2NDTIER") {
		$xAffiliateID = getFORM("xAffiliateID");
		$pResult = $dbA->query("select * from $tableAffiliates where affiliateID=$xAffiliateID");
		if ($dbA->count($pResult) > 0) {
			$pRecord = $dbA->fetch($pResult);
		}
		$pageTitle = "2nd Tier Affiliates For Affiliate: ".@$pRecord["username"];
		$searchAppend = "&xType=2NDTIER&xAffiliateID=$xAffiliateID".$xDirectional;
		$theQuery = "select * from $tableAffiliates where parentID=$xAffiliateID order by date DESC";
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
		$pageTitle = "Affiliate Search: &quot;$xSearchString&quot; $extraDesc";
		$searchAppend = "&xType=SEARCH&xSearchString=$xSearchString".$xDirectional;
		$theQuery = "select * from $tableAffiliates where (aff_Company like \"%$xSearchString%\" or aff_Email like \"%$xSearchString%\" or username like \"%$xSearchString%\") $extraAccSelect order by aff_Company";
	}
	$affGroupArray = $dbA->retrieveAllRecords($tableAffiliatesGroups,"groupID");
	$ordersperpage = retrieveOption("adminCustomersPerPage");
	$result=$dbA->query($theQuery);
	$resultCount = $dbA->count($result);
	$theQuery .= " LIMIT $xOffset,$ordersperpage";
	$upperCount = $xOffset+$ordersperpage;
	$lowerCount = $xOffset+1;
	$middleButtons = "Viewing <b>$lowerCount - ".$upperCount."</b>&nbsp;";
	if ($xOffset-$ordersperpage > -1) {
		$pOffset = $xOffset - $ordersperpage;
		$previousButton = "<input type=\"button\" id=\"buttonPrev\" class=\"button-expand\" onClick=\"self.location.href='affiliates_listing.php?".userSessionGET().$searchAppend."&xOffset=$pOffset'\" value=\"&lt; PREV\">";
		$previousButton .= "&nbsp;<input type=\"button\" id=\"buttonTop\" class=\"button-expand\" onClick=\"self.location.href='affiliates_listing.php?".userSessionGET().$searchAppend."&xOffset=0'\" value=\"[TOP]\">";
	} else {
		$previousButton = "";
	}
	if ($xOffset+$ordersperpage < $resultCount) {
		$nOffset = $xOffset + $ordersperpage;
		$nextButton = "<input type=\"button\" id=\"buttonNext\" class=\"button-expand\" onClick=\"self.location.href='affiliates_listing.php?".userSessionGET().$searchAppend."&xOffset=$nOffset'\" value=\"NEXT &gt;\">";
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
	function goDelete(affiliateID) {
		if (confirm("Are you sure you wish to delete this affiliate?")) {
			self.location.href="affiliates_process.php?xAction=delete&xAffiliateID="+affiliateID+"&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>";
		}
	}
	
	function setLive(affiliateID) {
		if (confirm("Are you sure you wish to accept this affiliate")) {
			self.location.href="affiliates_process.php?xAction=accept&xAffiliateID="+affiliateID+"&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>";
		}
	}
	
	function setDecline(affiliateID) {
		if (confirm("Are you sure you wish to decline this affiliate")) {
			self.location.href="affiliates_process.php?xAction=decline&xAffiliateID="+affiliateID+"&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>";
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
		<td colspan="5" class="table-white-no-border" align="right">Total selected affiliates: <b><?php print $resultCount; ?></b>, <?php print $navButtons; ?>&nbsp;</td>
	</tr>
	<tr>
		<td class="table-list-title">Company</td>
		<td class="table-list-title">Username</td>
		<td class="table-list-title">Email</td>
		<td class="table-list-title">Date Opened</td>
		<td class="table-list-title">Group</td>
		<td class="table-list-title">Status</td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	$ssResult = $dbA->query($theQuery);
	$ssCount = $dbA->count($ssResult);
	for ($f = 0; $f < $ssCount; $f++) {
		$ssRecord = $dbA->fetch($ssResult);
		$thisGroupName = "";
		for ($g = 0; $g < count($affGroupArray); $g++) {
			if (@$ssRecord["groupID"] == $affGroupArray[$g]["groupID"]) {
				$thisGroupName = $affGroupArray[$g]["name"];
				break;
			}
		}
		$authButton = "";
		switch ($ssRecord["status"]) {
			case "L":
				$thisStatus = "Live";
				break;
			case "H":
				$thisStatus = "On Hold";
				break;
			case "D":
				$thisStatus = "Declined";
				break;
			default:
				$thisStatus = "New";
				$authButton = "<input type=\"button\" name=\"buttonLive$f\" class=\"button-cyan\" onClick=\"setLive(".$ssRecord["affiliateID"].");\" value=\"Accept\">&nbsp;";
				$authButton .= "<input type=\"button\" name=\"buttonDecline$f\" class=\"button-cyan\" onClick=\"setDecline(".$ssRecord["affiliateID"].");\" value=\"Decline\">&nbsp;";
				break;
		}
?>
	<tr>
		<td class="table-list-entry1"><a href="affiliates_detail.php?xType=edit&xAffiliateID=<?php print $ssRecord["affiliateID"]; ?>&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>"><?php print $ssRecord["aff_Company"]; ?></a></td>
		<td class="table-list-entry1"><?php print $ssRecord["username"]; ?></td>
		<td class="table-list-entry1"><a href="mailto:<?php print $ssRecord["aff_Email"]; ?>"><?php print $ssRecord["aff_Email"]; ?></a></td>
		<td class="table-list-entry1"><?php print formatDate($ssRecord["date"]); ?></td>
		<td class="table-list-entry1"><?php print $thisGroupName; ?></td>
		<td class="table-list-entry1"><?php print $thisStatus; ?></td>
		<td class="table-list-entry1" align="right">
			<?php print @$authButton; ?>
			<input type="button" name="buttonPEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='affiliates_detail.php?xType=edit&xAffiliateID=<?php print $ssRecord["affiliateID"]; ?>&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>'" value="Edit">&nbsp;
			<input type="button" name="buttonPDelete<?php print $f; ?>" class="button-delete" onClick="javascript:goDelete(<?php print $ssRecord["affiliateID"]; ?>);" value="Delete">
		</td>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="6" class="table-list-title">Total Number of Affiliates:</td>
		<td class="table-list-title" align="right"><?php print $ssCount; ?></td>
	</tr>
</table>
</center>
<input type="hidden" name="customerCount" value="<?php print $ssCount; ?>">
</form>
</BODY>
</HTML>
