<?php
	include("../../static/config.php");
	include("../../routines/dbAccess_mysql.php");
	include("../../routines/tSys.php");
	include("../../routines/general.php");
	include("../../routines/stockControl.php");
	include("../../routines/emailOutput.php");

	$getString = "";
	$returnedVars = "";
	$retrunedVars["null"] = "null";
	$counter = 0;
	foreach ($HTTP_POST_VARS as $var => $value) {	    		
		if ($var != "hash") {
			$getString .="$var=".urlEncode($value)."&";
		}
		$returnedVars[$var]=$value;
		$counter++;
	}
	if ($counter==0) {
		echo "error - unauthorised access denied";
		exit;
	}

	$transStatus = @$returnedVars["transStatus"];
	$rawAuthMessage = @$returnedVars["rawAuthMessage"];
	$callbackPW = @$returnedVars["callbackPW"];
	$transId = @$returnedVars["transId"];

	if ($transStatus == "" || $callbackPW == "") {
		echo "error - unauthorised access denied";
		exit;
	}
	
	dbConnect($dbA);
	
	$gatewayOptions = retrieveGatewayOptions("WORLDPAY");
	
	if ($callbackPW != $gatewayOptions["callbackpassword"]) {
		echo "error - unauthorised access denied";
		exit;
	}

	$orderID = makeInteger(@$returnedVars["cartId"]) - retrieveOption("orderNumberOffset");
	$newOrderID = makeSafe(@$returnedVars["cartId"]);

	$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
	if ($dbA->count($result) == 0) {
		echo "error - unauthorised access denied";
		exit;
	}
	$orderArray = $dbA->fetch($result);
	$randID = $orderArray["randID"];
	switch ($orderArray["status"]) {
		case "N":
		case "F":
			$dt=date("YmdHis");
			switch ($transStatus) {
				case "Y":
					$authResponse="Gateway=WorldPay Select Junior&Authorisation Message=$rawAuthMessage&WorldPay Transaction ID=$transId";
					$dbA->query("update $tableOrdersHeaders set status=\"P\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
					//ok, this is where we should do the stock control then.
					include("process/paidProcessList.php");
					doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
					break;
				default:
					$authResponse="Gateway=WorldPay Select Junior&Response Message=$rawAuthMessage";
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
