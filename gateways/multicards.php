<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTPS,$tableCurrencies;
		$gatewayOptions = retrieveGatewayOptions("MULTICARDS");
		$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
		
		$callBack = "$jssStoreWebDirHTTPS"."gateways/response/multicards.php";		
		
		$user2 = md5($orderNumber.$gatewayOptions["merchantID"].$orderArray["forename"].$orderArray["surname"]);

		$pageIDs = explode("|",$gatewayOptions["pageIDs"]);
		$curBits = "";
		for ($f = 0; $f < count($currArray); $f++) {
			$curBits[$currArray[$f]["currencyID"]] = 1;
			for ($g = 0; $g < count($pageIDs); $g++) {
				$thisPage = explode(":",$pageIDs[$g]);
				if ($thisPage[0] == $currArray[$f]["currencyID"]) {
					$curBits[$currArray[$f]["currencyID"]] = $thisPage[1];
				}
			}
		}	
		
		$tpl = new tSys("templates/","gatewaytransfer.html",$requiredVars,0);	
		
		$gArray["method"] = "POST";
		$gArray["action"] = "https://secure.multicards.com/cgi-bin/order2/processorder1.pl";
		$gArray["fields"][] = array("name"=>"mer_id","value"=>$gatewayOptions["merchantID"]);
		$gArray["fields"][] = array("name"=>"mer_url_idx","value"=>$curBits[$orderArray["currencyID"]]);
		$gArray["fields"][] = array("name"=>"deferred_entry","value"=>$gatewayOptions["deferred"]);
		$gArray["fields"][] = array("name"=>"num_items","value"=>"1");
		$gArray["fields"][] = array("name"=>"item1_desc","value"=>$gatewayOptions["description"]);
		$gArray["fields"][] = array("name"=>"item1_qty","value"=>"1");
		$gArray["fields"][] = array("name"=>"item1_price","value"=>number_format($orderArray["orderTotal"],2,'.',''));
		$gArray["fields"][] = array("name"=>"cust_name","value"=>$orderArray["forename"]." ".$orderArray["surname"]);
		$gArray["fields"][] = array("name"=>"card_name","value"=>$orderArray["forename"]." ".$orderArray["surname"]);
		$gArray["fields"][] = array("name"=>"cust_email","value"=>$orderArray["email"]);
		$gArray["fields"][] = array("name"=>"cust_address1","value"=>$orderArray["address1"]);
		$gArray["fields"][] = array("name"=>"cust_city","value"=>$orderArray["town"]);
		$gArray["fields"][] = array("name"=>"cust_state","value"=>$orderArray["county"]);
		$gArray["fields"][] = array("name"=>"cust_country","value"=>$orderArray["country"]);
		$gArray["fields"][] = array("name"=>"cust_zip","value"=>$orderArray["postcode"]);
		$gArray["fields"][] = array("name"=>"cust_company","value"=>$orderArray["company"]);
		$gArray["fields"][] = array("name"=>"cust_phone","value"=>$orderArray["telephone"]);
		$gArray["fields"][] = array("name"=>"user1","value"=>$orderNumber);
		$gArray["fields"][] = array("name"=>"user2","value"=>$user2);
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
