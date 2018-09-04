<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTP,$jssStoreWebDirHTTPS;
		
		$callBack = "$jssStoreWebDirHTTPS"."gateways/response/protx.php";
		
		$cDetails = returnCurrencyDetails($orderArray["currencyID"]);

		$gatewayOptions = retrieveGatewayOptions("PROTX");
		if ($gatewayOptions["testMode"]=="Y") {
			$myAction = "https://ukvpstest.protx.com/vps2form/submit.asp";
		} else {
			$myAction = "https://ukvps.protx.com/vps2form/submit.asp";
		}
		$myVendor = $gatewayOptions["vendor"];
		$myEncryptionPassword = $gatewayOptions["encryptionPassword"];
		
		$billingAddress  = $orderArray["address1"]."\n";
		if ($orderArray["address2"] != "") {
			$billingAddress .= $orderArray["address2"]."\n";
		}
		$billingAddress .= $orderArray["town"]."\n";
		$billingAddress .= $orderArray["county"]."\n";
		$billingAddress .= $orderArray["country"];
		
		
		$crypt = "VendorTxCode=$orderNumber";
		$crypt .= "&Amount=".number_format($orderArray["orderTotal"],2,'.','');
		$crypt .= "&Currency=".@$cDetails["code"];
		$crypt .= "&Description=".$gatewayOptions["description"];
		$crypt .= "&SuccessURL=$callBack?xOid=$orderNumber&xRn=".$orderArray["randID"];
		$crypt .= "&FailureURL=$callBack?xOid=$orderNumber&xRn=".$orderArray["randID"];
		$crypt .= "&CustomerName=".$orderArray["title"]." ".$orderArray["forename"]." ".$orderArray["surname"];
		$crypt .= "&BillingAddress=".$billingAddress;
		$crypt .= "&BillingPostcode=".$orderArray["postcode"];
		if ($gatewayOptions["sendEmail"] == 1) {
			$crypt .= "&CustomerEmail=".$orderArray["email"];
		}
		$crypt .= "&VendorEmail=".$gatewayOptions["vendorEmail"];
		
		$crypt = base64_encode(protx_simpleXor($crypt,$myEncryptionPassword));
		
		$tpl = new tSys("templates/","gatewaytransfer.html",$requiredVars,0);	
		
		$gArray["method"] = "POST";
		$gArray["action"] = $myAction;
		$gArray["fields"][] = array("name"=>"VPSProtocol","value"=>"2.21");
		$gArray["fields"][] = array("name"=>"Vendor","value"=>$myVendor);
		$gArray["fields"][] = array("name"=>"TxType","value"=>$gatewayOptions["txType"]);
		$gArray["fields"][] = array("name"=>"Crypt","value"=>$crypt);
		
		$mArray = $gArray;
		
		$gArray["process"] = "document.automaticForm.submit();";

		$tpl->addVariable("shop",templateVarsShopRetrieve());
		$tpl->addVariable("labels",templateVarsLabelsRetrieve());

		$tpl->addVariable("automaticForm",$gArray);
		$tpl->addVariable("manualForm",$mArray);
		$tpl->showPage();
	}

	function protx_simpleXor($inString, $key) {
		$outString="";
		$l=0;
		if (strlen($inString)!=0) {
			for ($i = 0; $i < strlen($inString); $i++) {
	   			$outString=$outString . ($inString[$i]^$key[$l]);
	   			$l++;
	   			if ($l==strlen($key)) { $l=0; }
			}
		}
		return $outString;
	}	
?>
