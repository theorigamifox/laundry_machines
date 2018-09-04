<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	dbConnect($dbA);

	$result = $dbA->query("select * from $tableReportsPopular where type='P'");
	$totalProductViews = $dbA->count($result);

	$result = $dbA->query("select * from $tableReportsPopular where type='S'");
	$totalSectionViews = $dbA->count($result);

	$result = $dbA->query("select productID from $tableProducts");
	$totalProducts = $dbA->count($result);
	
	$result = $dbA->query("select sectionID from $tableSections");
	$totalSections = $dbA->count($result);
		
	$result = $dbA->query("select productID from $tableProducts where visible='N'");
	$totalHiddenProducts = $dbA->count($result);

	$result = $dbA->query("select sectionID from $tableSections where visible='N'");
	$totalHiddenSections = $dbA->count($result);
	
	$result = $dbA->query("select cartID from $tableCarts");
	$totalCarts = $dbA->count($result);	

	$result = $dbA->query("select cartID from $tableCartsContents");
	$totalCartsContents = $dbA->count($result);	
	
	$result = $dbA->query("select orderID from $tableOrdersHeaders");
	$totalOrders = $dbA->count($result);	

	$result = $dbA->query("select customerID from $tableCustomers");
	$totalCustomers = $dbA->count($result);	
		
	$dbA->close();
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
		<td class="detail-title">Reports Summary</td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title">Total Products</td>
		<td class="table-list-entry1"><?php print $totalProducts; ?></td>
	</tr>
	<tr>
		<td class="table-list-title">Total Hidden Products</td>
		<td class="table-list-entry1"><?php print $totalHiddenProducts; ?></td>
	</tr>
	<tr>
		<td class="table-list-title">Total Product Views</td>
		<td class="table-list-entry1"><?php print $totalProductViews; ?></td>
	</tr>
	<tr>
		<td class="table-list-title">Total Sections</td>
		<td class="table-list-entry1"><?php print $totalSections; ?></td>
	</tr>	
	<tr>
		<td class="table-list-title">Total Hidden Sections</td>
		<td class="table-list-entry1"><?php print $totalHiddenSections; ?></td>
	</tr>	
	<tr>
		<td class="table-list-title">Total Section Views</td>
		<td class="table-list-entry1"><?php print $totalSectionViews; ?></td>
	</tr>
	<tr>
		<td class="table-list-title">Total Outstanding Carts</td>
		<td class="table-list-entry1"><?php print $totalCarts; ?></td>
	</tr>	
	<tr>
		<td class="table-list-title">Total Products In Carts</td>
		<td class="table-list-entry1"><?php print $totalCartsContents; ?></td>
	</tr>	
	<tr>
		<td class="table-list-title">Total Orders</td>
		<td class="table-list-entry1"><?php print $totalOrders; ?></td>
	</tr>		
	<tr>
		<td class="table-list-title">Total Customers</td>
		<td class="table-list-entry1"><?php print $totalCustomers; ?></td>
	</tr>		
</table>
</center>
</BODY>
</HTML>
