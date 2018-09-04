<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$xStartDir = getFORM("xStartDir");
	if ($xStartDir == "") {
		$xStartDir = $jssShopFileSystem."templates/";
	}
	$upOneDir = "";
	$dirBits = split("/",$xStartDir);
	for ($f = 0; $f < count($dirBits)-2; $f++) {
		$upOneDir .= $dirBits[$f]."/";
	}
?>
<HTML>
<HEAD>
<TITLE>Image Picker...</TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript">
	function pickFile(theFile) {
		parent.showFile("<?php print $xStartDir; ?>"+theFile,"<?php print $xStartDir; ?>");
	}
	
	function refreshList() {
		self.location.href = "templates_navigate.php?xStartDir=<?php print $xStartDir; ?>&<?php print userSessionGET(); ?>";
	}
</script>
</HEAD>
<BODY class="dir-body" link="#000000" alink="#FF0000" vlink="#000000" marginheight="0" marginwidth="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
				<?php

					$myDir = opendir($xStartDir);
					if (@$myDir == false) {

						echo $xStartDir;

						?>
						<a href="templates_navigate.php?<?php print userSessionGET(); ?>">Cannot open directory - click here to go back to template route.<br></a>
						<?php
						exit;
					}
					if ($jssShopFileSystem != $xStartDir) {
				?>
				<A href="templates_navigate.php?xStartDir=<?php print $upOneDir; ?>&<?php print userSessionGET(); ?>">..</a><br>
				<?php
					}
				?>
				<?php
					$dirFileArray = "";
					$dirDateArray = "";
					$dirSizeArray = "";
					while (false !== ($file = readdir($myDir))) {
						if (is_dir($xStartDir.$file) && ($file != "." && $file != "..")) {
							$dirFileArray[] = array($file,"D");
						}
						if (substr($file,strlen($file)-5,5) == ".html") {
							$fileSize = filesize("$xStartDir/$file");
							$dirFileArray[] = array($file,"F");
						}
						if (substr($file,strlen($file)-4,4) == ".css") {
							$fileSize = filesize("$xStartDir/$file");
							$dirFileArray[] = array($file,"F");
						}
					}
					if (is_array($dirFileArray)) {			
						sort($dirFileArray);
						for ($f = 0; $f < count($dirFileArray); $f++) {
							$file = $dirFileArray[$f][0];
							$ftype = $dirFileArray[$f][1];
							if ($ftype == "F") {
?>
				<a href="javascript:pickFile('<?php print $file; ?>');" class="dirtext"><?php print $file; ?></a><br>

<?php						
							} else {
?>
				<a href="templates_navigate.php?xStartDir=<?php print $xStartDir.$file."/"; ?>&<?php print userSessionGET(); ?>"><?php print $file; ?></a><br>

<?php						
							}
						}
					}
?>
</BODY>
</HTML>
