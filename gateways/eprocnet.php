<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTPS,$tableCountries;
		$gatewayOptions = retrieveGatewayOptions("EPROCNET");
		
		$cDetails = returnCurrencyDetails($orderArray["currencyID"]);
		
		$rnd = md5($orderArray["randID"]);
		
		$callBack = "$jssStoreWebDirHTTPS"."gateways/response/eprocnet.php";
		$approvedURL = $callBack."?eproc=Y";
		$declineURL = $callBack."?eproc=N";
		
		$tpl = new tSys("templates/","gatewaytransfer.html",$requiredVars,0);	
		
		$gArray["method"] = "POST";
		$gArray["action"] = "https://www.eProcessingNetwork.com/cgi-bin/dbe/order.pl";
		$gArray["fields"][] = array("name"=>"ePNAccount","value"=>$gatewayOptions["ePNAccount"]);
		$gArray["fields"][] = array("name"=>"Total","value"=>number_format($orderArray["orderTotal"],2,'.',''));
		$gArray["fields"][] = array("name"=>"Address","value"=>$orderArray["address1"]);
		$gArray["fields"][] = array("name"=>"Zip","value"=>$orderArray["postcode"]);
		$gArray["fields"][] = array("name"=>"Email","value"=>$orderArray["email"]);
		$gArray["fields"][] = array("name"=>"ID","value"=>$orderNumber);
		$gArray["fields"][] = array("name"=>"ApprovedURL","value"=>$approvedURL);
		$gArray["fields"][] = array("name"=>"DeclinedURL","value"=>$declineURL);
		$gArray["fields"][] = array("name"=>"BackgroundColor","value"=>$gatewayOptions["BackgroundColor"]);
		$gArray["fields"][] = array("name"=>"TextColor","value"=>$gatewayOptions["TextColor"]);
		$gArray["fields"][] = array("name"=>"Redirect","value"=>"1");
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
