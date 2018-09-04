<?php
	function startProcessor() {
		global $dbA,$orderInfoArray,$tableCountries,$tableCCServerAuths,$authOrderTotal;
		
		if (array_key_exists("ccAuthID",$orderInfoArray)) {
			$ccAuthID = $orderInfoArray["ccAuthID"];
		} else {
			$result = $dbA->query("insert into $tableCCServerAuths (used) VALUES('Y')");
			$ccAuthID = $dbA->lastID();
			$ccAuthID = $ccAuthID + 100000000;
			$orderInfoArray["ccAuthID"] = $ccAuthID;
		}

		$deliveryName = explode(" ",$orderInfoArray["deliveryName"]);
		
		if (count($deliveryName) == 3) {
			$deliveryName[0] = $deliveryName[1];
			$deliveryName[1] = $deliveryName[2];
		}

		$gatewayOptions = retrieveGatewayOptions("AUTHORIZEAIM");
		
		$ch = curl_init("https://secure.authorize.net/gateway/transact.dll");
			
		// set some options for the connection
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_TIMEOUT,30);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			
		$data = "x_version=3.1"."&";
		$data .= "x_delim_data=True"."&";
		$data .= "x_login=".$gatewayOptions["x_login"]."&";
		$data .= "x_password=".$gatewayOptions["x_password"]."&";
		$data .= "x_amount=".number_format($authOrderTotal,2,'.','')."&";
		$data .= "x_card_num=".@$orderInfoArray["ccNumber"]."&";
		$data .= "x_exp_date=".str_replace("/","",@$orderInfoArray["ccExpiryDate"])."&";
		$data .= "x_type=".$gatewayOptions["type"]."&";
		$data .= "x_test_request=".$gatewayOptions["x_test_request"]."&";
		$data .= "x_delim_char=|"."&";
		$data .= "x_encap_char= "."&";

		$data .= "x_invoice_num= ".$orderNumber."&";
		$data .= "x_email= ".$orderInfoArray["email"]."&";

		$data .= "x_first_name= ".$orderInfoArray["forename"]."&";
		$data .= "x_last_name= ".$orderInfoArray["surname"]."&";
		$data .= "x_company= ".$orderInfoArray["company"]."&";
		$data .= "x_address= ".$orderInfoArray["address1"]."&";
		$data .= "x_city= ".$orderInfoArray["town"]."&";
		$data .= "x_state= ".$orderInfoArray["county"]."&";
		$data .= "x_zip= ".$orderInfoArray["postcode"]."&";
		$data .= "x_country= ".$orderInfoArray["country"]."&";
		$data .= "x_phone= ".$orderInfoArray["telephone"]."&";
		$data .= "x_fax= ".$orderInfoArray["fax"]."&";

		$data .= "x_ship_to_first_name= ".@$deliveryName[0]."&";
		$data .= "x_ship_to_last_name= ".@$deliveryName[1]."&";
		$data .= "x_ship_to_company= ".$orderInfoArray["deliveryCompany"]."&";
		$data .= "x_ship_to_address= ".$orderInfoArray["deliveryAddress1"]."&";
		$data .= "x_ship_to_city= ".$orderInfoArray["deliveryTown"]."&";
		$data .= "x_ship_to_state= ".$orderInfoArray["deliveryCounty"]."&";
		$data .= "x_ship_to_zip= ".$orderInfoArray["deliveryPostcode"]."&";
		$data .= "x_ship_to_country= ".$orderInfoArray["deliveryCountry"];


		// attach the data
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
			
		// run the transfer
		$result=curl_exec ($ch);
		curl_close ($ch);

		$bits = explode("\r\n",$result);
		$authBits = explode("|",$bits[count($bits)-1]);
			
		
		for ($f = 0; $f < count($authBits); $f++) {
			$authBits[$f] = str_replace('"','',$authBits[$f]);
		}


		if ($authBits[0] == "1") {
			$authCode = $authBits[4];
			$avsResult = $authBits[5];
			$cvvResult	 = $authBits[38];
			$tranID = $authBits[6];	
			$authInformation = "Gateway=Authorize.net AIM&Internal Transaction ID=$tranID&Auth Code=$authCode&AVS Result=$avsResult&CVV Result=$cvvResult";
			$orderInfoArray["authInformation"] = $authInformation;
			$orderInfoArray["ccNumber"] = "";
			$orderInfoArray["ccName"] = "";
			$orderInfoArray["ccExpiryDate"] = "";
			$orderInfoArray["ccStartDate"] = "";
			$orderInfoArray["ccType"] = "";
			$orderInfoArray["ccIssue"] = "";
			$orderInfoArray["ccCVV"] = "";
			return true;
		} else {
			return false;
		}
	}
?>