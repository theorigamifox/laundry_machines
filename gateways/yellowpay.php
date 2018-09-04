<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTP,$tableCountries;
		$gatewayOptions = retrieveGatewayOptions("WORLDPAY");
		
		//$callBack = "$jssStoreWebDirHTTP"."gateways/response/worldpay.php";
		
		//$callBack = "$jssStoreWebDirHTTP"."gateways/response/worldpay.php";
		//$callBack = str_replace("http://","",$callBack);
		//$callBack = str_replace("https://","",$callBack);
		
		$cDetails = returnCurrencyDetails($orderArray["currencyID"]);
		
		$billingAddress  = $orderArray["address1"]."\r\n";
		$billingAddress .= $orderArray["address2"]."\r\n";
		$billingAddress .= $orderArray["town"]."\r\n";
		$billingAddress .= $orderArray["county"]."\r\n";
		$billingAddress .= $orderArray["country"]."\r\n";

		$countryMatch = $orderArray["country"];
		$cResult = $dbA->query("select * from $tableCountries where name=\"$countryMatch\"");
		if (count($cResult) > 0) {
			$cRecord = $dbA->fetch($cResult);
			$customerCountry = $cRecord["isocode"];
		} else {
			$customerCountry = "";
		}
		
		$tpl = new tSys("templates/","gatewaytransfer.html",$requiredVars,0);	
		
		$gArray["method"] = "POST";
		$gArray["action"] = "https://yellowpaytest.postfinance.ch/checkout/Yellowpay.aspx?userctrl=Invisible";
		$gArray["fields"][] = array("name"=>"txtShopId","value"=>$gatewayOptions["shopID"]);
		$gArray["fields"][] = array("name"=>"txtOrderIDShop","value"=>$orderNumber);
		$gArray["fields"][] = array("name"=>"txtOrderAmount","value"=>number_format($orderArray["orderTotal"],2,'.',''));
		$gArray["fields"][] = array("name"=>"txtArtCurrency","value"=>@$cDetails["code"]);
		$gArray["fields"][] = array("name"=>"DeliveryPaymentType","value"=>$gatewayOptions["paytype"]);
		$mArray = $gArray;
		
		$gArray["process"] = "document.automaticForm.submit();";
		

		$tpl->addVariable("automaticForm",$gArray);
		$tpl->addVariable("manualForm",$mArray);
		$tpl->showPage();
		exit;
	}
?>
