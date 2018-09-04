<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTP,$currArray,$tableCountries;
		$gatewayOptions = retrieveGatewayOptions("BARCLAYSEPDQ");
		
		$callBack = "$jssStoreWebDirHTTP"."process.php";
		
		$cDetails = returnCurrencyDetails($orderArray["currencyID"]);

		$clientIDs = explode("|",$gatewayOptions["clientid"]);
		$curBits = "";
		for ($f = 0; $f < count($currArray); $f++) {
			$curBits[$currArray[$f]["currencyID"]] = 1;
			for ($g = 0; $g < count($clientIDs); $g++) {
				$thisPage = explode(":",$clientIDs[$g]);
				if ($thisPage[0] == $currArray[$f]["currencyID"]) {
					$curBits[$currArray[$f]["currencyID"]] = $thisPage[1];
				}
			}
		}

		$server="secure2.epdq.co.uk";
		$url="/cgi-bin/CcxBarclaysEpdqEncTool.e";
		$params="clientid=".$curBits[$orderArray["currencyID"]];
		$params.="&password=".$gatewayOptions["password"];
		$params.="&oid=".$orderNumber."-".$orderArray["randID"];
		$params.="&chargetype=".$gatewayOptions["chargetype"];
		$params.="&currencycode=".$cDetails["isonumber"];
		$params.="&total=".number_format($orderArray["orderTotal"],2,'.','');
		$response = pullpage( $server,$url,$params );
		$response_lines=explode("\n",$response);
		$response_line_count=count($response_lines);
		for ($i=0;$i<$response_line_count;$i++){
		    if (preg_match('/epdqdata/',$response_lines[$i])){
		        $strEPDQ=$response_lines[$i];
		    }
		}
		
		$countryMatch = $orderArray["country"];
		$cResult = $dbA->query("select * from $tableCountries where name=\"$countryMatch\"");
		if (count($cResult) > 0) {
			$cRecord = $dbA->fetch($cResult);
			$customerCountry = $cRecord["isocode"];
		} else {
			$customerCountry = 826;
		}
		
		$deliveryMatch = $orderArray["deliveryCountry"];
		$cResult = $dbA->query("select * from $tableCountries where name=\"$deliveryMatch\"");
		if (count($cResult) > 0) {
			$cRecord = $dbA->fetch($cResult);
			$deliveryCountry = $cRecord["isocode"];
		} else {
			$deliveryCountry = 826;
		}
		
		$tpl = new tSys("templates/","gatewaytransfer.html",$requiredVars,0);	
		
		$gArray["method"] = "POST";
		$gArray["action"] = "https://secure2.epdq.co.uk/cgi-bin/CcxBarclaysEpdq.e";
		$gArray["fields"][] = array("name"=>"returnurl","value"=>$callBack);
		$gArray["fields"][] = array("name"=>"merchantdisplayname","value"=>$gatewayOptions["merchantdisplayname"]);
		$gArray["fields"][] = array("name"=>"cpi_textcolor","value"=>$gatewayOptions["cpi_textcolor"]);
		$gArray["fields"][] = array("name"=>"cpi_bgcolor","value"=>$gatewayOptions["cpi_bgcolor"]);
		$gArray["fields"][] = array("name"=>"baddr1","value"=>$orderArray["address1"]);
		$gArray["fields"][] = array("name"=>"baddr2","value"=>$orderArray["address2"]);
		$gArray["fields"][] = array("name"=>"bcity","value"=>$orderArray["town"]);
		$gArray["fields"][] = array("name"=>"bcountyprovince","value"=>$orderArray["county"]);
		$gArray["fields"][] = array("name"=>"bcountry","value"=>$customerCountry);
		$gArray["fields"][] = array("name"=>"bpostalcode","value"=>$orderArray["postcode"]);
		$gArray["fields"][] = array("name"=>"email","value"=>$orderArray["email"]);
		$gArray["fields"][] = array("name"=>"saddr1","value"=>$orderArray["deliveryAddress1"]);
		$gArray["fields"][] = array("name"=>"saddr2","value"=>$orderArray["deliveryAddress2"]);
		$gArray["fields"][] = array("name"=>"scity","value"=>$orderArray["deliveryTown"]);
		$gArray["fields"][] = array("name"=>"scountyprovince","value"=>$orderArray["deliveryCounty"]);
		$gArray["fields"][] = array("name"=>"scountry","value"=>$deliveryCountry);
		$gArray["fields"][] = array("name"=>"spostalcode","value"=>$orderArray["deliveryPostcode"]);
		
		$gArray["fields"][] = array("name"=>"supportedcardtypes","value"=>$gatewayOptions["supportedcardtypes"]);
		
		if ($gatewayOptions["cpi_logo"] != "") {
			$gArray["fields"][] = array("name"=>"cpi_logo","value"=>$gatewayOptions["cpi_logo"]);
		}
		$gArray["fields"][] = array("name"=>"","value"=>"","full"=>$strEPDQ);
		
		$mArray = $gArray;
		
		$gArray["process"] = "document.automaticForm.submit();";
		
		$tpl->addVariable("shop",templateVarsShopRetrieve());
		$tpl->addVariable("labels",templateVarsLabelsRetrieve());
		$tpl->addVariable("automaticForm",$gArray);
		$tpl->addVariable("manualForm",$mArray);
		$tpl->showPage();
		exit;
	}

	function pullpage( $host, $usepath, $postdata = "" ) {
 		$fp = fsockopen( $host, 80, &$errno, &$errstr, 60 );
 		if( !$fp ) {
    		print "$errstr ($errno)<br>\n";
 		} else {
    		fputs( $fp, "POST $usepath HTTP/1.0\n");
    		$strlength = strlen( $postdata );
    		fputs( $fp, "Content-type: application/x-www-form-urlencoded\n" );
    		fputs( $fp, "Content-length: ".$strlength."\n\n" );
    		fputs( $fp, $postdata."\n\n" );
   			$output = "";
     		while( !feof( $fp ) ) {
        		$output .= fgets( $fp, 1024);
    		}
    		fclose( $fp);
 		}
 		return $output;
	}
?>
