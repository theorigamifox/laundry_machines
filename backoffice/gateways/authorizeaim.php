<?php
	$testArray[] = array("value"=>"TRUE","text"=>"Yes");
	$testArray[] = array("value"=>"FALSE","text"=>"No");

	$authArray[] = array("value"=>"AUTH_CAPTURE","text"=>"Full Authorization");
	$authArray[] = array("value"=>"AUTH_ONLY","text"=>"Pre Authorization");
?>

<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Login ID</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xLoginID",20,50,$gatewayOptions["x_login"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Password</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createPassword("xXpassword",20,50,$gatewayOptions["x_password"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Test Mode</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xTestRequest",$gatewayOptions["x_test_request"],"BOTH",$testArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Transaction Mode</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xType",$gatewayOptions["type"],"BOTH",$authArray); ?></td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
