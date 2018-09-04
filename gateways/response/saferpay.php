<?php
	include("../../static/config.php");
	include("../../routines/dbAccess_mysql.php");
	include("../../routines/tSys.php");
	include("../../routines/general.php");
	include("../../routines/stockControl.php");
	include("../../routines/emailOutput.php");
	
	dbConnect($dbA);
	
	$gatewayOptions = retrieveGatewayOptions("SAFERPAY");
	
	$execPath = $gatewayOptions["path"];

/* path to your Saferpay configuration */
$confPath = $execPath; /* maybe another path */

/* parse the query string */
parse_str($QUERY_STRING);
rawurldecode($DATA);
rawurldecode($SIGNATURE);

/* command line */
$command = $execPath."saferpay -payconfirm -p $confPath -d \"$DATA\" -s \"$SIGNATURE\"";

/* verify the PayConfirm message */
$fp = popen($command, "r");
$result = fgets($fp, 4096);

echo result;

exit;

	$getString = "";
	$returnedVars = "";
	$retrunedVars["null"] = "null";
	$counter = 0;
	foreach ($HTTP_POST_VARS as $var => $value) {	    		
		$returnedVars[$var]=$value;
		$counter++;
	}
	foreach ($HTTP_GET_VARS as $var => $value) {	    		
		$returnedVars[$var]=$value;
		$counter++;
	}
	if ($counter==0) {
		echo "error - unauthorised access denied";
		exit;
	}
	
	dbConnect($dbA);
	
	$gatewayOptions = retrieveGatewayOptions("SECUREHOSTING");

	$orderID = makeInteger(@$returnedVars["onum"]) - retrieveOption("orderNumberOffset");
	$newOrderID = makeSafe(@$returnedVars["onum"]);
	
	$checkString = makeSafe(@$returnedVars["check"]);
	$shTrans = makeSafe(@$returnedVars["transactionnumber"]);

	$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
	if ($dbA->count($result) == 0) {
		echo "error - unauthorised access denied";
		exit;
	}
	$orderArray = $dbA->fetch($result);	
	
	$myCheck = md5("$newOrderID".$gatewayOptions["shreference"]."YES");
	
	if ($myCheck != $checkString) {
		echo "error - unauthorised access denied";
		exit;
	}
	$randID = $orderArray["randID"];
	switch ($orderArray["status"]) {
		case "N":
		case "F":
			$dt=date("YmdHis");
			switch ($shTrans) {
				case "-1";
					$authResponse="Gateway=SecureHosting&Status=Payment Failed";
					$dbA->query("update $tableOrdersHeaders set status=\"F\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
					sendOrderPaymentEmail($orderID,"MERCHPAYFAIL");
					echo "success";
					break;			
				default:
					$authResponse="Gateway=SecureHosting&SecureHosting Transaction=$shTrans&Status=Payment Confirmed";
					$dbA->query("update $tableOrdersHeaders set status=\"P\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
					//ok, this is where we should do the stock control then.
					include("process/paidProcessList.php");			
					echo "success";
					break;
			}
			break;
		default:
			echo "completed";
			//doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
			break;
	}
?>
