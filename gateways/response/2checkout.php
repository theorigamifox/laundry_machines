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
		$getString .="$var=".urlEncode($value)."&";
		$returnedVars[$var]=$value;
		$counter++;
	}
	dbConnect($dbA);
	if ($counter == 0) {
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
		exit;
	}
	
	$gatewayOptions = retrieveGatewayOptions("2CHECKOUT");
	
	$orderID = makeInteger(@$returnedVars["cart_id"]) - retrieveOption("orderNumberOffset");
	$newOrderID = makeSafe(@$returnedVars["cart_id"]);
	
	$ccProc = @$returnedVars["credit_card_processed"];
	$ordernumber = @$returnedVars["order_number"];
	$theKey = @$returnedVars["key"];
	

	$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
	if ($dbA->count($result) == 0) {
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
		exit;
	}
	$orderArray = $dbA->fetch($result);
	
	$orderTotal = $orderArray["goodsTotal"]+$orderArray["shippingTotal"]+$orderArray["taxTotal"]-$orderArray["discountTotal"]-$orderArray["giftCertTotal"];
	
	$hashString = $gatewayOptions["secretword"].$gatewayOptions["accountNumber"].$ordernumber.number_format($orderTotal,2,'.','');
	
	if (strtoupper(md5($hashString)) != $theKey) {
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
		exit;
	}

	$randID = $orderArray["randID"];
	switch ($orderArray["status"]) {
		case "N":
		case "F":
			$dt=date("YmdHis");
			switch ($ccProc) {
				case "Y":
					$authResponse="Gateway=2checkout&2checkout Order ID=$ordernumber";
					$dbA->query("update $tableOrdersHeaders set status=\"P\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
					//ok, this is where we should do the stock control then.
					include("process/paidProcessList.php");			
					doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
					break;
				default:
					$authResponse="Gateway=2checkout&Status=Payment Failed";
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
