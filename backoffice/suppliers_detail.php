<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$statusArray[] = array("value"=>"N","text"=>"New");
	$statusArray[] = array("value"=>"L","text"=>"Live");
	$statusArray[] = array("value"=>"H","text"=>"On Hold");
	$statusArray[] = array("value"=>"D","text"=>"Declined");
	
	dbConnect($dbA);

	$emailTemplates = null;
	$result = $dbA->query("select * from $tableSuppliersEmails order by name");
	$count = $dbA->count($result);
	for ($f = 0; $f < $count; $f++) {
		$record = $dbA->fetch($result);
		$emailTemplates[] = array("value"=>$record["emailID"],"text"=>$record["name"]);
	}
	
	$xType=getFORM("xType");
	if ($xType=="new") {
		$pageTitle = "Add New Supplier";
		$submitButton = "Insert Supplier";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>".hiddenReturnPOST();

		$xLoginEnabledYes = "CHECKED";
		$xLoginEnabledNo = "";		
	}
	if ($xType=="edit") {
		$xSupplierID = getFORM("xSupplierID");
		$pageTitle = "Edit Existing Supplier";
		$submitButton = "Update Supplier";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xSupplierID' value='$xSupplierID'>".hiddenReturnPOST();
		$uResult = $dbA->query("select * from $tableSuppliers where supplierID=$xSupplierID");	
		$uRecord = $dbA->fetch($uResult);
	}
	
	$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='SU' and visible=1 order by position");
	
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
<?php $myForm->createForm("detailsForm","suppliers_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
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
		<td class="table-list-title" valign="top">Send Supplier Emails For Orders</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xEmailSupplier",@$uRecord["emailSupplier"],"YN"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Supplier Email Template</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xEmailID",@$uRecord["emailID"],"BOTH",$emailTemplates); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Email Address For Orders</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xNotifyEmail",50,250,@getGENERIC("notifyEmail",$uRecord),"email"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Account Number With Supplier</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xAccnum",50,50,@getGENERIC("accnum",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>
