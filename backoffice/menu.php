<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	$myForm = new formElements;
	
	$imageLink = "<img src=\"images/logo.gif\" border=\"0\" width=\"47\" height=\"48\" alt=\"JShop Server\">";
	
	$xDeniedList = split(";",$userRecord["deniedList"]);
	
	function isDenied($thisSection) {
		global $xDeniedList;
		for ($f = 0; $f < count($xDeniedList)-1; $f++) {
			if ($thisSection == $xDeniedList[$f]) {
				return "disabled";
			}
		}
		return "button-navbar";
	}

?>
<HTML>
<HEAD>
<TITLE></TITLE>
</HEAD>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript">
	function confirmLogout() {
		if (confirm("Are you sure you wish to logout?")) {
			top.jssMain.location.href="logout.php?<?php print userSessionGET(); ?>";
		}
	}
	
	function showHelpSys() {
		window.open("help/index.php","DPhelp","height=450,width=600,status=yes,toolbar=no,menubar=no,location=no,resizable=yes");
	}

</script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<BODY class="admin-body" topmargin="0" bottommargin="0" rightmargin="0" leftmargin="0">
<table width="100%" height="60" border="0" cellpadding="0" cellspacing="0">
	<tr bgcolor="#D4D0C8" height="58">
		<td height="100%" valign="center" width="60">
			<img src="images/spacer.gif" border="0" width="5" height="54"><A href="main.php?<?php print userSessionGET(); ?>" target="jssMain"><?php print $imageLink; ?></a>
		</td>
		<td valign="center">
			<table border="0" cellpadding="2" cellspacing="0">
				<tr>
					<td><?php $myForm->createNavBarButton("buttonUsers","Users","top.jssMain.location.href='section.php?xAdminSection=users&".userSessionGET()."'",isDenied("users"));?></td>
					<td><?php $myForm->createNavBarButton("buttonGeneral","General","top.jssMain.location.href='section.php?xAdminSection=general&".userSessionGET()."'",isDenied("general")); ?></td>
					<td><?php $myForm->createNavBarButton("buttonContents","Contents","top.jssMain.location.href='section.php?xAdminSection=contents&".userSessionGET()."'",isDenied("contents")); ?></td>
					<td><?php $myForm->createNavBarButton("buttonTaxShipping","Tax/Shipping","top.jssMain.location.href='section.php?xAdminSection=taxshipping&".userSessionGET()."'",isDenied("taxshipping")); ?></td>
					<td><?php $myForm->createNavBarButton("buttonLogs","Logs","top.jssMain.location.href='section.php?xAdminSection=logs&".userSessionGET()."'",isDenied("logs")); ?></td>
					<td><?php $myForm->createNavBarButton("buttonEmails","Templates","top.jssMain.location.href='section.php?xAdminSection=templates&".userSessionGET()."'",isDenied("templates")); ?></td>
					<td><?php $myForm->createNavBarButton("buttonExport","Import/Export","top.jssMain.location.href='section.php?xAdminSection=export&".userSessionGET()."'",isDenied("export")); ?></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td><?php $myForm->createNavBarButton("buttonNewsletter","Newsletter","top.jssMain.location.href='section.php?xAdminSection=newsletter&".userSessionGET()."'",isDenied("newsletter")); ?></td>
					<td><?php $myForm->createNavBarButton("buttonTemplates","Customers","top.jssMain.location.href='section.php?xAdminSection=customers&".userSessionGET()."'",isDenied("customers")); ?></td>
					<td><?php $myForm->createNavBarButton("buttonCheckout","Checkout","top.jssMain.location.href='section.php?xAdminSection=checkout&".userSessionGET()."'",isDenied("checkout")); ?></td>
					<td><?php $myForm->createNavBarButton("buttonReports","Reports","top.jssMain.location.href='section.php?xAdminSection=reports&".userSessionGET()."'",isDenied("reports")); ?></td>
					<td><?php $myForm->createNavBarButton("buttonAffiliates","Affiliates","top.jssMain.location.href='section.php?xAdminSection=affiliates&".userSessionGET()."'",isDenied("affiliates")); ?></td>
					<td><?php $myForm->createNavBarButton("buttonOrders","Orders","top.jssMain.location.href='orders_frames.php?".userSessionGET()."'",isDenied("orders")); ?></td>
					<td><?php $myForm->createNavBarButton("buttonBackup","Backup","top.jssMain.location.href='section.php?xAdminSection=backup&".userSessionGET()."'",isDenied("backup")); ?></td>
					<td><?php $myForm->createNavBarButton("buttonLogout","Logout","confirmLogout();","button-logout"); ?></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr bgcolor="#959595" height="1">
		<td colspan="2" height="1"><img src="images/spacer.gif" border="0" width="50" height="1"></td>
	</tr>
	<tr bgcolor="#D4D0C8" height="1">
		<td colspan="2" height="1"><img src="images/spacer.gif" border="0" width="50" height="1"></td>
	</tr>

</table>

</BODY>
</HTML>
