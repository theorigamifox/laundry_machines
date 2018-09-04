<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	
	$myForm = new formElements;
	
	$pageTitle = "County/State Level Tax";
	$submitButton = "Update County/State Tax Settings";

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
<?php $myForm->createForm("detailsForm","tax_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print hiddenFromPOST(); ?>
<input type="hidden" name="xAction" value="counties">
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Counties/States</td>
		<td class="table-list-entry1" valign="top">
<?php
	if ($countyActive == false) {
?>
<font class="boldtext">To activate counties/states tax you need to select<br>the 'Make County/DeliveryCounty Field A Select Box'<br>
option in Customers -&gt; Settings.
		</td>
	</tr>
<?php
	} else {
?>
<table cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<font class="normaltext"><b>Un-Taxed Counties/States:</b></font><br>
			<select name="xCountySelect" size="10" class="form-inputbox" style="width:150px;" MULTIPLE>
<?php
		$selectedList = split(";",retrieveOption("taxCountiesList"));
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
			<font class="normaltext"><b>Taxed Counties/States:</b></font><br>
			<select name="xSelectedCounties" size="10" class="form-inputbox" style="width:150px;" MULTIPLE>
<?php

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
	
?>
			</select>
		</td>		
	</tr>
</table>

		
		
		</td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Standard Tax Rate</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTaxStandard",8,10,retrieveOption("taxCountiesStandard"),"decimal"); ?> %</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Second Tax Rate</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTaxSecond",8,10,retrieveOption("taxCountiesSecond"),"decimal"); ?> %</td>
	</tr>	
<?php
	}
?>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
<input type="hidden" name="xCountyList" value="<?php print @$newSelectedList; ?>">
</form>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>