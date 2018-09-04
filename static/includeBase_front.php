<?php
	$timeStart = microtime();
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
	//header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	define("IN_JSHOP", TRUE);

	$inCheckoutPhase = false;
	
	set_magic_quotes_runtime(0);

	if (get_magic_quotes_gpc()) {
		foreach($HTTP_GET_VARS as $k=>$v) {
			$HTTP_GET_VARS[$k] = stripslashes($v);
		}
		foreach($HTTP_POST_VARS as $k=>$v) {
			$HTTP_POST_VARS[$k] = stripslashes($v);
		}
		foreach($HTTP_COOKIE_VARS as $k=>$v) {
			$HTTP_COOKIE_VARS[$k] = stripslashes($v);
		}
	}
	
	//@error_reporting (E_ERROR); 
	@error_reporting (0); 
	
	$trans_tbl = get_html_translation_table (HTML_ENTITIES); 
   	$trans_tbl = array_flip ($trans_tbl); 
	
	function unhtmlentities ($string)  {
		global $trans_tbl;
	    $ret = strtr ($string, $trans_tbl);
	    return preg_replace('/&#(\d+);/me',
	      "chr('\\1')",$ret);
	}

	include("./static/config.php");
	include("./routines/dbAccess_mysql.php");
	include("./routines/taxOperations.php");
	include("./routines/general.php");
	include("./routines/spiderDetect.php");
	include("./routines/cart.php");
	include("./routines/logSys.php");
	include("./routines/tSys.php");

?>
