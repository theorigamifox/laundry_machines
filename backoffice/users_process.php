<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$recordType = "User";
	$tableName = $tableUsers;
	$linkBackButton = "USER LIST";
	$linkBackLink = "users_list.php";

	$xAction=getFORM("xAction");
	if ($xAction == "insert") {
		$xUsernameInput = getFORM("xUsernameInput");
		if ($dbA->doesRecordExist($tableName,"username",$xUsernameInput)) {
			setupProcessMessage($recordType,$xUsernameInput,"error_duplicate_add","BACK","");
		} else {
			$rArray[] = array("username",$xUsernameInput,"S");			
			$rArray[] = array("password",md5(getFORM("xPassword")),"S");
			$rArray[] = array("realname",getFORM("xRealname"),"S");
			$rArray[] = array("loginEnabled",getFORM("xLoginEnabled"),"S");
			$rArray[] = array("deniedList",getFORM("xDeniedList"),"S");
			$dbA->insertRecord($tableName,$rArray);
			userLogActionAdd($recordType,$xUsernameInput);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "delete") {
		$xUserID = getFORM("xUserID");
		if (!$dbA->doesIDExist($tableName,"userID",$xUserID,$uRecord)) {
			setupProcessMessage($recordType,"","error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"userID",$xUserID);
			userLogActionDelete($recordType,$uRecord["username"]);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$xUserID = getFORM("xUserID");
		if (!$dbA->doesIDExist($tableName,"userID",$xUserID,$uRecord)) {
			setupProcessMessage($recordType,"","error_existance","BACK","");	
		} else {
			if (getFORM("xPassword") != "") {
				$rArray[] = array("password",md5(getFORM("xPassword")),"S");
			}
			$rArray[] = array("realname",getFORM("xRealname"),"S");
			$rArray[] = array("loginEnabled",getFORM("xLoginEnabled"),"S");
			$rArray[] = array("deniedList",getFORM("xDeniedList"),"S");
			$dbA->updateRecord($tableName,"userID=$xUserID",$rArray);
			userLogActionUpdate($recordType,getFORM("xUsernameInput"));
			doRedirect("$linkBackLink?".userSessionGET());
		}		
	}
	if ($xAction == "options") {
		updateOption("userLogging",getFORM("xUserLogging"));
		updateOption("userLoggingLogins",getFORM("xUserLoggingLogins"));
		updateOption("disableUserLogins",getFORM("xDisableUserLogins"));
		updateOption("adminUserLogPerPage",getFORM("xAdminUserLogPerPage"));
		updateOption("nonAdminSafeMode",getFORM("xNonAdminSafeMode"));
		updateOption("usersCheckIPAddress",getFORM("xUsersCheckIPAddress"));
		updateOption("usersTimeout",makeInteger(getFORM("xUsersTimeout")));
		userLog("Updated User Management");
		doRedirect("users_options.php?".userSessionGET());		
	}
	if ($xAction == "clearlog") {
		$dbA->query("delete from $tableUserLog");
		userLog("Cleared User Action Log");
		doRedirect("$linkBackLink?".userSessionGET());	
	}	
?>
