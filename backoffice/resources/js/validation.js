	function validateAlpha(e) {
		if (e.keyCode==13) { return true; }
		if (e.type!="keypress") { return true; }
		if (isAlpha(e.keyCode)) { return true; }
		e.keyCode = 0;
		return false;
	}
	
	function valAlpha(e) {
		var keyCode = e ? e.which : event.keyCode;
		if (keyCode==13) { return false; }
		if (e.type!="keypress") { return true; }
		if (generalNav(e)) { return true; }
		if (isAlpha(e.keyCode)) { return true; }
		return false;
	}

	function validateNumeric(e) {
		if (e.keyCode==13) { return true; }
		if (e.type!="keypress") { return true; }
		if (isNumeric(e.keyCode)) { return true; }
		if (isMinusChar(e.keyCode)) { return true; }
		e.keyCode = 0;
		return false;
	}

	function valNumeric(e) {
		var keyCode = e ? e.which : event.keyCode;
		if (keyCode==13) { return false; }
		if (e.type!="keypress") { return true; }
		if (generalNav(e)) { return true; }
		if (isNumeric(keyCode)) { return true; }
		if (isMinusChar(keyCode)) { return true; }
		return false;
	}
	
	//function valNumeric(e) {
	//	var keyCode = e ? e.which : event.keyCode;
	//	if (keyCode==13) { return false; }
	//	if (e.type!="keypress") { return true; }
	//	if (generalNav(e)) { return true; }
	//	if (isNumeric(keyCode)) { return true; }
	//	if (isMinusChar(keyCode)) { return true; }
	//	e.keyCode = 0;
	//	return false;
	//}

	function validateAlphaNumeric(e) {
		if (e.keyCode==13) { return true; }
		if (e.type!="keypress") { return true; }
		if (isAlpha(e.keyCode)) { return true; }
		if (isNumeric(e.keyCode)) { return true; }
		e.keyCode = 0;
		return false;
	}
	
	function valAlphaNumeric(e) {
		var keyCode = e ? e.which : event.keyCode;
		if (keyCode==13) { return false; }
		if (e.type!="keypress") { return true; }
		if (generalNav(e)) { return true; }
		if (isAlpha(keyCode)) { return true; }
		if (isNumeric(keyCode)) { return true; }
		return false;
	}

	function validateGeneral(e) {
		if (e.keyCode==13) { return true; }
		if (e.type!="keypress") { return true; }
		if (isDoubleQuote(e.keyCode)) {
			e.keyCode = 0;
			return false;
		}
		return true;
	}
	
	function valGeneral(e) {
		var keyCode = e ? e.which : event.keyCode;
		if (keyCode==13) { return false; }
		if (e.type!="keypress") { return true; }
		if (generalNav(e)) { return true; }
		if (isDoubleQuote(keyCode)) {
			return false;
		}
		return true;
	}

	function validateEmail(e) {
		if (e.keyCode==13) { return true; }
		if (e.type!="keypress") { return true; }
		if (isAlpha(e.keyCode)) { return true; }
		if (isNumeric(e.keyCode)) { return true; }
		if (isDecimalPlace(e.keyCode)) { return true; }
		if (isAtSign(e.keyCode)) { return true; }
		if (isUnderscore(e.keyCode)) { return true; }
		if (isMinusChar(e.keyCode)) { return true; }
		if (isComma(e.keyCode)) { return true; }
		e.keyCode = 0;
		return false;
	}
	
	function valEmail(e) {
		var keyCode = e ? e.which : event.keyCode;
		if (keyCode==13) { return false; }
		if (e.type!="keypress") { return true; }
		if (generalNav(e)) { return true; }
		if (isAlpha(keyCode)) { return true; }
		if (isNumeric(keyCode)) { return true; }
		if (isDecimalPlace(keyCode)) { return true; }
		if (isAtSign(keyCode)) { return true; }
		if (isUnderscore(keyCode)) { return true; }
		if (isMinusChar(keyCode)) { return true; }
		if (isComma(keyCode)) { return true; }
		return false;
	}

	function validateURL(e) {
		if (e.keyCode==13) { return true; }
		if (e.type!="keypress") { return true; }
		if (isAlpha(e.keyCode)) { return true; }
		if (isNumeric(e.keyCode)) { return true; }
		if (isDecimalPlace(e.keyCode)) { return true; }
		if (isUnderscore(e.keyCode)) { return true; }
		if (isForwardSlash(e.keyCode)) { return true; }
		if (isColon(e.keyCode)) { return true; }
		if (isMinusChar(e.keyCode)) { return true; }
		e.keyCode = 0;
		return false;
	}
	
	function valURL(e) {
		var keyCode = e ? e.which : event.keyCode;
		if (keyCode==13) { return false; }
		if (e.type!="keypress") { return true; }
		if (generalNav(e)) { return true; }
		if (isAlpha(keyCode)) { return true; }
		if (isNumeric(keyCode)) { return true; }
		if (isDecimalPlace(keyCode)) { return true; }
		if (isUnderscore(keyCode)) { return true; }
		if (isForwardSlash(keyCode)) { return true; }
		if (isColon(keyCode)) { return true; }
		if (isMinusChar(keyCode)) { return true; }
		return false;
	}

	function validateDecimal(e) {
		if (e.keyCode==13) { return false; }
		if (e.type!="keypress") { return true; }
		if (isNumeric(e.keyCode)) { return true; }
		if (isDecimalPlace(e.keyCode)) { return true; }
		if (isMinusChar(e.keyCode)) { return true; }
		e.keyCode = 0;
		return false;
	}
	
	function valDecimal(e) {
		var keyCode = e ? e.which : event.keyCode;
		if (keyCode==13) { return false; }
		if (e.type!="keypress") { return true; }
		if (generalNav(e)) { return true; }
		if (isNumeric(keyCode)) { return true; }
		if (isDecimalPlace(keyCode)) { return true; }
		if (isMinusChar(keyCode)) { return true; }
		return false;
	}
	
	function valNoReturn(e) {
		var keyCode = e ? e.which : event.keyCode;
		if (keyCode==13) { return false; }
		if (e.type!="keypress") { return true; }
		if (generalNav(e)) { return true; }
		return true;
	}
	
	function generalNav(e) {
		var keyCode = e ? e.which : e.keyCode;
		if (keyCode==8) { return true; }
		if (e.shiftKey) { return true; }
		if (e.ctrlKey) { return true; }
		if (keyCode == 0) { return true; }
		return false;
	}

	function isAlpha(myKeyCode) {
		if (myKeyCode >= 97 && myKeyCode <= 122) {
			return true;
		}
		if (myKeyCode >=65 && myKeyCode <=90) {
			return true;
		}
		return false;
	}

	function isNumeric(myKeyCode) {
		if (myKeyCode >= 48 && myKeyCode <=57) {
			return true;
		}
		return false;
	}

	function isDecimalPlace(myKeyCode) {
		if (myKeyCode == 46) { return true; }
		return false;
	}

	function isDoubleQuote(myKeyCode) {
		if (myKeyCode == 34) { return true; }
		return false;
	}

	function isAtSign(myKeyCode) {
		if (myKeyCode == 64) { return true; }
		return false;
	}

	function isUnderscore(myKeyCode) {
		if (myKeyCode == 95) { return true; }
		return false;
	}

	function isForwardSlash(myKeyCode) {
		if (myKeyCode == 47) { return true; }
		return false;
	}

	function isColon(myKeyCode) {
		if (myKeyCode == 58) { return true; }
		return false;
	}
	
	function isSemiColon(myKeyCode) {
		if (myKeyCode == 59) { return true; }
		return false;
	}	

	function isMinusChar(myKeyCode) {
		if (myKeyCode == 45) { return true; }
		return false;
	}
	
	function isAmp(myKeyCode) {
		if (myKeyCode == 38) { return true; }
		return false;
	}
	
	function isEquals(myKeyCode) {
		if (myKeyCode == 61) { return true; }
		return false;
	}
	
	function isComma(myKeyCode) {
		if (myKeyCode == 44) { return true; }
		return false;
	}