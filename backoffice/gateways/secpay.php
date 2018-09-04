<?php
	$testArray[] = array("value"=>"T","text"=>"Test Mode (Success)");
	$testArray[] = array("value"=>"F","text"=>"Test Mode (Failure)");
	$testArray[] = array("value"=>"O","text"=>"Live Mode");


	$authArray[] = array("value"=>"AUTH","text"=>"Full Authorisation (AUTH)");
	$authArray[] = array("value"=>"DEFT","text"=>"Deferred (DEFT)");
	$authArray[] = array("value"=>"DEFF","text"=>"Deferred Full (DEFF)");
	$authArray[] = array("value"=>"REUSE","text"=>"Deferred Reuse (REUSE)");
?>

<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Merchant ID</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMerchant",20,50,$gatewayOptions["merchant"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Transaction Mode</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xTestStatus",$gatewayOptions["test_status"],"BOTH",$testArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Authorisation Mode</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xAuthType",$gatewayOptions["authType"],"BOTH",$authArray); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Digest Key</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xDigestKey",20,32,$gatewayOptions["DigestKey"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Custom Template</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTemplate",40,50,$gatewayOptions["template"],"general"); ?><br>(leave blank to use standard SecPay template,<br>alternatively just enter the template name, e.g. mytemplate.html)</td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
