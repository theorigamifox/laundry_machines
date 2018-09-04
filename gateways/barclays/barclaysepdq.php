<?php
	include("../../static/config.php");
	include("../../routines/dbAccess_mysql.php");
	include("../../routines/tSys.php");
	include("../../routines/general.php");
	include("../../routines/stockControl.php");
	include("../../routines/emailOutput.php");

	$retrunedVars["null"] = "null";
	
	//$fp = fopen("output.txt","w");
	//fwrite($fp,"well, it's going in here");
	foreach ($HTTP_POST_VARS as $var => $value) {	    		
		$returnedVars[$var]=$value;
		//fwrite($fp, $var."=".$value."\r\n");
	}
	foreach ($HTTP_GET_VARS as $var => $value) {	    		
		$returnedVars[$var]=$value;
		//fwrite($fp, $var."=".$value."\r\n");
	}
	//fclose($fp);

	dbConnect($dbA);	

	$gatewayOptions = retrieveGatewayOptions("BARCLAYSEPDQ");
	
	$transStatus = @$returnedVars["transactionstatus"];
	$clientid = @$returnedVars["clientid"];
	$ecistatus = @$returnedVars["ecistatus"];

	if ($transStatus == "" || substr($gatewayOptions["clientid"],":".$clientid."|") === FALSE) {
		echo "error - unauthorised access denied";
		exit;
	}

	$splitup = makeSafe(@$returnedVars["oid"]);
	$bits = explode("-",$splitup);
	$orderID = @$bits[0];

	$orderID = makeInteger(@$returnedVars["oid"]) - retrieveOption("orderNumberOffset");
	$newOrderID = makeSafe(@$returnedVars["oid"]);

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
				case "Success":
					$authResponse="Gateway=Barclays ePDQ&Authorisation Message=$transStatus&VbV Response=$ecistatus";
					$dbA->query("update $tableOrdersHeaders set status=\"P\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
					//ok, this is where we should do the stock control then.
					include("../response/process/paidProcessList.php");
					doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
					break;
				default:
					$authResponse="Gateway=Barclays ePDQ&Response Message=$transStatus";
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
