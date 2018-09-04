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
	$xLimit = getFORM("xLimit");
	$xLimitText = getFORM("xLimitText");
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
	$whereClause = "(date >= '$xYearF$xMonthF$xDayF' and date <= '$xYearT$xMonthT$xDayT')";
	$whereClauseAbn = "($tableCarts.date >= '$xYearF$xMonthF$xDayF' and $tableCarts.date <= '$xYearT$xMonthT$xDayT')";
	$whereOrderClause = "(datetime >= '$xYearF$xMonthF$xDayF"."000000"."' and datetime <= '$xYearT$xMonthT$xDayT"."999999"."')";
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
		<td class="table-white" align="left">Report from <b>&quot;<?php print $xDayF."/".$xMonthF."/".$xYearF; ?>&quot;</b> to <b>&quot;<?php print $xDayT."/".$xMonthT."/".$xYearT; ?>&quot;</b>, Details Grouped By: <b><?php print $groupShow; ?></b>, Result Limit: <b><?php print $xLimitText; ?></b> </td>
		<td class="table-white" align="right" width="106"><button id="buttonPrint" class="button-grey" onClick="self.print();">Print Report</button></td>
	</tr>
</table>
<?php
	switch ($xReport) {
		case "search":
			showSearchReport();
			break;
		case "popprod":
			showPopularProductsReport();
			break;
		case "popsec":
			showPopularSectionsReport();
			break;
		case "abnprod":
			showAbandonedCartsProducts();
			break;
		case "ordtot":
			showOrderTotals();
			break;
		case "ordprod":
			showOrderProducts();
			break;						
		case "custacc":
			showCustomerAccountsNew();
			break;
	}
?>
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
	function showSearchReport() {
		global $dbA,$tableLogs,$whereClause,$xGrouping,$groupShow,$tableReportsSearch,$groupBit,$tableProducts,$xLimit;
		dbConnect($dbA);		
?>
		<p>
		<table width="98%" cellpadding="2" cellspacing="0" class="table-outline-white">
		<tr>
			<td class="table-grey-nocenter" colspan="6" align="left">SearchStatistics</td>
		</tr>
		<tr>
			<td class="table-white-nocenter-s" align="left"><?php print $groupShow; ?></td>
			<td class="table-white-nocenter-s" align="left">SearchString</td>
			<td class="table-white-nocenter-s" align="left">Average Products</td>
			<td class="table-white-nocenter-s" align="right">Average Sections</td>
			<td class="table-white-nocenter-s" align="right">Percentage</td>
			<td class="table-white-nocenter-s" align="right">Total</td>
		</tr>
<?php
		$result = $dbA->query("select * from $tableReportsSearch where $whereClause");
		$fullTotal = $dbA->count($result);
		$result = $dbA->query("select $tableReportsSearch.*,count(*) as total,sum(productResults) as pR, sum(sectionResults) as pS, $groupBit as groupBit from $tableReportsSearch where $whereClause group by groupBit,searchstring order by groupBit,total DESC, searchstring limit 0,$xLimit");
		$currGroup = "";
		$count = $dbA->count($result);
		$totalCounter = 0;
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
			<td class="table-white-nocenter-light-s" align="left"><?php print $record["searchstring"]; ?></td>
			<td class="table-white-nocenter-light-s" align="left"><?php print number_format($record["pR"]/$record["total"],0,".",","); ?></td>
			<td class="table-white-nocenter-light-s" align="left"><?php print number_format($record["pS"]/$record["total"],0,".",","); ?></td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $thisPercent; ?>%</td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $record["total"]; ?></td>
		</tr>
<?php
			$totalCounter = $totalCounter + $record["total"];
		}
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" colspan="5">Totals</td>
			<td class="table-white-nocenter-s" align="right" colspan="1"><?php print $totalCounter; ?></td>
		</tr>
		</table>
