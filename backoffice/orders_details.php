<?php
	$oDT = $oRecord["datetime"];
	$oDT = formatDate($oDT)." @ ".formatTime(substr($oDT,8,6));
	
	if ($oRecord["authInfo"] != "") {
		$extraFields = $oRecord["authInfo"];

		$nameValues = split("&",$extraFields);
		$splitExtraFields = "";
		for ($f = 0; $f < count($nameValues); $f++) {
			$thisCode = split("=",$nameValues[$f]);
			$splitExtraFields[] = array($thisCode[0],$thisCode[1]);
		}
	}
	
	if ($oRecord["status"] == "N") {
		$orderStatus = "NEW";
	}
	if ($oRecord["status"] == "F") {
		$dt = $oRecord["paymentDate"];
		if ($dt != "") {
			$dt = formatDate($dt);
		}
		$orderStatus = "Payment Failed $dt. ";
	}
	if ($oRecord["status"] != "N" && $oRecord["status"] != "F") {
		$dt = $oRecord["paymentDate"];
		if ($dt != "") {
			$dt = formatDate($dt);
		}
		$orderStatus = "Payment Processed $dt. ";
	}
	
	$currentStatus = "";
	switch ($oRecord["status"]) {
		case "N":
			$currentStatus = "NEW";
			break;
		case "P":
			$currentStatus = "PAID";
			break;
		case "D":
			$currentStatus = "DISPATCHED";
			break;
		case "I":
			$currentStatus = "PART DISPATCHED";
			break;
		case "F":
			$currentStatus = "FAILED";
			break;
		case "C":
			$currentStatus = "CANCELLED";
			break;
	}

	if ($oRecord["shippingMethod"] == "") {
		$shippingMethod = "";
	} else {
		$shippingMethod = " (Method: ".$oRecord["shippingMethod"].")";
	}
	$currencyID = $oRecord["currencyID"];
	
	$showID = $orderID + retrieveOption("orderNumberOffset");
	
	if (makeInteger($oRecord["customerID"]) > 0) {
		$custRes = $dbA->query("select * from $tableCustomers where customerID=".makeInteger($oRecord["customerID"]));
		if ($dbA->count($custRes) > 0) {
			$custRecord = $dbA->fetch($custRes);
			$customerExtra = "(Account Holder, Customer ID = ".$oRecord["customerID"].")";
		} else {
			$oRecord["customerID"] = 0;
			$customerExtra = "";
		}
	}
	
	if ($oRecord["affiliateID"] > 0) {
		$affRes = $dbA->query("select * from $tableAffiliates where affiliateID=".$oRecord["affiliateID"]);
		if ($dbA->count($affRes) > 0) {
			$affRecord = $dbA->fetch($affRes);
			$affiliateName = $affRecord["aff_Company"]." (".$affRecord["username"].")";
		}
	}
	
	$checkingString="01234567890 ";
	if ($oRecord["ccNumber"] != "") {
		$ccEnc = isValidCard($oRecord["ccNumber"]);
		$myCounter = 0;
		while ($ccEnc && $myCounter < 20) {
			$oRecord["ccNumber"] = $crypt->decrypt(base64_decode($oRecord["ccNumber"]), $teaEncryptionKey);
			$ccEnc = isValidCard($oRecord["ccNumber"]);
			$myCounter++;
		}
	}
	if ($oRecord["ccCVV"] != "") {
		$ccEnc = isValidCard($oRecord["ccCVV"]);
		$myCounter = 0;
		while ($ccEnc && $myCounter < 20) {
			$oRecord["ccCVV"] = $crypt->decrypt(base64_decode($oRecord["ccCVV"]), $teaEncryptionKey);
			$ccEnc = isValidCard($oRecord["ccCVV"]);
			$myCounter++;
		}		
	}	
	
	$colspan = 4;
	if ($oRecord["status"] == "I") { $colspan++; }
		
?>
<?php if ($onePrinted == true) { echo "<div style=\"page-break-before:always\">"; } ?>
<center>
<table width="96%" cellpadding="2" cellspacing="0" class="table-outline-white">
	<tr>
		<td class="table-white" align="left" width="106"><?php $myForm->createNavBarButton("buttonBack","< Back","self.history.go(-1);"); ?></td>
		<td class="table-white"><b>Order Number: <?php print $showID; ?> (Date: <?php print $oDT; ?>)</b></td>
		<td class="table-white" align="right" width="106"><?php $myForm->createNavBarButton("buttonPrint",$buttonTitle,"self.print();"); ?></td>
	</tr>
