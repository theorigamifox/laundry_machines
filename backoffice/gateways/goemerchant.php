<?php
?>

<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">goEmerchant Merchant Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMerchantName",20,50,$gatewayOptions["merchantname"],"general"); ?></td>
	</tr>		
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
