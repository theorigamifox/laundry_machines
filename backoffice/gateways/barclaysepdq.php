<?php
	$authArray[] = array("value"=>"Auth","text"=>"Full Authorisation (Auth)");
	$authArray[] = array("value"=>"PreAuth","text"=>"Pre Authorisation (PreAuth)");
	
	$cardsArray[] = array("value"=>"127","text"=>"All Cards");
	$cardsArray[] = array("value"=>"125","text"=>"All Cards Execpt American Express");
	$cardsArray[] = array("value"=>"65","text"=>"Visa &amp; Electron");

	$clientIDs = explode("|",$gatewayOptions["clientid"]);
	$curBits = "";
	for ($f = 0; $f < count($currArray); $f++) {
		$curBits[$currArray[$f]["currencyID"]] = 1;
		for ($g = 0; $g < count($clientIDs); $g++) {
			$thisPage = explode(":",$clientIDs[$g]);
			if ($thisPage[0] == $currArray[$f]["currencyID"]) {
				$curBits[$currArray[$f]["currencyID"]] = $thisPage[1];
			}
		}
	}	
?>

<table cellpadding="2" cellspacing="0" class="table-list">
	<?php
		for ($f = 0; $f < count($currArray); $f++) {
			if ($currArray[$f]["checkout"] == "Y") {
	?>
	<tr>
		<td class="table-list-title" valign="top">Client ID <?php print $currArray[$f]["code"]; ?></td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xClientid".$currArray[$f]["currencyID"],20,50,@$curBits[$currArray[$f]["currencyID"]],"general"); ?></td>
	</tr>	
	<?php
			}
		}
	?>
	<tr>
		<td class="table-list-title" valign="top">Charge Type</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xChargetype",$gatewayOptions["chargetype"],"BOTH",$authArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Passphrase</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xPassword",20,20,$gatewayOptions["password"],"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Merchant Display Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xMerchantdisplayname",30,40,$gatewayOptions["merchantdisplayname"],"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Payment Page Text Colour</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCPItext",10,7,$gatewayOptions["cpi_textcolor"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Payment Page Background Colour</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCPIbg",10,7,$gatewayOptions["cpi_bgcolor"],"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Payment Page Logo</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCPIlogo",50,100,$gatewayOptions["cpi_logo"],"general"); ?><br>Only use if you have a secure server and start with https://.<br>Leave blank for default Barclays logo.</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Supported Card Types</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xSupportedcardtypes",$gatewayOptions["supportedcardtypes"],"BOTH",$cardsArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Settings For Barclays</td>
		<td class="table-list-entry1" valign="top">URL Settings for <a href="https://secure2.epdq.co.uk/cgi-bin/CcxBarclaysEpdqAdminTool.e" target="_new">ePDQ CPI Administration</a>:
		<br>Allowed URL: <?php print $jssStoreWebDirHTTP."process.php"; ?>
		<br>POST URL: <?php print $jssStoreWebDirHTTP."gateways/barclays/barclaysepdq.php"; ?>
		</td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