</table>
<br>
<table width="96%" cellpadding="2" cellspacing="0" class="table-outline-white">
<tr>
	<td class="table-white-nocenter-s" align="left">Product Code</td>
	<td class="table-white-nocenter-s" align="left">Product Details</td>
	<td class="table-white-nocenter-s" align="right">Quantity</td>
	<?php
		if ($oRecord["status"] == "I") {
	?>
		<td class="table-white-nocenter-s" align="right">Qty Undispatched</td>
	<?php
		}
	?>
	<td class="table-white-nocenter-s" align="right">Price Each</td>
	<td class="table-white-nocenter-s" align="right">Total Cost</td>
</tr>
<?php
	$extraFieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$orderID order by lineID,extraFieldID");
	$lResult = $dbA->query("select * from $tableOrdersLines where orderID=$orderID order by lineID");
	$lCount = $dbA->count($lResult);
	for ($f = 0; $f < $lCount; $f++) {
		$lRecord = $dbA->fetch($lResult);
		$gResult = $dbA->retrieveAllRecordsFromQuery("select *  from $tableOrdersLinesGrouped where orderID=$orderID and lineID=".$lRecord["lineID"]." order by groupedID");
		$extraFields = @$lRecord["extrafields"];
		if ($extraFields != "") {
			$extraFields = "<br>".$extraFields;
		}
		$allFields = "";
		for ($g = 0; $g < count($extraFieldsArray); $g++) {
			$thisField = "";
			switch ($extraFieldsArray[$g]["type"]) {
					case "SELECT":
					case "RADIOBUTTONS":
					case "USERINPUT":
						$theContent = "";
						for ($i = 0; $i < count($extraFieldList); $i++) {
							if ($lRecord["lineID"] == $extraFieldList[$i]["lineID"] && $extraFieldsArray[$g]["extraFieldID"] == $extraFieldList[$i]["extraFieldID"]) {
								$theContent = $extraFieldList[$i]["content"];
								break;
							}
						}
						if ($theContent != "") {
							$thisField = $extraFieldsArray[$g]["title"].": ".$theContent;
						}
						break;								
					case "CHECKBOXES":
						$optionArray = "";
						$theContent = "";
						for ($i = 0; $i < count($extraFieldList); $i++) {
							if ($lRecord["lineID"] == $extraFieldList[$i]["lineID"] && $extraFieldsArray[$g]["extraFieldID"] == $extraFieldList[$i]["extraFieldID"]) {
								if ($extraFieldList[$i]["content"] != "") {
									if ($theContent == "") {
										$theContent = $extraFieldList[$i]["content"];
									} else {
										$theContent .= ", ".$extraFieldList[$i]["content"];
									}
								}
							}
						}
						if ($theContent != "") {
							$thisField = $extraFieldsArray[$g]["title"].": ".$theContent;
						}
						break;
			}	
			if 	($thisField != "") {
				$allFields .= $thisField . "<BR>";
			}
		}
		if ($allFields != "") {
			$allFields = "<BR>".$allFields;
		}
		$giftCert = "";
		if ($lRecord["code"]=="GIFTCERT") {
			//this is a gift certificate
			$result = $dbA->query("select * from $tableGiftCertificates where orderID=$orderID");
			if ($dbA->count($result) > 0) {
				$gRecord = $dbA->fetch($result);
				switch ($gRecord["type"]) {
					case "E":
						$gType = "Email to ".$gRecord["emailaddress"];
						break;
					case "P":
						$gType = "Postal to delivery address below <a href=\"orders_printcert.php?xOrderID=$orderID&".userSessionGET()."\" target=\"_new\">[Print Certificate]</a>";
						break;
				}
				switch ($gRecord["status"]) {
					case "N":
						$gStatus = "Not Activated";
						break;
					case "A":
						$gStatus = "Activated";
						break;
				}
				$giftCert .= "<BR>";
				$giftCert .= "Certificate: ".$gRecord["certSerial"]."<BR>";
				$giftCert .= "Status: ".$gStatus."<BR>";
				$giftCert .= "From: ".$gRecord["fromname"]."<BR>";
				$giftCert .= "To: ".$gRecord["toname"]."<BR>";
				$giftCert .= $gType."<BR>";
				$giftCert .= "<BR>Message:<BR>".eregi_replace("\r\n","<BR>",$gRecord["message"])."<BR>";
			}
		}
		$groupedList = "";
		if (is_array($gResult)) {
			$groupedList = "<BR>";
			for ($hh = 0; $hh < count($gResult); $hh++) {
				if ($gResult[$hh]["code"] != "") {
					$groupedList .= $gResult[$hh]["code"]." - ";
				}
				$groupedList .= $gResult[$hh]["name"]." x ".$gResult[$hh]["qty"]."<BR>";
			}
		}
			
?>
		<tr>
			<td class="table-white-nocenter-light-s" align="left" valign="top"><?php print $lRecord["code"]; ?></td>
			<td class="table-white-nocenter-light-s" align="left" valign="top"><b><?php print $lRecord["name"]; ?></b><?php print $allFields; ?><?php print $groupedList; ?><?php print $giftCert; ?></td>
			<td class="table-white-nocenter-light-s" align="right" valign="top"><?php print $lRecord["qty"]; ?></td>
			<?php
				if ($oRecord["status"] == "I") {
					$dispatchLines = $dbA->retrieveAllRecordsFromQuery("select lineID,qty from $tableDispatchesTree where orderID=".$lRecord["orderID"]." and lineID=".$lRecord["lineID"]);
					$qtyLeft = $lRecord["qty"];
					if (is_array($dispatchLines)) {
						for ($hh = 0; $hh < count($dispatchLines); $hh++) {
							$qtyLeft = $qtyLeft - $dispatchLines[$hh]["qty"];
						}
						if ($qtyLeft < 0) { $qtyLeft = 0; }
					}
			?>
				<td class="table-white-nocenter-light-s" align="right"><?php print $qtyLeft; ?></td>
			<?php
				}
			?>
			<td class="table-white-nocenter-light-s" align="right" valign="top"><?php print priceFormat($lRecord["price"],$currencyID); ?>
				<?php if ($lRecord["ooprice"] > 0) { ?>
					(+ <?php print priceFormat($lRecord["ooprice"],$currencyID); ?>)
				<?php } ?>
			</td>
			<td class="table-white-nocenter-light-s" align="right" valign="top"><?php print priceFormat((roundWithoutCalcDisplay($lRecord["price"],$currencyID)*$lRecord["qty"])+$lRecord["ooprice"],$currencyID); ?></td>
		</tr>
<?php
	}
