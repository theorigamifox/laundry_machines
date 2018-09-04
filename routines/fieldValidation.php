<?php
	if (!defined("IN_JSHOP")) {
		echo "File cannot be run direct";
		exit;
	}

	include($jssShopFileSystem."resources/includes/validations.php");
	
	function validateFields(&$fieldArray,&$errorArray) {
		global $regexValidate;
		$errorReturn = FALSE;
		for ($f = 0; $f < count($fieldArray); $f++) {
			$thisField = chop(makeSafe(getFORM($fieldArray[$f]["fieldname"])));
			$errorArray[$fieldArray[$f]["fieldname"]] = $thisField;
			if ($fieldArray[$f]["validation"] == 1) {
				$valError = FALSE;
				$validated = FALSE;
				switch ($fieldArray[$f]["validationType"]) {
					case "NOTBLANK":
					case "":
						if (empty($thisField)) {
							$valError = TRUE;
						}
						$validated = TRUE;
						break;
					case "CUSTOM":
						$regex = $fieldArray[$f]["regex"];
						if (!ereg($regex,$thisField)) {
							$valError = TRUE;
						}
						$validated = TRUE;
						break;
				}
				if (!$validated) {
					foreach ($regexValidate as $key => $value) {
						if ($key == $fieldArray[$f]["validationType"]) {
							if (!ereg($value,$thisField)) {
								$valError = TRUE;
							}
						}
					}
				}
				if ($valError) {
					$errorReturn = TRUE;
					$errorArray["error"] = "Y";
					$errorArray[$fieldArray[$f]["fieldname"]."_error"] = "Y";
				}
			}
		}
		return $errorReturn;
	}

	function validateFieldsNew($fieldArray,$errorArray) {
		global $regexValidate;
		$errorReturn = FALSE;
		for ($f = 0; $f < count($fieldArray); $f++) {
			$thisField = chop(makeSafe(getFORM($fieldArray[$f]["fieldname"])));
			$errorArray[$fieldArray[$f]["fieldname"]] = $thisField;
			if ($fieldArray[$f]["validation"] == 1) {
				$valError = FALSE;
				$validated = FALSE;
				switch ($fieldArray[$f]["validationType"]) {
					case "NOTBLANK":
					case "":
						if (empty($thisField)) {
							$valError = TRUE;
						}
						$validated = TRUE;
						break;
					case "CUSTOM":
						$regex = $fieldArray[$f]["regex"];
						if (!ereg($regex,$thisField)) {
							$valError = TRUE;
						}
						$validated = TRUE;
						break;
				}
				if (!$validated) {
					foreach ($regexValidate as $key => $value) {
						if ($key == $fieldArray[$f]["validationType"]) {
							if (!ereg($value,$thisField)) {
								$valError = TRUE;
							}
						}
					}
				}
				if ($valError) {
					$errorReturn = TRUE;
					$errorArray["error"] = "Y";
					$errorArray[$fieldArray[$f]["fieldname"]."_error"] = "Y";
				}
			}
		}
		return $errorArray;
	}

	
	function validateIndividual($fieldValue,$valType,$regex="") {
		global $regexValidate;
		switch ($valType) {
			case "NOTBLANK":
			case "":
				if (empty($fieldValue)) {
					return false;
				}
				break;
			case "CUSTOM":
				if (!ereg($regex,$fieldValue)) {
					return false;
				}
				break;
		}
		foreach ($regexValidate as $key => $value) {
			if ($key == $valType) {
				if (!ereg($value,$fieldValue)) {
					return false;
				}
			}
		}
		return true;
	}
?>