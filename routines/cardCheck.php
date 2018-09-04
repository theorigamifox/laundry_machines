<?php
	class cardCheck {
		var $ccNumber;
		var $ccType;
		var $ccExpiry;
		var $ccStart;
		var $ccIssue;
		var $ccName;
		var $ccError;
		var $validNumber;
		var $validType;
		var $validExpiry;
		var $validStart;
		var $validIssue;
		var $validName;
		
		function cardCheck($myNumber,$myType,$myExpiry,$myStart,$myIssue,$myName) {
			$this->ccNumber = ereg_replace("[^0-9]", "", $myNumber);
			$this->ccType = $myType;
			$this->ccExpiry = $myExpiry;
			$this->ccStart = $myStart;
			$this->ccIssue = ereg_replace("[^0-9]", "", $myIssue);
			$this->ccName = $myName;
			
			$this->validNumber = true;
			$this->validType = true;
			$this->validExpiry = true;
			$this->validStart = true;
			$this->validIssue = true;
			$this->validName = true;
			
			
			if ( empty($myName) ) {
				$this->validName = false;
			}
			
			
			$this->_checkExpiry();
			$this->_checkNumberFormat();
			switch ($myType) {
				case "Switch":
				case "Solo":
					$this->_checkStart();
					if ($this->validStart == false) {
						if (!is_numeric($this->ccIssue)) {
							$this->validIssue = false;
						} else {
							$this->validStart = true;
						}
					}
			}

			if (empty($this->ccNumber)) {
				$this->validNumber = false;
			}
		}
		
		function _checkNumberFormat() {
			switch($this->ccType) {
				case "Visa":
					$this->validNumber = ereg("^4[0-9]{12}([0-9]{3})?$", $this->ccNumber);
					$this->_mod10();
					break;
				case "Mastercard":
					$this->validNumber = ereg("^5[1-5][0-9]{14}$", $this->ccNumber);
					$this->_mod10();
					break;
				case "American Express":
					$this->validNumber = ereg("^3[47][0-9]{13}$", $this->ccNumber);
					$this->_mod10();
					break;
				case "Discover":
				case "Discover Card":
					$this->validNumber = ereg("^6011[0-9]{12}$", $this->ccNumber);
					$this->_mod10();
					break;
				case "JCB":
					$this->validNumber = ereg("^(3[0-9]{4}|2131|1800)[0-9]{11}$", $this->ccNumber);
					$this->_mod10();
					break;
				case "Diners Club":
					$this->validNumber = ereg("^3(0[0-5]|[68][0-9])[0-9]{11}$", $this->ccNumber);
					$this->_mod10();
					break;
				case "Switch":
					$this->validNumber = ereg("^([0-9]{6}[\s\-]{1}[0-9]{12}|[0-9]{18})$", $this->ccNumber);
				default:
					//this is an unsupported type by this checking script so we'll just return true
					//as we have no basis on which to reject it.
					$this->validNumber = true;
			}
		}
		
		function _mod10() {
			$cardRev = strrev($this->ccNumber);
			$numTotal = 0;

 			for($f = 0; $f < strlen($cardRev); $f++) {
 				$currentNum = substr($cardRev, $f, 1);
  				if($f % 2 == 1) {   $currentNum *= 2; }
  				if($currentNum > 9) {
 					$firstNum = $currentNum % 10;
 					$secondNum = ($currentNum - $firstNum) / 10;
 					$currentNum = $firstNum + $secondNum;
 				}
 				$numTotal += $currentNum;
 			}

			$modSuccess = ($numTotal % 10 == 0);
			
			if (!$modSuccess) {
				$this->validNumber = false;
			}
		}
		
		function _checkNumber() {
			if (empty($this->ccNumber)) {
				$this->validNumber = false;
			}
		}
		
		function _checkExpiry() {
			$expdate = $this->ccExpiry;
			$expdate = ereg_replace("[^0-9]", "", $expdate);
			if (strlen($expdate) > 6) {
				$this->validExpiry = false;
				return;
			}
			if (strlen($expdate) < 4) {
				$this->validExpiry = false;
				return;
			}
			$expM = substr($expdate,0,2);
			$expY = substr($expdate,-2);
			$cMonth = date("m");
			$cYear = date("y");
			if ($expM < 1 || $expM > 12) {
				$this->validExpiry = false;
				return;
			}
			if ($cYear == $expY && $expM < $cMonth) {
				$this->validExpiry = false;
				return;
			}
			if ($expY < $cYear) {
				$this->validExpiry = false;
			}
			$this->ccExpiry = $expM."/".$expY;
		}
		
		function _checkStart() {
			$startdate = $this->ccStart;
			$startdate = ereg_replace("[^0-9]","",$startdate);
			if ($this->ccType != "Switch" && $this->ccType != "Solo") {
				if (empty($startdate)) {
					return true;
				}
			}
			if (strlen($startdate) > 6) {
				$this->validStart = false;
				return;
			}
			if (strlen($startdate) < 4) {
				$this->validStart = false;
				return;
			}
			$startM = substr($startdate,0,2);
			$startY = substr($startdate,-2);
			$cMonth = date("m");
			$cYear = date("y");
			if ($startM < 1 || $startM > 12) {
				$this->validStart = false;
				return;
			}
			if ($cYear == $startY && $startM > $cMonth) {
				$this->validStart = false;
				return;
			}
			if ($startY > $cYear) {
				$this->validStart = false;
				return;
			}
			$this->ccStart = $startM."/".$startY;
		}
		
		function isValid() {
			if (!$this->validNumber || !$this->validType || !$this->validExpiry || !$this->validStart || !$this->validIssue || !$this->validName) {
				return false;
			} else {
				return true;
			}
		}
		
		function returnError() {
			return $this->ccError;
		}
	}
?>