?>
<tr>
	<td class="table-white-nocenter-s" colspan="<?php print $colspan; ?>" align="left">Goods Total</td>
	<td class="table-white-nocenter-s" align="right"><?php print priceFormat($oRecord["goodsTotal"],$currencyID); ?></td>
</tr>
<?php
	if ($oRecord["discountTotal"] > 0) {
?>
<?php
	}
?>
<tr>
	<td class="table-white-nocenter-s" colspan="<?php print $colspan; ?>" align="left">Shipping Total <?php print $shippingMethod; ?></td>
	<td class="table-white-nocenter-s" align="right"><?php print priceFormat($oRecord["shippingTotal"],$currencyID); ?></td>
</tr>
<tr>
	<td class="table-white-nocenter-s" colspan="<?php print $colspan; ?>" align="left">Tax Total</td>
	<td class="table-white-nocenter-s" align="right"><?php print priceFormat($oRecord["taxTotal"],$currencyID); ?></td>
</tr>
<tr>
	<td class="table-white-nocenter-s" colspan="<?php print $colspan; ?>" align="left">Discount Total <?php if ($oRecord["offerCode"] != "") { print "(Offer Code: ".$oRecord["offerCode"].")"; } ?></td>
	<td class="table-white-nocenter-s" align="right">-<?php print priceFormat($oRecord["discountTotal"],$currencyID); ?></td>
</tr>
<?php
	if ($oRecord["giftCertTotal"] > 0) {
		$res = $dbA->query("select * from $tableGiftCertificatesTrans where orderID = $orderID");
		if ($dbA->count($res) > 0) {
			$resRec = $dbA->fetch($res);
			$certSerial = "(Certificate: ".$resRec["certSerial"].")";
		} else {
			$certSerial = "";
		}
?>
<tr>
	<td class="table-white-nocenter-s" colspan="<?php print $colspan; ?>" align="left">Gift Certificate Total <?php print $certSerial; ?></td>
	<td class="table-white-nocenter-s" align="right">-<?php print priceFormat($oRecord["giftCertTotal"],$currencyID); ?></td>
</tr>
<?php
	}
