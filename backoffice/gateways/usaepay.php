<?php
	$testArray[] = array("value"=>"Y","text"=>"Test Mode");
	$testArray[] = array("value"=>"N","text"=>"Live Mode");


	$emailArray[] = array("value"=>"Y","text"=>"Yes: USA ePay will email customer a confirmation");
	$emailArray[] = array("value"=>"","text"=>"No: USA ePay won't email customer a confirmation");
	
	$authArray[] = array("value"=>"sale","text"=>"Sale");
	$authArray[] = array("value"=>"preauth","text"=>"Pre-Auth");	

?>

<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Source Key</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xSourceKey",40,100,$gatewayOptions["sourceKey"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Transaction Mode</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xTestmode",$gatewayOptions["testmode"],"BOTH",$testArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Authorisation Mode</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xAuthCommand",$gatewayOptions["command"],"BOTH",$authArray); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Email Customer</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xEmailcustomer",$gatewayOptions["emailcustomer"],"BOTH",$emailArray); ?></td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
