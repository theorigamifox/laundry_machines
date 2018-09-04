<?php
	$countryArray[] = array("value"=>"31","text"=>"Netherlands");
	$countryArray[] = array("value"=>"32","text"=>"Belgie");
	$countryArray[] = array("value"=>"1","text"=>"United States");
	$countryArray[] = array("value"=>"44","text"=>"Great Britain");
	$countryArray[] = array("value"=>"99","text"=>"Any");


	$languageArray[] = array("value"=>"NL","text"=>"NL");
	$languageArray[] = array("value"=>"EN","text"=>"EN");
	$languageArray[] = array("value"=>"FR","text"=>"FR");
?>

<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Administrator ID (free field)</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xAdminID",10,10,$gatewayOptions["adminID"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Seller ID from Multipay</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xSellerID",10,15,$gatewayOptions["sellerID"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Order Description</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xDescription",60,200,$gatewayOptions["description"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Country</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xCountry",$gatewayOptions["country"],"BOTH",$countryArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Language</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xLanguage",$gatewayOptions["language"],"BOTH",$languageArray); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Payment Types Allowed</td>
		<td class="table-list-entry1" valign="top">
			<input type="checkbox" name="xPT1" value="V" <?php if (strrpos($gatewayOptions["allowpayment"],"V") !== false) { echo "CHECKED"; } ?>>Visa<br>
			<input type="checkbox" name="xPT2" value="E" <?php if (strrpos($gatewayOptions["allowpayment"],"E") !== false) { echo "CHECKED"; } ?>>Mastercard<br>
			<input type="checkbox" name="xPT3" value="T" <?php if (strrpos($gatewayOptions["allowpayment"],"T") !== false) { echo "CHECKED"; } ?>>Teletik safepay<br>
			<input type="checkbox" name="xPT4" value="O" <?php if (strrpos($gatewayOptions["allowpayment"],"O") !== false) { echo "CHECKED"; } ?>>Overboeking<br>
			<input type="checkbox" name="xPT5" value="I" <?php if (strrpos($gatewayOptions["allowpayment"],"I") !== false) { echo "CHECKED"; } ?>>Incasso<br>
			<input type="checkbox" name="xPT6" value="C" <?php if (strrpos($gatewayOptions["allowpayment"],"C") !== false) { echo "CHECKED"; } ?>>Acceptgiro<br>
			<input type="checkbox" name="xPT7" value="R" <?php if (strrpos($gatewayOptions["allowpayment"],"R") !== false) { echo "CHECKED"; } ?>>Rabodirect<br>
		</td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
