<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$recordType = "Logs";
	$linkBackLink = "logs_options.php?d=d";
	
	$xAction = getFORM("xAction");

	if ($xAction == "options") {
		updateOption("enableLogging",getFORM("xEnableLogging"));
		updateOption("logsReverseDNS",getFORM("xLogsReverseDNS"));
		updateOption("ignoreReferrers",getFORM("xIgnoreReferrers"));
		updateOption("ignoreIPLogs",getFORM("xIgnoreIPLogs"));
		userLogActionUpdate("Logs","Settings");
		doRedirect($linkBackLink."&".userSessionGET());
	}
	if ($xAction == "clear") {
		$xYear = getFORM("xYear");
		$xMonth = getFORM("xMonth");
		$xDay = getFORM("xDay");
		$dbA->query("delete from $tableLogs where concdate <= \"$xYear$xMonth$xDay\"");
		userLogActionDelete("Logs","Log Data");
		doRedirect("logs_summary.php?".userSessionGET());
	}
?>
