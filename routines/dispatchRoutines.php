<?php
	//Dispatching routines
	//(c)2003 Whorl Ltd.
	
	function createDigitalDownload($lineRecord) {
		global $dbA,$tableDigitalPurchases,$tableOrdersLines;
		$regName = getFORM("regName".$lineRecord["lineID"]);
		$regCode = getFORM("regCode".$lineRecord["lineID"]);
		$rArray = null;
		$rArray[] = array("orderID",$lineRecord["orderID"],"N");
		$rArray[] = array("lineID",$lineRecord["lineID"],"N");
		$rArray[] = array("regName",$regName,"S");
		$rArray[] = array("regCode",$regCode,"S");
		$createTime = time();
		$downloadRef = $lineRecord["digitalFile"].$lineRecord["orderID"].$lineRecord["lineID"].$createTime;
		$downloadRef = md5($downloadRef);
		$rArray[] = array("downloadRef",$downloadRef,"S");
		$rArray[] = array("createtime",$createTime,"N");
		$rArray[] = array("digitalFile",$lineRecord["digitalFile"],"S");
		$rArray[] = array("attempts",0,"N");
		$dbA->insertRecord($tableDigitalPurchases,$rArray,0);
		$downloadID = $dbA->lastID();
		$dbA->query("update $tableOrdersLines set downloadID=".$downloadID." where orderID=".$lineRecord["orderID"]." and lineID=".$lineRecord["lineID"]);
		return $downloadID;
	}
	
	function autoDispatchDigital($xOrderID) {
		global $dbA,$tableOrdersHeaders,$tableOrdersLines,$tableDispatchesTree,$tableDispatches;
		if (retrieveOption("downloadsAllowInstant") == 1 && retrieveOption("orderAdminDispatchPartial") == 1) {
			$lineResult = $dbA->query("select * from $tableOrdersLines where orderID=$xOrderID and isDigital='Y' and digitalReg=0");
			if ($dbA->count($lineResult) == 0) { return true; }
			$showID=$xOrderID+retrieveOption("orderNumberOffset");
			$result = $dbA->query("select * from $tableOrdersHeaders where orderID=$xOrderID");
			if ($dbA->count($result) > 0) {
				$dt = date("Ymd");
				$rArray[] = array("orderID",$xOrderID,"N");
				$rArray[] = array("dispatchDate",$dt,"S");
				$rArray[] = array("trackingEnabled","N","S");
				$dbA->insertRecord($tableDispatches,$rArray,0);
				$dispatchID = $dbA->lastID();
				//this is a partial dispatch
				$lineResults = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersLines where orderID=$xOrderID");
				$newOrderStatus = "D";
				for ($f = 0; $f < count($lineResults); $f++) {
					if ($lineResults[$f]["isDigital"] == "N" || ($lineResults[$f]["isDigital"] == "Y" && $lineResults[$f]["digitalReg"] != 0)) {
						$newOrderStatus = "I";
					} else {
						$dispQty = $lineResults[$f]["qty"];
						if ($dispQty > 0) {
							$rArray = null;
							$rArray[] = array("dispatchID",$dispatchID,"N");
							$rArray[] = array("orderID",$xOrderID,"N");
							$rArray[] = array("lineID",$lineResults[$f]["lineID"],"N");
							$rArray[] = array("qty",$dispQty,"N");
							createDigitalDownload($lineResults[$f]);
							$dbA->insertRecord($tableDispatchesTree,$rArray);
							if (retrieveOption("stockDeductMode") == 2) {
								$result = $dbA->query("select * from $tableOrdersLines where orderID=$xOrderID and lineID=".$lineResults[$f]["lineID"]." order by lineID");	
								$count = $dbA->count($result);
								for ($g = 0; $g < $count; $g++) {
									$record = $dbA->fetch($result);
									$stockFields = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$xOrderID and lineID=".$record["lineID"]);
									alterStock($record["productID"],$dispQty,$stockFields);
								}
								$gresult = $dbA->query("select * from $tableOrdersLinesGrouped where orderID=$xOrderID and lineID=".$lineResults[$f]["lineID"]);
								for ($ff = 0; $ff < $dbA->count($gresult); $ff++) {
									$grecord = $dbA->fetch($gresult);
									//$stockFields = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$xOrderID and lineID=".$record["lineID"]);
									alterStock($grecord["productID"],$dispQty*$grecord["qty"]);
								}
							}						
						}	
					}			
				}
				$dbA->query("update $tableOrdersHeaders set status='$newOrderStatus' where orderID=$xOrderID");
				if (function_exists('userLog')) { 
					userLog("Set Order Dispatched: $showID");
				}
				sendDispatchEmail($xOrderID,$dispatchID);
			}		
		}
	}

	function sendDispatchEmail($xOrderID,$dispatchID) {
		global $dbA,$extraFieldList,$tableOrdersLines,$tableOrdersHeaders,$tableDispatchesTree,$tableOrdersExtraFields,$tableDispatches,$orderArray,$dispatchArray,$tableCouriers,$tableDigitalPurchases;
		global $jssStoreWebDirHTTP;
		$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$xOrderID");
		$orderArray = $dbA->fetch($result);
		$orderArray["ordernumber"] = $orderArray["orderID"]+retrieveOption("orderNumberOffset");
		$orderArray["orderdate"] = formatDate($orderArray["datetime"]);
		$orderArray["ordertime"] = formatTime(substr($orderArray["datetime"],-6));
		$orderProducts = $dbA->retrieveAllRecordsFromQuery("select $tableOrdersLines.*,$tableDispatchesTree.qty as dispatchQty from $tableOrdersLines LEFT JOIN $tableDispatchesTree ON $tableDispatchesTree.lineID = $tableOrdersLines.lineID where $tableOrdersLines.orderID=$xOrderID and $tableDispatchesTree.dispatchID=$dispatchID order by $tableOrdersLines.lineID");
		for ($f = 0; $f < count($orderProducts); $f++) {
			if ($orderProducts[$f]["isDigital"] == "Y") {
				$downloadID = $orderProducts[$f]["downloadID"];
				$digitalResult = $dbA->query("select * from $tableDigitalPurchases where downloadID=$downloadID");
				if ($dbA->count($digitalResult) > 0) {
					$digitalRecord = $dbA->fetch($digitalResult);
					$digitalLink = $jssStoreWebDirHTTP."digital.php?xRef=".$digitalRecord["downloadRef"];
					$orderProducts[$f]["downloadLink"] = $digitalLink;
					$orderProducts[$f]["regName"] = $digitalRecord["regName"];
					$orderProducts[$f]["regCode"] = $digitalRecord["regCode"];
				}
			}
		}
		$orderArray["products"] = $orderProducts;
		$extraFieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$xOrderID order by lineID,extraFieldID");
		
		$result = $dbA->query("select * from $tableDispatches where dispatchID=$dispatchID");
		$dispatchArray = $dbA->fetch($result);
		$dispatchArray["date"] = formatDate($dispatchArray["dispatchDate"]);
		if (makeInteger($dispatchArray["courierID"]) > 0) {
			$result = $dbA->query("select * from $tableCouriers where courierID=".$dispatchArray["courierID"]);
			if ($dbA->count($result) > 0) {
				$courier = $dbA->fetch($result);
				$dispatchArray["courier"] = $courier;
			}
		}
		if (retrieveOption("orderAdminEmailDispatch") == 1) {
			@sendEmail($orderArray["email"],"","CUSTDESPATCH");
			if (retrieveOption("orderAdminDispatchCopy") == 1) {
				@sendEmail(retrieveOption("orderAdminDispatchCopyAddress"),"","CUSTDESPATCH");
			}
		}
	}
?>
