<table cellpadding="2" cellspacing="0" class="table-list" width="100%">
			<tr>
				<td colspan="2" class="table-list-title"><center>Search Customers</center></td>
			</tr>
			<tr>
				<td class="table-list-entry1">


<table width="100% cellpadding="2" cellspacing="0">
<script language="JavaScript">
	function goSearchCustomers(goreturn) {
		accTypeID = document.searchFormCustomers.xAccTypeID.options[document.searchFormCustomers.xAccTypeID.selectedIndex].value;
		jssDetails.location.href='customers_listing.php?xSearchString='+document.searchFormCustomers.xSearchString.value+'&xAccTypeID='+accTypeID+'&xType=SEARCH&<?php print userSessionGET(); ?>';
		if (goreturn == 1) {
			return false;
		}
	}
</script>
<form name="searchFormCustomers" onSubmit="return goSearchCustomers(1);">
<tr>
	<td align="right">
		<select name="xAccTypeID" class="form-inputbox"><option value="0">All</option>
		<?php
			dbConnect($dbA);
			$uResult = $dbA->query("select * from $tableCustomersAccTypes order by name");
			$uCount = $dbA->count($uResult);
			for ($f = 0; $f < $uCount; $f++) {
				$uRecord = $dbA->fetch($uResult);
				?><option value="<?php print $uRecord["accTypeID"]; ?>"><?php print $uRecord["name"]; ?></option><?php
			}
			$dbA->close();
		?>
		</select><br>
		<input type="text" class="form-inputbox" size="24" value="" name="xSearchString"><br>
		<input type="button" name="searchCustomers" value="Search" class="button-grey" onClick="goSearchCustomers(0);">
	</td>
</tr>
</form>
</table>


				</td>
			</tr>
		</table>