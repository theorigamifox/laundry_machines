<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	$xGateway = getFORM("xGateway");
	$submitButton = "Update Gateway Settings";
	$hiddenFields = "<input type='hidden' name='xAction' value='options'><input type='hidden' name='xGateway' value='$xGateway'>";
	$gatewayOptions = retrieveGatewayOptions($xGateway);
	
	$myForm = new formElements;

	$gatewayArray = $dbA->retrieveAllRecords($tableCCProcessing,"name");

	$pageTitle = "";
	for ($f=0; $f < count($gatewayArray); $f++) {
		if ($xGateway == $gatewayArray[$f]["gateway"]) {
			$pageTitle = "Edit Gateway: ".$gatewayArray[$f]["name"];
			break;
		}
	}
	
	switch ($xGateway) {
		case "PAYPAL":
			$pageTitle = "Edit Gateway: PayPal";
			break;
		case "NOCHEX":
			$pageTitle = "Edit Gateway: Nochex";
			break;
	}
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
</HEAD>
<script>
	function checkFields() {
	}
</script>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title"><?php print $pageTitle; ?></td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","payment_gateway_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<?php include("gateways/".strtolower($xGateway).".php"); ?>
</form>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();

	function retrieveGatewayOptions($theGateway) {
		global $dbA,$tableGatewayConfigs;
		$result = $dbA->query("select * from $tableGatewayConfigs where gateway='$theGateway'");
		$count = $dbA->count($result);
		$gatewayOptions = "";
		for ($f = 0; $f < $count; $f++) {
			$record = $dbA->fetch($result);
			$gatewayOptions[$record["fieldname"]] = $record["fieldvalue"];
		}
		return $gatewayOptions;
	}	
?>