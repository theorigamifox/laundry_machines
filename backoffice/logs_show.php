<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/graphSys.php");
	$xGrouping = getFORM("xGrouping");
	$xReports = split(";",getFORM("xReports"));
	$xYearF = getFORM("xYearF");
	$xMonthF = getFORM("xMonthF");
	$xDayF = getFORM("xDayF");
	$xYearT = getFORM("xYearT");
	$xMonthT = getFORM("xMonthT");
	$xDayT = getFORM("xDayT");
	switch ($xGrouping) {
		case "year":
			$groupShow = "Year";
			break;
		case "month":
			$groupShow = "Month";
			break;
		case "day":
			$groupShow = "Day";
			$xGrouping = "concdate";
			break;
		case "dayofweek";
			$groupShow = "Day of Week";
			break;
		case "hour";
			$groupShow = "Hour of Day";
			break;
		default:
			$groupShow = "";
			break;
	}
	$whereClause = "(concdate >= '$xYearF$xMonthF$xDayF' and concdate <= '$xYearT$xMonthT$xDayT')";
	
	function createRefClause() {
		$xRefArray = retrieveOption("ignoreReferrers");
		$refSplit = split(",",$xRefArray);
		$refClause = "";
		for ($f = 0; $f < count($refSplit); $f++) {
			if (chop($refSplit[$f]) != "") {
				if ($refClause > "") {
					$refClause .=" and ";
				}
				$refClause .= "referrer not like \"%".$refSplit[$f]."%\"";
			}
		}
		if ($refClause != "") {
			return "(".$refClause.") and ";
		} else {
			return "";
		}
	}
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
		<td class="table-white" align="left">Log Reports from <b>&quot;<?php print $xDayF."/".$xMonthF."/".$xYearF; ?>&quot;</b> to <b>&quot;<?php print $xDayT."/".$xMonthT."/".$xYearT; ?>&quot;</b>, Details Grouped By: <b><?php print $groupShow; ?></b> </td>
		<td class="table-white" align="right" width="106"><button id="buttonPrint" class="button-grey" onClick="self.print();">Print Logs</button></td>
	</tr>
