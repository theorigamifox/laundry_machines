<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$timeStart = microtime();
	
	function getmicrotime($theTime){ 
	   list($usec, $sec) = explode(" ",$theTime); 
	   return ((float)$usec + (float)$sec); 
	} 
	
	$myForm = new formElements;
	$command = getFORM("command");
	$command2 = getFORM("command2");
	dbConnect($dbA);
	
		$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");

	$searchAppend = "";

	if ($command=="select") {
		$xSelectType = getFORM("xSelectType");
		if ($xSelectType=="search") {
			$xSearchString = getFORM("xSearchString");
			$searchInt = intval($xSearchString)-retrieveOption("orderNumberOffset");
			$theQuery = "select * from $tableOrdersHeaders where (concat(title,\" \",forename,\" \",surname) like \"%$xSearchString%\" or email like 
\"%$xSearchString%\" or company like \"%$xSearchString%\" or orderID=$searchInt) order by orderID DESC";
			$searchAppend = "&command=select&xSelectType=search&xSearchString=$xSearchString";
			$titleAppend = "Search for \"$xSearchString\"";
		}
		if ($xSelectType=="status") {
			$xOrderStatus = getFORM("xOrderStatus");
			$xPrinted = getFORM("xPrinted");
			$printBit = "";
			switch ($xPrinted) {
				case "A":
					$printBit ="";
					break;
				case "P":
					$printBit = "orderPrinted='Y' and ";
					break;
				case "U":
					$printBit = "orderPrinted='N' and ";
					break;
			}
			$theQuery = "select * from $tableOrdersHeaders where $printBit status=\"$xOrderStatus\" order by orderID DESC";
			$searchAppend = "&command=select&xSelectType=status&xOrderStatus=$xOrderStatus&xPrinted=$xPrinted";
			if ($xOrderStatus == "N") {
				$titleAppend = "All New Orders";
			}
			if ($xOrderStatus == "F") {
				$titleAppend = "All Failed Orders";
			}
			if ($xOrderStatus == "P") {
				$titleAppend = "All Paid Orders";
			}
			if ($xOrderStatus == "D") {
				$titleAppend = "All Dispatched Orders";
			}
			if ($xOrderStatus == "I") {
				$titleAppend = "All Part-Dispatched Orders";
			}
			if ($xOrderStatus == "C") {
				$titleAppend = "All Cancelled Orders";
			}
			switch ($xPrinted) {
				case "A":
					$titleAppend .=" (Printed and Unprinted)";
					break;
				case "P":
					$titleAppend .=" (Printed Only)";
					break;
				case "U":
					$titleAppend .=" (Unprinted Only)";
					break;
			}
		}
		if ($xSelectType=="month") {
			$xMonth = getFORM("xMonth");
			$xYear = getFORM("xYear");
			$theQuery = "select * from $tableOrdersHeaders where datetime >= \"".$xYear.$xMonth."01000000\" and datetime <= \"".$xYear.$xMonth."31235959\" order by orderID DESC";
			$searchAppend = "&command=select&xSelectType=month&xMonth=$xMonth&xYear=$xYear";
			$titleAppend = "Orders For $xMonth/$xYear";
		}
		if ($xSelectType=="period") {
			$xDateRange = getFORM("xDateRange");
			//GOT TO FINISH THIS ONE OFF!!!
			if ($xDateRange == "TODAY") {
				$tDate = date("Ymd");
				$sDate = formatDate($tDate);
				$theQuery = "select * from $tableOrdersHeaders where datetime >= \"".$tDate."000000\" and datetime <= \"".$tDate."235959\" 
order by orderID DESC";
				$titleAppend = "Orders For $sDate";
			}
			if ($xDateRange == "YESTERDAY") {
				$tDate = date("Ymd",mktime (0,0,0,date("m")  ,date("d")-1,date("Y")));
				$sDate = formatDate($tDate);
				$theQuery = "select * from $tableOrdersHeaders where datetime >= \"".$tDate."000000\" and datetime <= \"".$tDate."235959\" 
order by orderID DESC";
				$titleAppend = "Orders For $sDate";
			}
			if ($xDateRange == "THISWEEK") {
				$tDate = date("Ymd");
				$sDate = formatDate($tDate);
				$tDate2 = date("Ymd",mktime (0,0,0,date("m"),date("d")-6,date("Y")));
				$sDate2 = formatDate($tDate2);
				$theQuery = "select * from $tableOrdersHeaders where datetime >= \"".$tDate2."000000\" and datetime <= \"".$tDate."235959\" 
order by orderID DESC";
				$titleAppend = "Order Between $sDate2 and $sDate";
			}
			if ($xDateRange == "LASTMONTH") {
				$tDate = date("Ymd");
				$sDate = formatDate($tDate);
				$tDate2 = date("Ymd",mktime (0,0,0,date("m")-1,date("d")+1,date("Y")));
				$sDate2 = formatDate($tDate2);
				$theQuery = "select * from $tableOrdersHeaders where datetime >= \"".$tDate2."000000\" and datetime <= \"".$tDate."235959\" 
order by orderID DESC";
				$titleAppend = "Order Between $sDate2 and $sDate";
			}
			$searchAppend = "&command=select&xSelectType=period&xDateRange=$xDateRange";
		}
	} else {
		$theQuery = "select * from $tableOrdersHeaders order by orderID DESC";
		$searchAppend = "";
		$titleAppend = "All Orders";
	}
	//do search to find out max then add limit in
	$ordersperpage =  retrieveOption("adminOrdersPerPage");
	$xOffset = getFORM("xOffset");
	if ($xOffset=="") { $xOffset = 0; }
	$result=$dbA->query($theQuery);
	$resultCount = $dbA->count($result);
	$theQuery .= " LIMIT $xOffset,$ordersperpage";
	$upperCount = $xOffset+$ordersperpage;
	$lowerCount = $xOffset+1;
	$middleButtons = "Viewing <b>$lowerCount - ".$upperCount."</b>&nbsp;";
	if ($xOffset-$ordersperpage > -1) {
		$pOffset = $xOffset - $ordersperpage;
		$previousButton = "<input type=\"button\" id=\"buttonPrev\" class=\"button-expand\" onClick=\"self.location.href='orders.php?".userSessionGET().$searchAppend."&xOffset=$pOffset'\" value=\"&lt; Prev\">";
		$previousButton .= "&nbsp;<input type=\"button\"  id=\"buttonTop\" class=\"button-expand\" onClick=\"self.location.href='orders.php?".userSessionGET().$searchAppend."&xOffset=0'\" value=\"[Top]\">";
	} else {
		$previousButton = "";
	}
	if ($xOffset+$ordersperpage < $resultCount) {
		$nOffset = $xOffset + $ordersperpage;
		$nextButton = "<input type=\"button\"  id=\"buttonNext\" class=\"button-expand\" onClick=\"self.location.href='orders.php?".userSessionGET().$searchAppend."&xOffset=$nOffset'\" value=\"Next &gt;\">";
	} else {
		$nextButton = "";
	}
	$searchAppend .= "&xOffset=$xOffset";
	if ($previousButton=="" && $nextButton=="") {
		$navButtons = $middleButtons;
	}
	if ($previousButton=="" && $nextButton!="") {
		$navButtons = $middleButtons.$nextButton;
	}
	if ($previousButton!="" && $nextButton=="") {
		$navButtons = $middleButtons.$previousButton;
	}
	if ($previousButton!="" && $nextButton!="") {
		$navButtons = $middleButtons.$previousButton."&nbsp;".$nextButton;
	}

	$addColumn = 0;
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
</HEAD>
<script language="JavaScript">

	function executeCommand() {
		myCommand = document.ordersForm.xMyAction.options[document.ordersForm.xMyAction.selectedIndex].value;
		if (myCommand != "-") {
			if (document.ordersForm.selectList.value == "") {
				alert("Please select some orders to process first!");
			} else {
				if (confirm("Are you sure you wish to process the selected orders with the chosen command?")) {
					myList = document.ordersForm.selectList.value;
					if (myCommand == "view") {
						self.location.href="orders_show.php?xCmd=view&<?php print userSessionGET(); ?><?php print $searchAppend; ?>&xOrderID="+myList;
					}
					if (myCommand == "delete") {
						self.location.href="orders_process.php?xAction=deletelist&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?><?php print $searchAppend; ?>&xOrdersList="+myList;
					}
					if (myCommand == "print") {
						document.getElementById("printOrders").src="orders_show.php?xCmd=print&<?php print userSessionGET(); ?><?php print $searchAppend; ?>&xOrderID="+myList;
					}
					if (myCommand == "receipt") {
						document.getElementById("printOrders").src="orders_receipt.php?xCmd=print&<?php print userSessionGET(); ?><?php print $searchAppend; ?>&xOrderList="+myList;
					}
					if (myCommand == "setpaid") {
						self.location.href="orders_process.php?xAction=setpaid&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?><?php print $searchAppend; ?>&xOrderList="+myList;
					}
					if (myCommand == "setcancelled") {
						self.location.href="orders_process.php?xAction=setcancelled&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?><?php print $searchAppend; ?>&xOrderList="+myList;
					}
					if (myCommand == "suppliers") {
						self.location.href="orders_process.php?xAction=suppliers&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?><?php print $searchAppend; ?>&xOrderList="+myList;
					}
					if (myCommand.substring(0,9) == "paperwork") {
						document.getElementById("printOrders").src="orders_paperwork.php?xCmd=print&xPaperwork="+myCommand+"&<?php print userSessionGET(); ?><?php print $searchAppend; ?>&xOrderList="+myList;
					}
				}
			}
			document.ordersForm.xMyAction.selectedIndex = 0;
		}
	}

	function deleteOrder(orderID,showID) {
		if (confirm("Are you sure you wish to PERMANENTLY delete order: "+showID+" ?")) {
			self.location.href="orders_process.php?xAction=delete&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?><?php print $searchAppend; ?>&xOrderID="+orderID;
		}
	}

	function setPaid(orderID,showID) {
		if (confirm("Are you sure you wish to mark order "+showID+" as paid?")) {
			self.location.href="orders_process.php?xAction=setpaid&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?><?php print $searchAppend; ?>&xOrderID="+orderID;
		}
	}
	
	function setCancelled(orderID,showID) {
		if (confirm("Are you sure you wish to mark order "+showID+" as cancelled?")) {
			self.location.href="orders_process.php?xAction=setcancelled&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?><?php print $searchAppend; ?>&xOrderID="+orderID;
		}
	}
	
	function setDispatchedGiftCert(orderID,showID) {
		if (confirm("Are you sure you wish to mark order "+showID+" as dispatched?\nNOTE: Email gift certificates will automatically be sent.")) {
			self.location.href="orders_process.php?xAction=setdispatchedsimple&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?><?php print $searchAppend; ?>&xTrackingEnabled=N&xOrderID="+orderID;
		}
	}	
	
	function setDispatchedPre(orderID,showID) {
		if (confirm("Are you sure you wish to dispatch order "+showID+"?")) {
			self.location.href="orders_dispatch_pre.php?<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?><?php print $searchAppend; ?>&xOrderID="+orderID;
		}
	}
	
	function setDispatched(orderID,showID) {
		if (confirm("Are you sure you wish to dispatched order "+showID+"?")) {
			self.location.href="orders_dispatch_pre.php?<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?><?php print $searchAppend; ?>&xOrderID="+orderID;
		}
	}


	function ordersSelectAll() {
		if (document.ordersForm.selectAll.checked == true) {
			setStatus = true;
		} else {
			setStatus = false;
		}
		for (f = 0; f < document.ordersForm.transCount.value; f++) {
			document.ordersForm.elements["select"+f].checked = setStatus;
		}
		ordersRefresh();
	}
	
	function ordersRefresh() {
		selectList = "";
		for (f = 0; f < document.ordersForm.transCount.value; f++) {
			if (document.ordersForm.elements["select"+f].checked == true) {
				selectList = selectList + document.ordersForm.elements["select"+f].value+";";
			}
		}
		document.ordersForm.selectList.value = selectList;
	}		
	
	function ordersViewList() {
		if (document.ordersForm.selectList.value == "") {
			alert("Please select some orders to view.");
		} else {
			self.location.href="orders_show.php?xCmd=view&<?php print userSessionGET(); ?><?php print $searchAppend; ?>&xOrderID="+document.ordersForm.selectList.value;
		}
	}

	function showReceipt(orderID,showID) {
		window.open("orders_receipt.php?xCmd=view&<?php print userSessionGET(); ?>&xOrderID="+orderID);
	}	
	
	function pagePrint() {
		document.getElementById("printOrders").focus();
		document.getElementById("printOrders").print();
		//document.printOrders.focus();
		//document.printOrders.print();
	}

	function iePrint() {
		document.printOrders.focus();
		document.printOrders.print();
	}

	function createNewOrder() {
		currencyID = document.ordersForm.xCurrencyID.options[document.ordersForm.xCurrencyID.selectedIndex].value;
		self.location.href="orders_edit.php?xCurrencyID="+currencyID+"&<?php print userSessionGET(); ?>";
	}
