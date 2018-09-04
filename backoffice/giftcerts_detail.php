<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	
	$xType=getFORM("xType");
	if ($xType=="new") {
		$pageTitle = "Add New Gift Certificate";
		$submitButton = "Insert Gift Certificate";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>".hiddenReturnPOST();

		$xLoginEnabledYes = "CHECKED";
		$xLoginEnabledNo = "";	
		$uRecord["expiryDate"] = "N";	
	}
	if ($xType=="edit") {
		$xCertSerial = getFORM("xCertSerial");
		$pageTitle = "Edit Existing Gift Certificate";
		$submitButton = "Update Gift Certificate";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xCertSerial' value='$xCertSerial'>".hiddenReturnPOST();
		$uResult = $dbA->query("select * from $tableGiftCertificates where certSerial='$xCertSerial'");	
		$uRecord = $dbA->fetch($uResult);
	}
	
	$myForm = new formElements;

	$statusArray[] = array("value"=>"A","text"=>"Activated");
	$statusArray[] = array("value"=>"N","text"=>"Not Activated");	
	
	$currArray = $dbA->retrieveAllRecordsFromQuery("select * from $tableCurrencies order by currencyID");
	for ($f = 0; $f < count($currArray); $f++) {
		if ($currArray[$f]["checkout"] == "Y") {
			$currGoodArray[] = array("value"=>$currArray[$f]["currencyID"],"text"=>$currArray[$f]["name"]);
		}
		if ($currArray[$f]["currencyID"] == @$uRecord["currencyID"]) {
			$selectedCurrency = $currArray[$f]["name"];
		}
	}
	
	if ($uRecord["expiryDate"] == "N") {
		$theExpiry = "N";
		$uRecord["expiryDate"] = date("Ymd");
	} else {
		$theExpiry = "Y";
	}
	$expiryArray[] = array("value"=>"N","text"=>"No");
	$expiryArray[] = array("value"=>"Y","text"=>"Yes On Date -&gt;");	

	$typeArray[] = array("value"=>"E","text"=>"Email");
	$typeArray[] = array("value"=>"P","text"=>"Postal");
	
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
		if (document.detailsForm.xGType.options[document.detailsForm.xGType.selectedIndex].value == "E" && document.detailsForm.xEmailaddress.value == "") {
			rc=alert("You have selected an email gift certificate but haven't entered an email address");
			return false;
		}
		if (document.detailsForm.xGType.options[document.detailsForm.xGType.selectedIndex].value == "P" && document.detailsForm.deliveryAddress1.value == "") {
			rc=alert("You have selected a postal gift certificate but haven't entered a delivery address");
			return false;
		}
		if (document.detailsForm.xAmount.value == "") {
			rc=alert("Please enter an amount above 0");
			return false;
		}
		return true;
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
<?php $myForm->createForm("detailsForm","giftcerts_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
<?php
	if ($xType=="edit") {
?>
	<tr>
		<td class="table-list-title" valign="top">Certificate Serial</td>
		<td class="table-list-entry1" valign="top"><?php print $xCertSerial; ?></td>
	</tr>
<?php
	}
?>
	<tr>
		<td class="table-list-title" valign="top">Status</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xStatus",@$uRecord["status"],"BOTH",$statusArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">From Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xFromname",40,250,@$uRecord["fromname"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">To Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xToname",40,250,@$uRecord["toname"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Message</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createTextArea("xMessage",40,6,@$uRecord["message"],""); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Amount</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xAmount",8,10,@$uRecord["certValue"],"decimal"); ?>
		<?php
			if ($xType == "new") {
		?>
		&nbsp;<?php $myForm->createSelect("xCurrencyID",@$uRecord["currencyID"],"BOTH",$currGoodArray); ?>
		<?php
			} else {
		?>
		&nbsp;<?php print @$selectedCurrency; ?>
		<?php
			}
		?>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Expires</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xExpires",@$theExpiry,"BOTH",$expiryArray); ?>
		&nbsp;
		<select name="xDay" class="form-inputbox">
		<?php
			$tDay = substr(@$uRecord["expiryDate"],6,2);
			for ($f = 1; $f <= 31; $f++) {
				if ($f == $tDay) {
					$selected = "SELECTED";
				} else {
					$selected = "";
				}
				if ($f < 10) {
					$fshow = "0".$f;
				} else {
					$fshow = $f;
				}
		?>
			<option <?php print $selected; ?>><?php print $fshow; ?></option>
		<?php
			}
		?>
		</select>&nbsp;<select name="xMonth" class="form-inputbox">
		<?php
			$tMonth = substr(@$uRecord["expiryDate"],4,2);
			for ($f = 1; $f <= 12; $f++) {
				if ($f == $tMonth) {
					$selected = "SELECTED";
				} else {
					$selected = "";
				}
				if ($f < 10) { $padder = "0"; } else { $padder = ""; }
		?>
			<option <?php print $selected; ?>><?php print $padder.$f; ?></option>
		<?php
			}
		?>
		</select>&nbsp;<select name="xYear" class="form-inputbox">
		<?php
			$thisYear = substr(@$uRecord["expiryDate"],0,4);
			for ($f = 2003; $f <= date("Y")+1; $f++) {
				if ($f == $thisYear) {
					$selected = "SELECTED";
				} else {
					$selected = "";
				}
		?>
		<option <?php print $selected; ?>><?php print $f; ?></option>
		<?php
			}
		?>
		</select>
		
		</td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Type</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xGType",@$uRecord["type"],"BOTH",$typeArray); ?></td>
	</tr>

	<tr>
		<td class="table-list-title" valign="top" colspan="2">Delivery Details</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Email Address</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xEmailaddress",40,250,@$uRecord["emailaddress"],"email"); ?></td>
	</tr>
	<tr>
		<td class="table-list-entry1" valign="top" colspan="2"><center>or</center></td>
	</tr>	
	
<?php
	$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='D' order by position");
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
	if ($fieldList[$f]["fieldtype"] == "SELECT" && $fieldList[$f]["fieldname"] != "deliveryCountry") {
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
	if ($fieldList[$f]["fieldtype"] == "SELECT" && $fieldList[$f]["fieldname"] == "deliveryCountry") {
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
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
<?php
	if ($xType == "edit") {
?>
	<tr>
		<td class="table-list-entry0" valign="top" colspan="2">Use History</td>
	</tr>
	<tr>
		<td class="table-list-entry1" valign="top" colspan="2">
<?php
	$tResult = $dbA->query("select * from $tableGiftCertificatesTrans where certSerial = '$xCertSerial' order by orderID");
	if ($dbA->count($tResult) > 0) {
		$tCount = $dbA->count($tResult);
		$total = 0;
		for ($f = 0; $f < $tCount; $f++) {
			$tRecord=$dbA->fetch($tResult);
			echo "Order Number: ";
			echo retrieveOption("orderNumberOffset")+$tRecord["orderID"];
			echo "&nbsp;&nbsp;&nbsp;Amount Used: ".priceFormat($tRecord["amount"],$uRecord["currencyID"])."<BR>";
			$total = $total + $tRecord["amount"];
		}
		echo "<br>Total Amount Used: ".priceFormat($total,$uRecord["currencyID"]);
		echo "<br>Total Value Left: ".priceFormat($uRecord["certValue"]-$total,$uRecord["currencyID"]);
		
	} else {
		echo "This gift certificate has not been used yet.";
	}
?>
		</td>
	</tr>
<?php
	}
?>
</table>
</form>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>
