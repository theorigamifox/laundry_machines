<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTPS;
		$gatewayOptions = retrieveGatewayOptions("NOCHEX");
		
		$callBack = "$jssStoreWebDirHTTPS"."gateways/response/nochex.php";
		
		$cDetails = returnCurrencyDetails($orderArray["currencyID"]);

		$rIN = md5($orderArray["randID"]);
		
		$tpl = new tSys("templates/","gatewaytransfer.html",$requiredVars,0);	
		
		$gArray["method"] = "POST";
		$gArray["action"] = "https://www.nochex.com/nochex.dll/checkout";
		$gArray["fields"][] = array("name"=>"email","value"=>$gatewayOptions["account"]);
		$gArray["fields"][] = array("name"=>"amount","value"=>number_format($orderArray["orderTotal"],2,'.',''));
		$gArray["fields"][] = array("name"=>"description","value"=>$gatewayOptions["description"]);
		if ($gatewayOptions["logo"] != "") {
			$gArray["fields"][] = array("name"=>"logo","value"=>$gatewayOptions["logo"]);
		}
		$gArray["fields"][] = array("name"=>"ordernumber","value"=>$orderNumber);
		$gArray["fields"][] = array("name"=>"returnurl","value"=>$callBack."?xOid=".$orderNumber."&xRn=".$rIN);
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
