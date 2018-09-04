<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/graphSys.php");
	$xGrouping = getFORM("xGrouping");
	$xReport = getFORM("xReport");
	$xYearF = getFORM("xYearF");
	$xMonthF = getFORM("xMonthF");
	$xDayF = getFORM("xDayF");
	$xYearT = getFORM("xYearT");
	$xMonthT = getFORM("xMonthT");
	$xDayT = getFORM("xDayT");
	$xCurrency = getFORM("xCurrency");
	$xStatus = getFORM("xStatus");

	$totalArray = null;
	if (getFORM("xTotalGoods")) {
		$totalArray[] = "goods";
	}
	if (getFORM("xTotalShipping")) {
		$totalArray[] = "shipping";
	}
	if (getFORM("xTotalTax")) {
		$totalArray[] = "tax";
	}
	if (getFORM("xTotalDiscount")) {
		$totalArray[] = "discount";
	}
	if (getFORM("xTotalCert")) {
		$totalArray[] = "cert";
	}
	if (getFORM("xTotalOrder")) {
		$totalArray[] = "order";
	}


	$xStatusText = "";
	switch ($xStatus) {
			case "N":
				$xStatusText = "New Orders";
				break;
			case "P":
				$xStatusText = "Paid Orders";
				break;
			case "F":
				$xStatusText = "Failed Orders";
				break;
			case "D":
				$xStatusText = "Dispatched Orders";
				break;
			case "I":
				$xStatusText = "Part-Dispatched Orders";
				break;
			case "C":
				$xStatusText = "Cancelled Orders";
				break;
	}

	dbConnect($dbA);
	$uResult = $dbA->query("select * from $tableCurrencies where currencyID=$xCurrency");
	$cRec = $dbA->fetch($uResult);

	switch ($xGrouping) {
		case "year":
			$groupShow = "Year";
			$groupBit = "SUBSTRING(date,1,4)";
			$groupBitAbn = "SUBSTRING($tableCarts.date,1,4)";
			$groupOrderBit = "SUBSTRING(datetime,1,4)";
			break;
		case "month":
			$groupShow = "Month";
			$groupBit = "SUBSTRING(date,5,2)";
			$groupBitAbn = "SUBSTRING($tableCarts.date,5,2)";
			$groupOrderBit = "SUBSTRING(datetime,5,2)";
			break;
		case "day":
			$groupShow = "Day";
			$groupBit = "date";
			$groupBitAbn = "$tableCarts.date";
			$groupOrderBit = "SUBSTRING(datetime,1,8)";
			break;
		default:
			$groupShow = "";
			break;
	}
	$whereClause = "(date >= '$xYearF$xMonthF$xDayF' and date <= '$xYearT$xMonthT$xDayT') and currencyID=$xCurrency and status='$xStatus'";
	$whereClauseAbn = "($tableCarts.date >= '$xYearF$xMonthF$xDayF' and $tableCarts.date <= '$xYearT$xMonthT$xDayT')";
	$whereOrderClause = "(datetime >= '$xYearF$xMonthF$xDayF"."000000"."' and datetime <= '$xYearT$xMonthT$xDayT"."999999"."') and currencyID=$xCurrency and status='$xStatus'";
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
</HEAD>
<BODY class="detail-body">
<center>
<table width="100%" cellpadding="2" cellspacing="0" class="table-outline-white">
	<tr>
		<td class="table-white" align="left">Report from <b>&quot;<?php print $xDayF."/".$xMonthF."/".$xYearF; ?>&quot;</b> to <b>&quot;<?php print $xDayT."/".$xMonthT."/".$xYearT; ?>&quot;</b>, Currency: <b><?php print $cRec["code"]; ?></b>, Order Status: <b><?php print $xStatusText; ?></b>, Details Grouped By: <b><?php print $groupShow; ?></b> </td>
		<td class="table-white" align="right" width="106"><button id="buttonPrint" class="button-grey" onClick="self.print();">Print Report</button></td>
	</tr>
</table>
		<p>
		<table width="98%" cellpadding="2" cellspacing="0" class="table-outline-white">
		<tr>
			<td class="table-grey-nocenter" colspan="<?php print count($totalArray)+2; ?>" align="left">Orders Report</td>
		</tr>
		<tr>
			<td class="table-white-nocenter-s" align="left"><?php print $groupShow; ?></td>
			<td class="table-white-nocenter-s" align="right">Total Orders</td>
		<?php
			for ($f = 0; $f < count($totalArray); $f++) {
				switch ($totalArray[$f]) {
					case "goods":
						?><td class="table-white-nocenter-s" align="right">Goods Total</td><?php
						break;
					case "shipping":
						?><td class="table-white-nocenter-s" align="right">Shipping Total</td><?php
						break;
					case "tax":
						?><td class="table-white-nocenter-s" align="right">Tax Total</td><?php
						break;
					case "discount":
						?><td class="table-white-nocenter-s" align="right">Discount Total</td><?php
						break;
					case "cert":
						?><td class="table-white-nocenter-s" align="right">Cert Total</td><?php
						break;
					case "order":
						?><td class="table-white-nocenter-s" align="right">Order Total</td><?php
						break;
				}
			}
		?>
		</tr>
