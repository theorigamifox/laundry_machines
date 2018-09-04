<?php
?>

<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">SecureHosting SH Reference Number</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xSHreference",20,50,$gatewayOptions["shreference"],"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">SecureHosting Check Code</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCheckCode",15,32,$gatewayOptions["checkcode"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Template File Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xFilename",25,60,$gatewayOptions["filename"],"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
