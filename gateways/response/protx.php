<?php
	include("../../static/config.php");
	include("../../routines/dbAccess_mysql.php");
	include("../../routines/tSys.php");
	include("../../routines/general.php");
	include("../../routines/stockControl.php");
	include("../../routines/emailOutput.php");

	dbConnect($dbA);
	
	$orderID = makeSafe(getFORM("xOid"));
	$newOrderID = $orderID;
	$randID = makeSafe(getFORM("xRn"));
	$crypt = makeSafe(getFORM("crypt"));
	
	$gatewayOptions = retrieveGatewayOptions("PROTX");
	
	$orderID = makeInteger($orderID) - retrieveOption("orderNumberOffset");

	$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID and randID='$randID'");
	if ($dbA->count($result) == 0 || $crypt=="") {
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
		exit;
	}
	$orderArray = $dbA->fetch($result);

	$crypt = protx_simpleXor(base64_decode($crypt),$gatewayOptions["encryptionPassword"]);	
	
	$nameValues = split("&",$crypt);
	$resultCode = "";
	for ($f = 0; $f < count($nameValues); $f++) {
		$thisCode = split("=",$nameValues[$f]);
		$resultCode[$thisCode[0]] = $thisCode[1];
	}
	
	if ($resultCode["VendorTxCode"] != $newOrderID) {
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
		exit;
	}
	
	switch ($orderArray["status"]) {
		case "N":
		case "F":
			$dt=date("YmdHis");
			switch ($resultCode["Status"]) {
				case "OK":
					$authResponse="Gateway=Protx&Authorisation Code=".$resultCode["TxAuthNo"]."&Protx Transaction ID=".$resultCode["VPSTxID"]."&Status=Payment Confirmed";
					$dbA->query("update $tableOrdersHeaders set status=\"P\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
					//ok, this is where we should do the stock control then.
					include("process/paidProcessList.php");
					doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
					break;
				default:
					$authResponse="Gateway=Protx&Status=Payment Failed";
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

	function protx_simpleXor($inString, $key) {
		$outString="";
		$l=0;
		if (strlen($inString)!=0) {
			for ($i = 0; $i < strlen($inString); $i++) {
	   			$outString=$outString . ($inString[$i]^$key[$l]);
	   			$l++;
	   			if ($l==strlen($key)) { $l=0; }
			}
		}
		return $outString;
	}
?>
