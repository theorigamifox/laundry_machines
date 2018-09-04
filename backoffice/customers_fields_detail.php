<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include($jssShopFileSystem."resources/includes/validations.php");
	
	$validateArray[] = array("value"=>"NOTBLANK","text"=>"Not Blank");
	
	$validateArray[] = array("value"=>"CUSTOM","text"=>"Custom Regex:");
	
	foreach ($regexValidate as $key => $value) {
		$validateArray[] = array("value"=>$key,"text"=>$key);
	}
	
	$xCmd=getFORM("xCmd");
	$xType=getFORM("xType");
	
	dbConnect($dbA);
	
	if ($xCmd=="new") {
		$pageTitle = "Add New Field";
		$submitButton = "Insert Field";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>";

		$xLoginEnabledYes = "CHECKED";
		$xLoginEnabledNo = "";
		$xFieldType = getFORM("xFieldType");
		$uRecord["deletable"] = 1;	
		$uRecord["visible"] = 1;	
	}
	$xUseexchangerate = "N";
	if ($xCmd=="edit") {
		$xFieldID = getFORM("xFieldID");
		$pageTitle = "Edit Existing Field";
		$submitButton = "Update Field";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xFieldID' value='$xFieldID'>";
		$uResult = $dbA->query("select * from $tableCustomerFields where fieldID=$xFieldID");	
		$uRecord = $dbA->fetch($uResult);
		$xFieldType = $uRecord["fieldtype"];
		$xName = $uRecord["fieldname"];
	}

	$languages = $dbA->retrieveAllRecords($tableLanguages,"languageID");

	$myForm = new formElements;

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
<?php $myForm->createForm("detailsForm","customers_fields_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<input type="hidden" name="xType" value="<?php print $xType; ?>">
<input type="hidden" name="xFieldType" value="<?php print $xFieldType; ?>">
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Type</td>
		<td class="table-list-entry1" valign="top"><?php print $xFieldType; ?></td>
	</tr>	
<?php
	if ($xCmd == "new") {
?>
	<tr>
		<td class="table-list-title" valign="top">Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xFieldName",25,40,@getGENERIC("fieldname",$uRecord),"alpha-numeric"); ?></td>
	</tr>	
<?php
	} else {
?>
	<tr>
		<td class="table-list-title" valign="top">Name</td>
		<td class="table-list-entry1" valign="top"><?php print $xName; ?><input type="hidden" name="xFieldName" value="<?php print $xName; ?>"></td>
	</tr>
<?php
	}
?>
	<tr>
		<td class="table-list-title" valign="top">Title</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTitleText",50,250,@getGENERIC("titleText",$uRecord),"general"); ?></td>
	</tr>
<?php
	if ($xFieldType=="TEXT") {
?>
	<tr>
		<td class="table-list-title" valign="top">Size</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xSize",5,10,makeInteger(@getGENERIC("size",$uRecord)),"integer"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Maximum Length</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMaxLength",5,10,makeInteger(@getGENERIC("maxlength",$uRecord)),"integer"); ?></td>
	</tr>		
<?php
	}
?>
<?php
	if ($xFieldType=="TEXTAREA") {
?>
	<tr>
		<td class="table-list-title" valign="top">Columns</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCols",5,10,makeInteger(@getGENERIC("cols",$uRecord)),"integer"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Rows</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xRows",5,10,makeInteger(@getGENERIC("rows",$uRecord)),"integer"); ?></td>
	</tr>	
<?php
	}
