<?php
	$testArray[] = array("value"=>"{TEST}","text"=>"Test Mode (Success)");
	$testArray[] = array("value"=>"{TESTD}","text"=>"Test Mode (Failure)");
	$testArray[] = array("value"=>"","text"=>"Live Mode");


	$languageArray[] = array("value"=>"English","text"=>"English");
	$languageArray[] = array("value"=>"French","text"=>"French");
	$languageArray[] = array("value"=>"Spanish","text"=>"Spanish");
	$languageArray[] = array("value"=>"Japanese","text"=>"Japanese");
?>

<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Merchant Number</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMerchantNumber",20,50,$gatewayOptions["MerchantNumber"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Transaction Mode</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xTestmode",$gatewayOptions["testmode"],"BOTH",$testArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Language</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xLanguage",$gatewayOptions["language"],"BOTH",$languageArray); ?></td>
	</tr>	
	<!--<tr>
		<td class="table-list-title" valign="top">Code</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCode",20,50,$gatewayOptions["code"],"general"); ?></td>
	</tr>-->
	<tr>
		<td class="table-list-title" valign="top">Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xDescription",40,200,$gatewayOptions["description"],"general"); ?></td>
	</tr>		
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
