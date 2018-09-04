<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
			
	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	
	$commTypeArray[] = array("value"=>"P","text"=>"Percent");
	$commTypeArray[] = array("value"=>"F","text"=>"Flat Rate (".$currArray[0]["code"].")");
	
	
	
	$xType=getFORM("xType");
	if ($xType=="new") {
		$pageTitle = "Add New Banner";
		$submitButton = "Insert Banner";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>";

		$xLoginEnabledYes = "CHECKED";
		$xLoginEnabledNo = "";
		$uRecord["groups"] = 0;		
	}
	if ($xType=="edit") {
		$xBannerID = getFORM("xBannerID");
		$pageTitle = "Edit Existing Banner";
		$submitButton = "Update Banner";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xBannerID' value='$xBannerID'>";
		$uResult = $dbA->query("select * from $tableAffiliatesBanners where bannerID=$xBannerID");	
		$uRecord = $dbA->fetch($uResult);
	}
	
	$myForm = new formElements;
	
	$affiliateGroups = $dbA->retrieveAllRecords($tableAffiliatesGroups,"groupID");

	$dbA->close();
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
<?php $myForm->createForm("detailsForm","affiliates_banners_process.php","","multipart"); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xName",65,250,@getGENERIC("name",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Banner Image</td>
		<td class="table-list-entry1" valign="top">
			<?php $myForm->createImageEntry("xFilename",@$uRecord["filename"],$jssShopImagesFileSystem."banners","opener.jssDetails.document.detailsForm.xFilenamePick.value"); ?>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Image Size</td>
		<td class="table-list-entry1" valign="top">Width: <?php $myForm->createText("xWidth",5,5,@getGENERIC("width",$uRecord),"integer"); ?>&nbsp;&nbsp;Height: <?php $myForm->createText("xHeight",5,5,@getGENERIC("height",$uRecord),"integer"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createTextArea("xDescription",45,5,@getGENERIC("description",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Available To<br>Affiliate Groups</td>
		<td class="table-list-entry1" valign="top">
			<select name="xGroupsSelect" class="form-inputbox" size="5" MULTIPLE onChange="recalculateGroups();">
<?php
				$selectedGroups = split(";",@getGENERIC("groups",$uRecord));
				$allSelected = "";
				for ($f = 0; $f < count($selectedGroups); $f++) {
					if ($selectedGroups[$f] == "0") {
						$allSelected = " SELECTED";
					}
				}
?>
				<option value="0" <?php print $allSelected; ?>>All</option>
<?php
				for ($f = 0; $f < count($affiliateGroups); $f++) {
					$thisSelected = "";
					for ($g = 0; $g < count($selectedGroups); $g++) {
						if ($selectedGroups[$g] == $affiliateGroups[$f]["groupID"]) {
							$thisSelected = "SELECTED";
						}
					}
?>
				<option value="<?php print $affiliateGroups[$f]["groupID"]; ?>" <?php print $thisSelected; ?>><?php print $affiliateGroups[$f]["name"]; ?></option>
<?php				
				}
?>
			</select>
			<input type="hidden" name="xGroups" value="<?php print @getGENERIC("groups",$uRecord); ?>">
			<script language="JavaScript">
				function recalculateGroups() {
					groups = ";";
					for (f = 0; f < document.detailsForm.xGroupsSelect.options.length; f++) {
						if (document.detailsForm.xGroupsSelect.options[f].selected == true) {
							groups = groups + document.detailsForm.xGroupsSelect.options[f].value+";";
						}
					}
					document.detailsForm.xGroups.value = groups;
				}
			</script>
		</td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
<?php $myForm->closeForm("xName"); ?>
</BODY>
</HTML>
