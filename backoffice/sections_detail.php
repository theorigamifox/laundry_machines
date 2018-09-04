<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	
	$xType=getFORM("xType");
	$xParent=getFORM("xParent");
	if ($xType=="new") {
		$pageTitle = "Add New Section";
		$submitButton = "Insert Section";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>".hiddenReturnPOST();

		$xIsVisibleYes = "CHECKED";
		$xIsVisibleNo = "";	
		$sRecord["templateFile"] = "section.html";	
		$sRecord["accTypes"] = ";0;";
	}
	if ($xType=="edit") {
		$xSectionID = getFORM("xSectionID");
		$pageTitle = "Edit Existing Section";
		$submitButton = "Edit Section";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xSectionID' value='$xSectionID'>".hiddenReturnPOST();

		$sResult = $dbA->query("select * from $tableSections where sectionID=$xSectionID");	
		$sRecord = $dbA->fetch($sResult);

		$isVisible = $sRecord["visible"];
		if ($isVisible == "N") {
			$xIsVisibleYes = "";
			$xIsVisibleNo = "CHECKED";
		} else {
			$xIsVisibleYes = "CHECKED";
			$xIsVisibleNo = "";
		}		
	}

	$accTypes = $dbA->retrieveAllRecords($tableCustomersAccTypes,"accTypeID");
	$languages = $dbA->retrieveAllRecords($tableLanguages,"languageID");

	$noDouble = TRUE;
	for ($f = 0; $f < count($languages); $f++) {
		if ($languages[$f]["doubleByte"] == "Y") {
			$noDouble = FALSE;
		}
	}
	
	if ($noDouble) {
		foreach ($sRecord as $key => $value) {
			$sRecord[$key] = htmlspecialchars($value);
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
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title"><?php print $pageTitle; ?></td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","sections_process.php","","multipart"); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<input type="hidden" name="xParentReturn" value="<?php print $xParent; ?>">
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Title</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTitle",50,250,@getGENERIC("title",$sRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Short Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xShortDescription",80,250,@getGENERIC("shortDescription",$sRecord),"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Full Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createTextArea("xFullDescription",60,10,@getGENERIC("fullDescription",$sRecord),""); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">META Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMetaDescription",80,250,@getGENERIC("metaDescription",$sRecord),"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">META Keywords</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMetaKeywords",80,250,@getGENERIC("metaKeywords",$sRecord),"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Thumbnail</td>
		<td class="table-list-entry1" valign="top">
			<?php $myForm->createImageEntry("xThumbnail",@$sRecord["thumbnail"],$jssShopImagesFileSystem."sections/thumbnails","opener.jssDetails.document.detailsForm.xThumbnailPick.value"); ?>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Image</td>
		<td class="table-list-entry1" valign="top">
			<?php $myForm->createImageEntry("xImage",@$sRecord["image"],$jssShopImagesFileSystem."sections/normal","opener.jssDetails.document.detailsForm.xImagePick.value"); ?>	
		</td>
	</tr>
<?php
	if (@$sRecord["sectionID"] == 1) {
?>
	<input type="hidden" name="xIsVisible" value="Y">
<?php
	} else {
?>	
	<tr>
		<td class="table-list-title" valign="top">Is Section Visible?</td>
		<td class="table-list-entry1" valign="top"><input type="radio" name="xIsVisible" value="N" <?php print $xIsVisibleNo; ?>> NO&nbsp;&nbsp;&nbsp;<input type="radio" name="xIsVisible" value="Y" <?php print $xIsVisibleYes; ?>> YES</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Available To Customer Types</td>
		<td class="table-list-entry1" valign="top">
			<select name="xAccTypesSelect" class="form-inputbox" size="5" MULTIPLE onChange="recalculateAccTypes();">
<?php
				$selectedAccTypes = split(";",@getGENERIC("accTypes",$sRecord));
				$allSelected = "";
				for ($f = 0; $f < count($selectedAccTypes); $f++) {
					if ($selectedAccTypes[$f] == "0") {
						$allSelected = " SELECTED";
					}
				}
?>
				<option value="0" <?php print $allSelected; ?>>All</option>
<?php
				for ($f = 0; $f < count($accTypes); $f++) {
					$thisSelected = "";
					for ($g = 0; $g < count($selectedAccTypes); $g++) {
						if ($selectedAccTypes[$g] == $accTypes[$f]["accTypeID"]) {
							$thisSelected = "SELECTED";
						}
					}
?>
				<option value="<?php print $accTypes[$f]["accTypeID"]; ?>" <?php print $thisSelected; ?>><?php print $accTypes[$f]["name"]; ?></option>
<?php				
				}
?>
			</select>
			<input type="hidden" name="xAccTypes" value="<?php print @getGENERIC("accTypes",$sRecord); ?>">
			<script language="JavaScript">
				function recalculateAccTypes() {
					accTypes = ";";
					for (f = 0; f < document.detailsForm.xAccTypesSelect.options.length; f++) {
						if (document.detailsForm.xAccTypesSelect.options[f].selected == true) {
							accTypes = accTypes + document.detailsForm.xAccTypesSelect.options[f].value+";";
						}
					}
					document.detailsForm.xAccTypes.value = accTypes;
				}
			</script>
		</td>
	</tr>
<?php
	}
?>	
<?php
	if (@$sRecord["sectionID"] == 1) {
?>
	<input type="hidden" name="xParent" value="0">
<?php
	} else {
?>	
	<tr>
		<td class="table-list-title" valign="top">Parent Section</td>
		<td class="table-list-entry1" valign="top">
		<?php 
			getSectionsList(0,$sectionArray);
		?>
			<select name="xParent" class="form-inputbox">
		<?php
			$thisParent = $xParent;
			if ($xType == "edit") { $thisParent = @$sRecord["parent"]; }
			for ($f = 0; $f < count($sectionArray); $f++) {
				$thisSelected = "";
				if ($thisParent == $sectionArray[$f][0]) {
					$thisSelected = "SELECTED";
				}
				if ($xSectionID != $sectionArray[$f][0]) {
		?>
				<option value="<?php print $sectionArray[$f][0]; ?>" <?php print $thisSelected; ?>><?php print $sectionArray[$f][1]; ?></option>
		<?php
				}
			}
		?>		
		</td>
	</tr>
<?php
	}
?>
	<tr>
		<td class="table-list-title" valign="top">Template For Section Page</td>
		<td class="table-list-entry1" valign="top">
			<select name="xTemplateFile" class="form-inputbox">
<?php
					$myDir = opendir("../templates");
					while (false !== ($file = readdir($myDir))) {
						if (substr($file,strlen($file)-5,5) == ".html" || substr($file,strlen($file)-4,4) == ".htm") {
							if ($file == @$sRecord["templateFile"]) {
								$thisSelected = "SELECTED";
							} else {
								$thisSelected = "";
							}
?>
				<option <?php print $thisSelected; ?>><?php print $file; ?></option>
<?php
						}
					}	
?>				
			</select>		
		</td>
	</tr>

<?php
	for ($f = 0; $f < count($languages); $f++) {
		$thisLanguage = $languages[$f]["languageID"];
		if ($thisLanguage != 1) {
?>
	<tr>
		<td class="table-list-title" valign="top" colspan="2">Language: <?php print $languages[$f]["name"]; ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Title</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTitle".$thisLanguage,50,250,@getGENERIC("title".$thisLanguage,$sRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Short Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xShortDescription".$thisLanguage,80,250,@getGENERIC("shortDescription".$thisLanguage,$sRecord),"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Full Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createTextArea("xFullDescription".$thisLanguage,60,10,@getGENERIC("fullDescription".$thisLanguage,$sRecord),""); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">META Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMetaDescription".$thisLanguage,80,250,@getGENERIC("metaDescription".$thisLanguage,$sRecord),"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">META Keywords</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMetaKeywords".$thisLanguage,80,250,@getGENERIC("metaKeywords".$thisLanguage,$sRecord),"general"); ?></td>
	</tr>
<?php
		}
	}
?>

	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
<?php $myForm->closeForm("xTitle"); ?>
</center>
</BODY>
</HTML>
