<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	$xType=getFORM("xType");
	if ($xType=="new") {
		$pageTitle = "Add New Payment Option";
		$submitButton = "Insert Payment Option";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>";

		$xLoginEnabledYes = "CHECKED";
		$xLoginEnabledNo = "";	
		$uRecord["type"] = "OFFLINE";
		$uRecord["enabled"] = "Y";
	}
	$xUseexchangerate = "N";
	if ($xType=="edit") {
		$xPaymentID = getFORM("xPaymentID");
		$pageTitle = "Edit Existing Payment Option";
		$submitButton = "Update Payment Option";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xPaymentID' value='$xPaymentID'>";
		$uResult = $dbA->query("select * from $tablePaymentOptions where paymentID=$xPaymentID");	
		$uRecord = $dbA->fetch($uResult);
	}
	
	$myForm = new formElements;
	
	$accTypeArray = $dbA->retrieveAllRecords($tableCustomersAccTypes,"accTypeID");
	$gatewayArray = $dbA->retrieveAllRecords($tableCCProcessing,"name");
	$languages = $dbA->retrieveAllRecords($tableLanguages,"languageID");
	
	$comfArray[] = array("value"=>"0","text"=>"No customer confirmation email");
	$comfArray[] = array("value"=>"1","text"=>"When order is placed");
	$comfArray[] = array("value"=>"2","text"=>"When order is marked as paid");
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
<?php $myForm->createForm("detailsForm","payment_options_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xName",30,100,@getGENERIC("name",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Type</td>
		<td class="table-list-entry1" valign="top"><?php print @$uRecord["type"]; ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Enabled</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xEnabled",@getGENERIC("enabled",$uRecord),"YN"); ?></td>
	</tr>		
	<tr>
		<td class="table-list-title" valign="top">Account Types</td>
		<td class="table-list-entry1" valign="top">
			<select name="xAccTypeSelect" class="form-inputbox" size="5" MULTIPLE onChange="recalcAcc();">
<?php
				$currentValue = @getGENERIC("accTypes",$uRecord);
				$accSplit = split(";",$currentValue);
					$thisSelected = "";
					for ($h = 0; $h < count($accSplit); $h++) {
						if ($accSplit[$h] == "0") {
							$thisSelected = "SELECTED";
						}
					}
?>			
			<option value="0" <?php print $thisSelected; ?>>All</option>
			<?php
				for ($g= 0 ; $g < count($accTypeArray); $g++) {
					$thisSelected = "";
					for ($h = 0; $h < count($accSplit); $h++) {
						if ($accSplit[$h] == $accTypeArray[$g]["accTypeID"]) {
							$thisSelected = "SELECTED";
						}
					}
					?> <option value="<?php print $accTypeArray[$g]["accTypeID"]; ?>" <?php print $thisSelected; ?>><?php print $accTypeArray[$g]["name"]; ?></option> <?php
				}
			?>
			</select><input type="hidden" name="xAccTypes" value="<?php print @$uRecord["accTypes"]; ?>">
			<script>
				function recalcAcc() {
					newList = ";";
					for (f = 0; f < document.detailsForm.xAccTypeSelect.options.length; f++) {
						if (document.detailsForm.xAccTypeSelect.options[f].selected == true) {
							newList = newList + document.detailsForm.xAccTypeSelect.options[f].value+";";
						}
					}
					document.detailsForm.xAccTypes.value = newList;
				}
			</script>
		</td>
	</tr>		
<?php
	if ($uRecord["type"]=="CC") {
?>
	<tr>
		<td class="table-list-title" valign="top">Route Through Gateway</td>
		<td class="table-list-entry1" valign="top">
			<select name="xGateway" class="form-inputbox">
			<?php
				$currentValue = @getGENERIC("gateway",$uRecord);
				for ($g= 0 ; $g < count($gatewayArray); $g++) {
					if ($uRecord["gateway"] == $gatewayArray[$g]["gateway"]) {
						$thisSelected = "SELECTED";
					} else {
						$thisSelected = "";
					}
					?> <option value="<?php print $gatewayArray[$g]["gateway"]; ?>" <?php print $thisSelected; ?>><?php print $gatewayArray[$g]["name"]; ?></option> <?php
				}
			?>
			</select>		
		</td>
	</tr>	
<?php
	}
?>	
	<tr>
		<td class="table-list-title" valign="top">Customer Email Confirmation</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xCustConfirmation",@$uRecord["custConfirmation"],"BOTH",$comfArray); ?></td>
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
		<td class="table-list-title" valign="top">Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xName".$thisLanguage,30,100,@getGENERIC("name".$thisLanguage,$uRecord),"general"); ?></td>
	</tr>	
<?php
		}
	}
?>		
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