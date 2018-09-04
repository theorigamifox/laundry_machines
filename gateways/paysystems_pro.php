<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTPS,$tableCountries;
		$gatewayOptions = retrieveGatewayOptions("PAYSYSTEMS_PRO");
		
		$cbY = md5("YES".$orderNumber.$gatewayOptions["companyid"]);
		$cbN = md5("NO".$orderNumber.$gatewayOptions["companyid"]);
		$callBack = "$jssStoreWebDirHTTPS"."gateways/response/paysystems_pro.php?cb=$cbY";
		$callBackFail = "$jssStoreWebDirHTTPS"."gateways/response/paysystems_pro.php?cb=$cbN";
		
		$countryMatch = $orderArray["country"];
		$cResult = $dbA->query("select * from $tableCountries where name=\"$countryMatch\"");
		if (count($cResult) > 0) {
			$cRecord = $dbA->fetch($cResult);
			$customerCountry = $cRecord["isocode"];
		} else {
			$customerCountry = "";
		}
		
		$tpl = new tSys("templates/","gatewaytransfer.html",$requiredVars,0);	
		
		$gArray["method"] = "POST";
		$gArray["action"] = "https://secure.paysystems1.com/cgi-v310/payment/new_onlinesale-tpppro.asp";
		$gArray["fields"][] = array("name"=>"companyid","value"=>$gatewayOptions["companyid"]);
		$gArray["fields"][] = array("name"=>"option1","value"=>$orderNumber);
		$gArray["fields"][] = array("name"=>"Total","value"=>number_format($orderArray["orderTotal"],2,'.',''));
		$gArray["fields"][] = array("name"=>"email","value"=>$orderArray["email"]);
		$gArray["fields"][] = array("name"=>"Redirect","value"=>$callBack);
		$gArray["fields"][] = array("name"=>"Redirectfail","value"=>$callBackFail);
		$gArray["fields"][] = array("name"=>"formget","value"=>"N");
		$gArray["fields"][] = array("name"=>"b_firstname","value"=>$orderArray["forename"]);
		$gArray["fields"][] = array("name"=>"b_lastname","value"=>$orderArray["surname"]);
		$gArray["fields"][] = array("name"=>"b_address","value"=>$orderArray["address1"]);
		$gArray["fields"][] = array("name"=>"b_city","value"=>$orderArray["town"]);
		$gArray["fields"][] = array("name"=>"b_state","value"=>$orderArray["county"]);
		$gArray["fields"][] = array("name"=>"b_tel","value"=>$orderArray["telephone"]);
		$gArray["fields"][] = array("name"=>"b_zip","value"=>$orderArray["postcode"]);
		$gArray["fields"][] = array("name"=>"b_country","value"=>$customerCountry);
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
