<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	$myForm = new formElements;
	$myForm->buttonClass = "button-action";
?>
<HTML>
<HEAD>
<TITLE>Orders Search</TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
</HEAD>
<script language="JavaScript">
	function orderSearch() {
		top.jssMain.jssOrdersList.location.href="orders.php?command=select&<?php print userSessionGET(); ?>&xSelectType=search&xSearchString="+document.searchForm.xSearchString.value;
	}
	
	function orderStatus() {
		top.jssMain.jssOrdersList.location.href="orders.php?command=select&<?php print userSessionGET(); ?>&xSelectType=status&xOrderStatus="+document.searchForm.xStatus.options[document.searchForm.xStatus.selectedIndex].value+"&xPrinted="+document.searchForm.xPrinted.options[document.searchForm.xPrinted.selectedIndex].value;;
	}

	function orderUnprinted() {
		top.jssMain.jssOrdersList.location.href="orders.php?command=select&<?php print userSessionGET(); ?>&xSelectType=status&xOrderStatus=UP";
	}	

	function orderMonth() {
		top.jssMain.jssOrdersList.location.href="orders.php?command=select&<?php print userSessionGET(); ?>&xSelectType=month&xMonth="+document.searchForm.xMonth.value+"&xYear="+document.searchForm.xYear.value;
	}

	function orderPeriod() {
		top.jssMain.jssOrdersList.location.href="orders.php?command=select&<?php print userSessionGET(); ?>&xSelectType=period&xDateRange="+document.searchForm.xDateRange.value;
	}
</script>
<script>
	function checkFields() {
	}
</script>
<BODY topmargin="0" bottommargin="0" rightmargin="0" leftmargin="0" bgcolor="#FFFFFF">
<table width="100%" height="100" border="0" cellpadding="0" cellspacing="0">
	<tr bgcolor="#D4D0C8" height="1">
		<td colspan="1" height="1"><img src="../images/spacer.gif" border="0" width="50" height="1"></td>
	</tr>
	<tr bgcolor="#959595" height="1">
		<td colspan="1" height="1"><img src="../images/spacer.gif" border="0" width="50" height="1"></td>
	</tr>
	<tr bgcolor="#D4D0C8" height="5">
		<td colspan="1" height="5"><img src="../images/spacer.gif" border="0" width="50" height="5"></td>
	</tr>

	<tr bgcolor="#D4D0C8" height="95">
		<td valign="top">
			<centeR>
			<form name="searchForm" onSubmit="orderSearch(); return false;">
			<table width="96%" cellpadding="2" cellspacing="0" class="table-list">
			<tr>
				<td class="table-list-title" width="33%">Date Range</td>
				<td class="table-list-title" width="33%">Order Type</td>
				<td class="table-list-title" width="33%">Search</td>
			</tr>
			<tr>
				<td  class="table-list-entry1" valign="top">
					<table cellpadding="0" cellspacing="2" border="0">
					<tr>
						<td><font class="boldtext">Range:</font></td>
						<td>
							<select name="xDateRange" class="form-inputbox">
								<option value="TODAY">Today</option>
								<option value="YESTERDAY">Yesterday</option>
								<option value="THISWEEK">Last 7 Days</option>
								<option value="LASTMONTH">Last Month</option>
							</select>
						</td>
						<td><input type="button" id="buttonOrderType" class="button-expand" onClick="orderPeriod();" value="Show"></td>
					</tr>
					<tr>
						<td><font class="boldtext">Month:</font></td>
						<td>
							<select name="xMonth" class="form-inputbox">
								<option value="01">January</option>
								<option value="02">Febuary</option>
								<option value="03">March</option>
								<option value="04">April</option>
								<option value="05">May</option>
								<option value="06">June</option>
								<option value="07">July</option>
								<option value="08">August</option>
								<option value="09">September</option>
								<option value="10">October</option>
								<option value="11">November</option>
								<option value="12">December</option>
							</select>
							<select name="xYear" class="form-inputbox">
		<?php
			$thisYear = date("Y");
			for ($f = 2003; $f <= $thisYear; $f++) {
				if ($f == $thisYear) {
					$selected = "SELECTED";
				} else {
					$selected = "";
				}
		?>
		<option <?php print $selected; ?>><?php print $f; ?></option>
		<?php
			}
		?>																
							</select>
						</td>
						<td><input type="button" id="buttonOrderType" class="button-expand" onClick="orderMonth();" value="Show"></td>
					</tr>
					</table>
				</td>
				<td  class="table-list-entry1" valign="top">
					<table cellpadding="0" cellspacing="2" border="0">
					<tr>
						<td><font class="boldtext">Status:</font></td>
						<td>
							<select name="xStatus" class="form-inputbox">
								<option value="N">New Orders</option>
								<option value="P">Paid Orders</option>
								<option value="F">Failed Orders</option>
								<option value="D">Dispatched Orders</option>
								<option value="I">Part-Dispatched Orders</option>
								<option value="C">Cancelled Orders</option>
							</select>
						</td>
						<td rowspan="2"><input type="button" id="buttonOrderType" class="button-expand" onClick="orderStatus();" value="Show"></td>
					</tr>
					<tr>
						<td><font class="boldtext">Printed:</font></td>
						<td>
							<select name="xPrinted" class="form-inputbox">
								<option value="A">Printed and Unprinted</option>
								<option value="U">Unprinted Only</option>
								<option value="P">Printed Only</option>
							</select>
						</td>
					</tr>					
					</table>
				</td>
				<td  class="table-list-entry1" valign="top">
					<b>Please enter terms to search for below:</b>
					<table cellpadding="0" cellspacing="2" border="0">
					<tr>
						<td valign="top"><input type="text" name="xSearchString" size="30" maxlength="150" value="" class="form-inputbox" onFocusIn="this.style.backgroundColor='#E9E6E1'" 
onFocusOut="this.style.backgroundColor='#FFFFFF'"></td>
						<td valign="top"><input type="button" id="buttonOrderType" class="button-expand" onClick="orderSearch();" value="Search"></td>
					</tr>
					</table>
				</td>
			</tr>
			</table>
			</form>
			</center>
		</td>
	</tr>
</table>
</BODY>
</html>
