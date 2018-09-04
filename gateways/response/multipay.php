<?php
	include("../../static/config.php");
	include("../../routines/dbAccess_mysql.php");
	include("../../routines/tSys.php");
	include("../../routines/general.php");
	include("../../routines/stockControl.php");
	include("../../routines/emailOutput.php");

	$getString = "";
	$returnedVars = "";
	foreach ($HTTP_GET_VARS as $var => $value) {	    		
		$returnedVars[$var]=$value;
	}
	dbConnect($dbA);
	
	$gatewayOptions = retrieveGatewayOptions("MULTIPAY");
	
	$orderID = makeInteger($returnedVars["mpOrder_ID"]) - retrieveOption("orderNumberOffset");
	$newOrderID = makeSafe($returnedVars["mpOrder_ID"]);

	$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
	if ($dbA->count($result) == 0) {
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
	}
	$orderArray = $dbA->fetch($result);
	$randID = $orderArray["randID"];
	
	$sReq = "mpCheckTrans;.;" . $gatewayOptions["sellerID"] . " ;.;" . $returnedVars["mpOrder_ID"] . ";.;";
	
	$sResult = GetTcpinfo($sReq,"multipay.net", "2229","System ready.");
	$Items = split(";",$sResult);
	if ($Items[0] == "notfound") {
		doRedirect_JavaScript($jssStoreWebDirHTTP."index.php");
	}
	
	switch ($orderArray["status"]) {
		case "N":
		case "F":
			$dt=date("YmdHis");
			switch ($Items[1]) {
				case "B":
					$authResponse="Gateway=Multipay&Status=Payment Confirmed";
					$dbA->query("update $tableOrdersHeaders set status=\"P\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
					//ok, this is where we should do the stock control then.
					include("process/paidProcessList.php");			
					doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
					break;
				case "I":
				case "V":
				case "U":
					//ok, this is where we should do the stock control then.
					doRedirect_JavaScript($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID&xFS=1");
					break;					
				default:
					$authResponse="Gateway=Multipay&Status=Payment Failed";
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
		
	Function GetTcpinfo($psMsg,$IP, $Port, $psFront) {
		// Check to see if a connection is already open
		$Ret = "";
		if (!is_numeric(substr($IP,0,3))){
			$IP = gethostbyname($IP);
		}
		
		if(!isset($mysocket)){
			$mysocket = pfsockopen($IP, $Port, $errno, $errstr, "10");
		}
		if(isset($mysocket)){
			fputs($mysocket,"$psMsg\n");
			while(!feof($mysocket)) {
		 		 $Ret = $Ret . fgets($mysocket,128);
			 }
			  //fclose($mysocket);
		}
		$pos = strpos($Ret,$psFront);
		if ($pos >0){
			$Ret = trim(substr($Ret,$pos + strlen($psFront) +1));
		}
		fclose($mysocket);
		return $Ret;
	}	
?>
