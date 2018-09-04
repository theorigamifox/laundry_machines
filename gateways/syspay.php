<?php
	function startProcessor($orderNumber) {
		global $dbA,$orderArray,$jssStoreWebDirHTTP;
		$gatewayOptions = retrieveGatewayOptions("SYSPAY");
		
		$rIN = $orderArray["randID"];
		
		$callBack = "$jssStoreWebDirHTTP"."gateways/response/syspay_return.php";
		
		$cDetails = returnCurrencyDetails($orderArray["currencyID"]);
		
		$randEncoded = substr(md5($orderArray["randID"]),0,30);
	
		
?>
		<HTML>
		<HEAD>
		<TITLE></TITLE>
		</HEAD>
		<BODY>
		<FORM NAME="processForm" METHOD="POST" ACTION="https://www.syspay.it/gateway/home.asp">
		<INPUT TYPE=HIDDEN NAME="COD_CLI" VALUE="<?php print $gatewayOptions["codcli"]; ?>">
		<INPUT TYPE=HIDDEN NAME="SHOPTRANSACTIONID" VALUE="<?php print $orderNumber; ?>">
		<INPUT TYPE=HIDDEN NAME="AMOUNT" VALUE="<?php print number_format($orderArray["orderTotal"]*100,0,'.',''); ?>">
		<INPUT TYPE=HIDDEN NAME="LANGUAGE" VALUE="<?php print $gatewayOptions["language"]; ?>">
		<INPUT TYPE=HIDDEN NAME="PAR_AGG" VALUE="">
		<INPUT TYPE=HIDDEN NAME="URLBACK" VALUE="<?php print $callBack; ?>">

		</FORM>
		<script language="JavaScript">
			document.processForm.submit();
		</script>
		</BODY>
		</HTML>
<?php
	}
?>
