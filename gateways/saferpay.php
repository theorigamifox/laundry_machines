<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTPS,$jssStoreWebDirHTTP;
		$gatewayOptions = retrieveGatewayOptions("SAFERPAY");
		
		$rIN = $orderArray["randID"];
		
		$callBack = "$jssStoreWebDirHTTPS"."gateways/response/saferpay.php";
		
		$cDetails = returnCurrencyDetails($orderArray["currencyID"]);
		
		$randEncoded = substr(md5($orderArray["randID"]),0,30);

		$self_url = "http://" . $_SERVER["SERVER_NAME"] . $_SERVER["SCRIPT_NAME"];
		$self_url = substr($self_url, 0, strrpos($self_url, '/')) . "/";
		
		$attributes = array("-a", "AMOUNT", number_format($orderArray["orderTotal"]*100,0,'.',''),
				    "-a", "CURRENCY", $cDetails["code"],
				    "-a", "DESCRIPTION", $gatewayOptions["description"],
				    "-a", "ALLOWCOLLECT", "no",
				    "-a", "DELIVERY", "no",
				    "-a", "ACCOUNTID", $gatewayOptions["accountID"],
				    "-a", "BACKLINK", $jssStoreWebDirHTTP,
				    "-a", "FAILLINK", $self_url."failed.php",
				    "-a", "SUCCESSLINK", $callBack,
				    "-a", "ORDERID", $orderNumber);
		
		$strAttributes = join(" ", $attributes);
		
		/* path to the Saferpay command line executable */
		$execPath = $gatewayOptions["path"]; /* maybe another path */
		
		/* path to your Saferpay account */
		$confPath = $execPath; /* maybe another path */
		
		/* command line */
		$command = $execPath."saferpay -payinit -p $confPath $strAttributes";
		
		/* get the payinit URL */
		$fp = popen($command, "r");
		$payinit_url = fgets($fp, 4096);
	
		
?>
	<html>
	<head>
	<title>Saferpay Sample for PHP</title>
	<script src="http://www.saferpay.com/OpenSaferpayScript.js"></script>
	</head>
	<body>
	<h1>Check Out Sample Page</h1>
	<h2>Order Id: <?php print $attributes[29]; ?><br/></h2>
	<script language="JavaScript">OpenSaferpayTerminal('<?php print $payinit_url; ?>', this, 'LINK');</script>
	</body>
	</html>
<?php
	}
?>