<?php
	$languageArray[] = array("value"=>"1","text"=>"Italian");
	$languageArray[] = array("value"=>"2","text"=>"English");
	$languageArray[] = array("value"=>"3","text"=>"Spanish");
	$languageArray[] = array("value"=>"4","text"=>"French");
	$languageArray[] = array("value"=>"5","text"=>"German");

?>

<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Account ID</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xAccountID",30,50,$gatewayOptions["accountID"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Saferpay Server Path</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xPath",40,150,$gatewayOptions["path"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xDescription",30,100,$gatewayOptions["description"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