<?php		
	}	
	function showPopularProductsReport() {
		global $dbA,$tableLogs,$whereClause,$xGrouping,$groupShow,$tableReportsPopular,$groupBit,$tableProducts,$xLimit;
		dbConnect($dbA);		
?>
		<p>
		<table width="98%" cellpadding="2" cellspacing="0" class="table-outline-white">
		<tr>
			<td class="table-grey-nocenter" colspan="5" align="left">Popular Products</td>
		</tr>
		<tr>
			<td class="table-white-nocenter-s" align="left"><?php print $groupShow; ?></td>
			<td class="table-white-nocenter-s" align="left">Code</td>
			<td class="table-white-nocenter-s" align="left">Name</td>
			<td class="table-white-nocenter-s" align="right">Percentage</td>
			<td class="table-white-nocenter-s" align="right">Total</td>
		</tr>
<?php
		$result = $dbA->query("select * from $tableReportsPopular where $whereClause and type='P'");
		$fullTotal = $dbA->count($result);
		$result = $dbA->query("select $tableReportsPopular.*,count(*) as total, $groupBit as groupBit ,$tableProducts.name,$tableProducts.code from $tableReportsPopular,$tableProducts where $tableProducts.productID = $tableReportsPopular.theID and $whereClause and type='P' group by groupBit,theID order by groupBit,total DESC, name limit 0,$xLimit");
		$currGroup = "";
		$count = $dbA->count($result);
		$totalCounter = 0;
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
			<td class="table-white-nocenter-light-s" align="left"><?php print $record["code"]; ?></td>
			<td class="table-white-nocenter-light-s" align="left"><?php print $record["name"]; ?></td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $thisPercent; ?>%</td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $record["total"]; ?></td>
		</tr>
<?php
			$totalCounter = $totalCounter + $record["total"];
		}
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" colspan="4">Totals</td>
			<td class="table-white-nocenter-s" align="right" colspan="1"><?php print $totalCounter; ?></td>
		</tr>
		</table>
<?php		
	}
	function showPopularSectionsReport() {
		global $dbA,$tableLogs,$whereClause,$xGrouping,$groupShow,$tableReportsPopular,$groupBit,$tableSections,$xLimit;
		dbConnect($dbA);		
?>
		<p>
		<table width="98%" cellpadding="2" cellspacing="0" class="table-outline-white">
		<tr>
			<td class="table-grey-nocenter" colspan="4" align="left">Popular Sections</td>
		</tr>
		<tr>
			<td class="table-white-nocenter-s" align="left"><?php print $groupShow; ?></td>
			<td class="table-white-nocenter-s" align="left">Name</td>
			<td class="table-white-nocenter-s" align="right">Percentage</td>
			<td class="table-white-nocenter-s" align="right">Total</td>
		</tr>
<?php
		$result = $dbA->query("select * from $tableReportsPopular where $whereClause and type='S'");
		$fullTotal = $dbA->count($result);
		$result = $dbA->query("select $tableReportsPopular.*,count(*) as total, $groupBit as groupBit ,$tableSections.title from $tableReportsPopular,$tableSections where $tableSections.sectionID = $tableReportsPopular.theID and $whereClause and type='S' group by groupBit,theID order by groupBit,total DESC, title limit 0,$xLimit");
		$currGroup = "";
		$count = $dbA->count($result);
		$totalCounter = 0;
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
			<td class="table-white-nocenter-light-s" align="left"><?php print $record["title"]; ?></td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $thisPercent; ?>%</td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $record["total"]; ?></td>
		</tr>
<?php
			$totalCounter = $totalCounter + $record["total"];
		}
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" colspan="3">Totals</td>
			<td class="table-white-nocenter-s" align="right" colspan="1"><?php print $totalCounter; ?></td>
		</tr>
		</table>
