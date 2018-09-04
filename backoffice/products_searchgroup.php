<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	$xType = getFORM("xType");
	$xOffset = getFORM("xOffset");
	if ($xOffset=="") { $xOffset = 0; }	
	$xSearchString = getFORM("xSearchString");
	$searchAppend = "&xType=SEARCH&xSearchString=$xSearchString";
	$theQuery = "select $tableProducts.productID,code,name from $tableProducts LEFT JOIN $tableExtraFieldsValues on $tableProducts.productID = $tableExtraFieldsValues.productID where ($tableProducts.code like \"%$xSearchString%\" or $tableProducts.name like \"%$xSearchString%\") and groupedProduct = 'N' and $tableExtraFieldsValues.content IS NULL group by $tableProducts.productID order by code,name";
	dbConnect($dbA);
	$ordersperpage = retrieveOption("adminProdPerPage");
	$result=$dbA->query($theQuery);
	$resultCount = $dbA->count($result);
	$theQuery .= " LIMIT $xOffset,$ordersperpage";
	$upperCount = $xOffset+$ordersperpage;
	$lowerCount = $xOffset+1;
	$middleButtons = "<b>$lowerCount - ".$upperCount."</b>&nbsp;";
	if ($xOffset-$ordersperpage > -1) {
		$pOffset = $xOffset - $ordersperpage;
		$previousButton = "<button id=\"buttonPrev\" class=\"button-expand\" onClick=\"self.location.href='products_searchgroup.php?".userSessionGET().$searchAppend."&xOffset=$pOffset'\">&lt; PREV</button>";
		//$previousButton .= "&nbsp;<button id=\"buttonTop\" class=\"button-expand\" onClick=\"self.location.href='products_searchgroup.php?".userSessionGET().$searchAppend."&xOffset=0'\">[TOP]</button>";
	} else {
		$previousButton = "";
	}
	if ($xOffset+$ordersperpage < $resultCount) {
		$nOffset = $xOffset + $ordersperpage;
		$nextButton = "<button id=\"buttonNext\" class=\"button-expand\" onClick=\"self.location.href='products_searchgroup.php?".userSessionGET().$searchAppend."&xOffset=$nOffset'\">NEXT &gt;</button>";
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
</HEAD>
<BODY class="detail-body">
<center>
<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td colspan="2" class="table-white-no-border" align="right">Total: <b><?php print $resultCount; ?></b>, <?php print $navButtons; ?>&nbsp;</td>
	</tr>
	<tr>
		<td class="table-list-title">Code</td>
		<td class="table-list-title">Name</td>
	</tr>
<?php
	$ssResult = $dbA->query($theQuery);
	$ssCount = $dbA->count($ssResult);
	for ($f = 0; $f < $ssCount; $f++) {
		$ssRecord = $dbA->fetch($ssResult);
		$normalShow = $ssRecord["name"];
		$ssRecord["name"] = str_replace('"','\"',$ssRecord["name"]);
		$ssRecord["name"] = htmlentities($ssRecord["name"],ENT_QUOTES);
?>
	<tr>
		<td class="table-list-entry1"><a href='javascript:parent.addGroup(<?php print $ssRecord["productID"]; ?>,"<?php print $ssRecord["code"]; ?>","<?php print $ssRecord["name"]; ?>");'><?php print $ssRecord["code"]; ?></a></td>
		<td class="table-list-entry1"><a href='javascript:parent.addGroup(<?php print $ssRecord["productID"]; ?>,"<?php print $ssRecord["code"]; ?>","<?php print $ssRecord["name"]; ?>");'><?php print $normalShow; ?></a></td>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="2" class="table-white-no-border" align="right">Total: <b><?php print $resultCount; ?></b>, <?php print $navButtons; ?>&nbsp;</td>
	</tr>
</table>
</center>
</BODY>
</HTML>
