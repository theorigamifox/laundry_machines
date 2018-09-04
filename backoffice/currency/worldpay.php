<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Worldpay Installation ID</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xInstallationID",20,250,$lrOptions[0],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Worldpay Info Password</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xInfoPswd",50,250,$lrOptions[1],"general"); ?></td>
	</tr>		
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
