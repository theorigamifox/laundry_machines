<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTP,$tableCountries;
		$gatewayOptions = retrieveGatewayOptions("VELOCITYPAYFORM");
		
		$callBack = "$jssStoreWebDirHTTP"."gateways/response/velocitypayform.php";

		
		$cDetails = returnCurrencyDetails($orderArray["currencyID"]);

		$billingAddress  = $orderArray["address1"]."\n";
		if ($orderArray["address2"] != "") {
			$billingAddress .= $orderArray["address2"]."\n";
		}
		$billingAddress .= $orderArray["town"]."\n";
		$billingAddress .= $orderArray["county"];

		$countryMatch = $orderArray["country"];
		$cResult = $dbA->query("select * from $tableCountries where name=\"$countryMatch\"");
		if (count($cResult) > 0) {
			$cRecord = $dbA->fetch($cResult);
			$customerCountry = $cRecord["isonumber"];
		} else {
			$customerCountry = "";
		}

		$tpl = new tSys("templates/","gatewaytransfer.html",$requiredVars,0);	
		
		$gArray["method"] = "POST";
		$gArray["action"] = $gatewayOptions["formURL"];
		$gArray["fields"][] = array("name"=>"VPMerchantID","value"=>$gatewayOptions["VPMerchantID"]);
		$gArray["fields"][] = array("name"=>"VPMerchantPassword","value"=>$gatewayOptions["VPMerchantPassword"]);
		$gArray["fields"][] = array("name"=>"VPCountryCode","value"=>$gatewayOptions["VPCountryCode"]);
		$gArray["fields"][] = array("name"=>"VPAmount","value"=>number_format($orderArray["orderTotal"]*100,0,'.',''));
		$gArray["fields"][] = array("name"=>"VPCurrencyCode","value"=>@$cDetails["isonumber"]);
		$gArray["fields"][] = array("name"=>"VPTransactionUnique","value"=>md5($orderNumber.":VP"));
		$gArray["fields"][] = array("name"=>"VPOrderDesc","value"=>$orderNumber);

		$gArray["fields"][] = array("name"=>"VPCallBack","value"=>$callBack);

		$gArray["fields"][] = array("name"=>"VPMailingAddress","value"=>$billingAddress);
		$gArray["fields"][] = array("name"=>"VPMailingCountry","value"=>$customerCountry);
		$gArray["fields"][] = array("name"=>"VPMailingPostCode","value"=>$orderArray["postcode"]);
		$gArray["fields"][] = array("name"=>"VPMailingName","value"=>$orderArray["title"]." ".$orderArray["forename"]." ".$orderArray["surname"]);
		$gArray["fields"][] = array("name"=>"VPEmail","value"=>$orderArray["email"]);

		
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
