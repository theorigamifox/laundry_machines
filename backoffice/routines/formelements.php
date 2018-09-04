<?php
	class formElements {

		var $boxNormal;
		var $boxHighlight;
		var $inputClass;
		var $buttonClass;
		var $formName;
		var $checkString;

		function formElements() {
			$this->boxNormal = "#000000";
			$this->boxHighlight = "#FF0000";
			$this->inputClass = "form-inputbox";
			$this->buttonClass = "button-expand";
			$this->checkString = "";
			$this->formName = "";
		}
		
		function setButtonClass($fClass) {
			$this->buttonClass=$fClass;
		}

		function createText($fName,$fSize,$fMaxLength,$fValue,$fValidation,$fDisabled=0,$fOnChange="",$fExtra="") {
			global $xBrowserShort;
			$fScript = "";
			switch ($fValidation) {
				case "alpha-numeric":
					$fValidation = " onKeyPress=\"validateAlphaNumeric(this,event);\"";
					$fScript = "document.getElementById('$fName').onkeypress = valAlphaNumeric;";
					break;
				case "general":
					$fValidation = " onKeyPress=\"validateGeneral(this,event);\"";
					$fScript = "document.getElementById('$fName').onkeypress = valGeneral;";
					break;
				case "email":
					$fValidation = " onKeyPress=\"validateEmail(this,event);\"";
					$fScript = "document.getElementById('$fName').onkeypress = valEmail;";
					break;
				case "url":
					$fValidation = " onKeyPress=\"validateURL(this,event);\"";
					$fScript = "document.getElementById('$fName').onkeypress = valURL;";
					break;
				case "decimal":
					$fValidation = " onKeyPress=\"validateDecimal(event);\"";
					$fScript = "document.getElementById('$fName').onkeypress = valDecimal;";
					break;
				case "integer":
					$fValidation = " onKeyPress=\"validateNumeric(this,event);\"";
					$fScript = "document.getElementById('$fName').onkeypress = valNumeric;";
					break;
				case "noreturn":
					//$fValidation = " onKeyPress=\"captureExtraReturn(this,event,".$extraFieldsArray[$f]["extraFieldID"].");\"";
					//$fScript = "document.getElementById('$fName').onkeypress = valNoReturn;";
					break;
				default:
					$fScript = "document.getElementById('$fName').onkeypress = $fValidation";
					$fValidation = " onKeyPress=\"$fValidation\"";
			}
			if ($fDisabled == 1) {
				$addDisabled = " DISABLED onKeyPress=\"return false;\" onKeyDown=\"return false;\"";
			} else {
				$addDisabled = "";
			}
			if ($xBrowserShort == "Firefox") {
				$fValidation = "";
			} else {
				$fScript = "";
			}
			if ($fOnChange != "") {
				$fOnChange = "onChange=\"$fOnChange\"";
			}
			$thisBox = "<input type=\"text\" alt=\"$fExtra\" id=\"$fName\" name=\"$fName\" size=\"$fSize\" maxlength=\"$fMaxLength\" value=\"$fValue\" class=\"".$this->inputClass."\" onFocus=\"this.style.borderColor='".$this->boxHighlight."';\" onBlur=\"this.style.borderColor='".$this->boxNormal."';\" $fValidation $addDisabled $fOnChange><script>$fScript</script>";
			echo $thisBox;
			return TRUE;
		}

		function createPassword($fName,$fSize,$fMaxLength,$fValue,$fValidation) {
			if ($fValidation == "alpha-numeric") {
				$fValidation = " onKeyPress=\"validateAlphaNumeric(this,event);\"";
			}
			$thisBox = "<input type=\"password\" name=\"$fName\" size=\"$fSize\" 
maxlength=\"$fMaxLength\" value=\"$fValue\" class=\"".$this->inputClass."\" 
onFocus=\"this.style.borderColor='".$this->boxHighlight."'\" 
onBlur=\"this.style.borderColor='".$this->boxNormal."'\" $fValidation>
";
			echo $thisBox;
			return TRUE;
		}

		function createTextArea($fName,$fCols,$fRows,$fValue,$fValidation,$fExtra="") {
?>
	<textarea name="<?php print $fName; ?>" cols="<?php print $fCols; ?>" rows="<?php print $fRows; ?>" wrap="VIRTUAL" class="<?php print $this->inputClass; ?>" onFocus="this.style.borderColor='<?php print $this->boxHighlight; ?>'" onBlur="this.style.borderColor='<?php print $this->boxNormal; ?>'" <?php print $fExtra; ?>><?php print $fValue; ?></textarea>
<?php
			return TRUE;
		}		

		function createNavBarButton($bName,$bText,$bOnClick,$bClass="button-navbar") {
			if ($bClass == "disabled") {
				$bClass="button-navbar";
				$disabled = " DISABLED";
			} else {
				$disabled = "";
			}
			$thisButton = "<input type=\"button\" id=\"$bName\" class=\"$bClass\" onClick=\"$bOnClick\" $disabled value=\"$bText\">";
			echo $thisButton;
			return TRUE;
		}

		function createExpandButton($bName,$bText,$bOnClick) {
			$thisButton = "<input type=\"button\" id=\"$bName\" class=\"button-expand\" onClick=\"$bOnClick\" value=\"$bText\">";
			echo $thisButton;
			return TRUE;
		}		

		function createMenuButton($bName,$bText,$bOnClick) {
			$thisButton = "<input type=\"button\" id=\"$bName\" class=\"".$this->buttonClass."\" onClick=\"$bOnClick\" value=\"$bText\">";
			echo $thisButton;
			return TRUE;
		}

		function createSubmit($bName,$bValue) {
			$thisButton = "<input type=\"submit\" name=\"$bName\" value=\"$bValue\" class=\"button-save\">";
			echo $thisButton;
			return TRUE;
		}
		
		function createYesNo($fieldName,$fieldValue,$fType,$fText = array("NO","YES")) {
			if ($fType == "YN") {
				$fieldYes = "Y";
				$fieldNo = "N";
			} else {
				$fieldYes = "1";
				$fieldNo = "0";
			}
			if ($fieldValue == $fieldYes) {
				$yesChecked = "CHECKED";
				$noChecked = "";
			} else {
				$yesChecked = "";
				$noChecked = "CHECKED";
			}
			$thisElement = "<input type=\"radio\" name=\"$fieldName\" value=\"$fieldNo\" $noChecked> ".$fText[0]."&nbsp;&nbsp;&nbsp;<input type=\"radio\" name=\"$fieldName\" value=\"$fieldYes\" $yesChecked> ".$fText[1]."";
			echo $thisElement;
			return TRUE;
		}		


		function createSelect($fieldName,$fieldValue,$fType,$fOptions,$fOther="") {
			$optionList = "";
			if (is_array($fOptions)) {
				for ($f = 0; $f < count($fOptions); $f++ ) {
					if ($fType="BOTH") {
						if ($fieldValue == $fOptions[$f]["value"]) {
							$thisSelected = "SELECTED";
						} else {
							$thisSelected = "";
						}
						$optionList .= "<option value=\"".$fOptions[$f]["value"]."\" $thisSelected>".$fOptions[$f]["text"]."</option>";
					}
					if ($fType != "BOTH") {
						if ($fieldValue == $fOptions[$f]["text"]) {
							$thisSelected = "SELECTED";
						} else {
							$thisSelected = "";
						}
						$optionList .= "<option $thisSelected>".$fOptions[$f]["text"]."</option>";
					}
				}
			}
			$thisElement = "<select name=\"$fieldName\" class=\"form-inputbox\">$optionList</select>";
			echo $thisElement;
			return TRUE;
		}			

		function createBack() {
			$thisButton = "<input type=\"button\" name=\"goBack\" value=\"&lt; Back\" class=\"button-expand\" onClick=\"self.history.go(-1);\">";
			echo $thisButton;
			return TRUE;
		}
		

		function createForm($fName,$fAction,$fMethod,$fType = "") {
			$this->formName = $fName;
			if ($fMethod == "") { $fMethod = "POST"; }
			if ($fType == "multipart") { $fType = "enctype=\"multipart/form-data\""; }
			$thisForm = "<form name=\"$fName\" action=\"$fAction\" method=\"$fMethod\" 
onSubmit=\"return checkFields();\" $fType>\n";
			echo $thisForm;
			return TRUE;
		}
		
		function closeForm($fDefault = "") {
			if ($fDefault != "") {
				$fDefault = "</form><script language=\"JavaScript\">document.".$this->formName.".$fDefault.focus();</script>";
			} else {
				$fDefault = "</form>";
			}
			echo $fDefault;
			return true;
		}

		function addCheck($fForm,$fName,$fShow,$fType) {
			$this->formName = $fForm;
			$myCheck = "";
			if ($fType == "!empty") {
				$myCheck  = " if (document.".$fForm.".$fName.value == \"\") {\n";
				$myCheck .= "  formErrors = formErrors + \" - Please enter your $fShow\\n\";\n";
				$myCheck .= " }\n";
			}
			$this->checkString .= $myCheck;
			return TRUE;
		}

		function createCheck() {
			$fullString = "";
			$fullString .= "<script language=\"JavaScript\">\n";
			$fullString .= "function checkFields() {\n";
			$fullString .= " formErrors = \"\";\n";
			$fullString .= $this->checkString;
			$fullString .= " if (formErrors != \"\") {\n";
			$fullString .= "  rc=alert(\"The following problems exist with the information you have entered:\\n\\n\"+formErrors);\n";
			$fullString .= "  return false;\n";
			$fullString .= " } else {\n";
			$fullString .= "  document.".$this->formName.".submit.disabled = true;\n";
			$fullString .= "  return true;\n";
			$fullString .= " }\n";
			$fullString .= "}\n";
			$fullString .= "</script>\n";

			echo $fullString;
			$this->checkString = "";
			return TRUE;
		}
		
		function createHidden($hArray) {
			for ($f = 0; $f < count($hArray); $f++) {
				echo "<input type=hidden name='".$hArray[$f]["name"]."' value='".$hArray[$f]["value"]."'>";	
			}
		}
		
		function createImageEntry($theFieldName,$currentImage,$imagePath,$returnFunction) {
?>
			<table cellpadding="1" cellspacing="0" border="0">
			<tr>
				<td valign="top">
					<?php
						if ($currentImage != "") {
							if (substr($currentImage,0,7) == "http://" || substr($currentImage,0,8) == "https://") {
					?>
						<img src="<?php print $currentImage; ?>" border="1" width="50" height="50" alt="Current: <?php print $currentImage; ?>">
					<?php
							} else {
					?>
						<img src="../<?php print $currentImage; ?>" border="1" width="50" height="50" alt="Current: <?php print $currentImage; ?>">
					<?php
							}
						}
					?>
				</td>
				<td valign="top">
					<table cellspacing="2" cellpadding="0" border="0">
						<tr>
							<td><font class="boldtext">Upload:</font></td>
							<td><input type="file" name="<?php print $theFieldName; ?>" size="35" class="form-inputbox" accept="image/jpeg,image/gif" onFocus="this.style.borderColor='<?php print $this->boxHighlight; ?>';" onBlur="this.style.borderColor='<?php print $this->boxNormal; ?>'" onKeyPress="return false;" onKeyDown="return false;"></td>
							<td><font class="normaltext"><?php if ($currentImage != "") { ?><input type="checkbox" name="<?php print $theFieldName; ?>ImgClear" value="clearimage"> Remove Image<?php } else { echo"&nbsp;"; } ?></font></td>
						</tr>
						<tr>
							<td><font class="boldtext">Pick or URL:</font></td>
							<td><input type="text" name="<?php print $theFieldName; ?>Pick" size="35" maxlength="250" value="" class="form-inputbox" onFocus="this.style.borderColor='<?php print $this->boxHighlight; ?>';" onBlur="this.style.borderColor='<?php print $this->boxNormal; ?>'" onKeyPress="validateGeneral(this,event);">
								<input type="button" id="buttonPick<?php print $theFieldName; ?>" class="button-expand" onClick="parent.showImagePicker('<?php print $imagePath; ?>','<?php print $returnFunction; ?>');" value="Pick Image...">
							</td>
							<td>&nbsp;</td>
						</tr>
					</table>
				</td>
			</tr>
			</table>

<?php		
		}	
		
		function createDigitalEntry($theFieldName,$currentFile,$filePath,$returnFunction) {
			global $xType;
?>
			<table cellspacing="2" cellpadding="0" border="0">
				<tr>
					<td valign="top">
						<?php
							if ($currentFile != "") {
						?>
							<font class="boldtext">Current File:</font> <font class="normaltext"><?php print $currentFile; ?></font>
						<?php
							}
						?>
					</td>
				</tr>			
				<tr>
					<td><font class="boldtext">Upload:</font></td>
					<td><input type="file" name="<?php print $theFieldName; ?>" size="35" class="form-inputbox" onFocus="this.style.borderColor='<?php print $this->boxHighlight; ?>';" onBlur="this.style.borderColor='<?php print $this->boxNormal; ?>'" onKeyPress="return false;" onKeyDown="return false;"></td>
				</tr>
				<tr>
					<td><font class="boldtext">Pick File:</font></td>
					<td><input type="text" name="<?php print $theFieldName; ?>Pick" size="35" maxlength="250" value="<?php print $currentFile; ?>" class="form-inputbox" onFocus="this.style.borderColor='<?php print $this->boxHighlight; ?>';" onBlur="this.style.borderColor='<?php print $this->boxNormal; ?>'" onKeyPress="validateGeneral(this,event);">
						<input type="button" id="buttonPick<?php print $theFieldName; ?>" class="button-expand" onClick="parent.showDigitalPicker('<?php print $filePath; ?>','<?php print $returnFunction; ?>');" value="Pick File...">
					</td>
				</tr>
				<tr>
					<td valign="top" colspan="2">
						<font class="boldtext">NOTE:</font> <font class="normaltext">PHP has a maximum file upload limit that will be set by your ISP.<br>Alternatively upload via. FTP and use 'Pick File...'<br></font>
					</td>
				</tr>					
			</table>

<?php		
		}			
		
		function createPricingFields($currArray,$sRecord,$recordFieldPrefix,$outputFieldPrefix,$breakAfterEach = 0) {
?>		
			<table cellpadding="0" cellspacing="0" border="0">
<?php
			$thisCol = 1;
			for ($f = 0; $f< count($currArray); $f++) {
				if ($thisCol == 1 || $breakAfterEach == 1) {
					print "<tr>";
				}
				if ($currArray[$f]["useexchangerate"] == "Y") {
					$addDisabled = 1;
				} else {
					$addDisabled = 0;
				}
				if ($f == 0) {
					$recalcBit = "recalculateCurrencies_$recordFieldPrefix();";
				} else {
					$recalcBit = "";
				}
?>
		<td><font class="normaltext">
		<?php print $currArray[$f]["code"]; ?><?php //$this->createText($outputFieldPrefix.$currArray[$f]["currencyID"],8,15,number_format(@getGENERIC($recordFieldPrefix.$currArray[$f]["currencyID"],$sRecord),$currArray[$f]["decimals"],".",""),"decimal",$addDisabled,$recalcBit); 
		$this->createText($outputFieldPrefix.$currArray[$f]["currencyID"],8,15,number_format(@getGENERIC($recordFieldPrefix.$currArray[$f]["currencyID"],$sRecord),4,".",""),"decimal",$addDisabled,$recalcBit); 
		?>
		&nbsp;&nbsp;</font>
		</td>
<?php
				if ($thisCol == 4 || $breakAfterEach == 1) {
					print "</tr>";
					$thisCol = 0;
				}
				$thisCol++;
			}
?>
			</table>		
<?php
		}

		function createPricingFieldsDecs6($currArray,$sRecord,$recordFieldPrefix,$outputFieldPrefix,$breakAfterEach = 0) {
?>		
			<table cellpadding="0" cellspacing="0" border="0">
<?php
			$thisCol = 1;
			for ($f = 0; $f< count($currArray); $f++) {
				if ($thisCol == 1 || $breakAfterEach == 1) {
					print "<tr>";
				}
				if ($currArray[$f]["useexchangerate"] == "Y") {
					$addDisabled = 1;
				} else {
					$addDisabled = 0;
				}
				if ($f == 0) {
					$recalcBit = "recalculateCurrencies_$recordFieldPrefix();";
				} else {
					$recalcBit = "";
				}
?>
		<td><font class="normaltext">
		<?php print $currArray[$f]["code"]; ?><?php $this->createText($outputFieldPrefix.$currArray[$f]["currencyID"],8,15,number_format(@getGENERIC($recordFieldPrefix.$currArray[$f]["currencyID"],$sRecord),6,".",""),"decimal",$addDisabled,$recalcBit); ?>
		&nbsp;&nbsp;</font>
		</td>
<?php
				if ($thisCol == 4 || $breakAfterEach == 1) {
					print "</tr>";
					$thisCol = 0;
				}
				$thisCol++;
			}
?>
			</table>		
<?php
		}
		
		function createCurrencyRecalculate($currArray,$recordFieldPrefix,$outputFieldPrefix) {
?>		
			function recalculateCurrencies_<?php print $recordFieldPrefix; ?>() {
				theValue = document.detailsForm.<?php print $outputFieldPrefix; ?>1.value;
<?php
			for ($f = 0; $f< count($currArray); $f++) {
				if ($currArray[$f]["useexchangerate"] == "Y") {
?>
				document.detailsForm.<?php print $outputFieldPrefix.$currArray[$f]["currencyID"]; ?>.value = presentValue(theValue * <?php print $currArray[$f]["exchangerate"]; ?>,4,"",".","");
<?php
				}
			}
?>
			} //end of this function
<?php
			return "recalculateCurrencies_$recordFieldPrefix();";
		}		

		function createCurrencyRecalculateDecs6($currArray,$recordFieldPrefix,$outputFieldPrefix) {
?>		
			function recalculateCurrencies_<?php print $recordFieldPrefix; ?>() {
				theValue = document.detailsForm.<?php print $outputFieldPrefix; ?>1.value;
<?php
			for ($f = 0; $f< count($currArray); $f++) {
				if ($currArray[$f]["useexchangerate"] == "Y") {
?>
				document.detailsForm.<?php print $outputFieldPrefix.$currArray[$f]["currencyID"]; ?>.value = presentValue(theValue * <?php print $currArray[$f]["exchangerate"]; ?>,6,"",".","");
<?php
				}
			}
?>
			} //end of this function
<?php
			return "recalculateCurrencies_$recordFieldPrefix();";
		}	
		
		function outputCurrencyArray($currArray) {
?>
			currCount = <?php print count($currArray); ?>;
			currArray = new Array(currCount); 
<?php
			for ($f = 0; $f < count($currArray); $f++) {
?>
			currArray[<?php print $f; ?>] = new Array();
			currArray[<?php print $f; ?>]["currencyID"] = <?php print $currArray[$f]["currencyID"]; ?>;
			currArray[<?php print $f; ?>]["decimals"] = 4;
			currArray[<?php print $f; ?>]["pretext"] = "<?php print $currArray[$f]["pretext"]; ?>";
			currArray[<?php print $f; ?>]["middletext"] = "<?php print $currArray[$f]["middletext"]; ?>";
			currArray[<?php print $f; ?>]["posttext"] = "<?php print $currArray[$f]["posttext"]; ?>";
<?php
			}
		}

		function outputLanguagesArray($langArray) {
?>
			langCount = <?php print count($langArray); ?>;
			langArray = new Array(langCount); 
<?php
			for ($f = 0; $f < count($langArray); $f++) {
?>
			langArray[<?php print $f; ?>] = new Array();
			langArray[<?php print $f; ?>]["languageID"] = <?php print $langArray[$f]["languageID"]; ?>;
			langArray[<?php print $f; ?>]["name"] = "<?php print $langArray[$f]["name"]; ?>";
<?php
			}
		}		
	
		function outputExtraFieldsArray($efArray) {
			if (is_array($efArray)) {
?>
				efCount = <?php print count($efArray); ?>;
				efArray = new Array(efCount); 
<?php
				for ($f = 0; $f < count($efArray); $f++) {
?>
				efArray[<?php print $f; ?>] = new Array();
				efArray[<?php print $f; ?>]["extraFieldID"] = <?php print $efArray[$f]["extraFieldID"]; ?>;
				efArray[<?php print $f; ?>]["name"] = "<?php print $efArray[$f]["name"]; ?>";
				efArray[<?php print $f; ?>]["title"] = "<?php print $efArray[$f]["title"]; ?>";
				efArray[<?php print $f; ?>]["type"] = "<?php print $efArray[$f]["type"]; ?>";
				efArray[<?php print $f; ?>]["position"] = <?php print $efArray[$f]["position"]; ?>;
<?php
				}
			} else {
?>
				efCount = 0;
<?php
			}
		}
	}
?>