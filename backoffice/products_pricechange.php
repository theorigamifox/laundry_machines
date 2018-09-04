<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$myForm = new formElements;
	
	dbConnect($dbA);
	getSectionsList(0,$sectionArray);
	$catArray = $dbA->retrieveAllRecords($tableProductsCategories,"categoryID");
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script>
	function checkFields() {
		if (parseFloat(document.detailsForm.xPercent.value) == 0 || document.detailsForm.xPercent.value == "") {
			alert("A percentage of zero will zero all prices. Please enter a non-zero value.");
			return false;
		}	
		if (confirm("Are you sure you wish to change prices\nNOTE: There is no undo function - you should backup your data first!")) {
			return true;
		} else {
			return false;
		}
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Global Price Change</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","products_pricechange_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="pricechange">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Product Selection</td>
		<td class="table-list-entry1" valign="top">
			<input type="radio" name="xSelection" value="CATEGORY" CHECKED> By Product Category
			&nbsp;<select name="xCategoryID" class="form-inputbox">
<?php		
			for ($f = 0; $f < count($catArray); $f++) {
?>
				<option value="<?php print $catArray[$f]["categoryID"]; ?>"><?php print $catArray[$f]["name"]; ?></option>
<?php
			}
?>
			</select>
			<br>
			<!--<input type="radio" name="xSelection" value="SECTION"> By Product Section
			&nbsp;<select name="xSectionID" class="form-inputbox">
		<?php
			for ($f = 0; $f < count($sectionArray); $f++) {
		?>
				<option value="<?php print $sectionArray[$f][0]; ?>"><?php print $sectionArray[$f][1]; ?></option>
		<?php
			}
		?>	
			</select>
			<br>-->
			<input type="radio" name="xSelection" value="ALL"> All Products
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Percentage Change +/-</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xPercent",7,6,"0","decimal"); ?> %</td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit","Update Prices"); ?></td>
	</tr>
</table>
</form>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>
