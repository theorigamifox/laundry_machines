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
	function goDelete(accTypeID) {
		if (confirm("Are you sure you wish to delete this customer account type?")) {
			self.location.href="customers_acctypes_process.php?xAction=delete&xAccTypeID="+accTypeID+"&<?php print userSessionGET(); ?>";
		}
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Customer Account Types</td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title">Name</td>
		<td class="table-list-title">Description</td>
		<td class="table-list-title">Discount %</td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	$dbA = new dbAccess();
	$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
	$uResult = $dbA->query("select * from $tableCustomersAccTypes order by name");
	$uCount = $dbA->count($uResult);
	for ($f = 0; $f < $uCount; $f++) {
		$uRecord = $dbA->fetch($uResult);
?>
	<tr>
		<td class="table-list-entry1"><a href="customers_acctypes_detail.php?xType=edit&xAccTypeID=<?php print $uRecord["accTypeID"]; ?>&<?php print userSessionGET(); ?>"><?php print $uRecord["name"]; ?></a></td>
		<td class="table-list-entry1"><?php print $uRecord["description"]; ?></td>
		<td class="table-list-entry1" align="right"><?php print $uRecord["defaultDiscount"]; ?>%</td>
		<td class="table-list-entry1" align="right">
			<button id="buttonEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='customers_acctypes_detail.php?xType=edit&xAccTypeID=<?php print $uRecord["accTypeID"]; ?>&<?php print userSessionGET(); ?>';">Edit</button><?php if ($uRecord["accTypeID"] != 1) { ?>&nbsp;<button id="buttonDelete<?php print $f; ?>" class="button-delete" onClick="goDelete(<?php print $uRecord["accTypeID"]; ?>);">Delete</button><?php } ?></td>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="3" class="table-list-title">Total Number of Account Types:</td>
		<td class="table-list-title" align="right"><?php print $uCount; ?></td>
	</tr>
</table>
<p>
<button id="buttonSectionsEdit" class="button-expand" onClick="self.location.href='customers_acctypes_detail.php?xType=new&<?php print userSessionGET(); ?>'">Add New Account Type</button>
</center>
</BODY>
</HTML>
