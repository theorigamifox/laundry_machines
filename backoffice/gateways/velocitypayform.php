<?php
	$countryArray[] = array("value"=>"826","text"=>"United Kingdom");
?>

<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Merchant ID</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xVPMerchantID",20,50,$gatewayOptions["VPMerchantID"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Merchant Password</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xVPMerchantPassword",20,50,$gatewayOptions["VPMerchantPassword"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Merchant Location</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xVPCountryCode",$gatewayOptions["VPCountryCode"],"BOTH",$countryArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Integration URL</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xFormURL",50,250,$gatewayOptions["formURL"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
