<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");	
	$dbA = new dbAccess();
	$dbA->connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);

	$xUsername = getFORM("xUsername");
	srand((double)microtime()*1000000);
	$randID = rand();
	$logIP = rand();
	$randIDEncoded = md5($randID);
	$result = $dbA->query("select * from $tableUsers where username=\"$xUsername\" and randID=\"".md5($userRecord["randID"])."\"");
	if ($dbA->count($result) != 0) {
		$dbA->query("update $tableUsers set logIP='$logIP', randID='$randIDEncoded' where username='$xUsername'");
		userLogLogout($xUsername);
	}
	$dbA->close();
	doRedirectTop("index.php");
	
	function userLogLogout($xUsername) {
		global $dbA,$tableUserLog,$tableOptions;
		$logging = retrieveOption("userLoggingLogins");
		if ($logging == 1) {
			$theDate = date("Ymd");
			$theTime = date("His");
			$logIP = @$_SERVER["REMOTE_ADDR"];
			$vDescription = "Logout";
			$dbA->query("insert into $tableUserLog (actionDate,actionTime,ip,username,description) VALUES(\"$theDate\",\"$theTime\",\"$logIP\",\"$xUsername\",\"$vDescription\")");
		}
	}	
?>
