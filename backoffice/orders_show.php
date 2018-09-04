<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	$myForm = new formElements;
	include ("../routines/Xtea.php");
	$crypt = new Crypt_Xtea();	

?>
<HTML>
<HEAD>
<TITLE>Order Details</TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
</HEAD>
<?php
	$xCmd = getFORM("xCmd");
	if ($xCmd == "print") {
		$printBit = "OnLoad=\"window.print();\"";
	} else {
		$printBit = "";
	}
?>
<?php
	if ($xCmd == "print") {
		if ($xBrowserShort == "IE") {
			?><BODY onLoad="parent.iePrint();"><?php
		} else {
			?><BODY onLoad="window.print();"><?php
		}
	} else {
		echo "<BODY>";
	}
?>
<?php

	if ($xCmd == "single") {
		$buttonTitle = "Print Order";
	} else {
		$buttonTitle = "Print Orders";
	}

	dbConnect($dbA);
	

	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"position,name");
	
	$onePrinted = false;
	$orderList = getFORM("xOrderID");
	$orderArray = split(";",$orderList);
	for ($x=0;$x<count($orderArray); $x++) {
		if ($orderArray[$x] != "") {
			$orderID=$orderArray[$x];
			$result = $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");	
			$oRecord = $dbA->fetch($result);
			if ($xCmd == "print") {
				$pResult = $dbA->query("update $tableOrdersHeaders set orderPrinted='Y' where orderID=$orderID");
			}
			include("orders_details.php");
			$onePrinted = true;
		}	
	}
	$dbA->close();
?>
</BODY>
</HTML>
