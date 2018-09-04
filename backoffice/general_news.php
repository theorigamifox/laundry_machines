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
	function goDelete(newsID) {
		if (confirm("Are you sure you wish to delete this news item?")) {
			self.location.href="general_news_process.php?xAction=delete&xNewsID="+newsID+"&<?php print userSessionGET(); ?>";
		}
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Latest News</td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title">Title</td>
		<td class="table-list-title">Date</td>
		<td class="table-list-title">Posted By</td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	dbConnect($dbA);
	$uResult = $dbA->query("select * from $tableNews order by position, datetime DESC");
	$uCount = $dbA->count($uResult);
	for ($f = 0; $f < $uCount; $f++) {
		$uRecord = $dbA->fetch($uResult);
		if ($uRecord["postedBy"] == "") { $uRecord["postedBy"] = "&nbsp;"; }
?>
	<tr>
		<td class="table-list-entry1"><a href="general_news_detail.php?xType=edit&xNewsID=<?php print $uRecord["newsID"]; ?>&<?php print userSessionGET(); ?>"><?php print $uRecord["title"]; ?></a></td>
		<td class="table-list-entry1"><a href="general_news_detail.php?xType=edit&xNewsID=<?php print $uRecord["newsID"]; ?>&<?php print userSessionGET(); ?>"><?php print formatDate($uRecord["datetime"]); ?></a></td>
		<td class="table-list-entry1"><?php print $uRecord["postedBy"]; ?></td>
		<td class="table-list-entry1" align="right">
			<button id="buttonEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='general_news_detail.php?xType=edit&xNewsID=<?php print $uRecord["newsID"]; ?>&<?php print userSessionGET(); ?>';">Edit</button>&nbsp;<button id="buttonEdit<?php print $f; ?>" class="button-delete" onClick="goDelete(<?php print $uRecord["newsID"]; ?>);">Delete</button></td>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="3" class="table-list-title">Total News:</td>
		<td class="table-list-title" align="right"><?php print $uCount; ?></td>
	</tr>
	<tr>
		<td colspan="4" class="table-list-title" align="right">
			<button name="buttonReorder" class="button-grey" onClick="self.location.href='reorder.php?xType=news&<?php print userSessionGET(); ?>';">Sort / Reorder News</button>
		</td>
	</tr>	
</table>
<p>
<button id="buttonSnippetAdd" class="button-expand" onClick="self.location.href='general_news_detail.php?xType=new&<?php print userSessionGET(); ?>'">Add New News</button>
</center>
</BODY>
</HTML>
