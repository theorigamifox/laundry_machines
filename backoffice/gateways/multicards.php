<?php
	$authArray[] = array("value"=>"0","text"=>"Normal");
	$authArray[] = array("value"=>"1","text"=>"Deferred");
	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	$pageIDs = explode("|",$gatewayOptions["pageIDs"]);
	$curBits = "";
	for ($f = 0; $f < count($currArray); $f++) {
		$curBits[$currArray[$f]["currencyID"]] = 1;
		for ($g = 0; $g < count($pageIDs); $g++) {
			$thisPage = explode(":",$pageIDs[$g]);
			if ($thisPage[0] == $currArray[$f]["currencyID"]) {
				$curBits[$currArray[$f]["currencyID"]] = $thisPage[1];
			}
		}
	}
	
?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Multicards Merchant ID</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMerchantID",40,250,$gatewayOptions["merchantID"],"email"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xDescription",50,250,$gatewayOptions["description"],"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Authorisation Mode</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xDeferred",$gatewayOptions["deferred"],"BOTH",$authArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Silent Post Password</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xPostpassword",15,20,$gatewayOptions["postpassword"],"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Post URL</td>
		<td class="table-list-entry1" valign="top"><b><?php print $jssStoreWebDirHTTPS."gateways/response/multicards.php"; ?></b><br>(This needs to be set as the PostURL property for each of your<br>order pages in the Multicards administration system)</td>
	</tr>	
	<?php
		for ($f = 0; $f < count($currArray); $f++) {
			if ($currArray[$f]["checkout"] == "Y") {
	?>
	<tr>
		<td class="table-list-title" valign="top">Page ID For <?php print $currArray[$f]["code"]; ?></td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xPage".$currArray[$f]["currencyID"],5,3,@$curBits[$currArray[$f]["currencyID"]],"integer"); ?> (Blank or 0 will use default Multicards Page ID of 1)</td>
	</tr>
	<?php
			}
		}
	?>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
