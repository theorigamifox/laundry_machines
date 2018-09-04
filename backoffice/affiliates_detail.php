<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$statusArray[] = array("value"=>"N","text"=>"New");
	$statusArray[] = array("value"=>"L","text"=>"Live");
	$statusArray[] = array("value"=>"H","text"=>"On Hold");
	$statusArray[] = array("value"=>"D","text"=>"Declined");
	
	dbConnect($dbA);
	
	$xType=getFORM("xType");
	if ($xType=="new") {
		$pageTitle = "Add New Affiliate";
		$submitButton = "Insert Affiliate";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>".hiddenReturnPOST();

		$xLoginEnabledYes = "CHECKED";
		$xLoginEnabledNo = "";		
	}
	if ($xType=="edit") {
		$xAffiliateID = getFORM("xAffiliateID");
		$pageTitle = "Edit Existing Affiliate";
		$submitButton = "Update Affiliate";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xAffiliateID' value='$xAffiliateID'>".hiddenReturnPOST();
		$uResult = $dbA->query("select * from $tableAffiliates where affiliateID=$xAffiliateID");	
		$uRecord = $dbA->fetch($uResult);
	}
	
	$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='AF' and visible=1 order by position");
	$affGroupArray = $dbA->retrieveAllRecords($tableAffiliatesGroups,"groupID");
	
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
<?php $myForm->createForm("detailsForm","affiliates_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Username</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xAffUsername",15,15,@getGENERIC("username",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Password</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createPassword("xPassword",15,20,"","general"); ?></td>
	</tr>
<?php
	for ($f = 0; $f < count($fieldList); $f++) {
?>

<?php
	if ($fieldList[$f]["fieldtype"] == "TEXT") {
?>
	<tr>
		<td class="table-list-title" valign="top"><?php print $fieldList[$f]["titleText"]; ?></td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText($fieldList[$f]["fieldname"],$fieldList[$f]["size"],$fieldList[$f]["maxlength"],@getGENERIC($fieldList[$f]["fieldname"],$uRecord),"general"); ?></td>
	</tr>
<?php
	}
?>
<?php
	if ($fieldList[$f]["fieldtype"] == "TEXTAREA") {
?>
	<tr>
		<td class="table-list-title" valign="top"><?php print $fieldList[$f]["titleText"]; ?></td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createTextArea($fieldList[$f]["fieldname"],$fieldList[$f]["cols"],$fieldList[$f]["rows"],@getGENERIC($fieldList[$f]["fieldname"],$uRecord),"general"); ?></td>
	</tr>
<?php
	}
?>
<?php
	if ($fieldList[$f]["fieldtype"] == "CHECKBOX") {
		if (@$uRecord[$fieldList[$f]["fieldname"]] != "") {
			$thisChecked = "CHECKED";
		} else {
			$thisChecked = "";
		}
?>
	<tr>
		<td class="table-list-entry1" valign="top" colspan="2"><input type="checkbox" name="<?php print $fieldList[$f]["fieldname"]; ?>" value="Y" <?php print $thisChecked; ?>> <?php print $fieldList[$f]["titleText"]; ?></td>
	</tr>
<?php
	}
?>
<?php
	if ($fieldList[$f]["fieldtype"] == "SELECT" && $fieldList[$f]["fieldname"] != "country") {
?>
	<tr>
		<td class="table-list-title" valign="top"><?php print $fieldList[$f]["titleText"]; ?></td>
		<td class="table-list-entry1" valign="top"><select name="<?php print $fieldList[$f]["fieldname"]; ?>" class="form-inputbox">
		<?php
			$currentValue = @getGENERIC($fieldList[$f]["fieldname"],$uRecord);
			$contentBits = split(";",$fieldList[$f]["contentvalues"]);
			for ($g= 0 ; $g < count($contentBits); $g++) {
				if ($contentBits[$g] != "") {
					if ($currentValue == $contentBits[$g]) {
						$thisSelected = "SELECTED";
					} else {
						$thisSelected = "";
					}
					?> <option <?php print $thisSelected; ?>><?php print $contentBits[$g]; ?></option> <?php
				}
			}
		?>
		</select>
		</td>
	</tr>
<?php
	}
?>
<?php
	if ($fieldList[$f]["fieldtype"] == "SELECT" && $fieldList[$f]["fieldname"] == "country") {
?>
	<tr>
		<td class="table-list-title" valign="top"><?php print $fieldList[$f]["titleText"]; ?></td>
		<td class="table-list-entry1" valign="top"><select name="<?php print $fieldList[$f]["fieldname"]; ?>" class="form-inputbox">
		<?php
			$currentValue = @getGENERIC($fieldList[$f]["fieldname"],$uRecord);
			$result = $dbA->query("select * from $tableCountries where visible='Y' order by name");
			$count = $dbA->count($result);
			for ($g= 0 ; $g < $count; $g++) {
				$record = $dbA->fetch($result);
				if ($currentValue == $record["countryID"]) {
					$thisSelected = "SELECTED";
				} else {
					$thisSelected = "";
				}
				?> <option value="<?php print $record["countryID"]; ?>" <?php print $thisSelected; ?>><?php print $record["name"]; ?></option> <?php
			}
		?>
		</select>
		</td>
	</tr>
<?php
	}
?>

<?php
	}
?>
	<tr>
		<td class="table-list-title" valign="top">Affiliate Group</td>
		<td class="table-list-entry1" valign="top"><select name="xGroupID" class="form-inputbox">
		<?php
			$currentValue = @getGENERIC("groupID",$uRecord);
			for ($g= 0 ; $g < count($affGroupArray); $g++) {
				if ($currentValue == $affGroupArray[$g]["groupID"]) {
					$thisSelected = "SELECTED";
				} else {
					$thisSelected = "";
				}
				?> <option value="<?php print $affGroupArray[$g]["groupID"]; ?>" <?php print $thisSelected; ?>><?php print $affGroupArray[$g]["name"]; ?></option> <?php
			}
		?>
		</select>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Status</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xStatus",@$uRecord["status"],"BOTH",$statusArray); ?></td>
	</tr>	
			<?php 
				if ($uRecord["parentID"] != 0) { 
					$parentID = $uRecord["parentID"];
					$pResult = $dbA->query("select * from $tableAffiliates where affiliateID=$parentID");
					if ($dbA->count($pResult) > 0) {
						$pRecord = $dbA->fetch($pResult);
			?>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right">
				This is a 2nd tier affiliate. Parent affiliate is <a href="affiliates_detail.php?xType=edit&xAffiliateID=<?php print $uRecord["parentID"]; ?>&<?php print userSessionGET(); ?>"><?php print $pRecord["username"]; ?></a>
		</td>
	</tr>
			<?php
					}
				}
			?>
			<?php 
				$pResult = $dbA->query("select affiliateID from $tableAffiliates where parentID=".$uRecord["affiliateID"]);
				if ($dbA->count($pResult) > 0) {
					$pRecord = $dbA->fetch($pResult);
			?>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right">
				This affiliate has <?php print $dbA->count($pResult); ?> 2nd tier affiliates. Please <a href="affiliates_listing.php?xType=2NDTIER&xAffiliateID=<?php print $uRecord["affiliateID"]; ?>&<?php print userSessionGET(); ?>">click here for a list</a>.
		</td>
	</tr>
			<?php
				}
			?>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
<?php $myForm->closeForm("xAffUsername"); ?>
</HTML>
<?php
	$dbA->close();
?>
