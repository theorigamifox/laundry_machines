<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	$xLiveService = getFORM("xLiveService");
	$submitButton = "Update Live Exchange Rate Service";
	$hiddenFields = "<input type='hidden' name='xAction' value='liveservice'><input type='hidden' name='xLiveService' value='$xLiveService'>";
	
	if ($xLiveService == "off") {
		doRedirect("general_currencies_process.php?xLiveService=off&xAction=liveservice&".userSessionGET());
	}
	if (retrieveOption("currencyLiveRates") != $xLiveService) {
		switch ($xLiveService) {
			case "Worldpay":
				$lrOptions[] = "";
				$lrOptions[] = "";
				break;
		}
	} else {
		$lrOptions = explode("|",retrieveOption("currencyLiveRatesInfo"));
	}
	
	$myForm = new formElements;

	$pageTitle = "Edit Service: ".$xLiveService;

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
<?php $myForm->createForm("detailsForm","general_currencies_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<?php include("currency/".strtolower($xLiveService).".php"); ?>
</form>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>