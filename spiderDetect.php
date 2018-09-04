<?php
	function spiderDetect() {
		$ipArray = array (	"198.3.103.50","198.3.103.35","198.3.103.56","198.3.103.57","198.3.103.58","198.3.103.59","198.3.103.65",
							"198.3.103.66","198.3.103.68","198.3.103.69","198.3.103.70","198.3.103.72","198.3.103.81","198.3.103.84",
							"198.3.103.93","198.3.103.97","204.62.245.167","204.62.245.187","204.62.245.32","198.3.103.105","198.3.103.108",
							"198.3.103.112","198.3.103.60","199.172.149.131","199.172.149.132","199.172.149.138","199.172.149.139",
							"199.172.149.140","199.172.149.141","199.172.149.142","199.172.149.143","199.172.149.144","199.172.149.161",
							"204.62.245.178"
							);
		$agentArray = array("ArchitextSpider","Googlebot","TeomaAgent","Zyborg","Gulliver","Architext spider","FAST-WebCrawler",
							"Slurp","Ask Jeeves","ia_archiver","Scooter","Mercator","crawler@fast","Crawler","InfoSeek Sidewinder",
							"almaden.ibm.com","appie 1.1","augurfind","baiduspider","bannana_bot","bdcindexer","docomo",
							"frooglebot","geobot","henrythemiragorobot","sidewinder","lachesis","moget/1.0","nationaldirectory-webspider",
							"naverrobot","ncsa beta","netresearchserver","ng/1.0","osis-project","polybot","pompos","seventwentyfour",
							"steeler/1.3","szukacz","teoma","turnitinbot","vagabondo","zao/0","zyborg/1.0","Lycos_Spider_(T-Rex)",
							"Lycos_Spider_Beta2(T-Rex)","Fluffy the Spider","Ultraseek","MantraAgent","Moget","T-H-U-N-D-E-R-S-T-O-N-E",
							"MuscatFerret","VoilaBot","Sleek Spider","KIT_Fireball","WISEnut","WebCrawler","asterias2.0","suchtop-bot","YahooSeeker",
							"ai_archiver","Jetbot","msnbot","Gigabot","aipbot","abot"
							);
							
		$theIP = $_SERVER["REMOTE_ADDR"];
		$theAgent = $_SERVER["HTTP_USER_AGENT"];

		for ($f = 0; $f < count($agentArray); $f++) {
			if (strpos(" ".strtolower($theAgent),strtolower($agentArray[$f])) != false) {
				return true;
			}
		}

		for ($f = 0; $f < count($ipArray); $f++) {
			if (strpos(" ".$theIP,$ipArray[$f]) != false) {
				return true;
			}
		}		
		
		return false;
	}
?>