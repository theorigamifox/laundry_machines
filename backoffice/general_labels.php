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
	function goDelete(labelID) {
		if (confirm("Are you sure you wish to delete this label?")) {
			self.location.href="general_labels_process.php?xAction=delete&xLabelID="+labelID+"&<?php print userSessionGET(); ?>";
		}
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Labels</td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title">Type</td>
		<td class="table-list-title" align="right">Number</td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	dbConnect($dbA);
	$uResult = $dbA->query("select *,count(*) as counter from $tableLabels group by type order by type");
	$uCount = $dbA->count($uResult);
	$daCounter = 0;
	for ($f = 0; $f < $uCount; $f++) {
		$uRecord = $dbA->fetch($uResult);
		$daCounter = $daCounter + $uRecord["counter"];
?>
	<tr>
		<td class="table-list-entry1"><a href="general_labels_detail.php?xType=<?php print $uRecord["type"]; ?>&<?php print userSessionGET(); ?>"><?php print $uRecord["type"]; ?></a></td>
		<td class="table-list-entry1" align="right"><?php print $uRecord["counter"]; ?></td>
		<td class="table-list-entry1" align="right">
			<button id="buttonNew<?php print $f; ?>" class="button-green" onClick="self.location.href='general_labels_add.php?xType=<?php print $uRecord["type"]; ?>&<?php print userSessionGET(); ?>';">Add</button>
			&nbsp;<button id="buttonEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='general_labels_detail.php?xType=<?php print $uRecord["type"]; ?>&<?php print userSessionGET(); ?>';">Edit</button></td>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="1" class="table-list-title">Total Labels:</td>
		<td class="table-list-title" align="right"><?php print $daCounter; ?></td>
		<td class="table-list-title" align="right">&nbsp;</td>
	</tr>
</table>
</BODY>
</HTML>
