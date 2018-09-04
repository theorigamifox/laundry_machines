<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	$xType=getFORM("xType");
	if ($xType=="new") {
		$pageTitle = "Add New Zone";
		$submitButton = "Insert Zone";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>";

		$xLoginEnabledYes = "CHECKED";
		$xLoginEnabledNo = "";		
	}
	$xUseexchangerate = "N";
	if ($xType=="edit") {
		$xZoneID = getFORM("xZoneID");
		$pageTitle = "Edit Existing Zone";
		$submitButton = "Update Zone";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xZoneID' value='$xZoneID'>";
		$uResult = $dbA->query("select * from $tableZones where zoneID=$xZoneID");	
		$uRecord = $dbA->fetch($uResult);
	}
	
	$myForm = new formElements;
	
	$result = $dbA->query("select * from $tableCustomerFields where fieldname='county'");
	$record = $dbA->fetch($result);
	if (retrieveOption("fieldCountyAsSelect") == 0) {
		$countyActive = false;
	} else {
		$countyActive = true;
	}
	$countySplit = split(";",$record["contentvalues"]);
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
</HEAD>
<Script>
	function removeCountries(fromBox,toBox) {
		for (f = 0; f < toBox.length; f++) {
			if (toBox.options[f].selected == true) {
				fromBox.options[fromBox.options.length] = new Option(toBox.options[f].text,toBox.options[f].value);
				document.detailsForm.xCountryDeletedList.value = document.detailsForm.xCountryDeletedList.value + toBox.options[f].value +";";
			}
		}
		for (f = toBox.length-1; f > -1; f--) {
			if (toBox.options[f].selected == true) {
				toBox.options[f] = null;
			}
		}		
		recalcCountries(toBox);
	}	
	
	function addCountries(fromBox,toBox) {
		for (f = 0; f < fromBox.length; f++) {
			if (fromBox.options[f].selected == true) {
				thisOption = fromBox.options[f].text;
				thisValue = fromBox.options[f].value;
				toBox.options[toBox.options.length] = new Option(thisOption,thisValue);
			}
		}
		for (f = fromBox.length-1; f > -1; f--) {
			if (fromBox.options[f].selected == true) {
				fromBox.options[f] = null;
			}
		}		
		recalcCountries(toBox);
	}

	function recalcCountries(theBox) {
		newList = "";
		for (f = 0; f < theBox.length; f++) {
			newList = newList + theBox.options[f].value + ";";
		}
		document.detailsForm.xCountryList.value = newList;
	}  	
</script>
<Script>
	function removeCounties(fromBox,toBox) {
		for (f = 0; f < toBox.length; f++) {
			if (toBox.options[f].selected == true) {
				fromBox.options[fromBox.options.length] = new Option(toBox.options[f].text,"");
			}
		}
		for (f = toBox.length-1; f > -1; f--) {
			if (toBox.options[f].selected == true) {
				toBox.options[f] = null;
			}
		}		
		recalcCounties(toBox);
	}	
	
	function addCounties(fromBox,toBox) {
		for (f = 0; f < fromBox.length; f++) {
			if (fromBox.options[f].selected == true) {
				thisOption = fromBox.options[f].text;
				thisValue = fromBox.options[f].value;
				toBox.options[toBox.options.length] = new Option(thisOption,"");
			}
		}
		for (f = fromBox.length-1; f > -1; f--) {
			if (fromBox.options[f].selected == true) {
				fromBox.options[f] = null;
			}
		}		
		recalcCounties(toBox);
	}

	function recalcCounties(theBox) {
		newList = "";
		for (f = 0; f < theBox.length; f++) {
			newList = newList + theBox.options[f].text + ";";
		}
		if (newList != "") {
			newList = ";"+newList;
		}
		document.detailsForm.xCountyList.value = newList;
	}  	