</table>
<?php
	for ($f = 0; $f < count($xReports); $f++) {
		switch ($xReports[$f]) {
			case "browser":
				showBrowserReport();
				break;
			case "os":
				showOSReport();
				break;
			case "referrer":
				showReferrerReport();
				break;
			case "searchsum":
				showSearchSummaryReport();
				break;	
			case "searchquery":
				showSearchCombinedReport();
				break;		
			case "keywords":
				showSearchKeywordsReport();
				break;	
			case "pages":
				showPagesReport();
				break;
			case "domain":
				showTopLevelDomainReport();
				break;		
			case "ip":
				showIPReport();
				break;																			
		}
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
				case "concdate":
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
	function showBrowserReport() {
		global $dbA,$tableLogs,$whereClause,$xGrouping,$groupShow;
		$myGraph = new graphSys();
		$myGraph->setGraphTitle("Top 10 Browsers");
		$myGraph->setYAxis("% Of Total");
		dbConnect($dbA);
		$result = $dbA->query("select *,count(*) as total from $tableLogs where $whereClause group by browser order by total DESC, browser");
		$count = $dbA->count($result);
		$totalCounter = 0;
		if ($count == 0) { return false; }
		for ($f = 0; $f < $count; $f++ ) {
			$record = $dbA->fetch($result);
			if ($f < 11) {	//limit graphing to 10 columns
				$records[] = $record;
			}
			$totalCounter = $totalCounter + $record["total"];
		}			
		for ($f = 0; $f < count($records); $f++) {
			$xAxis[] = $f+1;
			$xKey[] = array($f+1,$records[$f]["browser"]);
			$xValues[] = (100/$totalCounter)*$records[$f]["total"];
		}
		echo "<p>";
		$myGraph->setXAxis("Browser",$xAxis);
		$myGraph->addXValues($xValues,"Rainbow");
		$myGraph->graphWidth=550;
		$myGraph->graphHeight=160;
		$myGraph->getYSplit();
		$myGraph->showKey(1);
		$myGraph->setKey($xKey);
		$myGraph->drawGraph();
		

?>
		<p>
		<table width="98%" cellpadding="2" cellspacing="0" class="table-outline-white">
		<tr>
			<td class="table-grey-nocenter" colspan="4" align="left">Browsers</td>
		</tr>
		<tr>
			<td class="table-white-nocenter-s" align="left"><?php print $groupShow; ?></td>
			<td class="table-white-nocenter-s" align="left">Browser Name</td>
			<td class="table-white-nocenter-s" align="right">Percentage</td>
			<td class="table-white-nocenter-s" align="right">Total</td>
		</tr>
<?php
		$result = $dbA->query("select * from $tableLogs where $whereClause");
		$fullTotal = $dbA->count($result);
		$result = $dbA->query("select *,count(*) as total from $tableLogs where $whereClause group by $xGrouping,browser order by $xGrouping,total DESC, browser");
		$currGroup = "";
		$count = $dbA->count($result);
		$totalCounter = 0;
		if ($count == 0) { return false; }
		for ($f = 0; $f < $count; $f++) {
			$record = $dbA->fetch($result);
			$thisPercent = number_format((100/$fullTotal)*$record["total"],2,".","");
			if ($currGroup != $record[$xGrouping]) {
				$currGroup = $record[$xGrouping];
				$showGroup = $currGroup;
			} else {
				$showGroup = "";
			}
?>
		<tr>
			<td class="table-white-nocenter-light-s" align="left"><?php print groupText($showGroup); ?></td>
			<td class="table-white-nocenter-light-s" align="left"><?php print $record["browser"]; ?></td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $thisPercent; ?>%</td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $record["total"]; ?></td>
		</tr>
<?php
		}
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" colspan="3">Totals</td>
			<td class="table-white-nocenter-s" align="right" colspan="1"><?php print $fullTotal; ?></td>
		</tr>
		</table>
<?php		
	}
	function showOSReport() {
		global $dbA,$tableLogs,$whereClause,$xGrouping,$groupShow;
		$myGraph = new graphSys();
		$myGraph->setGraphTitle("Top 10 Operating Systems");
		$myGraph->setYAxis("% Of Total");
		dbConnect($dbA);
		$result = $dbA->query("select *,count(*) as total from $tableLogs where $whereClause group by os order by total DESC, os");
		$count = $dbA->count($result);
		$totalCounter = 0;
		if ($count == 0) { return false; }
		for ($f = 0; $f < $count; $f++ ) {
			$record = $dbA->fetch($result);
			if ($f < 11) {	//limit graphing to 10 columns
				$records[] = $record;
			}
			$totalCounter = $totalCounter + $record["total"];
		}			
		for ($f = 0; $f < count($records); $f++) {
			$xAxis[] = $records[$f]["os"];
			$xValues[] = (100/$totalCounter)*$records[$f]["total"];
		}
		echo "<p>";
		$myGraph->setXAxis("Operating System",$xAxis);
		$myGraph->addXValues($xValues,"Rainbow");
		$myGraph->graphWidth=550;
		$myGraph->graphHeight=160;
		$myGraph->getYSplit();
		$myGraph->drawGraph();
?>
		<p>
		<table width="98%" cellpadding="2" cellspacing="0" class="table-outline-white">
		<tr>
			<td class="table-grey-nocenter" colspan="4" align="left">Operating Systems</td>
		</tr>
		<tr>
			<td class="table-white-nocenter-s" align="left"><?php print $groupShow; ?></td>
			<td class="table-white-nocenter-s" align="left">Operating System</td>
			<td class="table-white-nocenter-s" align="right">Percentage</td>
			<td class="table-white-nocenter-s" align="right">Total</td>
		</tr>
<?php
		$result = $dbA->query("select * from $tableLogs where $whereClause");
		$fullTotal = $dbA->count($result);
		$result = $dbA->query("select *,count(*) as total from $tableLogs where $whereClause group by $xGrouping,os order by $xGrouping,total DESC, os");
		$currGroup = "";
		$count = $dbA->count($result);
		$totalCounter = 0;
		for ($f = 0; $f < $count; $f++) {
			$record = $dbA->fetch($result);
			$thisPercent = number_format((100/$fullTotal)*$record["total"],2,".","");
			if ($currGroup != $record[$xGrouping]) {
				$currGroup = $record[$xGrouping];
				$showGroup = $currGroup;
			} else {
				$showGroup = "";
			}
?>
		<tr>
			<td class="table-white-nocenter-light-s" align="left"><?php print groupText($showGroup); ?></td>
			<td class="table-white-nocenter-light-s" align="left"><?php print $record["os"]; ?></td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $thisPercent; ?>%</td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $record["total"]; ?></td>
		</tr>
<?php
		}
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" colspan="3">Totals</td>
			<td class="table-white-nocenter-s" align="right" colspan="1"><?php print $fullTotal; ?></td>
		</tr>
		</table>
<?php		
	}
	function showReferrerReport() {
		global $dbA,$tableLogs,$whereClause,$xGrouping,$groupShow;
		$myGraph = new graphSys();
		$myGraph->setGraphTitle("Top 10 Referrers");
		$myGraph->setYAxis("% Of Total");
		dbConnect($dbA);
		$result = $dbA->query("select *,count(*) as total from $tableLogs where ".createRefClause()." $whereClause group by referrer order by total DESC, referrer");
		$count = $dbA->count($result);
		$totalCounter = 0;
		if ($count == 0) { return false; }
		for ($f = 0; $f < $count; $f++ ) {
			$record = $dbA->fetch($result);
			if ($f < 11) {	//limit graphing to 10 columns
				$records[] = $record;
			}
			$totalCounter = $totalCounter + $record["total"];
		}			
		for ($f = 0; $f < count($records); $f++) {
			$xAxis[] = $f+1;
			$xKey[] = array($f+1,$records[$f]["referrer"]);
			$xValues[] = (100/$totalCounter)*$records[$f]["total"];
		}
		echo "<p>";
		$myGraph->setXAxis("Referrer",$xAxis);
		$myGraph->addXValues($xValues,"Rainbow");
		$myGraph->graphWidth=300;
		$myGraph->graphHeight=160;
		$myGraph->getYSplit();
		$myGraph->showKey(1);
		$myGraph->setKey($xKey);
		$myGraph->drawGraph();
?>
		<p>
		<table width="98%" cellpadding="2" cellspacing="0" class="table-outline-white">
		<tr>
			<td class="table-grey-nocenter" colspan="4" align="left">Referrers</td>
		</tr>
		<tr>
			<td class="table-white-nocenter-s" align="left"><?php print $groupShow; ?></td>
			<td class="table-white-nocenter-s" align="left">URL</td>
			<td class="table-white-nocenter-s" align="right">Percentage</td>
			<td class="table-white-nocenter-s" align="right">Total</td>
		</tr>
<?php
		$result = $dbA->query("select * from $tableLogs where ".createRefClause()." $whereClause");
		$fullTotal = $dbA->count($result);
		$result = $dbA->query("select *,count(*) as total from $tableLogs where ".createRefClause()." $whereClause group by $xGrouping,referrer order by $xGrouping,total DESC, referrer");
		$currGroup = "";
		$count = $dbA->count($result);
		$totalCounter = 0;
		for ($f = 0; $f < $count; $f++) {
			$record = $dbA->fetch($result);
			$thisPercent = number_format((100/$fullTotal)*$record["total"],2,".","");
			if ($currGroup != $record[$xGrouping]) {
				$currGroup = $record[$xGrouping];
				$showGroup = $currGroup;
			} else {
				$showGroup = "";
			}
?>
		<tr>
			<td class="table-white-nocenter-light-s" align="left"><?php print groupText($showGroup); ?></td>
			<td class="table-white-nocenter-light-s" align="left"><?php print substr($record["referrer"],0,150); ?></td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $thisPercent; ?>%</td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $record["total"]; ?></td>
		</tr>
<?php
		}
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" colspan="3">Totals</td>
			<td class="table-white-nocenter-s" align="right" colspan="1"><?php print $fullTotal; ?></td>
		</tr>
		</table>
<?php		
	}
	function showSearchSummaryReport() {
		global $dbA,$tableLogs,$whereClause,$xGrouping,$groupShow;
		$myGraph = new graphSys();
		$myGraph->setGraphTitle("Top 10 Search Engines");
		$myGraph->setYAxis("% Of Total");
		dbConnect($dbA);
		$result = $dbA->query("select *,count(*) as total from $tableLogs where $whereClause and searchengine != '' group by searchengine order by total DESC, searchengine");
		$count = $dbA->count($result);
		$totalCounter = 0;
		if ($count == 0) { return false; }
		for ($f = 0; $f < $count; $f++ ) {
			$record = $dbA->fetch($result);
			if ($f < 11) {	//limit graphing to 10 columns
				$records[] = $record;
			}
			$totalCounter = $totalCounter + $record["total"];
		}			
		for ($f = 0; $f < count($records); $f++) {
			$xAxis[] = $f+1;
			$xKey[] = array($f+1,$records[$f]["searchengine"]);
			$xValues[] = (100/$totalCounter)*$records[$f]["total"];
		}
		echo "<p>";
		$myGraph->setXAxis("Browser",$xAxis);
		$myGraph->addXValues($xValues,"Rainbow");
		$myGraph->graphWidth=350;
		$myGraph->graphHeight=160;
		$myGraph->getYSplit();
		$myGraph->showKey(1);
		$myGraph->setKey($xKey);		
		$myGraph->drawGraph();
?>
		<p>
		<table width="98%" cellpadding="2" cellspacing="0" class="table-outline-white">
		<tr>
			<td class="table-grey-nocenter" colspan="4" align="left">Search Engine Summary</td>
		</tr>
		<tr>
			<td class="table-white-nocenter-s" align="left"><?php print $groupShow; ?></td>
			<td class="table-white-nocenter-s" align="left">Search Engine Name</td>
			<td class="table-white-nocenter-s" align="right">Percentage</td>
			<td class="table-white-nocenter-s" align="right">Total</td>
		</tr>
<?php
		$result = $dbA->query("select * from $tableLogs where $whereClause and searchengine != ''");
		$fullTotal = $dbA->count($result);
		$result = $dbA->query("select *,count(*) as total from $tableLogs where $whereClause and searchengine != '' group by $xGrouping,searchengine order by $xGrouping,total DESC, searchengine");
		$currGroup = "";
		$count = $dbA->count($result);
		$totalCounter = 0;
		if ($count == 0) { return false; }
		for ($f = 0; $f < $count; $f++) {
			$record = $dbA->fetch($result);
			$thisPercent = number_format((100/$fullTotal)*$record["total"],2,".","");
			if ($currGroup != $record[$xGrouping]) {
				$currGroup = $record[$xGrouping];
				$showGroup = $currGroup;
			} else {
				$showGroup = "";
			}
?>
		<tr>
			<td class="table-white-nocenter-light-s" align="left"><?php print groupText($showGroup); ?></td>
			<td class="table-white-nocenter-light-s" align="left"><?php print $record["searchengine"]; ?></td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $thisPercent; ?>%</td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $record["total"]; ?></td>
		</tr>
<?php
		}
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" colspan="3">Totals</td>
			<td class="table-white-nocenter-s" align="right" colspan="1"><?php print $fullTotal; ?></td>
		</tr>
		</table>
<?php		
	}
	function showSearchCombinedReport() {
		global $dbA,$tableLogs,$whereClause,$xGrouping,$groupShow;
		$myGraph = new graphSys();
		$myGraph->setGraphTitle("Top 10 Search Engines / Keywords Combined");
		$myGraph->setYAxis("% Of Total");
		dbConnect($dbA);
		$result = $dbA->query("select *,count(*) as total from $tableLogs where $whereClause and searchengine != '' and searchstring != '' group by searchengine,searchstring order by total DESC, searchengine");
		$count = $dbA->count($result);
		$totalCounter = 0;
		if ($count == 0) { return false; }
		for ($f = 0; $f < $count; $f++ ) {
			$record = $dbA->fetch($result);
			if ($f < 11) {	//limit graphing to 10 columns
				$records[] = $record;
			}
			$totalCounter = $totalCounter + $record["total"];
		}			
		for ($f = 0; $f < count($records); $f++) {
			$xAxis[] = $f+1;
			$xKey[] = array($f+1,"<b>".$records[$f]["searchengine"]."</b> ".$records[$f]["searchstring"]);
			$xValues[] = (100/$totalCounter)*$records[$f]["total"];
		}
		echo "<p>";
		$myGraph->setXAxis("Engine &amp; Keywords",$xAxis);
		$myGraph->addXValues($xValues,"Rainbow");
		$myGraph->graphWidth=350;
		$myGraph->graphHeight=160;
		$myGraph->getYSplit();
		$myGraph->showKey(1);
		$myGraph->setKey($xKey);		
		$myGraph->drawGraph();
?>
		<p>
		<table width="98%" cellpadding="2" cellspacing="0" class="table-outline-white">
		<tr>
			<td class="table-grey-nocenter" colspan="5" align="left">Search Engine / Keywords Combined</td>
		</tr>
		<tr>
			<td class="table-white-nocenter-s" align="left"><?php print $groupShow; ?></td>
			<td class="table-white-nocenter-s" align="left">Search Engine Name</td>
			<td class="table-white-nocenter-s" align="left">Keywords</td>
			<td class="table-white-nocenter-s" align="right">Percentage</td>
			<td class="table-white-nocenter-s" align="right">Total</td>
		</tr>
<?php
		$result = $dbA->query("select * from $tableLogs where $whereClause and searchengine != ''");
		$fullTotal = $dbA->count($result);
		$result = $dbA->query("select *,count(*) as total from $tableLogs where $whereClause and searchengine != '' group by $xGrouping,searchengine,searchstring order by $xGrouping,total DESC, searchengine,searchstring");
		$currGroup = "";
		$count = $dbA->count($result);
		$totalCounter = 0;
		if ($count == 0) { return false; }
		for ($f = 0; $f < $count; $f++) {
			$record = $dbA->fetch($result);
			$thisPercent = number_format((100/$fullTotal)*$record["total"],2,".","");
			if ($currGroup != $record[$xGrouping]) {
				$currGroup = $record[$xGrouping];
				$showGroup = $currGroup;
			} else {
				$showGroup = "";
			}
?>
		<tr>
			<td class="table-white-nocenter-light-s" align="left"><?php print groupText($showGroup); ?></td>
			<td class="table-white-nocenter-light-s" align="left"><?php print $record["searchengine"]; ?></td>
			<td class="table-white-nocenter-light-s" align="left"><?php print $record["searchstring"]; ?></td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $thisPercent; ?>%</td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $record["total"]; ?></td>
		</tr>
<?php
		}
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" colspan="4">Totals</td>
			<td class="table-white-nocenter-s" align="right" colspan="1"><?php print $fullTotal; ?></td>
		</tr>
		</table>
<?php		
	}
	function showSearchKeywordsReport() {
		global $dbA,$tableLogs,$whereClause,$xGrouping,$groupShow;
		$myGraph = new graphSys();
		$myGraph->setGraphTitle("Top 10 Keywords");
		$myGraph->setYAxis("% Of Total");
		dbConnect($dbA);
		$result = $dbA->query("select *,count(*) as total from $tableLogs where $whereClause and searchstring != '' group by searchstring order by total DESC, searchstring");
		$count = $dbA->count($result);
		$totalCounter = 0;
		if ($count == 0) { return false; }
		for ($f = 0; $f < $count; $f++ ) {
			$record = $dbA->fetch($result);
			if ($f < 11) {	//limit graphing to 10 columns
				$records[] = $record;
			}
			$totalCounter = $totalCounter + $record["total"];
		}			
		for ($f = 0; $f < count($records); $f++) {
			$xAxis[] = $f+1;
			$xKey[] = array($f+1,$records[$f]["searchstring"]);
			$xValues[] = (100/$totalCounter)*$records[$f]["total"];
		}
		echo "<p>";
		$myGraph->setXAxis("Keywords",$xAxis);
		$myGraph->addXValues($xValues,"Rainbow");
		$myGraph->graphWidth=350;
		$myGraph->graphHeight=160;
		$myGraph->getYSplit();
		$myGraph->showKey(1);
		$myGraph->setKey($xKey);		
		$myGraph->drawGraph();
?>
		<p>
		<table width="98%" cellpadding="2" cellspacing="0" class="table-outline-white">
		<tr>
			<td class="table-grey-nocenter" colspan="4" align="left">Search Engine Keywords</td>
		</tr>
		<tr>
			<td class="table-white-nocenter-s" align="left"><?php print $groupShow; ?></td>
			<td class="table-white-nocenter-s" align="left">Keywords</td>
			<td class="table-white-nocenter-s" align="right">Percentage</td>
			<td class="table-white-nocenter-s" align="right">Total</td>
		</tr>
<?php
		$result = $dbA->query("select * from $tableLogs where $whereClause and searchstring != ''");
		$fullTotal = $dbA->count($result);
		$result = $dbA->query("select *,count(*) as total from $tableLogs where $whereClause and searchstring != '' group by $xGrouping,searchstring order by $xGrouping,total DESC, searchstring");
		$currGroup = "";
		$count = $dbA->count($result);
		$totalCounter = 0;
		if ($count == 0) { return false; }
		for ($f = 0; $f < $count; $f++) {
			$record = $dbA->fetch($result);
			$thisPercent = number_format((100/$fullTotal)*$record["total"],2,".","");
			if ($currGroup != $record[$xGrouping]) {
				$currGroup = $record[$xGrouping];
				$showGroup = $currGroup;
			} else {
				$showGroup = "";
			}
?>
		<tr>
			<td class="table-white-nocenter-light-s" align="left"><?php print groupText($showGroup); ?></td>
			<td class="table-white-nocenter-light-s" align="left"><?php print $record["searchstring"]; ?></td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $thisPercent; ?>%</td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $record["total"]; ?></td>
		</tr>
<?php
		}
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" colspan="3">Totals</td>
			<td class="table-white-nocenter-s" align="right" colspan="1"><?php print $fullTotal; ?></td>
		</tr>
		</table>
<?php		
	}
	function showPagesReport() {
		global $dbA,$tableLogs,$whereClause,$xGrouping,$groupShow;
		$myGraph = new graphSys();
		$myGraph->setGraphTitle("Top 10 Pages");
		$myGraph->setYAxis("% Of Total");
		dbConnect($dbA);
		$result = $dbA->query("select *,count(*) as total from $tableLogs where $whereClause group by page order by total DESC, searchstring");
		$count = $dbA->count($result);
		$totalCounter = 0;
		if ($count == 0) { return false; }
		for ($f = 0; $f < $count; $f++ ) {
			$record = $dbA->fetch($result);
			if ($f < 11) {	//limit graphing to 10 columns
				$records[] = $record;
			}
			$totalCounter = $totalCounter + $record["total"];
		}			
		for ($f = 0; $f < count($records); $f++) {
			$xAxis[] = $f+1;
			$xKey[] = array($f+1,$records[$f]["page"]);
			$xValues[] = (100/$totalCounter)*$records[$f]["total"];
		}
		echo "<p>";
		$myGraph->setXAxis("Page",$xAxis);
		$myGraph->addXValues($xValues,"Rainbow");
		$myGraph->graphWidth=350;
		$myGraph->graphHeight=160;
		$myGraph->getYSplit();
		$myGraph->showKey(1);
		$myGraph->setKey($xKey);		
		$myGraph->drawGraph();
?>
		<p>
		<table width="98%" cellpadding="2" cellspacing="0" class="table-outline-white">
		<tr>
			<td class="table-grey-nocenter" colspan="4" align="left">Pages Views</td>
		</tr>
		<tr>
			<td class="table-white-nocenter-s" align="left"><?php print $groupShow; ?></td>
			<td class="table-white-nocenter-s" align="left">Page</td>
			<td class="table-white-nocenter-s" align="right">Percentage</td>
			<td class="table-white-nocenter-s" align="right">Total</td>
		</tr>
<?php
		$result = $dbA->query("select * from $tableLogs where $whereClause");
		$fullTotal = $dbA->count($result);
		$result = $dbA->query("select *,count(*) as total from $tableLogs where $whereClause group by $xGrouping,page order by $xGrouping,total DESC, page");
		$currGroup = "";
		$count = $dbA->count($result);
		$totalCounter = 0;
		if ($count == 0) { return false; }
		for ($f = 0; $f < $count; $f++) {
			$record = $dbA->fetch($result);
			$thisPercent = number_format((100/$fullTotal)*$record["total"],2,".","");
			if ($currGroup != $record[$xGrouping]) {
				$currGroup = $record[$xGrouping];
				$showGroup = $currGroup;
			} else {
				$showGroup = "";
			}
?>
		<tr>
			<td class="table-white-nocenter-light-s" align="left"><?php print groupText($showGroup); ?></td>
			<td class="table-white-nocenter-light-s" align="left"><?php print $record["page"]; ?></td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $thisPercent; ?>%</td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $record["total"]; ?></td>
		</tr>
<?php
		}
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" colspan="3">Totals</td>
			<td class="table-white-nocenter-s" align="right" colspan="1"><?php print $fullTotal; ?></td>
		</tr>
		</table>
<?php		
	}
	function showTopLevelDomainReport() {
		global $dbA,$tableLogs,$whereClause,$xGrouping,$groupShow;
		$myGraph = new graphSys();
		$myGraph->setGraphTitle("Top 10 Domains");
		$myGraph->setYAxis("% Of Total");
		dbConnect($dbA);
		$result = $dbA->query("select *,count(*) as total from $tableLogs where $whereClause group by domaintoplevel order by total DESC, domaintoplevel");
		$count = $dbA->count($result);
		$totalCounter = 0;
		if ($count == 0) { return false; }
		for ($f = 0; $f < $count; $f++ ) {
			$record = $dbA->fetch($result);
			if ($f < 11) {	//limit graphing to 10 columns
				$records[] = $record;
			}
			$totalCounter = $totalCounter + $record["total"];
		}			
		for ($f = 0; $f < count($records); $f++) {
			$xAxis[] = $f+1;
			$xKey[] = array($f+1,$records[$f]["domaintoplevel"]);
			$xValues[] = (100/$totalCounter)*$records[$f]["total"];
		}
		echo "<p>";
		$myGraph->setXAxis("Domains",$xAxis);
		$myGraph->addXValues($xValues,"Rainbow");
		$myGraph->graphWidth=350;
		$myGraph->graphHeight=160;
		$myGraph->getYSplit();
		$myGraph->showKey(1);
		$myGraph->setKey($xKey);		
		$myGraph->drawGraph();
?>
		<p>
		<table width="98%" cellpadding="2" cellspacing="0" class="table-outline-white">
		<tr>
			<td class="table-grey-nocenter" colspan="4" align="left">Top Level Domains</td>
		</tr>
		<tr>
			<td class="table-white-nocenter-s" align="left"><?php print $groupShow; ?></td>
			<td class="table-white-nocenter-s" align="left">Domain</td>
			<td class="table-white-nocenter-s" align="right">Percentage</td>
			<td class="table-white-nocenter-s" align="right">Total</td>
		</tr>
<?php
		$result = $dbA->query("select * from $tableLogs where $whereClause");
		$fullTotal = $dbA->count($result);
		$result = $dbA->query("select *,count(*) as total from $tableLogs where $whereClause group by $xGrouping,domaintoplevel order by $xGrouping,total DESC, domaintoplevel");
		$currGroup = "";
		$count = $dbA->count($result);
		$totalCounter = 0;
		if ($count == 0) { return false; }
		for ($f = 0; $f < $count; $f++) {
			$record = $dbA->fetch($result);
			$thisPercent = number_format((100/$fullTotal)*$record["total"],2,".","");
			if ($currGroup != $record[$xGrouping]) {
				$currGroup = $record[$xGrouping];
				$showGroup = $currGroup;
			} else {
				$showGroup = "";
			}
?>
		<tr>
			<td class="table-white-nocenter-light-s" align="left"><?php print groupText($showGroup); ?></td>
			<td class="table-white-nocenter-light-s" align="left"><?php print $record["domaintoplevel"]; ?></td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $thisPercent; ?>%</td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $record["total"]; ?></td>
		</tr>
<?php
		}
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" colspan="3">Totals</td>
			<td class="table-white-nocenter-s" align="right" colspan="1"><?php print $fullTotal; ?></td>
		</tr>
		</table>
<?php		
	}
	function showIPReport() {
		global $dbA,$tableLogs,$whereClause,$xGrouping,$groupShow;
		$myGraph = new graphSys();
		$myGraph->setGraphTitle("Top 10 IP Addresses");
		$myGraph->setYAxis("% Of Total");
		dbConnect($dbA);
		$result = $dbA->query("select *,count(*) as total from $tableLogs where $whereClause group by ip order by total DESC, ip");
		$count = $dbA->count($result);
		$totalCounter = 0;
		if ($count == 0) { return false; }
		for ($f = 0; $f < $count; $f++ ) {
			$record = $dbA->fetch($result);
			if ($f < 11) {	//limit graphing to 10 columns
				$records[] = $record;
			}
			$totalCounter = $totalCounter + $record["total"];
		}			
		for ($f = 0; $f < count($records); $f++) {
			$xAxis[] = $f+1;
			$xKey[] = array($f+1,$records[$f]["ip"]);
			$xValues[] = (100/$totalCounter)*$records[$f]["total"];
		}
		echo "<p>";
		$myGraph->setXAxis("IPs",$xAxis);
		$myGraph->addXValues($xValues,"Rainbow");
		$myGraph->graphWidth=350;
		$myGraph->graphHeight=160;
		$myGraph->getYSplit();
		$myGraph->showKey(1);
		$myGraph->setKey($xKey);		
		$myGraph->drawGraph();
?>
		<p>
		<table width="98%" cellpadding="2" cellspacing="0" class="table-outline-white">
		<tr>
			<td class="table-grey-nocenter" colspan="4" align="left">IP Addresses</td>
		</tr>
		<tr>
			<td class="table-white-nocenter-s" align="left"><?php print $groupShow; ?></td>
			<td class="table-white-nocenter-s" align="left">IP Address</td>
			<td class="table-white-nocenter-s" align="right">Percentage</td>
			<td class="table-white-nocenter-s" align="right">Total</td>
		</tr>
<?php
		$result = $dbA->query("select * from $tableLogs where $whereClause");
		$fullTotal = $dbA->count($result);
		$result = $dbA->query("select *,count(*) as total from $tableLogs where $whereClause group by $xGrouping,ip order by $xGrouping,total DESC, ip");
		$currGroup = "";
		$count = $dbA->count($result);
		$totalCounter = 0;
		if ($count == 0) { return false; }
		for ($f = 0; $f < $count; $f++) {
			$record = $dbA->fetch($result);
			$thisPercent = number_format((100/$fullTotal)*$record["total"],2,".","");
			if ($currGroup != $record[$xGrouping]) {
				$currGroup = $record[$xGrouping];
				$showGroup = $currGroup;
			} else {
				$showGroup = "";
			}
?>
		<tr>
			<td class="table-white-nocenter-light-s" align="left"><?php print groupText($showGroup); ?></td>
			<td class="table-white-nocenter-light-s" align="left"><?php print $record["ip"]; ?></td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $thisPercent; ?>%</td>
			<td class="table-white-nocenter-light-s" align="right"><?php print $record["total"]; ?></td>
		</tr>
<?php
		}
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" colspan="3">Totals</td>
			<td class="table-white-nocenter-s" align="right" colspan="1"><?php print $fullTotal; ?></td>
		</tr>
		</table>
<?php		
	}
?>
