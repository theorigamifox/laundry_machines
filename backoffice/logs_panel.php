<script language="JavaScript">
	function showReports() {
		xReports = "";
		for (f = 1; f <=9; f++) {
			eval("rc2 = document.logsReports.check"+f+".checked");
			if (rc2 == true) {
				eval("daRep = document.logsReports.check"+f+".value");
				xReports = xReports + daRep+";";
			}
		}
		if (xReports == "") {
			rc=alert("You haven't selected any reports to view.");
		} else {
			xGrouping = document.logsReports.xGrouping.options[document.logsReports.xGrouping.selectedIndex].value;
			xYearF = document.logsReports.xYearF.options[document.logsReports.xYearF.selectedIndex].text;
			xMonthF = document.logsReports.xMonthF.options[document.logsReports.xMonthF.selectedIndex].text;
			xDayF = document.logsReports.xDayF.options[document.logsReports.xDayF.selectedIndex].text;
			xYearT = document.logsReports.xYearT.options[document.logsReports.xYearT.selectedIndex].text;
			xMonthT = document.logsReports.xMonthT.options[document.logsReports.xMonthT.selectedIndex].text;
			xDayT = document.logsReports.xDayT.options[document.logsReports.xDayT.selectedIndex].text;
			jssDetails.location.href = "logs_show.php?xReports="+xReports+"&xGrouping="+xGrouping+"&xYearF="+xYearF+"&xMonthF="+xMonthF+"&xDayF="+xDayF+"&xYearT="+xYearT+"&xMonthT="+xMonthT+"&xDayT="+xDayT+"&<?php print userSessionGET(); ?>";
		}
	}
</script>
<table cellpadding="2" cellspacing="0" class="table-list" width="100%">
			<tr>
				<td colspan="2" class="table-list-title"><center>Select Reports</center></td>
			</tr>
			<tr>
				<td class="table-list-entry1">


<table width="100%" cellpadding="1" cellspacing="0">
<form name="logsReports" onSubmit="return false;">
<tr>
	<td valign="top"><input type="checkbox" name="check1" value="browser"></td>
	<td><font class="normaltext">Browser Type</td>
</tr>
<tr>
	<td valign="top"><input type="checkbox" name="check2" value="os"></td>
	<td><font class="normaltext">Operating System</td>
</tr>
<tr>
	<td valign="top"><input type="checkbox" name="check3" value="referrer"></td>
	<td><font class="normaltext">Referring URL</td>
</tr>
<tr>
	<td valign="top"><input type="checkbox" name="check4" value="searchsum"></td>
	<td><font class="normaltext">Search Engine Summary</td>
</tr>
<tr>
	<td valign="top"><input type="checkbox" name="check5" value="searchquery"></td>
	<td><font class="normaltext">Search Engine / Keywords Combined</td>
</tr>
<tr>
	<td valign="top"><input type="checkbox" name="check6" value="keywords"></td>
	<td><font class="normaltext">Search Engine Keywords</td>
</tr>
<tr>
	<td valign="top"><input type="checkbox" name="check7" value="pages"></td>
	<td><font class="normaltext">Pages Viewed</td>
</tr>
<tr>
	<td valign="top"><input type="checkbox" name="check8" value="domain"></td>
	<td><font class="normaltext">Top Level Domain</td>
</tr>
<tr>
	<td valign="top"><input type="checkbox" name="check9" value="ip"></td>
	<td><font class="normaltext">IP Address</td>
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
			<option value="dayofweek">Day of Week</option>
			<option value="hour">Hour of Day</option>
		</select>
		<br>(Only applicable for detailed output, graph will be formatted by type of record)<br>
		<button name="show1" class="button-grey" onClick="showReports();">Show Reports</button><input type="hidden" name="xReports" value="">
	</td>
</tr>
</form>
</table>


				</td>
			</tr>
		</table>
