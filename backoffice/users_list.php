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
	function goDelete(userID) {
		if (confirm("Are you sure you wish to delete this user?")) {
			self.location.href="users_process.php?xAction=delete&xUserID="+userID+"&<?php print userSessionGET(); ?>";
		}
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">List of Users</td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-title">Username</td>
		<td class="table-list-title">Real Name</td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	$dbA = new dbAccess();
	$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
	$uResult = $dbA->query("select * from $tableUsers order by username");
	$uCount = $dbA->count($uResult);
	for ($f = 0; $f < $uCount; $f++) {
		$uRecord = $dbA->fetch($uResult);
?>
	<tr>
		<td class="table-list-entry1"><a href="users_detail.php?xType=edit&xUserID=<?php print $uRecord["userID"]; ?>&<?php print userSessionGET(); ?>"><?php print $uRecord["username"]; ?></a></td>
		<td class="table-list-entry1"><?php print $uRecord["realname"]; ?></td>
		<td class="table-list-entry1" align="right">
			<button id="buttonEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='users_detail.php?xType=edit&xUserID=<?php print $uRecord["userID"]; ?>&<?php print userSessionGET(); ?>';">Edit</button><?php if ($uRecord["userID"] != 1) { ?>&nbsp;<button id="buttonDelete<?php print $f; ?>" class="button-delete" onClick="goDelete(<?php print $uRecord["userID"]; ?>);">Delete</button><?php } ?>&nbsp;<button id="buttonLog<?php print $f; ?>" class="button-view" onClick="self.location.href='users_log.php?xUsernameLog=<?php print $uRecord["username"]; ?>&xCommand=view&<?php print userSessionGET(); ?>';">View Log</button></td>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="2" class="table-list-title">Total Number of Users:</td>
		<td class="table-list-title" align="right"><?php print $uCount; ?></td>
	</tr>
</table>
</center>
</BODY>
</HTML>