?>
<tr>
	<td class="table-white-nocenter-s" colspan="<?php print $colspan; ?>" align="left">Order Total</td>
	<td class="table-white-nocenter-s" align="right"><?php print priceFormat($oRecord["goodsTotal"]+$oRecord["shippingTotal"]+$oRecord["taxTotal"]-$oRecord["discountTotal"]-$oRecord["giftCertTotal"],$currencyID); ?></td>
</tr>
</table>
<br>
	<table cellpadding="0" cellspacing="1" border="0" width="96%">
	<tr>
		<td valign="top" width="48%">
			<table width="100%" cellpadding="2" cellspacing="0" class="table-outline-white">
			<tr>
				<td class="table-grey-nocenter" colspan="2" align="left">Customer's Details <?php print @$customerExtra; ?></td>
			</tr>
			<tr>
				<td class="table-white-nocenter-s" align="left">Name</td>
				<td class="table-white-nocenter-light-s" align="left"><?php print addPadding($oRecord["title"]); ?> <?php print addPadding($oRecord["forename"]); ?> <?php print addPadding($oRecord["surname"]); ?></td>
			</tr>
	<?php
			$result = $dbA->query("select * from $tableCustomerFields where type='C' and visible=1 and internalOnly=0 and incOrdering=1 order by position,fieldID");
			$count = $dbA->count($result);
			for ($f = 0; $f < $count; $f++) {
				$record = $dbA->fetch($result);
				if ($record["fieldname"] != "title" && $record["fieldname"] != "forename" && $record["fieldname"] != "surname") {
	?>
			<tr>
				<td class="table-white-nocenter-s" align="left"><?php print $record["titleText"]; ?></td>
				<td class="table-white-nocenter-light-s" align="left"><?php print addPadding($oRecord[$record["fieldname"]]); ?></td>
			</tr>
				
	<?php
				}
			}
	?>
			<tr>
				<td class="table-white-nocenter-s" align="left">Email Address</td>
				<td class="table-white-nocenter-light-s" align="left"><?php print addPadding($oRecord["email"]); ?></td>
			</tr>	
			</table>
		</td>
		<td valign="top" width="48%">
				
				<?php
					if ($oRecord["deliveryName"] != "" || $oRecord["deliveryAddress1"] != "") {
				?>
						<table width="100%" cellpadding="2" cellspacing="0" class="table-outline-white">
						<tr>
							<td class="table-grey-nocenter" colspan="2" align="left">Delivery Details</td>
						</tr>
				<?php
						$result = $dbA->query("select * from $tableCustomerFields where type='D' and visible=1 and internalOnly=0 and incOrdering=1 order by position,fieldID");
						$count = $dbA->count($result);
						for ($f = 0; $f < $count; $f++) {
							$record = $dbA->fetch($result);
				?>
						<tr>
							<td class="table-white-nocenter-s" align="left"><?php print $record["titleText"]; ?></td>
							<td class="table-white-nocenter-light-s" align="left"><?php print addPadding($oRecord[$record["fieldname"]]); ?></td>
						</tr>

				<?php
						}
						?> </table> <?php
					}	
				?>
	
		</td>
	</tr>
	</table>
	<table width="96%" cellpadding="2" cellspacing="0" class="table-outline-white">
<?php
	if ($oRecord["customerID"] > 0) {
		$result = $dbA->query("select * from $tableCustomerFields where type='C' and visible=1 and internalOnly=1 and incOrdering=1 order by position,fieldID");
		$count = $dbA->count($result);
		if ($count > 0) {
?>
		<tr>
			<td class="table-grey-nocenter" colspan="2" align="left">Internal Customer Account Fields</td>
		</tr>
<?php
		}
		for ($f = 0; $f < $count; $f++) {
			$record = $dbA->fetch($result);
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" width="200"><?php print $record["titleText"]; ?></td>
			<td class="table-white-nocenter-light-s" align="left"><?php print addPadding($custRecord[$record["fieldname"]]); ?></td>
		</tr>
<?php
		}
	}
?>


		<tr>
			<td class="table-grey-nocenter" colspan="2" align="left">Payment Details</td>
		</tr>
