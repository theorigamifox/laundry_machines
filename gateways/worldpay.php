<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTP,$tableCountries;
		$gatewayOptions = retrieveGatewayOptions("WORLDPAY");
		
		//$callBack = "$jssStoreWebDirHTTP"."gateways/response/worldpay.php";
		
		$callBack = "$jssStoreWebDirHTTP"."gateways/response/worldpay.php";
		$callBack = str_replace("http://","",$callBack);
		$callBack = str_replace("https://","",$callBack);
		
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
		$gArray["action"] = "https://select.worldpay.com/wcc/purchase";
		$gArray["fields"][] = array("name"=>"instId","value"=>$gatewayOptions["installationID"]);
		$gArray["fields"][] = array("name"=>"cartId","value"=>$orderNumber);
		$gArray["fields"][] = array("name"=>"amount","value"=>number_format($orderArray["orderTotal"],2,'.',''));
		$gArray["fields"][] = array("name"=>"currency","value"=>@$cDetails["code"]);
		$gArray["fields"][] = array("name"=>"desc","value"=>$gatewayOptions["description"]);
		$gArray["fields"][] = array("name"=>"testMode","value"=>$gatewayOptions["testmode"]);
		$gArray["fields"][] = array("name"=>"authMode","value"=>$gatewayOptions["authmode"]);
		$gArray["fields"][] = array("name"=>"name","value"=>$orderArray["title"]." ".$orderArray["forename"]." ".$orderArray["surname"]);
		$gArray["fields"][] = array("name"=>"address","value"=>$billingAddress);
		$gArray["fields"][] = array("name"=>"postcode","value"=>$orderArray["postcode"]);
		$gArray["fields"][] = array("name"=>"country","value"=>$customerCountry);
		$gArray["fields"][] = array("name"=>"tel","value"=>$orderArray["telephone"]);
		$gArray["fields"][] = array("name"=>"email","value"=>$orderArray["email"]);
		$gArray["fields"][] = array("name"=>"MC_callback","value"=>$callBack);
		$mArray = $gArray;
		
		$gArray["process"] = "document.automaticForm.submit();";
		
		$tpl->addVariable("shop",templateVarsShopRetrieve());
		$tpl->addVariable("labels",templateVarsLabelsRetrieve());
		$tpl->addVariable("automaticForm",$gArray);
		$tpl->addVariable("manualForm",$mArray);
		$tpl->showPage();
		exit;
	}
?>
