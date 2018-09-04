<?php
		$xUsername = makeSafe(getFORM("xUsername"));
		$xRandID = makeInteger(getFORM("xRandID"));
		//echo $xUsername;
		//echo $xRandID;
		//echo @$_SERVER["SCRIPT_NAME"];
		//exit;
		if ($xUsername == "") { doForceLogout("unauthorised"); }
		$thisIP = $_SERVER["REMOTE_ADDR"];
		dbConnect($dbA);
		$userRecord = dbUsersCheckLogin($xUsername,$xRandID);
		$safeMode = retrieveOption("nonAdminSafeMode");
		if ($xUsername == "administrator") {
			$safeMode = 0;
		}
		if (!is_array($userRecord)) {
			$dbA->close();
			doForceLogout("unauthorised");
		} else {
			if ($thisIP != $userRecord["logIP"] && retrieveOption("usersCheckIPAddress") == 1) {
				doForceLogout("iperror");
			}
			$mTime = microtime();
			$mSplit = explode(" ",$mTime);
			$curSeconds = $mSplit[1];			
			$lastAction = retrieveOption("usersTimeout");
			if ($lastAction > 0 && ($userRecord["lastAction"] + ($lastAction * 60)) < ($curSeconds)) {
				doForceLogout("timeout");
			}
		}		
		$userRecord["randID"] = $xRandID;
		$dbA->close();

	function dbUsersCheckLogin($vUsername,$vRandID) {
		global $dbA, $tableUsers;
		$randIDenc = md5($vRandID);
		$result = $dbA->query("select * from $tableUsers where username=\"$vUsername\" and randID=\"$randIDenc\"");
		if ($dbA->count($result) != 1) {
			return false;
		} else {
			$userRecord = $dbA->fetch($result);
			$mTime = microtime();
			$mSplit = explode(" ",$mTime);
			$curSeconds = $mSplit[1];
			$result = $dbA->query("update $tableUsers set lastAction=$curSeconds where username=\"$vUsername\"");			
			return $userRecord;
		}
	}

	function userSessionGET() {
		global $userRecord;
		return "xUsername=".$userRecord["username"]."&xRandID=".$userRecord["randID"];
	}

	function userSessionPOST() {
		global $userRecord;
		echo "<input type=\"hidden\" name=\"xUsername\" value=\"".$userRecord["username"]."\">";
		echo "<input type=\"hidden\" name=\"xRandID\" value=\"".$userRecord["randID"]."\">";
	}
	
	function hiddenReturnPOST() {
		$xReturn = urldecode(getFORM("xReturn"));		
		$xReturn = str_replace(userSessionGET(),"",$xReturn);
		return "<input type=\"hidden\" name=\"xReturn\" value=\"".urlEncode($xReturn)."\">";
	}

	function hiddenReturnGET() {
		$xReturn = urldecode(getFORM("xReturn"));
		$xReturn = str_replace(userSessionGET(),"",$xReturn);	
		return "xReturn=".urlEncode($xReturn);
	}

	function hiddenFromPOST() {
		$xQueryString = str_replace("&".userSessionGET(),"",@$_SERVER["QUERY_STRING"]);
		$xQueryString = str_replace(userSessionGET(),"",$xQueryString);
		if (array_key_exists("REQUEST_URI",$_SERVER)) {
			$xPath = @$_SERVER["REQUEST_URI"];
		} else {
			$xPath = @$_SERVER["SCRIPT_NAME"];
		}
		$xPathSplit = explode("/",$xPath);
		$xPath = $xPathSplit[count($xPathSplit)-1];
		$xPathSplit = explode("?",$xPath);
		$xPath = $xPathSplit[0];			
		if ($xQueryString == "") {
			$xQueryString = "d=d";
		}
		return "<input type=\"hidden\" name=\"xReturn\" value=\"".urlEncode($xPath."?".$xQueryString)."\">";
	}

	function hiddenFromGET() {
		$xQueryString = str_replace("&".userSessionGET(),"",$_SERVER["QUERY_STRING"]);
		$xQueryString = str_replace(userSessionGET(),"",$xQueryString);
		if (array_key_exists("REQUEST_URI",$_SERVER)) {
			$xPath = @$_SERVER["REQUEST_URI"];
		} else {
			$xPath = @$_SERVER["SCRIPT_NAME"];
		}
		$xPathSplit = explode("/",$xPath);
		$xPath = $xPathSplit[count($xPathSplit)-1];	
		$xPathSplit = explode("?",$xPath);
		$xPath = $xPathSplit[0];	
		if ($xQueryString == "") {
			$xQueryString = "d=d";
		}		
		return "xReturn=".urlEncode($xPath."?".$xQueryString);
	}	
	
	function doForceLogout($reasonCode) {
		echo "<script>top.location.href=\"index.php?error=$reasonCode\";</script>";
		exit;
	}
	
	function userLog($vDescription) {
		global $dbA,$tableUserLog,$tableOptions,$xUsername;
		$logging = retrieveOption("userLogging");
		if ($logging == 1) {
			$theDate = date("Ymd");
			$theTime = date("His");
			$logIP = @$_SERVER["REMOTE_ADDR"];
			$dbA->query("insert into $tableUserLog (actionDate,actionTime,ip,username,description) VALUES(\"$theDate\",\"$theTime\",\"$logIP\",\"$xUsername\",\"$vDescription\")");
		}
	}
	
	function userLogActionAdd($vType,$vRecord) {
		userLog("Added $vType: $vRecord");
	}
	
	function userLogActionDelete($vType,$vRecord) {
		userLog("Deleted $vType: $vRecord");
	}
	
	function userLogActionUpdate($vType,$vRecord) {
		userLog("Updated $vType: $vRecord");
	}
	
	function userLogAction($vAction,$vType,$vRecord) {
		userLog("$vAction $vType: $vRecord");
	}
	
	function userLogSettings($vType) {
		userLog("Updated Settings: $vType");
	}
?>