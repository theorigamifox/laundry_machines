<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");
	include("../routines/emailOutput.php");
	include("../routines/stockControl.php");
	include("../routines/tSys.php");
	include("../routines/Xtea.php");
	include("../routines/dispatchRoutines.php");
	$crypt = new Crypt_Xtea();		

	$orderArray = "";
	$dispatchArray = "";

	dbConnect($dbA);

	$recordType = "Order";
	$linkBackLink = urldecode(getFORM("xReturn"));
	
	$xAction = getFORM("xAction");
	$xOrderID = getFORM("xOrderID");
	
	$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"position,name");
	
	if ($xAction == "updatebasket") {
		$result = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersLines where orderID=$xOrderID");
		$resultCount = count($result);
		$oResult = $dbA->query("select * from $tableOrdersHeaders where orderID=$xOrderID");
		if ($dbA->count($oResult) != 0) {
			$oRecord = $dbA->fetch($oResult);
		}
		if (makeDecimal(getFORM("xTax")) != $oRecord["taxTotal"]) {
			$dbA->query("update $tableOrdersHeaders set taxTotal=".makeDecimal(getFORM("xTax"))." where orderID=$xOrderID");
			userLogActionUpdate("Order Tax",$xOrderID+retrieveOption("orderNumberOffset"));
			//does this have any tax implications??
		}
		if (makeDecimal(getFORM("xShipping")) != $oRecord["shippingTotal"]) {
			$newShipping = makeDecimal(getFORM("xShipping"));
			$dbA->query("update $tableOrdersHeaders set shippingTotal=".makeDecimal(getFORM("xShipping"))." where orderID=$xOrderID");
			userLogActionUpdate("Order Shipping",$xOrderID+retrieveOption("orderNumberOffset"));
			$taxRates = retrieveTaxRates($oRecord["country"],$oRecord["county"],$oRecord["deliveryCountry"],$oRecord["deliveryCounty"]);
			if ($newShipping > $oRecord["shippingTotal"]) {
				$shippingDifference = $newShipping - $oRecord["shippingTotal"];
				$taxOnShipping = calculateTax($shippingDifference);
				if (retrieveOption("taxOnShipping") == 1) {
					$dbA->query("update $tableOrdersHeaders set taxTotal = taxTotal + $taxOnShipping where orderID=$xOrderID");
				}
			} else {
				if ($newShipping < $oRecord["shippingTotal"]) {
					$shippingDifference = $oRecord["shippingTotal"] - $newShipping;
					$taxOnShipping = calculateTax($shippingDifference);
					if (retrieveOption("taxOnShipping") == 1) {
						$dbA->query("update $tableOrdersHeaders set taxTotal = taxTotal - $taxOnShipping where orderID=$xOrderID");
					}
				}
			}		
			//what about tax on the shipping?
		}
		if (makeDecimal(getFORM("xDiscount")) != $oRecord["discountTotal"]) {
			$dbA->query("update $tableOrdersHeaders set discountTotal=".makeDecimal(getFORM("xDiscount"))." where orderID=$xOrderID");
			userLogActionUpdate("Order Discount",$xOrderID+retrieveOption("orderNumberOffset"));
			//does this have any tax implications??
		}
		if (getFORM("xShippingMethod") != $oRecord["shippingMethod"]) {
			$dbA->query("update $tableOrdersHeaders set shippingMethod=\"".getFORM("xShippingMethod")."\" where orderID=$xOrderID");
			userLogActionUpdate("Order Shipping Method",$xOrderID+retrieveOption("orderNumberOffset"));
		}			
		if (getFORM("xZeroTax") == "Y") {
			//zero the tax totals here
			$dbA->query("update $tableOrdersHeaders set taxTotal=0 where orderID=$xOrderID");
			//echo "update $tableOrdersHeaders set taxTotal=0 where orderID=$xOrderID";
			for ($f = 0; $f < $resultCount; $f++) {
				$record = $result[$f];
				$dbA->query("update $tableOrdersLines set taxamount=0 where orderID=$xOrderID and lineID=".$record["lineID"]);
			}
			userLogActionUpdate("Order Tax",$xOrderID+retrieveOption("orderNumberOffset"));
		}
		$beenChanged = false;
		$totalAddition = 0;
		$totalTaxAddition = 0;
		for ($f = 0; $f < $resultCount; $f++) {
			$record = $result[$f];
			$thisQty = makeInteger(getFORM("xQty".$record["lineID"]));
			if ($thisQty != $record["qty"] && $thisQty > 0) {
				$dbA->query("update $tableOrdersLines set qty=$thisQty where orderID=$xOrderID and lineID=".$record["lineID"]);
				$beenChanged = true;
				if ($thisQty > $record["qty"]) {
					$qtyDifference = $thisQty - $record["qty"];
					$priceDifference = $qtyDifference * $record["price"];
					$taxDifference = $qtyDifference * $record["taxamount"];
					$totalAddition = $totalAddition + $priceDifference;
					$totalTaxAddition = $totalTaxAddition + $taxDifference;
				}
				if ($thisQty < $record["qty"]) {
					$qtyDifference = $record["qty"] - $thisQty;
					$priceDifference = $qtyDifference * $record["price"];
					$taxDifference = $qtyDifference * $record["taxamount"];
					$totalAddition = $totalAddition - $priceDifference;
					$totalTaxAddition = $totalTaxAddition - $taxDifference;
				}
			}
		}
		if ($beenChanged == true) {
			$dbA->query("update $tableOrdersHeaders set taxTotal=taxTotal+$totalTaxAddition, goodsTotal=goodsTotal+$totalAddition where orderID=$xOrderID");
			userLogActionUpdate("Order Quantities",$xOrderID+retrieveOption("orderNumberOffset"));
		}
		$beenChanged = false;
		$result = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersLines where orderID=$xOrderID");
		$resultCount = count($result);
		for ($f = 0; $f < $resultCount; $f++) {
			$record = $result[$f];
			$thisQty = makeInteger(getFORM("xQty".$record["lineID"]));
			if (getFORM("xRemove".$record["lineID"]) == "Y") {
				$dbA->query("delete from $tableOrdersLines where orderID=$xOrderID and lineID=".$record["lineID"]);
				$beenChanged = true;
				$priceDifference = ($record["qty"] * $record["price"])+$record["ooprice"];
				$taxDifference = ($record["qty"] * $record["taxamount"])+$record["ootaxamount"];
				$totalAddition = $totalAddition + $priceDifference;
				$totalTaxAddition = $totalTaxAddition + $taxDifference;
			}
		}		
		if ($beenChanged == true) {
			$dbA->query("update $tableOrdersHeaders set taxTotal=taxTotal-$totalTaxAddition, goodsTotal=goodsTotal-$totalAddition where orderID=$xOrderID");
			userLogActionUpdate("Order Lines",$xOrderID+retrieveOption("orderNumberOffset"));
		}
		$beenChanged = false;
		$result = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersLines where orderID=$xOrderID");
		$resultCount = count($result);
		for ($f = 0; $f < $resultCount; $f++) {
			$record = $result[$f];
			$thisPrice = makeDecimal(getFORM("xPrice".$record["lineID"]));
			if ($thisPrice != $record["price"]) {
				$dbA->query("update $tableOrdersLines set price=$thisPrice where orderID=$xOrderID and lineID=".$record["lineID"]);
				$beenChanged = true;
				if ($thisPrice > $record["price"]) {
					$priceDifference = $thisPrice - $record["price"];
					$qtyDifference = $priceDifference * $record["qty"];
					$totalAddition = $totalAddition + $priceDifference;
					//$totalTaxAddition = $totalTaxAddition + $taxDifference;
					$totalTaxAddition = 0;
				}
				if ($thisPrice < $record["price"]) {
					$priceDifference = $record["price"] - $thisPrice;
					$qtyDifference = $priceDifference * $record["qty"];
					$totalAddition = $totalAddition - $priceDifference;
					//$totalTaxAddition = $totalTaxAddition - $taxDifference;
					$totalTaxAddition = 0;
				}
			}
		}
		if ($beenChanged == true) {
			$dbA->query("update $tableOrdersHeaders set taxTotal=taxTotal+$totalTaxAddition, goodsTotal=goodsTotal+$totalAddition where orderID=$xOrderID");
			userLogActionUpdate("Order Quantities",$xOrderID+retrieveOption("orderNumberOffset"));
		}		
		$oResult = $dbA->query("select * from $tableOrdersHeaders where orderID=$xOrderID");
		if ($dbA->count($oResult) != 0) {
			$oRecord = $dbA->fetch($oResult);
		}
		$beenChanged = false;
		$result = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersLines where orderID=$xOrderID");
		$resultCount = count($result);
		for ($f = 0; $f < $resultCount; $f++) {
			$record = $result[$f];
			if (getFORM("xOOPrice".$record["lineID"]) != "") {
				$thisPrice = makeDecimal(getFORM("xOOPrice".$record["lineID"]));
				if ($thisPrice != $record["ooprice"]) {
					$dbA->query("update $tableOrdersLines set ooprice=$thisPrice where orderID=$xOrderID and lineID=".$record["lineID"]);
					$beenChanged = true;
					if ($thisPrice > $record["ooprice"]) {
						$priceDifference = $thisPrice - $record["ooprice"];
						$totalAddition = $totalAddition + $priceDifference;
						//$totalTaxAddition = $totalTaxAddition + $taxDifference;
						$totalTaxAddition = 0;
					}
					if ($thisPrice < $record["ooprice"]) {
						$priceDifference = $record["ooprice"] - $thisPrice;
						$qtyDifference = $priceDifference * $record["qty"];
						$totalAddition = $totalAddition - $priceDifference;
						//$totalTaxAddition = $totalTaxAddition - $taxDifference;
						$totalTaxAddition = 0;
					}
				}
			}
		}
		if ($beenChanged == true) {
			$dbA->query("update $tableOrdersHeaders set taxTotal=taxTotal+$totalTaxAddition, goodsTotal=goodsTotal+$totalAddition where orderID=$xOrderID");
			userLogActionUpdate("Order Quantities",$xOrderID+retrieveOption("orderNumberOffset"));
		}		
		$oResult = $dbA->query("select * from $tableOrdersHeaders where orderID=$xOrderID");
		if ($dbA->count($oResult) != 0) {
			$oRecord = $dbA->fetch($oResult);
		}
		doRedirect("orders_edit_basket.php?xOrderID=$xOrderID"."&".userSessionGET());
	}
	
	if ($xAction == "update") {

		$ccNumber = getFORM("ccNumber");
		if ($ccNumber != "") {
			$ccNumber = base64_encode($crypt->encrypt($ccNumber, $teaEncryptionKey));
		}	
	
		$rArray[] = array("orderNotes",getFORM("xOrderNotes"),"S");
		$rArray[] = array("email",getFORM("xEmail"),"S");
		
		$rArray[] = array("paymentName",getFORM("paymentName"),"S");
		$rArray[] = array("ccName",getFORM("ccName"),"S");
		$rArray[] = array("ccNumber",$ccNumber,"S");
		$rArray[] = array("ccType",getFORM("ccType"),"S");
		$rArray[] = array("ccExpiryDate",getFORM("ccExpiryDate"),"S");
		$rArray[] = array("ccStartDate",getFORM("ccStartDate"),"S");
		$rArray[] = array("ccIssue",getFORM("ccIssue"),"S");
		$rArray[] = array("ccCVV",getFORM("ccCVV"),"S");
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='C' and visible=1 and internalOnly=0 and incOrdering=1 order by position,fieldID");
		for ($f = 0; $f < count($fieldList); $f++) {
			$rArray[] = array($fieldList[$f]["fieldname"],getFORM($fieldList[$f]["fieldname"]),"S");
		}
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='D' and visible=1 and internalOnly=0 and incOrdering=1 order by position,fieldID");
		for ($f = 0; $f < count($fieldList); $f++) {
			$rArray[] = array($fieldList[$f]["fieldname"],getFORM($fieldList[$f]["fieldname"]),"S");
		}
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='O' and visible=1 and internalOnly=0 order by position,fieldID");
		for ($f = 0; $f < count($fieldList); $f++) {
			$rArray[] = array($fieldList[$f]["fieldname"],getFORM($fieldList[$f]["fieldname"]),"S");
		}
		$dbA->updateRecord($tableOrdersHeaders,"orderID=$xOrderID",$rArray,0);

		userLogActionUpdate($recordType,$xOrderID+retrieveOption("orderNumberOffset"));
		doRedirect($linkBackLink."&".userSessionGET());
	}
	
	if ($xAction == "setpaid") {
		include("../routines/affiliateTracking.php");
		include("../routines/supplierRoutines.php");
		$xOrderID=getFORM("xOrderID");
		$xOrderList=getFORM("xOrderList");
		$orderList = null;
		if ($xOrderID != "") {
			$orderList[] = $xOrderID;
		}
		if ($xOrderList != "") {
			$orderList = split(";",$xOrderList);
		}
		$oCount = count($orderList);
		if ($oCount > 0) {
			for ($g = 0; $g < $oCount; $g++) {
				$xOrderID = $orderList[$g];
				if ($xOrderID != "") {
					$result = $dbA->query("select * from $tableOrdersHeaders where orderID=$xOrderID");
					if ($dbA->count($result) > 0) {
						$oRecord = $dbA->fetch($result);
						if ($oRecord["status"]=="N" || $oRecord["status"]=="F" || $oRecord["status"]=="C") {
							$dt = date("YmdHis");
							if (retrieveOption("orderAdminClearCC") == 1) {
								$clearCCString = ",ccName='', ccNumber='', ccExpiryDate='', ccType='', ccStartDate='', ccIssue='', ccCVV=''";
							} else {
								$clearCCString = "";
							}
							$dbA->query("update $tableOrdersHeaders set status='P',paymentDate='$dt'".$clearCCString." where orderID=$xOrderID");
							$showID = $xOrderID+retrieveOption("orderNumberOffset");
							userLog("Set Order Paid: $showID");
							sendConfirmationEmails($xOrderID,0);
							if (retrieveOption("stockDeductMode") == 1) {
								$result = $dbA->query("select * from $tableOrdersLines where orderID=$xOrderID order by lineID");	
								$count = $dbA->count($result);
								for ($f = 0; $f < $count; $f++) {
									$record = $dbA->fetch($result);
									$stockFields = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$xOrderID and lineID=".$record["lineID"]);
									alterStock($record["productID"],$record["qty"],$stockFields);
									$gresult = $dbA->query("select * from $tableOrdersLinesGrouped where orderID=$xOrderID and lineID=".$record["lineID"]);
									for ($ff = 0; $ff < $dbA->count($gresult); $ff++) {
										$grecord = $dbA->fetch($gresult);
										//$stockFields = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$xOrderID and lineID=".$record["lineID"]);
										alterStock($grecord["productID"],$record["qty"]*$grecord["qty"]);
									}
								}
							}	
							if (retrieveOption("affiliatesActivated") == 1 && retrieveOption("affiliatesCreatePayment") == "PAID" && $orderArray["affiliateID"] > 0) {
								affiliatesCreatePayment($orderArray);
							}
							autoDispatchDigital($xOrderID);
							if (retrieveOption("suppliersEnabled") == 1 && retrieveOption("suppliersEmailTiming") == 2) {
								sendSupplierEmails($xOrderID);
							}
						}
					}
				}
			}
		}
		doRedirect($linkBackLink."&".userSessionGET());
	}

	if ($xAction == "setcancelled") {
		$xOrderID=getFORM("xOrderID");
		$xOrderList=getFORM("xOrderList");
		$orderList = null;
		if ($xOrderID != "") {
			$orderList[] = $xOrderID;
		}
		if ($xOrderList != "") {
			$orderList = split(";",$xOrderList);
		}
		$oCount = count($orderList);
		if ($oCount > 0) {
			for ($g = 0; $g < $oCount; $g++) {
				$xOrderID = $orderList[$g];
				if ($xOrderID != "") {
					$result = $dbA->query("select * from $tableOrdersHeaders where orderID=$xOrderID");
					if ($dbA->count($result) > 0) {
						$oRecord = $dbA->fetch($result);
						if ($oRecord["status"]=="N" || $oRecord["status"]=="F") {
							$dt = date("YmdHis");
							if (retrieveOption("orderAdminClearCC") == 1) {
								$clearCCString = ",ccName='', ccNumber='', ccExpiryDate='', ccType='', ccStartDate='', ccIssue='', ccCVV=''";
							} else {
								$clearCCString = "";
							}
							$dbA->query("update $tableOrdersHeaders set status='C',paymentDate='$dt'".$clearCCString." where orderID=$xOrderID");
							$showID = $xOrderID+retrieveOption("orderNumberOffset");
							userLog("Set Order Cancelled: $showID");
						}
					}
				}
			}
		}
		doRedirect($linkBackLink."&".userSessionGET());
	}	
	
	if ($xAction == "suppliers") {
		include("../routines/supplierRoutines.php");
		$xOrderID=getFORM("xOrderID");
		$xOrderList=getFORM("xOrderList");
		$orderList = null;
		if ($xOrderID != "") {
			$orderList[] = $xOrderID;
		}
		if ($xOrderList != "") {
			$orderList = split(";",$xOrderList);
		}
		$oCount = count($orderList);
		if ($oCount > 0) {
			for ($g = 0; $g < $oCount; $g++) {
				$xOrderID = $orderList[$g];
				if ($xOrderID != "") {
					$result = $dbA->query("select * from $tableOrdersHeaders where orderID=$xOrderID");
					if ($dbA->count($result) > 0) {
						$oRecord = $dbA->fetch($result);
						$showID = $xOrderID+retrieveOption("orderNumberOffset");
						userLog("Sent Supplier Emails: $showID");
						sendSupplierEmails($xOrderID);
					}
				}
			}
		}
		doRedirect($linkBackLink."&".userSessionGET());
	}
	
	if ($xAction == "delete") {
		$xOrderID=getFORM("xOrderID");
		$dbA->query("delete from $tableOrdersHeaders where orderID=$xOrderID");
		$dbA->query("delete from $tableOrdersLines where orderID=$xOrderID");		
		userLogActionDelete($recordType,$xOrderID+retrieveOption("orderNumberOffset"));
		doRedirect($linkBackLink."&".userSessionGET());
	}
	
	if ($xAction == "deletelist") {
		$xOrdersList=getFORM("xOrdersList");
		$orderArray = split(";",$xOrdersList);
		for ($f = 0; $f < count($orderArray); $f++) {
			if ($orderArray[$f] != "") {
				$dbA->query("delete from $tableOrdersHeaders where orderID=".$orderArray[$f]);
				$dbA->query("delete from $tableOrdersLines where orderID=".$orderArray[$f]);
			}
		}
		userLog("Deleted List Of Orders");
		doRedirect($linkBackLink."&".userSessionGET());
	}
	
	if ($xAction == "setdispatchedsimple") {
		$xOrderID=getFORM("xOrderID");
		$showID=$xOrderID+retrieveOption("orderNumberOffset");
		$result = $dbA->query("select * from $tableOrdersHeaders where orderID=$xOrderID");
		if ($dbA->count($result) > 0) {
			$oRecord = $dbA->fetch($result);
			if ($oRecord["status"]=="N" || $oRecord["status"]=="P" || $oRecord["status"]=="I") {
				$dt = date("Ymd");
				$rArray[] = array("orderID",$xOrderID,"N");
				$rArray[] = array("dispatchDate",$dt,"S");	
				$rArray[] = array("trackingEnabled","N","S");	
				$dbA->insertRecord($tableDispatches,$rArray);
				$dispatchID = $dbA->lastID();
				$dbA->query("update $tableOrdersHeaders set status='D' where orderID=$xOrderID");
				$lineResults = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersLines where orderID=$xOrderID");
				$lrCount = count($lineResults);
				for ($f = 0; $f < $lrCount; $f++) {
					$rArray = null;
					$rArray[] = array("dispatchID",$dispatchID,"N");
					$rArray[] = array("orderID",$xOrderID,"N");
					$rArray[] = array("lineID",$lineResults[$f]["lineID"],"N");
					$rArray[] = array("qty",$lineResults[$f]["qty"],"N");
					$dbA->insertRecord($tableDispatchesTree,$rArray);
					if ($lineResults[$f]["isDigital"] == "Y") {
						//this is a digital one!
						createDigitalDownload($lineResults[$f]);
					}					
				}					
				userLog("Set Order Dispatched: $showID");
				sendDispatchEmail($xOrderID,$dispatchID);
				if (retrieveOption("stockDeductMode") == 2) {
					$result = $dbA->query("select * from $tableOrdersLines where orderID=$xOrderID order by lineID");	
					$count = $dbA->count($result);
					for ($f = 0; $f < $count; $f++) {
						$record = $dbA->fetch($result);
						$stockFields = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$xOrderID and lineID=".$record["lineID"]);
						alterStock($record["productID"],$record["qty"],$stockFields);
						$gresult = $dbA->query("select * from $tableOrdersLinesGrouped where orderID=$xOrderID and lineID=".$record["lineID"]);
						$gCount = count($gresult);
						for ($ff = 0; $ff < $gCount; $ff++) {
							$grecord = $dbA->fetch($gresult);
							//$stockFields = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$xOrderID and lineID=".$record["lineID"]);
							alterStock($grecord["productID"],$record["qty"]*$grecord["qty"]);
						}
					}
				}
				if ($oRecord["giftCertOrder"] == "Y") {
					$result = $dbA->query("select * from $tableGiftCertificates where orderID=$xOrderID");
					if ($dbA->count($result) > 0) {
						$record = $dbA->fetch($result);
						if ($record["type"] == "E") {
							//this is an email one!
							$giftCert = $record;
							@sendEmail($giftCert["emailaddress"],"","GIFTCERT");
						}
						if (makeInteger(retrieveOption("giftCertificatesExpiry")) > 0) {
							$expiryDate = date("Ymd",mktime(23,59,59,date("m"),date("d")+makeInteger(retrieveOption("giftCertificatesExpiry")),date("Y")));
						} else {
							$expiryDate = "N";
						}
						$result = $dbA->query("update $tableGiftCertificates set status='A', expiryDate='$expiryDate' where orderID=$xOrderID and certSerial='".$giftCert["certSerial"]."'");
					}
				}
			}
		}
	}



	if ($xAction == "setdispatchedcomplex") {
		$xOrderID = getFORM("xOrderID");
		$showID=$xOrderID+retrieveOption("orderNumberOffset");
		$result = $dbA->query("select * from $tableOrdersHeaders where orderID=$xOrderID");
		if ($dbA->count($result) > 0) {
			$dt = date("Ymd");
			$rArray[] = array("orderID",$xOrderID,"N");
			$rArray[] = array("dispatchDate",$dt,"S");
			if (retrieveOption("orderAdminDispatchTracking") == 1 && getFORM("xTrackingEnabled") == "Y") {
				$rArray[] = array("trackingEnabled",getFORM("xTrackingEnabled"),"S");
				$rArray[] = array("trackingReference",getFORM("xTrackingReference"),"S");
				$rArray[] = array("trackingMisc",getFORM("xTrackingMisc"),"S");
				$rArray[] = array("courierID",getFORM("xCourierID"),"N");
			} else {
				$rArray[] = array("trackingEnabled","N","S");
			}
			$dbA->insertRecord($tableDispatches,$rArray,0);
			$dispatchID = $dbA->lastID();
			if (retrieveOption("orderAdminDispatchPartial") == 0) {
				$lineResults = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersLines where orderID=$xOrderID");
				$lineCount = count($lineResults);
				for ($f = 0; $f < $lineCount; $f++) {
					$rArray = null;
					$rArray[] = array("dispatchID",$dispatchID,"N");
					$rArray[] = array("orderID",$xOrderID,"N");
					$rArray[] = array("lineID",$lineResults[$f]["lineID"],"N");
					$rArray[] = array("qty",$lineResults[$f]["qty"],"N");
					$dbA->insertRecord($tableDispatchesTree,$rArray);
					if ($lineResults[$f]["isDigital"] == "Y") {
						//this is a digital one!
						createDigitalDownload($lineResults[$f]);
					}
				}
				$dbA->query("update $tableOrdersHeaders set status='D' where orderID=$xOrderID");
				userLog("Set Dispatched Order: $showID");
				sendDispatchEmail($xOrderID,$dispatchID);
				if (retrieveOption("stockDeductMode") == 2) {
					$result = $dbA->query("select * from $tableOrdersLines where orderID=$xOrderID order by lineID");	
					$count = $dbA->count($result);
					for ($f = 0; $f < $count; $f++) {
						$record = $dbA->fetch($result);
						$stockFields = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$xOrderID and lineID=".$record["lineID"]);
						alterStock($record["productID"],$record["qty"],$stockFields);
						$gresult = $dbA->query("select * from $tableOrdersLinesGrouped where orderID=$xOrderID and lineID=".$record["lineID"]);
						$gcount = $dbA->count($gresult);
						for ($ff = 0; $ff < $gcount; $ff++) {
							$grecord = $dbA->fetch($gresult);
							//$stockFields = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$xOrderID and lineID=".$record["lineID"]);
							alterStock($grecord["productID"],$record["qty"]*$grecord["qty"]);
						}
					}
				}				
			} else {
				//this is a partial dispatch
				$lineResults = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersLines where orderID=$xOrderID");
				$newOrderStatus = "D";
				$lineCount = count($lineResults);
				for ($f = 0; $f < $lineCount; $f++) {
					$dispQty = makeInteger(getFORM("dispQty".$lineResults[$f]["lineID"]));
					$leftQty = makeInteger(getFORM("leftQty".$lineResults[$f]["lineID"]));
					if ($dispQty > $leftQty) {
						$dispQty = $leftQty;
					}
					if ($dispQty < 0) {
						$dispQty = 0;
					}
					if ($dispQty > 0) {
						$rArray = null;
						$rArray[] = array("dispatchID",$dispatchID,"N");
						$rArray[] = array("orderID",$xOrderID,"N");
						$rArray[] = array("lineID",$lineResults[$f]["lineID"],"N");
						$rArray[] = array("qty",$dispQty,"N");
						if ($lineResults[$f]["isDigital"] == "Y") {
							//this is a digital one!
							createDigitalDownload($lineResults[$f]);
						}
						$dbA->insertRecord($tableDispatchesTree,$rArray);
						if ($dispQty != $leftQty) {
							$newOrderStatus = "I";
						}
						if (retrieveOption("stockDeductMode") == 2) {
							$result = $dbA->query("select * from $tableOrdersLines where orderID=$xOrderID and lineID=".$lineResults[$f]["lineID"]." order by lineID");	
							$count = $dbA->count($result);
							for ($g = 0; $g < $count; $g++) {
								$record = $dbA->fetch($result);
								$stockFields = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$xOrderID and lineID=".$record["lineID"]);
								alterStock($record["productID"],$dispQty,$stockFields);
							}
							$gresult = $dbA->query("select * from $tableOrdersLinesGrouped where orderID=$xOrderID and lineID=".$lineResults[$f]["lineID"]);
							$gcount = $dbA->count($gresult);
							for ($ff = 0; $ff < $gcount; $ff++) {
								$grecord = $dbA->fetch($gresult);
								//$stockFields = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$xOrderID and lineID=".$record["lineID"]);
								alterStock($grecord["productID"],$dispQty*$grecord["qty"]);
							}
						}						
					}	
					if ($dispQty == 0 && $leftQty > 0) {
						$newOrderStatus = "I";
					}			
				}
				$dbA->query("update $tableOrdersHeaders set status='$newOrderStatus' where orderID=$xOrderID");
				userLog("Set Order Dispatched: $showID");
				sendDispatchEmail($xOrderID,$dispatchID);
			}
		}
	}
	doRedirect($linkBackLink."&".userSessionGET());

	function retrieveTaxRates($thisCountry,$thisCounty,$deliveryCountry="",$deliveryCounty="") {
		global $dbA,$tableCountries;
		$taxArray = null;
		$taxArray["countryTaxStandard"] = 0;
		$taxArray["countryTaxSecond"] = 0;		
		if ($thisCountry == "") {
			$thisCountry = retrieveOption("defaultCountry");
		}
		$result = $dbA->query("select * from $tableCountries where name=\"$thisCountry\"");
		if ($dbA->count($result) != 1) {
			$taxArray["countryTaxStandard"] = 0;
			$taxArray["countryTaxSecond"] = 0;
		} else {
			$record = $dbA->fetch($result);
			$taxArray["countryTaxStandard"] = $record["taxstandard"];
			$taxArray["countryTaxSecond"] = $record["taxsecond"];
		}
		if (retrieveOption("fieldCountyAsSelect") == 1) {
			//ok, so let's see if a state tax rate exists;
			$countyList = split(";",retrieveOption("taxCountiesList"));
			for ($f = 0; $f < count($countyList); $f++) {
				if ($countyList[$f] != "") {
					if ($thisCounty == $countyList[$f]) {
						$taxArray["countryTaxStandard"] = retrieveOption("taxCountiesStandard");
						$taxArray["countryTaxSecond"] = retrieveOption("taxCountiesSecond");
						return $taxArray;
					}
				}
			}
		}
		if ($taxArray["countryTaxStandard"] == 0 && retrieveOption("taxIncludeDeliveryAddress") == 1) {
			//check the delivery parts as well
			if ($deliveryCountry != "") {
				$result = $dbA->query("select * from $tableCountries where name =\"$deliveryCountry\"");
				if ($dbA->count($result) != 1) {
					$taxArray["countryTaxStandard"] = 0;
					$taxArray["countryTaxSecond"] = 0;
				} else {
					$record = $dbA->fetch($result);
					$taxArray["countryTaxStandard"] = $record["taxstandard"];
					$taxArray["countryTaxSecond"] = $record["taxsecond"];
				}			
			}
			if ($deliveryCounty != "") {
				if (retrieveOption("fieldCountyAsSelect") == 1) {
					//ok, so let's see if a state tax rate exists;
					$countyList = split(";",retrieveOption("taxCountiesList"));
					for ($f = 0; $f < count($countyList); $f++) {
						if ($countyList[$f] != "") {
							if ($deliveryCounty == $countyList[$f]) {
								$taxArray["countryTaxStandard"] = retrieveOption("taxCountiesStandard");
								$taxArray["countryTaxSecond"] = retrieveOption("taxCountiesSecond");
								return $taxArray;
							}
						}
					}
				}			
			}
		}
		return $taxArray;
	}	

	function calculateTax($thePrice) {
		global $taxRates;
		$theTax = 0;
		if (retrieveOption("taxEnabled") == 0) {
			return 0;
		}
		$theTax = ($thePrice / 100) * $taxRates["countryTaxStandard"];
		$theTax = number_format(abs($theTax),2,".","");
		return $theTax;
	}	
?>
