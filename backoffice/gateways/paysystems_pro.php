<?php
?>

<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">PaySystems Merchant ID</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCompanyid",20,50,$gatewayOptions["companyid"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Payment Page Order Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xDescription",60,250,$gatewayOptions["description"],"general"); ?></td>
	</tr>		
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
