<?php
	include("../../static/config.php");
	include("../../routines/dbAccess_mysql.php");
	include("../../routines/tSys.php");
	include("../../routines/general.php");
	include("../../routines/stockControl.php");
	include("../../routines/emailOutput.php");

	$getString = "";
	$returnedVars = "";
	foreach ($HTTP_GET_VARS as $var => $value) {	    		
		if ($var != "hash") {
			$getString .="$var=".urlEncode($value)."&";
		}
		$returnedVars[$var]=$value;
	}
	
	dbConnect($dbA);
	
	$gatewayOptions = retrieveGatewayOptions("NOCHEX");
	
	$orderID = makeInteger(@$returnedVars["xOid"]) - retrieveOption("orderNumberOffset");
	$newOrderID = makeSafe(@$returnedVars["xOid"]);

	$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
	if ($dbA->count($result) == 0) {
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
	}
	$orderArray = $dbA->fetch($result);
	$randID = $orderArray["randID"];
	if (md5($randID) != @$returnedVars["xRn"]) {
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
	}
	switch ($orderArray["status"]) {
		case "N":
		case "F":
			$dt=date("YmdHis");
			$authResponse="Gateway=Nochex&Status=Payment Confirmed";
			$dbA->query("update $tableOrdersHeaders set status=\"P\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
			//ok, this is where we should do the stock control then.
			include("process/paidProcessList.php");			
			doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
			break;
		default:
			doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
			break;
	}
?>
