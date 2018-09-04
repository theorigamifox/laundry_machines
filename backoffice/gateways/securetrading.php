<?php
	$emailArray[] = array("value"=>"1","text"=>"Yes");
	$emailArray[] = array("value"=>"0","text"=>"No");
?>

<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Merchant ID</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMerchantID",20,50,$gatewayOptions["merchantID"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Email Customer Confirmation</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xEmailconf",$gatewayOptions["emailconf"],"BOTH",$emailArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Merchant Email Address</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xEmail",40,250,$gatewayOptions["email"],"email"); ?></td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
