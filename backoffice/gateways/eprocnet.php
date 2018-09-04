<?php
?>

<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Account Number / Username</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xEPNAccount",20,50,$gatewayOptions["ePNAccount"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Background Color</td>
		<td class="table-list-entry1" valign="top">#<?php $myForm->createText("xBackgroundColor",10,6,$gatewayOptions["BackgroundColor"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Text Color</td>
		<td class="table-list-entry1" valign="top">#<?php $myForm->createText("xTextColor",10,6,$gatewayOptions["TextColor"],"general"); ?></td>
	</tr>		
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
