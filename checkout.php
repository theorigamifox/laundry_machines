<?php
	include("static/includeBase_front.php");
	include("routines/emailOutput.php");
	include("routines/stockControl.php");
	include("routines/Xtea.php");
	include("routines/giftCerts.php");
	include("routines/fieldValidation.php");
	include("routines/orderOperations.php");
	
	
	@ignore_user_abort(true);
	
	$crypt = new Crypt_Xtea();

	dbConnect($dbA);

	$inCheckoutPhase = true;

	$xFrom = "";
	$xCmd = makeSafe(getFORM("xCmd"));
	if ($xCmd == "") {
		doRedirect(configureURL("cart.php"));
	}
	if ($xCmd == "login") {
		dbConnect($dbA);	
		$orderInfoArray = retrieveOrderInformation();	
		cartHasProducts();
		//ok this is step one.
		if ($jssCustomer != "" && $customerMain["loggedin"] == "Y") {
			$orderInfoArray = $customerMain;
			$orderInfoArray["deliveryCountry"] = $orderInfoArray["country"];
			$orderInfoArray["shippingID"] = checkShippingID(retrieveOption("defaultShipping"));
			$orderString = commitOrderInformation();
			$xCmd = "s2";
		} else {
			dbConnect($dbA);
			$orderInfoArray = null;
			$orderInfoArray["country"] = retrieveOption("defaultCountry");
			$orderInfoArray["deliveryCountry"] = retrieveOption("defaultCountry");
			$orderInfoArray["shippingID"] = checkShippingID(retrieveOption("defaultShipping"));
			$orderString = commitOrderInformation();
			if (retrieveOption("customerAccounts") == 0) {
				$orderInfoArray["shippingID"] = checkShippingID(retrieveOption("defaultShipping"));
				$orderString = commitOrderInformation();
				$xCmd = "s1";
			} else {
				$pageType="checkoutlogin";
				$thisTemplate = "checkoutlogin.html";
				$xFwd = urlencode("checkout.php?xCmd=login");
				include("routines/cartOutputData.php");		
				$tpl->showPage();
				$dbA->close();
				exit;		
			}
		}
	}
	$inOrderProcessing = true;
	if (makeSafe(getFORM("xExtra")) == "shipping") {
		//dbConnect($dbA);
		$orderInfoArray = retrieveOrderInformation();
		$newShippingID = makeInteger(getFORM("xShippingSelect"));
		if ($newShippingID > 0) {
			$orderInfoArray["shippingID"] = checkShippingID($newShippingID);
		}
		$orderString = commitOrderInformation();
		//$dbA->close();
	}
	if (makeSafe(getFORM("xExtra")) == "rgc") {
		//dbConnect($dbA);
		$orderInfoArray = retrieveOrderInformation();
		$giftCert = makeSafe(getFORM("xGC"));
		$giftCertList = split("\|",@$orderInfoArray["giftCerts"]);
		$newList = "";
		for ($f = 0; $f < count($giftCertList); $f++) {
			if ($giftCertList[$f] != "") {
				if ($giftCertList[$f] != $giftCert) {
					$newList .= $giftCertList[$f]."|";
				}
			}
		}
		$orderInfoArray["giftCerts"] = $newList;
		$xCmd = "s3";
		$xFrom = "s3";
		$orderString = commitOrderInformation();
		//$dbA->close();
	}
	$orderInfoArray = retrieveOrderInformation();
	if ($xCmd == "s1") {
		dbConnect($dbA);	
		$orderInfoArray = retrieveOrderInformation();
		checkCheckoutCurrency();	
		if (@$orderInfoArray["orderID"] != "") {
			$orderID = $orderInfoArray["orderID"];
			$newOrderID = $orderID + retrieveOption("orderNumberOffset");
			$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
			$orderArray = $dbA->fetch($result);
			$randID = $orderArray["randID"];
			doRedirect(configureURL("process.php?xOid=$newOrderID&xRn=$randID"));
			exit;			
		}
		cartHasProducts();
		$xFrom = chop(makeSafe(getFORM("xFrom")));
		if ($xFrom == "") {
			//ok, let's reset the order info stuff as this is a new order by the looks of things
			//if (makeInteger($orderInfoArray["shippingID"]) < 1) {
				$orderInfoArray["shippingID"] = checkShippingID(retrieveOption("defaultShipping"));
			//}
			$orderInfoArray["country"] = retrieveOption("defaultCountry");
			$orderString = commitOrderInformation();
		}
		$orderTotals = calculateOrderTotals();
		$giftCertTotal = calculateGiftCertTotal(@$orderInfoArray["giftCerts"]);	
		if ($giftCertTotal > $orderTotals["orderTotal"]) {
			$giftCertTotal = $orderTotals["orderTotal"];
		}
		$orderInfoArray["giftCertTotal"] = $giftCertTotal;
		$pageType="checkoutstep1";
		$thisTemplate = "checkoutstep1.html";
		if ($xFrom == "s4") {
			$orderInfoArray = retrieveOrderInformation();
			$xFrom = "s4f";
		} else {
			$orderInfoArray = retrieveOrderInformation();
			$orderInfoArray["country"] = retrieveOption("defaultCountry");
			if (makeInteger(@$orderInfoArray["shippingID"]) < 1) {
				$orderInfoArray["shippingID"] = checkShippingID(retrieveOption("defaultShipping"));
			}
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
		dbConnect($dbA);	
		$orderInfoArray = retrieveOrderInformation();
		checkCheckoutCurrency();
		$orderString = commitOrderInformation();
		if (@$orderInfoArray["orderID"] != "") {
			$orderID = $orderInfoArray["orderID"];
			$newOrderID = $orderID + retrieveOption("orderNumberOffset");
			$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
			$orderArray = $dbA->fetch($result);
			$randID = $orderArray["randID"];
			doRedirect(configureURL("process.php?xOid=$newOrderID&xRn=$randID"));
			exit;			
		}
		cartHasProducts();
		$xFrom = chop(makeSafe(getFORM("xFrom")));
		$orderTotals = calculateOrderTotals();
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='C' and visible=1 and internalOnly=0 and incOrdering=1");
		if (($xFrom == "s1" || $xFrom == "s4f") || ($xFrom == "" && $jssCustomer == 0)) {
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
				$orderInfoArray["shippingID"] = checkShippingID(@$orderInfoArray["shippingID"]);
				$orderString = commitOrderInformation();
			} else {
				//got to show this page again!
				$pageType = "checkoutstep1";
				$thisTemplate = "checkoutstep1.html";
				$xFrom = "s1";
				include("routines/cartOutputData.php");
				$tpl->showPage();
				$dbA->close();
				exit;
			}
		}
		if ($xFrom == "") {
			if ($jssCustomer != 0) {
				//this is an account customer so the first thing we need to do is grab their details from the database and put into the order info array
				$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='C' and visible=1 and internalOnly=0 and incOrdering=1");
				for ($f = 0; $f < count($fieldList); $f++) {
					$orderInfoArray[$fieldList[$f]["fieldname"]] = $customerMain[$fieldList[$f]["fieldname"]];
				}
				$orderInfoArray["loggedin"] = "Y";
				$orderInfoArray["customerID"] = $jssCustomer;
				$orderInfoArray["shippingID"] = checkShippingID(retrieveOption("defaultShipping"));
				$orderInfoArray["email"] = $customerMain["email"];
				$orderString = commitOrderInformation();
			}
		}
		if ($xFrom == "s4f") {
			$pageType="checkoutstep4";
			$thisTemplate = "checkoutstep4.html";
		} else {
			if (retrieveOption("allowShippingAddress") == 0) {
				$orderInfoArray["deliveryName"] = $orderInfoArray["title"]." ".$orderInfoArray["forename"]." ".$orderInfoArray["surname"];
				$orderInfoArray["deliveryCompany"] = @$orderInfoArray["company"];
				$orderInfoArray["deliveryAddress1"] = $orderInfoArray["address1"];
				$orderInfoArray["deliveryAddress2"] = $orderInfoArray["address2"];
				$orderInfoArray["deliveryTown"] = $orderInfoArray["town"];
				$orderInfoArray["deliveryCounty"] = $orderInfoArray["county"];
				$orderInfoArray["deliveryCountry"] = $orderInfoArray["country"];
				$orderInfoArray["deliveryPostcode"] = $orderInfoArray["postcode"];
				$orderInfoArray["deliveryTelephone"] = $orderInfoArray["telephone"];
				$orderInfoArray["shippingID"] = checkShippingID(retrieveOption("defaultShipping"));
				$xFrom = "s1";
				$pageType="checkoutstep3";
				$thisTemplate = "checkoutstep3.html";
				if (retrieveOption("orderingSkipPayment") == 1) {
					$pResult = $dbA->query("select * from $tablePaymentOptions where enabled='Y' and (accTypes like '%;0;%' or accTypes like '%;".$cartMain["accTypeID"].";%') order by position,name");
					$pCount = $dbA->count($pResult);
					if (retrieveOption("giftCertificatesEnabled") == 1) { $pCount++; }
					if (retrieveOption("offerCodesEnabled") == 1) { $pCount++; }
					if ($pCount == 1) {
						if ($dbA->count($pResult) == 1) {
							$pRecord = $dbA->fetch($pResult);
							if ($pRecord["type"] == "CC") {
								$ccResult = $dbA->query("select * from $tableCCProcessing where gateway='".$pRecord["gateway"]."'");
								$ccRecord = @$dbA->fetch($ccResult);
								if ($ccRecord["askCC"] != "Y") {
									$orderInfoArray["paymentID"] = $pRecord["paymentID"];
									$xFrom = "s4f";
								}
							} else {
								$orderInfoArray["paymentID"] = $pRecord["paymentID"];
								$xFrom = "s4f";
							}
						}
					}
				}
				$orderString = commitOrderInformation();
			} else {
				//CHECK THE CUSTOMER ACCOUNT TYPE
				$accID = $cartMain["accTypeID"];
				$allowShipping = TRUE;
				for ($f = 0; $f < count($accTypeArray); $f++) {
					if ($accID == $accTypeArray[$f]["accTypeID"]) {
						if ($accTypeArray[$f]["allowShippingAddress"] == "N") {
							$allowShipping = FALSE;
						}
					}
				}
				if ($allowShipping) {
					$pageType="checkoutstep2";
					$thisTemplate = "checkoutstep2.html";
				} else {
					$orderInfoArray["deliveryName"] = $orderInfoArray["title"]." ".$orderInfoArray["forename"]." ".$orderInfoArray["surname"];
					$orderInfoArray["deliveryCompany"] = @$orderInfoArray["company"];
					$orderInfoArray["deliveryAddress1"] = $orderInfoArray["address1"];
					$orderInfoArray["deliveryAddress2"] = $orderInfoArray["address2"];
					$orderInfoArray["deliveryTown"] = $orderInfoArray["town"];
					$orderInfoArray["deliveryCounty"] = $orderInfoArray["county"];
					$orderInfoArray["deliveryCountry"] = $orderInfoArray["country"];
					$orderInfoArray["deliveryPostcode"] = $orderInfoArray["postcode"];
					$orderInfoArray["deliveryTelephone"] = $orderInfoArray["telephone"];
					$orderInfoArray["shippingID"] = checkShippingID(retrieveOption("defaultShipping"));
					$xFrom = "s1";
					$pageType="checkoutstep3";
					$thisTemplate = "checkoutstep3.html";
					if (retrieveOption("orderingSkipPayment") == 1) {
						$pResult = $dbA->query("select * from $tablePaymentOptions where enabled='Y' and (accTypes like '%;0;%' or accTypes like '%;".$cartMain["accTypeID"].";%') order by position,name");
						$pCount = $dbA->count($pResult);
						if (retrieveOption("giftCertificatesEnabled") == 1) { $pCount++; }
						if (retrieveOption("offerCodesEnabled") == 1) { $pCount++; }
						if ($pCount == 1) {
							if ($dbA->count($pResult) == 1) {
								$pRecord = $dbA->fetch($pResult);
								if ($pRecord["type"] == "CC") {
									$ccResult = $dbA->query("select * from $tableCCProcessing where gateway='".$pRecord["gateway"]."'");
									$ccRecord = @$dbA->fetch($ccResult);
									if ($ccRecord["askCC"] != "Y") {
										$orderInfoArray["paymentID"] = $pRecord["paymentID"];
										$xFrom = "s4f";
									}
								} else {
									$orderInfoArray["paymentID"] = $pRecord["paymentID"];
									$xFrom = "s4f";
								}
							}
						}
					}
					$orderString = commitOrderInformation();
				}
			}
		}
		if ($xFrom == "s4f") {
			$pageType = "checkoutstep4";
			$thisTemplate = "checkoutstep4.html";
		}
		if ($xFrom == "s4") {
			$xFrom = "s4f";
		} else {
			$xFrom = "s2";	
		}
		include("routines/cartOutputData.php");	
		if ($jssCustomer == 0) {
			$tpl->theVariables["ordering"]["addresses"] = null;
		}
		$tpl->showPage();
		$dbA->close();
		exit;
	}
	if ($xCmd == "s3") {
		dbConnect($dbA);
		$orderInfoArray = retrieveOrderInformation();
		checkCheckoutCurrency();
		//$orderInfoArray["shippingID"] = checkShippingID(retrieveOption("defaultShipping"));
		if (@$orderInfoArray["orderID"] != "") {
			$orderID = $orderInfoArray["orderID"];
			$newOrderID = $orderID + retrieveOption("orderNumberOffset");
			$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
			$orderArray = $dbA->fetch($result);
			$randID = $orderArray["randID"];
			doRedirect(configureURL("process.php?xOid=$newOrderID&xRn=$randID"));
			exit;			
		}
		cartHasProducts();
		$orderTotals = calculateOrderTotals();	
		$xFrom = chop(makeSafe(getFORM("xFrom")));
		//time to update the shipping address
		$xType = chop(makeSafe(getFORM("xType")));
		$xAid = makeInteger(getFORM("xAid"));
		if ($xType == "select") {
			//ok, we've selected one here
			if ($xAid == 0) {
				//selected the billing address
				$orderInfoArray["deliveryName"] = $orderInfoArray["title"]." ".$orderInfoArray["forename"]." ".$orderInfoArray["surname"];
				$orderInfoArray["deliveryCompany"] = @$orderInfoArray["company"];
				$orderInfoArray["deliveryAddress1"] = @$orderInfoArray["address1"];
				$orderInfoArray["deliveryAddress2"] = @$orderInfoArray["address2"];
				$orderInfoArray["deliveryTown"] = @$orderInfoArray["town"];
				$orderInfoArray["deliveryCounty"] = @$orderInfoArray["county"];
				$orderInfoArray["deliveryCountry"] = @$orderInfoArray["country"];
				$orderInfoArray["deliveryPostcode"] = @$orderInfoArray["postcode"];
				$orderInfoArray["deliveryTelephone"] = @$orderInfoArray["telephone"];
				$orderInfoArray["xShipAddress"] = $xAid;
				$orderInfoArray["shippingID"] = checkShippingID(@$orderInfoArray["shippingID"]);
				$orderString = commitOrderInformation();
			} else {
				//selected an address book entry
				$result = $dbA->query("select * from $tableCustomersAddresses where addressID=$xAid and customerID=$jssCustomer");
				$addressRecord = $dbA->fetch($result);
				foreach($addressRecord as $k=>$v) {
					$orderInfoArray[$k] = $v;
				}
				$orderInfoArray["xShipAddress"] = $xAid;
				$orderInfoArray["shippingID"] = checkShippingID(@$orderInfoArray["shippingID"]);
				$orderString = commitOrderInformation();
			}
		}
		if ($xType == "new" || $xType == "" || ($xType != "select")) {
			//this is a new address that's been entered
			//dbConnect($dbA);
			//$orderInfoArray = retrieveOrderInformation();
			$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='D' and visible=1 and internalOnly=0 and incOrdering=1 order by position,fieldID");
			$addressRecord = null;
			$orderInfoArray["error"] = "N";
			$error = validateFields($fieldList,$orderInfoArray);
			if ($orderInfoArray["error"] == "Y") {
				$pageType = "checkoutstep2";
				$thisTemplate = "checkoutstep2.html";
				include("routines/cartOutputData.php");
				$tpl->showPage();
				$dbA->close();
				exit;
			} else {
				$orderInfoArray["deliveryNew"] = "Y";
				$orderInfoArray["shippingID"] = checkShippingID(@$orderInfoArray["shippingID"]);
				$orderString = commitOrderInformation();
				$orderInfoArray = retrieveOrderInformation();
			}			
		}
		$orderInfoArray["shippingID"] = checkShippingID(@$orderInfoArray["shippingID"]);
		
		if (retrieveOption("orderingSkipPayment") == 1) {
			$pResult = $dbA->query("select * from $tablePaymentOptions where enabled='Y' and (accTypes like '%;0;%' or accTypes like '%;".$cartMain["accTypeID"].";%') order by position,name");
			$pCount = $dbA->count($pResult);
			if (retrieveOption("giftCertificatesEnabled") == 1) { $pCount++; }
			if (retrieveOption("offerCodesEnabled") == 1) { $pCount++; }
			if ($pCount == 1) {
				if ($dbA->count($pResult) == 1) {
					$pRecord = $dbA->fetch($pResult);
					if ($pRecord["type"] == "CC") {
						$ccResult = $dbA->query("select * from $tableCCProcessing where gateway='".$pRecord["gateway"]."'");
						$ccRecord = @$dbA->fetch($ccResult);
						if ($ccRecord["askCC"] != "Y") {
							$orderInfoArray["paymentID"] = $pRecord["paymentID"];
							$xFrom = "s4f";
						}
					} else {
						$orderInfoArray["paymentID"] = $pRecord["paymentID"];
						$xFrom = "s4f";
					}
				}
			}
		}
		$orderString = commitOrderInformation();

		$orderInfoArray = retrieveOrderInformation();
		if ($xFrom == "s4f") {
			$pageType = "checkoutstep4";
			$thisTemplate = "checkoutstep4.html";
		} else {
			$pageType="checkoutstep3";
			$thisTemplate = "checkoutstep3.html";
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
		dbConnect($dbA);
		checkCheckoutCurrency();	
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
		cartHasProducts();
		//this is the final confirmation page, off buttons to go back and forwards etc.
		$xFrom = chop(makeSafe(getFORM("xFrom")));
		$orderTotals = calculateOrderTotals();
		$orderInfoArray["giftCertTotal"] = $orderTotals["giftCertTotal"];
		$linkMethod = "HTTPS";
		if ($xFrom == "s3" || $xFrom == "s2" || $xFrom == "") {
			$xPaymentID = makeSafe(getFORM("xPaymentID"));
			$giftCertOutstandingValue = $orderTotals["orderTotal"] - $orderTotals["giftCertTotal"];
			if ($xPaymentID == "OFFERCODE") {
				$xOfferCode = makeSafe(getFORM("xOfferCode"));
				$offerResult = offerCodeValid($xOfferCode,$orderTotals);
				if ($offerResult != "OK") {
					$orderInfoArray["offerCodeError"] = $offerResult;
					$pageType = "checkoutstep3";
					$thisTemplate = "checkoutstep3.html";
					$xFrom = "s3";
					include("routines/cartOutputData.php");
					$tpl->showPage();
					$dbA->close();
					exit;
				} else {
					$offerCodeValue = offerCodeAmount($xOfferCode,$orderTotals);
					@$orderInfoArray["offerCode"] = $xOfferCode;
					$orderString = commitOrderInformation();
					$orderInfoArray["offerTotal"] = $offerCodeValue;
					$orderTotals["offerTotal"] = $offerCodeValue;
				}
			}
			if ($xPaymentID == "GIFTCERT") {
				$xGiftCertSerial = makeSafe(getFORM("xGiftCertSerial"));
				$giftResult = giftCertValid($xGiftCertSerial,@$orderInfoArray["giftCerts"]);
				if ($giftResult != "OK") {
					//this isn't a valid gift certificate, reason code is sent back as well
					$orderInfoArray["giftCertificateError"] = $giftResult;
					$pageType = "checkoutstep3";
					$thisTemplate = "checkoutstep3.html";
					$xFrom = "s3";
					include("routines/cartOutputData.php");
					$tpl->showPage();
					$dbA->close();
					exit;					
				} else {
					$giftCertValueLeft = giftCertValueLeft($xGiftCertSerial);
					if ($giftCertValueLeft <= 0) {
						//this certificate doesnt have any value left
						$orderInfoArray["giftCertificateError"] = "USED";
						$pageType = "checkoutstep3";
						$thisTemplate = "checkoutstep3.html";
						$xFrom = "s3";
						include("routines/cartOutputData.php");
						$tpl->showPage();
						$dbA->close();
						exit;							
					} else {
						$giftCertOutstandingValue = $giftCertOutstandingValue - $giftCertValueLeft;
						$giftCertTotal = $orderTotals["giftCertTotal"] + $giftCertValueLeft;
						@$orderInfoArray["giftCerts"] .= "|".$xGiftCertSerial;
						$orderString = commitOrderInformation();
						$orderInfoArray["giftCertTotal"] = $giftCertTotal;
						$orderTotals["giftCertTotal"] = $giftCertTotal;
					}
				}
			}
			if ($orderTotals["giftCertTotal"] > $orderTotals["orderTotal"]) {
				$giftCertTotal = $orderTotals["orderTotal"];
				$orderInfoArray["giftCertTotal"] = $giftCertTotal;
			}
			if (makeInteger($xPaymentID) < 1) {
				if (makeInteger(@$orderInfoArray["paymentID"]) == 0 && ($giftCertOutstandingValue - @$orderInfoArray["offerTotal"]) > 0) {
					$pageType = "checkoutstep3";
					$thisTemplate = "checkoutstep3.html";
					$xFrom = "s3";
					include("routines/cartOutputData.php");
					$tpl->showPage();
					$dbA->close();
					exit;
				}
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
					$orderInfoArray["ccNumber"] = $ccc->ccNumber;
					$orderInfoArray["ccName"] = $ccc->ccName;
					$orderInfoArray["ccExpiryDate"] = $ccc->ccExpiry;
					$orderInfoArray["ccStartDate"] = $ccc->ccStart;
					$orderInfoArray["ccType"] = $ccc->ccType;
					$orderInfoArray["ccIssue"] = $ccc->ccIssue;
					$orderInfoArray["ccCVV"] = $ccCVV;		
					if ($ccc->isValid() == false || $orderInfoArray["error"] == "Y") {
						if (!$ccc->validNumber) { $orderInfoArray["ccNumber_error"] = "Y"; }
						if (!$ccc->validExpiry) { $orderInfoArray["ccExpiryDate_error"] = "Y"; }
						if (!$ccc->validStart) { $orderInfoArray["ccStartDate_error"] = "Y"; }
						if (!$ccc->validName) { $orderInfoArray["ccName_error"] = "Y"; }
						if (!$ccc->validIssue) { $orderInfoArray["ccIssue_error"] = "Y"; }
						$pageType = "checkoutstep3";
						$thisTemplate = "checkoutstep3.html";
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
		$thisTemplate = "checkoutstep4.html";
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
		checkCheckoutCurrency();
		//$result = $dbA->query("LOCK TABLES $tableGiftCertificatesTrans WRITE");
		$orderTotals = calculateOrderTotals();
		$goodsTotal = $orderTotals["goodsTotal"];
		$shippingTotal = $orderTotals["shippingTotal"];
		$taxTotal = $orderTotals["taxTotal"];
		$shippingTotalGoods = $orderTotals["shippingTotalGoods"];
		$shippingTotalWeight = $orderTotals["shippingTotalWeight"];
		$shippingTotalQty = $orderTotals["shippingTotalQty"];		
		$discountAmount = $orderTotals["discountAmount"];
		
		$orderTotal = $goodsTotal+$shippingTotal+$taxTotal-$discountAmount;
		if ($orderTotals["giftCertTotal"] > $orderTotal) {
			$orderTotals["giftCertTotal"] = $orderTotal;
		}
		$authOrderTotal = $orderTotal - $orderTotals["giftCertTotal"];
		if ($orderTotals["giftCertTotal"] < $orderTotals["orderTotal"] && makeInteger(@$orderInfoArray["paymentID"]) == 0) {
			//ok, problemo here, gift certificate value isn't enough and there's no other payment method.
			$pageType = "checkoutstep3";
			$thisTemplate = "checkoutstep3.html";
			$xFrom = "s3";
			include("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;			
		}
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='O' and visible=1 and internalOnly=0 order by position,fieldID");
		$addressRecord = null;
		$orderInfoArray["error"] = "N";
		$error = validateFields($fieldList,$orderInfoArray);
		$orderString = commitOrderInformation();
		if ($orderInfoArray["error"] == "Y") {
			$pageType = "checkoutstep4";
			$thisTemplate = "checkoutstep4.html";
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
					doRedirect(configureURL("checkout.php?xCmd=s3&xFrom=s4&xError=cc"));
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
		$rArray[] = array("paymentNameNative",$paymentNameNative,"S");
		$rArray[] = array("customerID",@$orderInfoArray["customerID"],"N");
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
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='D' and visible=1 and internalOnly=0 and incOrdering=1");
		for ($f = 0; $f < count($fieldList); $f++) {
			if ($fieldList[$f]["fieldname"] == "deliveryCountry") {
				$rArray[] = array($fieldList[$f]["fieldname"],retrieveCountry(@$orderInfoArray[$fieldList[$f]["fieldname"]]),"S");
			} else {
				$rArray[] = array($fieldList[$f]["fieldname"],@$orderInfoArray[$fieldList[$f]["fieldname"]],"S");
			}
		}
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='O' and visible=1 and internalOnly=0");
		for ($f = 0; $f < count($fieldList); $f++) {
			$rArray[] = array($fieldList[$f]["fieldname"],makeSafe(getFORM($fieldList[$f]["fieldname"])),"S");
		}		
		
		//INSERT NEW ADDRESS FOR CUSTOMER
		if ((@$orderInfoArray["customerID"] != "" || @$orderInfoArray["customerID"] != "0") && @$orderInfoArray["deliveryNew"] == "Y") {
			$result = $dbA->query("select * from $tableCustomerFields where type='D' and visible=1 and internalOnly=0 and incOrdering=1 order by position,fieldID");
			$fCount = $dbA->count($result);
			$adArray = null;
			$adArray[] = array("customerID",$jssCustomer,"N");
			$addressRecord = null;
			$addressRecord["error"] = "N";
			for ($f = 0; $f < $fCount; $f++) {
				$fRecord = $dbA->fetch($result);
				$adArray[] = array($fRecord["fieldname"],makeSafe(@$orderInfoArray[$fRecord["fieldname"]]),"S");
			}
			$dbA->insertRecord($tableCustomersAddresses,$adArray,0);
		}
				
		
		
		$checkingString="01234567890 ";
		if (@$orderInfoArray["ccNumber"] != "") {
			@$orderInfoArray["ccNumber"] = base64_encode($crypt->encrypt($orderInfoArray["ccNumber"], $teaEncryptionKey));
		}
		if (@$orderInfoArray["ccCVV"] != "") {
			$invalidChar = false;
			for ($g = 0; $g < strlen($orderInfoArray["ccCVV"]); $g++) {
				$charFound = false;
				for ($h = 0; $h < strlen($checkingString); $h++) {
					if (substr($orderInfoArray["ccCVV"],$g,1) == substr($checkingString,$h,1)) {
						$charFound = true;
					}
				}
				if ($charFound == false) {
					$invalidChar = true;
				}
			}
			if ($invalidChar) {
				@$orderInfoArray["ccCVV"] = base64_encode($crypt->decrypt($orderInfoArray["ccCVV"], $teaEncryptionKey));
			}		
		}
		if ($orderInfoArray["paymentID"] == 1) {
			$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='CC'");
			for ($f = 0; $f < count($fieldList); $f++) {
				$rArray[] = array($fieldList[$f]["fieldname"],@$orderInfoArray[$fieldList[$f]["fieldname"]],"S");
			}
		}
		if (@$orderInfoArray["ccNumber"] != "") {
			$orderArray["ccNumber"] = $crypt->decrypt(base64_decode($orderInfoArray["ccNumber"]), $teaEncryptionKey);
		}
		$rArray[] = array("currencyID",$cartMain["currencyID"],"N");
		$rArray[] = array("datetime",date("YmdHis"),"S");
		
		if (@$orderInfoArray["newsletter"] == "Y") {
			$result = $dbA->query("insert into $tableNewsletter (emailaddress) VALUES(\"".$orderInfoArray["email"]."\")");
		}
		
		$orderTotals = calculateOrderTotals();
		
		$rArray[] = array("goodsTotal",$goodsTotal,"D");
		$rArray[] = array("discountTotal",$discountAmount,"D");
		$rArray[] = array("shippingTotal",$shippingTotal,"D");
		$rArray[] = array("taxTotal",$taxTotal,"D");
		$rArray[] = array("giftCertTotal",$orderTotals["giftCertTotal"],"D");
		$rArray[] = array("languageID",$cartMain["languageID"],"N");
		$rArray[] = array("accTypeID",$cartMain["accTypeID"],"N");
		
		$rArray[] = array("referURL",$cartMain["referURL"],"S");
		
		$rArray[] = array("affiliateID",$cartMain["affiliateID"],"S");
		
		if (@$orderInfoArray["shippingID"] != "") {
			$result = $dbA->query("select * from $tableShippingTypes where shippingID=".$orderInfoArray["shippingID"]);
			if ($dbA->count($result) == 0) {
				$shippingMethod = "";
				$shippingMethodNative = "";
			} else {
				$therecord = $dbA->fetch($result);
				$shippingMethod = $therecord["name"];
				$shippingMethodNative = findCorrectLanguage($therecord,"name");
			}
		} else {
			$shippingMethod = "";
			$shippingMethodNative = "";
		}	
		$rArray[] = array("shippingID",@makeInteger(@$orderInfoArray["shippingID"]),"S");
		$rArray[] = array("shippingMethod",$shippingMethod,"S");
		$rArray[] = array("shippingMethodNative",$shippingMethodNative,"S");
		$rArray[] = array("offerCode",@$orderInfoArray["offerCode"],"S");
		
		
		
		$dbA->insertRecord($tableOrdersHeaders,$rArray,0);
		
		$orderID = $dbA->lastID();
		
		//COOKIE CREATION FOR OFFER CODE
		if (@$orderInfoArray["offerCode"] != "") {
			$pArray = null;
			$pArray[] = array("code",@$orderInfoArray["offerCode"],"S");
			$pArray[] = array("email",@$orderInfoArray["email"],"S");
			$pArray[] = array("orderID",$orderID,"N");
			$pArray[] = array("amount",$orderTotals["offerTotal"],"D");
			$pArray[] = array("date",date("Ymd"),"S");
			$dbA->insertRecord($tableOfferCodesTrans,$pArray,0);
			setcookie("offerCode",@$_COOKIE["offerCode"].@$orderInfoArray["offerCode"].";",time()+60*60*24*365);
		}		
		
		//GIFT CERT BITS
		$availableOrderTotal = $orderTotals["orderTotal"];
		$giftCerts = split("\|",@$orderInfoArray["giftCerts"]);
		for ($f = 0; $f < count($giftCerts); $f++) {
			if ($giftCerts[$f] != "") {
				$thisCertLeft = giftCertValueLeft($giftCerts[$f]);
				if ($availableOrderTotal > $thisCertLeft) {
					$thisAmount = $thisCertLeft;
					$availableOrderTotal = $availableOrderTotal - $thisCertLeft;
				} else {
					$thisAmount = $availableOrderTotal;
					$availableOrderTotal = 0;
				}
				if ($thisAmount > 0) {
					allocateGiftCertificate($giftCerts[$f],$thisAmount,$orderID);
				}
			}
		}
		
		$result = $dbA->query("FLUSH TABLES");
		$result = $dbA->query("UNLOCK TABLES");
		for ($f = 0; $f < count(@$cartMain["products"]); $f++) {
			$zArray = "";
			$zArray[] = array("orderID",$orderID,"N");
			$zArray[] = array("productID",$cartMain["products"][$f]["productID"],"N");
			$zArray[] = array("code",$cartMain["products"][$f]["code"],"S");
			$zArray[] = array("name",$cartMain["products"][$f]["name"],"S");
			$zArray[] = array("nameNative",findCorrectLanguage($cartMain["products"][$f],"name"),"S");
			$zArray[] = array("qty",$cartMain["products"][$f]["qty"],"N");
			$zArray[] = array("weight",$cartMain["products"][$f]["weight"],"D");
			$zArray[] = array("isDigital",$cartMain["products"][$f]["isDigital"],"YN");
			$zArray[] = array("digitalFile",$cartMain["products"][$f]["digitalFile"],"S");
			$zArray[] = array("digitalReg",$cartMain["products"][$f]["digitalReg"],"N");
			//$zArray[] = array("price",roundWithoutCalcPrice($cartMain["products"][$f]["price".$cartMain["currencyID"]]),"D");
			$zArray[] = array("price",$cartMain["products"][$f]["price".$cartMain["currencyID"]],"D");
			$theTax = calculateTax($cartMain["products"][$f]["price".$cartMain["currencyID"]],$cartMain["products"][$f]);
			$zArray[] = array("taxamount",$theTax,"D");
			$zArray[] = array("ooprice",$cartMain["products"][$f]["ooPrice".$cartMain["currencyID"]],"D");
			$theOOTax = calculateTax($cartMain["products"][$f]["ooPrice".$cartMain["currencyID"]],$cartMain["products"][$f]);
			$zArray[] = array("ootaxamount",$theOOTax,"D");
			$zArray[] = array("supplierID",$cartMain["products"][$f]["supplierID"],"N");
			$zArray[] = array("suppliercode",$cartMain["products"][$f]["suppliercode"],"S");
			
			$dbA->insertRecord($tableOrdersLines,$zArray,0);
			$lineID = $dbA->lastID();
			$stockFields = "";
			if (is_array($extraFieldsArray)) {
				for ($g = 0; $g < count($extraFieldsArray); $g++) {
					$thisEFID = @$cartContents[$f]["extrafieldid".$extraFieldsArray[$g]["extraFieldID"]];
					if ($extraFieldsArray[$g]["type"] == "USERINPUT") {
						if ($thisEFID != "") {
									$eArray = null;
									$eArray[] = array("orderID",$orderID,"N");
									$eArray[] = array("lineID",$lineID,"N");
									$eArray[] = array("extraFieldID",$extraFieldsArray[$g]["extraFieldID"],"N");
									$eArray[] = array("extraFieldName",$extraFieldsArray[$g]["name"],"S");
									$eArray[] = array("extraFieldTitle",$extraFieldsArray[$g]["title"],"S");
									$eArray[] = array("exvalID",0,"N");
									$eArray[] = array("content",$thisEFID,"S");
									$eArray[] = array("contentNative",$thisEFID,"S");
									$dbA->insertRecord($tableOrdersExtraFields,$eArray,0);					
									//exit;
						}
					} else {
						$thisEFContent = @$cartContents[$f]["extrafield".$extraFieldsArray[$g]["extraFieldID"]];
						
						$thisEFContentNative = @findCorrectLanguageExtraField($cartContents[$f],"extrafield".$extraFieldsArray[$g]["extraFieldID"]);
						if (!empty($thisEFContent)) {
							$splitsID = explode("|",$thisEFID);
							$splitsContent = explode("|",$thisEFContent);
							$splitsContentNative = explode("|",$thisEFContentNative);
							for ($h = 0; $h < count($splitsID); $h++) {
								if (!empty($splitsID)) {
									$eArray = null;
									$eArray[] = array("orderID",$orderID,"N");
									$eArray[] = array("lineID",$lineID,"N");
									$eArray[] = array("extraFieldID",$extraFieldsArray[$g]["extraFieldID"],"N");
									$eArray[] = array("extraFieldName",$extraFieldsArray[$g]["name"],"S");
									$eArray[] = array("extraFieldTitle",$extraFieldsArray[$g]["title"],"S");
									$eArray[] = array("exvalID",$splitsID[$h],"N");
									$eArray[] = array("content",$splitsContent[$h],"S");
									$eArray[] = array("contentNative",$splitsContentNative[$h],"S");
									$dbA->insertRecord($tableOrdersExtraFields,$eArray,0);
									$stockFields[] = array("extraFieldID"=>$extraFieldsArray[$g]["extraFieldID"],"exvalID"=>$splitsID[$h],"content"=>$splitsContent[$h]);
								}
							}
						}
					}
				}
			}
			if (retrieveOption("stockDeductMode") == 0 || (retrieveOption("stockDeductMode") == 1 && $currentStatus == "P")) {
				alterStock($cartMain["products"][$f]["productID"],$cartMain["products"][$f]["qty"],$stockFields);
			}
			$groupedResult = $dbA->query("select $tableProducts.*,$tableProductsGrouped.qty from $tableProducts,$tableProductsGrouped where $tableProducts.productID=$tableProductsGrouped.groupedID and $tableProductsGrouped.productID=".$cartMain["products"][$f]["productID"]." order by $tableProductsGrouped.position");
			if ($dbA->count($groupedResult) > 0) {
				//we have some grouped products here
				for ($h = 0; $h < $dbA->count($groupedResult); $h++) {
					$groupedRecord = $dbA->fetch($groupedResult);
					$xArray = null;
					$xArray[] = array("orderID",$orderID,"N");
					$xArray[] = array("lineID",$lineID,"N");
					$xArray[] = array("productID",$groupedRecord["productID"],"N");
					$xArray[] = array("code",$groupedRecord["code"],"S");
					$xArray[] = array("name",$groupedRecord["name"],"S");
					$xArray[] = array("nameNative",findCorrectLanguage($groupedRecord,"name"),"S");
					$xArray[] = array("qty",$groupedRecord["qty"],"N");
					$dbA->insertRecord($tableOrdersLinesGrouped,$xArray,0);
					if (retrieveOption("stockDeductMode") == 0 || (retrieveOption("stockDeductMode") == 1 && $currentStatus == "P")) {
						alterStock($groupedRecord["productID"],$cartMain["products"][$f]["qty"]*$groupedRecord["qty"],"");
					}
				}
			}	
		}
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
		if (retrieveOption("suppliersEnabled") == 1 && (retrieveOption("suppliersEmailTiming") == 1 || (retrieveOption("suppliersEmailTiming") == 2 && $orderArray["status"] == "P"))) {
			include("routines/supplierRoutines.php");
			sendSupplierEmails($orderID);
		}
		
		$orderInfoArray = null;
		$orderInfoArray["orderID"] = $orderID;
		$orderString = commitOrderInformation();
		clearCart();
		if (retrieveOption("affiliatesActivated") == 1 && $orderArray["affiliateID"] > 0 && retrieveOption("affiliatesCreatePayment") == "PLACED" && $orderArray["status"]=="N") {
			affiliatesCreatePayment($orderArray);
		}
		if (retrieveOption("affiliatesActivated") == 1 && $orderArray["affiliateID"] > 0 && retrieveOption("affiliatesCreatePayment") == "PAID" && $orderArray["status"]=="P") {
			affiliatesCreatePayment($orderArray);
		}
		
		$newOrderID = $orderID + retrieveOption("orderNumberOffset");
		$linkMethod = "HTTPS";
		$linkPost = false;
		if ($orderArray["paymentID"] == 1) {
			$ccResult = $dbA->query("select * from $tableCCProcessing where gateway='".$payRecord["gateway"]."'");
			$ccRecord = @$dbA->fetch($ccResult);
			if ($ccRecord["linkHTTPS"] == "N") {
				$linkMethod = "HTTP";
			}	
			if ($ccRecord["linkPOST"] == "Y") {
				$linkPost = true;
			}
		}
		switch ($linkPost) {
			case true:
				if ($linkMethod == "HTTP") {
					$redirectLink = $jssStoreWebDirHTTP."process.php";
				} else {
					$redirectLink = $jssStoreWebDirHTTPS."process.php";
				}
		?>
				<HTML>
				<HEAD>
				<TITLE></TITLE>
				</HEAD>
				<BODY>
				<FORM NAME="processForm" METHOD="POST" ACTION="<?php print $redirectLink; ?>">
				<INPUT TYPE=HIDDEN NAME="xOid" VALUE="<?php print $newOrderID; ?>">
				<INPUT TYPE=HIDDEN NAME="xRn" VALUE="<?php print $randID; ?>">
				</FORM>
				<script language="JavaScript">
					document.processForm.submit();
				</script>
				</BODY>
				</HTML>			
		<?php
				exit;
				break;
			case false:
				if ($linkMethod == "HTTP") {
					doRedirect($jssStoreWebDirHTTP."process.php?xOid=$newOrderID&xRn=$randID");
				} else {
					doRedirect($jssStoreWebDirHTTPS."process.php?xOid=$newOrderID&xRn=$randID");
				}
				exit;
				break;
		}
		exit;
	}
	echo "invalid command";	

	function isValidCard($ccField) {
		$checkingString="01234567890 ";
		$invalidChar = false;
		for ($g = 0; $g < strlen($ccField); $g++) {
			$charFound = false;
			for ($h = 0; $h < strlen($checkingString); $h++) {
				if (substr($ccField,$g,1) == substr($checkingString,$h,1)) {
					$charFound = true;
				}
			}
			if ($charFound == false) {
				return true;
			}
		}
		return false;
	}
?>
