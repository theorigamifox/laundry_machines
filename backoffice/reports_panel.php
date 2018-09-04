<script language="JavaScript">
	function showReports() {
		xReport = document.logsReports.xReport.value;
		if (xReport == "") {
			rc=alert("You haven't selected a report to view.");
		} else {
			xGrouping = document.logsReports.xGrouping.options[document.logsReports.xGrouping.selectedIndex].value;
			xYearF = document.logsReports.xYearF.options[document.logsReports.xYearF.selectedIndex].text;
			xMonthF = document.logsReports.xMonthF.options[document.logsReports.xMonthF.selectedIndex].text;
			xDayF = document.logsReports.xDayF.options[document.logsReports.xDayF.selectedIndex].text;
			xYearT = document.logsReports.xYearT.options[document.logsReports.xYearT.selectedIndex].text;
			xMonthT = document.logsReports.xMonthT.options[document.logsReports.xMonthT.selectedIndex].text;
			xDayT = document.logsReports.xDayT.options[document.logsReports.xDayT.selectedIndex].text;
			xLimit = document.logsReports.xLimit.options[document.logsReports.xLimit.selectedIndex].value;
			xLimitText = document.logsReports.xLimit.options[document.logsReports.xLimit.selectedIndex].text;
			jssDetails.location.href = "reports_show.php?xReport="+xReport+"&xLimit="+xLimit+"&xLimitText="+xLimitText+"&xGrouping="+xGrouping+"&xYearF="+xYearF+"&xMonthF="+xMonthF+"&xDayF="+xDayF+"&xYearT="+xYearT+"&xMonthT="+xMonthT+"&xDayT="+xDayT+"&<?php print userSessionGET(); ?>";
		}
	}
</script>
<table cellpadding="2" cellspacing="0" class="table-list" width="100%">
			<tr>
				<td colspan="2" class="table-list-title"><center>Select Report</center></td>
			</tr>
			<tr>
				<td class="table-list-entry1">


<table width="100%" cellpadding="1" cellspacing="0">
<form name="logsReports" onSubmit="return false;">
<input type="hidden" name="xReport" value="popprod">
<tr>
	<td valign="top"><input type="radio" name="radio1" onClick="document.logsReports.xReport.value='popprod';" CHECKED></td>
	<td><font class="normaltext">Product Popularity</td>
</tr>
<tr>
	<td valign="top"><input type="radio" name="radio1" onClick="document.logsReports.xReport.value='popsec';"></td>
	<td><font class="normaltext">Section Popularity</td>
</tr>
<tr>
	<td valign="top"><input type="radio" name="radio1" onClick="document.logsReports.xReport.value='abnprod';"></td>
	<td><font class="normaltext">Abandoned Cart Products</td>
</tr>
<tr>
	<td valign="top"><input type="radio" name="radio1" onClick="document.logsReports.xReport.value='search';"></td>
	<td><font class="normaltext">Search Statistics</td>
</tr>
<tr>
	<td valign="top"><input type="radio" name="radio1" onClick="document.logsReports.xReport.value='ordtot';"></td>
	<td><font class="normaltext">Total Orders</td>
</tr>
<tr>
	<td valign="top"><input type="radio" name="radio1" onClick="document.logsReports.xReport.value='ordprod';"></td>
	<td><font class="normaltext">Ordered Products</td>
</tr>
<tr>
	<td valign="top"><input type="radio" name="radio1" onClick="document.logsReports.xReport.value='custacc';"></td>
	<td><font class="normaltext">New Customer Accounts</td>
</tr>
</table>


				</td>
			</tr>
		</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list" width="100%">
			<tr>
				<td colspan="2" class="table-list-title"><center>Select Range</center></td>
			</tr>
			<tr>
				<td class="table-list-entry1">


<table width="100% cellpadding="2" cellspacing="0">
<tr>
	<td>
		<font class="boldtext">From:<br>
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
		</select><br>
		<font class="boldtext">To:<br>
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
		</select><br>
		Group By:<br></font><font class="normaltext">
		<select name="xGrouping" class="form-inputbox">
			<option value="year">Year</option>
			<option value="month" SELECTED>Month</option>
			<option value="day">Day</option>

		</select><br>
		<b>Limit Result Lines:<br></font><font class="normaltext">
		<select name="xLimit" class="form-inputbox">
			<option value="10">10</option>
			<option value="20">20</option>
			<option value="50">50</option>
			<option value="100">100</option>
			<option value="200">200</option>
			<option value="300">300</option>
			<option value="99999999">Unlimited</option>


		</select>
		<br><br>
		<button name="show1" class="button-grey" onClick="showReports();">Show Reports</button>
	</td>
</tr>
</form>
</table>


				</td>
			</tr>
		</table>
