<?php
	include("static/includeBase_front.php");

	dbConnect($dbA);
	
	$orderID = makeSafe(getFORM("xOid"));
	$randID = makeSafe(getFORM("xRn"));
	$oid = makeSafe(getFORM("oid"));
	if ($oid != "") {
		$splitup = makeSafe($oid);
		$bits = explode("-",$splitup);
		$oid = makeInteger(@$bits[0]);
		$randID = makeInteger(@$bits[1]);
		$oid = $oid - retrieveOption("orderNumberOffset");
		$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$oid and randID=$randID");
		if ($dbA->count($result) == 0) {
			doRedirect(configureURL("index.php"));
		}
		$record = $dbA->fetch($result);
	}
	$xF = makeSafe(getFORM("xF"));
	if ($orderID == "" || $randID == "") {
		doRedirect(configureURL("index.php"));
	}
	$randID = makeInteger($randID);
	$orderID = makeInteger($orderID);

	$shownOrderID = $orderID;
	
	$orderID = $orderID - retrieveOption("orderNumberOffset");
	
	$inOrderProcessing = false;
	$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID and randID=$randID");
	if ($dbA->count($result) == 0) {
		doRedirect(configureURL("index.php"));
	}
	$orderArray = $dbA->fetch($result);
	$orderArray["ordernumber"] = $orderArray["orderID"]+retrieveOption("orderNumberOffset");
	$orderArray["orderdate"] = formatDate($orderArray["datetime"]);
	$orderArray["ordertime"] = formatTime(substr($orderArray["datetime"],-6));
	$orderProducts = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersLines where orderID=$orderID order by lineID");
	$orderArray["products"] = $orderProducts;
	$extraFieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$orderID order by lineID,extraFieldID");

	$goodsTotal = $orderArray["goodsTotal"];
	$taxTotal = $orderArray["taxTotal"];
	$shippingTotal = $orderArray["shippingTotal"];
	$discountTotal = $orderArray["discountTotal"];
	$giftCertTotal = $orderArray["giftCertTotal"];
	$orderArray["orderTotal"] = $goodsTotal + $taxTotal + $shippingTotal - $discountTotal - $giftCertTotal;

	if (makeSafe(getFORM("xFS")) == 1) {
		$orderArray["status"] = "P";
	}
	if (makeSafe(getFORM("xFF")) == 1) {
		$orderArray["status"] = "F";
	}
	switch ($orderArray["status"]) {
		case "F":
			if ($xF != "Y") {
				$pageType = "orderfailed";
				if (makeSafe(getFORM("xPage")) != "") {
					$thisTemplate = makeSafe(getFORM("xPage"));
				} else {
					$thisTemplate = "orderfailed.html";
				}
				include("routines/cartOutputData.php");
				$tpl->showPage();
				$dbA->close();
				exit;
			}
			break;
		case "P":
		case "D":
		case "I":
			$pageType = "ordersuccess";
			if (makeSafe(getFORM("xPage")) != "") {
				$thisTemplate = makeSafe(getFORM("xPage"));
			} else {
				$thisTemplate = "ordersuccess.html";
			}
			include("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;
	}
	if ($oid != "") {
		$pageType = "ordersuccess";
		$thisTemplate = "ordersuccess.html";
		include("routines/cartOutputData.php");
		$tpl->showPage();
		$dbA->close();
		exit;
	}
	//ok, so this is paid, so we need to find out if it needs paying and, if so, go to the payment gateway.
	$xPaymentID = $orderArray["paymentID"];
	$payResult = $dbA->query("select * from $tablePaymentOptions where paymentID=$xPaymentID");
	$payRecord = @$dbA->fetch($payResult);
	if ($orderArray["orderTotal"] <= 0) {
		include("routines/stockControl.php");
		include("routines/emailOutput.php");
		loopAlterStock($orderID);
		sendConfirmationEmails($orderID,0);
		sendOrderPaymentEmail($orderID,"MERCHPAYCONF");
		if (retrieveOption("affiliatesActivated") == 1 && retrieveOption("affiliatesCreatePayment") == "PAID" && $orderArray["affiliateID"] > 0) {
			include("routines/affiliateTracking.php");
			affiliatesCreatePayment($orderArray);
		}
		if (retrieveOption("downloadsActivate") == 1) {
			include("routines/dispatchRoutines.php");
			autoDispatchDigital($orderID);
		}
		$pageType = "ordersuccess";
		if (makeSafe(getFORM("xPage")) != "") {
			$thisTemplate = makeSafe(getFORM("xPage"));
		} else {
			$thisTemplate = "ordersuccess.html";
		}
		include("routines/cartOutputData.php");
		$tpl->showPage();
		$dbA->close();
		exit;
	}	
	switch (@$payRecord["type"]) {
		case "CC":
			if (@$payRecord["gateway"] == "OFFLINE") {
				$pageType = "ordersuccess";
				if (makeSafe(getFORM("xPage")) != "") {
					$thisTemplate = makeSafe(getFORM("xPage"));
				} else {
					$thisTemplate = "ordersuccess.html";
				}
				include("routines/cartOutputData.php");
				$tpl->showPage();
				$dbA->close();
				exit;				
			}
			$ccResult = $dbA->query("select * from $tableCCProcessing where gateway='".$payRecord["gateway"]."'");
			$ccRecord = @$dbA->fetch($ccResult);
			include("gateways/".strtolower($payRecord["gateway"]).".php");
			startProcessor($shownOrderID);
			exit;
		case "NOCHEX":
			include("gateways/nochex.php");
			startProcessor($shownOrderID);
			exit;
		case "PAYPAL":
			include("gateways/paypal.php");
			startProcessor($shownOrderID);
			exit;
		case "OTHER":
			break;
		default:
			$pageType = "ordersuccess";
			if (makeSafe(getFORM("xPage")) != "") {
				$thisTemplate = makeSafe(getFORM("xPage"));
			} else {
				$thisTemplate = "ordersuccess.html";
			}
			include("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;
	}		
	doRedirect(configureURL("index.php"));

	function templateVarsShopRetrieve() {
		$shop["baseDir"] = findBaseDir("customer.php");
		$shop["home"] = configureURL("index.php");
		return $shop;
	}

	function templateVarsLabelsRetrieve() {
		global $dbA,$tableLabels;
		$labelArray = "";
		$result = $dbA->query("select * from $tableLabels");
		$count = $dbA->count($result);
		for ($f = 0; $f < $count; $f++) {
			$record = $dbA->fetch($result);
			$labelArray[$record["type"]][$record["name"]] = findCorrectLanguage($record,"content");
		}
		return $labelArray;
	}
?>