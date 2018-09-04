<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTP;
		$gatewayOptions = retrieveGatewayOptions("SECUREHOSTING");
		
		$callBack = "$jssStoreWebDirHTTP"."gateways/response/securehosting.php";
		
		$cDetails = returnCurrencyDetails($orderArray["currencyID"]);	
		
		$md5string = md5("$orderNumber".$gatewayOptions["shreference"]."YES");
		$callBackData = "onum|$orderNumber|check|$md5string";	

		$billingAddress  = $orderArray["address1"].", ";
		$billingAddress .= $orderArray["town"].", ";
		$billingAddress .= $orderArray["county"].", ";
		$billingAddress .= $orderArray["country"];	
		
		$tpl = new tSys("templates/","gatewaytransfer.html",$requiredVars,0);	
		
		$gArray["method"] = "POST";
		$gArray["action"] = "https://www.secure-server-hosting.com/secutran/secuitems.php";
		$gArray["fields"][] = array("name"=>"shreference","value"=>$gatewayOptions["shreference"]);
		$gArray["fields"][] = array("name"=>"checkcode","value"=>$gatewayOptions["checkcode"]);
		$gArray["fields"][] = array("name"=>"filename","value"=>$gatewayOptions["shreference"]."/".$gatewayOptions["filename"]);
		$gArray["fields"][] = array("name"=>"transactionamount","value"=>number_format($orderArray["orderTotal"],2,'.',''));
		$gArray["fields"][] = array("name"=>"OrderNumber","value"=>$orderNumber);
		$gArray["fields"][] = array("name"=>"transactioncurrency","value"=>@$cDetails["code"]);
		$gArray["fields"][] = array("name"=>"cardholdersname","value"=>$orderArray["title"]." ".$orderArray["forename"]." ".$orderArray["surname"]);
		$gArray["fields"][] = array("name"=>"address","value"=>$billingAddress);
		$gArray["fields"][] = array("name"=>"postcode","value"=>$orderArray["postcode"]);
		$gArray["fields"][] = array("name"=>"telephone","value"=>$orderArray["telephone"]);
		$gArray["fields"][] = array("name"=>"cardholdersemail","value"=>$orderArray["email"]);
		$gArray["fields"][] = array("name"=>"callbackurl","value"=>$callBack);
		$gArray["fields"][] = array("name"=>"callbackdata","value"=>$callBackData);
		$gArray["fields"][] = array("name"=>"merchanturl","value"=>$jssStoreWebDirHTTP."process.php?xOid=$orderNumber&xRn=".$orderArray["randID"]);
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
