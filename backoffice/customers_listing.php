<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	$xType = getFORM("xType");
	$xOffset = getFORM("xOffset");
	if ($xOffset=="") { $xOffset = 0; }	
	$xDirectional = "";
	$xSectionID = getFORM("xSectionID");
	$xDirectional = "";
	dbConnect($dbA);	
	if ($xType=="ABC") {
		$pageTitle = "ABC Customers Listing";
		$searchAppend = "&xType=ABC".$xDirectional;
		$theQuery = "select * from $tableCustomers order by concat(surname,', ',forename,' ',title)";
	}
	if ($xType=="DATE") {
		$pageTitle = "Date Customers Listing";
		$searchAppend = "&xType=DATE".$xDirectional;
		$theQuery = "select * from $tableCustomers order by date DESC";
	}
	if ($xType=="SEARCH") {
		$xSearchString = getFORM("xSearchString");
		$xAccTypeID = makeInteger(getFORM("xAccTypeID"));
		if ($xAccTypeID != 0) {
			$extraAccSelect = " and accTypeID = $xAccTypeID ";
			$uResult = $dbA->query("select * from $tableCustomersAccTypes where accTypeID=$xAccTypeID");
			if ($dbA->count($uResult) != 0) {
				$uRecord = $dbA->fetch($uResult);
				$extraDesc = " And Account Type = ".$uRecord["name"];
			} else {
				$extraDesc = "";
			}
		} else {
			$extraAccSelect = "";
			$extraDesc = "";
		}
		$pageTitle = "Customer Search: &quot;$xSearchString&quot; $extraDesc";
		$searchAppend = "&xType=SEARCH&xAccTypeID=$xAccTypeID&xSearchString=$xSearchString".$xDirectional;
		$theQuery = "select * from $tableCustomers where (concat(forename,' ',surname) like \"%$xSearchString%\" or email like \"%$xSearchString%\") $extraAccSelect order by concat(surname,', ',forename,' ',title)";
	}
	$accTypeArray = $dbA->retrieveAllRecords($tableCustomersAccTypes,"accTypeID");
	$ordersperpage = retrieveOption("adminCustomersPerPage");
	$result=$dbA->query($theQuery);
	$resultCount = $dbA->count($result);
	$theQuery .= " LIMIT $xOffset,$ordersperpage";
	$upperCount = $xOffset+$ordersperpage;
	$lowerCount = $xOffset+1;
	$middleButtons = "Viewing <b>$lowerCount - ".$upperCount."</b>&nbsp;";
	if ($xOffset-$ordersperpage > -1) {
		$pOffset = $xOffset - $ordersperpage;
		$previousButton = "<input type=\"button\" id=\"buttonPrev\" class=\"button-expand\" onClick=\"self.location.href='customers_listing.php?".userSessionGET().$searchAppend."&xOffset=$pOffset'\" value=\"&lt; PREV\">";
		$previousButton .= "&nbsp;<input type=\"button\" id=\"buttonTop\" class=\"button-expand\" onClick=\"self.location.href='customers_listing.php?".userSessionGET().$searchAppend."&xOffset=0'\" value=\"[TOP]\">";
	} else {
		$previousButton = "";
	}
	if ($xOffset+$ordersperpage < $resultCount) {
		$nOffset = $xOffset + $ordersperpage;
		$nextButton = "<input type=\"button\" id=\"buttonNext\" class=\"button-expand\" onClick=\"self.location.href='customers_listing.php?".userSessionGET().$searchAppend."&xOffset=$nOffset'\" value=\"NEXT &gt;\">";
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
	function goDelete(customerID) {
		if (confirm("Are you sure you wish to delete this customer?")) {
			self.location.href="customers_process.php?xAction=delete&xCustomerID="+customerID+"&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>";
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
		<td colspan="3" class="table-white-no-border" align="right">Total selected customers: <b><?php print $resultCount; ?></b>, <?php print $navButtons; ?>&nbsp;</td>
	</tr>
	<tr>
		<td class="table-list-title">Name</td>
		<td class="table-list-title">Email</td>
		<td class="table-list-title">Date Opened</td>
		<td class="table-list-title">Account Type</td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	$ssResult = $dbA->query($theQuery);
	$ssCount = $dbA->count($ssResult);
	for ($f = 0; $f < $ssCount; $f++) {
		$ssRecord = $dbA->fetch($ssResult);
		$thisAccountName = "";
		for ($g = 0; $g < count($accTypeArray); $g++) {
			if ($ssRecord["accTypeID"] == $accTypeArray[$g]["accTypeID"]) {
				$thisAccountName = $accTypeArray[$g]["name"];
				break;
			}
		}
?>
	<tr>
		<td class="table-list-entry1"><a href="customers_detail.php?xType=edit&xCustomerID=<?php print $ssRecord["customerID"]; ?>&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>"><?php print $ssRecord["surname"].", ".$ssRecord["forename"]." ".$ssRecord["title"]; ?></a></td>
		<td class="table-list-entry1"><a href="mailto:<?php print $ssRecord["email"]; ?>"><?php print $ssRecord["email"]; ?></a></td>
		<td class="table-list-entry1"><?php print formatDate($ssRecord["date"]); ?></td>
		<td class="table-list-entry1"><?php print $thisAccountName; ?></td>
		<td class="table-list-entry1" align="right">
			<input type="button" name="buttonPEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='customers_detail.php?xType=edit&xCustomerID=<?php print $ssRecord["customerID"]; ?>&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>'" value="Edit">&nbsp;
			<input type="button" name="buttonPDelete<?php print $f; ?>" class="button-delete" onClick="javascript:goDelete(<?php print $ssRecord["customerID"]; ?>);" value="Delete">
		</td>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="4" class="table-list-title">Total Number of Customers:</td>
		<td class="table-list-title" align="right"><?php print $ssCount; ?></td>
	</tr>
</table>
</center>
<input type="hidden" name="customerCount" value="<?php print $ssCount; ?>">
</form>
</BODY>
</HTML>
