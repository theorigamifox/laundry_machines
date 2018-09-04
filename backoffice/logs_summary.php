<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	dbConnect($dbA);
	$uResult = $dbA->query("select * from $tableLogs order by concdate ASC");
	$totalHits = $dbA->count($uResult);
	$avPerDay = "n/a";
	$numDays = "n/a";
	if ($totalHits == 0) {
		$startDate = "no hits in database";
		$endDate = "no hits in database";
	} else {
		$uRecord = $dbA->fetch($uResult);
		$startDate = formatDate($uRecord["concdate"]);
		if ($dbA->seek($uResult,$totalHits-1)) {
			$uRecord = $dbA->fetch($uResult);
			$endDate = formatDate($uRecord["concdate"]);
			$uResult = $dbA->query("select * from $tableLogs group by concdate order by concdate ASC");
			$numDays = $dbA->count($uResult);
			$avPerDay = number_format($totalHits/$numDays,0,"","");
		} else {
			$endDate = "n/a";
		}
	}
	$dbA->close();
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
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Logs Summary</td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title">Total Hits In Database</td>
		<td class="table-list-entry1"><?php print $totalHits; ?></td>
	</tr>
	<tr>
		<td class="table-list-title">Earliest Log Date</td>
		<td class="table-list-entry1"><?php print $startDate; ?></td>
	</tr>
	<tr>
		<td class="table-list-title">Latest Log Date</td>
		<td class="table-list-entry1"><?php print $endDate; ?></td>
	</tr>
	<tr>
		<td class="table-list-title">Total Number Of Days</td>
		<td class="table-list-entry1"><?php print $numDays; ?></td>
	</tr>			
	<tr>
		<td class="table-list-title">Average Hits Per Day</td>
		<td class="table-list-entry1"><?php print $avPerDay; ?></td>
	</tr>	
</table>
</center>
</BODY>
</HTML>
