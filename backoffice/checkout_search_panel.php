<table cellpadding="2" cellspacing="0" class="table-list" width="100%">
			<tr>
				<td colspan="2" class="table-list-title"><center>Search Gift Certificates</center></td>
			</tr>
			<tr>
				<td class="table-list-entry1">


<table width="100% cellpadding="2" cellspacing="0">
<script language="JavaScript">
	function goSearchGiftCerts(goreturn) {
		theStatus=document.searchFormGC.xStatus.options[document.searchFormGC.xStatus.selectedIndex].value;
		jssDetails.location.href='giftcerts_listing.php?xStatus='+theStatus+'&xSearchString='+document.searchFormGC.xSearchString.value+'&xType=SEARCH&<?php print userSessionGET(); ?>';
		if (goreturn == 1) {
			return false;
		}
	}
</script>
<form name="searchFormGC" onSubmit="return goSearchGiftCerts(1);">
<tr>
	<td align="right">
		<font class="normaltext"><b>Status:</b>&nbsp;
		<select name="xStatus" class="form-inputbox">
		<option value="X">All</option>
		<option value="N">Not Activated</option>
		<option value="A">Activated</option>
		<option value="E">Expired</option>
		</select><br>
		<input type="text" class="form-inputbox" size="24" value="" name="xSearchString"><br>
		<input type="button" name="searchCerts" value="Search" class="button-grey" onClick="goSearchGiftCerts(0);">
	</td>
</tr>
</form>
</table>


				</td>
			</tr>
		</table>