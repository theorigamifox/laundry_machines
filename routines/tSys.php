<?php
	class tSys {

		var $templateContents;
		var $templateDir;
		var $theVariables;
		var $loopCounters;
		var $baseVar;
		var $baseVarLimited;
		var $looperMatch;
		var $nextForLoop;
		var $startTime;
		var $endTime;
		var $requiredVariables;

		function tSys($templateDir,$templateFile,&$requiredVars,$compileTemplate=0) {
			$this->nextForLoop = 0;
			$this->baseVar="@\$this->theVariables";
			$this->baseVarLimited="@\$this->_RUNTIME_stringLimit(@\$this->theVariables";
			$this->looperMatch = "<?php print \$this->loopCounters[\$variableBits[1]][\$variableBits[2]]; ?>";
			$this->templateDir = $templateDir;
			$this->startTime = microtime();
			
			if ($templateDir=="FROMDB") {
				$this->templateContents = $templateFile;
				$this->templateContents = $this->_findComments($this->templateContents);
				$this->templateContents = $this->_findSets($this->templateContents);
				$this->templateContents = $this->_findLoops($this->templateContents);
				$this->_convertVariables();	
				$this->templateContents = $this->_findIfStatements($this->templateContents);	
				$this->endTime = microtime();
				$requiredVars = @array_keys(@$this->requiredVariables);
				return;
			}				

			$doRecompile = false;
			$useNormal = true;
			if ($compileTemplate==0 || $compileTemplate==2) {
				$fp = @fopen($templateDir.$templateFile,"r");
				if ($fp == false) {
					$this->_returnError($templateFile,"Template file does not exist");
				} else {
					$this->templateContents = fread ($fp, filesize ($templateDir.$templateFile));
					fclose($fp);
				}	
			}
			if ($compileTemplate==1) {
				$fp = @fopen($templateDir."compiled/".$templateFile,"r");
				if ($fp == false) {
					$fp = @fopen($templateDir.$templateFile,"r");
					if ($fp == false) {
						$this->_returnError($templateFile,"Template file does not exist");
					} else {
						$this->templateContents = fread ($fp, filesize ($templateDir.$templateFile));
						fclose($fp);
					}					
					$doRecompile = true;
					$useNormal = true;
				} else {
					$this->templateContents = fread ($fp, filesize ($templateDir."compiled/".$templateFile));
					$this->templateContents = $this->_replaceFormFromFile($this->templateContents);
					$useNormal = false;
					$doRecompile = false;
					fclose($fp);
					$fp = @fopen($templateDir."compiled/".$templateFile.".req","r");
					if ($fp == false) {
						$this->_returnError($templateDir."compiled/".$templateFile.".req","Cannot open compiled .req file - you need to recompile your templates");
						exit;
					} else {
						//this is where we can get the <#form:#> bits and change them.
						
						$tempString = fread ($fp, filesize ($templateDir."compiled/".$templateFile.".req"));
						$tempString = $this->_replaceFormFromFile($tempString);
						fclose($fp);
						eval("?>".$tempString);
					}
				}
			}
			if ($compileTemplate==2) {
				$doRecompile = true;
			}
			if ($useNormal == true) {								
				for ($f = 1; $f <=3; $f++) {
					$this->templateContents = $this->_parseIncludes($this->templateContents);
				}
				$this->templateContents = $this->_findComments($this->templateContents);
				$this->templateContents = $this->_findSets($this->templateContents);
				$this->templateContents = $this->_findLoops($this->templateContents);
				$this->_convertVariables();	
				$this->templateContents = $this->_findIfStatements($this->templateContents);	
				$this->endTime = microtime();
				$this->_replaceFormFromArray();
				$this->templateContents = $this->_replaceFormFromFile($this->templateContents);
				$requiredVars = array_keys($this->requiredVariables);					
			}
			if ($doRecompile == true) {
				//write the file out here and break if there's an error
				$fp = fopen ($templateDir."compiled/".$templateFile, "w"); 
				if ($fp == false) {
					$this->_returnError($templateDir."compiled/".$templateFile,"Cannot create compiled version - check user rights on compiled directory");
					exit;
				} else {
					fwrite($fp,$this->templateContents);
					fclose($fp);
					$fp = fopen ($templateDir."compiled/".$templateFile.".req", "w");
					if ($fp == false) {
						$this->_returnError($templateDir."compiled/".$templateFile.".req","Cannot create compiled .req file - check user rights on compiled directory");
						exit;
					} else {
						if (count($requiredVars) != 0) {
							$outputString = "<"."?php ";
							for ($f=0; $f < count($requiredVars); $f++) {
								$outputString .= " \$requiredVars[] = \"".$requiredVars[$f]."\"; ";								
							}
							$outputString .= " ?".">";
							fwrite($fp,$outputString);
						} else {
							fwrite($fp,"<"."?php \$requiredVars = null; ?".">");
						}
						fclose($fp);
					}
				}
			}
			$this->endTime = microtime();
		}
		
		function _stripTags($theString) {
			$theString = substr(trim($theString),2);
			return trim(substr($theString,0,-2));
		}
		
		function _stripVariable($theString) {
			if (substr($theString,0,1) != "{") { return $theString; }
			$theString = substr(trim($theString),1);
			return trim(substr($theString,0,-1));
		}
		
		function _parseIncludes($theContent) {
			$matchFound = preg_match_all("/(<#\s*include:[a-zA-Z0-9\/\s_\.:]+\s*#>[\r\n]*)/",$theContent,$arrayMatch);
			for ($f = 0; $f < count($arrayMatch[0]); $f++) {
				$origInclude = $arrayMatch[0][$f];
				$include = $this->_stripTags($arrayMatch[0][$f],2);
				$includeBits = split(":",$include);
				if (!file_exists($this->templateDir.$includeBits[1])) {
					$this->_returnError($this->templateDir.$includeBits[1],"Include file does not exist.");
					exit;
				}
				$fp = fopen($this->templateDir.$includeBits[1],"r");
				$includeContents = fread ($fp, filesize ($this->templateDir.$includeBits[1]));
				fclose($fp);
				$includeContents = $this->_parseIncludes($includeContents);
				$theContent = eregi_replace($origInclude,$includeContents,$theContent);
			}		
			return $theContent;	
		}
		
		function _convertVariables() {
			//$matchFound = preg_match_all("/(\{)([a-zA-Z0-9\.:_=*#<>])+(\})/",$this->templateContents,$arrayMatch);
			$matchFound = preg_match_all("/(\{[^{}\s\r\n;]+\})/",$this->templateContents,$arrayMatch);
			$counter = count($arrayMatch[0]);
			for ($f = 0; $f < $counter; $f++) {
				$theValue = $this->_getVariableValue($arrayMatch[0][$f]);
				if (substr($arrayMatch[0][$f],1,1) == "*") {
					$this->templateContents = str_replace($arrayMatch[0][$f],"$theValue",$this->templateContents);
				} else {
					$this->templateContents = str_replace($arrayMatch[0][$f],"<?php print $theValue; ?>",$this->templateContents);
				}
			}	
		}

		function _findComments($theContent) {
			$retval = preg_match_all("/(<##.*##>[\r\n]*)/s",$theContent,$arrayMatch);
			for ($f = 0; $f < count($arrayMatch[0]); $f++) {
				$theContent = str_replace($arrayMatch[0][$f],"",$theContent);
			}	
			return $theContent;
		}

		function _findSets($theContent) {
			$retval = preg_match_all("/(<#\s*set:[a-zA-Z0-9_\.:_=#<>]+\s*#>[\r\n]*)/",$theContent,$arrayMatch);
			for ($f = 0; $f < count($arrayMatch[0]); $f++) {
				$thisOne = $this->_stripTags($arrayMatch[0][$f]);
				$depthCount = 0;
				for ($g = 0; $g < strlen($thisOne); $g++) {
					if (substr($thisOne,$g,1) == "<") { $depthCount++; }
					if (substr($thisOne,$g,1) == ">") { $depthCount--; }
					if (substr($thisOne,$g,1) == ":" && $depthCount > 0) { $thisOne = substr_replace($thisOne,"^",$g,1); }
				}
				$formBits = split(":",$thisOne);
				for ($g = 0; $g < count($formBits); $g++) {
					$formBits[$g] = str_replace("^",":",$formBits[$g]);
				}
				if (count($formBits) != 3) {
					$this->_returnError($arrayMatch[0][$f],"Incorrect number of parameters.");
				}
				$theContent = $this->_convertSetVariables($theContent,$formBits[1],$formBits[2]);
				$theContent = str_replace($arrayMatch[0][$f],"",$theContent);
			}	
			return $theContent;
		}

		function _convertSetVariables($theContent,$fromValue,$toValue) {
			//find the variables
			$theContent = str_replace("{".$fromValue."}","{".$toValue."}",$theContent);
			$theContent = str_replace("{".$fromValue.".","{".$toValue.".",$theContent);
			$theContent = str_replace("{loop.".$fromValue.".","{loop.".$toValue.".",$theContent);
			$theContent = str_replace("<#loop:".$fromValue,"<#loop:".$toValue,$theContent);
			$theContent = str_replace("<#if:".$fromValue,"<#if:".$toValue,$theContent);
			
			$theContent = str_replace("{*".$fromValue."}","{*".$toValue."}",$theContent);
			$theContent = str_replace("{*".$fromValue.".","{*".$toValue.".",$theContent);
			return $theContent;	
		}		
		
		function _getVariableValue($theVariable) {
			$theVariable = $this->_stripVariable($theVariable);
			$splitVarCommands = explode("/",$theVariable);
			$variableBits = explode(".",@$splitVarCommands[0]);
			if (substr($variableBits[0],0,1) == "*") { $variableBits[0] = substr($variableBits[0],1,strlen($variableBits[0])-1); }
			$counter = count($variableBits);
			if ($variableBits[0] == "loop") {
				$loopCheck = "";
				for ($f = 1; $f < count($variableBits)-1; $f++ ) {
					if ($loopCheck == "") {
						$loopCheck = $variableBits[$f];
					} else {
						$loopCheck .= ".".$variableBits[$f];
					}
				}				
				$varMatch = "\$this->loopCounters[\"$loopCheck\"]";
				$varMatch .= "[\"".$variableBits[count($variableBits)-1]."\"]";
			} else{
				$extraCommands = split("\:",$variableBits[count($variableBits)-1]);
				$variableBits[count($variableBits)-1] = $extraCommands[0];
				$lengthLimit = "";
				$lengthAdd = "";
				if (count($extraCommands) > 1) {
					$lengthLimit = $extraCommands[1];
					$lengthAdd = @$extraCommands[2];
					if ($lengthLimit != "") {
						$varMatch = $this->baseVarLimited;
					} else {
						$varMatch = $this->baseVar;
					}
				} else {
					$varMatch = $this->baseVar;
				}
				$loopCheck = "";
				for ($f = 0; $f < count($variableBits); $f++) {
					if ($loopCheck == "") {
						$loopCheck = $variableBits[$f];
					} else {
						$loopCheck .= ".".$variableBits[$f];
					}
					if (@array_key_exists($loopCheck,$this->loopCounters)) {
						$varMatch .= "[\"$variableBits[$f]\"]";
						$varMatch .= "[\$maloop".$this->loopCounters[$loopCheck]["looper"]."]";
					} else {
						$varMatch .= "[\"$variableBits[$f]\"]";
					}
				}
				$doneLimiting = false;
				if ($lengthLimit != "") {
					$varMatch .=",$lengthLimit,\"$lengthAdd\")";
				}
			}
			for ($f = 0; $f < count($variableBits); $f++) {
				if ($f == 0) {
					$thisOne = $variableBits[0];
				} else {
					if ($variableBits[0] != "labels" && $variableBits[0] != "loop") {
						$thisOne .= ".".$variableBits[$f];
					}
				}
				$this->requiredVariables[$thisOne] = true;
			}
			if (count($splitVarCommands) > 1) {
				for ($f = 1; $f < count($splitVarCommands); $f++) {
					$currentCommand = $splitVarCommands[$f];
					if (substr($currentCommand,0,1) == "@") {
						//this is a PHP command
						$currentCommand = str_replace("@","",$currentCommand);
						$currentCommand = str_replace(")","",$currentCommand);
						$comSplit = explode("(",$currentCommand);
						$parSplit = explode(",",@$comSplit[1]);
						for ($g = 0; $g < count($parSplit); $g++) {
							if ($parSplit[$g] == "$") {
								$parSplit[$g] = $varMatch;
							}
						}
						$varMatch = $comSplit[0]."(";
						for ($g = 0; $g < count($parSplit); $g++) {
							if ($g != 0) { $varMatch .= ","; }
							$varMatch .= $parSplit[$g];
						}
						$varMatch .= ")";
					} else {
						switch ($currentCommand) {
							case "lowercase":
								$varMatch = "strtolower(".$varMatch.")";
								break;
							case "uppercase":
								$varMatch = "strtoupper(".$varMatch.")";
								break;
							case "wordcount":
								$varMatch = "count(preg_split(\"/(\W|\.|,|\!|\-)+/\", $varMatch))";
						}
					}
				}
			}
			return $varMatch;		
		}
		
		function _findIfStatements($theContent) {
			$theContent = preg_replace("/(<#\/if#>[\r\n]{0,1})/","<?php } ?>",$theContent);
			$theContent = preg_replace("/(<#else#>[\r\n]{0,1})/","<?php } else { ?>",$theContent);
			$retval = preg_match_all("/(<#\s*if:[a-zA-Z0-9\s_\.:-=]+\s*#>[\r\n]*)/",$theContent,$arrayMatch);
			for ($f = 0; $f < count($arrayMatch[0]); $f++) {
				$ifBits = split(":",$this->_stripTags($arrayMatch[0][$f]));
				if (count($ifBits) != 4) {
					echo "<b>Template Execution Halted!</b><br><b>Error:</b> $arrayMatch[$f]<br>Incorrect number of parameters";
					exit;
				}
				switch ($ifBits[1]) {
					case "blank":
						$ifBits[1] = "\"\"";
						break;
					default:
						if (preg_match("/\./",$ifBits[1])) {
							$ifBits[1] = $this->_getVariableValue($ifBits[1]);
						} else {
							$ifBits[1] = "\"".$ifBits[1]."\"";
						}
						break;
				}
				$ifValue = $ifBits[1];
				switch ($ifBits[3]) {
					case "blank":
						$ifBits[3] = "\"\"";
						break;
					default:
						if (is_numeric($ifBits[3])) {
							$ifBits[3] = $ifBits[3];
						} else {						
							if (preg_match("/\./",$ifBits[3])) {
								$ifBits[3] = $this->_getVariableValue($ifBits[3]);
							} else {
								$ifBits[3] = "\"".$ifBits[3]."\"";
							}
						}
						break;
				}
				if (substr($ifBits[2],0,3)=="mod") {
					$modulusAmount = substr($ifBits[2],3,strlen($ifBits[2])-3);
					$ifBits[2] = "mod";
				}
				if (substr($ifBits[2],0,3)=="columns") {
					$modulusAmount = substr($ifBits[2],7,strlen($ifBits[2])-7);
					$ifBits[2] = "mod";
				}
				switch ($ifBits[2]) {
					case "eq":
						$theContent = str_replace($arrayMatch[0][$f],"<?php if ($ifValue == $ifBits[3]) { ?>",$theContent);
						break;
					case "neq":
						$theContent = str_replace($arrayMatch[0][$f],"<?php if ($ifValue != $ifBits[3]) { ?>",$theContent);
						break;
					case "even":
						$theContent = str_replace($arrayMatch[0][$f],"<?php if ($ifValue/2 == 0) { ?>",$theContent);
						break;
					case "odd":
						$theContent = str_replace($arrayMatch[0][$f],"<?php if ($ifValue/2 != 0) { ?>",$theContent);
						break;
					case "mod":
						$theContent = str_replace($arrayMatch[0][$f],"<?php if ($ifValue % $modulusAmount == $ifBits[3]) { ?>",$theContent);
						break;
					case "lt":
						$theContent = str_replace($arrayMatch[0][$f],"<?php if ($ifValue < $ifBits[3]) { ?>",$theContent);
						break;
					case "lte":
						$theContent = str_replace($arrayMatch[0][$f],"<?php if ($ifValue <= $ifBits[3]) { ?>",$theContent);
						break;
					case "gt":
						$theContent = str_replace($arrayMatch[0][$f],"<?php if ($ifValue > $ifBits[3]) { ?>",$theContent);
						break;
					case "gte":
						$theContent = str_replace($arrayMatch[0][$f],"<?php if ($ifValue >= $ifBits[3]) { ?>",$theContent);
						break;	
					case "starts":
						$theContent = str_replace($arrayMatch[0][$f],"<?php if (substr($ifValue,0,strlen($ifBits[3])) == $ifBits[3]) { ?>",$theContent);					
						break;
					case "ends":
						$theContent = str_replace($arrayMatch[0][$f],"<?php if (substr($ifValue,-strlen($ifBits[3])) == $ifBits[3]) { ?>",$theContent);					
						break;
					case "contains":
						$theContent = str_replace($arrayMatch[0][$f],"<?php if (strpos($ifValue, $ifBits[3]) !== false) { ?>",$theContent);					
						break;
					case "nstarts":
						$theContent = str_replace($arrayMatch[0][$f],"<?php if (substr($ifValue,0,strlen($ifBits[3])) !== $ifBits[3]) { ?>",$theContent);					
						break;
					case "nends":
						$theContent = str_replace($arrayMatch[0][$f],"<?php if (substr($ifValue,-strlen($ifBits[3])) !== $ifBits[3]) { ?>",$theContent);					
						break;
					case "ncontains":
						$theContent = str_replace($arrayMatch[0][$f],"<?php if (strpos($ifValue, $ifBits[3]) === false) { ?>",$theContent);					
						break;
					default: 
						break;
				}
			}	
			//echo $theContent;
			//exit;
			return $theContent;
		}

		function _replaceFormFromFile($theContent) {
			$retval = preg_match_all("/(<#\s*form:[a-zA-Z0-9\s_\.:]+\s*#>[\r\n]*)/",$theContent,$arrayMatch);
			for ($f = 0; $f < count($arrayMatch[0]); $f++) {
				$formBits = split(":",$this->_stripTags($arrayMatch[0][$f]));
				if (count($formBits) > 2) {
					$this->_returnError($arrayMatch[0][$f],"Incorrect number of parameters.");
				}
				$theContent = str_replace($arrayMatch[0][$f],getFORM($formBits[1]),$theContent);
			}	
			return $theContent;
		}
		
		function _replaceFormFromArray() {
			if (is_array($this->requiredVariables)) {
				$tempArray = array_keys($this->requiredVariables);
				$this->requiredVariables = null;
				for ($g = 0; $g < count($tempArray); $g++) {
					$retval = preg_match_all("/(<#\s*form:[a-zA-Z0-9\s_\.:]+\s*#>[\r\n]*)/",$tempArray[$g],$arrayMatch);
					for ($f = 0; $f < count($arrayMatch[0]); $f++) {
						$formBits = split(":",$this->_stripTags($arrayMatch[0][$f]));
						if (count($formBits) > 2) {
							$this->_returnError($arrayMatch[0][$f],"Incorrect number of parameters.");
						}
						$tempArray[$g] = str_replace($arrayMatch[0][$f],getFORM($formBits[1]),$tempArray[$g]);
					}
					$this->requiredVariables[$tempArray[$g]] = true;				
				}
			}
		}
				
		function _findLoops($theContent) {
			$theContent = preg_replace("/(<#\/loop#>[\r\n]{0,1})/","<?php } ?>",$theContent);
			$retval = preg_match_all("/(<#\s*loop:[a-zA-Z0-9\s_\.:]+\s*#>[\r\n]*)/",$theContent,$arrayMatch);
			$topContent = "";
			$thisLooper = 0;
			for ($f = 0; $f < count($arrayMatch[0]); $f++) {
				$loopBits = split(":",$this->_stripTags($arrayMatch[0][$f]));
				if (count($loopBits) > 3) {
					$this->_returnError($arrayMatch[0][$f],"Incorrect number of parameters.");
				}
				$thisVariable = $this->_getVariableValue($loopBits[1]);
				if (!@array_key_exists($loopBits[1],@$this->loopCounters)) {
					$thisLooper++;
					$this->loopCounters[$loopBits[1]] = array("count"=>0,"total"=>0,"looper"=>$thisLooper);
				}
				
				$loopContent = "<?php \$counter$thisLooper = count($thisVariable);\n";
				if (count($loopBits) == 3 && $loopBits[2] != "") {
					$limitValue = $loopBits[2];
					$loopContent .= "if (\$counter$thisLooper > $limitValue) { \$counter$thisLooper = $limitValue; }";
				}
				$loopContent .= "\$this->loopCounters[\"$loopBits[1]\"] = array(\"count\"=>0,\"total\"=>\$counter$thisLooper,\"looper\"=>$thisLooper);\n";
				
				$topContent = "<?php \$counter$thisLooper = count($thisVariable);\n";
				$topContent .= "\$this->loopCounters[\"$loopBits[1]\"] = array(\"count\"=>0,\"total\"=>\$counter$thisLooper,\"looper\"=>$thisLooper); ?>\n";
				
				$loopContent .= "for (\$maloop$thisLooper = 0; \$maloop$thisLooper < \$counter$thisLooper; \$maloop$thisLooper++) {\n";
				$loopContent .= "\$this->loopCounters[\"$loopBits[1]\"][\"count\"] = \$maloop$thisLooper + 1;\n";
				$loopContent .= "?>\n";
				$theContent = $topContent.str_replace($arrayMatch[0][$f],$loopContent,$theContent);
			}	
			return $topContent.$theContent;
		}
		
		function showPage() {		
			ob_implicit_flush(0);
			ob_start();
			eval("?>".$this->templateContents);
			$content = ob_get_contents();
			ob_end_clean();
			echo $content;
		}
		
		function retrievePage() {
			$this->templateContents = eregi_replace("\r\n","<full>",$this->templateContents);
			ob_implicit_flush(0);
			ob_start();
			eval("?>".$this->templateContents);
			$content = ob_get_contents();
			ob_end_clean();
			$content = eregi_replace("<full>","\r\n",$content);			
			return $content;
		}		
		
		function convertText($from,$to) {
			$this->templateContents = eregi_replace($from,$to,$this->templateContents);
		}		
		
		function addVariable($variableName,$variableValue) {
			$this->theVariables[$variableName] = $variableValue;	
		}
		
		function _RUNTIME_stringLimit($theString,$theLength,$limitAdd) {
			if (strlen($theString) > $theLength) {
				$theString = substr($theString,0,$theLength);
				if ($limitAdd == "dots") {
					$theString .= "...";
				}
			}
			return $theString;
		}
		
		function _returnError($theLine,$theError) {
			echo "<b>Template Execution Halted!</b><br><b>Error:</b> $theLine ($theError)";
			exit;
		}
	}
?>
