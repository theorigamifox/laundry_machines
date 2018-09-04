<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$dbA = new dbAccess();
	$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
	
	$myForm = new formElements;
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
</HEAD>
<script language="JavaScript">
	function recalculateFields() {
		newFields = "";
		for (f = 0; f <=6; f++) {
			fieldChecked = eval("document.detailsForm.searchField"+f+".checked");
			if (fieldChecked) {
				theValue = eval("document.detailsForm.searchField"+f+".value");
				newFields = newFields + theValue+";";
			}
		}
		document.detailsForm.xSearchFields.value = newFields;
	}
</script>
<script>
	function checkFields() {
	}
</script>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Search/Listing Settings</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","general_options_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<input type="hidden" name="xType" value="searchlist">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Number Products Per Page</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xAdminProdPerPage",10,5,retrieveOption("adminProdPerPage"),"integer"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Number Sections Per Page</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xAdminSecPerPage",10,5,retrieveOption("adminSecPerPage"),"integer"); ?></td>
	</tr>
	<!--<tr>
		<td class="table-list-title" valign="top">Number User Actions Per Page</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xAdminUserLogPerPage",10,5,retrieveOption("adminUserLogPerPage"),"integer"); ?></td>
	</tr>-->
	<tr>
		<td class="table-list-title" valign="top">Number Orders Per Page</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xAdminOrdersPerPage",10,5,retrieveOption("adminOrdersPerPage"),"integer"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Number Customers Per Page</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xAdminCustomersPerPage",10,5,retrieveOption("adminCustomersPerPage"),"integer"); ?></td>
	</tr>			
	<tr>
		<td class="table-list-title" valign="top">Number Customer Reviews Per Page</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xAdminReviewsPerPage",10,5,retrieveOption("adminReviewsPerPage"),"integer"); ?></td>
	</tr>		
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit","Update Settings"); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>
