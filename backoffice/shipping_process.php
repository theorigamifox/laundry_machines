<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$recordType = "Shipping";
	$linkBackLink = urldecode(getFORM("xReturn"));
	
	$xAction = getFORM("xAction");
	$xType = getFORM("xType");

	if ($xAction == "options") {
		updateOption("shippingEnabled",getFORM("xShippingEnabled"));
		updateOption("defaultShipping",getFORM("xDefaultShipping"));
		userLog("Updated Shipping Settings");
		doRedirect($linkBackLink."&".userSessionGET());
	}
?>