<?php
	if ($oRecord["paymentName"] != "") {
?>
		<tr>
			<td class="table-white-nocenter-s" align="left">Payment Type</td>
			<td class="table-white-nocenter-light-s" align="left"><?php print addPadding($oRecord["paymentName"]); ?></td>
		</tr>
<?php
	}
	if ($oRecord["ccName"] != "") {
?>
		<tr>
			<td class="table-white-nocenter-s" align="left">Name On Card</td>
			<td class="table-white-nocenter-light-s" align="left"><?php print addPadding($oRecord["ccName"]); ?></td>
		</tr>
<?php
	}
	if ($oRecord["ccType"] != "") {
?>
		<tr>
			<td class="table-white-nocenter-s" align="left">Credit Card Type</td>
			<td class="table-white-nocenter-light-s" align="left"><?php print addPadding($oRecord["ccType"]); ?></td>
		</tr>
<?php
	}
	if ($oRecord["ccNumber"] != "") {
?>
		<tr>
			<td class="table-white-nocenter-s" align="left">Credit Card Number</td>
			<td class="table-white-nocenter-light-s" align="left">
				<?php 
					if (retrieveOption("ordersSpaceCC") == 0) {
						print addPadding($oRecord["ccNumber"]);
					} else {
						$newCC = "";
						$ccc = 0;
						for ($cc = 0; $cc < strlen($oRecord["ccNumber"]); $cc++) {
							$ccc++;
							$newCC = $newCC . substr($oRecord["ccNumber"],$cc,1);
							if ($ccc == 4) {
								$newCC = $newCC . " ";
								$ccc = 0;
							}
						}
						print $newCC;
					}
				?>
			</td>
		</tr>
<?php
	}
	if ($oRecord["ccExpiryDate"] != "") {
?>
		<tr>
			<td class="table-white-nocenter-s" align="left">Expiry Date</td>
			<td class="table-white-nocenter-light-s" align="left"><?php print addPadding($oRecord["ccExpiryDate"]); ?></td>
		</tr>
<?php
	}
	if ($oRecord["ccStartDate"] != "") {
?>
		<tr>
			<td class="table-white-nocenter-s" align="left">Start Date</td>
			<td class="table-white-nocenter-light-s" align="left"><?php print addPadding($oRecord["ccStartDate"]); ?></td>
		</tr>
<?php
	}
?>
<?php
	if ($oRecord["ccIssue"] != "") {
?>
		<tr>
			<td class="table-white-nocenter-s" align="left">Issue Number</td>
			<td class="table-white-nocenter-light-s" align="left"><?php print addPadding($oRecord["ccIssue"]); ?></td>
		</tr>
<?php
	}
?>
<?php
	if ($oRecord["ccCVV"] != "") {
?>
		<tr>
			<td class="table-white-nocenter-s" align="left">CVV</td>
			<td class="table-white-nocenter-light-s" align="left"><?php print addPadding($oRecord["ccCVV"]); ?></td>
		</tr>
<?php
	}
?>
		<tr>
			<td class="table-grey-nocenter" colspan="2" align="left">Processing Details</td>
		</tr>
		<tr>
			<td class="table-white-nocenter-s" align="left" valign="top">Current Status</td>
			<td class="table-white-nocenter-light-s" align="left"><?php print $currentStatus; ?></td>
		</tr>
		<?php
			if ($oRecord["status"] != "N") {
		?>		
		<tr>
			<td class="table-white-nocenter-s" align="left" valign="top">Payment Processed</td>
			<td class="table-white-nocenter-light-s" align="left"><?php print $orderStatus; ?></td>
		</tr>
		<?php
			}
		?>
<?php
		if ($oRecord["authInfo"] != "") {
			for ($f = 0; $f < count($splitExtraFields); $f++) {
				$thisName = $splitExtraFields[$f][0];
				$thisValue = $splitExtraFields[$f][1];
?>
		<tr>
			<td class="table-white-nocenter-s" align="left"><?php print $thisName; ?></td>
			<td class="table-white-nocenter-light-s" align="left"><?php print addPadding($thisValue); ?></td>
		</tr>

<?php			
			}
		}
