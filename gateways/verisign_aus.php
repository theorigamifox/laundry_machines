<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTP;
		$gatewayOptions = retrieveGatewayOptions("VERISIGN_AUS");
		
		$callBack = "$jssStoreWebDirHTTP"."gateways/response/verisign_aus.php";
		
		$cDetails = returnCurrencyDetails($orderArray["currencyID"]);
		
		$randEncoded = substr(md5($orderArray["randID"]),0,30);
		
		$tpl = new tSys("templates/","gatewaytransfer.html",$requiredVars,0);	
		
		$gArray["method"] = "POST";
		$gArray["action"] = "https://payments.verisign.com.au/payflowlink";
		$gArray["fields"][] = array("name"=>"LOGIN","value"=>$gatewayOptions["login"]);
		$gArray["fields"][] = array("name"=>"PARTNER","value"=>$gatewayOptions["partner"]);
		$gArray["fields"][] = array("name"=>"AMOUNT","value"=>number_format($orderArray["orderTotal"],2,'.',''));
		$gArray["fields"][] = array("name"=>"TYPE","value"=>$gatewayOptions["type"]);
		$gArray["fields"][] = array("name"=>"DESCRIPTION","value"=>$gatewayOptions["description"]);
		$gArray["fields"][] = array("name"=>"NAME","value"=>$orderArray["forename"]." ".$orderArray["surname"]);
		$gArray["fields"][] = array("name"=>"ADDRESS","value"=>$orderArray["address1"]);
		$gArray["fields"][] = array("name"=>"CITY","value"=>$orderArray["town"]);
		$gArray["fields"][] = array("name"=>"STATE","value"=>$orderArray["county"]);
		$gArray["fields"][] = array("name"=>"COUNTRY","value"=>$orderArray["country"]);
		$gArray["fields"][] = array("name"=>"ZIP","value"=>$orderArray["postcode"]);
		$gArray["fields"][] = array("name"=>"INVOICE","value"=>$orderNumber);
		$gArray["fields"][] = array("name"=>"USER1","value"=>$randEncoded);
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
