<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");
	include("../routines/emailOutput.php");
	include("../routines/tSys.php");
	
	dbConnect($dbA);
	
	$xCmd = getFORM("xCmd");
	$xOrderID = getFORM("xOrderID");
	$xOrderList = getFORM("xOrderList");

	$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"position,name");
	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	$result = $dbA->query("select * from $tableGeneral");
	$companyRecord = $dbA->fetch($result);	

	if ($xCmd == "view") {
		grabOrder($xOrderID);
	}
	if ($xCmd == "print") {
?>
<HTML>
<HEAD>
</HEAD>
<?php
	if ($xBrowserShort == "IE") {
		?><BODY onLoad="parent.iePrint();"><?php
	} else {
		?><BODY onLoad="window.print();"><?php
	}
?>
<?php
		$onePrinted = false;
		$orderArray = split(";",$xOrderList);
		for ($x=0;$x<count($orderArray); $x++) {
			if ($orderArray[$x] != "") {
				$orderID=$orderArray[$x];
				if ($onePrinted == true) { echo "<div style='page-break-before:always'>"; } else { echo "<div>"; }
				grabOrder($orderID);
				if ($onePrinted == true) { echo "</div>"; }
				$onePrinted = true;
			}	
		}
?>
</BODY>
</HTML>
<?php
	}

	$dbA->close();

	function grabOrder($xOrderID) {
		global $dbA,$tableOrdersHeaders,$tableOrdersLines,$tableOrdersExtraFields,$extraFieldsArray,$currArray,$companyRecord;
		$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$xOrderID");
		$orderArray = $dbA->fetch($result);
		$orderArray["ordernumber"] = $orderArray["orderID"]+retrieveOption("orderNumberOffset");
		$orderArray["orderdate"] = formatDate($orderArray["datetime"]);
		$orderArray["ordertime"] = formatTime(substr($orderArray["datetime"],-6));
		$orderProducts = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersLines where $tableOrdersLines.orderID=$xOrderID order by $tableOrdersLines.lineID");
		$orderArray["products"] = $orderProducts;
		$extraFieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$xOrderID order by lineID,extraFieldID");


		for ($f = 0; $f < count($orderArray["products"]); $f++) {
			$theQty = $orderArray["products"][$f]["qty"];
			$thePrice = $orderArray["products"][$f]["price"];
			$thePriceRounded = roundWithoutCalcDisplay($orderArray["products"][$f]["price"],$orderArray["currencyID"]);
			$theOOPrice = $orderArray["products"][$f]["ooprice"];
			$orderArray["products"][$f]["price"] = formatWithoutCalcPriceInCurrency($thePrice,$orderArray["currencyID"]);
			$orderArray["products"][$f]["tax"] = formatWithoutCalcPriceInCurrency($orderArray["products"][$f]["taxamount"],$orderArray["currencyID"]);
			$orderArray["products"][$f]["ooPrice1"] = $theOOPrice;
			$orderArray["products"][$f]["ooprice"] = formatWithoutCalcPriceInCurrency($theOOPrice,$orderArray["currencyID"]);
			$orderArray["products"][$f]["oopricetax"] = formatWithoutCalcPriceInCurrency($orderArray["products"][$f]["ootaxamount"],$orderArray["currencyID"]);
			$orderArray["products"][$f]["total"] = formatWithoutCalcPriceInCurrency(($thePriceRounded*$theQty)+$theOOPrice,$orderArray["currencyID"]);
			$orderArray["products"][$f]["totalextax"] = formatWithoutCalcPriceInCurrency(($thePriceRounded*$theQty)+$theOOPrice,$orderArray["currencyID"]);
			$orderArray["products"][$f]["totaltax"] = formatWithoutCalcPriceInCurrency((($orderArray["products"][$f]["taxamount"])*$theQty)+($orderArray["products"][$f]["ootaxamount"]),$orderArray["currencyID"]);
			$orderArray["products"][$f]["totalinctax"] = formatWithoutCalcPriceInCurrency((($thePriceRounded+$orderArray["products"][$f]["taxamount"])*$theQty)+($theOOPrice+$orderArray["products"][$f]["ootaxamount"]),$orderArray["currencyID"]);
			
			$allExtraFields = "";
			for ($g = 0; $g < count($extraFieldsArray); $g++) {
				$thisExtraField = "";
				switch ($extraFieldsArray[$g]["type"]) {
					case "USERINPUT":
					case "SELECT":
					case "RADIOBUTTONS":
						$theContent = "";
						for ($i = 0; $i < count($extraFieldList); $i++) {
							if ($orderArray["products"][$f]["lineID"] == $extraFieldList[$i]["lineID"] && $extraFieldsArray[$g]["extraFieldID"] == $extraFieldList[$i]["extraFieldID"]) {
								$theContent = $extraFieldList[$i]["content"];
								break;
							}
						}
						$thisExtraField["name"] = $extraFieldsArray[$g]["name"];
						$thisExtraField["title"] = $extraFieldsArray[$g]["title"];
						$thisExtraField["type"] = $extraFieldsArray[$g]["type"];
						$thisExtraField["content"] = $theContent;
												
						$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["name"] = $extraFieldsArray[$g]["name"];
						$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["title"] = $extraFieldsArray[$g]["title"];
						$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["type"] = $extraFieldsArray[$g]["type"];
						$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["content"] = $theContent;
										$allExtraFields[] = $thisExtraField;
						break;								
					case "CHECKBOXES":
						$optionArray = "";
						$theContent = "";
						for ($i = 0; $i < count($extraFieldList); $i++) {
							if ($orderArray["products"][$f]["lineID"] == $extraFieldList[$i]["lineID"] && $extraFieldsArray[$g]["extraFieldID"] == $extraFieldList[$i]["extraFieldID"]) {
								if ($extraFieldList[$i]["content"] != "") {
									$optionArray[] = array("option"=>$extraFieldList[$i]["content"]);
									$theContent = "Y";
								}
							}
						}
						$thisExtraField["name"] = $extraFieldsArray[$g]["name"];
						$thisExtraField["title"] = $extraFieldsArray[$g]["title"];
						$thisExtraField["type"] = $extraFieldsArray[$g]["type"];
						$thisExtraField["content"] = $theContent;
						$thisExtraField["options"] = $optionArray;
												
						$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["name"] = $extraFieldsArray[$g]["name"];
						$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["title"] = $extraFieldsArray[$g]["title"];
						$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["type"] = $extraFieldsArray[$g]["type"];
						$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["content"] = $theContent;
						$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["options"] = $optionArray;
						$allExtraFields[] = $thisExtraField;
						break;
				}
			}			
			if (is_array($allExtraFields)) {
				$orderArray["products"][$f]["extrafields"] = $allExtraFields;
			}					
		}
		$goodsTotal = $orderArray["goodsTotal"];
		$shippingTotal = $orderArray["shippingTotal"];
		$taxTotal = $orderArray["taxTotal"];
		$discountTotal = $orderArray["discountTotal"];
		$giftCertTotal = $orderArray["giftCertTotal"];
		$orderTotal = $goodsTotal+$shippingTotal+$taxTotal-$discountTotal-$giftCertTotal;
		$orderArray["totals"]["goods"] = formatWithoutCalcPriceInCurrency($goodsTotal,$orderArray["currencyID"]);
		if ($discountTotal > 0) {
			$orderArray["totals"]["isDiscount"] = "Y";
		} else {
			$orderArray["totals"]["isDiscount"] = "N";
		}
		$orderArray["totals"]["discount"] = formatWithoutCalcPriceInCurrency($discountTotal,$orderArray["currencyID"]);
		$orderArray["totals"]["shipping"] = formatWithoutCalcPriceInCurrency($shippingTotal,$orderArray["currencyID"]);
		$orderArray["totals"]["goodsandshipping"] = formatWithoutCalcPriceInCurrency($shippingTotal+$goodsTotal,$orderArray["currencyID"]);
		$orderArray["totals"]["tax"] = formatWithoutCalcPriceInCurrency($taxTotal,$orderArray["currencyID"]);
		$orderArray["totals"]["order"] = formatWithoutCalcPriceInCurrency($orderTotal,$orderArray["currencyID"]);	
		$orderArray["totals"]["giftcertificates"] = formatWithoutCalcPriceInCurrency($giftCertTotal,$orderArray["currencyID"]);		

		$tpl = new tSys("../templates/","receipt.html",$requiredVars,0);				

		$tpl->addVariable("company",$companyRecord);
		$tpl->addVariable("order",$orderArray);
		$tpl->showPage();
	}
?>
