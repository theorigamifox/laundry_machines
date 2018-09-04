<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	$xType = getFORM("xType");
	$xOffset = getFORM("xOffset");
	if ($xOffset=="") { $xOffset = 0; }	
	$xDirectional = "";
	$xSectionID = getFORM("xSectionID");
	$xDirectional = "";
	$xStatus = getFORM("xStatus");
	$xSearchString = getFORM("xSearchString");
	$tDate = date("Ymd");
	switch($xStatus) {
		case "A":
			$pageTitle = " Activated Gift Certificates";
			$searchAppend = "&xStatus=$xStatus&xSearchString=$xSearchString".$xDirectional;
			$searchBit = "(status='A') and";
			break;
		case "N":
			$pageTitle = "Not Activated Gift Certificates";
			$searchAppend = "&xStatus=$xStatus&xSearchString=$xSearchString".$xDirectional;
			$searchBit = "(status='N') and";
			break;
		case "E":
			$pageTitle = "Expired Gift Certificates";
			$searchAppend = "&xStatus=$xStatus&xSearchString=$xSearchString".$xDirectional;
			$searchBit = "(expiryDate < '$tDate' and expiryDate != 'N') and";
			break;
		case "X":
			$pageTitle = "All Gift Certificates";
			$searchAppend = "&xStatus=$xStatus&xSearchString=$xSearchString".$xDirectional;
			$searchBit = "";
			break;			
	}
	if ($xSearchString != "") {
		$pageTitle .= " (Search Filter: $xSearchString)";
	}
	$theQuery = "select $tableGiftCertificates.*,sum($tableGiftCertificatesTrans.amount) as used from $tableGiftCertificates left join $tableGiftCertificatesTrans on $tableGiftCertificatesTrans.certSerial = $tableGiftCertificates.certSerial where $searchBit (fromname like \"%$xSearchString%\" or toname like \"%$xSearchString%\" or emailaddress like \"%$xSearchString%\" or $tableGiftCertificates.certSerial like \"%$xSearchString%\") group by $tableGiftCertificates.certSerial order by certID desc";
	dbConnect($dbA);
	$ordersperpage = 20;
	$result=$dbA->query($theQuery);
	$resultCount = $dbA->count($result);
	$theQuery .= " LIMIT $xOffset,$ordersperpage";
	$upperCount = $xOffset+$ordersperpage;
	$lowerCount = $xOffset+1;
	$middleButtons = "Viewing <b>$lowerCount - ".$upperCount."</b>&nbsp;";
	if ($xOffset-$ordersperpage > -1) {
		$pOffset = $xOffset - $ordersperpage;
		$previousButton = "<input type=\"button\" id=\"buttonPrev\" class=\"button-expand\" onClick=\"self.location.href='giftcerts_listing.php?".userSessionGET().$searchAppend."&xOffset=$pOffset'\" value=\"&lt; PREV\">";
		$previousButton .= "&nbsp;<input type=\"button\" id=\"buttonTop\" class=\"button-expand\" onClick=\"self.location.href='giftcerts_listing.php?".userSessionGET().$searchAppend."&xOffset=0'\" value=\"[TOP]\">";
	} else {
		$previousButton = "";
	}
	if ($xOffset+$ordersperpage < $resultCount) {
		$nOffset = $xOffset + $ordersperpage;
		$nextButton = "<input type=\"button\" id=\"buttonNext\" class=\"button-expand\" onClick=\"self.location.href='giftcerts_listing.php?".userSessionGET().$searchAppend."&xOffset=$nOffset'\" value=\"NEXT &gt;\">";
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
	function goDelete(certSerial) {
		if (confirm("Are you sure you wish to delete this gift certificate?")) {
			self.location.href="giftcerts_process.php?xAction=delete&xCertSerial="+certSerial+"&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>";
		}
	}
	
	function goShow(certSerial) {
		window.open("orders_printcert.php?xCertSerial="+certSerial+"&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>");
	}
	
	function goEmail(certSerial) {
		if (confirm("Are you sure you wish to email this gift certificate?")) {
			self.location.href="giftcerts_process.php?xAction=email&xCertSerial="+certSerial+"&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>";
		}
	}
</script>
</HEAD>
<BODY class="detail-body">
<form name="selectGiftCerts">
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
		<td colspan="4" class="table-white-no-border" align="left">&nbsp;</td>
		<td colspan="5" class="table-white-no-border" align="right">Total selected gift certificates: <b><?php print $resultCount; ?></b>, <?php print $navButtons; ?>&nbsp;</td>
	</tr>
	<tr>
		<td class="table-list-title">Certificate Serial</td>
		<td class="table-list-title">From</td>
		<td class="table-list-title">To</td>
		<td class="table-list-title">Type</td>
		<td class="table-list-title">Expiry Date</td>
		<td class="table-list-title" align="right">Value</td>
		<td class="table-list-title" align="right">Value Left</td>
		<td class="table-list-title">Status</td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	$ssResult = $dbA->query($theQuery);
	$ssCount = $dbA->count($ssResult);
	for ($f = 0; $f < $ssCount; $f++) {
		$ssRecord = $dbA->fetch($ssResult);
		$thisAccountName = "";
		switch ($ssRecord["status"]) {
			case "A":
				$theStatus = "Activated";
				break;
			case "N":
				$theStatus = "Not Activated";
				break;
		}
		if ($ssRecord["expiryDate"] < $tDate && $ssRecord["expiryDate"] != "N") {
			$theStatus = "Expired";
		}
		if ($ssRecord["expiryDate"] == "N") {
			$theExpiry = "n/a";
		} else {
			$theExpiry = formatDate($ssRecord["expiryDate"]);
		}
		switch ($ssRecord["type"]) {
			case "E":
				$theType = "Email";
				break;
			case "P":
				$theType = "Postal";
				break;
		}
?>
	<tr>
		<td class="table-list-entry1"><?php print $ssRecord["certSerial"]; ?></a></td>
		<td class="table-list-entry1"><?php print $ssRecord["fromname"]; ?></td>
		<td class="table-list-entry1"><?php print $ssRecord["toname"]; ?></td>
		<td class="table-list-entry1"><?php print @$theType; ?></td>
		<td class="table-list-entry1"><?php print @$theExpiry; ?></td>
		<td class="table-list-entry1" align="right"><?php print priceFormat($ssRecord["certValue"],$ssRecord["currencyID"]); ?></td>
		<td class="table-list-entry1" align="right"><?php print priceFormat($ssRecord["certValue"]-$ssRecord["used"],$ssRecord["currencyID"]); ?></td>
		<td class="table-list-entry1"><?php print @$theStatus; ?></td>
		<td class="table-list-entry1" align="right">
			<?php if ($ssRecord["type"]=="E") { ?><input type="button" name="buttonPEmail<?php print $f; ?>" class="button-cyan" onClick="javascript:goEmail('<?php print $ssRecord["certSerial"]; ?>');" value="Email">&nbsp;<?php } ?>
			<?php if ($ssRecord["type"]=="P") { ?><input type="button" name="buttonPPostal<?php print $f; ?>" class="button-cyan" onClick="javascript:goShow('<?php print $ssRecord["certSerial"]; ?>');" value="Show">&nbsp;<?php } ?>
			<input type="button" name="buttonPEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='giftcerts_detail.php?xType=edit&xCertSerial=<?php print $ssRecord["certSerial"]; ?>&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>'" value="Edit">&nbsp;
			<input type="button" name="buttonPDelete<?php print $f; ?>" class="button-delete" onClick="javascript:goDelete('<?php print $ssRecord["certSerial"]; ?>');" value="Delete">
		</td>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="8" class="table-list-title">Total Number of Gift Certificates:</td>
		<td class="table-list-title" align="right"><?php print $ssCount; ?></td>
	</tr>
</table>
</center>
</form>
</BODY>
</HTML>
