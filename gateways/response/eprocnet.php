<?php
	include("../../static/config.php");
	include("../../routines/dbAccess_mysql.php");
	include("../../routines/tSys.php");
	include("../../routines/general.php");
	include("../../routines/stockControl.php");
	include("../../routines/emailOutput.php");

	$getString = "";
	$returnedVars = "";
	foreach ($HTTP_POST_VARS as $var => $value) {	    		
		$returnedVars[$var]=$value;
	}
	foreach ($HTTP_GET_VARS as $var => $value) {	    		
		$returnedVars[$var]=$value;
	}
	dbConnect($dbA);
	
	$gatewayOptions = retrieveGatewayOptions("EPROCNET");
	
	if ($returnedVars["eproc"] != "N" && @$returnedVars["eproc"] != "Y") {
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
		exit;
	}

	$orderID = makeInteger($returnedVars["ID"]) - retrieveOption("orderNumberOffset");
	$newOrderID = makeSafe($returnedVars["ID"]);

	$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
	if ($dbA->count($result) == 0) {
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
	}
	$orderArray = $dbA->fetch($result);
	$randID = $orderArray["randID"];
	switch ($orderArray["status"]) {
		case "N":
		case "F":
			$dt=date("YmdHis");
			switch ($returnedVars["eproc"]) {
				case "Y":
					$authResponse="Gateway=eProcessingNetwork&Auth Response=".@$returnedVars["auth_response"]."&AVS Response=".@$returnedVars["avs_response"]."&cvv2 Response=".@$returnedVars["acvv2_response"]."&Status=Payment Confirmed";
					$dbA->query("update $tableOrdersHeaders set status=\"P\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
					//ok, this is where we should do the stock control then.
					include("process/paidProcessList.php");			
					doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
					break;
				default:
					$authResponse="Gateway=eProcessingNetwork&Auth Response=".@$returnedVars["auth_response"]."&AVS Response=".@$returnedVars["avs_response"]."&cvv2 Response=".@$returnedVars["acvv2_response"]."&Status=Payment Failed";
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
