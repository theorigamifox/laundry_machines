<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$xType = getFORM("xType");
	
	if ($xType == "") {
		$xType = "images";
	}
	
	$delimiterArray[] = array("value"=>"tab","name"=>"Tab");
	$delimiterArray[] = array("value"=>"comma","name"=>"Comma");
	$delimiterArray[] = array("value"=>"semicolon","name"=>"SemiColon");
	$delimiterArray[] = array("value"=>"pipe","name"=>"Pipe");
	
	dbConnect($dbA);
	
	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	$languages = $dbA->retrieveAllRecords($tableLanguages,"languageID");
	$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"position,name");	
	
	switch ($xType) {
		case "qtydiscounts":
			$fieldList[] = "SKIP_FIELD";
			$fieldList[] = "productID";
			$fieldList[] = "code";
			$fieldList[] = "accTypeID";
			$fieldList[] = "QtyFrom";
			$fieldList[] = "QtyTo";
			$fieldList[] = "Percentage";
			for ($f = 0; $f < count($currArray); $f++) {
				if ($currArray[$f]["useexchangerate"] == "N") {
					$fieldList[] = "discount".$currArray[$f]["code"];
				}
			}
			$theProcessor = "import_process.php";
			$boxTitle = "Update Qty Discounts";
			break;
		case "basepricing":
			$fieldList[] = "SKIP_FIELD";
			$fieldList[] = "productID";
			$fieldList[] = "code";
			$fieldList[] = "accTypeID";
			$fieldList[] = "QtyFrom";
			$fieldList[] = "QtyTo";
			$fieldList[] = "Percentage";
			for ($f = 0; $f < count($currArray); $f++) {
				if ($currArray[$f]["useexchangerate"] == "N") {
					$fieldList[] = "discount".$currArray[$f]["code"];
				}
			}
			if (is_array($extraFieldsArray)) {
				for ($z = 0; $z < count($extraFieldsArray); $z++) {
					switch ($extraFieldsArray[$z]["type"]) {
						case "SELECT":
						case "CHECKBOXES":
						case "RADIOBUTTONS":
							$fieldList[] = $extraFieldsArray[$z]["name"];
							break;
						default:
							break;
					}
				}		
			}
			$theProcessor = "import_process.php";
			$boxTitle = "Update Base Pricing";
			break;
		case "attributes":
			$fieldList[] = "SKIP_FIELD";
			$fieldList[] = "productID";
			$fieldList[] = "code";
			$fieldList[] = "type";
			$fieldList[] = "content";
			$fieldList[] = "exclude";
			if (is_array($extraFieldsArray)) {
				for ($z = 0; $z < count($extraFieldsArray); $z++) {
					switch ($extraFieldsArray[$z]["type"]) {
						case "SELECT":
						case "CHECKBOXES":
						case "RADIOBUTTONS":
							$fieldList[] = $extraFieldsArray[$z]["name"];
							break;
						default:
							break;
					}
				}		
			}
			$theProcessor = "import_process.php";
			$boxTitle = "Update Attribute Combinations";
			break;
		case "customers":
			$fieldList[] = "SKIP_FIELD";
			$fieldList[] = "customerID";
			$fieldList[] = "Email";
			$fieldList[] = "Password";
			$fieldList[] = "Account Type ID";
			$fieldList[] = "Tax Exempt";
			$accFields = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='C' and visible=1 order by position");
			for ($f = 0; $f < count($accFields); $f++) {
				$fieldList[] = $accFields[$f]["fieldname"];
			}
			$theProcessor = "import_process.php";
			$boxTitle = "Import Customers";
			break;
		case "mailinglist":
			$fieldList[] = "SKIP_FIELD";
			$fieldList[] = "recipientID";
			$fieldList[] = "emailaddress";
			$theProcessor = "import_process.php";
			$boxTitle = "Import Mailing List";
			break;
		case "images":
			$fieldList[] = "SKIP_FIELD";
			$fieldList[] = "productID";
			$fieldList[] = "code";
			$fieldList[] = "thumbnail";
			$fieldList[] = "mainimage";
			$theProcessor = "import_process.php";
			$boxTitle = "Update Product Images";
			break;			
		case "stocklevels":
			$fieldList[] = "SKIP_FIELD";
			$fieldList[] = "productID";
			$fieldList[] = "code";
			$fieldList[] = "scLevel";
			$fieldList[] = "scWarningLevel";
			$fieldList[] = "scEnabled";
			$theProcessor = "import_process.php";
			$boxTitle = "Update Stock Levels";
			break;		
		case "prices":
			$fieldList[] = "SKIP_FIELD";
			$fieldList[] = "productID";
			$fieldList[] = "code";
			for ($f = 0; $f < count($currArray); $f++) {
				if ($currArray[$f]["useexchangerate"] == "N") {
					$fieldList[] = "price".$currArray[$f]["code"];
				}
			}
			$theProcessor = "import_process.php";
			$boxTitle = "Update Main Prices";
			break;
		case "products":
			$fieldList[] = "SKIP_FIELD";
			$fieldList[] = "productID";
			$fieldList[] = "code";
			$fieldList[] = "name";
			$fieldList[] = "shortdescription";
			$fieldList[] = "description";
			for ($z = 0; $z < count($languages); $z++) {
				if ($languages[$z]["languageID"] != 1) {
					$fieldList[] = "name_".$languages[$z]["name"];
					$fieldList[] = "shortdescription_".$languages[$z]["name"];
					$fieldList[] = "description_".$languages[$z]["name"];
				}
			}			
			$fieldList[] = "thumbnail";
			$fieldList[] = "mainimage";
			$fieldList[] = "visible";
			$fieldList[] = "metaDescription";
			$fieldList[] = "metaKeywords";
			$fieldList[] = "keywords";
			for ($f = 0; $f < count($currArray); $f++) {
				if ($currArray[$f]["useexchangerate"] == "N") {
					$fieldList[] = "price".$currArray[$f]["code"];
				}
			}
			for ($f = 0; $f < count($currArray); $f++) {
				if ($currArray[$f]["useexchangerate"] == "N") {
					$fieldList[] = "normalPrice".$currArray[$f]["code"];
				}
			}
			for ($f = 0; $f < count($currArray); $f++) {
				if ($currArray[$f]["useexchangerate"] == "N") {
					$fieldList[] = "oneOffPrice".$currArray[$f]["code"];
				}
			}
			$fieldList[] = "templateFile";
			$fieldList[] = "sectionIDs";
			$fieldList[] = "sectionNames";
			$fieldList[] = "newproduct";
			$fieldList[] = "topproduct";
			$fieldList[] = "specialoffer";
			$fieldList[] = "freeShipping";
			$fieldList[] = "productType";
			$fieldList[] = "weight";
			$fieldList[] = "taxrate";
			$fieldList[] = "scLevel";
			$fieldList[] = "scWarningLevel";
			$fieldList[] = "scEnabled";
			$fieldList[] = "scActionZero";
			if (is_array($extraFieldsArray)) {
				for ($z = 0; $z < count($extraFieldsArray); $z++) {
					switch ($extraFieldsArray[$z]["type"]) {
						case "SELECT":
						case "CHECKBOXES":
						case "RADIOBUTTONS":
						default:
							$fieldList[] = $extraFieldsArray[$z]["name"];
							for ($y = 0; $y < count($languages); $y++) {
								if ($languages[$y]["languageID"] != 1) {
									$fieldList[] = $extraFieldsArray[$z]["name"]."_".$languages[$y]["name"];
								}
							}
							if ($extraFieldsArray[$z]["type"] == "SELECT" || $extraFieldsArray[$z]["type"] == "CHECKBOXES" || $extraFieldsArray[$z]["type"] == "RADIOBUTTONS") {
								for ($f = 0; $f < count($currArray); $f++) {
									if ($currArray[$f]["useexchangerate"] == "N") {
										$fieldList[] = $extraFieldsArray[$z]["name"]."_price".$currArray[$f]["code"];
									}
								}
							}
					}
				}		
			}
			$theProcessor = "import_process.php";
			$boxTitle = "Import New Products";
			break;			
		case "updateproducts":
			$fieldList[] = "SKIP_FIELD";
			$fieldList[] = "productID";
			$fieldList[] = "code";
			$fieldList[] = "name";
			$fieldList[] = "shortdescription";
			$fieldList[] = "description";
			for ($z = 0; $z < count($languages); $z++) {
				if ($languages[$z]["languageID"] != 1) {
					$fieldList[] = "name_".$languages[$z]["name"];
					$fieldList[] = "shortdescription_".$languages[$z]["name"];
					$fieldList[] = "description_".$languages[$z]["name"];
				}
			}			
			$fieldList[] = "thumbnail";
			$fieldList[] = "mainimage";
			$fieldList[] = "visible";
			$fieldList[] = "metaDescription";
			$fieldList[] = "metaKeywords";
			$fieldList[] = "keywords";
			for ($f = 0; $f < count($currArray); $f++) {
				if ($currArray[$f]["useexchangerate"] == "N") {
					$fieldList[] = "price".$currArray[$f]["code"];
				}
			}
			$fieldList[] = "templateFile";
			$fieldList[] = "sectionIDs";
			$fieldList[] = "sectionNames";
			$fieldList[] = "newproduct";
			$fieldList[] = "topproduct";
			$fieldList[] = "specialoffer";
			$fieldList[] = "freeShipping";
			$fieldList[] = "productType";
			$fieldList[] = "weight";
			$fieldList[] = "taxrate";
			$fieldList[] = "scLevel";
			$fieldList[] = "scWarningLevel";
			$fieldList[] = "scEnabled";
			$fieldList[] = "scActionZero";
			if (is_array($extraFieldsArray)) {
				for ($z = 0; $z < count($extraFieldsArray); $z++) {
					switch ($extraFieldsArray[$z]["type"]) {
						case "SELECT":
						case "CHECKBOXES":
						case "RADIOBUTTONS":
						default:
							$fieldList[] = $extraFieldsArray[$z]["name"];
							for ($y = 0; $y < count($languages); $y++) {
								if ($languages[$y]["languageID"] != 1) {
									$fieldList[] = $extraFieldsArray[$z]["name"]."_".$languages[$y]["name"];
								}
							}
							if ($extraFieldsArray[$z]["type"] == "SELECT" || $extraFieldsArray[$z]["type"] == "CHECKBOXES" || $extraFieldsArray[$z]["type"] == "RADIOBUTTONS") {
								for ($f = 0; $f < count($currArray); $f++) {
									if ($currArray[$f]["useexchangerate"] == "N") {
										$fieldList[] = $extraFieldsArray[$z]["name"]."_price".$currArray[$f]["code"];
									}
								}
							}
					}
				}		
			}
			$theProcessor = "import_process.php";
			$boxTitle = "Update Existing Products";
			break;														
	}	
	
	$xCmd = getFORM("xCmd");
	$xTransferID = getFORM("xTransferID");
	if ($xCmd == "saveas") {
		$xName = getFORM("xName");
		$xFieldList = getFORM("xFieldList");
		$xTextQualifier = getFORM("xTextQualifier");
		$xDelimiter = getFORM("xDelimiter");
		$xFirstRowHeadings = getFORM("xFirstRowHeadings");
		$result = $dbA->query("insert into $tableDataTransfers (mode,type,name,fieldOrder,firstRowHeadings,delimiter,textQualifier) VALUES(\"I\",\"$xType\",\"$xName\",\"$xFieldList\",\"$xFirstRowHeadings\",\"$xDelimiter\",\"$xTextQualifier\")");
		$xTransferID = $dbA->lastID();
	}
	if ($xCmd == "save") {
		$xFieldList = getFORM("xFieldList");
		$xTextQualifier = getFORM("xTextQualifier");
		$xDelimiter = getFORM("xDelimiter");
		$xFirstRowHeadings = getFORM("xFirstRowHeadings");		
		$result = $dbA->query("update $tableDataTransfers set fieldOrder=\"$xFieldList\",firstRowHeadings=\"$xFirstRowHeadings\",delimiter=\"$xDelimiter\",textQualifier=\"$xTextQualifier\" where transferID=$xTransferID");
	}
	if ($xCmd == "delete") {
		$result = $dbA->query("delete from $tableDataTransfers where transferID=$xTransferID");
		$xTransferID = "";
	}

	$myForm = new formElements;
	
	if ($xTransferID == "") { $xTransferID = 0; }
	
	if ($xTransferID > 0) {
		$result = $dbA->query("select * from $tableDataTransfers where transferID=$xTransferID");
		$transRecord = $dbA->fetch($result);
	} else {
		$transRecord["fieldOrder"] = "";
	}
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript">
	function moveField(theBox,direction) {
		currentSelected = theBox.selectedIndex;
		totalItems = theBox.length;
		if (currentSelected == -1) {
			rc=alert("Please select a field to move first.");
		} else {
			nogo = false;
			if (direction=="up" && currentSelected == 0) { nogo = true; }
			if (direction=="down" && currentSelected == totalItems-1) { nogo=true; }
			if (nogo == false) {
				if (direction=="up") {
					changeWith = currentSelected - 1;
				}
				if (direction=="down") {
					changeWith = currentSelected + 1;
				}
				optionValue = theBox.options[changeWith].value;
				optionText = theBox.options[changeWith].text;
				theBox.options[changeWith].value = theBox.options[currentSelected].value;
				theBox.options[changeWith].text = theBox.options[currentSelected].text;
				theBox.options[currentSelected].value = optionValue;
				theBox.options[currentSelected].text = optionText;
				theBox.selectedIndex = changeWith;
			}
		}
		recalcFields(theBox);
	}
	
	function deleteField(thisBox) {
		theSelected = thisBox.selectedIndex;
		if (theSelected == -1) {
			rc=alert("Please select a field to delete first.");
		} else {
			theID = thisBox.options[theSelected].value;
			thisBox.options[theSelected] = null;
			recalcFields(thisBox);
		}
	}	
	
	function addFields(fromBox,toBox) {
		for (f = 0; f < fromBox.length; f++) {
			if (fromBox.options[f].selected == true) {
				thisOption = fromBox.options[f].text;
				toBox.options[toBox.options.length] = new Option(thisOption,thisOption);
			}
		}
		recalcFields(toBox);
	}

	function recalcFields(theBox) {
		newList = "";
		for (f = 0; f < theBox.length; f++) {
			newList = newList + theBox.options[f].text + ";";
		}
		document.detailsForm.xFieldList.value = newList;
	}  	
	
	function loadExport() {
		theTransferID = document.detailsForm2.xLayoutLoad.options[document.detailsForm2.xLayoutLoad.selectedIndex].value;
		if (confirm("Are you sure you wish to load this layout?")) {
			self.location.href="import.php?xType=<?php print $xType; ?>&xCmd=load&xTransferID="+theTransferID+"&<?php print userSessionGET(); ?>";
		}
	}
	
	function deleteExport() {
		theTransferID = document.detailsForm2.xLayoutLoad.options[document.detailsForm2.xLayoutLoad.selectedIndex].value;
		if (confirm("Are you sure you wish to delete this layout?")) {
			self.location.href="import.php?xType=<?php print $xType; ?>&xCmd=delete&xTransferID="+theTransferID+"&<?php print userSessionGET(); ?>";
		}
	}
	
	function saveExport() {
		theTransferID = <?php print $xTransferID; ?>;
		if (document.detailsForm.xTextQualifier[0].checked == true) {
			theQualifier = "N";
		} else {
			theQualifier = "Y";
		}
		if (document.detailsForm.xFirstRowHeadings[0].checked == true) {
			theHeadings = "N";
		} else {
			theHeadings = "Y";
		}
		theFieldList = document.detailsForm.xFieldList.value;
		theDelimiter = document.detailsForm.xDelimiter.options[document.detailsForm.xDelimiter.selectedIndex].value;
		if (confirm("Are you sure you wish to save this layout?")) {
			self.location.href="import.php?xType=<?php print $xType; ?>&xCmd=save&xTransferID="+theTransferID+"&xFieldList="+theFieldList+"&xFirstRowHeadings="+theHeadings+"&xDelimiter="+theDelimiter+"&xTextQualifier="+theQualifier+"&<?php print userSessionGET(); ?>";
		}
	}
	
	function saveAsExport() {
		theName = document.detailsForm2.xSaveAsName.value;
		if (theName == "") {
			rc=alert("Please enter a name to describe this import layout.");
		} else {
			if (document.detailsForm.xTextQualifier[0].checked == true) {
				theQualifier = "N";
			} else {
				theQualifier = "Y";
			}
			if (document.detailsForm.xFirstRowHeadings[0].checked == true) {
				theHeadings = "N";
			} else {
				theHeadings = "Y";
			}
			theFieldList = document.detailsForm.xFieldList.value;
			theDelimiter = document.detailsForm.xDelimiter.options[document.detailsForm.xDelimiter.selectedIndex].value;
			if (confirm("Are you sure you wish to save this layout?")) {
				self.location.href="import.php?xType=<?php print $xType; ?>&xCmd=saveas&xName="+theName+"&xFieldList="+theFieldList+"&xFirstRowHeadings="+theHeadings+"&xDelimiter="+theDelimiter+"&xTextQualifier="+theQualifier+"&<?php print userSessionGET(); ?>";
			}
		}
	}
	
	function checkFields() {
		return true;
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Import: <?php print $boxTitle; ?></td>
	</tr>
</table>
<p>
<form name="detailsForm2" onSubmit="return false;">
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" colspan="1"><font class="boldtext"><?php print $boxTitle; ?> Stored Import Layouts</font></td>
	</tr>
	<tr>
		<td class="table-list-entry1" colspan="1"><font class="boldtext"><select name="xLayoutLoad" class="form-inputbox">
<?php
		$result = $dbA->query("select * from $tableDataTransfers where mode='I' and type='$xType' order by name");
		$count = $dbA->count($result);
		for ($f = 0; $f < $count; $f++) {
			$dtRecord = $dbA->fetch($result);
			if ($xTransferID == $dtRecord["transferID"]) {
				$thisSelected = "SELECTED";
			} else {
				$thisSelected = "";
			}
?>
			<option value="<?php print $dtRecord["transferID"]; ?>" <?php print $thisSelected; ?>><?php print $dtRecord["name"]; ?></option>
<?php
		}
?>
		</select>
		&nbsp;<button name="buttonLoadExport" class="button-expand" onClick="loadExport();">Load</button>&nbsp;<button name="buttonDeleteExport" class="button-expand" onClick="deleteExport();">Delete</button></font></td>
	</tr>
	<tr>
		<td class="table-list-entry1" colspan="1"><?php if ($xTransferID > 0) { ?><button name="buttonSaveExport" class="button-expand" onClick="saveExport();">Save</button><font class="boldtext"> or <?php } ?><?php $myForm->createText("xSaveAsName",40,100,"","general"); ?>&nbsp;
		&nbsp;<button name="buttonSaveAsExport" class="button-expand" onClick="saveAsExport();">Save As</button></font></td>
	</tr>	
<?php
	if ($xTransferID != 0) {
?>
	<tr>
		<td class="table-list-title" colspan="1"><font class="boldtext">Loaded Layout: <?php print $transRecord["name"]; ?></font></td>
	</tr>
<?php
	}
?>	
</table>
</form>
<p>
<?php $myForm->createForm("detailsForm",$theProcessor,"","multipart"); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xFieldList" value="<?php print @$transRecord["fieldOrder"]; ?>">
<input type="hidden" name="xAction" value="import">
<input type="hidden" name="xType" value="<?php print $xType; ?>">
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" colspan="4"><font class="boldtext"><?php print $boxTitle; ?> Fields</font>
		</td>
	</tr>
	<tr>
		<td class="table-list-entry1">
			<font class="normaltext"><b>Available Fields:</b></font><br>
			<select name="xFieldSelect" size="20" class="form-inputbox" style="width:200px;" MULTIPLE>
<?php
	for ($f = 0; $f < count($fieldList); $f++) {
?>
	<option value="<?php print $fieldList[$f]; ?>"><?php print $fieldList[$f]; ?></option>
<?php
	}
?>
			</select>
		</td>
		<td class="table-list-entry1">
			<center>
			<a href="javascript:addFields(document.detailsForm.xFieldSelect,document.detailsForm.xSelectedFields);"><img src="images/select_right.gif" width="15" height="15" alt="Add Field" border="0"></a>
		</td>		
		<td class="table-list-entry1">
			<font class="normaltext"><b>Import File Fields:</b></font><br>
			<select name="xSelectedFields" size="20" class="form-inputbox" style="width:200px;">
<?php
	if ($xTransferID > 0) {
		$fieldList = split(";",$transRecord["fieldOrder"]);
		for ($f = 0; $f < count($fieldList); $f++) {
			if ($fieldList[$f] != "") {
?>
			<option value="<?php print $fieldList[$f]; ?>"><?php print $fieldList[$f]; ?></option>
<?php		
			}
		}	
	}		
?>
			</select>
		</td>		
	<td class="table-list-entry1">
		<center>
		<a href="javascript:moveField(document.detailsForm.xSelectedFields,'up');"><img src="images/select_up.gif" border="0" width="15" height="15"><br>
		<img src="images/spacer.gif" border="0" width="15" height="3"><br>
		<a href="javascript:moveField(document.detailsForm.xSelectedFields,'down');"><img src="images/select_down.gif" border="0" width="15" height="15"><br>
		<img src="images/spacer.gif" border="0" width="15" height="3"><br>
		<a href="javascript:deleteField(document.detailsForm.xSelectedFields);"><img src="images/select_delete.gif" border="0" width="15" height="15">
		</center>
	</td>
	</tr>
	<tr>
		<td class="table-list-title" colspan="4"><font class="boldtext">Additional Import Information</td>
	</tr>	
	<tr>
		<td colspan="4" class="table-list-entry1">
			<center>
			<table cellpadding="2" cellspacing="0" class="table-list">
				<tr>
					<td class="table-list-title"><font class="boldtext">First row contains column headings</font></td>
					<td class="table-list-entry1"><font class="normaltext"><?php $myForm->createYesNo("xFirstRowHeadings",@getGENERIC("firstRowHeadings",$transRecord),"YN"); ?></font></td>
				</tr>
				<tr>
					<td class="table-list-title"><font class="boldtext">Field Delimiter (Seperator)</font></td>
					<td class="table-list-entry1"><font class="normaltext"><select name="xDelimiter" class="form-inputbox">
<?php
	for ($f = 0; $f < count($delimiterArray); $f++) {
		if ($delimiterArray[$f]["value"] == @$transRecord["delimiter"]) {
			$thisSelected = "SELECTED";
		} else {
			$thisSelected = "";
		}
?>
	<option value="<?php print $delimiterArray[$f]["value"]; ?>" <?php print $thisSelected; ?>><?php print $delimiterArray[$f]["name"]; ?></option>
<?php
	}
?>					
					</select></font></td>
				</tr>
				<tr>
					<td class="table-list-title"><font class="boldtext">Text Qualified by Quote Marks</font></td>
					<td class="table-list-entry1"><font class="normaltext"><?php $myForm->createYesNo("xTextQualifier",@getGENERIC("textQualifier",$transRecord),"YN"); ?></font></td>
				</tr>				
			</table>
			</center>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" colspan="4"><font class="boldtext">File To Import</td>
	</tr>
	<tr>
		<td class="table-list-title" colspan="1">Upload From Computer</td>
		<td colspan="3" class="table-list-entry1">
			<center>
			<input type="file" name="xFile" size="30" class="form-inputbox" accept="" onKeyPress="return false;" onKeyDown="return false;">
			</center>
		</td>
	</tr>	
	<?php
		$myPath = realpath("index.php");
		$myPath = str_replace("index.php","",$myPath);
		$myPath = str_replace("\\","/",$myPath);
	?>
	<tr>
		<td class="table-list-title" colspan="1"><font color="#ff0000">or</font> Load From Server</td>
		<td colspan="3" class="table-list-entry1">
			<center>
			<input type="text" name="xFileLocal" value="<?php print $myPath; ?>" size="45" class="form-inputbox" onFocusIn="this.style.borderColor='#FF0000'" onFocusOut="this.style.borderColor='#000000'">
			</center>
		</td>
	</tr>
<?php
	if ($xType=="products") {
?>
	<tr>
		<td class="table-list-title" colspan="4"><font class="boldtext">Insert Method</td>
	</tr>
	<tr>
		<td colspan="4" class="table-list-entry1">
			<center>
			<select name="xInsertMethod" class="form-inputbox">
				<option value="I">Straight Insert</option>
				<option value="R">Replace Insert</option>
				<option value="D">Drop All Products Then Insert</option>
			</select>
			</center>
		</td>
	</tr>
<?php
	}
?>	
	<tr>
		<td colspan="4" class="table-list-title" align="right"><?php $myForm->createSubmit("submit","Import Now"); ?></td>
	</tr>
	<?php $myForm->closeForm("xFieldSelect"); ?>	
</table>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>