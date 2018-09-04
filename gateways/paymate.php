<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTP,$jssStoreWebDirHTTPS;
		$gatewayOptions = retrieveGatewayOptions("PAYMATE");
		
		$cDetails = returnCurrencyDetails($orderArray["currencyID"]);

		$rIN = md5($orderArray["randID"]);
		
		$tpl = new tSys("templates/","gatewaytransfer.html",$requiredVars,0);	
		
		$gArray["method"] = "GET";
		$gArray["action"] = "https://www.paymate.com.au/PayMate/ExpressPayment";
		$gArray["fields"][] = array("name"=>"mid","value"=>$gatewayOptions["mid"]);
		$gArray["fields"][] = array("name"=>"amt","value"=>number_format($orderArray["orderTotal"],2,'.',''));
		$gArray["fields"][] = array("name"=>"ref","value"=>$orderNumber);
		$gArray["fields"][] = array("name"=>"curr","value"=>@$cDetails["code"]);
		$gArray["fields"][] = array("name"=>"PMT_SENDER_EMAIL","value"=>$orderArray["email"]);
		$gArray["fields"][] = array("name"=>"PMT_CONTACT_FIRSTNAME","value"=>$orderArray["forename"]);
		$gArray["fields"][] = array("name"=>"PMT_CONTACT_SURNAME","value"=>$orderArray["surname"]);
		$gArray["fields"][] = array("name"=>"regindi_address1","value"=>$orderArray["address1"]);
		$gArray["fields"][] = array("name"=>"regindi_address2","value"=>$orderArray["address2"]);
		$gArray["fields"][] = array("name"=>"regindi_sub","value"=>$orderArray["town"]);
		$gArray["fields"][] = array("name"=>"regindi_pcode","value"=>$orderArray["postcode"]);
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
