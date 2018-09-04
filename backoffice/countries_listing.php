<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	$xType = getFORM("xType");
	if ($xType=="") { $xType="ABC"; }
	$xOffset = getFORM("xOffset");
	if ($xOffset=="") { $xOffset = 0; }	
	$xDirectional = "";
	$xDirectional = "";
	dbConnect($dbA);	
	if ($xType=="ABC") {
		$pageTitle = "Main Country List";
		$searchAppend = "&xType=ABC".$xDirectional;
		$theQuery = "select * from $tableCountries order by name";
	}
	$ordersperpage = 30;
	$result=$dbA->query($theQuery);
	$resultCount = $dbA->count($result);
	$theQuery .= " LIMIT $xOffset,$ordersperpage";
	$upperCount = $xOffset+$ordersperpage;
	$lowerCount = $xOffset+1;
	$middleButtons = "Viewing <b>$lowerCount - ".$upperCount."</b>&nbsp;";
	if ($xOffset-$ordersperpage > -1) {
		$pOffset = $xOffset - $ordersperpage;
		$previousButton = "<input type=\"button\" id=\"buttonPrev\" class=\"button-expand\" onClick=\"self.location.href='countries_listing.php?".userSessionGET().$searchAppend."&xOffset=$pOffset'\" value=\"&lt; PREV\">";
		$previousButton .= "&nbsp;<input type=\"button\" id=\"buttonTop\" class=\"button-expand\" onClick=\"self.location.href='countries_listing.php?".userSessionGET().$searchAppend."&xOffset=0'\" value=\"[TOP]\">";
	} else {
		$previousButton = "";
	}
	if ($xOffset+$ordersperpage < $resultCount) {
		$nOffset = $xOffset + $ordersperpage;
		$nextButton = "<input type=\"button\" id=\"buttonNext\" class=\"button-expand\" onClick=\"self.location.href='countries_listing.php?".userSessionGET().$searchAppend."&xOffset=$nOffset'\" value=\"NEXT &gt;\">";
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

	$myForm = new formElements;
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript">
	function goDelete(countryID) {
		if (confirm("Are you sure you wish to delete this country?")) {
			self.location.href="countries_process.php?xAction=delete&xCountryID="+countryID+"&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>";
		}
	}
</script>
</HEAD>
<BODY class="detail-body">
<form name="selectCustomers">
<input type="hidden" name="selectedCustomers" value="">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title"><?php print $pageTitle; ?></td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td colspan="5" class="table-white-no-border" align="right">Total selected countries: <b><?php print $resultCount; ?></b>, <?php print $navButtons; ?>&nbsp;</td>
	</tr>
	<tr>
		<td class="table-list-title">Name</td>
		<td class="table-list-title">ISO Code</td>
		<td class="table-list-title">ISO Number</td>
		<td class="table-list-title">Visible</td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	$ssResult = $dbA->query($theQuery);
	$ssCount = $dbA->count($ssResult);
	for ($f = 0; $f < $ssCount; $f++) {
		$ssRecord = $dbA->fetch($ssResult);
?>
	<tr>
		<td class="table-list-entry1"><a href="countries_detail.php?xType=edit&xCountryID=<?php print $ssRecord["countryID"]; ?>&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>"><?php print $ssRecord["name"]; ?></a></td>
		<td class="table-list-entry1"><?php print $ssRecord["isocode"]; ?></td>
		<td class="table-list-entry1"><?php print $ssRecord["isonumber"]; ?></td>
		<td class="table-list-entry1"><?php print $ssRecord["visible"]; ?></td>
		<td class="table-list-entry1" align="right">
			<input type="button" name="buttonPEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='countries_detail.php?xType=edit&xCountryID=<?php print $ssRecord["countryID"]; ?>&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>'" value="Edit">&nbsp;
			<input type="button" name="buttonPDelete<?php print $f; ?>" class="button-delete" onClick="javascript:goDelete(<?php print $ssRecord["countryID"]; ?>);" value="Delete">
		</td>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="4" class="table-list-title">Total Number of Countries:</td>
		<td class="table-list-title" align="right"><?php print $ssCount; ?></td>
	</tr>
</table>
<p>
<input type="button" id="buttonCountryAdd" class="button-expand" onClick="self.location.href='countries_detail.php?xType=new&<?php print userSessionGET(); ?>&<?php print hiddenFromGET(); ?>'" value="Add New Country">
</center>
</form>
</BODY>
</HTML>
