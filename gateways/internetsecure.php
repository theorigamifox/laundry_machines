<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTP,$tableCountries;

		$callBack = "$jssStoreWebDirHTTP"."process.php?xOid=$orderNumber&xRn=".$orderArray["randID"]."&xFS=1";
		
		$gatewayOptions = retrieveGatewayOptions("INTERNETSECURE");
		
		$cDetails = returnCurrencyDetails($orderArray["currencyID"]);
		
		$rnd = md5($orderArray["randID"]);
		
		$productString = "Price::Qty::Order::Description::Flags|";
		$productString .=number_format($orderArray["orderTotal"],2,'.','')."::1::".$orderNumber."::".$gatewayOptions["description"]."::";
		
		if (@$cDetails["code"] == "USD") {
			$productString .= "{US}";
		}
		$productString .= $gatewayOptions["testmode"];
		
		$tpl = new tSys("templates/","gatewaytransfer.html",$requiredVars,0);	
		
		$gArray["method"] = "POST";
		$gArray["action"] = "https://secure.internetsecure.com/process.cgi";
		$gArray["fields"][] = array("name"=>"MerchantNumber","value"=>$gatewayOptions["MerchantNumber"]);
		$gArray["fields"][] = array("name"=>"language","value"=>$gatewayOptions["language"]);
		$gArray["fields"][] = array("name"=>"ReturnURL","value"=>$callBack);
		$gArray["fields"][] = array("name"=>"SalesOrderNumber","value"=>$orderNumber);
		$gArray["fields"][] = array("name"=>"Products","value"=>$productString);
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
