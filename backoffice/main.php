<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	$myForm = new formElements;

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
	dbConnect($dbA);
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">JShop Server v<?php print retrieveOption("jssVersion"); ?> Administration Home</td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" colspan="2">JShop Server Administration Menu</td>
	</tr>	
	<tr>
		<td class="table-list-title"><?php $myForm->createNavBarButton("buttonUsers","Users","self.location.href='section.php?xAdminSection=users&".userSessionGET()."'",isDenied("users"));?></td>
		<td class="table-list-entry1">User Administration</td>
	</tr>
	<tr>
		<td class="table-list-title"><?php $myForm->createNavBarButton("buttonGeneral","General","self.location.href='section.php?xAdminSection=general&".userSessionGET()."'",isDenied("general")); ?></td>
		<td class="table-list-entry1">General Settings</td>
	</tr>
	<tr>
		<td class="table-list-title"><?php $myForm->createNavBarButton("buttonContents","Contents","self.location.href='section.php?xAdminSection=contents&".userSessionGET()."'",isDenied("contents")); ?></td>
		<td class="table-list-entry1">Products &amp; Sections</td>
	</tr>
	<tr>
		<td class="table-list-title"><?php $myForm->createNavBarButton("buttonTaxShipping","Tax/Shipping","self.location.href='section.php?xAdminSection=taxshipping&".userSessionGET()."'",isDenied("taxshipping")); ?></td>
		<td class="table-list-entry1">Tax / Shipping Options</td>
	</tr>
	<tr>
		<td class="table-list-title"><?php $myForm->createNavBarButton("buttonLogs","Logs","self.location.href='section.php?xAdminSection=logs&".userSessionGET()."'",isDenied("logs")); ?></td>
		<td class="table-list-entry1">Visitor Logs</td>
	</tr>
	<tr>
		<td class="table-list-title"><?php $myForm->createNavBarButton("buttonEmails","Templates","self.location.href='section.php?xAdminSection=templates&".userSessionGET()."'",isDenied("templates")); ?></td>
		<td class="table-list-entry1">Email Templates</td>
	</tr>			
	<tr>
		<td class="table-list-title"><?php $myForm->createNavBarButton("buttonExport","Import/Export","self.location.href='section.php?xAdminSection=export&".userSessionGET()."'",isDenied("export")); ?></td>
		<td class="table-list-entry1">Export Data</td>
	</tr>			

	<tr>
		<td class="table-list-title"><?php $myForm->createNavBarButton("buttonNewsletter","Newsletter","self.location.href='section.php?xAdminSection=newsletter&".userSessionGET()."'",isDenied("newsletter")); ?></td>
		<td class="table-list-entry1">Newsletter Options</td>
	</tr>
	<tr>
		<td class="table-list-title"><?php $myForm->createNavBarButton("buttonCustomers","Customers","self.location.href='section.php?xAdminSection=customers&".userSessionGET()."'",isDenied("customers")); ?></td>
		<td class="table-list-entry1">Customer Administration</td>
	</tr>
	<tr>
		<td class="table-list-title"><?php $myForm->createNavBarButton("buttonCheckout","Checkout","self.location.href='section.php?xAdminSection=checkout&".userSessionGET()."'",isDenied("checkout")); ?></td>
		<td class="table-list-entry1">Ordering Options</td>
	</tr>
	<tr>
		<td class="table-list-title"><?php $myForm->createNavBarButton("buttonReports","Reports","self.location.href='section.php?xAdminSection=reports&".userSessionGET()."'",isDenied("reports")); ?></td>
		<td class="table-list-entry1">Reports &amp; Statistics</td>
	</tr>
	<tr>
		<td class="table-list-title"><?php $myForm->createNavBarButton("buttonOrders","Orders","self.location.href='orders_frames.php?".userSessionGET()."'",isDenied("orders")); ?></td>
		<td class="table-list-entry1">Order Management</td>
	</tr>
	<tr>
		<td class="table-list-title"><?php $myForm->createNavBarButton("buttonBackup","Backup","self.location.href='section.php?xAdminSection=backup&".userSessionGET()."'",isDenied("backup")); ?></td>
		<td class="table-list-entry1">Backup &amp; Restore</td>
	</tr>			
</table>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>
