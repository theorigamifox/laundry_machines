<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	$xDirectional = "";
	dbConnect($dbA);	
	$ordersperpage = 30;
	$theQuery = "select * from $tableAffiliates order by aff_Company";
	$result=$dbA->query($theQuery);
	$resultCount = $dbA->count($result);


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
	
	function setPaid(affiliateID,amount) {
		if (confirm("Are you sure you wish to authorize this payment?")) {
			self.location.href="affiliates_trans_process.php?xAction=payment&xAffiliateID="+affiliateID+"&xAmount="+amount+"&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>";
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
		<td class="detail-title">Affiliate Payments Due</td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-title">Affiliate</td>
		<td class="table-list-title">Address</td>
		<td class="table-list-title">Payable To</td>
		<td class="table-list-title">Amount</td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	$ssResult = $dbA->query($theQuery);
	$ssCount = $dbA->count($ssResult);
	$totalPayments = 0;
	for ($f = 0; $f < $ssCount; $f++) {
		$ssRecord = $dbA->fetch($ssResult);
		$result = $dbA->query("select *,sum(amount) as total from $tableAffiliatesTrans where type='C' and status='1' and affiliateID=".$ssRecord["affiliateID"]." group by type");
		if ($dbA->count($result) == 0) {
			$creditTotal = 0;
		} else {
			$pRecord = $dbA->fetch($result);
			$creditTotal = $pRecord["total"];
		}
		$result = $dbA->query("select *,sum(amount) as total from $tableAffiliatesTrans where type='D' and status='1' and affiliateID=".$ssRecord["affiliateID"]." group by type");
		if ($dbA->count($result) == 0) {
			$debitTotal = 0;
		} else {
			$pRecord = $dbA->fetch($result);
			$debitTotal = $pRecord["total"];
		}
		$result = $dbA->query("select *,sum(amount) as total from $tableAffiliatesTrans where type='P' and status='1' and affiliateID=".$ssRecord["affiliateID"]." group by type");
		if ($dbA->count($result) == 0) {
			$paymentTotal = 0;
		} else {
			$pRecord = $dbA->fetch($result);
			$paymentTotal = $pRecord["total"];
		}
		$totalOutstanding = $creditTotal - $debitTotal - $paymentTotal;
		if ($totalOutstanding > retrieveOption("affiliatesMinimumPayment")) {
			$totalPayments++;
?>
	<tr>
		<td class="table-list-entry1" valign="top"><?php print $ssRecord["aff_Company"]." (".$ssRecord["username"].")"; ?></td>
		<td class="table-list-entry1" valign="top">
			<?php print @$ssRecord["aff_Address1"]; ?>
			<br><?php print @$ssRecord["aff_Address2"]; ?>
			<br><?php print @$ssRecord["aff_Town"]; ?>
			<br><?php print @$ssRecord["aff_County"]; ?>
			<br><?php print @$ssRecord["aff_Postcode"]; ?>
			<br><?php print @$ssRecord["aff_Country"]; ?>		
		</td>
		<td class="table-list-entry1" valign="top"><?php print $ssRecord["aff_ChequeName"]; ?></td>
		<td class="table-list-entry1" valign="top"><?php print priceFormat(@$totalOutstanding,1); ?></td>
		<td class="table-list-entry1" align="right" valign="top">
			<input type="button" name="buttonPDelete<?php print $f; ?>" class="button-cyan" onClick="javascript:setPaid(<?php print $ssRecord["affiliateID"]; ?>,<?php print $totalOutstanding; ?>);" value="Mark Paid">
		</td>
	</tr>
<?php
		}
	}
	$dbA->close();
?>
	<tr>
		<td colspan=4" class="table-list-title">Total Number of Payment To Be Made:</td>
		<td class="table-list-title" align="right"><?php print $totalPayments; ?></td>
	</tr>
</table>
</center>
<input type="hidden" name="customerCount" value="<?php print $ssCount; ?>">
</form>
</BODY>
</HTML>
