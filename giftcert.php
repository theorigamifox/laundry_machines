<?php
	include("static/includeBase_front.php");
	include("routines/emailOutput.php");
	include("routines/stockControl.php");
	include("routines/Xtea.php");
	include("routines/giftCerts.php");
	include("routines/fieldValidation.php");
	
	$crypt = new Crypt_Xtea();

	dbConnect($dbA);

	$inCheckoutPhase = true;

	$xFrom = "";
	$xCmd = makeSafe(getFORM("xCmd"));
	$orderingGiftCertificate = true;	
	if ($xCmd == "") {
		$orderInfoArray = "";
		$orderString = commitOrderInformation();
		$pageType="giftcertenter";
		$thisTemplate = "giftcertificate.html";
		include("routines/cartOutputData.php");		
		$tpl->showPage();
		$dbA->close();
		exit;	
	}

	if ($xCmd == "login") {
		if (makeSafe(getFORM("xEX"))!="bypass") {
			//check the gift cert's details here.
			$orderInfoArray["error"] = "N";
			$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='G' and visible=1 order by position");
			for ($f = 0; $f < count($fieldList); $f++) {
				$thisField = chop(makeSafe(getFORM($fieldList[$f]["fieldname"])));
				if ($fieldList[$f]["validation"] == 1 && !validateIndividual($thisField,$fieldList[$f]["validationType"],$fieldList[$f]["regex"])) {
					if ($fieldList[$f]["fieldname"] == "certEmail") {
						if (getFORM("sendPostal") != "Y") {
							$orderInfoArray["error"] = "Y";
							$orderInfoArray[$fieldList[$f]["fieldname"]."_error"] = "Y";
						}
					} else {
						$orderInfoArray["error"] = "Y";
						$orderInfoArray[$fieldList[$f]["fieldname"]."_error"] = "Y";
					}
				}
				$orderInfoArray[$fieldList[$f]["fieldname"]] = $thisField;
			}
			$theAmount = makeInteger(@$orderInfoArray["certValue"]);
			if ($theAmount < 1) {
				$orderInfoArray["certValue"] = "0";
				$orderInfoArray["certValue_error"] = "Y";
				$orderInfoArray["error"] = "Y";
			}
			if (getFORM("sendPostal") != "") {
				$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='D' and visible=1 and internalOnly=0 and incOrdering=1 order by position,fieldID");
				$error = validateFields($fieldList,$orderInfoArray);
			}
			if ($orderInfoArray["error"] == "Y") {
				$pageType="giftcertenter";
				$thisTemplate = "giftcertificate.html";
				include("routines/cartOutputData.php");		
				$tpl->showPage();
				$dbA->close();
				exit;
			}
			$orderString = commitOrderInformation();
		}						
		//ok this is step one.
		if ($jssCustomer != "" && $customerMain["loggedin"] == "Y") {
			$orderInfoArray = retrieveOrderInformation();
			foreach($customerMain as $k=>$v) {
				$orderInfoArray[$k] = stripslashes($v);
			}
			$orderString = commitOrderInformation();
			$xCmd = "s2";
		} else {
			dbConnect($dbA);
			$orderInfoArray = retrieveOrderInformation();
			$orderInfoArray["country"] = retrieveOption("defaultCountry");
			$orderString = commitOrderInformation();
			if (retrieveOption("customerAccounts") == 0) {
				$orderString = commitOrderInformation();
				$xCmd = "s1";
			} else {
				$pageType="checkoutlogin";
				$thisTemplate = "checkoutlogin.html";
				$xFwd = urlencode("giftcert.php?xCmd=login2");
				include("routines/cartOutputData.php");		
				$tpl->showPage();
				$dbA->close();
				exit;		
			}
		}
	}
	if ($xCmd == "login2") {
		if ($jssCustomer != "" && $customerMain["loggedin"] == "Y") {
			$orderInfoArray = retrieveOrderInformation();
			foreach($customerMain as $k=>$v) {
				$orderInfoArray[$k] = stripslashes($v);
			}
			$orderString = commitOrderInformation();
			$xCmd = "s2";
		} else {
			dbConnect($dbA);
			$orderInfoArray = retrieveOrderInformation();
			$orderInfoArray["country"] = retrieveOption("defaultCountry");
			$orderString = commitOrderInformation();
			if (retrieveOption("customerAccounts") == 0) {
				$orderString = commitOrderInformation();
				$xCmd = "s1";
			} else {
				$pageType="checkoutlogin";
				$thisTemplate = "checkoutlogin.html";
				$xFwd = urlencode("giftcert.php?xCmd=login2");
				include("routines/cartOutputData.php");		
				$tpl->showPage();
				$dbA->close();
				exit;		
			}
		}
	}	
	$inOrderProcessing = true;
	$orderInfoArray = retrieveOrderInformation();
	if ($xCmd == "s1") {
		if (@$orderInfoArray["orderID"] != "") {
			doRedirect(configureURL("index.php"));
		}
		$xFrom = chop(makeSafe(getFORM("xFrom")));
		dbConnect($dbA);
		$pageType="checkoutstep1";
		$thisTemplate = "giftcheckoutstep1.html";
		if ($xFrom == "s4") {
			$orderInfoArray = retrieveOrderInformation();
			$xFrom = "s4f";
		} else {
			$orderInfoArray = retrieveOrderInformation();
			$orderInfoArray["country"] = retrieveOption("defaultCountry");
			$orderInfoArray["loggedin"] = "N";
			$orderString = commitOrderInformation();
			$xFrom = "s1";
		}
		include("routines/cartOutputData.php");		
		$tpl->showPage();
		$dbA->close();
		exit;
	}	
	if ($xCmd == "s2") {
		if (@$orderInfoArray["orderID"] != "") {
			doRedirect(configureURL("index.php"));
		}
		@dbConnect($dbA);
		$xFrom = chop(makeSafe(getFORM("xFrom")));
		$orderInfoArray = retrieveOrderInformation();
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='C' and visible=1 and internalOnly=0 and incOrdering=1");
		if ($xFrom == "s1" || $xFrom == "s4f") {
			//ok this customer doesn't have an account so we get what's been posted.
			$orderInfoArray["error"] = "N";
			if ($orderInfoArray["loggedin"] == "N") {
				$theEmail = chop(makeSafe(getFORM("xEmailAddress")));
				$orderInfoArray["email"] = $theEmail;
				if (!validateIndividual($theEmail,"Email Address","")) {
					$orderInfoArray["error"] = "Y";
					$orderInfoArray["email_error"] = "Y";
				}
			}
			$error = validateFields($fieldList,$orderInfoArray);
			$orderInfoArray["newsletter"] = makeSafe(getFORM("xNewsletter"));
			if ($orderInfoArray["error"] == "N") {
				$orderString = commitOrderInformation();
			} else {
				//got to show this page again!
				$pageType = "checkoutstep1.html";
				$thisTemplate = "giftcheckoutstep1.html";
				$xFrom = "s1";
				include("routines/cartOutputData.php");
				$tpl->showPage();
				$dbA->close();
				exit;
			}
		}
		if ($xFrom == "") {
			//this is an account customer so the first thing we need to do is grab their details from the database and put into the order info array
			$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='C' and visible=1 and internalOnly=0 and incOrdering=1");
			for ($f = 0; $f < count($fieldList); $f++) {
				$orderInfoArray[$fieldList[$f]["fieldname"]] = $customerMain[$fieldList[$f]["fieldname"]];
			}
			$orderInfoArray["loggedin"] = "Y";
			$orderInfoArray["customerID"] = $jssCustomer;
			$orderInfoArray["email"] = $customerMain["email"];
			$orderString = commitOrderInformation();
		}
		if ($xFrom == "s4f") {
			$pageType="checkoutstep4";
			$thisTemplate = "giftcheckoutstep4.html";
		} else {
			$pageType="checkoutstep3";
			$thisTemplate = "giftcheckoutstep3.html";
		}
		if ($xFrom == "s4") {
			$xFrom = "s4f";
		} else {
			$xFrom = "s2";	
		}
		include("routines/cartOutputData.php");	
		$tpl->showPage();
		$dbA->close();
		exit;
	}
	if ($xCmd == "s3") {
		dbConnect($dbA);
		$orderInfoArray = retrieveOrderInformation();
		$xFrom = chop(makeSafe(getFORM("xFrom")));
		//time to update the shipping address
		$xType = chop(makeSafe(getFORM("xType")));
		$xAid = makeInteger(getFORM("xAid"));
		if ($xFrom == "s4f") {
			$pageType = "checkoutstep4";
			$thisTemplate = "giftcheckoutstep4.html";
		} else {
			$pageType="checkoutstep3";
			$thisTemplate = "giftcheckoutstep3.html";
		}
		if ($xFrom == "s4") {
			$xFrom = "s3";
			if (strtolower(makeSafe(getFORM("xError"))) == "cc") {
				$orderInfoArray["ccDeclined"] = "Y";
			}
		} else {
			$xFrom = "s3";
		}
		include("routines/cartOutputData.php");		
		$tpl->showPage();
		$dbA->close();
		exit;	
	}
	if ($xCmd == "s4") {
		if (@$orderInfoArray["orderID"] != "") {
			doRedirect(configureURL("index.php"));
		}
		//this is the final confirmation page, off buttons to go back and forwards etc.
		$xFrom = chop(makeSafe(getFORM("xFrom")));
		dbConnect($dbA);
		$orderInfoArray = retrieveOrderInformation();
		$linkMethod = "HTTPS";
		if ($xFrom == "s3" || $xFrom == "s2") {
			$xPaymentID = makeInteger(getFORM("xPaymentID"));
			if (makeInteger($xPaymentID) < 1) {
				$pageType = "checkoutstep3";
				$thisTemplate = "giftcheckoutstep3.html";
				$xFrom = "s3";
				include("routines/cartOutputData.php");
				$tpl->showPage();
				$dbA->close();
				exit;
			}
			$orderInfoArray["paymentID"] = $xPaymentID;
			$payResult = $dbA->query("select * from $tablePaymentOptions where paymentID=$xPaymentID");
			$payRecord = $dbA->fetch($payResult);
			if ($xPaymentID == 1) {
				//this is a credit card payment
				$ccResult = $dbA->query("select * from $tableCCProcessing where gateway='".$payRecord["gateway"]."'");
				$ccRecord = @$dbA->fetch($ccResult);
				if ($ccRecord["askCC"] == "Y") {
					$ccNumber = makeSafe(getFORM("ccNumber"));
					$ccName = makeSafe(getFORM("ccName"));
					$ccExpiryDate = makeSafe(getFORM("ccExpiryDate"));
					$ccStartDate = makeSafe(getFORM("ccStartDate"));
					$ccType = makeSafe(getFORM("ccType"));
					$ccIssue = makeSafe(getFORM("ccIssue"));
					$ccCVV = makeSafe(getFORM("ccCVV"));
					include("routines/cardCheck.php");
					$ccc = new cardCheck($ccNumber,$ccType,$ccExpiryDate,$ccStartDate,$ccIssue,$ccName);
					$orderInfoArray["ccNumber"] = $ccc->ccNumber;
					$orderInfoArray["ccName"] = $ccc->ccName;
					$orderInfoArray["ccExpiryDate"] = $ccc->ccExpiry;
					$orderInfoArray["ccStartDate"] = $ccc->ccStart;
					$orderInfoArray["ccType"] = $ccc->ccType;
					$orderInfoArray["ccIssue"] = $ccc->ccIssue;
					$orderInfoArray["ccCVV"] = $ccCVV;
					$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='CC' and visible=1 order by position,fieldID");
					$orderInfoArray["error"] = "N";
					$error = validateFields($fieldList,$orderInfoArray);
					if ($ccc->isValid() == false || $orderInfoArray["error"] == "Y") {
						if (!$ccc->validNumber) { $orderInfoArray["ccNumber_error"] = "Y"; }
						if (!$ccc->validExpiry) { $orderInfoArray["ccExpiryDate_error"] = "Y"; }
						if (!$ccc->validStart) { $orderInfoArray["ccStartDate_error"] = "Y"; }
						if (!$ccc->validName) { $orderInfoArray["ccName_error"] = "Y"; }
						if (!$ccc->validIssue) { $orderInfoArray["ccIssue_error"] = "Y"; }
						$pageType = "checkoutstep3";
						$thisTemplate = "giftcheckoutstep3.html";
						$xFrom = "s3";
						include("routines/cartOutputData.php");
						$tpl->showPage();
						$dbA->close();
						exit;
					} else {
						$orderString = commitOrderInformation();
					}
				} else {
					$orderString = commitOrderInformation();
				}
			} else {
				$orderString = commitOrderInformation();
			}
		} else {
			$xPaymentID = @$orderInfoArray["paymentID"];
		}
		$pageType="checkoutstep4";
		$thisTemplate = "giftcheckoutstep4.html";
		$xFrom = "s4";
		include("routines/cartOutputData.php");		
		$tpl->showPage();
		$dbA->close();
		exit;
	}
	if ($xCmd == "s5") {
		//this would be where we would save the order and
		//forward the customer to a payment gateway (if that's what they've chosen).
		//for now it just saves the order and shows the confirmation.
		dbConnect($dbA);
		$orderInfoArray = retrieveOrderInformation();
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='O' and visible=1 and internalOnly=0 order by position,fieldID");
		$addressRecord = null;
		$orderInfoArray["error"] = "N";
		$authOrderTotal = $orderInfoArray["certValue"];
		$error = validateFields($fieldList,$orderInfoArray);
		if ($orderInfoArray["error"] == "Y") {
			$result = $dbA->query("UNLOCK TABLES");
			$pageType = "checkoutstep4";
			$thisTemplate = "giftcheckoutstep4.html";
			$xFrom = "s5";
			include("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;
		}	
		$rArray = null;
		$orderInfoArray = retrieveOrderInformation();
		if (@$orderInfoArray["orderID"] != "") {
			$orderID = $orderInfoArray["orderID"];
			$newOrderID = $orderID + retrieveOption("orderNumberOffset");
			$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
			$orderArray = $dbA->fetch($result);
			$randID = $orderArray["randID"];
			doRedirect(configureURL("process.php?xOid=$newOrderID&xRn=$randID"));
			exit;			
		}
		$rArray[] = array("paymentID",@ $orderInfoArray["paymentID"],"N");
		$result = $dbA->query("select * from $tablePaymentOptions where paymentID=".$orderInfoArray["paymentID"]);
		if ($dbA->count($result) != 0) {
			$payRecord = $dbA->fetch($result);
			$paymentName = $payRecord["name"];
			$paymentNameNative = findCorrectLanguage($payRecord,"name");
		} else {
			$paymentName = "";
			$paymentNameNative = "";
		}
		$currentStatus = "N";
		if ($orderInfoArray["paymentID"] == 1) {
			$ccResult = $dbA->query("select * from $tableCCProcessing where gateway='".$payRecord["gateway"]."'");
			$ccRecord = @$dbA->fetch($ccResult);
			if ($ccRecord["askCC"] == "Y" && $ccRecord["gateway"] != "OFFLINE") {
				//this is where we would do the processing for server -> server connections
				include("gateways/".strtolower($payRecord["gateway"]).".php");
				$ccResponse = startProcessor();
				if ($ccResponse == false) {		
					doRedirect(configureURL("giftcert.php?xCmd=s3&xFrom=s4&xError=cc"));
					exit;
				} else {
					$rArray[] = array("authInfo",$orderInfoArray["authInformation"],"S");
					$currentStatus = "P";
					$dt=date("YmdHis");
					$rArray[] = array("paymentDate",$dt,"S");
				}
			}
		}
		
		$rArray[] = array("status",$currentStatus,"S");
		$rArray[] = array("paymentName",$paymentName,"S");
		$rArray[] = array("customerID",@$orderInfoArray["customerID"],"N");
		$rArray[] = array("giftCertOrder","Y","S");
		$rArray[] = array("email",@$orderInfoArray["email"],"S");
		$rArray[] = array("ip",@$_SERVER["REMOTE_ADDR"],"S");
		
		srand((double)microtime()*1000000);
		$randID = rand();
		
		$rArray[] = array("randID",$randID,"S");
		
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='C' and visible=1 and internalOnly=0 and incOrdering=1");
		for ($f = 0; $f < count($fieldList); $f++) {
			if ($fieldList[$f]["fieldname"] == "country") {
				$rArray[] = array($fieldList[$f]["fieldname"],retrieveCountry(@$orderInfoArray[$fieldList[$f]["fieldname"]]),"S");
			} else {
				$rArray[] = array($fieldList[$f]["fieldname"],@$orderInfoArray[$fieldList[$f]["fieldname"]],"S");
			}
		}
		if ($orderInfoArray["sendPostal"] == "Y") {
			$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='D' and visible=1 and internalOnly=0 and incOrdering=1");
			for ($f = 0; $f < count($fieldList); $f++) {
				if ($fieldList[$f]["fieldname"] == "deliveryCountry") {
					$rArray[] = array($fieldList[$f]["fieldname"],retrieveCountry(@$orderInfoArray[$fieldList[$f]["fieldname"]]),"S");
				} else {
					$rArray[] = array($fieldList[$f]["fieldname"],@$orderInfoArray[$fieldList[$f]["fieldname"]],"S");
				}
			}
		}
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='O' and visible=1 and internalOnly=0");
		for ($f = 0; $f < count($fieldList); $f++) {
			$rArray[] = array($fieldList[$f]["fieldname"],makeSafe(getFORM($fieldList[$f]["fieldname"])),"S");
		}		
		

		if (@$orderInfoArray["ccNumber"] != "") {
			@$orderInfoArray["ccNumber"] = base64_encode($crypt->encrypt($orderInfoArray["ccNumber"], $teaEncryptionKey));
		}
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='CC'");
		for ($f = 0; $f < count($fieldList); $f++) {
			$rArray[] = array($fieldList[$f]["fieldname"],@$orderInfoArray[$fieldList[$f]["fieldname"]],"S");
		}
		
		$rArray[] = array("currencyID",$orderInfoArray["certCurrency"],"N");
		$rArray[] = array("datetime",date("YmdHis"),"S");
		
		if (@$orderInfoArray["newsletter"] == "Y") {
			$result = $dbA->query("insert into $tableNewsletter (emailaddress) VALUES(\"".$orderInfoArray["email"]."\")");
		}
		

		$goodsTotal = $orderInfoArray["certValue"];
		$shippingTotal = 0;
		$taxTotal = 0;
		$shippingTotalGoods = 0;
		$shippingTotalWeight = 0;
		$shippingTotalQty = 0;		
		$discountAmount = 0;
		
		$rArray[] = array("goodsTotal",$goodsTotal,"D");
		$rArray[] = array("discountTotal",0,"D");
		$rArray[] = array("shippingTotal",0,"D");
		$rArray[] = array("taxTotal",0,"D");
		$rArray[] = array("giftCertTotal",0,"D");
		$rArray[] = array("languageID",$cartMain["languageID"],"N");
		$rArray[] = array("accTypeID",$cartMain["accTypeID"],"N");
		$rArray[] = array("referURL",$cartMain["referURL"],"S");
		$rArray[] = array("affiliateID",$cartMain["affiliateID"],"S");
		
		$shippingID = 0;
		$shippingMethod = "";
		
		$rArray[] = array("shippingID",@makeInteger(@$orderInfoArray["shippingID"]),"S");
		$rArray[] = array("shippingMethod",$shippingMethod,"S");
		
		$dbA->insertRecord($tableOrdersHeaders,$rArray,0);
		
		$orderID = $dbA->lastID();
		
		$zArray = "";
		$zArray[] = array("orderID",$orderID,"N");
		$zArray[] = array("productID",0,"N");
		$zArray[] = array("code","GIFTCERT","S");
		$zArray[] = array("name","Gift Certificate","S");
		$zArray[] = array("nameNative","Gift Certificate","S");
		$zArray[] = array("qty",1,"N");
		$zArray[] = array("weight",0,"D");
		$zArray[] = array("price",$orderInfoArray["certValue"],"D");
		$dbA->insertRecord($tableOrdersLines,$zArray,0);
		

		//GIFT CERTIFICATE CREATION
		$zArray = "";
		$zArray[] = array("certSerial",getCertID(),"S");
		$zArray[] = array("orderID",$orderID,"N");
		if ($orderInfoArray["sendPostal"] == "Y") {
			$zArray[] = array("type","P","S");
		} else {
			$zArray[] = array("type","E","S");
		}
		$zArray[] = array("certValue",$orderInfoArray["certValue"],"D");
		$zArray[] = array("currencyID",$orderInfoArray["certCurrency"],"N");
		$zArray[] = array("expiryDate","N","S");
		$zArray[] = array("status","N","S");
		$zArray[] = array("fromname",$orderInfoArray["fromname"],"S");
		$zArray[] = array("toname",$orderInfoArray["toname"],"S");
		$zArray[] = array("message",$orderInfoArray["message"],"S");
		$zArray[] = array("emailaddress",$orderInfoArray["certEmail"],"S");
		if ($orderInfoArray["sendPostal"] == "Y") {
			$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='D' and visible=1 and internalOnly=0 and incOrdering=1");
			for ($f = 0; $f < count($fieldList); $f++) {
				if ($fieldList[$f]["fieldname"] == "deliveryCountry") {
					$zArray[] = array($fieldList[$f]["fieldname"],retrieveCountry(@$orderInfoArray[$fieldList[$f]["fieldname"]]),"S");
				} else {
					$zArray[] = array($fieldList[$f]["fieldname"],@$orderInfoArray[$fieldList[$f]["fieldname"]],"S");
				}
			}
		}		
		$dbA->insertRecord($tableGiftCertificates,$zArray,0);
		
		$inOrderProcessing = false;
		$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
		$orderArray = $dbA->fetch($result);
		$orderArray["ordernumber"] = $orderArray["orderID"]+retrieveOption("orderNumberOffset");
		$orderArray["orderdate"] = formatDate($orderArray["datetime"]);
		$orderArray["ordertime"] = formatTime(substr($orderArray["datetime"],-6));
		$orderProducts = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersLines where orderID=$orderID order by lineID");
		
		$orderArray["products"] = $orderProducts;
		$extraFieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$orderID order by lineID,extraFieldID");
		$result = $dbA->query("select * from $tablePaymentOptions where paymentID=".$orderArray["paymentID"]);
		$goSendEmail = false;
		if ($dbA->count($result) > 0) {
			$paymentArray = $dbA->fetch($result);
			if ($paymentArray["custConfirmation"] == 1) {
				$goSendEmail = true;
			}
		} else {
			$goSendEmail = true;
		}
		if ($goSendEmail || ($paymentArray["custConfirmation"] == 2 && $orderArray["status"]=="P")) {		
			@sendEmail($orderArray["email"],"","CUSTORDER");
		}
		@sendEmail("COMPANY","","MERCHORDER");
		
		$orderInfoArray = null;
		$orderInfoArray["orderID"] = $orderID;
		$orderString = commitOrderInformation();
		
		$newOrderID = $orderID + retrieveOption("orderNumberOffset");
		$linkMethod = "HTTPS";
		if ($orderArray["paymentID"] == 1) {
			$ccResult = $dbA->query("select * from $tableCCProcessing where gateway='".$payRecord["gateway"]."'");
			$ccRecord = @$dbA->fetch($ccResult);
			if ($ccRecord["linkHTTPS"] == "N") {
				$linkMethod = "HTTP";
			}	
		}
		if ($linkMethod == "HTTP") {
			doRedirect($jssStoreWebDirHTTP."process.php?xOid=$newOrderID&xRn=$randID");
		} else {
			doRedirect($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
		}
		exit;
	}
		


	function retrieveOrderInformation() {
		global $cartMain,$crypt,$teaEncryptionKey;
		$orderInfo = $cartMain["orderInfo"];
		$orderPairs = split("&",$orderInfo);
		$orderArray = null;
		for ($f = 0; $f< count($orderPairs); $f++) {
			$keyValPair = split("=",$orderPairs[$f]);
			if (@$keyValPair[0] != "") {
				@$keyValPair[1] = urlDecode(@$keyValPair[1]);
				$orderArray[$keyValPair[0]] = $keyValPair[1];
			}
		}
		if (@$orderArray["ccNumber"] != "") {
			$orderArray["ccNumber"] = $crypt->decrypt(base64_decode($orderArray["ccNumber"]), $teaEncryptionKey);
		}
		if (@$orderArray["ccCVV"] != "") {
			$orderArray["ccCVV"] = $crypt->decrypt(base64_decode($orderArray["ccCVV"]), $teaEncryptionKey);
		}
		return $orderArray;
	}
	
	function commitOrderInformation() {
		global $cartMain,$orderInfoArray,$tableCarts,$dbA,$cartID,$crypt,$teaEncryptionKey;
		$orderString = "";
		if (is_array($orderInfoArray)) {
			if (@$orderInfoArray["ccNumber"] != "") {
				$orderInfoArray["ccNumber"] = base64_encode($crypt->encrypt($orderInfoArray["ccNumber"], $teaEncryptionKey));
			}
			if (@$orderInfoArray["ccCVV"] != "") {
				$orderInfoArray["ccCVV"] = base64_encode($crypt->encrypt($orderInfoArray["ccCVV"], $teaEncryptionKey));
			}
			foreach($orderInfoArray as $k=>$v) {
				if ($orderString != "") {
					$orderString .= "&";
				}
				$orderString .= $k."=".urlEncode($v);
			}
		}
		$cartMain["orderInfo"] = $orderString;
		$dbA->query("update $tableCarts set orderInfo=\"$orderString\" where cartID=\"$cartID\"");
		return $orderString;
	}
	
	function getCertID() {
		global $dbA,$tableGiftCertificates;
		srand((double)microtime()*1000000);
		$rID = rand();
		$rID = strtoupper(md5($rID));
		$result = $dbA->query("select certSerial from $tableGiftCertificates where certSerial='$rID'");
		while ($dbA->count($result) > 0) {
			$rID = rand();
			$rID = md5($rID);
			$result = $dbA->query("select certSerial from $tableGiftCertificates where certSerial='$rID'");
		}
		return $rID;
	}
?>