<?php
	$typeArray[] = array("value"=>"S","text"=>"Sale");
	$typeArray[] = array("value"=>"A","text"=>"Authorisation");
	$typeArray[] = array("value"=>"D","text"=>"Delayed Capture");
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
		<td class="table-list-title" valign="top">Transaction Type</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xType",$gatewayOptions["type"],"BOTH",$typeArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xDescription",50,200,$gatewayOptions["description"],"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">URLs For Verisign AUS</td>
		<td class="table-list-entry1" valign="top">
		Return URL: <?php print $jssStoreWebDirHTTP."gateways/response/verisign_aus_return.php"; ?>
		<br>Silent POST URL: <?php print $jssStoreWebDirHTTP."gateways/response/verisign_aus.php"; ?>
		</td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
