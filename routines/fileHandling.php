<?php
	class fileHandling {

		//constructor class
		function fileHandling() {
		
		}
		
		function writeFile($vFilename,$vContents) {
			$fp = fopen($vFilename,"w");
			fputs($fp, $vContents, strlen($vContents));
			fclose($fp);
		}

		function openFile($vFilename) {
			$fp = @fopen($vFilename,"r");
			if ($fp == FALSE) { return false; }
			$buffer = "";
			while (!feof($fp)) {
				$buffer = $buffer . fread($fp,1024);
			}
			fclose($fp);
			return $buffer;
		}

	}
?>