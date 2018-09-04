<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript">
	function goDelete(shippingID) {
		if (confirm("Are you sure you wish to delete this shipping type?")) {
			self.location.href="shipping_types_process.php?xAction=delete&xShippingID="+shippingID+"&<?php print userSessionGET(); ?>";
		}
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Shipping Types</td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title">Name</td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	dbConnect($dbA);
	$uResult = $dbA->query("select * from $tableShippingTypes order by position,name");
	$uCount = $dbA->count($uResult);
	for ($f = 0; $f < $uCount; $f++) {
		$uRecord = $dbA->fetch($uResult);
?>
	<tr>
		<td class="table-list-entry1"><a href="shipping_types_detail.php?xType=edit&xShippingID=<?php print $uRecord["shippingID"]; ?>&<?php print userSessionGET(); ?>"><?php print $uRecord["name"]; ?></a></td>
		<td class="table-list-entry1" align="right">
			<button id="buttonEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='shipping_types_detail.php?xType=edit&xShippingID=<?php print $uRecord["shippingID"]; ?>&<?php print userSessionGET(); ?>';">Edit</button>&nbsp;<button id="buttonRates<?php print $f; ?>" class="button-green" onClick="self.location.href='shipping_rates.php?xShippingID=<?php print $uRecord["shippingID"]; ?>&<?php print userSessionGET(); ?>';">Rates</button>&nbsp;<button id="buttonDelete<?php print $f; ?>" class="button-delete" onClick="goDelete(<?php print $uRecord["shippingID"]; ?>);">Delete</button></td>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="1" class="table-list-title">Total Shipping Types:</td>
		<td class="table-list-title" align="right"><?php print $uCount; ?></td>
	</tr>
	<tr>
		<td colspan="2" class="table-list-title" align="right">
			<button name="buttonReorder" class="button-grey" onClick="self.location.href='reorder.php?xType=shippingtypes&<?php print userSessionGET(); ?>';">Sort / Reorder Types</button>
		</td>
	</tr>	
</table>
<p>
<button id="buttonSectionsEdit" class="button-expand" onClick="self.location.href='shipping_types_detail.php?xType=new&<?php print userSessionGET(); ?>'">Add New Shipping Type</button>
</center>
</BODY>
</HTML>