</script>
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
<?php $myForm->createForm("detailsForm","shipping_zones_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xName",60,250,@getGENERIC("name",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Countries In Zone</td>
		<td class="table-list-entry1" valign="top">

<table cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<font class="normaltext"><b>Available Countries:</b></font><br>
			<select name="xCountrySelect" size="10" class="form-inputbox" style="width:150px;" MULTIPLE>
<?php
		$result = $dbA->query("select * from $tableCountries where zoneID=0 and visible='Y' order by name");
		$count = $dbA->count($result);
		for ($f = 0; $f < $count; $f++) {
			$record = $dbA->fetch($result);
?>
			<option value="<?php print $record["countryID"]; ?>"><?php print $record["name"]; ?></option>
<?php		
		}	
?>
			</select>
		</td>
		<td>
			<center>
			<a href="javascript:addCountries(document.detailsForm.xCountrySelect,document.detailsForm.xSelectedCountries);"><img src="images/select_right.gif" width="15" height="15" alt="Add Countries" border="0"></a>
			<br>
			<a href="javascript:removeCountries(document.detailsForm.xCountrySelect,document.detailsForm.xSelectedCountries);"><img src="images/select_left.gif" width="15" height="15" alt="Remove Countries" border="0"></a>

		</td>		
		<td>
			<font class="normaltext"><b>Selected Countries:</b></font><br>
			<select name="xSelectedCountries" size="10" class="form-inputbox" style="width:150px;" MULTIPLE>
<?php
	if (@$xZoneID > 0) {
		$countryList = "";
		$result = $dbA->query("select * from $tableCountries where zoneID=$xZoneID and visible='Y' order by name");
		$count = $dbA->count($result);
		for ($f = 0; $f < $count; $f++) {
			$record = $dbA->fetch($result);
			$countryList .= $record["countryID"].";";
?>
			<option value="<?php print $record["countryID"]; ?>"><?php print $record["name"]; ?></option>
<?php		
		}	
	}		
?>
			</select>
		</td>		
	</tr>

	
</table>

		
		
		</td>
	</tr>	
	
	
	
	<tr>
		<td class="table-list-title" valign="top">Counties/States In Zone</td>
		<td class="table-list-entry1" valign="top">
<?php
	if ($countyActive == false) {
?>
<font class="boldtext">To activate counties/states shipping you need to select<br>the 'Make County/DeliveryCounty Field A Select Box'<br>
option in Customers -&gt; Settings.
<?php
	} else {
?>
<table cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<font class="normaltext"><b>Available Counties/States:</b></font><br>
			<select name="xCountySelect" size="10" class="form-inputbox" style="width:150px;" MULTIPLE>
<?php
		$selectedList = split(";",@$uRecord["countyList"]);
		for ($f = 0; $f < count($countySplit); $f++) {
			if ($countySplit[$f] != "") {
				$addMe = true;
				for ($g = 0; $g < count($selectedList); $g++) {
					if ($selectedList[$g] == $countySplit[$f]) {
						$addMe = false;
					}
				}
				if ($addMe == true) {
?>
			<option><?php print $countySplit[$f]; ?></option>
<?php		
				}
			}
		}	
?>
			</select>
		</td>
		<td>
			<center>
			<a href="javascript:addCounties(document.detailsForm.xCountySelect,document.detailsForm.xSelectedCounties);"><img src="images/select_right.gif" width="15" height="15" alt="Add Countries" border="0"></a>
			<br>
			<a href="javascript:removeCounties(document.detailsForm.xCountySelect,document.detailsForm.xSelectedCounties);"><img src="images/select_left.gif" width="15" height="15" alt="Remove Countries" border="0"></a>

		</td>		
		<td>
			<font class="normaltext"><b>Selected Counties/States:</b></font><br>
			<select name="xSelectedCounties" size="10" class="form-inputbox" style="width:150px;" MULTIPLE>
<?php
	if (@$xZoneID > 0) {
		$newSelectedList = "";
		for ($f = 0; $f < count($selectedList); $f++) {
			$record = $dbA->fetch($result);
			if ($selectedList[$f] != "") {
				$addMe = false;
				for ($g = 0; $g < count($countySplit); $g++) {
					if ($selectedList[$f] == $countySplit[$g]) {
						$addMe = true;
					}
				}
				if ($addMe == true) {			
					$newSelectedList .= $selectedList[$f].";";
?>
			<option><?php print $selectedList[$f]; ?></option>
<?php		
				}
			}
		}	
	}		
?>
			</select>
		</td>		
	</tr>
</table>

		
<?php
	}
?>		
		</td>
	</tr>		
	
	
	
	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
<input type="hidden" name="xCountryList" value="<?php print @$countryList; ?>">
<input type="hidden" name="xCountryDeletedList" value="">
<input type="hidden" name="xCountyList" value="<?php print @$newSelectedList; ?>">
</form>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>