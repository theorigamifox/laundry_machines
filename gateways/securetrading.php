<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTP,$tableCountries;
		$gatewayOptions = retrieveGatewayOptions("SECURETRADING");
		
		$cDetails = returnCurrencyDetails($orderArray["currencyID"]);
		
		$rnd = md5($orderArray["randID"]);
		
		$tpl = new tSys("templates/","gatewaytransfer.html",$requiredVars,0);	
		
		$gArray["method"] = "POST";
		$gArray["action"] = "https://securetrading.net/authorize/form.cgi";
		$gArray["fields"][] = array("name"=>"merchant","value"=>$gatewayOptions["merchantID"]);
		$gArray["fields"][] = array("name"=>"merchantemail","value"=>$gatewayOptions["email"]);
		$gArray["fields"][] = array("name"=>"orderref","value"=>$orderNumber);
		$gArray["fields"][] = array("name"=>"currency","value"=>@$cDetails["code"]);
		$gArray["fields"][] = array("name"=>"amount","value"=>number_format($orderArray["orderTotal"]*100,0,'.',''));
		$gArray["fields"][] = array("name"=>"customeremail","value"=>$gatewayOptions["emailconf"]);
		$gArray["fields"][] = array("name"=>"callbackurl","value"=>"1");
		$gArray["fields"][] = array("name"=>"failureurl","value"=>"1");
		$gArray["fields"][] = array("name"=>"name","value"=>$orderArray["title"]." ".$orderArray["forename"]." ".$orderArray["surname"]);
		$gArray["fields"][] = array("name"=>"address","value"=>$orderArray["address1"]);
		$gArray["fields"][] = array("name"=>"town","value"=>$orderArray["town"]);
		$gArray["fields"][] = array("name"=>"county","value"=>$orderArray["county"]);
		$gArray["fields"][] = array("name"=>"postcode","value"=>$orderArray["postcode"]);
		$gArray["fields"][] = array("name"=>"country","value"=>$orderArray["country"]);
		$gArray["fields"][] = array("name"=>"telephone","value"=>$orderArray["telephone"]);
		$gArray["fields"][] = array("name"=>"email","value"=>$orderArray["email"]);
		$gArray["fields"][] = array("name"=>"company","value"=>$orderArray["company"]);
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
