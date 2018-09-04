<table cellpadding="2" cellspacing="0" class="table-list" width="100%">
			<tr>
				<td colspan="2" class="table-list-title"><center>Search Sections</center></td>
			</tr>
			<tr>
				<td class="table-list-entry1">


<table width="100% cellpadding="2" cellspacing="0">
<script language="JavaScript">
	function goSearchSections(goreturn) {
		jssDetails.location.href='sections_listing.php?xSearchString='+document.searchFormSections.xSearchString.value+'&xType=SEARCH&<?php print userSessionGET(); ?>';
		if (goreturn == 1) {
			return false;
		}
	}
</script>
<form name="searchFormSections" onSubmit="return goSearchSections(1);">
<tr>
	<td align="right">
		<input type="text" class="form-inputbox" size="24" value="" name="xSearchString"><br>
		<input type="button" name="searchSections" value="Search" class="button-grey" onClick="goSearchSections(0);">
	</td>
</tr>
</form>
</table>


				</td>
			</tr>
		</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list" width="100%">
			<tr>
				<td colspan="2" class="table-list-title"><center>Search Products</center></td>
			</tr>
			<tr>
				<td class="table-list-entry1">


<table width="100% cellpadding="2" cellspacing="0">
<script language="JavaScript">
	function goSearchProducts(goreturn) {
		jssDetails.location.href='products_listing.php?xSearchString='+document.searchFormProducts.xSearchString.value+'&xType=SEARCH&xCategoryID='+document.searchFormProducts.xCategoryID.value+'&<?php print userSessionGET(); ?>';
		if (goreturn == 1) {
			return false;
		}
	}
</script>
<form name="searchFormProducts" onSubmit="return goSearchProducts(1);">
<tr>
	<td align="right">
		<font class="boldtext">Category: <select name="xCategoryID" class="form-inputbox"><option value="0">All</option>
		<?php
			dbConnect($dbA);
			$uResult = $dbA->query("select * from $tableProductsCategories order by name");
			$uCount = $dbA->count($uResult);
			for ($f = 0; $f < $uCount; $f++) {
				$uRecord = $dbA->fetch($uResult);
				?><option value="<?php print $uRecord["categoryID"]; ?>"><?php print $uRecord["name"]; ?></option><?php
			}
			$dbA->close();
		?>
		</select><br>
		<input type="text" class="form-inputbox" size="24" value="" name="xSearchString"><br>
		<input type="button" name="searchProducts" value="Search" class="button-grey" onClick="goSearchProducts(0);">
	</td>
</tr>
</form>
</table>


				</td>
			</tr>
		</table>
