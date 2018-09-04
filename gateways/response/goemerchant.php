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
	
	$gatewayOptions = retrieveGatewayOptions("GOEMERCHANT");
	
	if ($returnedVars["Status"] != "1" && @$returnedVars["Status"] != "0") {
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
		exit;
	}

	$orderID = makeInteger($returnedVars["OrderID"]) - retrieveOption("orderNumberOffset");
	$newOrderID = makeSafe($returnedVars["OrderID"]);

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
			switch ($returnedVars["Status"]) {
				case "1":
					$authResponse="Gateway=goEmerchant&Approval Code=".@$returnedVars["approval_code"]."&Auth Response=".@$returnedVars["authresponse"]."&AVS Response=".@$returnedVars["avs"]."&Status=Payment Confirmed";
					$dbA->query("update $tableOrdersHeaders set status=\"P\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
					//ok, this is where we should do the stock control then.
					include("process/paidProcessList.php");			
					doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
					break;
				default:
					$authResponse="Gateway=goEmerchant&Auth Response=".@$returnedVars["authresponse"]."&AVS Response=".@$returnedVars["avs_response"]."&Status=Payment Failed";
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
