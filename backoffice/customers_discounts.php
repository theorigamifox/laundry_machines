<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	dbConnect($dbA);	
	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	$accTypeArray = $dbA->retrieveAllRecords($tableCustomersAccTypes,"accTypeID");

?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript">
	function goDelete(discountID) {
		if (confirm("Are you sure you wish to delete this customer discount?")) {
			self.location.href="customers_discounts_process.php?xAction=delete&xDiscountID="+discountID+"&<?php print userSessionGET(); ?>";
		}
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Special Discounts (Shown On Cart)</td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title">Name</td>
		<td class="table-list-title">Trigger</td>
		<td class="table-list-title">Accounts</td>
		<td class="table-list-title">Discount</td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	$uResult = $dbA->query("select * from $tableDiscounts order by type, compvalue1 DESC");
	$uCount = $dbA->count($uResult);
	$mainType = "";
	for ($f = 0; $f < $uCount; $f++) {
		$uRecord = $dbA->fetch($uResult);
		$accountList = $uRecord["accTypes"];
		$accSplit = split(";",$accountList);
		$accList = "";
		for ($g = 0; $g < count($accSplit); $g++) {
			if ($accSplit[$g] == "0") {
				$accList .= "All<BR>";
			}
			for ($h = 0; $h < count($accTypeArray); $h++) {
				if ($accSplit[$g] == $accTypeArray[$h]["accTypeID"]) {
					$accList .= $accTypeArray[$h]["name"]."<BR>";
				}
			}
		}

		$compPrices = "";
		if ($uRecord["qty"] > 0) {
			$compPrices = "Quantity Total &gt; ".$uRecord["qty"];
		} else {
			for ($g = 0; $g < count($currArray); $g++) {
				$compPrices .= calculatePriceFormat($uRecord["compvalue1"],$uRecord["compvalue".$currArray[$g]["currencyID"]],$currArray[$g]["currencyID"]). " ";
			}	
			$compPrices = "Goods Total &gt; ".$compPrices;
		}
		switch ($uRecord["type"]) {
			case "G":
				$thisType = "Goods Total Discount";
				break;
			case "S":
				$thisType = "Shipping Discount";
				break;
		}
		if ($thisType != $mainType) {
			$mainType = $thisType;
?>
	<tr>
		<td class="table-list-entry2" colspan="5">Discount Type: <?php print $mainType; ?></td>
	</tr>
<?php
		}		
?>			
	<tr>
		<td class="table-list-entry1"><a href="customers_discounts_detail.php?xType=edit&xDiscountID=<?php print $uRecord["discountID"]; ?>&<?php print userSessionGET(); ?>"><?php print $uRecord["name"]; ?></a></td>
		<td class="table-list-entry1"><?php print $compPrices; ?></td>
		<td class="table-list-entry1"><?php print $accList; ?></td>
		<td class="table-list-entry1" align="right"><?php print $uRecord["percent"]; ?>%</td>
		<td class="table-list-entry1" align="right">
			<button id="buttonEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='customers_discounts_detail.php?xType=edit&xDiscountID=<?php print $uRecord["discountID"]; ?>&<?php print userSessionGET(); ?>';">Edit</button>&nbsp;<button id="buttonDelete<?php print $f; ?>" class="button-delete" onClick="goDelete(<?php print $uRecord["discountID"]; ?>);">Delete</button></td>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="4" class="table-list-title">Total Special Discounts:</td>
		<td class="table-list-title" align="right"><?php print $uCount; ?></td>
	</tr>
</table>
<p>
<button id="buttonGTD" class="button-expand" onClick="self.location.href='customers_discounts_detail.php?xType=new&<?php print userSessionGET(); ?>'">Add New Discount</button>
</center>
</BODY>
</HTML>
