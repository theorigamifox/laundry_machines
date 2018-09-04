<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	
	$xType=getFORM("xType");
	if ($xType=="new") {
		$pageTitle = "Add New Offer Code";
		$submitButton = "Insert Offer Code";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>".hiddenReturnPOST();

		$xLoginEnabledYes = "CHECKED";
		$xLoginEnabledNo = "";	
		$uRecord["expiryDate"] = "N";	
		$uRecord["excludeShipping"] = "N";	
	}
	if ($xType=="edit") {
		$xOfferID = getFORM("xOfferID");
		$pageTitle = "Edit Existing Offer Code";
		$submitButton = "Update Offer Code";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xOfferID' value='$xOfferID'>".hiddenReturnPOST();
		$uResult = $dbA->query("select * from $tableOfferCodes where offerID='$xOfferID'");	
		$uRecord = $dbA->fetch($uResult);
	}
	
	$myForm = new formElements;

	$statusArray[] = array("value"=>"A","text"=>"Activated");
	$statusArray[] = array("value"=>"N","text"=>"Not Activated");	
	
	$currArray = $dbA->retrieveAllRecordsFromQuery("select * from $tableCurrencies order by currencyID");
	for ($f = 0; $f < count($currArray); $f++) {
		if ($currArray[$f]["checkout"] == "Y") {
			$currGoodArray[] = array("value"=>$currArray[$f]["currencyID"],"text"=>$currArray[$f]["name"]);
		}
		if ($currArray[$f]["currencyID"] == @$uRecord["currencyID"]) {
			$selectedCurrency = $currArray[$f]["name"];
		}
	}
	$currGoodArray[]  = array("value"=>0,"text"=>"% Percent");
	
	if ($uRecord["expiryDate"] == "N") {
		$theExpiry = "N";
		$uRecord["expiryDate"] = date("Ymd");
	} else {
		$theExpiry = "Y";
	}
	$expiryArray[] = array("value"=>"N","text"=>"No");
	$expiryArray[] = array("value"=>"Y","text"=>"Yes On Date -&gt;");	

	$accTypeArray = $dbA->retrieveAllRecords($tableCustomersAccTypes,"accTypeID");
	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	
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
	<?php $recalculator = $myForm->createCurrencyRecalculate($currArray,"level","xLevel"); ?>

	function checkFields() {
		if (document.detailsForm.xAmount.value == "") {
			rc=alert("Please enter an amount above 0");
			return false;
		}
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
<?php $myForm->createForm("detailsForm","offercodes_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Code</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCode",30,30,@$uRecord["code"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Amount</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xAmount",8,10,@$uRecord["amount1"],"decimal"); ?>
		<?php
			if ($xType == "new") {
		?>
		&nbsp;<?php $myForm->createSelect("xCurrencyID",@$uRecord["currencyID"],"BOTH",$currGoodArray); ?>
		<?php
			} else {
		?>
		&nbsp;<?php if ($uRecord["currencyID"] != 0) { print @$selectedCurrency; } else { print "% Percent"; } ?>
		<?php
			}
		?>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Expires</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xExpires",@$theExpiry,"BOTH",$expiryArray); ?>
		&nbsp;
		<select name="xDay" class="form-inputbox">
		<?php
			$tDay = substr(@$uRecord["expiryDate"],6,2);
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
		</select>&nbsp;<select name="xMonth" class="form-inputbox">
		<?php
			$tMonth = substr(@$uRecord["expiryDate"],4,2);
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
		</select>&nbsp;<select name="xYear" class="form-inputbox">
		<?php
			$thisYear = substr(@$uRecord["expiryDate"],0,4);
			for ($f = 2003; $f <= date("Y")+1; $f++) {
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
		<td class="table-list-title" valign="top">Allow Multiple Use By Same Person</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xMultiple",@getGENERIC("multiple",$uRecord),"YN"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Exclude Shipping From Discount</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xExcludeShipping",@getGENERIC("excludeShipping",$uRecord),"YN"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Only Allow If Goods Total &gt;</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createPricingFields($currArray,@$uRecord,"level","xLevel"); ?></td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
<script language="JavaScript"><?php print $recalculator; ?></script>
</BODY>
</HTML>
<?php
	$dbA->close();
?>