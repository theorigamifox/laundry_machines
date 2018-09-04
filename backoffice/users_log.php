<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	$xCommand = getFORM("xCommand");
	if ($xCommand == "clear") {
		include("routines/confirmMessage.php");
		createConfirmMessage("Clear User Action Log",
		"Clear User Action Log?",
		"Are you sure you wish to clear the user action log?<br>Please click YES to clear the log file,<br>otherwise click NO",
		"self.location.href='users_process.php?xAction=clearlog&".userSessionGET()."';",
		"self.history.go(-1);");	
	}
	dbConnect($dbA);
	if ($xCommand == "view") {
		$xUsernameLog = getFORM("xUsernameLog");
		if ($xUsernameLog != "") {
			$theQuery = "select * from $tableUserLog where username=\"$xUsernameLog\" order by actionDate DESC, actionTime DESC";
			$searchAppend = "&xCommand=view&xUsernameLog=$xUsernameLog";
			$pageTitle = "($xUsernameLog ONLY)";
		} else {
			$theQuery = "select * from $tableUserLog order by actionDate DESC, actionTime DESC";
			$searchAppend = "&xCommand=view";
			$pageTitle = "(ALL USERS)";
		}
	}
	$xOffset = getFORM("xOffset");
	if ($xOffset=="") { $xOffset = 0; }	
	$ordersperpage = retrieveOption("adminUserLogPerPage");
	$result=$dbA->query($theQuery);
	$resultCount = $dbA->count($result);
	$theQuery .= " LIMIT $xOffset,$ordersperpage";
	$upperCount = $xOffset+$ordersperpage;
	$lowerCount = $xOffset+1;
	$middleButtons = "Viewing <b>$lowerCount - ".$upperCount."</b>&nbsp;";
	if ($xOffset-$ordersperpage > -1) {
		$pOffset = $xOffset - $ordersperpage;
		$previousButton = "<button id=\"buttonPrev\" class=\"button-expand\" onClick=\"self.location.href='users_log.php?".userSessionGET().$searchAppend."&xOffset=$pOffset'\">&lt; PREV</button>";
		$previousButton .= "&nbsp;<button id=\"buttonTop\" class=\"button-expand\" onClick=\"self.location.href='users_log.php?".userSessionGET().$searchAppend."&xOffset=0'\">[TOP]</button>";
	} else {
		$previousButton = "";
	}
	if ($xOffset+$ordersperpage < $resultCount) {
		$nOffset = $xOffset + $ordersperpage;
		$nextButton = "<button id=\"buttonNext\" class=\"button-expand\" onClick=\"self.location.href='users_log.php?".userSessionGET().$searchAppend."&xOffset=$nOffset'\">NEXT &gt;</button>";
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
		<td class="detail-title">Action Log File <?php print $pageTitle; ?></td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td colspan="5" class="table-white-no-border" align="right">Total selected entries: <b><?php print $resultCount; ?></b>, <?php print $navButtons; ?>&nbsp;</td>
	</tr>
	<tr>
		<td class="table-list-title">Date</td>
		<td class="table-list-title">Time</td>
		<td class="table-list-title">Username</td>
		<td class="table-list-title">IP Address</td>
		<td class="table-list-title">Description</td>
	</tr>
<?php
	$lResult = $dbA->query($theQuery);
	$lCount = $dbA->count($lResult);
	for ($f = 0; $f < $lCount; $f++) {
		$lRecord = $dbA->fetch($lResult);
?>
	<tr>
		<td class="table-list-entry1"><?php print formatDate($lRecord["actionDate"]); ?></td>
		<td class="table-list-entry1"><?php print formatTime($lRecord["actionTime"]); ?></td>
		<td class="table-list-entry1"><?php print $lRecord["username"]; ?></td>
		<td class="table-list-entry1"><?php print $lRecord["ip"]; ?></td>
		<td class="table-list-entry1"><?php print $lRecord["description"]; ?></td>
	</tr>
<?php
	}
	$dbA->close();
?>
</table>
</center>
</BODY>
</HTML>
