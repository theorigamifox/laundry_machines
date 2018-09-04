<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	
	$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"position,name");
	
	$xSearchFields = split(";",retrieveOption("searchFields"));
	$fieldsArray = array(
					array("name"=>"code","title"=>"Product Code","selected"=>""),
					array("name"=>"name","title"=>"Product Name","selected"=>""),
					array("name"=>"shortdescription","title"=>"Short Description","selected"=>""),
					array("name"=>"description","title"=>"Full Description","selected"=>""),
					array("name"=>"keywords","title"=>"Product Keywords","selected"=>""),
					array("name"=>"metaDescription","title"=>"Meta Description Tag","selected"=>""),
					array("name"=>"metaKeywords","title"=>"Meta Keywords Tag","selected"=>"")
					);
	if (is_array($extraFieldsArray)) {
		for ($f = 0; $f < count($extraFieldsArray); $f++) {
			switch ($extraFieldsArray[$f]["type"]) {
				case "TEXT":
				case "TEXTAREA":
					$fieldsArray[] = array("name"=>"extrafield".$extraFieldsArray[$f]["extraFieldID"],"title"=>$extraFieldsArray[$f]["title"],"selected"=>"");
			}
		}
	}
	if (is_array($fieldsArray)) {
		for ($f = 0; $f < count($fieldsArray); $f++) {
			for ($g = 0; $g < count($xSearchFields); $g++) {
				if ($fieldsArray[$f]["name"] == $xSearchFields[$g]) {
					$fieldsArray[$f]["selected"] = " CHECKED";
				}
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
	function recalculateFields() {
		newFields = "";
		for (f = 0; f <=<?php print count($fieldsArray)-1; ?>; f++) {
			fieldChecked = eval("document.detailsForm.searchField"+f+".checked");
			if (fieldChecked) {
				theValue = eval("document.detailsForm.searchField"+f+".value");
				newFields = newFields + theValue+";";
			}
		}
		document.detailsForm.xSearchFields.value = newFields;
	}
</script>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Search Settings</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","general_options_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<input type="hidden" name="xType" value="search">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Number Products Per Page</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xSearchProductsPerPage",10,5,retrieveOption("searchProductsPerPage"),"integer"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Product Fields To Search On</td>
		<td class="table-list-entry1" valign="top">
<?php
			for ($f = 0; $f < count($fieldsArray); $f++) {
?>
<input type="checkbox" name="searchField<?php print $f; ?>" value="<?php print $fieldsArray[$f]["name"]; ?>" <?php print $fieldsArray[$f]["selected"]; ?> onClick="recalculateFields();"> <?php print $fieldsArray[$f]["title"]; ?><br>
<?php
			}
?>
			<input type="hidden" name="xSearchFields" value="<?php print retrieveOption("searchFields"); ?>">
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Include Sections In Search?</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xSearchIncludeSections",retrieveOption("searchIncludeSections"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Max Number Of Sections To Return</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xSearchMaxSections",10,5,retrieveOption("searchMaxSections"),"integer"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">If 1 Product Found Take Directly To Product Page</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xSearchSoloProductShow",retrieveOption("searchSoloProductShow"),"01"); ?></td>
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
