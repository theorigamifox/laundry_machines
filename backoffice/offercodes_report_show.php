<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script>
	function checkFields() {
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Redeemed Offer Codes Report From: <?php print getFORM("xDayF"); ?>/<?php print getFORM("xMonthF"); ?>/<?php print getFORM("xYearF"); ?> to <?php print getFORM("xDayT"); ?>/<?php print getFORM("xMonthT"); ?>/<?php print getFORM("xYearT"); ?></td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title">Code</td>
		<td class="table-list-title">Times Used</td>
		<td class="table-list-title">Total Redeemed</td>
	</tr>
<?php
	dbConnect($dbA);
	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	for ($f = 0; $f < count($currArray); $f++) {
		$currArray[$f]["total"] = 0;
	}
	$dFrom = getFORM("xYearF").getFORM("xMonthF").getFORM("xDayF");
	$dTo = getFORM("xYearT").getFORM("xMonthT").getFORM("xDayT");
	$uResult = $dbA->query("select $tableOrdersHeaders.currencyID,$tableOfferCodesTrans.*,count(*) as nu,sum($tableOfferCodesTrans.amount) as tr from $tableOfferCodesTrans LEFT JOIN $tableOrdersHeaders on $tableOrdersHeaders.orderID = $tableOfferCodesTrans.orderID where date >= '$dFrom' and date <= '$dTo' group by $tableOfferCodesTrans.code,$tableOrdersHeaders.currencyID order by $tableOfferCodesTrans.code,$tableOrdersHeaders.currencyID");
	$uCount = $dbA->count($uResult);
	$grandTotal = 0;
	for ($f = 0; $f < $uCount; $f++) {
		$uRecord = $dbA->fetch($uResult);
		$cCode = "";
		for ($g = 0; $g < count($currArray); $g++) {
			if ($currArray[$g]["currencyID"] == $uRecord["currencyID"]) {
				$currArray[$g]["total"] += $uRecord["tr"];
				$cCode = $currArray[$g]["code"];
			}
		}
?>
	<tr>
		<td class="table-list-entry1" valign="top"><?php print $uRecord["code"]; ?></td>
		<td class="table-list-entry1" align="right" valign="top"><?php print $uRecord["nu"]; ?></td>
		<td class="table-list-entry1" align="right" valign="top"><?php print priceFormat($uRecord["tr"],$uRecord["currencyID"]); ?> <?php print $cCode; ?></td>
	</tr>
<?php
	}
?>
<tr>
		<td class="table-list-title" colspan="2" valign="top">Grand Total</td>
		<td class="table-list-title" align="right" valign="top">
<?php
		for ($g = 0; $g < count($currArray); $g++) {
?>
			<?php print priceFormat($currArray[$g]["total"],$currArray[$g]["currencyID"]); ?> <?php print $currArray[$g]["code"]; ?><br>
<?php
		}
?>
		</td>
	</tr>
</table>
<p>
</form>
</center>
</BODY>
</HTML>
<?php	$dbA->close(); ?>
