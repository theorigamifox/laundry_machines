<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTP,$jssStoreWebDirHTTPS;
		$gatewayOptions = retrieveGatewayOptions("PAYPAL");
		
		$callBack = "$jssStoreWebDirHTTP"."gateways/response/paypal.php";
		$successLink = "$jssStoreWebDirHTTPS"."process.php?xOid=$orderNumber&xRn=".$orderArray["randID"]."&xFS=1";
		$failureLink = "$jssStoreWebDirHTTPS"."process.php?xOid=$orderNumber&xRn=".$orderArray["randID"]."&xFF=1";
		
		$cDetails = returnCurrencyDetails($orderArray["currencyID"]);

		$rIN = md5($orderArray["randID"]);
		
		$tpl = new tSys("templates/","gatewaytransfer.html",$requiredVars,0);	
		
		$gArray["method"] = "POST";
		$gArray["action"] = "https://www.paypal.com/cgi-bin/webscr";
		$gArray["fields"][] = array("name"=>"cmd","value"=>"_xclick");
		$gArray["fields"][] = array("name"=>"business","value"=>$gatewayOptions["account"]);
		$gArray["fields"][] = array("name"=>"amount","value"=>number_format($orderArray["orderTotal"],2,'.',''));
		$gArray["fields"][] = array("name"=>"item_name","value"=>$gatewayOptions["description"]);
		if ($gatewayOptions["logo"] != "") {
			$gArray["fields"][] = array("name"=>"image_url","value"=>$gatewayOptions["logo"]);
		}
		$gArray["fields"][] = array("name"=>"no_shipping","value"=>"1");
		$gArray["fields"][] = array("name"=>"no_note","value"=>"1");
		$gArray["fields"][] = array("name"=>"invoice","value"=>$orderNumber);
		$gArray["fields"][] = array("name"=>"return","value"=>$successLink);
		$gArray["fields"][] = array("name"=>"cancel_return","value"=>$failureLink);
		$gArray["fields"][] = array("name"=>"notify_url","value"=>$callBack);
		$gArray["fields"][] = array("name"=>"address1","value"=>$orderArray["address1"]);
		$gArray["fields"][] = array("name"=>"address2","value"=>$orderArray["address2"]);
		$gArray["fields"][] = array("name"=>"city","value"=>$orderArray["town"]);
		$gArray["fields"][] = array("name"=>"zip","value"=>$orderArray["postcode"]);
		$gArray["fields"][] = array("name"=>"currency_code","value"=>@$cDetails["code"]);
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
