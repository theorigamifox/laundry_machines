<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	dbConnect($dbA);	
	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	$xShippingID = getFORM("xShippingID");
	$typeResult = $dbA->query("select * from $tableShippingTypes where shippingID=$xShippingID");
	$typeRecord = $dbA->fetch($typeResult);
	$zoneArray = $dbA->retrieveAllRecords($tableZones,"name");
	switch ($typeRecord["calcType"]) {
		case "W":
			$calcType = "Weight";
			break;
		case "Q":
			$calcType = "Qty";
			break;
		case "T":
			$calcType = "Total";
			break;
	}
	switch ($typeRecord["fmType"]) {
		case "F":
			$fmType = "Flat Rate";
			break;
		case "M":
			$fmType = "Multiplication";
			break;
	}
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript">
	function goDelete(rateID) {
		if (confirm("Are you sure you wish to delete this shipping rate?")) {
			self.location.href="shipping_rates_process.php?xAction=delete&xShippingID=<?php print $xShippingID; ?>&xRateID="+rateID+"&<?php print userSessionGET(); ?>";
		}
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Shipping Rates For: <?php print $typeRecord["name"]; ?></td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title">From <?php print $calcType; ?></td>
		<td class="table-list-title">To <?php print $calcType; ?></td>
		<td class="table-list-title">Pricing (<?php print $fmType; ?>)</td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	$uResult = $dbA->query("select * from $tableShippingRates where shippingID=$xShippingID order by zoneID,sfrom,sto");
	$uCount = $dbA->count($uResult);
	$zoneName = "";
	for ($f = 0; $f < $uCount; $f++) {
		$uRecord = $dbA->fetch($uResult);
		$shippingPrices = "";
		for ($g = 0; $g < count($currArray); $g++) {
			$shippingPrices .= calculatePriceFormatDecs($uRecord["price1"],$uRecord["price".$currArray[$g]["currencyID"]],$currArray[$g]["currencyID"],6). " ";
		}
		$thisZoneName = "";
		for ($g = 0; $g < count($zoneArray); $g++) {
			if ($uRecord["zoneID"] == $zoneArray[$g]["zoneID"]) {
				$thisZoneName = $zoneArray[$g]["name"];
			}
		}
		if ($thisZoneName != $zoneName) {
			$zoneName = $thisZoneName;
?>
	<tr>
		<td class="table-list-entry2" colspan="4">Zone: <?php print $zoneName; ?></td>
	</tr>
<?php
		}		
?>
	<tr>
		<?php
			if ($uRecord["sfrom"] != -1) {
		?>
		<td class="table-list-entry1"><?php print $uRecord["sfrom"]; ?></td>
		<td class="table-list-entry1"><?php print $uRecord["sto"]; ?></td>
		<?php
			} else {
		?>
		<td class="table-list-entry1" colspan="2"><center>All / Others</center></td>
		<?php
			}
		?>
		<td class="table-list-entry1"><?php print $shippingPrices; ?></td>
		<td class="table-list-entry1" align="right">
			<button id="buttonEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='shipping_rates_detail.php?xType=edit&xShippingID=<?php print $xShippingID; ?>&xRateID=<?php print $uRecord["rateID"]; ?>&<?php print userSessionGET(); ?>';">Edit</button>&nbsp;<button id="buttonDelete<?php print $f; ?>" class="button-delete" onClick="goDelete(<?php print $uRecord["rateID"]; ?>);">Delete</button></td>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="3" class="table-list-title">Total Rates:</td>
		<td class="table-list-title" align="right"><?php print $uCount; ?></td>
	</tr>
</table>
<p>
<button id="buttonSectionsEdit" class="button-expand" onClick="self.location.href='shipping_types.php?<?php print userSessionGET(); ?>'">&lt; Back To Types</button>
&nbsp;<button id="buttonSectionsEdit" class="button-expand" onClick="self.location.href='shipping_rates_detail.php?xShippingID=<?php print $xShippingID; ?>&xType=new&<?php print userSessionGET(); ?>'">Add New Rate</button>
</center>
</BODY>
</HTML>
