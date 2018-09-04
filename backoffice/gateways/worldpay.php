<?php
	$testArray[] = array("value"=>"100","text"=>"Test Mode (Success)");
	$testArray[] = array("value"=>"101","text"=>"Test Mode (Failure)");
	$testArray[] = array("value"=>"","text"=>"Live Mode");


	$authArray[] = array("value"=>"A","text"=>"Full Authorisation (AUTH)");
	$authArray[] = array("value"=>"E","text"=>"Pre Authorisation (DEFT)");
?>

<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Installation ID</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xInstallationID",20,50,$gatewayOptions["installationID"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xDescription",50,250,$gatewayOptions["description"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Transaction Mode</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xTestmode",$gatewayOptions["testmode"],"BOTH",$testArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Authorisation Mode</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xAuthmode",$gatewayOptions["authmode"],"BOTH",$authArray); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Callback Password</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCallbackpassword",20,32,$gatewayOptions["callbackpassword"],"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
