<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	$xType = getFORM("xType");
	switch ($xType) {
		case "C":
			$pageTitle = "Customer Fields";
			break;
		case "D":
			$pageTitle = "Address/Delivery Fields";
			break;
		case "O":
			$pageTitle = "Extra Order Fields";
			break;
		case "F":
			$pageTitle = "Contact Form Fields";
			break;	
		case "CC":
			$pageTitle = "Credit Card Fields<br>(applicable if using Offline method or payment gateway that credit card details can be supplied to)";
			break;	
		case "G":
			$pageTitle = "Gift Certificate Fields";
			break;	
		case "AF":
			$pageTitle = "Affiliate Account Fields";
			break;		
		case "SU":
			$pageTitle = "Supplier Fields";
			break;	
	}
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript">
	function goDelete(fieldID,mytype) {
		if (confirm("Are you sure you wish to delete this field?")) {
			self.location.href="customers_fields_process.php?xAction=delete&xFieldID="+fieldID+"&xType="+mytype+"&<?php print userSessionGET(); ?>";
		}
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title"><?php print $pageTitle; ?></td>
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
	$uResult = $dbA->query("select * from $tableCustomerFields where type='$xType' order by position,titleText");
	$uCount = $dbA->count($uResult);
	for ($f = 0; $f < $uCount; $f++) {
		$uRecord = $dbA->fetch($uResult);
?>
	<tr>
		<td class="table-list-entry1"><a href="customers_fields_detail.php?xType=<?php print $xType; ?>&xCmd=edit&xFieldID=<?php print $uRecord["fieldID"]; ?>&<?php print userSessionGET(); ?>"><?php print $uRecord["fieldname"]; ?></a></td>
		<td class="table-list-entry1"><?php print $uRecord["titleText"]; ?></td>
		<td class="table-list-entry1"><?php print $uRecord["fieldtype"]; ?></td>
		<td class="table-list-entry1" align="right">
			<button name="test" class="button-edit" onClick="self.location.href='customers_fields_detail.php?xType=<?php print $xType; ?>&xCmd=edit&xFieldID=<?php print $uRecord["fieldID"]; ?>&<?php print userSessionGET(); ?>'">Edit</button>&nbsp;
			<?php if ($uRecord["deletable"] == 1) { ?><button name="test" class="button-delete" onClick="goDelete(<?php print $uRecord["fieldID"]; ?>,'<?php print $uRecord["type"]; ?>');">Delete</button><?php } ?>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="3" class="table-list-title">Total Fields:</td>
		<td class="table-list-title" align="right"><?php print $uCount; ?></td>
	</tr>
	<tr>
		<td colspan="4" class="table-list-title" align="right">
			<button name="buttonReorder" class="button-grey" onClick="self.location.href='reorder.php?xType=fields&xFType=<?php print $xType; ?>&<?php print userSessionGET(); ?>';">Sort / Reorder Fields</button>
		</td>
	</tr>	
</table>
<?php
	if ($xType != "CC" && $xType != "G") {
?>
<form name="extraFieldForm">
<p>
<select name="xFieldType" class="form-inputbox">
<option>TEXT</option>
<option>TEXTAREA</option>
<option>SELECT</option>
<option>CHECKBOX</option>
</select>
&nbsp;<input type="button" name="anf" class="button-expand" onClick="self.location.href='customers_fields_detail.php?xType=<?php print $xType; ?>&xCmd=new&xFieldType='+document.extraFieldForm.xFieldType.options[document.extraFieldForm.xFieldType.selectedIndex].text+'&<?php print userSessionGET(); ?>'" value="Add New Field">
<?php
	}
?>
</center>
</form>
</BODY>
</HTML>
