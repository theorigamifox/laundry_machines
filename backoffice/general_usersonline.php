<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$dbA = new dbAccess();
	$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
	
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
		<td class="detail-title">Users Online Settings</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","general_options_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<input type="hidden" name="xType" value="usersonline">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Activate Users Online Feature</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xUsersOnlineActivated",retrieveOption("usersOnlineActivated"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Time Limit For Calculation</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xUsersOnlineTimeFrame",10,5,retrieveOption("usersOnlineTimeFrame"),"integer"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit","Update Settings"); ?></td>
	</tr>
<?php
	if (retrieveOption("usersOnlineActivated") == 1) {
		$usersonlineArray["timelimit"] = makeInteger(retrieveOption("usersOnlineTimeFrame"));
		$timeFrame = makeInteger(retrieveOption("usersOnlineTimeFrame"))*60;
		$currentTime = time();
		$oldestTime = $currentTime - $timeFrame;
		$result = $dbA->query("select * from $tableCarts where createtime >= $oldestTime");
		$count = $dbA->count($result);
		$usersonlineArray["current"] = $count;
		if ($count > makeInteger(retrieveOption("usersOnlineMost"))) {
			updateOption("usersOnlineMost",$count);
			updateOption("usersOnlineMostDate",date("Ymd"));
			$usersonlineArray["most"] = $count;
			$usersonlineArray["mostdate"] = formatDate(date("Ymd"));
		} else {
			$usersonlineArray["most"] = makeInteger(retrieveOption("usersOnlineMost"));
			if (retrieveOption("usersOnlineMostDate") != "") {
				$usersonlineArray["mostdate"] = formatDate(retrieveOption("usersOnlineMostDate"));
			} else {
				$usersonlineArray["mostdate"] = "";
			}
		}
?>
	<tr>
		<td class="table-list-entry0" valign="top" colspan="2">Current Users Online</td>
	</tr>
	<tr>
		<td class="table-list-entry1" valign="top" colspan="2">
			There are <?php print $usersonlineArray["current"]; ?> users online currently
			<br>The most ever was <?php print $usersonlineArray["most"]; ?> users on <?php print $usersonlineArray["mostdate"]; ?>
		</td>
	</tr>
<?php
	}
?>
</table>
</form>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>
