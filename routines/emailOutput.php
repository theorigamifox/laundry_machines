<?php
	function sendEmail($emailaddress,$ccAddresses="",$emailTemplate) {
		global $extraFieldList,$dbA,$tableGeneral,$jssShopFileSystem,$orderPaymentArray,$tableCustomers,$affiliateMain,$taxRates,$tableWishlists,$tableProducts,$giftCert,$tableEmails,$customerMain,$contactform,$tableCustomerFields,$orderArray,$currArray,$extraFieldsArray,$dispatchArray,$stockArray,$cartMain;
		global $tableProducts,$tableWishlists,$reviewRecord,$newsletter;
		global $safeMode;
		global $tableSuppliers,$tableSuppliersEmails;
		if ($safeMode == "1") { return; }
		$result = $dbA->query("select * from $tableGeneral");
		$companyRecord = $dbA->fetch($result);
		if ($emailTemplate == "SUPPLIER") {
			$result = $dbA->query("select * from $tableSuppliers where supplierID=".$orderArray["supplierID"]);
			$supplier = $dbA->fetch($result);
			if ($supplier["emailSupplier"] == "N") { return false; }
			$emailID = $supplier["emailID"];
			$result = $dbA->query("select * from $tableSuppliersEmails where emailID=$emailID");
			$tRecord = $dbA->fetch($result);
			$tRecord["activated"] = "Y";
		} else {
			$result = $dbA->query("select * from $tableEmails where template='$emailTemplate'");
			$tRecord = $dbA->fetch($result);
		}
	
		if ($emailaddress == "COMPANY") {
			if ($tRecord["recipient"] != "") {
				$emailaddress = $tRecord["recipient"];
			} else {
				$emailaddress = retrieveOption("emailMerchTo");
			}
		}
		if ($emailaddress == "SUPPLIER") {
			$emailaddress = $supplier["notifyEmail"];
		}
		
		if ($tRecord["replyto"] != "") {
			$fromaddress = $tRecord["replyto"];
		} else {
			$fromaddress = retrieveOption("emailCustomerFrom");
		}
		
		if ($tRecord["activated"] == "N") {
			return false;
		}
		
		$outputList[] = $tRecord["subject"];
		$outputList[] = $tRecord["message"];
		$outputList[] = $tRecord["messageHTML"];
		
		$settings["languageID"] = retrieveOption("defaultLanguage");
		if (array_key_exists("languageID",$cartMain)) {
			$settings["languageID"] = $cartMain["languageID"];
		}
		
		for ($y = 0; $y < count($outputList); $y++) {
			$tpl = new tSys("FROMDB",$outputList[$y],$requiredVars,0);
			for ($z = 0; $z < count($requiredVars); $z++) {
				switch ($requiredVars[$z]) {
					case "supplier":
						if (array_key_exists("languageID",$cartMain)) {
							$settings["languageID"] = $cartMain["languageID"];
						}
						$tpl->addVariable("supplier",@$supplier);
						break;
					case "shop":
						$shop["baseDir"] = $jssStoreWebDirHTTP;
						$tpl->addVariable("shop",$shop);
						break;
					case "review":
						if (array_key_exists("languageID",$cartMain)) {
							$settings["languageID"] = $cartMain["languageID"];
						}
						$tpl->addVariable("review",$reviewRecord);
						break;
					case "newsletter":
						if (array_key_exists("languageID",$cartMain)) {
							$settings["languageID"] = $cartMain["languageID"];
						}
						$tpl->addVariable("newsletter",$newsletter);
						break;
					case "affiliate":
						if (array_key_exists("languageID",$cartMain)) {
							$settings["languageID"] = $cartMain["languageID"];
						}
						@$affiliateMain["payment"]["amount"] = @formatWithoutCalcPriceInCurrency(@$affiliateMain["payment"]["amount"],1);
						$tpl->addVariable("affiliate",$affiliateMain);
						break;
					case "company":
						if (array_key_exists("languageID",$cartMain)) {
							$settings["languageID"] = $cartMain["languageID"];
						}
						$tpl->addVariable("company",$companyRecord);
						break;
					case "customer":
						if (array_key_exists("languageID",$cartMain)) {
							$settings["languageID"] = $cartMain["languageID"];
						}
						$tpl->addVariable("customer",$customerMain);
						break;
					case "wishlist":
						if (array_key_exists("languageID",$cartMain)) {
							$settings["languageID"] = $cartMain["languageID"];
						}
						$taxRates = retrieveTaxRates($cartMain["country"],$cartMain["country"]);
						$wishlist = null;
						$wishlistID = $customerMain["wishlistID"];
						$wishlist["languageID"] = @$cartMain["languageID"];
						$theQuery = "select * from $tableProducts,$tableWishlists where $tableProducts.productID = $tableWishlists.productID and $tableProducts.visible = 'Y' and $tableWishlists.wishlistID = '$wishlistID' order by code,name";
						$productArray = retrieveProducts($theQuery,$counter,"m",0);
						if ($counter != 0) {
							for ($f = 0; $f < count($productArray); $f++) {
								$productArray[$f]["deletelink"] = configureURL("customer.php?xCmd=wldelete&xProd=".$productArray[$f]["productID"]);
								$productArray[$f]["qtyboxname"] = "qty".$productArray[$f]["uniqueID"];
								$productArray[$f]["commentboxname"] = "comment".$productArray[$f]["uniqueID"];
								$productArray[$f]["comment"] = str_replace("\"","&quot;",$productArray[$f]["comment"]);
							}
							$wishlist["products"] = $productArray;
						}
						
						
						/*$result = $dbA->query($theQuery);
						$pCount = $dbA->count($result);
						$productArray = null;
						for ($f = 0; $f < $pCount; $f++) {
							$thisProduct = $dbA->fetch($result);
							$thisProduct["name"] = findCorrectLanguage($thisProduct,"name");
							$thisProduct["price"] = formatPrice($thisProduct["price1"],$thisProduct["productID"]);
							$thisProduct["link"] = $companyRecord["storeurl"]."product.php?xProd=".$thisProduct["productID"];
							$productArray[] = $thisProduct;
						}*/
						$wishlist["products"] = $productArray;
						$tpl->addVariable("wishlist",$wishlist);
						break;	
					case "giftcertificate":
						if (array_key_exists("languageID",$giftCert)) {
							$settings["languageID"] = $giftCert["languageID"];
						}
						$giftCert["amount"] = formatWithoutCalcPriceInCurrency($giftCert["certValue"],$giftCert["currencyID"]);
						$tpl->addVariable("giftcertificate",$giftCert);
						break;
					case "contactform":
						if (array_key_exists("languageID",$cartMain)) {
							$settings["languageID"] = $cartMain["languageID"];
						}
						$result = $dbA->query("select * from $tableCustomerFields where type='F' and visible=1 order by position,fieldID");
						$count = $dbA->count($result);
						for ($f = 0; $f < $count; $f++) {
							$fRecord = $dbA->fetch($result);
							if ($fRecord["fieldtype"]=="SELECT") {
								$optionsSplit = explode(";",$fRecord["contentvalues"]);
								$options = null;
								for ($g = 0; $g < count($optionsSplit); $g++) {
									if (chop($optionsSplit[$g]) != "") {
										$options[] = array("name"=>$optionsSplit[$g],"value"=>$optionsSplit[$g]);
									}
								}
								$fRecord["options"] = $options;
							}
							if ($fRecord["fieldtype"]=="CHECKBOX") {
								if (@$customerMain[$fRecord["fieldname"]] != "") {
									$fRecord["checked"] = "CHECKED";
								}
							}
							$fRecord["error"] = @$contactform[$fRecord["fieldname"]."_error"];
							$fRecord["content"] = @$contactform[$fRecord["fieldname"]];
							$contactform["fields"][] = $fRecord;
							$contactform["field"][$fRecord["fieldname"]] = $fRecord;
						}
						if ($contactform["field"]["EmailAddress"]["content"] != "") {
							$fromaddress = $contactform["field"]["EmailAddress"]["content"];
						}
						$tpl->addVariable("contactform",$contactform);								
						break;
					case "dispatch":
						if (array_key_exists("languageID",$dispatchArray)) {
							$settings["languageID"] = $dispatchArray["languageID"];
						}
						$tpl->addVariable("dispatch",$dispatchArray);								
						break;
					case "productstock":
						$tpl->addVariable("productstock",$stockArray);								
						break;
					case "orderpayment":
						$tpl->addVariable("orderpayment",$orderPaymentArray);
						break;						
					case "order":
						if (array_key_exists("languageID",$orderArray)) {
							$settings["languageID"] = $orderArray["languageID"];
						}
						$orderArrayCopy=$orderArray;
						for ($f = 0; $f < count($orderArray["products"]); $f++) {
							$theQty = $orderArray["products"][$f]["qty"];
							$thePrice = $orderArray["products"][$f]["price"];
							$thePriceRounded = roundWithoutCalcDisplay($thePrice,$orderArray["currencyID"]);
							$theOOPrice = $orderArray["products"][$f]["ooprice"];
							$orderArray["products"][$f]["price"] = formatWithoutCalcPriceInCurrency($thePrice,$orderArray["currencyID"]);
							$orderArray["products"][$f]["priceextax"] = formatWithoutCalcPriceInCurrency($thePriceRounded,$orderArray["currencyID"]);
							$orderArray["products"][$f]["priceinctax"] = formatWithoutCalcPriceInCurrency($thePriceRounded+$orderArray["products"][$f]["taxamount"],$orderArray["currencyID"]);

							$orderArray["products"][$f]["ooPrice1"] = $theOOPrice;
							$orderArray["products"][$f]["ooprice"] = formatWithoutCalcPriceInCurrency($theOOPrice,$orderArray["currencyID"]);
							$orderArray["products"][$f]["oopriceextax"] = formatWithoutCalcPriceInCurrency($theOOPrice,$orderArray["currencyID"]);
							$orderArray["products"][$f]["oopriceinctax"] = formatWithoutCalcPriceInCurrency($thePrice+$orderArray["products"][$f]["ootaxamount"],$orderArray["currencyID"]);


							$orderArray["products"][$f]["total"] = formatWithoutCalcPriceInCurrency(($thePriceRounded*$theQty)+($theOOPrice),$orderArray["currencyID"]);
							$orderArray["products"][$f]["totalextax"] = formatWithoutCalcPriceInCurrency(($thePriceRounded*$theQty)+($theOOPrice),$orderArray["currencyID"]);
							$orderArray["products"][$f]["totalinctax"] = formatWithoutCalcPriceInCurrency((($thePriceRounded+$orderArray["products"][$f]["taxamount"])*$theQty)+($theOOPrice+$orderArray["products"][$f]["ootaxamount"]),$orderArray["currencyID"]);
							$allExtraFields = "";
							for ($g = 0; $g < count($extraFieldsArray); $g++) {
								$thisExtraField = "";
								switch ($extraFieldsArray[$g]["type"]) {
									case "USERINPUT":
									case "SELECT":
									case "RADIOBUTTONS":
										$theContentNative = "";
										$theContent = "";
										for ($i = 0; $i < count($extraFieldList); $i++) {
											if ($orderArray["products"][$f]["lineID"] == $extraFieldList[$i]["lineID"] && $extraFieldsArray[$g]["extraFieldID"] == $extraFieldList[$i]["extraFieldID"]) {
												$theContentNative = $extraFieldList[$i]["content"];
												$theContent = $extraFieldList[$i]["contentNative"];
												break;
											}
										}
										$thisExtraField["name"] = $extraFieldsArray[$g]["name"];
										$thisExtraField["title"] = $extraFieldsArray[$g]["title"];
										$thisExtraField["type"] = $extraFieldsArray[$g]["type"];
										$thisExtraField["content"] = $theContent;
										$thisExtraField["contentNative"] = $theContentNative;
												
										$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["name"] = $extraFieldsArray[$g]["name"];
										$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["title"] = $extraFieldsArray[$g]["title"];
										$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["type"] = $extraFieldsArray[$g]["type"];
										$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["content"] = $theContent;
										$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["contentNative"] = $theContentNative;
										$allExtraFields[] = $thisExtraField;
										break;								
									case "CHECKBOXES":
										$optionArray = "";
										$theContent = "";
										$theContentNative = "";
										for ($i = 0; $i < count($extraFieldList); $i++) {
											if ($orderArray["products"][$f]["lineID"] == $extraFieldList[$i]["lineID"] && $extraFieldsArray[$g]["extraFieldID"] == $extraFieldList[$i]["extraFieldID"]) {
												if ($extraFieldList[$i]["content"] != "") {
													$optionArray[] = array("option"=>$extraFieldList[$i]["content"],"optionNative"=>$extraFieldList[$i]["contentNative"]);
													$theContent = "Y";
												}
											}
										}
										$thisExtraField["name"] = $extraFieldsArray[$g]["name"];
										$thisExtraField["title"] = $extraFieldsArray[$g]["title"];
										$thisExtraField["type"] = $extraFieldsArray[$g]["type"];
										$thisExtraField["content"] = $theContent;
										$thisExtraField["contentNative"] = $theContentNative;
										$thisExtraField["options"] = $optionArray;
												
										$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["name"] = $extraFieldsArray[$g]["name"];
										$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["title"] = $extraFieldsArray[$g]["title"];
										$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["type"] = $extraFieldsArray[$g]["type"];
										$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["content"] = $theContent;
										$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["contentNative"] = $theContentNative;
										$orderArray["products"][$f]["extra_".$extraFieldsArray[$g]["name"]]["options"] = $optionArray;
										$allExtraFields[] = $thisExtraField;
										break;
								}
							}			
							if (is_array($allExtraFields)) {
								$orderArray["products"][$f]["extrafields"] = $allExtraFields;
							}					
						}
						$goodsTotal = $orderArray["goodsTotal"];
						$shippingTotal = $orderArray["shippingTotal"];
						$taxTotal = $orderArray["taxTotal"];
						$discountTotal = $orderArray["discountTotal"];
						$giftCertTotal = $orderArray["giftCertTotal"];
						$orderTotal = $goodsTotal+$shippingTotal+$taxTotal-$discountTotal-$giftCertTotal;
						$orderArray["totals"]["goods"] = formatWithoutCalcPriceInCurrency($goodsTotal,$orderArray["currencyID"]);
						if ($discountTotal > 0) {
							$orderArray["totals"]["isDiscount"] = "Y";
						} else {
							$orderArray["totals"]["isDiscount"] = "N";
						}
						$orderArray["totals"]["discount"] = formatWithoutCalcPriceInCurrency($discountTotal,$orderArray["currencyID"]);
						$orderArray["totals"]["shipping"] = formatWithoutCalcPriceInCurrency($shippingTotal,$orderArray["currencyID"]);
						$orderArray["totals"]["tax"] = formatWithoutCalcPriceInCurrency($taxTotal,$orderArray["currencyID"]);
						$orderArray["totals"]["order"] = formatWithoutCalcPriceInCurrency($orderTotal,$orderArray["currencyID"]);		
						$orderArray["totals"]["giftcertificates"] = formatWithoutCalcPriceInCurrency($giftCertTotal,$orderArray["currencyID"]);	
						$tpl->addVariable("order",$orderArray);
						$orderArray = $orderArrayCopy;
						break;
					default:
						if (file_exists($jssShopFileSystem."routines/emailOutputExtra.php")) {
							include ($jssShopFileSystem."routines/emailOutputExtra.php");
						}	
						break;
				}
			}
			$tpl->addVariable("settings",$settings);
			$outputList[$y] = $tpl->retrievePage();
		}
		$extraHeaders = "";
		$theSubject=chop(unhtmlentitiesemail($outputList[0]));
		$theMessage=chop(unhtmlentitiesemail($outputList[1]));
		$theMessageHTML=chop(unhtmlentitiesemail($outputList[2]));


		@sendEmailWithType($emailaddress,$fromaddress,$extraHeaders,$theSubject,$theMessage,$theMessageHTML);
		
		if ($ccAddresses != "") {
			$ccBits = explode(",",$ccAddresses);
			for ($f = 0; $f < count($ccBits); $f++) {
				@sendEmailWithType(trim($ccBits[$f]),$fromaddress,$extraHeaders,$theSubject,$theMessage,$theMessageHTML);
			}
		}

		//$emailConnection = new emailSocket;
		//$emailConnection->openSocket();	
		//$emailConnection->sendEmail($emailaddress,$fromaddress,$extraHeaders,$theSubject,$theMessage,$theMessageHTML);
		//$emailConnection->closeSocket();
		//@mail($emailaddress,$theSubject,$theMessage,"From: ".$fromaddress.$extraHeaders);
	}

	function sendConfirmationEmails($orderID,$sendMerchant=1,$compareType=2) {
		global $dbA,$tableOrdersHeaders,$tableOrdersLines,$tableOrdersExtraFields,$tablePaymentOptions,$orderArray,$extraFieldsArray,$extraFieldList,$tableCurrencies,$tableLanguages,$tableExtraFields,$currArray,$langArray;
		$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
		$langArray = $dbA->retrieveAllRecords($tableLanguages,"languageID");
		$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"position,name");
		$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
		$orderArray = $dbA->fetch($result);
		$orderArray["ordernumber"] = $orderArray["orderID"]+retrieveOption("orderNumberOffset");
		$orderArray["orderdate"] = formatDate($orderArray["datetime"]);
		$orderArray["ordertime"] = formatTime(substr($orderArray["datetime"],-6));
		$orderProducts = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersLines where orderID=$orderID order by lineID");
		
		$orderArray["products"] = $orderProducts;
		$extraFieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$orderID order by lineID,extraFieldID");
			
		$result = $dbA->query("select * from $tablePaymentOptions where paymentID=".$orderArray["paymentID"]);
		$goSendEmail = false;
		if ($dbA->count($result) > 0) {
			$paymentArray = $dbA->fetch($result);
			if ($paymentArray["custConfirmation"] == $compareType) {
				$goSendEmail = true;
			}
		} else {
			$goSendEmail = false;
		}
		if ($goSendEmail) {
			if ($sendMerchant == 1) {
				@sendEmail("COMPANY","","MERCHORDER");
			}
			@sendEmail($orderArray["email"],"","CUSTORDER");
		}
	}

	function sendOrderPaymentEmail($orderID,$theTemplate) {
		global $dbA,$tableOrdersHeaders,$tableOrdersLines,$tableOrdersExtraFields,$tablePaymentOptions,$orderArray,$extraFieldsArray,$extraFieldList,$tableCurrencies,$tableLanguages,$tableExtraFields,$currArray,$langArray;
		$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
		$langArray = $dbA->retrieveAllRecords($tableLanguages,"languageID");
		$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"position,name");
		$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
		$orderArray = $dbA->fetch($result);
		$orderArray["ordernumber"] = $orderArray["orderID"]+retrieveOption("orderNumberOffset");
		$orderArray["orderdate"] = formatDate($orderArray["datetime"]);
		$orderArray["ordertime"] = formatTime(substr($orderArray["datetime"],-6));
		$orderProducts = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersLines where orderID=$orderID order by lineID");
		
		$orderArray["products"] = $orderProducts;
		$extraFieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$orderID order by lineID,extraFieldID");
		

		if (retrieveOption("sendMerchPaymentEmail") == 1) {
			@sendEmail("COMPANY","",$theTemplate);
		}
	}

	function sendEmailWithType($to, $from, $extraHeaders, $subject, $textMessage, $htmlMessage) { 

		$separator = "JSS__Newsletter__Send"; 
		
		$header = "From: $from\n"; 
		$header .= "Reply-To: $from\n"; 
		//$header .= "To: $to\n"; 
		$header .= $extraHeaders;
		$header .= "MIME-Version: 1.0\n"; 
		$header .= "X-Mailer: JSS\n"; 
		$header .= "Content-Type: multipart/alternative; boundary=\"$separator\";\n\n";
		
		$message =""; 
		if (chop($textMessage) != "") {
			$message .= "--$separator\n"; 
			$message .= "Content-Type: text/plain\n";
			$message .= "Content-Transfer-Encoding: 7bit\n\n";
			$message .= $textMessage."\n\n"; 
		}
		
		if (chop($htmlMessage) != "") {		
			$message .= "--$separator\n"; 
			$message .= "Content-Type: text/html\n";
			$message .= "Content-Transfer-Encoding: 7bit\n\n";
			$message .= $htmlMessage."\n\n";
		}
		
		$message .= "--$separator--\n\n\n"; 
		
		@mail ( $to, $subject, $message, $header); 
		return true; 
	} 	

	class emailSocket {
		var $emailConnection;
		var $seperator;
		
		function emailSocket() {
		}
		
		function openSocket() {
 			$this->emailConnection = fsockopen (ini_get("SMTP"), 25, $errno, $errstr, 30) or die("Could not talk to the sendmail server!"); 
 			$this->setTimeout();
   			$rcv = fgets($this->emailConnection, 1024);
   			$this->checkTimeout();
   			$this->seperator = "JSS__Newsletter__Send"; 
   			flush();
		}
		
		function setTimeout() {
			//Different versions of PHP have a different name for this function.
			if (function_exists('socket_set_timeout')) {
				socket_set_timeout($this->emailConnection, 2,0);
			}
			if (function_exists('set_socket_timeout')) {
				set_socket_timeout($this->emailConnection, 2,0);
			}
			if (function_exists('stream_set_timeout')) {
				stream_set_timeout($this->emailConnection, 2,0);
			}
		}
		
		function checkTimeout() {
			$socket_status = socket_get_status($this->emailConnection);
			if ($socket_status["timed_out"]) {
				$this->closeSocket();
				$this->openSocket();
				return true;
			} else {
				return false;
			}
		}

		function sendEmail($to, $from, $extraheaders, $subject, $textMessage, $htmlMessage) {
   			fputs ($this->emailConnection, "MAIL FROM:$from"."\r\n"); 
			if ($this->checkTimeout()) { return false; }
     		$rcv = fgets ($this->emailConnection, 1024); 
			if ($this->checkTimeout()) { return false; }
			fputs ($this->emailConnection, "RCPT TO:$to\r\n"); 
			if ($this->checkTimeout()) { return false; }
     		$rcv = fgets ($this->emailConnection, 1024); 
     		if (substr($rcv,0,3) != "250") { $this->closeSocket(); $this->openSocket(); return false; }
			if ($extraheaders != "") {
				fputs ($this->emailConnection, $extraheaders); 
				if ($this->checkTimeout()) { return false; }
     			$rcv = fgets ($this->emailConnection, 1024); 
     			if (substr($rcv,0,3) != "250") { $this->closeSocket(); $this->openSocket(); return false; }     		
     		}
			if ($this->checkTimeout()) { return false; }
   			fputs ($this->emailConnection, "DATA\r\n"); 
			if ($this->checkTimeout()) { return false; }
     		$rcv = fgets ($this->emailConnection, 1024); 
			if ($this->checkTimeout()) { return false; }
   			fputs ($this->emailConnection, "Subject: $subject" . "\r\n" ); 
			if ($this->checkTimeout()) { return false; }
   			fputs ($this->emailConnection, "From: \"$from\" <$from>" . "\r\n" ); 
			if ($this->checkTimeout()) { return false; }
   			fputs ($this->emailConnection, "Reply-To: $from" . "\r\n" ); 
			if ($this->checkTimeout()) { return false; }
   			fputs ($this->emailConnection, "To: $to" . "\r\n" ); 
			if ($this->checkTimeout()) { return false; }
   			fputs ($this->emailConnection, "MIME-Version: 1.0" . "\r\n" ); 
			if ($this->checkTimeout()) { return false; }
   			fputs ($this->emailConnection, "X-Mailer: JSS" . "\r\n" ); 
			if ($this->checkTimeout()) { return false; }
   			fputs ($this->emailConnection, "Content-Type: multipart/alternative; boundary=\"".$this->seperator."\";" . "\r\n" ); 
			if ($this->checkTimeout()) { return false; }			
   			fputs ($this->emailConnection, "\r\n" ); 
			if ($this->checkTimeout()) { return false; }

			$message =""; 
			if (chop($textMessage) != "") {
				$message .= "--".$this->seperator."\r\n"; 
				$message .= "Content-Type: text/plain\r\n";
				$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
				$message .= $textMessage."\r\n\r\n"; 
			}
			
			if (chop($htmlMessage) != "") {		
				$message .= "--".$this->seperator."\r\n"; 
				$message .= "Content-Type: text/html\r\n";
				$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
				$message .= $htmlMessage."\r\n\r\n";
			}
			
			$message .= "--".$this->seperator."--\r\n\r\n\r\n"; 

   			fputs ($this->emailConnection, "$message " . "\r\n" ); 
			if ($this->checkTimeout()) { return false; }
   			fputs ($this->emailConnection, ".\r\n"); 
			if ($this->checkTimeout()) { return false; }
     		$rcv = fgets ($this->emailConnection, 1024); 
			if ($this->checkTimeout()) { return false; }
   			fputs ($this->emailConnection, "RSET\r\n"); 
			if ($this->checkTimeout()) { return false; }
     		$rcv = fgets ($this->emailConnection, 1024); 
			if ($this->checkTimeout()) { return false; }
		}
		
		function closeSocket() {
 			fputs ($this->emailConnection, "QUIT\r\n"); 
   			$rcv = fgets ($this->emailConnection, 1024); 
 			fclose($this->emailConnection);		
		}
	}

	function unhtmlentitiesemail($string) { 
		$trans_tbl = get_html_translation_table (HTML_ENTITIES); 
	   	$trans_tbl = array_flip ($trans_tbl); 
	   	return strtr ($string, $trans_tbl); 
	}
?>
