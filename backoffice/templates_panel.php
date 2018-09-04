<table cellpadding="2" cellspacing="0" class="table-list" width="100%">
			<tr>
				<td colspan="2" class="table-list-title"><center>Open Shop</center></td>
			</tr>
			<tr>
				<td class="table-list-entry1">
<a href="<?php print $jssStoreWebDirHTTP."index.php?xTFC=1"; ?>" target="_new">With Force Compile On</a>
<br><a href="<?php print $jssStoreWebDirHTTP."index.php?xRTU=1"; ?>" target="_new">With Uncompiled On</a>
<br><a href="<?php print $jssStoreWebDirHTTP."index.php?xTFC=0"; ?>" target="_new">With Normal Settings</a>
				</td>
			</tr>
		</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list" width="100%">
			<tr>
				<td colspan="2" class="table-list-title"><center>Template Navigation</center></td>
			</tr>
			<tr>
				<td class="table-list-entry1">
				<iframe name="jssNavigate" src="templates_navigate.php?<?php print userSessionGET(); ?>" width="100%" height="350" frameborder="0" STYLE="border:solid black 1px; margin:0"></iframe>


<table width="100% cellpadding="2" cellspacing="0">
<script language="JavaScript">
	function goSearchSections(goreturn) {
		jssDetails.location.href='sections_listing.php?xSearchString='+document.searchFormSections.xSearchString.value+'&xType=SEARCH&<?php print userSessionGET(); ?>';
		if (goreturn == 1) {
			return false;
		}
	}
	
	function showFile(theFile,xStartDir) {
		jssDetails.location.href = "templates_edit.php?xFile="+theFile+"&xStartDir="+xStartDir+"&<?php print userSessionGET(); ?>";
	}
	
	function refreshList() {
		jssNavigate.refreshList();
	}
</script>
				</td>
			</tr>
		</table>
</td>
</tr>
</table>