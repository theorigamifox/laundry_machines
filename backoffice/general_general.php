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
		<td class="detail-title">General Settings</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","general_options_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<input type="hidden" name="xType" value="general">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Date Format</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xDateFormat",30,100,retrieveOption("dateFormat"),"general"); ?><br><b>Format Options:</b>
		<br><table cellpadding="2">
		<tr>
			<td valign="top"><font class="normaltext">d</font></td><td valign="top"><font class="normaltext">Day of month, 2 digits</font></td>
		</tr>
		<tr>
			<td valign="top"><font class="normaltext">D</font></td><td valign="top"><font class="normaltext">Day of week, e.g. Fri</font></td>
		</tr>
		<tr>
			<td valign="top"><font class="normaltext">m</font></td><td valign="top"><font class="normaltext">Month, 2 digits</font></td>
		</tr>
		<tr>
			<td valign="top"><font class="normaltext">M</font></td><td valign="top"><font class="normaltext">Month, 2 letter text, e.g. Mar</font></td>
		</tr>
		<tr>
			<td valign="top"><font class="normaltext">F</font></td><td valign="top"><font class="normaltext">Text month, e.g. March</font></td>
		</tr>
		<tr>
			<td valign="top"><font class="normaltext">y</font></td><td valign="top"><font class="normaltext">Year, 2 digits, e.g. 03</font></td>
		</tr>
		<tr>
			<td valign="top"><font class="normaltext">Y</font></td><td valign="top"><font class="normaltext">Year, 4 digits, e.g. 2003</font></td>
		</tr>
		</table>
		<b>Other characters shown as entered</b>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Time Format</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTimeFormat",30,100,retrieveOption("timeFormat"),"general"); ?><br><b>Format Options:</b>
		<br><table cellpadding="2">
		<tr>
			<td valign="top"><font class="normaltext">H</font></td><td valign="top"><font class="normaltext">Hour, 24-hour format</font></td>
		</tr>
		<tr>
			<td valign="top"><font class="normaltext">h</font></td><td valign="top"><font class="normaltext">Hour, 12-hour format</font></td>
		</tr>
		<tr>
			<td valign="top"><font class="normaltext">i</font></td><td valign="top"><font class="normaltext">Minutes</font></td>
		</tr>
		<tr>
			<td valign="top"><font class="normaltext">s</font></td><td valign="top"><font class="normaltext">Seconds</font></td>
		</tr>
		<tr>
			<td valign="top"><font class="normaltext">a</font></td><td valign="top"><font class="normaltext">Lowercase 'am' or 'pm'</font></td>
		</tr>
		<tr>
			<td valign="top"><font class="normaltext">A</font></td><td valign="top"><font class="normaltext">Uppercase 'AM' or 'PM'</font></td>
		</tr>
		</table>
		<b>Other characters shown as entered</b>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Convert Line Breaks To &lt;BR&gt;<br>on Products and Section Fields</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xConvertToBR",retrieveOption("convertToBR"),"01"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit","Update Settings"); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>