<?php		
	}	
	function showAbandonedCartsProducts() {
		global $dbA,$tableLogs,$whereClause,$whereClauseAbn,$xGrouping,$groupShow,$tableReportsPopular,$groupBit,$groupBitAbn,$tableProducts,$xLimit,$tableCartsContents,$tableCarts;
		dbConnect($dbA);		
?>
		<p>
		<table width="98%" cellpadding="2" cellspacing="0" class="table-outline-white">
		<tr>
			<td class="table-grey-nocenter" colspan="5" align="left">Abandoned Cart Products</td>
		</tr>
		<tr>
			<td class="table-white-nocenter-s" align="left"><?php print $groupShow; ?></td>
			<td class="table-white-nocenter-s" align="left">Code</td>
			<td class="table-white-nocenter-s" align="left">Name</td>
			<td class="table-white-nocenter-s" align="right">Percentage</td>
			<td class="table-white-nocenter-s" align="right">Total</td>
		</tr>
<?php
		//im not sure this is correct either??
		$result = $dbA->query("select * from $tableCarts where $whereClauseAbn");
		$fullTotal = $dbA->count($result);
		$result = $dbA->query("select $tableCarts.*,$tableCartsContents.*,count(*) as total, $groupBitAbn as groupBit ,$tableProducts.name,$tableProducts.code from $tableCarts,$tableCartsContents,$tableProducts where $tableProducts.productID = $tableCartsContents.productID and $tableCarts.cartID = $tableCartsContents.cartID and $whereClauseAbn group by groupBit,$tableCartsContents.productID order by groupBit,total DESC, name limit 0,$xLimit");
		$currGroup = "";
		$count = $dbA->count($result);
		$totalCounter = 0;
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
			<td class="table-white-nocenter-light-s" align="left"><?php print $record["code"]; ?></td>
			<td class="table-white-nocenter-light-s" align="left"><?php print $record["name"]; ?></td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $thisPercent; ?>%</td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $record["total"]; ?></td>
		</tr>
<?php
			$totalCounter = $totalCounter + $record["total"];
		}
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" colspan="4">Totals</td>
			<td class="table-white-nocenter-s" align="right" colspan="1"><?php print $totalCounter; ?></td>
		</tr>
		</table>
<?php		
	}
	function showOrderTotals() {
		global $dbA,$tableLogs,$whereOrderClause,$xGrouping,$groupShow,$groupOrderBit,$tableProducts,$xLimit,$tableOrdersHeaders,$tableCarts;
		dbConnect($dbA);		
?>
		<p>
		<table width="98%" cellpadding="2" cellspacing="0" class="table-outline-white">
		<tr>
			<td class="table-grey-nocenter" colspan="3" align="left">Total Orders</td>
		</tr>
		<tr>
			<td class="table-white-nocenter-s" align="left"><?php print $groupShow; ?></td>
			<td class="table-white-nocenter-s" align="right">Percentage</td>
			<td class="table-white-nocenter-s" align="right">Total</td>
		</tr>
<?php
		$result = $dbA->query("select * from $tableOrdersHeaders where $whereOrderClause");
		$fullTotal = $dbA->count($result);
		$result = $dbA->query("select $tableOrdersHeaders.*,count(*) as total, $groupOrderBit as groupBit from $tableOrdersHeaders where $whereOrderClause group by groupBit order by groupBit,total DESC limit 0,$xLimit");
		$currGroup = "";
		$count = $dbA->count($result);
		$totalCounter = 0;
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
			<td class="table-white-nocenter-light-s" align="right"><?php print $thisPercent; ?>%</td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $record["total"]; ?></td>
		</tr>
<?php
			$totalCounter = $totalCounter + $record["total"];
		}
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" colspan="2">Totals</td>
			<td class="table-white-nocenter-s" align="right" colspan="1"><?php print $totalCounter; ?></td>
		</tr>
		</table>