?>
<?php
	if ($xFieldType=="SELECT" && (@$xName != "country" && @$xName != "deliveryCountry")) {
?>
	<tr>
		<td class="table-list-title" valign="top">Content</td>
		<td class="table-list-entry1" valign="top">
			<table cellpadding="2" cellspacing="0" border="0">
				<tr>
					<td>
						<select name="xSelect" class="form-inputbox" style="width: 300px" size="10" onChange="optionShowItem();">
						<?php
							$splitBits = split(";",@getGENERIC("contentvalues",$uRecord));
							for ($f = 0; $f < count($splitBits); $f++) {
								if ($splitBits[$f] != "") {
						?>
									<option><?php print $splitBits[$f]; ?></option>
						<?php
								}
							}
						?>
						</select>
					</td>
					<td valign="top">
						<center>
						<a href="javascript:optionMoveItem('up');"><img src="images/select_up.gif" border="0" width="15" height="15"><br>
						<img src="images/spacer.gif" border="0" width="15" height="3"><br>
						<a href="javascript:optionMoveItem('down');"><img src="images/select_down.gif" border="0" width="15" height="15"><br>
						<img src="images/spacer.gif" border="0" width="15" height="3"><br>
						<a href="javascript:optionDeleteItem('xSelect');"><img src="images/select_delete.gif" border="0" width="15" height="15">
						</center>
					</td>
				</tr>
			</table>

<script>
						function optionAddItem(addMode) {
							if (document.detailsForm.xOption.value == "") {
								alert("Options cannot be blank");
								return false;
							}
							if (addMode == 1) {
								if (document.detailsForm.xSelect.selectedIndex == -1) {
									alert("Please select an option to update first");
									return false;
								}
							}
							if (addMode == 1) {
								document.detailsForm.xSelect.options[document.detailsForm.xSelect.selectedIndex].text = document.detailsForm.xOption.value;
							} else {
								document.detailsForm.xSelect.options[document.detailsForm.xSelect.options.length] = new Option(document.detailsForm.xOption.value,"");
							}
							document.detailsForm.xSelect.selectedIndex = -1;
							document.detailsForm.xOption.value = "";
							document.detailsForm.xOption.focus();
							optionRecalculate();
						}
						
						function optionRecalculate() {
							sectionList = "";
							for (f = 0; f < document.detailsForm.xSelect.options.length; f++) {
								thisOption = document.detailsForm.xSelect.options[f].text;
								sectionList = sectionList + thisOption+";";
							}
							document.detailsForm.xContentValues.value = sectionList;
						}
						
						function optionDeleteItem() {
							eval("thisBox = document.detailsForm.xSelect");
							theSelected = thisBox.selectedIndex;
							if (theSelected == -1) {
								rc=alert("Please select a product to delete first.");
							} else {
								theID = thisBox.options[theSelected].value;
								thisBox.options[theSelected] = null;
								optionRecalculate();
								document.detailsForm.xOption.value = "";
							}
						}
						
						function optionMoveItem(direction) {
							eval("thisBox = document.detailsForm.xSelect");
							currentSelected = thisBox.selectedIndex;
							totalItems = thisBox.length;
							if (currentSelected == -1) {
								rc=alert("Please select an option to move first.");
							} else {
								$doMove = true;
								if (direction=="up" && currentSelected == 0) { $doMove =  false; }
								if (direction=="down" && currentSelected == totalItems-1) { $doMove =  false; }
								if ($doMove) {
									if (direction=="up") {
										changeWith = currentSelected - 1;
									}
									if (direction=="down") {
										changeWith = currentSelected + 1;
									}
									optionValue = thisBox.options[changeWith].value;
									optionText = thisBox.options[changeWith].text;
									thisBox.options[changeWith].value = thisBox.options[currentSelected].value;
									thisBox.options[changeWith].text = thisBox.options[currentSelected].text;
									thisBox.options[currentSelected].value = optionValue;
									thisBox.options[currentSelected].text = optionText;
									thisBox.selectedIndex = changeWith;
									optionRecalculate();
								}
							}
						}
						
						function optionShowItem() {
							if (document.detailsForm.xSelect.selectedIndex != -1) {
								document.detailsForm.xOption.value = document.detailsForm.xSelect.options[document.detailsForm.xSelect.selectedIndex].text;
								document.detailsForm.xOption.focus();
							}
						}

						function captureExtraReturn(obj,e) {
							if (e.keyCode==13) {
								e.keyCode = 0;
								optionAddItem(0);
								return false;
							} else {
								if (e.type!="keypress") { return true; }
								if (isDoubleQuote(e.keyCode) || isSemiColon(e.keyCode)) {
									e.keyCode = 0;
									return false;
								} else {
									return true;
								}
							}
							return true;
						}						
</script>
		
		<font class="boldtext">Option:&nbsp;</font><?php $myForm->createText("xOption",30,250,"","captureExtraReturn(this,event)"); ?>
		&nbsp;<input type="button" onClick="optionAddItem(0);" name="optionAddItems" class="button-grey" value="Add">
		&nbsp;<input type="button" onClick="optionAddItem(1);" name="optionApplyItems" class="button-grey" value="Apply">
		<input type="hidden" name="xContentValues" value="<?php print @getGENERIC("contentvalues",$uRecord); ?>">
		<?php if (@$xName == "county" || @$xName == "deliveryCounty") { ?>
			<br><font class="boldtext">NOTE: As your county/state field is a select box, updating these<br>
			values will update the options available in both the customer's<br>county/state field and
			the delivery address county/state field.</font>
		<?php } ?>
		</td>
	</tr>
<?php
	}
?>
	<tr>
		<td class="table-list-title" valign="top">Validation</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xValidation",@getGENERIC("validation",$uRecord),"01"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Validate For</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xValidationType",@$uRecord["validationType"],"BOTH",$validateArray); ?>&nbsp;<?php $myForm->createText("xRegex",50,1000,@getGENERIC("regex",$uRecord),""); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Validation Message</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xValidationMessage",50,250,@getGENERIC("validationmessage",$uRecord),"general"); ?></td>
	</tr>	
<?php
	if ($uRecord["deletable"]==1 && ($xType == "C" || $xType == "D")) {
?>
	<tr>
		<td class="table-list-title" valign="top">Include When Ordering</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xIncOrdering",@getGENERIC("incOrdering",$uRecord),"01"); ?></td>
	</tr>
<?php
	}
?>		
<?php
	if ($uRecord["deletable"]==1 && ($xType == "C" || $xType == "O")) {
?>
	<tr>
		<td class="table-list-title" valign="top">Internal Only</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xInternalOnly",@getGENERIC("internalOnly",$uRecord),"01"); ?></td>
	</tr>	
<?php
	}
?>		
<?php
	if ($uRecord["deletable"]==1 || substr($uRecord["fieldname"],0,4) == "aff_" || $uRecord["fieldname"] == "company" || ($xType=="CC" && ($uRecord["fieldname"]=="ccCVV" || $uRecord["fieldname"]=="ccIssue"))) {
?>
	<tr>
		<td class="table-list-title" valign="top">Visible</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xVisible",@getGENERIC("visible",$uRecord),"01"); ?></td>
	</tr>
<?php
	}
?>		
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
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTitleText".$thisLanguage,50,250,@getGENERIC("titleText".$thisLanguage,$uRecord),""); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Validation Message</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xValidationMessage".$thisLanguage,50,250,@getGENERIC("validationmessage".$thisLanguage,$uRecord),""); ?></td>
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
