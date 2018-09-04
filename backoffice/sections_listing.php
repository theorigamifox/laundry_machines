<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	$xType = getFORM("xType");
	$xOffset = getFORM("xOffset");
	if ($xOffset=="") { $xOffset = 0; }	
	if ($xType=="ABC") {
		$pageTitle = "ABC Sections Listing";
		$searchAppend = "&xType=ABC";
		$theQuery = "select * from $tableSections order by title";
	}
	if ($xType=="INVISIBLE") {
		$pageTitle = "Invisible Sections Listing";
		$searchAppend = "&xType=INVISIBLE";
		$theQuery = "select * from $tableSections where visible=\"N\" order by title";
	}
	if ($xType=="SEARCH") {
		$xSearchString = getFORM("xSearchString");
		$pageTitle = "Sections Search: &quot;$xSearchString&quot;";
		$searchAppend = "&xType=SEARCH&xSearchString=$xSearchString";
		$theQuery = "select * from $tableSections where (title like \"%$xSearchString%\" or shortDescription like \"%$xSearchString%\") order by title";
	}

	dbConnect($dbA);
	$ordersperpage = retrieveOption("adminSecPerPage");
	$result=$dbA->query($theQuery);
	$resultCount = $dbA->count($result);
	$theQuery .= " LIMIT $xOffset,$ordersperpage";
	$upperCount = $xOffset+$ordersperpage;
	$lowerCount = $xOffset+1;
	$middleButtons = "Viewing <b>$lowerCount - ".$upperCount."</b>&nbsp;";
	if ($xOffset-$ordersperpage > -1) {
		$pOffset = $xOffset - $ordersperpage;
		$previousButton = "<button id=\"buttonPrev\" class=\"button-expand\" onClick=\"self.location.href='sections_listing.php?".userSessionGET().$searchAppend."&xOffset=$pOffset'\">&lt; PREV</button>";
		$previousButton .= "&nbsp;<button id=\"buttonTop\" class=\"button-expand\" onClick=\"self.location.href='sections_listing.php?".userSessionGET().$searchAppend."&xOffset=0'\">[TOP]</button>";
		//$previousButton = "<A href=\"search_listing.php?".userSessionGET().$searchAppend."&xOffset=$pOffset\">&lt;&lt;Previous</a>";
	} else {
		$previousButton = "";
	}
	if ($xOffset+$ordersperpage < $resultCount) {
		$nOffset = $xOffset + $ordersperpage;
		$nextButton = "<button id=\"buttonNext\" class=\"button-expand\" onClick=\"self.location.href='sections_listing.php?".userSessionGET().$searchAppend."&xOffset=$nOffset'\">NEXT &gt;</button>";
		//$nextButton = "<A href=\"sections_listing.php?".userSessionGET().$searchAppend."&xOffset=$nOffset\">Next &gt;&gt;</a>";
	} else {
		$nextButton = "";
	}
	$searchAppend .= "&xOffset=$xOffset";
	if ($previousButton=="" && $nextButton=="") {
		$navButtons = $middleButtons;
	}
	if ($previousButton=="" && $nextButton!="") {
		$navButtons = $middleButtons.$nextButton;
	}
	if ($previousButton!="" && $nextButton=="") {
		$navButtons = $middleButtons.$previousButton;
	}
	if ($previousButton!="" && $nextButton!="") {
		$navButtons = $middleButtons.$previousButton."&nbsp;".$nextButton;
	}	

?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript">
	function goDelete(sectionID) {
		if (confirm("Are you sure you wish to delete this section?")) {
			self.location.href="sections_process.php?xAction=delete&xSectionID="+sectionID+"&<?php print userSessionGET(); ?>&<?php print hiddenFromGET(); ?>";
		}
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title"><?php print $pageTitle; ?></td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td colspan="6" class="table-white-no-border" align="right">Total selected sections: <b><?php print $resultCount; ?></b>, <?php print $navButtons; ?>&nbsp;</td>
	</tr>
	<tr>
		<td class="table-list-title">Title</td>
		<td class="table-list-title">Short Description</td>
		<td class="table-list-title"><center>Visible</center></td>
		<td class="table-list-title" align="right">Sections</td>
		<td class="table-list-title" align="right">Products</td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	$ssResult = $dbA->query($theQuery);
	$ssCount = $dbA->count($ssResult);
	for ($f = 0; $f < $ssCount; $f++) {
		$ssRecord = $dbA->fetch($ssResult);
		$subsResult = $dbA->query("select * from $tableSections where parent=".$ssRecord["sectionID"]);
		$subsCount = $dbA->count($subsResult);
		$subsResult = $dbA->query("select * from $tableProductsTree where productID != 1 and sectionID=".$ssRecord["sectionID"]);
		$subpCount = $dbA->count($subsResult);
		$shortDescription = $ssRecord["shortDescription"];
		if (strlen($shortDescription) > 60) {
			$shortDescription = substr($shortDescription,0,60)."...";
		}
?>
	<tr>
		<td class="table-list-entry1"><a href="sections_structure.php?xSectionID=<?php print $ssRecord["sectionID"]; ?>&<?php print userSessionGET(); ?>"><?php print $ssRecord["title"]; ?></a></td>
		<td class="table-list-entry1"><?php print $shortDescription; ?></td>
		<td class="table-list-entry1"><center><?php print $ssRecord["visible"]; ?></center></td>
		<td class="table-list-entry1" align="right"><?php print $subsCount; ?></td>
		<td class="table-list-entry1" align="right"><?php print $subpCount; ?></td>
		<td class="table-list-entry1" align="right">
			<button name="buttonPEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='sections_detail.php?xType=edit&xSectionID=<?php print $ssRecord["sectionID"]; ?>&<?php print userSessionGET(); ?>&<?php print hiddenFromGET(); ?>'">Edit</button><?php if ($ssRecord["sectionID"] != 1) { ?>&nbsp;<button name="buttonPDelete<?php print $f; ?>" class="button-delete" onClick="goDelete(<?php print $ssRecord["sectionID"]; ?>);">Delete</button><?php } ?></td>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="5" class="table-list-title">Total Number of Sections:</td>
		<td class="table-list-title" align="right"><?php print $ssCount; ?></td>
	</tr>
</table>
</center>
</BODY>
</HTML>
