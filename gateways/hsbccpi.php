<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTPS,$tableCountries;
		$gatewayOptions = retrieveGatewayOptions("HSBCCPI");
		
		$cDetails = returnCurrencyDetails($orderArray["currencyID"]);
		
		$callBack = "$jssStoreWebDirHTTPS"."process.php";
		
		$rIN = $orderArray["randID"];

		$cpiDirectResultUrl = $gatewayOptions["CpiReturnUrl"];
		$cpiReturnUrl = $callBack."?xOid=".$orderNumber."&xRn=".$rIN;
		$merchantData = "";
		$mode = $gatewayOptions["Mode"];
		$orderDesc = $gatewayOptions["OrderDesc"];
		$orderId = $orderNumber;
		$purchaseAmount = number_format($orderArray["orderTotal"],2,'','');
		$purchaseCurrency = "826";
		$storefrontId = $gatewayOptions["StorefrontId"];
		$timeStamp = gmmktime()."000";
		$transactionType = $gatewayOptions["TransactionType"];
		$userId = "";

		$call = $gatewayOptions["EncryptLocation"]." ".$gatewayOptions["EncryptionKey"]." $cpiDirectResultUrl $cpiReturnUrl $merchantData $mode $orderDesc $orderId $purchaseAmount $purchaseCurrency $storefrontId $timeStamp $transactionType $userId";
		$one = system($call, $two);

		$orderhash = substr($one, 13,28);
		
		$countryMatch = $orderArray["country"];
		$cResult = $dbA->query("select * from $tableCountries where name=\"$countryMatch\"");
		if (count($cResult) > 0) {
			$cRecord = $dbA->fetch($cResult);
			$customerCountry = $cRecord["isocode"];
		} else {
			$customerCountry = "";
		}
		
?>
		<HTML>
		<HEAD>
		<TITLE></TITLE>
		</HEAD>
		<BODY>
    	<FORM name="processForm" action="https://www.cpi.hsbc.com/servlet" method="POST">
        <INPUT type="hidden" name="CpiReturnUrl" value="<?php print $callBack; ?>?xOid=<?php print $orderNumber; ?>&xRn=<?php print $rIN; ?>">
        <INPUT type="hidden" name="CpiDirectResultUrl" value="<?php print $gatewayOptions["CpiReturnUrl"]; ?>">
		<INPUT type="hidden" name="OrderId" value="<?php print $orderNumber; ?>">
		<INPUT type="hidden" name="TimeStamp" value="<?php print gmmktime()."000"; ?>">
		<INPUT type="hidden" name="StorefrontId" value="<?php print $gatewayOptions["StorefrontId"]; ?>">
		<INPUT type="hidden" name="OrderDesc" value="<?php print $gatewayOptions["OrderDesc"]; ?>">
		<INPUT type="hidden" name="PurchaseAmount" value="<?php print number_format($orderArray["orderTotal"],2,'',''); ?>">
		<INPUT type="hidden" name="PurchaseCurrency" value="826">
		<INPUT type="hidden" name="TransactionType" value="<?php print $gatewayOptions["TransactionType"]; ?>">
		<INPUT type="hidden" name="Mode" value="<?php print $gatewayOptions["Mode"]; ?>">
		<INPUT type="hidden" name="OrderHash" value="<?php echo $orderhash; ?>">

		<!--<INPUT TYPE=HIDDEN NAME="BillingFirstName" VALUE="<?php print $orderArray["forename"]; ?>">
		<INPUT TYPE=HIDDEN NAME="BillingLastName" VALUE="<?php print $orderArray["surname"]; ?>">
		<INPUT TYPE=HIDDEN NAME="BillingAddress1" VALUE="<?php print $orderArray["address1"]; ?>">
		<INPUT TYPE=HIDDEN NAME="BillingAddress2" VALUE="<?php print $orderArray["address2"]; ?>">
		<INPUT TYPE=HIDDEN NAME="BillingTown" VALUE="<?php print $orderArray["town"]; ?>">
		<INPUT TYPE=HIDDEN NAME="BillingCounty" VALUE="<?php print $orderArray["county"]; ?>">
		<INPUT TYPE=HIDDEN NAME="BillingCountry" VALUE="<?php print $customerCountry; ?>">
		<INPUT TYPE=HIDDEN NAME="BillingPostal" VALUE="<?php print $orderArray["postcode"]; ?>">
		<INPUT TYPE=HIDDEN NAME="ShopperEmail" VALUE="<?php print $orderArray["email"]; ?>">-->		
		</FORM>
		<script language="JavaScript">
			document.processForm.submit();
		</script>
		</BODY>
		</HTML>
<?php
	}
?>
