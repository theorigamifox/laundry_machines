<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$dbA = new dbAccess();
	$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
	
	$xProdsDivAdd = split(";",retrieveOption("prodDivsAdd"));
	$xProdsDivEdit = split(";",retrieveOption("prodDivsEdit"));
	$xProdsDivClone = split(";",retrieveOption("prodDivsClone"));
	$divsArray = array(
					array("name"=>"divGeneralDetails","title"=>"General Details","add"=>"","edit"=>"","clone"=>""),
					array("name"=>"divExtraFields","title"=>"Extra Fields","add"=>"","edit"=>"","clone"=>""),
					array("name"=>"divDigital","title"=>"Digital Download Options","add"=>"","edit"=>"","clone"=>""),
					array("name"=>"divGroup","title"=>"Group Product Settings","add"=>"","edit"=>"","clone"=>""),
					array("name"=>"divAdvancedPricing","title"=>"Advanced Pricing","add"=>"","edit"=>"","clone"=>""),
					array("name"=>"divStockControl","title"=>"Stock Control","add"=>"","edit"=>"","clone"=>""),
					array("name"=>"divProductOptions","title"=>"Product Options","add"=>"","edit"=>"","clone"=>""),
					array("name"=>"divAssociated","title"=>"Associated Products","add"=>"","edit"=>"","clone"=>""),
					array("name"=>"divMisc","title"=>"Misc.","add"=>"","edit"=>"","clone"=>""),
					array("name"=>"divLanguages","title"=>"Other Languages","add"=>"","edit"=>"","clone"=>"")
					);
	for ($f = 0; $f < count($divsArray); $f++) {
		for ($g = 0; $g < count($xProdsDivAdd); $g++) {
			if ($divsArray[$f]["name"] == $xProdsDivAdd[$g]) {
				$divsArray[$f]["add"] = " CHECKED";
			}
		}
		for ($g = 0; $g < count($xProdsDivEdit); $g++) {
			if ($divsArray[$f]["name"] == $xProdsDivEdit[$g]) {
				$divsArray[$f]["edit"] = " CHECKED";
			}
		}		
		for ($g = 0; $g < count($xProdsDivClone); $g++) {
			if ($divsArray[$f]["name"] == $xProdsDivClone[$g]) {
				$divsArray[$f]["clone"] = " CHECKED";
			}
		}			
	}
	
	$myForm = new formElements;
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
</HEAD>
<script>
	function checkFields() {
	}
</script>
<script language="JavaScript">
	function recalculateAddFields() {
		newFields = "";
		for (f = 0; f <=<?php print count($divsArray)-1; ?>; f++) {
			fieldChecked = eval("document.detailsForm.prodAdd"+f+".checked");
			if (fieldChecked) {
				theValue = eval("document.detailsForm.prodAdd"+f+".value");
				newFields = newFields + theValue+";";
			}
		}
		document.detailsForm.xProdDivsAdd.value = newFields;
	}

	function recalculateEditFields() {
		newFields = "";
		for (f = 0; f <=<?php print count($divsArray)-1; ?>; f++) {
			fieldChecked = eval("document.detailsForm.prodEdit"+f+".checked");
			if (fieldChecked) {
				theValue = eval("document.detailsForm.prodEdit"+f+".value");
				newFields = newFields + theValue+";";
			}
		}
		document.detailsForm.xProdDivsEdit.value = newFields;
	}	

	function recalculateCloneFields() {
		newFields = "";
		for (f = 0; f <=<?php print count($divsArray)-1; ?>; f++) {
			fieldChecked = eval("document.detailsForm.prodClone"+f+".checked");
			if (fieldChecked) {
				theValue = eval("document.detailsForm.prodClone"+f+".value");
				newFields = newFields + theValue+";";
			}
		}
		document.detailsForm.xProdDivsClone.value = newFields;
	}	
</script>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Product Editing</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","general_options_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<input type="hidden" name="xType" value="productediting">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Product Sections Hidden<br>By Default</td>
		<td class="table-list-entry1" valign="top">
			<table cellpadding="2" cellspacing="0" border="0">
				<tr>
					<td class="table-list-title" valign="top">Section</td>
					<td class="table-list-title" valign="top">Add</td>
					<td class="table-list-title" valign="top">Edit</td>
					<td class="table-list-title" valign="top">Clone</td>
				</tr>
<?php
			for ($f = 0; $f < count($divsArray); $f++) {
?>
<tr>
	<td>
		<font class="normaltext"><?php print $divsArray[$f]["title"]; ?></font>
	</td>
	<td align="center">
		<input type="checkbox" name="prodAdd<?php print $f; ?>" value="<?php print $divsArray[$f]["name"]; ?>" <?php print $divsArray[$f]["add"]; ?> onClick="recalculateAddFields();">
	</td>
	<td align="center">
		<input type="checkbox" name="prodEdit<?php print $f; ?>" value="<?php print $divsArray[$f]["name"]; ?>" <?php print $divsArray[$f]["edit"]; ?> onClick="recalculateEditFields();">
	</td>
	<td align="center">
		<input type="checkbox" name="prodClone<?php print $f; ?>" value="<?php print $divsArray[$f]["name"]; ?>" <?php print $divsArray[$f]["clone"]; ?> onClick="recalculateCloneFields();">
	</td>
</tr>
<?php
			}
?>
			</table>
			<input type="hidden" name="xProdDivsAdd" value="<?php print retrieveOption("prodDivsAdd"); ?>">
			<input type="hidden" name="xProdDivsEdit" value="<?php print retrieveOption("prodDivsEdit"); ?>">
			<input type="hidden" name="xProdDivsClone" value="<?php print retrieveOption("prodDivsClone"); ?>">
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Default Check 2-way Links<br>For Associated Products</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xProdEditAssociatedLinkDefault",retrieveOption("prodEditAssociatedLinkDefault"),"01"); ?></td>
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