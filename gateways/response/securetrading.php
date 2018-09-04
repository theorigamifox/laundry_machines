<?php
	include("../../static/config.php");
	include("../../routines/dbAccess_mysql.php");
	include("../../routines/tSys.php");
	include("../../routines/general.php");
	include("../../routines/stockControl.php");
	include("../../routines/emailOutput.php");

	$getString = "";
	$returnedVars = "";
	$counter=0;
	foreach ($HTTP_GET_VARS as $var => $value) {	    		
		$getString .="$var=".urlEncode($value)."&";
		$returnedVars[$var]=$value;
		$counter++;
	}
	foreach ($HTTP_POST_VARS as $var => $value) {	    		
		$getString .="$var=".urlEncode($value)."&";
		$returnedVars[$var]=$value;
		$counter++;
	}	
	dbConnect($dbA);
	if ($counter == 0) {
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
		exit;
	}
	
	$gatewayOptions = retrieveGatewayOptions("SECURETRADING");
	
	$orderID = makeInteger(@$returnedVars["orderref"]) - retrieveOption("orderNumberOffset");
	$newOrderID = makeSafe(@$returnedVars["orderref"]);
	
	$rnd = @$returnedVars["rnd"];
	$streference = @$returnedVars["streference"];
	$stauthcode = @$returnedVars["stauthcode"];
	$stresult = @$returnedVars["stresult"];

	$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
	if ($dbA->count($result) == 0) {
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
		exit;
	}
	$orderArray = $dbA->fetch($result);
	/*if ($rnd != md5($orderArray["randID"])) {
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
		exit;
	}*/
	$randID = $orderArray["randID"];
	switch ($orderArray["status"]) {
		case "N":
		case "F":
			$dt=date("YmdHis");
			switch ($stresult) {
				case "1":
					$authResponse="Gateway=SecureTrading&Auth Code=$stauthcode&SecureTrading Transaction ID=$streference";
					$dbA->query("update $tableOrdersHeaders set status=\"P\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
					//ok, this is where we should do the stock control then.
					include("process/paidProcessList.php");			
					doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
					break;
				default:
					$authResponse="Gateway=SecureTrading&Status=Payment Failed&Response Message=$stauthcode";
					$dbA->query("update $tableOrdersHeaders set status=\"F\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
					sendOrderPaymentEmail($orderID,"MERCHPAYFAIL");
					doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
					break;
			}
			break;
		default:
			doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
			break;
	}
?>
