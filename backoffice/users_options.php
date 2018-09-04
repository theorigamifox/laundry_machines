<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$dbA = new dbAccess();
	$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
	$userLogging = retrieveOption("userLogging");
	if ($userLogging == 0) {
		$xUserLoggingYes = "";
		$xUserLoggingNo = "CHECKED";
	} else {
		$xUserLoggingYes = "CHECKED";
		$xUserLoggingNo = "";
	}
	
	$disableUserLogins = retrieveOption("disableUserLogins");
	if ($disableUserLogins == 0) {
		$xDisableUserLoginsYes = "";
		$xDisableUserLoginsNo = "CHECKED";
	} else {
		$xDisableUserLoginsYes = "CHECKED";
		$xDisableUserLoginsNo = "";
	}

	$userLoggingLogins = retrieveOption("userLoggingLogins");
	if ($userLoggingLogins == 0) {
		$xUserLoggingLoginsYes = "";
		$xUserLoggingLoginsNo = "CHECKED";
	} else {
		$xUserLoggingLoginsYes = "CHECKED";
		$xUserLoggingLoginsNo = "";
	}	

	$myForm = new formElements;
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
</HEAD>
<script>
	function checkFields() {
	}
</script>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Management Options</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","users_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Non-Administrator Safe Mode</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xNonAdminSafeMode",retrieveOption("nonAdminSafeMode"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">User Action Logging Enabled?</td>
		<td class="table-list-entry1" valign="top"><input type="radio" name="xUserLogging" value="0" <?php print $xUserLoggingNo; ?>> NO&nbsp;&nbsp;&nbsp;<input type="radio" name="xUserLogging" value="1" <?php print $xUserLoggingYes; ?>> YES</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">User Login/Logout Logging Enabled?</td>
		<td class="table-list-entry1" valign="top"><input type="radio" name="xUserLoggingLogins" value="0" <?php print $xUserLoggingLoginsNo; ?>> NO&nbsp;&nbsp;&nbsp;<input type="radio" name="xUserLoggingLogins" value="1" <?php print $xUserLoggingLoginsYes; ?>> YES</td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Disable User Logins?</td>
		<td class="table-list-entry1" valign="top"><input type="radio" name="xDisableUserLogins" value="0" <?php print $xDisableUserLoginsNo; ?>> NO&nbsp;&nbsp;&nbsp;<input type="radio" name="xDisableUserLogins" value="1" <?php print $xDisableUserLoginsYes; ?>> YES
		<br>(This will not affect the default administrator login.)</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Check IP Address</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xUsersCheckIPAddress",retrieveOption("usersCheckIPAddress"),"01"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Timeout Login With No Action After</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xUsersTimeout",10,5,retrieveOption("usersTimeout"),"integer"); ?> minutes (0 = no timeout)</td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Number User Actions Per Page</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xAdminUserLogPerPage",10,5,retrieveOption("adminUserLogPerPage"),"integer"); ?></td>
	</tr>		
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit","Update Options"); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
</HTML>
<?php 	$dbA->close();	?>
