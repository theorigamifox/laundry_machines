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
	
	$gatewayOptions = retrieveGatewayOptions("MULTICARDS");
	
	if ($gatewayOptions["postpassword"] != $returnedVars["SilentPostPassword"]) {
		exit;
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
	}

	$orderID = makeInteger($returnedVars["user1"]) - retrieveOption("orderNumberOffset");
	$newOrderID = makeSafe($returnedVars["user1"]);
	
	$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
	if ($dbA->count($result) == 0) {
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
	}
	
	$orderArray = $dbA->fetch($result);
	
	$user2 = md5($newOrderID.$gatewayOptions["merchantID"].$orderArray["forename"].$orderArray["surname"]);

	if ($user2 != $returnedVars["user2"]) {
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
	}
	$randID = $orderArray["randID"];
	switch ($orderArray["status"]) {
		case "N":
		case "F":
			$dt=date("YmdHis");
			//switch ($returnedVars["user21"]) {
			//	case "success":
					$authResponse="Gateway=Multicards&Status=Payment Confirmed";
					$dbA->query("update $tableOrdersHeaders set status=\"P\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
					//ok, this is where we should do the stock control then.
					include("process/paidProcessList.php");			
					doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
			//		break;
			//	default:
			//		$authResponse="Gateway=Multicards&Status=Payment Failed";
			//		$dbA->query("update $tableOrdersHeaders set status=\"F\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
			//		sendOrderPaymentEmail($orderID,"MERCHPAYFAIL");
			//		doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
			//		break;
			//}
			break;
		default:
			doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
			break;
	}
?>
