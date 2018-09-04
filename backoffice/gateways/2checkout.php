<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Account Number</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xAccountNumber",10,50,$gatewayOptions["accountNumber"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Secret Word</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xSecretword",15,50,$gatewayOptions["secretword"],"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
