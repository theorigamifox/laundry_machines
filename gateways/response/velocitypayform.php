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
	foreach ($HTTP_GET_VARS as $var => $value) {	    		
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

	$VPMessage = @$returnedVars["VPMessage"];
	$VPCrossReference = @$returnedVars["VPCrossReference"];
	$VPTransactionUnique = @$returnedVars["VPTransactionUnique"];
	$VPOrderDesc = @$returnedVars["VPOrderDesc"];

	if ($VPOrderDesc == "" || $VPTransactionUnique == "") {
		echo "error - unauthorised access denied";
		exit;
	}
	
	dbConnect($dbA);
	
	$gatewayOptions = retrieveGatewayOptions("VELOCITYPAYFORM");
	
	if ($VPTransactionUnique != md5($VPOrderDesc.":VP")) {
		echo "error - unauthorised access denied";
		exit;
	}

	$orderID = makeInteger($VPOrderDesc) - retrieveOption("orderNumberOffset");
	$newOrderID = makeSafe($VPOrderDesc);

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
			if (substr($VPMessage,0,9) == "AUTHCODE:") {
				$bits = explode(":",$VPMessage);
				$authResponse="Gateway=Velocity Pay&Cross Reference ID=$VPCrossReference&Authorisation Message=$VPMessage";
				$dbA->query("update $tableOrdersHeaders set status=\"P\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
				//ok, this is where we should do the stock control then.
				include("process/paidProcessList.php");
				doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
			} else {
				$authResponse="Gateway=Velocity Pay&Cross Reference ID=$VPCrossReference&Response Message=$VPMessage";
				$dbA->query("update $tableOrdersHeaders set status=\"F\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
				sendOrderPaymentEmail($orderID,"MERCHPAYFAIL");
				doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
			}
		default:
			doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
			break;
	}
?>
