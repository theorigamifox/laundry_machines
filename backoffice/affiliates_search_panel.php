<table cellpadding="2" cellspacing="0" class="table-list" width="100%">
			<tr>
				<td colspan="2" class="table-list-title"><center>Search Affiliates</center></td>
			</tr>
			<tr>
				<td class="table-list-entry1">


<table width="100% cellpadding="2" cellspacing="0">
<script language="JavaScript">
	function goSearchAffiliates(goreturn) {
		groupID = document.searchFormAffiliates.xGroupID.options[document.searchFormAffiliates.xGroupID.selectedIndex].value;
		status = document.searchFormAffiliates.xStatus.options[document.searchFormAffiliates.xStatus.selectedIndex].value;
		jssDetails.location.href='affiliates_listing.php?xSearchString='+document.searchFormAffiliates.xSearchString.value+'&xGroupID='+groupID+'&xStatus='+status+'&xType=SEARCH&<?php print userSessionGET(); ?>';
		if (goreturn == 1) {
			return false;
		}
	}
</script>
<form name="searchFormAffiliates" onSubmit="return goSearchAffiliates(1);">
<tr>
	<td align="right">
		<font class="normaltext">Status: <select name="xStatus" class="form-inputbox"><option value="X">All</option><option value="N">New</option><option value="L">Live</option><option value="H">On Hold</option><option value="D">Declined</option></select>
		<br>Group: <select name="xGroupID" class="form-inputbox"><option value="0">All</option>
		<?php
			dbConnect($dbA);
			$uResult = $dbA->query("select * from $tableAffiliatesGroups order by name");
			$uCount = $dbA->count($uResult);
			for ($f = 0; $f < $uCount; $f++) {
				$uRecord = $dbA->fetch($uResult);
				?><option value="<?php print $uRecord["groupID"]; ?>"><?php print $uRecord["name"]; ?></option><?php
			}
			$dbA->close();
		?>
		</select><br>
		<input type="text" class="form-inputbox" size="24" value="" name="xSearchString"><br>
		<input type="button" name="searchAffiliates" value="Search" class="button-grey" onClick="goSearchAffiliates(0);">
	</td>
</tr>
</form>
</table>


				</td>
			</tr>
		</table>