?>		
		<?php
			if ($oRecord["status"] == "D" || $oRecord["status"] == "I") {
		?>		
		<tr>
			<td class="table-white-nocenter-s" align="left" valign="top">Dispatch Info</td>
			<td class="table-white-nocenter-light-s" align="left">
			<?php
				$dResult = $dbA->query("select * from $tableDispatches where orderID=$orderID");
				$dCount = $dbA->count($dResult);
				for ($z = 0; $z < $dCount; $z++) {
					$dRecord = $dbA->fetch($dResult);
					$dDate = formatDate($dRecord["dispatchDate"]);
			?>
				<table>
				<tr>
					<td rowspan="2" valign="top"><font class="boldtext"><?php print $z+1; ?></font></td>
					<td class="table-grey-nocenter"><font class="boldtext">Date</font></td>
					<td class="table-grey-nocenter"><font class="boldtext">Tracking Enabled</font></td>
					<?php
						if ($dRecord["trackingEnabled"] == "Y") {
					?>
					<td class="table-grey-nocenter"><font class="boldtext">Tracking Reference</font></td>
					<td class="table-grey-nocenter"><font class="boldtext">Tracking Misc</font></td>
					<td class="table-grey-nocenter"><font class="boldtext">Courier</font></td>
					<?php
						}
					?>
				</tr>
				<tr>
					<td><font class="normaltext"><?php print $dDate; ?></font></td>
					<td><font class="normaltext"><?php print $dRecord["trackingEnabled"]; ?></font></td>
					<?php
						if ($dRecord["trackingEnabled"] == "Y") {
							$cResult = $dbA->query("select * from $tableCouriers where courierID = ".$dRecord["courierID"]);
							$courierName = "n/a";
							if ($dbA->count($cResult) > 0) {
								$cRecord = $dbA->fetch($cResult);
								$courierName = $cRecord["name"];
							}
					?>
					<td><font class="normaltext"><?php print $dRecord["trackingReference"]; ?></font></td>
					<td><font class="normaltext"><?php print $dRecord["trackingMisc"]; ?></font></td>
					<td><font class="normaltext"><?php print $courierName; ?></font></td>
					<?php
						}
					?>
				</tr>
				</table>
			<?php
				}
			?>
			</td>
		</tr>
		<?php
			}
		?>		
<?php
		$result = $dbA->query("select * from $tableCustomerFields where type='O' and visible=1 order by position,fieldID");
		$count = $dbA->count($result);
		if ($count > 0) {
?>
		<tr>
			<td class="table-grey-nocenter" colspan="2" align="left">Extra Order Fields</td>
		</tr>
<?php
		}
		for ($f = 0; $f < $count; $f++) {
				$record = $dbA->fetch($result);
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" width="200"><?php print $record["titleText"]; ?></td>
			<td class="table-white-nocenter-light-s" align="left"><?php print addPadding($oRecord[$record["fieldname"]]); ?></td>
		</tr>
<?php
		}
?>

		<tr>
			<td class="table-grey-nocenter" colspan="2" align="left">Other Fields</td>
		</tr>
		<tr>
			<td class="table-white-nocenter-s" align="left" valign="top">Internal Order Notes</td>
			<td class="table-white-nocenter-light-s" align="left"><?php print eregi_replace("\r\n","<BR>",addPadding($oRecord["orderNotes"])); ?></td>
		</tr>		
<?php
			$fieldSplit = split("]",@$oRecord["otherFields"]);
			for ($f = 0; $f < count($fieldSplit); $f++) {
				$thisField = split("\|",$fieldSplit[$f]);
				$thisName = $thisField[0];
				$thisValue = @$thisField[1];
				if (substr($thisName,0,1)=="[") {
					$thisName = substr($thisName,1,strlen($thisName)-1);
				}
				if (substr($thisValue,strlen($thisValue)-1,1)=="]") {
					$thisValue = substr($thisValue,0,strlen($thisValue)-1);
				}
				if ($thisName != "") {
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" valign="top"><?php print $thisName; ?></td>
			<td class="table-white-nocenter-light-s" align="left"><?php print addPadding($thisValue); ?></td>
		</tr>

<?php			
				}
			}
?>
		<tr>
			<td class="table-white-nocenter-s" align="left" valign="top">IP Address</td>
			<td class="table-white-nocenter-light-s" align="left"><?php print addPadding($oRecord["ip"]); ?></td>
		</tr>
		<?php
			if ($oRecord["affiliateID"] != 0) {
		?>		
		<tr>
			<td class="table-white-nocenter-s" align="left" valign="top">Affiliate</td>
			<td class="table-white-nocenter-light-s" align="left"><?php print $affiliateName; ?></td>
		</tr>
		<?php
			}
		?>			
		<?php
			if ($oRecord["referURL"] != "") {
		?>		
		<tr>
			<td class="table-white-nocenter-s" align="left" valign="top">Referring Site</td>
			<td class="table-white-nocenter-light-s" align="left"><?php print $oRecord["referURL"]; ?></td>
		</tr>
		<?php
			}
		?>		
		</table>

</center>
<p><br>
<?php if ($onePrinted == true) { echo "</div>"; } ?>