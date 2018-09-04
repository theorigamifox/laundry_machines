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
	function goDelete(snippetID) {
		if (confirm("Are you sure you wish to delete this snippet?")) {
			self.location.href="general_snippets_process.php?xAction=delete&xSnippetID="+snippetID+"&<?php print userSessionGET(); ?>";
		}
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Snippets</td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title">Name</td>
		<td class="table-list-title">Title</td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	dbConnect($dbA);
	$uResult = $dbA->query("select * from $tableSnippets order by name");
	$uCount = $dbA->count($uResult);
	for ($f = 0; $f < $uCount; $f++) {
		$uRecord = $dbA->fetch($uResult);
?>
	<tr>
		<td class="table-list-entry1"><a href="general_snippets_detail.php?xType=edit&xSnippetID=<?php print $uRecord["snippetID"]; ?>&<?php print userSessionGET(); ?>"><?php print $uRecord["name"]; ?></a></td>
		<td class="table-list-entry1"><a href="general_snippets_detail.php?xType=edit&xSnippetID=<?php print $uRecord["snippetID"]; ?>&<?php print userSessionGET(); ?>"><?php print $uRecord["title"]; ?></a></td>
		<td class="table-list-entry1" align="right">
			<button id="buttonEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='general_snippets_detail.php?xType=edit&xSnippetID=<?php print $uRecord["snippetID"]; ?>&<?php print userSessionGET(); ?>';">Edit</button>&nbsp;<button id="buttonEdit<?php print $f; ?>" class="button-delete" onClick="goDelete(<?php print $uRecord["snippetID"]; ?>);">Delete</button></td>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="2" class="table-list-title">Total Snippets:</td>
		<td class="table-list-title" align="right"><?php print $uCount; ?></td>
	</tr>
</table>
<p>
<button id="buttonSnippetAdd" class="button-expand" onClick="self.location.href='general_snippets_detail.php?xType=new&<?php print userSessionGET(); ?>'">Add New Snippet</button>
</center>
</BODY>
</HTML>