</script>
<BODY>
<center>
<table width="100%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="table-outset">Order Management - <?php print $titleAppend; ?></td>
	</tr>
</table>
<Br>
<table width="100%" cellpadding="1" cellspacing="0" class="table-list">
<form name="ordersForm">
<tr>
	<td colspan="4" class="table-white-no-border" align="left">&nbsp;Selected Orders Action: 
		<select name="xMyAction" class="form-inputbox" onChange="executeCommand();">
			<option value="">Please select...</option>
			<option value="view">View Orders</option>
			<option value="print">Print: Orders</option>
			<?php if (retrieveOption("orderAdminActivateReceipt") == 1) { ?>
				<option value="receipt">Print: Receipts</option>
			<?php } ?>
			<?php
				$resPW = $dbA->query("select * from $tablePaperwork order by name");
				$countPW = $dbA->count($resPW);
				for ($pw = 0; $pw < $countPW; $pw++) {
					$recPW = $dbA->fetch($resPW);
					?><option value="paperwork:<?php print $recPW["paperworkID"]; ?>">Print: <?php print $recPW["name"]; ?></option><?php
				}
			?>
			<?php if (retrieveOption("suppliersEnabled") == 1) { ?>
				<option value="suppliers">Send Supplier Emails</option>
			<?php } ?>
			<option value="setpaid">Set Paid</option>
			<option value="setcancelled">Set Cancelled</option>
			<option value="-">-----------</option>
			<option value="delete">Delete Orders</option>
		</select>
		&nbsp;
	<iframe src="blank.html" id="printOrders" name="printOrders" frameborder="0" style="width:5px; height:5px; border:0"></iframe>
	Currency:
	<select name="xCurrencyID" class="form-inputbox">
	<?php
		for ($f = 0; $f < count($currArray); $f++) {
			?><option value="<?php print $currArray[$f]["currencyID"]; ?>"><?php print $currArray[$f]["code"]; ?></option><?php
		}
	?>
	</select>
	<input type="button" name="createOrder" value="Create Order" class="button-grey" onClick="createNewOrder();">
	</td>
	<td colspan="<?php print 5+$addColumn; ?>" class="table-white-no-border" align="right">Total selected transactions: <b><?php print $resultCount; ?></b>, <?php print $navButtons; ?>&nbsp;</td>
