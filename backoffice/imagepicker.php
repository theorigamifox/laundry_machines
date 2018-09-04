<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$xStartDir = getFORM("xStartDir");
	$xPickerField = getFORM("xPickerField");
	$xSearchString = getFORM("xSearchString");
	$xOffset = getFORM("xOffset");
	if ($xOffset == "") { $xOffset = 0; }
?>
<HTML>
<HEAD>
<TITLE>Image Picker...</TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript">
	function pickImage(theImage) {
		<?php print $xPickerField; ?> = theImage;
		self.close();
	}
	
	function goSearch() {
		self.location.href = "imagepicker.php?xStartDir=<?php print $xStartDir; ?>&xPickerField=<?php print $xPickerField; ?>&xSearchString="+document.searchForm.xSearchString.value+"&<?php print userSessionGET(); ?>";
		return false;
	}
</script>
</HEAD>
<BODY class="detail-body" link="#000000" alink="#FF0000" vlink="#000000" onLoad="self.focus()">
<center>
<table width="100%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="table-list-title" align="left" valign="top">
		<table width="100%" cellpadding="0" cellspacing="2" border="0">
			<form name="searchForm" onSubmit="return goSearch();">
			<tr>
				<td align="center" valign="top">
					<font class="boldtext">Directory: </font><font class="normaltext"><?php print $xStartDir; ?>/</font>
				</td>
			</tr>
			<tr>
				<td align="center" valign="top">
					<input type="text" name="xSearchString" value="<?php print $xSearchString; ?>" size="20" class="form-inputbox">
					&nbsp;<button id="buttonSearch" class="button-expand" onClick="goSearch();">SEARCH</button>
				</td>
			</tr>
			</form>
			</table>				
		</td>
	</tr>
	</table><img src="images/spacer.gif" border="0" width="300" height="3"><table cellpadding="2" cellspacing="0" class="table-list" width="100%">
				<?php
					$myDir = opendir($xStartDir);
					$dirFileArray = "";
					$dirDateArray = "";
					$dirSizeArray = "";
					while (false !== ($file = readdir($myDir))) {
						if (substr($file,strlen($file)-4,4) == ".jpg" || substr($file,strlen($file)-4,4) == ".gif") {
							if ($xSearchString == "" || ($xSearchString != "" && eregi($xSearchString,$file))) {
								$fileSize = filesize("$xStartDir/$file");
								$dirFileArray[] = $file;
								$dirDateArray[$file] = date("d/m/Y",filemtime("$xStartDir/$file"));
								$dirSizeArray[$file] = number_format($fileSize/1024,1);
							}
						}
					}
					if (is_array($dirFileArray)) {
						$perpage = 25;
						$totalImages = count($dirFileArray);
						$upperCount = $xOffset+$perpage;
						$lowerCount = $xOffset+1;
						$middleButtons = "Images: <b>$totalImages</b>, Viewing: <b>$lowerCount - ".$upperCount."</b>&nbsp;";

						if ($xOffset-$perpage > -1) {
							$pOffset = $xOffset - $perpage;
							$previousButton = "<button id=\"buttonPrev\" class=\"button-action\" onMouseOver=\"buttonOn(this)\" onMouseOut=\"buttonOff(this)\" onClick=\"self.location.href='imagepicker.php?xSearchString=$xSearchString&xStartDir=$xStartDir&xPickerField=$xPickerField&".userSessionGET()."&xOffset=$pOffset'\">&lt; PREV</button>";
							$previousButton .= "&nbsp;<button id=\"buttonTop\" class=\"button-action\" onMouseOver=\"buttonOn(this)\" onMouseOut=\"buttonOff(this)\" onClick=\"self.location.href='imagepicker.php?xSearchString=$xSearchString&xStartDir=$xStartDir&xPickerField=$xPickerField&".userSessionGET()."&xOffset=0'\">[TOP]</button>";
						} else {
							$previousButton = "";
						}
						if ($xOffset+$perpage < $totalImages) {
							$nOffset = $xOffset + $perpage;
							$nextButton = "<button id=\"buttonNext\" class=\"button-action\" onMouseOver=\"buttonOn(this)\" onMouseOut=\"buttonOff(this)\" onClick=\"self.location.href='imagepicker.php?xSearchString=$xSearchString&xStartDir=$xStartDir&xPickerField=$xPickerField&".userSessionGET()."&xOffset=$nOffset'\">NEXT &gt;</button>";
						} else {
							$nextButton = "";
						}
						if ($previousButton=="" && $nextButton=="") {
							$navButtons = "";
						}
						if ($previousButton=="" && $nextButton!="") {
							$navButtons = $nextButton."&nbsp;";
						}
						if ($previousButton!="" && $nextButton=="") {
							$navButtons = $previousButton."&nbsp;";
						}
						if ($previousButton!="" && $nextButton!="") {
							$navButtons = $previousButton."&nbsp;".$nextButton."&nbsp;";
						}	

						
?>
	<tr>
		<td colspan="3" class="table-white-no-border" align="right"><?php print $middleButtons; ?> <?php print @$navButtons; ?></td>
	</tr>
	<tr>
		<td class="table-list-title">Name</td>
		<td class="table-list-title">Date</td>
		<td class="table-list-title" align="right">Size</td>
	</tr>	
<?php						
						sort($dirFileArray);
						$finalCount = $xOffset + $perpage;
						if ($finalCount > count($dirFileArray)) { $finalCount = count($dirFileArray); }
						for ($f = $xOffset; $f < $finalCount; $f++) {
							$file = $dirFileArray[$f];
							$fileDate = @$dirDateArray[$file];
							$fileSize = @$dirSizeArray[$file];
?>
				<tr>
					<td class="table-list-entry1"><a href="javascript:pickImage('<?php print $file; ?>');"><?php print $file; ?></a></td>
					<td class="table-list-entry1"><?php print $fileDate; ?></td>
					<td class="table-list-entry1" align="right"><?php print $fileSize; ?>kb</td>
				</tr>
<?php						}
					}
?>
					
	<!--<tr>
		<td colspan="1" class="table-list-title">Total Number of Categories:</td>
		<td class="table-list-title" align="right"><?php print $uCount; ?></td>
	</tr>-->
</table>
</center>
</BODY>
</HTML>
