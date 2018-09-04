<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	$xType=getFORM("xType");
	$xShippingID=getFORM("xShippingID");
	$typeResult = $dbA->query("select * from $tableShippingTypes where shippingID=$xShippingID");
	$typeRecord = $dbA->fetch($typeResult);
	$zoneArray = $dbA->retrieveAllRecords($tableZones,"name");
	
	if ($xType=="new") {
		$pageTitle = "Add New Shipping Rate For: ".$typeRecord["name"];
		$submitButton = "Insert Shipping Rate";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'><input type='hidden' name='xShippingID' value='$xShippingID'>";

		$xLoginEnabledYes = "CHECKED";
		$xLoginEnabledNo = "";		
	}
	$allOthers = 0;
	if ($xType=="edit") {
		$xRateID = getFORM("xRateID");
		$pageTitle = "Edit Existing Shipping Rate For: ".$typeRecord["name"];
		$submitButton = "Update Shipping Rate";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xRateID' value='$xRateID'><input type='hidden' name='xShippingID' value='$xShippingID'>";
		$uResult = $dbA->query("select * from $tableShippingRates where rateID=$xRateID");	
		$uRecord = $dbA->fetch($uResult);
		if (@$uRecord["sfrom"] == -1) {
			$allOthers = 1;
		} else {
			$allOthers = 0;
		}
		if ($uRecord["sfrom"] == -1) { $uRecord["sfrom"] = 0; }
		if ($uRecord["sto"] == -1) { $uRecord["sto"] = 0; }
	}
	
	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	$myForm = new formElements;

	switch ($typeRecord["calcType"]) {
		case "W":
			$calcType = "Weight";
			break;
		case "Q":
			$calcType = "Qty";
			break;
		case "T":
			$calcType = "Total";
			break;
	}
	switch ($typeRecord["fmType"]) {
		case "F":
			$fmType = "Flat Rate";
			break;
		case "M":
			$fmType = "Multiplication";
			break;
	}
	
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
</HEAD>
<script>
	<?php $myForm->outputCurrencyArray($currArray); ?>
	<?php $recalculator = $myForm->createCurrencyRecalculateDecs6($currArray,"price","xPrice"); ?>

	function checkFields() {
		return true;
	}

    function presentValue(value,dp,pt,mt,at) {
    	if (value < 0) {
    		isMinus = "-";
    	} else {
    		isMinus = "";
    	}
    	value = eval(Math.abs(value));
        if(value<=0.9999) {
            newPounds='0';
        } else {
            newPounds=parseInt(value);
        }
        dec='1';
        for (var i=1; i<=dp;i++) {
            dec=dec+'0';
        }
        if (value>0) {
            newPence=Math.round((eval(value)+.000008 - newPounds)*(eval(dec)));
        } else {
            newPence=0;
        }
        compstring='9';
        for (var i=1; i <=dp-1;i++) {
            if (eval(newPence) <= eval(compstring)) newPence='0'+newPence;
            compstring=compstring+'9';
        }
        if (dp>0) {
            if (newPence==eval(dec)) { newPounds++; newPence=0; }
            newString=isMinus+pt+newPounds+mt+newPence+at;
        } else {
            newString=isMinus+pt+newPounds+at;
        }
        return (newString);
    }	
	
</script>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title"><?php print $pageTitle; ?></td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","shipping_rates_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">From <?php print $calcType; ?></td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xFrom",15,10,@getGENERIC("sfrom",$uRecord),"decimal"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">To <?php print $calcType; ?></td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTo",15,10,@getGENERIC("sto",$uRecord),"decimal"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">All/Others</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xAllOthers",$allOthers,"01"); ?><br><font class="normaltext">(If YES is selected, From and To will be ignored)</font></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Price (<?php print $fmType; ?>)</td>
		<td class="table-list-entry1" valign="top">
			<?php $myForm->createPricingFieldsDecs6($currArray,@$uRecord,"price","xPrice"); ?>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Zone</td>
		<td class="table-list-entry1" valign="top">
			<select name="xZoneID" class="form-inputbox">
<?php
				$selectedZone = makeInteger(@getGENERIC("zoneID",$uRecord));
				for ($f = 0; $f < count($zoneArray); $f++) {
					$thisSelected = "";
					if ($selectedZone == $zoneArray[$f]["zoneID"]) {
						$thisSelected = "SELECTED";
					}
?>
				<option value="<?php print $zoneArray[$f]["zoneID"]; ?>" <?php print $thisSelected; ?>><?php print $zoneArray[$f]["name"]; ?></option>
<?php				
				}
?>
			</select>
		</td>
	</tr>		
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
<script language="JavaScript"><?php print $recalculator; ?></script>
</BODY>
<?php
	$dbA->close();
?>
