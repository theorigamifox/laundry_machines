<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$xSectionID=getFORM("xSectionID");
	if ($xSectionID == "") { $xSectionID = 1; }
	$dbA = new dbAccess();
	$thisSectionID = $xSectionID;
	$reachedRoot = false;
	$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
	$thisSectionPath = "";
	while ($reachedRoot == false) {
		$sResult = $dbA->query("select * from $tableSections where sectionID=$thisSectionID");
		$sRecord = $dbA->fetch($sResult);
		if (strlen($thisSectionPath) > 0) {
			$thisSectionPath = "<a href=\"sections_structure.php?xSectionID=".$sRecord["sectionID"]."&".userSessionGET()."\">".$sRecord["title"]."</a> <font class=\"boldtext\"><b>&gt;</b></font> ".$thisSectionPath;
		} else {
			$thisSectionPath = "<a href=\"sections_structure.php?xSectionID=".$sRecord["sectionID"]."&".userSessionGET()."\">".$sRecord["title"]."</a>";
		}
		$thisSectionID = $sRecord["parent"];
		if ($thisSectionID == 0) {
			$reachedRoot = true;
		}
	}
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript">
	function goDeleteSection(sectionID,parentID) {
		if (confirm("Are you sure you wish to delete this section?")) {
			self.location.href="sections_process.php?xAction=delete&xSectionID="+sectionID+"&xParent="+parentID+"&<?php print userSessionGET(); ?>";
		}
	}
	
	function goDeleteProduct(productID,parentID) {
		if (confirm("Are you sure you wish to delete this product?")) {
			self.location.href="products_product_process.php?xAction=delete&xProductID="+productID+"&xSectionID="+parentID+"&<?php print userSessionGET(); ?>";
		}
	}
	
	function goRemoveProduct(productID,parentID) {
		if (confirm("Are you sure you wish to remove this product from this page?")) {
			self.location.href="products_product_process.php?xAction=remove&xProductID="+productID+"&xSectionID="+parentID+"&<?php print userSessionGET(); ?>";
		}
	}	

	function goSearchProducts() {
		if (document.searchProductsForm.xSearchString.value == "") {
			rc=alert("Please enter a search term first.");
			return false;
		} else {
			self.location.href='products_listing.php?xSelect=Y&xSectionID=<?php print $xSectionID; ?>&xSearchString='+document.searchProductsForm.xSearchString.value+'&xType=SEARCH&<?php print userSessionGET(); ?>';
			return false;
		}
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Section Structure: <?php print $thisSectionPath; ?></td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list" width="99%" border="0">
	<tr>
		<td class="table-list-title" colspan="6" valign="top">
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td><font class="boldtext">SUB SECTIONS</font></td>
					<td align="right"><button id="buttonSectionsEdit" class="button-expand" onClick="self.location.href='sections_detail.php?xType=edit&xSectionID=<?php print $xSectionID ?>&xParent=<?php print $xSectionID; ?>&<?php print userSessionGET(); ?>'">Edit Section</button>&nbsp;
						<button id="buttonSectionsAdd" class="button-expand" onClick="self.location.href='sections_detail.php?xType=new&xParent=<?php print $xSectionID; ?>&<?php print userSessionGET(); ?>'">Insert New Sub Section</button>&nbsp;
						<button id="buttonSectionsReorder" class="button-expand" onClick="self.location.href='reorder.php?xType=sections&xSectionID=<?php print $xSectionID; ?>&<?php print userSessionGET(); ?>'">Sort / Reorder</button>
					</td>
				</tr>
			</table>
		</td>
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
	$ssResult = $dbA->query("select * from $tableSections where parent=$xSectionID order by position,title");
	$ssCount = $dbA->count($ssResult);
	for ($f = 0; $f < $ssCount; $f++) {
		$ssRecord = $dbA->fetch($ssResult);
		$subsResult = $dbA->query("select * from $tableSections where parent=".$ssRecord["sectionID"]);
		$subsCount = $dbA->count($subsResult);
		$subsResult = $dbA->query("select * from $tableProducts,$tableProductsTree where $tableProducts.productID = $tableProductsTree.productID and sectionID=".$ssRecord["sectionID"]." order by position,name");
		$subpCount = $dbA->count($subsResult);;
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
			<button name="editsec" class="button-edit" onClick="self.location.href='sections_detail.php?xType=edit&xSectionID=<?php print $ssRecord["sectionID"]; ?>&xParent=<?php print $xSectionID; ?>&<?php print userSessionGET(); ?>'">Edit</button>&nbsp;
			<button name="deletesec" class="button-delete" onClick="goDeleteSection(<?php print $ssRecord["sectionID"]; ?>,<?php print $xSectionID; ?>);">Delete</button>

		<!--<a href="sections_detail.php?xType=edit&xSectionID=<?php print $ssRecord["sectionID"]; ?>&xParent=<?php print $xSectionID; ?>&<?php print userSessionGET(); ?>"><img src="images/edit.gif" width="25" height="15" border="0" alt="[view | edit]"></a><?php if ($ssRecord["sectionID"] != 1) { ?>&nbsp;<a href="javascript:goDeleteSection(<?php print $ssRecord["sectionID"]; ?>,<?php print $xSectionID; ?>);"><img src="images/delete.gif" width="38" height="15" border="0" alt="[delete]"></a><?php } ?></td>
	--></tr>
<?php
	}
?>
	<tr>
		<td colspan="5" class="table-list-title">Total Number of Sections:</td>
		<td class="table-list-title" align="right"><?php print $ssCount; ?></td>
	</tr>
</table>
<p>
<?php
	if (retrieveOption("stockShowSectionStructure") == 1) {
		$extraFields = 1;
	} else {
		$extraFields = 0;
	}
?>
<table cellpadding="2" cellspacing="0" class="table-list" width="99%">
	<tr>
		<td class="table-list-title" colspan="<?php print 6+$extraFields; ?>">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td><font class="boldtext">PRODUCTS SHOWN HERE</font></td>
					<td align="right" valign="top">
						<button id="buttonProductsAdd" class="button-grey" onClick="self.location.href='products_product_detail.php?xType=new&xSectionID=<?php print $xSectionID; ?>&<?php print userSessionGET(); ?>'">Insert New Product Here</button>&nbsp;
						<button id="buttonProductsReorder" class="button-grey" onClick="self.location.href='reorder.php?xType=products&xSectionID=<?php print $xSectionID; ?>&<?php print userSessionGET(); ?>'">Sort / Reorder</button>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="table-list-title">Code</td>
		<td class="table-list-title">Name</td>
		<td class="table-list-title"><center>Visible</center></td>
		<?php if (retrieveOption("stockShowSectionStructure") == 1) { ?><td class="table-list-title"><center>Stock Level</center></td><?php } ?>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	$ssResult = $dbA->query("select * from $tableProducts,$tableProductsTree where $tableProducts.productID = $tableProductsTree.productID and $tableProducts.productID != 1 and sectionID=$xSectionID order by position,name");
	$ssCount = $dbA->count($ssResult);
	for ($f = 0; $f < $ssCount; $f++) {
		$spRecord = $dbA->fetch($ssResult);
		if ($spRecord["scEnabled"] == "Y") {
			$stockShow = $spRecord["scLevel"];
		} else {
			$stockShow = "n/a";
		}
?>
	<tr>
		<td class="table-list-entry1"><a href="products_product_detail.php?xType=edit&xProductID=<?php print $spRecord["productID"]; ?>&xSectionID=<?php print $xSectionID; ?>&<?php print userSessionGET(); ?>"><?php print $spRecord["code"]; ?></a></td>
		<td class="table-list-entry1"><a href="products_product_detail.php?xType=edit&xProductID=<?php print $spRecord["productID"]; ?>&xSectionID=<?php print $xSectionID; ?>&<?php print userSessionGET(); ?>"><?php print $spRecord["name"]; ?></a></td>
		<td class="table-list-entry1"><center><?php print $spRecord["visible"]; ?></center></td>
		<?php if (retrieveOption("stockShowSectionStructure") == 1) { ?><td class="table-list-entry1"><center><?php print $stockShow; ?></center></td><?php } ?>
		<td class="table-list-entry1" align="right">
			<button name="buttonPEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='products_product_detail.php?xType=edit&xProductID=<?php print $spRecord["productID"]; ?>&xSectionID=<?php print $xSectionID; ?>&<?php print userSessionGET(); ?>'">Edit</button>&nbsp;
			<button name="buttonPClone<?php print $f; ?>" class="button-clone" onClick="self.location.href='products_product_detail.php?xType=clone&xProductID=<?php print $spRecord["productID"]; ?>&xSectionID=<?php print $xSectionID; ?>&<?php print userSessionGET(); ?>'">Clone</button>&nbsp;
			<button name="buttonPRemove<?php print $f; ?>" class="button-remove" onClick="javascript:goRemoveProduct(<?php print $spRecord["productID"]; ?>,<?php print $xSectionID; ?>);">Remove</button>
			<!--<a href=><img src="images/edit.gif" width="25" height="15" border="0" alt="[view | edit]"></a>&nbsp;<a href="javascript:goRemoveProduct(<?php print $spRecord["productID"]; ?>,<?php print $xSectionID; ?>);"><img src="images/remove.gif" width="46" height="15" border="0" alt="[remove from page]"></a></td>-->
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="<?php print 3+$extraFields; ?>" class="table-list-title">Total Number of Products:</td>
		<td class="table-list-title" align="right"><?php print $ssCount; ?></td>
	</tr>
	<form name="searchProductsForm" onSubmit="return goSearchProducts();">
	<tr>
		<td colspan="<?php print 4+$extraFields; ?>" class="table-list-title">
			<center>
			<table cellpadding="2" cellspacing="0" border="0">
				<tr>
					<td><font class="boldtext">Search For Products To Add Here:</td>
					<td><font class="boldtext">
						<input type="text" class="form-inputbox" size="24" value="" name="xSearchString">&nbsp;
					<button id="buttonSearchProducts" class="button-expand" onClick="goSearchProducts();">Search</button>
					</td>
				</tr>
			</table>
			</center>
		</td>
	</tr>
	</form>
</table>
</center>
</BODY>
</HTML>
