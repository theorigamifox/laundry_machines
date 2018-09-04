<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("resources/sections.php");
	if (file_exists("resources/extrasections.php")) {
		include("resources/extrasections.php");
	}
	$xAdminSection = getFORM("xAdminSection");
	$deniedList = split(";",$userRecord["deniedList"]);
	for ($f = 0; $f < count($deniedList)-1; $f++) {
		if ($xAdminSection == $deniedList[$f]) {
			include("routines/processMessage.php");
			createProcessMessage("Unauthorised Access!",
			"You are not authorised to use this section!",
			"Your user account is not authorised to use this section of JShop Server.<br>Please see your system administrator for more information.",
			"Home",
			"self.location.href='main.php?".userSessionGET()."';");	
			exit;
		}
	}
?>
<HTML>
<HEAD>
<TITLE><?php print $sectionsArray[$xAdminSection][0]; ?></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript">
	function showImagePicker(startDir,pickerField) {
		window.open("imagepicker.php?xStartDir="+startDir+"&xPickerField="+pickerField+"&<?php print userSessionGET(); ?>","JSSImagePicker","height=600,width=350,scrollbars=yes,status=no,toolbar=no,menubar=no,location=no,resizable=yes");
	}
	
	function showDigitalPicker(startDir,pickerField) {
		window.open("digitalpicker.php?xStartDir="+startDir+"&xPickerField="+pickerField+"&<?php print userSessionGET(); ?>","JSSDigitalPicker","height=600,width=350,scrollbars=yes,status=no,toolbar=no,menubar=no,location=no,resizable=yes");
	}	
</script>
</HEAD>
<BODY class="admin-body">
<center>
<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
<tr>
	<td height="100%">

<table width="100%" border="0" cellpadding="2" cellspacing="2" height="100%">
<tr>
	<td width="150" valign="top" height="100%">
		<table cellpadding="2" cellspacing="0" class="table-list" width="100%">
			<tr>
				<td colspan="2" class="table-list-title"><center><?php print $sectionsArray[$xAdminSection][0]; ?> Menu</center></td>
			</tr>
	
<?php
	for ($f = 0; $f < count($menuOptions[$xAdminSection]); $f++) {
		if ($menuOptions[$xAdminSection][$f][0] == "NEWMENU") {
?>
		</table>
		<p>
		<table cellpadding="2" cellspacing="0" class="table-list" width="100%">
			<tr>
				<td colspan="2" class="table-list-title"><center><?php print $menuOptions[$xAdminSection][$f][1]; ?> Menu</center></td>
			</tr>
<?php
		} else {
?>
			<tr>
				<td class="table-list-entry1" align="left"><a href="<?php print $menuOptions[$xAdminSection][$f][1]."?".userSessionGET().$menuOptions[$xAdminSection][$f][2]; ?>" target="jssDetails"><?php print $menuOptions[$xAdminSection][$f][0]; ?></a></td>
			</tr>
<?php
		}
	}
?>	
		</table>
<?php
	if ($subpanelArray[$xAdminSection][0] != "") {
?>
		<p>
		<?php include($subpanelArray[$xAdminSection][1]); ?>		
<?php
	}
?>
	</td>
	<td valign="top"><iframe name="jssDetails" src="<?php print configureIFrame($sectionsArray[$xAdminSection][1]); ?>" width="100%" height="100%" frameborder="0" STYLE="border:solid black 1px"></iframe></td>
</tr>
<tr><td>&nbsp;</td></tr>
</table>

	</td>
</tr>
</table>
</center>
</BODY>
</HTML>
<?php
	function configureIFrame($theURL) {
		if (strpos($theURL, "?") === false) {
			return $theURL."?".userSessionGET();
		} else {
			return $theURL."&".userSessionGET();
		}
	}
?>