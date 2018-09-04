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
		if ($var != "hash") {
			$getString .="$var=".urlEncode($value)."&";
		}
		$returnedVars[$var]=$value;
	}
	if (array_key_exists("REQUEST_URI",$_SERVER)) {
		$xPageName = @$_SERVER["REQUEST_URI"];
	} else {
		$xPageName = @$_SERVER["SCRIPT_NAME"];
	}
	//$xPageName = $_SERVER["SCRIPT_NAME"];
	$getString = "$xPageName?".$getString;
	
	dbConnect($dbA);
	
	$gatewayOptions = retrieveGatewayOptions("SECPAY");
	
	$digestKey = $gatewayOptions["DigestKey"];
	
	$getString .= $digestKey;

## SNC-WEST63RD ##
        $getString = urldecode($getString);
        $getString = str_replace(" ","+",$getString);
## SNC-WEST63RD ##

	$testHash = md5($getString);
	
	if ($testHash != $returnedVars["hash"]) {
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
		exit;
	}

	$orderID = makeInteger($returnedVars["trans_id"]) - retrieveOption("orderNumberOffset");
	$newOrderID = makeSafe($returnedVars["trans_id"]);

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
			switch ($returnedVars["valid"]) {
				case "true":
					$authResponse="Gateway=Secpay&Authorisation Code=".$returnedVars["auth_code"]."&Status=Payment Confirmed";
					$dbA->query("update $tableOrdersHeaders set status=\"P\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
					//ok, this is where we should do the stock control then.
					include("process/paidProcessList.php");			
					doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
					break;
				default:
					$authResponse="Gateway=Secpay&Status=Payment Failed";
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
