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
	foreach ($HTTP_POST_VARS as $var => $value) {	    		
		$returnedVars[$var]=$value;
		$counter++;
	}
	dbConnect($dbA);
	if ($counter == 0) {
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
		exit;
	}
	
	$gatewayOptions = retrieveGatewayOptions("AUTHORIZESIM");
	
	$orderID = makeInteger(@$returnedVars["x_invoice_num"]) - retrieveOption("orderNumberOffset");
	$newOrderID = makeSafe(@$returnedVars["x_invoice_num"]);
	
	$ccProc = @$returnedVars["x_response_code"];
	$ordernumber = @$returnedVars["x_invoice_num"];

	$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
	if ($dbA->count($result) == 0) {
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
		exit;
	}
	$orderArray = $dbA->fetch($result);
	
	$randID = $orderArray["randID"];
	switch ($orderArray["status"]) {
		case "N":
		case "F":
			$dt=date("YmdHis");
			switch ($ccProc) {
				case "1":
					$authResponse="Gateway=Authorize.net SIM&Auth Code=".$returnedVars["x_auth_code"]."&AVS Result=".$returnedVars["x_avs_code"]."&CVV2 Result=".@$returnedVars["x_cvv2_resp_code"]."&Transaction ID=".$returnedVars["x_trans_id"];
					$dbA->query("update $tableOrdersHeaders set status=\"P\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
					//ok, this is where we should do the stock control then.
					include("process/paidProcessList.php");			
					doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
					break;
				default:
					$authResponse="Gateway=Authorize.net SIM&Status=Payment Failed&Reason=".$returnedVars["x_response_reason_text"]."&Transaction ID=".$returnedVars["x_trans_id"];
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
