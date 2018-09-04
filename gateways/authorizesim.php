<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTPS,$tableCountries;
		$gatewayOptions = retrieveGatewayOptions("AUTHORIZESIM");
		
		$callBack = "$jssStoreWebDirHTTPS"."gateways/response/authorizesim.php";
		
		$cDetails = returnCurrencyDetails($orderArray["currencyID"]);
	
		srand(time());
		$sequence = rand(1, 1000);
		
		$deliveryName = explode(" ",$orderArray["deliveryName"]);
		
		if (count($deliveryName) == 3) {
			$deliveryName[0] = $deliveryName[1];
			$deliveryName[1] = $deliveryName[2];
		}
		
		$tpl = new tSys("templates/","gatewaytransfer.html",$requiredVars,0);	
		
		$gArray["method"] = "POST";
		$gArray["action"] = "https://secure.authorize.net/gateway/transact.dll";
		$gArray["fields"][] = array("name"=>"x_login","value"=>$gatewayOptions["x_login"]);
		$gArray["fields"][] = array("name"=>"x_description","value"=>$gatewayOptions["x_description"]);
		$gArray["fields"][] = array("name"=>"x_Type","value"=>$gatewayOptions["type"]);
		$gArray["fields"][] = array("name"=>"x_test_request","value"=>$gatewayOptions["x_test_request"]);
		$gArray["fields"][] = array("name"=>"x_amount","value"=> number_format($orderArray["orderTotal"],2,'.',''));
		$gArray["fields"][] = array("name"=>"x_currency_code","value"=>$cDetails["code"]);
		$gArray["fields"][] = array("name"=>"x_show_form","value"=>"PAYMENT_FORM");
		$gArray["fields"][] = array("name"=>"x_invoice_num","value"=>$orderNumber);
		$gArray["fields"][] = array("name"=>"x_email","value"=>$orderArray["email"]);
		$gArray["fields"][] = array("name"=>"x_email_customer","value"=>$gatewayOptions["x_email_customer"]);
		$gArray["fields"][] = array("name"=>"x_first_name","value"=>$orderArray["forename"]);
		$gArray["fields"][] = array("name"=>"x_last_name","value"=>$orderArray["surname"]);
		$gArray["fields"][] = array("name"=>"x_company","value"=>$orderArray["company"]);
		$gArray["fields"][] = array("name"=>"x_address","value"=>$orderArray["address1"]);
		$gArray["fields"][] = array("name"=>"x_city","value"=>$orderArray["town"]);
		$gArray["fields"][] = array("name"=>"x_state","value"=>$orderArray["county"]);
		$gArray["fields"][] = array("name"=>"x_zip","value"=>$orderArray["postcode"]);
		$gArray["fields"][] = array("name"=>"x_country","value"=>$orderArray["country"]);
		$gArray["fields"][] = array("name"=>"x_phone","value"=>$orderArray["telephone"]);
		$gArray["fields"][] = array("name"=>"x_fax","value"=>$orderArray["fax"]);
		$gArray["fields"][] = array("name"=>"x_ship_to_first_name","value"=>@$deliveryName[0]);
		$gArray["fields"][] = array("name"=>"x_ship_to_last_name","value"=>@$deliveryName[1]);
		$gArray["fields"][] = array("name"=>"x_ship_to_company","value"=>$orderArray["deliveryCompany"]);
		$gArray["fields"][] = array("name"=>"x_ship_to_address","value"=>$orderArray["deliveryAddress1"]);
		$gArray["fields"][] = array("name"=>"x_ship_to_city","value"=>$orderArray["deliveryTown"]);
		$gArray["fields"][] = array("name"=>"x_ship_to_state","value"=>$orderArray["deliveryCounty"]);
		$gArray["fields"][] = array("name"=>"x_ship_to_zip","value"=>$orderArray["deliveryPostcode"]);
		$gArray["fields"][] = array("name"=>"x_ship_to_country","value"=>$orderArray["deliveryCountry"]);
		$gArray["fields"][] = array("name"=>"x_relay_response","value"=>"TRUE");
		$gArray["fields"][] = array("name"=>"x_relay_url","value"=>$callBack);
		
		$ret = InsertFP ($gatewayOptions["x_login"], $gatewayOptions["trankey"], number_format($orderArray["orderTotal"],2,'.',''), $sequence, $cDetails["code"]);
		$gArray["fields"][] = array("name"=>"","value"=>"","full"=>$ret);
		
		$mArray = $gArray;
		
		$gArray["process"] = "document.automaticForm.submit();";
		
		$tpl->addVariable("shop",templateVarsShopRetrieve());
		$tpl->addVariable("labels",templateVarsLabelsRetrieve());
		$tpl->addVariable("automaticForm",$gArray);
		$tpl->addVariable("manualForm",$mArray);
		$tpl->showPage();
		exit;
	}

	function InsertFP ($loginid, $txnkey, $amount, $sequence, $currency = "") {
		$tstamp = time ();
		$fingerprint = hmac ($txnkey, $loginid . "^" . $sequence . "^" . $tstamp . "^" . $amount . "^" . $currency);
		$ret = '<input type="hidden" name="x_fp_sequence" value="' . $sequence . '">';
		$ret .= '<input type="hidden" name="x_fp_timestamp" value="' . $tstamp . '">';
		$ret .= '<input type="hidden" name="x_fp_hash" value="' . $fingerprint . '">';
		return $ret;
	}

	function hmac ($key, $data) {
	   // Creates an md5 HMAC.
	   $b = 64; // byte length for md5
	   if (strlen($key) > $b) {
	       $key = pack("H*",md5($key));
	   }
	   $key  = str_pad($key, $b, chr(0x00));
	   $ipad = str_pad('', $b, chr(0x36));
	   $opad = str_pad('', $b, chr(0x5c));
	   $k_ipad = $key ^ $ipad ;
	   $k_opad = $key ^ $opad;
	   return md5($k_opad  . pack("H*",md5($k_ipad . $data)));
	}	
?>
