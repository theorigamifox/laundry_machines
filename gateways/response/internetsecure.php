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
		echo "error";
		exit;
	}
	
	$gatewayOptions = retrieveGatewayOptions("INTERNETSECURE");
	
	$productList = @$returnedVars["DoubleColonProducts"];
	$receiptnumber = @$returnedVars["receiptnumber"];
	$NiceVerbage = @$returnedVars["NiceVerbage"];
	$ApprovalCode = @$returnedVars["ApprovalCode"];
	
	if ($productList == "" | $receiptnumber == "" | $NiceVerbage == "" | $ApprovalCode == "") {
		echo "error";
		exit;
	}
	
	$pSplit = explode("::",$productList);
	
	$orderID = makeInteger(@$pSplit[2]) - retrieveOption("orderNumberOffset");
	$newOrderID = makeSafe(@$pSplit[2]);
	
	if ($newOrderID == "") {
		echo "error";
		exit;
	}
	
	$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
	if ($dbA->count($result) == 0) {
		echo "error";
		exit;
	}
	$orderArray = $dbA->fetch($result);
	$randID = $orderArray["randID"];
	switch ($orderArray["status"]) {
		case "N":
		case "F":
			$dt=date("YmdHis");
			$authResponse="Gateway=InternetSecure&Auth Code=$ApprovalCode&InternetSecure Receipt=$receiptnumber";
			$dbA->query("update $tableOrdersHeaders set status=\"P\", authInfo=\"$authResponse\", paymentDate=\"$dt\" where orderID=$orderID");
			//ok, this is where we should do the stock control then.
			include("process/paidProcessList.php");			
			echo "finished";
			exit;
			break;
		default:
			echo "error";
			exit;
			break;
	}
?>
