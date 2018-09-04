<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Account</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xAccount",40,250,$gatewayOptions["account"],"email"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xDescription",50,250,$gatewayOptions["description"],"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Logo (Should be https://)</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xLogo",50,250,$gatewayOptions["logo"],"general"); ?></td>
	</tr>		
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
