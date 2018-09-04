<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	$xType = getFORM("xType");
	$xOffset = getFORM("xOffset");
	if ($xOffset=="") { $xOffset = 0; }	
	if ($xType=="ABC") {
		$pageTitle = "Reviews By Product";
		$searchAppend = "&xType=ABC";
		$theQuery = "select $tableReviews.*, $tableProducts.code, $tableProducts.name as pname from $tableReviews,$tableProducts where $tableReviews.productID = $tableProducts.productID order by $tableProducts.code,$tableProducts.name,$tableReviews.reviewID DESC";
	}
	if ($xType=="UNMOD") {
		$pageTitle = "Unmoderated (Invisible) Reviews";
		$searchAppend = "&xType=INVISIBLE";
		$theQuery = "select $tableReviews.*, $tableProducts.code, $tableProducts.name as pname from $tableReviews,$tableProducts where $tableReviews.productID = $tableProducts.productID and $tableReviews.visible = 'N' order by $tableReviews.reviewID DESC";
	}
	/*if ($xType=="SEARCH") {
		$xSearchString = getFORM("xSearchString");
		$pageTitle = "Sections Search: &quot;$xSearchString&quot;";
		$searchAppend = "&xType=SEARCH&xSearchString=$xSearchString";
		$theQuery = "select * from $tableSections where (title like \"%$xSearchString%\" or shortDescription like \"%$xSearchString%\") order by title";
	}*/

	dbConnect($dbA);
	$ordersperpage = retrieveOption("adminReviewsPerPage");
	$result=$dbA->query($theQuery);
	$resultCount = $dbA->count($result);
	$theQuery .= " LIMIT $xOffset,$ordersperpage";
	$upperCount = $xOffset+$ordersperpage;
	$lowerCount = $xOffset+1;
	$middleButtons = "Viewing <b>$lowerCount - ".$upperCount."</b>&nbsp;";
	if ($xOffset-$ordersperpage > -1) {
		$pOffset = $xOffset - $ordersperpage;
		$previousButton = "<input type=\"button\" id=\"buttonPrev\" class=\"button-expand\" onClick=\"self.location.href='customers_reviews.php?".userSessionGET().$searchAppend."&xOffset=$pOffset'\" value=\"&lt; PREV\">";
		$previousButton .= "&nbsp;<input type=\"button\" id=\"buttonTop\" class=\"button-expand\" onClick=\"self.location.href='customers_reviews.php?".userSessionGET().$searchAppend."&xOffset=0'\" value=\"[TOP]\">";
	} else {
		$previousButton = "";
	}
	if ($xOffset+$ordersperpage < $resultCount) {
		$nOffset = $xOffset + $ordersperpage;
		$nextButton = "<input type=\"button\" id=\"buttonNext\" class=\"button-expand\" onClick=\"self.location.href='customers_reviews.php?".userSessionGET().$searchAppend."&xOffset=$nOffset'\" value=\"NEXT &gt;\">";
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
	function goDelete(reviewID) {
		if (confirm("Are you sure you wish to delete this review?")) {
			self.location.href="customers_reviews_process.php?xAction=delete&xReviewID="+reviewID+"&<?php print userSessionGET(); ?>&<?php print hiddenFromGET(); ?>";
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
		<td colspan="5" class="table-white-no-border" align="right">Total selected reviews: <b><?php print $resultCount; ?></b>, <?php print $navButtons; ?>&nbsp;</td>
	</tr>
	<tr>
		<td class="table-list-title">Author</td>
		<td class="table-list-title">Date</td>
		<td class="table-list-title">Product</td>
		<td class="table-list-title">Rating</td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	$ssResult = $dbA->query($theQuery);
	$ssCount = $dbA->count($ssResult);
	for ($f = 0; $f < $ssCount; $f++) {
		$ssRecord = $dbA->fetch($ssResult);
		$productString = ($ssRecord["code"] != "") ? $ssRecord["code"]." : ".$ssRecord["pname"] : $ssRecord["pname"];
?>
	<tr>
		<td class="table-list-entry1"><a href="customers_reviews_detail.php?xType=edit&xReviewID=<?php print $ssRecord["reviewID"]; ?>&<?php print userSessionGET(); ?>&<?php print hiddenFromGET(); ?>"><?php print $ssRecord["name"]; ?></a></td>
		<td class="table-list-entry1"><?php print formatDate($ssRecord["reviewdate"]); ?></td>
		<td class="table-list-entry1"><?php print $productString; ?></td>
		<td class="table-list-entry1"><?php print $ssRecord["rating"]; ?></td>
		<td class="table-list-entry1" align="right">
			<input type="button" name="buttonPEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='customers_reviews_detail.php?xType=edit&xReviewID=<?php print $ssRecord["reviewID"]; ?>&<?php print userSessionGET(); ?>&<?php print hiddenFromGET(); ?>'" value="Edit">&nbsp;<input type="button" name="buttonPDelete<?php print $f; ?>" class="button-delete" onClick="goDelete(<?php print $ssRecord["reviewID"]; ?>);" value="Delete"></td>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="4" class="table-list-title">Total Number of Reviews:</td>
		<td class="table-list-title" align="right"><?php print $ssCount; ?></td>
	</tr>
</table>
</center>
</BODY>
</HTML>
