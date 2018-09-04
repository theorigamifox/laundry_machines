<?php
	include("resources/includeBase.php");
	$dbA = new dbAccess();
	$dbA->connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);

	$xUsername = makeSafe(getFORM("xUsername"));
	$xPassword = makeSafe(getFORM("xPassword"));
	$userRecord =  dbUsersLogin($xUsername,$xPassword);
	if (!is_array($userRecord)) {
		userLogLogin(false,$xUsername);
		doRedirect("index.php?error=notfound&xUsername=$xUsername");
		$dbA->close();
		exit;
	}
	$versionNumber = retrieveOption("jssVersion");
	if (retrieveOption("nonAdminSafeMode")) {
		$safeMode = " * SAFE MODE ENABLED *";
	} else {
		$safeMode = "";
	}
	$dbA->close();
	include("frontend.php");


	function dbUsersLogin($vUsername,$vPassword) {
		global $dbA,$tableUsers;
		$vPassword2=md5($vPassword);
		$result = $dbA->query("select * from $tableUsers where username=\"$vUsername\" and password=\"$vPassword2\"");
		if ($dbA->count($result) != 1) {
			return false;
		} else {
			$userRecord = $dbA->fetch($result);
			if ($userRecord["loginEnabled"]==0) {
				userLogLogin(false,$vUsername);
				$dbA->close();
				doRedirect("index.php?error=accdisabled&xUsername=$vUsername");				
				exit;
			}			
			if (retrieveOption("disableUserLogins")==1) {
				if ($userRecord["userID"] != 1) {
					userLogLogin(false,$vUsername);
					$dbA->close();
					doRedirect("index.php?error=disabled&xUsername=$vUsername");				
					exit;
				}
			}
			srand((double)microtime()*1000000);
			$randID = rand();
			$randIDEncoded = md5($randID);
			$logIP = @$_SERVER["REMOTE_ADDR"];
			$mTime = microtime();
			$mSplit = explode(" ",$mTime);
			$curSeconds = $mSplit[1];
			$dbA->query("update $tableUsers set logIP=\"$logIP\", randID=\"$randIDEncoded\",lastAction=$curSeconds where username=\"$vUsername\"");
			userLogLogin(true,$vUsername);
			$userRecord["randID"]=$randID;
			return $userRecord;
		}
	}

	function userSessionGET() {
		global $userRecord;
		return "xUsername=".$userRecord["username"]."&xRandID=".$userRecord["randID"];
	}
	
	function userLogLogin($xSuccess,$xUsername) {
		global $dbA,$tableUserLog,$tableOptions;
		$logging = retrieveOption("userLoggingLogins");
		if ($logging == 1) {
			$theDate = date("Ymd");
			$theTime = date("His");
			$logIP = @$_SERVER["REMOTE_ADDR"];
			if ($xSuccess == true) {
				$vDescription = "Login Success";
			} else {
				$vDescription = "<B>Login Failed";
			}
			$dbA->query("insert into $tableUserLog (actionDate,actionTime,ip,username,description) VALUES(\"$theDate\",\"$theTime\",\"$logIP\",\"$xUsername\",\"$vDescription\")");
		}
	}	
?>
