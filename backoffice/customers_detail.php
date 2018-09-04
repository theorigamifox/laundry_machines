<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	
	$xType=getFORM("xType");
	if ($xType=="new") {
		$pageTitle = "Add New Customer";
		$submitButton = "Insert Customer";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>".hiddenReturnPOST();

		$xLoginEnabledYes = "CHECKED";
		$xLoginEnabledNo = "";		
	}
	if ($xType=="edit") {
		$xCustomerID = getFORM("xCustomerID");
		$pageTitle = "Edit Existing Customer";
		$submitButton = "Update Customer";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xCustomerID' value='$xCustomerID'>".hiddenReturnPOST();
		$uResult = $dbA->query("select * from $tableCustomers where customerID=$xCustomerID");	
		$uRecord = $dbA->fetch($uResult);
	}
	
	$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='C' and visible=1 order by position");
	$accTypeArray = $dbA->retrieveAllRecords($tableCustomersAccTypes,"accTypeID");
	
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
<?php $myForm->createForm("detailsForm","customers_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Email Address</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xEmail",40,250,@getGENERIC("email",$uRecord),"email"); ?></td>
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
		<td class="table-list-title" valign="top">Account Type</td>
		<td class="table-list-entry1" valign="top"><select name="xAccTypeID" class="form-inputbox">
		<?php
			$currentValue = @getGENERIC("accTypeID",$uRecord);
			for ($g= 0 ; $g < count($accTypeArray); $g++) {
				if ($currentValue == $accTypeArray[$g]["accTypeID"]) {
					$thisSelected = "SELECTED";
				} else {
					$thisSelected = "";
				}
				?> <option value="<?php print $accTypeArray[$g]["accTypeID"]; ?>" <?php print $thisSelected; ?>><?php print $accTypeArray[$g]["name"]; ?></option> <?php
			}
		?>
		</select>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Tax Exempt</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xTaxExempt",@getGENERIC("taxExempt",$uRecord),"YN"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
<?php $myForm->closeForm("xEmail"); ?>
</HTML>
<?php
	$dbA->close();
?>
