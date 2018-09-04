<?php
	function giftCertValid($giftCert,$currentGiftCertList) {
		global $dbA,$tableGiftCertificates,$tableGiftCertificatesTrans,$cartMain;
		$giftCerts = split("\|",$currentGiftCertList);
		if ($giftCert == "") { 	return "INVALID"; }
		for ($f = 0; $f < count($giftCerts); $f++) {
			if ($giftCerts[$f] == $giftCert) {
				return "DUPLICATE";
			}
		}
		$result = $dbA->query("select * from $tableGiftCertificates where certSerial='$giftCert' and status='A'");
		$count = $dbA->count($result);
		if ($count == 0) {
			return "INVALID";
		}
		$record = $dbA->fetch($result);
		$tDate = date("Ymd");
		if ($record["expiryDate"] != "N" && $record["expiryDate"] < $tDate) {
			return "EXPIRED";
		}
		if ($cartMain["currencyID"] != $record["currencyID"]) {
			return "CURRENCY";
		}
		return "OK";
	}
	function giftCertValueLeft($giftCert) {
		global $dbA,$tableGiftCertificates,$tableGiftCertificatesTrans,$cartMain;
		$result = $dbA->query("select * from $tableGiftCertificates where certSerial='$giftCert'");
		if ($dbA->count($result) == 0) { return 0; }
		$certRecord = $dbA->fetch($result);
		$certValue = $certRecord["certValue"];
		$result = $dbA->query("select sum(amount) as totalSpent from $tableGiftCertificatesTrans where certSerial = '$giftCert' group by certSerial");
		$count = $dbA->count($result);
		if ($count == 0) {
			$totalSpent = 0;
		} else {
			$record = $dbA->fetch($result);
			$totalSpent = $record["totalSpent"];
		}
		return $certValue - $totalSpent;
	}
	
	function calculateGiftCertTotal($giftCerts) {
		$giftCerts = split("\|",$giftCerts);
		if ($giftCerts == "") { return 0; }
		$certTotal = 0;
		for ($f = 0; $f < count($giftCerts); $f++) {
			$certTotal = $certTotal + giftCertValueLeft($giftCerts[$f]);
		}		
		return $certTotal;
	}
	
	function allocateGiftCertificate($giftCert,$theAmount,$orderID) {
		global $dbA,$tableGiftCertificatesTrans;
		$rArray[] = array("certSerial",$giftCert,"S");
		$rArray[] = array("orderID",$orderID,"N");
		$rArray[] = array("type","A","S");
		$rArray[] = array("amount",$theAmount,"D");
		$dbA->insertRecord($tableGiftCertificatesTrans,$rArray,0);
		return true;
	}
?>
