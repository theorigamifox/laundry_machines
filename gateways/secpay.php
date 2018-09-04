<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTP;
		$gatewayOptions = retrieveGatewayOptions("SECPAY");
		
		$callBack = "$jssStoreWebDirHTTP"."gateways/response/secpay.php";
		
		$cDetails = returnCurrencyDetails($orderArray["currencyID"]);

		$testStatus = "";
		if ($gatewayOptions["test_status"]=="T") {
			$testStatus = "<input type=HIDDEN NAME=\"test_status\" VALUE=\"true\">";
		} else {
			if ($gatewayOptions["test_status"]=="F") {
				$testStatus = "<input type=HIDDEN NAME=\"test_status\" VALUE=\"false\">";
			}
		}
		$deferred = "";
		if ($gatewayOptions["authType"]=="DEFT") {
			$deferred = "<input type=HIDDEN NAME=\"deferred\" VALUE=\"true\">";
		} else {
			if ($gatewayOptions["authType"]=="DEFF") {
				$deferred = "<input type=HIDDEN NAME=\"deferred\" VALUE=\"full\">";
			} else {
				if ($gatewayOptions["authType"]=="REUSE") {
					$deferred = "<input type=HIDDEN NAME=\"deferred\" VALUE=\"reuse\">";
				}
			}
		}		
		
		$tpl = new tSys("templates/","gatewaytransfer.html",$requiredVars,0);	
		
		$gArray["method"] = "POST";
		$gArray["action"] = "https://www.secpay.com/java-bin/ValCard";
		$gArray["fields"][] = array("name"=>"merchant","value"=>$gatewayOptions["merchant"]);
		$gArray["fields"][] = array("name"=>"amount","value"=>number_format($orderArray["orderTotal"],2,'.',''));
		$gArray["fields"][] = array("name"=>"currency","value"=>@$cDetails["code"]);
		$gArray["fields"][] = array("name"=>"trans_id","value"=>$orderNumber);
		$gArray["fields"][] = array("name"=>"cb_post","value"=>"true");
## SNC MOD ##
                $gArray["fields"][] = array("name"=>"mail_customer","value"=>"false");
                $gArray["fields"][] = array("name"=>"fraud_check","value"=>"passive");
                $gArray["fields"][] = array("name"=>"req_cv2","value"=>"true");
## SNC MOD ##
		$gArray["fields"][] = array("name"=>"callback","value"=>$callBack);
		if ($gatewayOptions["template"] != "") {
			$gArray["fields"][] = array("name"=>"template","value"=>"http://www.secpay.com/users/".$gatewayOptions["merchant"]."/".$gatewayOptions["template"]);
		}
		$gArray["fields"][] = array("name"=>"bill_name","value"=>$orderArray["title"]." ".$orderArray["forename"]." ".$orderArray["surname"]);
		$gArray["fields"][] = array("name"=>"bill_addr_1","value"=>$orderArray["address1"]);
		$gArray["fields"][] = array("name"=>"bill_addr_2","value"=>$orderArray["address2"]);
		$gArray["fields"][] = array("name"=>"bill_city","value"=>$orderArray["town"]);
		$gArray["fields"][] = array("name"=>"bill_state","value"=>$orderArray["county"]);
		$gArray["fields"][] = array("name"=>"bill_post_code","value"=>$orderArray["postcode"]);
		$gArray["fields"][] = array("name"=>"bill_country","value"=>$orderArray["country"]);
		$gArray["fields"][] = array("name"=>"bill_tel","value"=>$orderArray["telephone"]);
		$gArray["fields"][] = array("name"=>"bill_email","value"=>$orderArray["email"]);
		$gArray["fields"][] = array("name"=>"bill_company","value"=>$orderArray["company"]);
		
		$gArray["fields"][] = array("name"=>"","value"=>"","full"=>$testStatus);
		$gArray["fields"][] = array("name"=>"","value"=>"","full"=>$deferred);
		
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
