<?php
	$testArray[] = array("value"=>"Y","text"=>"Test Mode");
	$testArray[] = array("value"=>"N","text"=>"Live Mode");


	$authArray[] = array("value"=>"PAYMENT","text"=>"Full Authorisation");
	$authArray[] = array("value"=>"DEFERRED","text"=>"Deferred");
?>

<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Vendor ID</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xVendor",20,50,$gatewayOptions["vendor"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Encryption Password</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xEncryptionPassword",20,50,$gatewayOptions["encryptionPassword"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xDescription",50,250,$gatewayOptions["description"],"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Transaction Mode</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xTestMode",$gatewayOptions["testMode"],"BOTH",$testArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Authorisation Mode</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xTxType",$gatewayOptions["txType"],"BOTH",$authArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Vendor Email</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xVendorEmail",35,250,$gatewayOptions["vendorEmail"],"email"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Send Customer Email From Protx</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xSendEmail",$gatewayOptions["sendEmail"],"01"); ?></td>
	</tr>		
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
