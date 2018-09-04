<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);

	$uResult = $dbA->query("select * from $tableCurrencies order by currencyID");
	$currencySelectArray = null;
	$uCount = $dbA->count($uResult);
	for ($f = 0; $f < $uCount; $f++) {
		$uRecord = $dbA->fetch($uResult);
		$currencySelectArray[] = array("text"=>$uRecord["code"],"value"=>$uRecord["currencyID"]);
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
		<td class="detail-title">Orders Report</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","reports_orders_show.php",""); ?>
<?php userSessionPOST(); ?>
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Date From:</td>
		<td class="table-list-entry1" valign="top">
			<select name="xDayF" class="form-inputbox">
			<?php
				$tDay = date("d");
				for ($f = 1; $f <= 31; $f++) {
					if ($f == 1) {
						$selected = "SELECTED";
					} else {
						$selected = "";
					}
					if ($f < 10) {
						$fshow = "0".$f;
					} else {
						$fshow = $f;
					}
			?>
				<option <?php print $selected; ?>><?php print $fshow; ?></option>
			<?php
				}
			?>
			</select>&nbsp;<select name="xMonthF" class="form-inputbox">
			<?php
				$tMonth = date("m");
				for ($f = 1; $f <= 12; $f++) {
					if ($f == $tMonth) {
						$selected = "SELECTED";
					} else {
						$selected = "";
					}
					if ($f < 10) { $padder = "0"; } else { $padder = ""; }
			?>
				<option <?php print $selected; ?>><?php print $padder.$f; ?></option>
			<?php
				}
			?>
			</select>&nbsp;<select name="xYearF" class="form-inputbox">
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
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Date To:</td>
		<td class="table-list-entry1" valign="top">
			<select name="xDayT" class="form-inputbox">
			<?php
				$tDay = date("d");
				for ($f = 1; $f <= 31; $f++) {
					if ($f == $tDay) {
						$selected = "SELECTED";
					} else {
						$selected = "";
					}
					if ($f < 10) {
						$fshow = "0".$f;
					} else {
						$fshow = $f;
					}
			?>
				<option <?php print $selected; ?>><?php print $fshow; ?></option>
			<?php
				}
			?>
			</select>&nbsp;<select name="xMonthT" class="form-inputbox">
			<?php
				$tMonth = date("m");
				for ($f = 1; $f <= 12; $f++) {
					if ($f == $tMonth) {
						$selected = "SELECTED";
					} else {
						$selected = "";
					}
					if ($f < 10) { $padder = "0"; } else { $padder = ""; }
			?>
				<option <?php print $selected; ?>><?php print $padder.$f; ?></option>
			<?php
				}
			?>
			</select>&nbsp;<select name="xYearT" class="form-inputbox">
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
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Group By:</td>
		<td class="table-list-entry1" valign="top">
			<select name="xGrouping" class="form-inputbox">
			<option value="year">Year</option>
			<option value="month" SELECTED>Month</option>
			<option value="day">Day</option>
			</select>		
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Orders In Currency:</td>
		<td class="table-list-entry1" valign="top">
			<?php $myForm->createSelect("xCurrency",retrieveOption("defaultCurrency"),"BOTH",$currencySelectArray); ?>	
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Order Status:</td>
		<td class="table-list-entry1" valign="top">
			<select name="xStatus" class="form-inputbox">
				<option value="N">New Orders</option>
				<option value="P">Paid Orders</option>
				<option value="F">Failed Orders</option>
				<option value="D">Dispatched Orders</option>
				<option value="I">Part-Dispatched Orders</option>
				<option value="C">Cancelled Orders</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Totals To Show:</td>
		<td class="table-list-entry1" valign="top">
			<input type="checkbox" name="xTotalGoods" value="Y" CHECKED> Goods Total
			<br><input type="checkbox" name="xTotalShipping" value="Y" CHECKED> Shipping Total
			<br><input type="checkbox" name="xTotalTax" value="Y" CHECKED> Tax Total
			<br><input type="checkbox" name="xTotalDiscount" value="Y"> Discount Total
			<br><input type="checkbox" name="xTotalCert" value="Y"> Cert Total
			<br><input type="checkbox" name="xTotalOrder" value="Y" CHECKED> Order Total
		</td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit","Generate Report"); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>