<?php		
	}
	function showOrderProducts() {
		global $dbA,$tableLogs,$whereOrderClause,$xGrouping,$groupShow,$groupOrderBit,$tableProducts,$xLimit,$tableOrdersHeaders,$tableOrdersLines;
		dbConnect($dbA);		
?>
		<p>
		<table width="98%" cellpadding="2" cellspacing="0" class="table-outline-white">
		<tr>
			<td class="table-grey-nocenter" colspan="5" align="left">Ordered Products</td>
		</tr>
		<tr>
			<td class="table-white-nocenter-s" align="left"><?php print $groupShow; ?></td>
			<td class="table-white-nocenter-s" align="left">Code</td>
			<td class="table-white-nocenter-s" align="left">Name</td>
			<td class="table-white-nocenter-s" align="right">Percentage</td>
			<td class="table-white-nocenter-s" align="right">Total Qty</td>
		</tr>
<?php
		//this is going to be incorrect isnt it???
		
		$result = $dbA->query("select sum(qty) as tquant from $tableOrdersHeaders,$tableOrdersLines where $whereOrderClause and $tableOrdersHeaders.orderID = $tableOrdersLines.orderID");
		$record = $dbA->fetch($result);
		$fullTotal = $record["tquant"];
		$result = $dbA->query("select $tableOrdersHeaders.orderID,$tableOrdersLines.*,sum(qty) as total, $groupOrderBit as groupBit ,$tableProducts.name,$tableProducts.code from $tableOrdersHeaders,$tableOrdersLines,$tableProducts where $tableProducts.productID = $tableOrdersLines.productID and $tableOrdersHeaders.orderID = $tableOrdersLines.orderID and $whereOrderClause group by groupBit,$tableOrdersLines.productID order by groupBit,total DESC, $tableProducts.name limit 0,$xLimit");
		$currGroup = "";
		$count = $dbA->count($result);
		$totalCounter = 0;
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
			<td class="table-white-nocenter-light-s" align="left"><?php print $record["code"]; ?></td>
			<td class="table-white-nocenter-light-s" align="left"><?php print $record["name"]; ?></td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $thisPercent; ?>%</td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $record["total"]; ?></td>
		</tr>
<?php
			$totalCounter = $totalCounter + $record["total"];
		}
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" colspan="4">Totals</td>
			<td class="table-white-nocenter-s" align="right" colspan="1"><?php print $totalCounter; ?></td>
		</tr>
		</table>
<?php		
	}
	function showCustomerAccountsNew() {
		global $dbA,$tableLogs,$whereClause,$xGrouping,$groupShow,$groupBit,$tableCustomers,$xLimit;
		dbConnect($dbA);		
?>
		<p>
		<table width="98%" cellpadding="2" cellspacing="0" class="table-outline-white">
		<tr>
			<td class="table-grey-nocenter" colspan="3" align="left">New Customer Accounts</td>
		</tr>
		<tr>
			<td class="table-white-nocenter-s" align="left"><?php print $groupShow; ?></td>
			<td class="table-white-nocenter-s" align="right">Percentage</td>
			<td class="table-white-nocenter-s" align="right">Total</td>
		</tr>
<?php
		$result = $dbA->query("select * from $tableCustomers where $whereClause");
		$fullTotal = $dbA->count($result);
		$result = $dbA->query("select count(*) as total, $groupBit as groupBit from $tableCustomers where $whereClause group by groupBit order by groupBit,total DESC limit 0,$xLimit");
		$currGroup = "";
		$count = $dbA->count($result);
		$totalCounter = 0;
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
			<td class="table-white-nocenter-light-s" align="right"><?php print $thisPercent; ?>%</td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $record["total"]; ?></td>
		</tr>
<?php
			$totalCounter = $totalCounter + $record["total"];
		}
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" colspan="2">Totals</td>
			<td class="table-white-nocenter-s" align="right" colspan="1"><?php print $totalCounter; ?></td>
		</tr>
		</table>
<?php		
	}
