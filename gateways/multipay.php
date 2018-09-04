<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTP;
		$gatewayOptions = retrieveGatewayOptions("MULTIPAY");
		
		$callBack = "$jssStoreWebDirHTTP"."gateways/response/multipay.php";
		
		$cDetails = returnCurrencyDetails($orderArray["currencyID"]);		
		
		$tpl = new tSys("templates/","gatewaytransfer.html",$requiredVars,0);	
		
		$gArray["method"] = "POST";
		$gArray["action"] = "https://multipay.net/transaction/mpmain.php";
		$gArray["fields"][] = array("name"=>"mpAdministration_ID","value"=>$gatewayOptions["adminID"]);
		$gArray["fields"][] = array("name"=>"mpSeller_ID","value"=>$gatewayOptions["sellerID"]);
		$gArray["fields"][] = array("name"=>"mpOrder_ID","value"=>$orderNumber);
		$gArray["fields"][] = array("name"=>"mpDescription","value"=>$gatewayOptions["description"]);
		$gArray["fields"][] = array("name"=>"mpItems","value"=>$gatewayOptions["description"]);
		$gArray["fields"][] = array("name"=>"mpCountry","value"=>$gatewayOptions["country"]);
		$gArray["fields"][] = array("name"=>"mpLanguage","value"=>$gatewayOptions["language"]);
		$gArray["fields"][] = array("name"=>"mpCurrency","value"=>$cDetails["code"]);
		$gArray["fields"][] = array("name"=>"mpType","value"=>"WEB");
		$gArray["fields"][] = array("name"=>"mpSuccessURL","value"=>$callBack);
		$gArray["fields"][] = array("name"=>"mpAmount","value"=>number_format($orderArray["orderTotal"],2,'.',''));
		$gArray["fields"][] = array("name"=>"mpnaw_email","value"=>$orderArray["email"]);
		$gArray["fields"][] = array("name"=>"mpnaw_last","value"=>$orderArray["surname"]);
		$gArray["fields"][] = array("name"=>"mpnaw_first","value"=>$orderArray["forename"]);
		$gArray["fields"][] = array("name"=>"mpnaw_street","value"=>$orderArray["address1"]);
		$gArray["fields"][] = array("name"=>"mpnaw_zipcode","value"=>$orderArray["postcode"]);
		$gArray["fields"][] = array("name"=>"mpnaw_city","value"=>$orderArray["town"]);
		$gArray["fields"][] = array("name"=>"mpnaw_country","value"=>$orderArray["country"]);
		$gArray["fields"][] = array("name"=>"mpnaw_telno","value"=>$orderArray["telephone"]);
		$gArray["fields"][] = array("name"=>"mpallow","value"=>$gatewayOptions["allowpayment"]);
		
		
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