<?php
		$result = $dbA->query("select * from $tableOrdersHeaders where $whereOrderClause");
		$fullTotal = $dbA->count($result);
		$result = $dbA->query("select $tableOrdersHeaders.*,count(*) as total, sum(goodsTotal) as totalGoods, sum(shippingTotal) as totalShipping, sum(taxTotal) as totalTax, sum(discountTotal) as totalDiscount, sum(giftCertTotal) as totalCert , $groupOrderBit as groupBit from $tableOrdersHeaders where $whereOrderClause group by groupBit order by groupBit,total DESC");
		$currGroup = "";
		$count = $dbA->count($result);
		$totalCounter = 0;
		$grandGoods = 0;
		$grandTax = 0;
		$grandShipping = 0;
		$grandDiscount = 0;
		$grandCert = 0;
		if ($count == 0) { return false; }
		for ($f = 0; $f < $count; $f++) {
			$record = $dbA->fetch($result);
			$thisPercent = number_format((100/$fullTotal)*$record["total"],2,".","");
			if ($currGroup != $record["groupBit"]) {
				$currGroup = $record["groupBit"];
				$showGroup = $currGroup;
			} else {
				$showGroup = "";
			}
?>
		<tr>
			<td class="table-white-nocenter-light-s" align="left"><?php print groupText($showGroup); ?></td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $record["total"]; ?></td>
			<?php
				for ($g = 0; $g < count($totalArray); $g++) {
					?><td class="table-white-nocenter-light-s" align="right"><?php
					switch ($totalArray[$g]) {
						case "goods":
							print priceFormat($record["totalGoods"],$record["currencyID"]);
							break;
						case "shipping":
							print priceFormat($record["totalShipping"],$record["currencyID"]);
							break;
						case "tax":
							print priceFormat($record["totalTax"],$record["currencyID"]);
							break;
						case "discount":
							print priceFormat($record["totalDiscount"],$record["currencyID"]);
							break;
						case "cert":
							print priceFormat($record["totalCert"],$record["currencyID"]);
							break;
						case "order":
							print priceFormat(($record["totalGoods"]+$record["totalShipping"]+$record["totalTax"])-($record["totalDiscount"]+$record["totalCert"]),$record["currencyID"]);
							break;
					}
					?></td><?php
				}
			?>
		</tr>
<?php
			$totalCounter = $totalCounter + $record["total"];
			$grandGoods = $grandGoods + $record["totalGoods"];
			$grandTax = $grandTax + $record["totalTax"];
			$grandShipping = $grandShipping + $record["totalShipping"];
			$grandDiscount = $grandDiscount + $record["totalDiscount"];
			$grandCert = $grandCert + $record["totalCert"];
		}
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" colspan="1">Totals</td>
			<td class="table-white-nocenter-s" align="right" colspan="1"><?php print $totalCounter; ?></td>
			<?php
				for ($g = 0; $g < count($totalArray); $g++) {
					?><td class="table-white-nocenter-s" align="right" colspan="1"><?php
					switch ($totalArray[$g]) {
						case "goods":
							print priceFormat($grandGoods,$record["currencyID"]);
							break;
						case "shipping":
							print priceFormat($grandShipping,$record["currencyID"]);
							break;
						case "tax":
							print priceFormat($grandTax,$record["currencyID"]);
							break;
						case "discount":
							print priceFormat($grandDiscount,$record["currencyID"]);
							break;
						case "cert":
							print priceFormat($grandCert,$record["currencyID"]);
							break;
						case "order":
							print priceFormat(($grandGoods+$grandShipping+$grandTax)-($grandDiscount+$grandCert),$record["currencyID"]);
							break;
					}
					?></td><?php
				}
			?>
		</tr>
		</table>
</center>
</BODY>
</HTML>
<?php
	function groupText($thisGroup) {
		$monthArray = array("January","Febuary","March","April","May","June","July","August","September","October","November","December");
		$dayofweekArray = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
		$dayArray = array("1st","2nd","3rd","4th","5th","6th","7th","8th","9th","10th","11th","12th","13th","14th","15th","16th","17th","18th","19th","20th","21st","22nd","23rd","24th","25th","26th","27th","28th","29th","30th","31st");
		$hourArray = array("00:00 - 01:00","01:00 - 02:00","02:00 - 03:00","03:00 - 04:00","04:00 - 05:00","05:00 - 06:00","06:00 - 07:00",
							"07:00 - 08:00","08:00 - 09:00","09:00 - 10:00","10:00 - 11:00","11:00 - 12:00","12:00 - 13:00","13:00 - 14:00",
							"14:00 - 15:00","15:00 - 16:00","16:00 - 17:00","17:00 - 18:00","18:00 - 19:00","19:00 - 20:00","20:00 - 21:00",
							"21:00 - 22:00","22:00 - 23:00","23:00 - 24:00"
						);
		global $xGrouping;
		if ($thisGroup == "") {
			return "&nbsp;";
		} else {
			switch ($xGrouping) {
				case "year":
					$groupShow = $thisGroup;
					break;
				case "month":
					$groupShow = $monthArray[$thisGroup-1];
					break;
				case "day":
					$groupShow = formatDate($thisGroup);
					break;
				case "dayofweek";
					$groupShow = $dayofweekArray[$thisGroup];
					break;
				case "hour";
					$groupShow = $hourArray[$thisGroup];
					break;
				default:
					$groupShow = "";
					break;
			}
		}
		return $groupShow;		
	}
?>