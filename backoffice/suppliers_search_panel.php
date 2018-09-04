<table cellpadding="2" cellspacing="0" class="table-list" width="100%">
			<tr>
				<td colspan="2" class="table-list-title"><center>Search Suppliers</center></td>
			</tr>
			<tr>
				<td class="table-list-entry1">


<table width="100% cellpadding="2" cellspacing="0">
<script language="JavaScript">
	function goSearchSuppliers(goreturn) {		jssDetails.location.href='suppliers_listing.php?xSearchString='+document.searchFormSuppliers.xSearchString.value+'&xType=SEARCH&<?php print userSessionGET(); ?>';
		if (goreturn == 1) {
			return false;
		}
	}
</script>
<form name="searchFormSuppliers" onSubmit="return goSearchSuppliers(1);">
<tr>
	<td align="right">
		<input type="text" class="form-inputbox" size="24" value="" name="xSearchString"><br>
		<input type="button" name="searchSuppliers" value="Search" class="button-grey" onClick="goSearchSuppliers(0);">
	</td>
</tr>
</form>
</table>


				</td>
			</tr>
		</table>