</tr>
<?php
	$result = $dbA->query($theQuery);
	$transCount = $dbA->count($result);
?>
<tr>
	<td class="table-list-title"><input type="checkbox" name="selectAll" onClick="ordersSelectAll();"><input type="hidden" name="selectList" value=""><input type="hidden" name="transCount" value="<?php print $transCount; ?>"></td>
	<td class="table-list-title">Order</td>
	<td class="table-list-title">Date &amp; Time</td>
	<td class="table-list-title">Customer Name</td>
	<td class="table-list-title" align="right">Total</td>
	<td class="table-list-title" align="center">Printed</td>
	<td class="table-list-title" align="center">Status</td>
	<td class="table-list-title" align="right">Action</td>
	<td class="table-list-title" align="right">Process</td>
</tr>
<?php
	for ($f = 0; $f < $transCount; $f++) {
		$tRecord = $dbA->fetch($result);
		$orderID = $tRecord["orderID"];
		$orderIDShow = retrieveOption("orderNumberOffset")+$orderID;
		$customerName = $tRecord["title"]." ".$tRecord["forename"]." ".$tRecord["surname"];
		$customerEmail = $tRecord["email"];
		$currencyID = $tRecord["currencyID"];
		$orderTotal = $tRecord["goodsTotal"]+$tRecord["shippingTotal"]+$tRecord["taxTotal"]-$tRecord["discountTotal"]-$tRecord["giftCertTotal"];
		$orderStatus = $tRecord["status"];
		$paidLink = "";
		if ($orderStatus == "P" || $orderStatus=="D") {
			$paidLink = "";
		} else {
			if ($orderStatus == "N" || $orderStatus == "F" || $orderStatus == "C") { $paidLink = "<input type=\"button\" name=\"buttonOSetPaid$f\" class=\"button-cyan\" onClick=\"setPaid($orderID, $orderIDShow);\" value=\"Set Paid\">"; }
		}
		if ($orderStatus == "D") {
			$dispatchLink = "";
		} else {
			if ($orderStatus == "N" || $orderStatus == "F" || $orderStatus == "C") {
				$dispatchLink = "";
			} else {
				if (retrieveOption("orderAdminActivateDispatch")==0) {
					$dispatchLink = "";
				} else {
					if (retrieveOption("orderAdminDispatchTracking") == 0 && retrieveOption("orderAdminDispatchPartial") == 0) {
						$dispatchLink = "<input type=\"button\" name=\"buttonOSetDispatched$f\" class=\"button-cyan\" onClick=\"setDispatched($orderID,$orderIDShow);\" value=\"Dispatch\">";
					} else {
						$dispatchLink = "<input type=\"button\" name=\"buttonOSetDispatched$f\" class=\"button-cyan\" onClick=\"setDispatchedPre($orderID, $orderIDShow);\" value=\"Dispatch\">";
					}
					if ($tRecord["giftCertOrder"] == "Y") {
						$dispatchLink = "<input type=\"button\" name=\"buttonOSetDispatched$f\" class=\"button-cyan\" onClick=\"setDispatchedGiftCert($orderID, $orderIDShow);\" value=\"Dispatch\">";
					}
				}
			}
		}
		if ($orderStatus == "N" || $orderStatus == "F") {
			$cancelButton = "<input type=\"button\" name=\"buttonOSetCancelled$f\" class=\"button-cyan\" onClick=\"setCancelled($orderID, $orderIDShow);\" value=\"Cancel\">&nbsp;";
		} else {
			$cancelButton = "";
		}
		if ($orderStatus == "N") { $orderStatus="NEW"; }
		if ($orderStatus == "F") { $orderStatus="FAILED"; }
		if ($orderStatus == "P") { $orderStatus="PAID"; }
		if ($orderStatus == "D") { $orderStatus="DISPATCHED"; }
		if ($orderStatus == "I") { $orderStatus="PART DISPATCHED"; }
		if ($orderStatus == "C") { $orderStatus="CANCELLED"; }
		
		if ($paidLink == "" || $dispatchLink == "") {
			$bufferBit = "&nbsp;";
		} else {
			$bufferBit = "";
		}
		$oDT = $tRecord["datetime"];
		$oDT = formatDate($oDT)." (".formatTime(substr($oDT,8,6)).")";
		
		switch ($tRecord["orderPrinted"]) {
			case "Y":
				$orderPrinted = "YES";
				break;
			case "N":
				$orderPrinted = "NO";
				break;
		}	
?>
<tr>
	<td class="table-list-entry1"><input type="checkbox" name="select<?php print $f; ?>" value="<?php print $orderID; ?>" onClick="ordersRefresh();"></td>
	<td class="table-list-entry1"><a href="orders_show.php?xCmd=single&xOrderID=<?php print $orderID; ?>&<?php print userSessionGET(); ?>"><?php print $orderIDShow; ?></a></td>
	<td class="table-list-entry1"><?php print $oDT; ?></td>
	<td class="table-list-entry1"><a href="mailto:<?php print $customerEmail; ?>"><?php print $customerName; ?></a></td>
	<td class="table-list-entry1" align="right"><?php print priceFormat($orderTotal,$currencyID); ?></td>
	<td class="table-list-entry1" align="center"><?php print $orderPrinted; ?></td>
	<td class="table-list-entry1" align="center"><?php print $orderStatus; ?></td>
	<td class="table-list-entry1" align="right">
		&nbsp;<input type="button" name="buttonOEdit<?php print $f; ?>" class="button-blue" onClick="self.location.href='orders_edit.php?xOrderID=<?php print $orderID; ?>&<?php print hiddenFromGET(); ?>&<?php print userSessionGET(); ?>'" value="Edit">
		&nbsp;<input type="button" name="buttonODelete<?php print $f; ?>" class="button-red" onClick="javascript:deleteOrder(<?php print $orderID; ?>,<?php print $orderIDShow; ?>);" value="Delete">
		<?php if (retrieveOption("orderAdminActivateReceipt") == 1) { ?>
		&nbsp;<input type="button" name="buttonOReceipt<?php print $f; ?>" class="button-orange" onClick="javascript:showReceipt(<?php print $orderID; ?>,<?php print $orderIDShow; ?>);" value="Receipt">
		<?php } ?>
	</td>
	<td class="table-list-entry1" align="right"><?php print $cancelButton; ?><?php print $paidLink; ?><?php print $dispatchLink; ?><?php print $bufferBit; ?></td>
</tr>
<?php
	}
?>
<tr>
	<td class="table-list-title" colspan="<?php print 7+$addColumn; ?>">Total Number of Orders</td>
	<td class="table-list-title" align="right"><?php print $dbA->count($result); ?></td>
	<td class="table-list-title" align="right">&nbsp;</td>
</tr>
</form>
</table>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>

