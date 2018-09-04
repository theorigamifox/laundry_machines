<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTPS;
		$gatewayOptions = retrieveGatewayOptions("GOEMERCHANT");
		
		$callBack = "$jssStoreWebDirHTTPS"."gateways/response/goemerchant.php";
		
		$tpl = new tSys("templates/","gatewaytransfer.html",$requiredVars,0);	
		
		$gArray["method"] = "POST";
		$gArray["action"] = "https://www.goemerchant7.com/cgi-bin/gateway/process.cgi";
		$gArray["fields"][] = array("name"=>"Merchant","value"=>$gatewayOptions["merchantname"]);
		$gArray["fields"][] = array("name"=>"OrderID","value"=>$orderNumber);
		$gArray["fields"][] = array("name"=>"total","value"=>number_format($orderArray["orderTotal"],2,'.',''));
		$gArray["fields"][] = array("name"=>"email","value"=>$orderArray["email"]);
		$gArray["fields"][] = array("name"=>"URL","value"=>$callBack);
		$gArray["fields"][] = array("name"=>"NameonCard","value"=>$orderArray["title"]." ".$orderArray["forename"]." ".$orderArray["surname"]);
		$gArray["fields"][] = array("name"=>"Cardstreet","value"=>$orderArray["address1"]);
		$gArray["fields"][] = array("name"=>"Cardcity","value"=>$orderArray["town"]);
		$gArray["fields"][] = array("name"=>"Cardstate","value"=>$orderArray["county"]);
		$gArray["fields"][] = array("name"=>"Cardzip","value"=>$orderArray["postcode"]);
		$gArray["fields"][] = array("name"=>"Cardcountry","value"=>$orderArray["country"]);
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
