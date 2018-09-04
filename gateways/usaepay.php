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

		$gatewayOptions = retrieveGatewayOptions("USAEPAY");
		
		$tran=new usaepayConnect();
		
		$thisIP = @$_SERVER["REMOTE_ADDR"];
		
		$tran->key = $gatewayOptions["sourceKey"];
		$tran->card = @$orderInfoArray["ccNumber"];
		$tran->UMname = @$orderInfoArray["ccName"];
		$tran->ip = $thisIP;
		$tran->UMcommand = $gatewayOptions["command"];
		$tran->exp = str_replace("/","",@$orderInfoArray["ccExpiryDate"]);
		$tran->amount = number_format($authOrderTotal,2,'.','');
		$tran->invoice = $ccAuthID;
		$tran->cardholder = @$orderInfoArray["ccName"];
		$tran->street = @$orderInfoArray["address1"];
		$tran->zip = @$orderInfoArray["postcode"];
		$tran->cvv2 = @$orderInfoArray["ccCVV"];
		if ($gatewayOptions["testmode"]=="Y") {
			$tran->testmode=1;
		}
		if ($gatewayOptions["emailcustomer"]=="Y") {
			$tran->custemail=@$orderInfoArray["email"];
			$tran->custreceipt=1;
		}
	
		flush();

		$response = $tran->Process();
		
		if ($response == true) {
			$authCode = $tran->authcode;
			$avsResult = $tran->avs;
			$cvvResult = $tran->cvv2;	
			$authInformation = "Gateway=USA ePay&Internal Transaction ID=$ccAuthID&Auth Code=$authCode&AVS Result=$avsResult&CVV Result=$cvvResult";
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
	
	class usaepayConnect {
	
		// these are required
		var $source;		// Source key
		var $card;			// card number, no dashes, no spaces
		var $exp;			// expiration date 4 digits no /
		var $amount;		// charge amount in dollars (no international support yet)
		var $invoice;   	// invoice number.  must be unique.
		var $cardholder; 	// name of card holder
		var $street;		// street address
		var $zip;			// zip code
		var $UMname;
		var $UMcommand;
	
		// these are optional
		var $description;	// description of charge
		var $cvv2;			// cvv2 code	
		var $custemail;		// customers email address
		var $custreceipt;	// send customer a receipt
		var $ip;			// ip address of remote host
		var $testmode;		// test transaction but don't process it
	
		// response fields
		var $result;		// full result:  Approved, Declined, Error	
		var $resultcode; 	// abreviated result code: A D E
		var $authcode;		// authorization code
		var $refnum;		// reference number
		var $avs;			// avs result
		var $cvv2;			// cvv2 result
		var $error; 		// error message if result is an error
	
		function umTransaction()
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error="Transaction not processed yet.";
		}
	
		function CheckData()
		{
			if(!$this->key) return "Source Key is required";
			if(!$this->card) return "Credit Card Number is required";
			if(!$this->exp) return "Expiration Date is required";
			if(!$this->amount) return "Amonut is required";
			if(!$this->invoice) return "Invoice number is required";
			if(!$this->cardholder) return "Cardholder Name is required";
			if(!$this->street) return "Street Address is required";
			if(!$this->zip) return "Zipcode is required";
			return 0;		
		}
		
		function Process()
		{
			// check that we have the needed data
			if($tmp=$this->CheckData())
			{
				$this->result="Error";
				$this->resultcode="E";
				$this->error=$tmp;
				return false;
			}
			
			// check to make sure we have curl
			if(!function_exists("curl_version"))
			{
				$this->result="Error";
				$this->resultcode="E";
				$this->error="Libary Error: CURL support not found";
				echo "Error: USA ePay Requires cURL support in PHP in order to authorise transactions. Please see the JShop Server documentation for more information.";
				exit;
				return false;
			}
			
			//init the connection
			$ch = curl_init("https://www.usaepay.com/gate.php");
			
			// set some options for the connection
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch,CURLOPT_POST,1);
			curl_setopt($ch,CURLOPT_TIMEOUT,30);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			
			// format the data
			$data = "UMkey=" . rawurlencode($this->key) . "&" . 
				"UMcard=" . rawurlencode($this->card) . "&" .
				"UMexpir=" . rawurlencode($this->exp) . "&" . 
				"UMamount=" . rawurlencode($this->amount) . "&" . 
				"UMinvoice=" . rawurlencode($this->invoice) . "&" . 
				"UMcardholder=" . rawurlencode($this->cardholder) . "&" .
				"UMstreet=" . rawurlencode($this->street) . "&" . 
				"UMzip=" . rawurlencode($this->zip) . "&" .
				"UMdescription=" . rawurlencode($this->description) . "&" .
				"UMcvv2=" . rawurlencode($this->cvv2) . "&" .
				"UMip=" . rawurlencode($this->ip) . "&" .
				"UMtestmode=" . rawurlencode($this->testmode) . "&" .
				"UMcustemail=" . rawurlencode($this->custemail) . "&" .
				"UMname=" . rawurlencode($this->UMname) . "&" .
				"UMcommand=" . rawurlencode($this->UMcommand) . "&" .
				"UMcustreceipt=" . rawurlencode($this->custreceipt);
				
			// attach the data
			curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
			
			// run the transfer
			$result=curl_exec ($ch);
			curl_close ($ch);
			
			//get the result and parse it for the response line.
			if(!strlen($result))
			{
				$this->result="Error";
				$this->resultcode="E";
				$this->error="Error reading from card processing gateway.";
				return false;			
			}
			$tmp=split("\n",$result);
			for($i=0; $i < count($tmp); $i++)
				if(strstr("UMversion",$tmp[$i])) $result=$tmp[$i];
			parse_str($result,$tmp);
	
			// check to make sure we got the right data
			if(!$tmp["UMversion"] && !$tmp["UMstatus"])
			{
				$this->result="Error";
				$this->resultcode="E";
				$this->error="Error parsing data from card processing gateway.";
				return false;			
			}
	
			
			$this->result=$tmp["UMstatus"];	
			$this->resultcode=$tmp["UMresult"];	
			$this->authcode=$tmp["UMauthCode"];
			$this->refnum=$tmp["UMrefNum"];
			$this->avs=$tmp["UMavsResult"];
			$this->cvv2=$tmp["UMcvv2Result"];
			$this->error=$tmp["UMerror"];
			
			if($this->resultcode == "A") return true;
			return false;
			
		}
	}	
?>
