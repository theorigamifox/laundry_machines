<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTP,$tableCountries;
		$gatewayOptions = retrieveGatewayOptions("2CHECKOUT");
		
		$cDetails = returnCurrencyDetails($orderArray["currencyID"]);
		
		$rnd = md5($orderArray["randID"]);
		
		$tpl = new tSys("templates/","gatewaytransfer.html",$requiredVars,0);	
		
		$gArray["method"] = "POST";
		$gArray["action"] = "https://www.2checkout.com/cgi-bin/sbuyers/cartpurchase.2c";
		$gArray["fields"][] = array("name"=>"sid","value"=>$gatewayOptions["accountNumber"]);
		$gArray["fields"][] = array("name"=>"cart_order_id","value"=>$orderNumber);
		$gArray["fields"][] = array("name"=>"total","value"=>number_format($orderArray["orderTotal"],2,'.',''));
		$gArray["fields"][] = array("name"=>"card_holder_name","value"=>$orderArray["title"]." ".$orderArray["forename"]." ".$orderArray["surname"]);
		$gArray["fields"][] = array("name"=>"street_address","value"=>$orderArray["address1"]);
		$gArray["fields"][] = array("name"=>"city","value"=>$orderArray["town"]);
		$gArray["fields"][] = array("name"=>"state","value"=>$orderArray["county"]);
		$gArray["fields"][] = array("name"=>"zip","value"=>$orderArray["postcode"]);
		$gArray["fields"][] = array("name"=>"country","value"=>$orderArray["country"]);
		$gArray["fields"][] = array("name"=>"phone","value"=>$orderArray["telephone"]);
		$gArray["fields"][] = array("name"=>"email","value"=>$orderArray["email"]);
		$gArray["fields"][] = array("name"=>"rnd","value"=>$rnd);
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
