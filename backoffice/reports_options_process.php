<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$recordType = "Reports";
	$linkBackLink = "reports_options.php?d=d";
	
	$xAction = getFORM("xAction");

	if ($xAction == "options") {
		updateOption("reportsPopularProducts",getFORM("xReportsPopularProducts"));
		updateOption("reportsPopularSections",getFORM("xReportsPopularSections"));
		updateOption("reportsSearchStats",getFORM("xReportsSearchStats"));
		userLogActionUpdate("Reports","Settings");
		doRedirect($linkBackLink."&".userSessionGET());
	}
	if ($xAction == "clearpopular") {
		$xYear = getFORM("xYear");
		$xMonth = getFORM("xMonth");
		$xDay = getFORM("xDay");
		$dbA->query("delete from $tableReportsPopular where date <= \"$xYear$xMonth$xDay\"");
		userLogActionDelete("Reports","Popular Products / Sections Data");
		doRedirect("reports_summary.php?".userSessionGET());
	}
	if ($xAction == "clearcarts") {
		$xYear = getFORM("xYear");
		$xMonth = getFORM("xMonth");
		$xDay = getFORM("xDay");
		$dbA->query("delete from $tableCarts where date <= \"$xYear$xMonth$xDay\"");
		$dbA->query("delete from $tableCartsContents where date <= \"$xYear$xMonth$xDay\"");
		userLogActionDelete("Carts","Old Carts");
		doRedirect("reports_summary.php?".userSessionGET());
	}
	if ($xAction == "clearsearch") {
		$xYear = getFORM("xYear");
		$xMonth = getFORM("xMonth");
		$xDay = getFORM("xDay");
		$dbA->query("delete from $tableReportsSearch where date <= \"$xYear$xMonth$xDay\"");
		userLogActionDelete("Reports","Search Statistics Data");
		doRedirect("reports_summary.php?".userSessionGET());
	}	
?>
