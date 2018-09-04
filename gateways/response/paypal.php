<?php
	include("../../static/config.php");
	include("../../routines/dbAccess_mysql.php");
	include("../../routines/tSys.php");
	include("../../routines/general.php");
	include("../../routines/stockControl.php");
	include("../../routines/emailOutput.php");

	if (function_exists("curl_init") == false) {
		echo "curl not installed";
		exit;
	}
	
	$req = "";
	$xOutput = "";
	foreach ($HTTP_POST_VARS as $var => $value) {	    		
		$req .= "&$var=".urlencode($value);
	}
	
	dbConnect($dbA);

	$xPaymentStatus = getFORM("payment_status");
	$transNum = makeSafe(getFORM("invoice"));
	$xTxnID = makeSafe(getFORM("txn_id"));

	$ch = @curl_init();
	@curl_setopt($ch,CURLOPT_POST,1);
	@curl_setopt($ch,CURLOPT_URL,"https://www.paypal.com/cgi-bin/webscr");
	@curl_setopt($ch,CURLOPT_POSTFIELDS,"cmd=_notify-validate".$req);
	@curl_setopt($ch,CURLOPT_TIMEOUT,20);
	@curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	@curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
	$result=@curl_exec($ch);
	@curl_close($ch);

	if ($result != "VERIFIED") {
		//can't verify the POST information, most likely fraudulent so exit.
		echo "fail";
		exit;
	}	
	
	$gatewayOptions = retrieveGatewayOptions("PAYPAL");
	
	$orderID = makeInteger(@$transNum) - retrieveOption("orderNumberOffset");
	$newOrderID = makeSafe(@$transNum);

	$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
	if ($dbA->count($result) == 0) {
		echo "fail";
		exit;
	}
	$orderArray = $dbA->fetch($result);
	$randID = $orderArray["randID"];
	switch ($orderArray["status"]) {
		case "N":
		case "F":
			$dt=date("YmdHis");
			switch ($xPaymentStatus) {
				case "Completed":
					$authResponse="Gateway=PayPal&PayPal Transaction ID=".$xTxnID."&Status=Payment Confirmed";
					$dbA->query("update $tableOrdersHeaders set status=\"P\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
					//ok, this is where we should do the stock control then.
					include("process/paidProcessList.php");			
					echo "success";
					break;
				case "Pending":
					$authResponse="Gateway=PayPal&Status=Pending Confirmation";
					$dbA->query("update $tableOrdersHeaders set status=\"N\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
					//ok, this is where we should do the stock control then.		
					echo "success";
					break;					
				default:
					$authResponse="Gateway=PayPal&Status=Payment Failed&Status=$xPaymentStatus";
					$dbA->query("update $tableOrdersHeaders set status=\"F\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
					sendOrderPaymentEmail($orderID,"MERCHPAYFAIL");
					echo "fail";
					break;
			}
			break;
		default:
			echo "fail";
			break;
	}
?>
