<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	dbConnect($dbA);
	$accTypeArray = $dbA->retrieveAllRecords($tableCustomersAccTypes,"accTypeID");

?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript">
	function goDelete(paymentID) {
		if (confirm("Are you sure you wish to delete this payment option?")) {
			self.location.href="payment_options_process.php?xAction=delete&xPaymentID="+paymentID+"&<?php print userSessionGET(); ?>";
		}
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Payment Options</td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title">Name</td>
		<td class="table-list-title">Type</td>
		<td class="table-list-title">Enabled</td>
		<td class="table-list-title">Account Types</td>		
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	$uResult = $dbA->query("select * from $tablePaymentOptions order by position,paymentID");
	$uCount = $dbA->count($uResult);
	for ($f = 0; $f < $uCount; $f++) {
		$uRecord = $dbA->fetch($uResult);
		if ($uRecord["type"] == "CC") {
			$uExtra = " -&gt; ".$uRecord["gateway"];
		} else {
			$uExtra = "";
		}
		$accountList = $uRecord["accTypes"];
		$accSplit = split(";",$accountList);
		$accList = "";
		for ($g = 0; $g < count($accSplit); $g++) {
			if ($accSplit[$g] == "0") {
				$accList .= "All<BR>";
			}
			for ($h = 0; $h < count($accTypeArray); $h++) {
				if ($accSplit[$g] == $accTypeArray[$h]["accTypeID"]) {
					$accList .= $accTypeArray[$h]["name"]."<BR>";
				}
			}
		}
?>
	<tr>
		<td class="table-list-entry1" valign="top"><a href="payment_options_detail.php?xType=edit&xPaymentID=<?php print $uRecord["paymentID"]; ?>&<?php print userSessionGET(); ?>"><?php print $uRecord["name"]; ?></a>
		</td>
		<td class="table-list-entry1" valign="top"><?php print $uRecord["type"].$uExtra; ?>
			<?php if ($uRecord["type"] == "CC" && $uRecord["gateway"] != "OFFLINE") { ?>
				&nbsp;<button id="buttonOptions<?php print $f; ?>" class="button-green" onClick="self.location.href='payment_gateway_detail.php?xType=edit&xGateway=<?php print $uRecord["gateway"]; ?>&<?php print userSessionGET(); ?>';">Settings</button>
			<?php } ?>
			<?php if ($uRecord["type"] == "PAYPAL") { ?>
				&nbsp;<button id="buttonOptions<?php print $f; ?>" class="button-green" onClick="self.location.href='payment_gateway_detail.php?xType=edit&xGateway=PAYPAL&<?php print userSessionGET(); ?>';">Settings</button>
			<?php } ?>			
			<?php if ($uRecord["type"] == "NOCHEX") { ?>
				&nbsp;<button id="buttonOptions<?php print $f; ?>" class="button-green" onClick="self.location.href='payment_gateway_detail.php?xType=edit&xGateway=NOCHEX&<?php print userSessionGET(); ?>';">Settings</button>
			<?php } ?>			

		</td>
		<td class="table-list-entry1" valign="top"><?php print $uRecord["enabled"]; ?></td>
		<td class="table-list-entry1" valign="top"><?php print $accList; ?></td>
		<td class="table-list-entry1" align="right" valign="top">
			<button id="buttonEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='payment_options_detail.php?xType=edit&xPaymentID=<?php print $uRecord["paymentID"]; ?>&<?php print userSessionGET(); ?>';">Edit</button><?php if ($uRecord["paymentID"] > 3) { ?>&nbsp;<button id="buttonEdit<?php print $f; ?>" class="button-delete" onClick="goDelete(<?php print $uRecord["paymentID"]; ?>);">Delete</button><?php } ?>
		</td>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="4" class="table-list-title">Total Payment Options:</td>
		<td class="table-list-title" align="right"><?php print $uCount; ?></td>
	</tr>
	<tr>
		<td colspan="5" class="table-list-title" align="right">
			<button name="buttonReorder" class="button-grey" onClick="self.location.href='reorder.php?xType=paymentoptions&<?php print userSessionGET(); ?>';">Sort / Reorder Options</button>
		</td>
	</tr>	
</table>
<p>
<button id="buttonSectionsEdit" class="button-expand" onClick="self.location.href='payment_options_detail.php?xType=new&<?php print userSessionGET(); ?>'">Add New Offline Option</button>
</center>
</BODY>
</HTML>
