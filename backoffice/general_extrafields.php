<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript">
	function goDelete(extraFieldID) {
		if (confirm("Are you sure you wish to delete this extra field?")) {
			self.location.href="general_extrafields_process.php?xAction=delete&xExtraFieldID="+extraFieldID+"&<?php print userSessionGET(); ?>";
		}
	}
</script>
<script>
	function checkFields() {
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Extra Fields</td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title">Name</td>		
		<td class="table-list-title">Title</td>
		<td class="table-list-title">Type</td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	$dbA = new dbAccess();
	$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
	$uResult = $dbA->query("select * from $tableExtraFields order by position,name");
	$uCount = $dbA->count($uResult);
	for ($f = 0; $f < $uCount; $f++) {
		$uRecord = $dbA->fetch($uResult);
?>
	<tr>
		<td class="table-list-entry1"><a href="general_extrafields_detail.php?xType=edit&xExtraFieldID=<?php print $uRecord["extraFieldID"]; ?>&<?php print userSessionGET(); ?>"><?php print $uRecord["name"]; ?></a></td>
		<td class="table-list-entry1"><?php print $uRecord["title"]; ?></td>
		<td class="table-list-entry1"><?php print $uRecord["type"]; ?></td>
		<td class="table-list-entry1" align="right">
			<button name="test" class="button-edit" onClick="self.location.href='general_extrafields_detail.php?xType=edit&xExtraFieldID=<?php print $uRecord["extraFieldID"]; ?>&<?php print userSessionGET(); ?>'">Edit</button>&nbsp;
			<button name="test" class="button-delete" onClick="goDelete(<?php print $uRecord["extraFieldID"]; ?>);">Delete</button>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="3" class="table-list-title">Total Extra Fields:</td>
		<td class="table-list-title" align="right"><?php print $uCount; ?></td>
	</tr>
	<tr>
		<td colspan="4" class="table-list-title" align="right">
			<button name="buttonReorder" class="button-grey" onClick="self.location.href='reorder.php?xType=extrafields&<?php print userSessionGET(); ?>';">Sort / Reorder Extra Fields</button>
		</td>
	</tr>	
</table>

<form name="extraFieldForm" onSubmit="return false;">
<p>
<select name="xFieldType" class="form-inputbox">
<option>TEXT</option>
<option>TEXTAREA</option>
<option>IMAGE</option>
<option>SELECT</option>
<option>CHECKBOXES</option>
<option>RADIOBUTTONS</option>
<option>USERINPUT</option>
</select>
<button name="buttonSectionsEdit" class="button-expand" onClick="self.location.href='general_extrafields_detail.php?xType=new&xFieldType='+document.extraFieldForm.xFieldType.options[document.extraFieldForm.xFieldType.selectedIndex].text+'&<?php print userSessionGET(); ?>'">Add New Field</button>
</center>
</form>
</BODY>
</HTML>
