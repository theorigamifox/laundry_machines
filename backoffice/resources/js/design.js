	function buttonOn(obj) {
		obj.style.backgroundColor="#959595";
		obj.style.color="#000000";
	}

	function buttonOff(obj) {
		obj.style.backgroundColor="#D4D0C8";
		obj.style.color="#000000";
	}

	function buttonClick(obj) {
		obj.style.paddingLeft=2;
		obj.style.paddingTop=2;
		obj.style.paddingRight=0;
		obj.style.paddingBottom=0;
		obj.style.border = "none";
	}

	function buttonRelease(obj) {
		obj.style.paddingLeft=0;
		obj.style.paddingTop=0;
		obj.style.paddingRight=0;
		obj.style.paddingBottom=0;
		obj.style.border = "2 dotted #000000";
	}
