<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	
	$myForm = new formElements;
	
	$pageTitle = "Country Level Tax";
	$submitButton = "Update Country Tax Settings";
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
<input type="hidden" name="xAction" value="countries">
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Countries</td>
		<td class="table-list-entry1" valign="top">

<table cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<font class="normaltext"><b>Un-Taxed Countries:</b></font><br>
			<select name="xCountrySelect" size="10" class="form-inputbox" style="width:150px;" MULTIPLE>
<?php
		$result = $dbA->query("select * from $tableCountries where taxstandard = 0 and taxsecond = 0 and visible='Y' order by name");
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
			<font class="normaltext"><b>Taxed Countries:</b></font><br>
			<select name="xSelectedCountries" size="10" class="form-inputbox" style="width:150px;" MULTIPLE>
<?php
		$countryList = "";
		$result = $dbA->query("select * from $tableCountries where taxstandard > 0 or taxsecond > 0 order by name");
		$count = $dbA->count($result);
		for ($f = 0; $f < $count; $f++) {
			$record = $dbA->fetch($result);
			$countryList .= $record["countryID"].";";
?>
			<option value="<?php print $record["countryID"]; ?>"><?php print $record["name"]; ?></option>
<?php		
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
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTaxStandard",8,10,makeDecimal(@$record["taxstandard"]),"decimal"); ?> %</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Second Tax Rate</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTaxSecond",8,10,makeDecimal(@$record["taxsecond"]),"decimal"); ?> %</td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
<input type="hidden" name="xCountryList" value="<?php print @$countryList; ?>">
<input type="hidden" name="xCountryDeletedList" value="">
</form>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>