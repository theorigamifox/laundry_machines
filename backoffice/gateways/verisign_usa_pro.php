<?php
	$typeArray[] = array("value"=>"S","text"=>"Sale");
	$typeArray[] = array("value"=>"A","text"=>"Authroisation");
?>

<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Login ID</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xLogin",20,50,$gatewayOptions["login"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Partner</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xPartner",20,50,$gatewayOptions["partner"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Password</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createPassword("xPassword",20,50,$gatewayOptions["password"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Transaction Type</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xType",$gatewayOptions["type"],"BOTH",$typeArray); ?></td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
