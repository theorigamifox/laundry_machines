<?php
	function dbConnect(&$dbA) {
		global $databaseHost,$databaseUsername,$databasePassword,$databaseName;
		$dbA = new dbAccess();
		$dbStatus = $dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
		if ($dbStatus == false) {
			$dbA->showDBError();
			exit;
		}
	}
	
	class dbAccess {

		var $resID;
		var $lastError;
		var $currentDatabase;
		var $queriesRun;
		var $lastQuery;

		function dbAccess() {
			$this->resID = FALSE;
			$this->lastError = "";
			$this->queriesRun = 0;
			$this->lastQuery = "";
		}
		
		function showDBError() {
			global $jssShopFileSystem;
			include($jssShopFileSystem."templates/databaseproblem.html");
			return false;
		}

		function connect($sql_host_name,$sql_username,$sql_password,$sql_database_name) {
			$this->currentDatabase = $sql_database_name;
			$this->resID = @mysql_connect($sql_host_name,$sql_username,$sql_password);
			if ($this->resID == FALSE) {
				$this->lastError = "Could not connect to mySQL server";
				return FALSE;
			} else {
				if (@mysql_select_db($sql_database_name)) {
					return TRUE;
				} else {
					return @mysql_query("create database $sql_database_name");
					return FALSE;
				}
			}
		}

		function close() {
			if (@mysql_close()) {
				return TRUE;
			} else {
				$this->lastError = @mysql_errno().": ".mysql_error();
				return FALSE;
			}
		}
		
		function showQueries() {
			echo "Total Queries: ".$this->queriesRun;
		}

		function query($sql_query) {
			global $safeMode;
			$this->queriesRun++;
			$this->lastQuery = $sql_query;
			//echo $sql_query."<BR>";
			if ((@$safeMode == "1" && strtolower(substr($sql_query,0,6)=="select")) || @$safeMode != "1") {
				//if (get_magic_quotes_runtime() == 1) {
				//	$sql_query=stripslashes($sql_query);
				//}
				$result = @mysql_query($sql_query);
				if ($result == FALSE) {
					$this->lastError = mysql_errno().": ".mysql_error();
					//echo $sql_query;
					return FALSE;
				} else {
					return $result;
				}
			} else {
				return true;
			}	
		}

		function fetch($sql_result) {
			$result =  @mysql_fetch_assoc($sql_result);
			if ($result == FALSE) {
				$this->lastError = mysql_errno().": ".mysql_error();
				return FALSE;
			} else {
				foreach($result as $k=>$v) {
					$result[$k] = stripslashes($v);
				}
				return $result;
			}
		}

		function seek($sql_result,$sql_row) {
			$result =  @mysql_data_seek($sql_result,$sql_row);
			if (mysql_data_seek($sql_result,$sql_row)) {
				return TRUE;
			} else {
				$this->lastError = mysql_errno().": ".mysql_error();
				return FALSE;
			}
		}

		function count($sql_result) {
			//echo $this->lastQuery."<br>";
			return @mysql_num_rows($sql_result);
		}

		function lastID() {
			return mysql_insert_id();
		}
		
		//extra functions to quicken development
		
		//checks to see if an ID exists in a table
		function doesIDExist($theTable,$idField,$theValue,&$resultSet) {
			$this->queriesRun++;
			$result = mysql_query("select * from $theTable where $idField=$theValue");
			$myCount = mysql_num_rows($result);
			if ($myCount > 0) {
				$resultSet = mysql_fetch_assoc($result);
				foreach($resultSet as $k=>$v) {
					$resultSet[$k] = stripslashes($v);
				}
				return true;
			} else {
				return false;
			}
		}

		//checks to see if a field is the same in another ID
		function isUnique($theTable,$idField,$idValue,$uniqueField,$uniqueFieldValue) {
			$this->queriesRun++;
			$result = $this->query("select * from $theTable where $uniqueField=\"$uniqueFieldValue\" and $idField != $idValue");
			$myCount = mysql_num_rows($result);
			if ($myCount > 0) {
				return false;
			} else {
				return true;
			}
		}

		function doesRecordExist($theTable,$uniqueField,$uniqueFieldValue) {
			$this->queriesRun++;
			$result = $this->query("select * from $theTable where $uniqueField=\"$uniqueFieldValue\"");
			$myCount = mysql_num_rows($result);
			if ($myCount > 0) {
				return true;
			} else {
				return false;
			}
		}
		
		function deleteRecord($theTable,$idField,$idValue) {
			$this->queriesRun++;
			$result = $this->query("delete from $theTable where $idField=$idValue");
			return true;
		}
		
		function updateRecord($theTable,$whereClause,$recArray,$myDebug=0) {
			$this->queriesRun++;
			$theQuery="update $theTable set ";
			for ($f = 0; $f < count($recArray); $f++) {
				if ($f > 0) { $theQuery .=","; }
				switch ($recArray[$f][2]) {
					case "S":	//string
						$theQuery .= $recArray[$f][0]."=\"".addSlashes($recArray[$f][1])."\"";
						break;
					case "N":	//number
						$theQuery .= $recArray[$f][0]."=".makeInteger($recArray[$f][1]);
						break;
					case "YN":	//yes no field
						$theQuery .= $recArray[$f][0]."=\"".makeYesNo($recArray[$f][1])."\"";
						break;
					case "D":
						$theQuery .= $recArray[$f][0]."=".makeDecimal($recArray[$f][1]);
						break;
					case "C":
						$theQuery .= $recArray[$f][1];
						break;						
				}
			}
			if ($whereClause != "") {
				$theQuery .= " where ".$whereClause;
			}
			if ($myDebug == 1) { echo $theQuery; }
			$result = $this->query($theQuery);
			return $result;
		}

		function insertRecord($theTable,$recArray,$myDebug=0) {
			$this->queriesRun++;
			$theQuery="insert into $theTable (";
			for ($f = 0; $f < count($recArray); $f++) {
				if ($f > 0) { $theQuery .=","; }
				$theQuery .= $recArray[$f][0];
			}
			$theQuery .= ") VALUES(";
			for ($f = 0; $f < count($recArray); $f++) {
				if ($f > 0) { $theQuery .=","; }
				switch ($recArray[$f][2]) {
					case "S":	//string
						$theQuery .= "\"".addSlashes($recArray[$f][1])."\"";
						break;
					case "N":	//number
						$theQuery .= makeInteger($recArray[$f][1]);
						break;
					case "YN":	//yes no field
						$theQuery .= "\"".makeYesNo($recArray[$f][1])."\"";
						break;
					case "D":
						$theQuery .= makeDecimal($recArray[$f][1]);
						break;
				}
			}
			$theQuery .= ")";
			if ($myDebug == 1) { echo $theQuery; }
			$result = $this->query($theQuery);
			return true;
		}

		function replaceRecord($theTable,$recArray,$myDebug=0) {
			$this->queriesRun++;
			$theQuery="replace into $theTable (";
			for ($f = 0; $f < count($recArray); $f++) {
				if ($f > 0) { $theQuery .=","; }
				$theQuery .= $recArray[$f][0];
			}
			$theQuery .= ") VALUES(";
			for ($f = 0; $f < count($recArray); $f++) {
				if ($f > 0) { $theQuery .=","; }
				switch ($recArray[$f][2]) {
					case "S":	//string
						$theQuery .= "\"".addSlashes($recArray[$f][1])."\"";
						break;
					case "N":	//number
						$theQuery .= makeInteger($recArray[$f][1]);
						break;
					case "YN":	//yes no field
						$theQuery .= "\"".makeYesNo($recArray[$f][1])."\"";
						break;
					case "D":
						$theQuery .= makeDecimal($recArray[$f][1]);
						break;
				}
			}
			$theQuery .= ")";
			if ($myDebug == 1) { echo $theQuery; }
			$result = $this->query($theQuery);
			return true;
		}				

		function getTableList() {
			$tableList = "";
			$result = mysql_list_tables($this->currentDatabase);
			for ($f = 0; $f < mysql_num_rows($result); $f++) {
	   			$tableList[] = mysql_tablename($result,$f);
			}
			return $tableList;
		}
		
		function tableFields($result) {
			return mysql_num_fields($result);
		}
		
		function retrieveFieldInformation($tableName) {
			$result = mysql_query("SHOW FIELDS FROM ".$tableName);
			while ($row = mysql_fetch_array($result)) {
				$ftype  = $row['Type'];
   				$fname  = $row['Field'];
   				$fnull   = $row['Null'];
   				$fkey = $row['Key'];
   				$fdefault = @$row['Default'];
   				$fextra = $row['Extra'];
   				$allFields[] = array("name"=>$fname,"type"=>$ftype,"null"=>$fnull,"key"=>$fkey,"default"=>$fdefault,"extra"=>$fextra);
			}			
			return $allFields;
		}
		
		function retrieveIndexInformation($tableName) {
			$result = mysql_query("SHOW INDEX FROM ".$tableName);
			while ($row = mysql_fetch_array($result)) {
				$fkeyname  = $row['Key_name'];
   				$fseq  = $row['Seq_in_index'];
   				$fcolumn   = $row['Column_name'];
   				$fnonunique   = $row['Non_unique'];
   				$allFields[] = array("key"=>$fkeyname,"seq"=>$fseq,"column"=>$fcolumn,"nonunique"=>$fnonunique);
			}			
			return $allFields;
		}
		
		function retrieveAllRecords($theTable,$theOrder) {
			$this->queriesRun++;
			$result = $this->query("select * from $theTable order by $theOrder");
			$count = mysql_num_rows($result);
			$retArray = "";
			for ($f = 0; $f < $count; $f++) {
				$retArray[] = mysql_fetch_assoc($result);
				foreach($retArray[$f] as $k=>$v) {
					$retArray[$f][$k] = stripslashes($v);
				}
			}
			return $retArray;
		}

		function retrieveAllRecordsFromQuery($theQuery) {
			$this->queriesRun++;
			$result = $this->query($theQuery);
			$count = mysql_num_rows($result);
			$retArray = null;
			for ($f = 0; $f < $count; $f++) {
				$retArray[] = mysql_fetch_assoc($result);
				foreach($retArray[$f] as $k=>$v) {
					$retArray[$f][$k] = stripslashes($v);
				}
			}
			return $retArray;
		}		
	}
	
	function addImageUpdate($imageField,$dbField,$imagePath,&$myArray) {
		global $jssShopImagesWeb,$jssShopImagesFileSystem;
		$fileName = @$_FILES[$imageField]["name"];
		$fileType = @$_FILES[$imageField]["type"];
		$fileSize = @$_FILES[$imageField]["size"];
		$fileTmpName = @$_FILES[$imageField]["tmp_name"];
		$clearImage = getFORM($imageField."ImgClear");
		if ($clearImage == "clearimage") {
			$myArray[] = array($dbField,"","S");
		} else {
			if ($fileName == "") {
				$xImagePick = getFORM($imageField."Pick");
				if ($xImagePick != "") {
					if (substr($xImagePick,0,7) == "http://" || substr($xImagePick,0,8) == "https://") {
						$myArray[] = array($dbField,$xImagePick,"S");	
					} else {
						$myArray[] = array($dbField,$jssShopImagesWeb.$imagePath.$xImagePick,"S");	
					}
				}
			} else {
				move_uploaded_file($fileTmpName,$jssShopImagesFileSystem.$imagePath.$fileName);
				@chmod($jssShopImagesFileSystem.$imagePath.$fileName, 0644);
				$myArray[] = array($dbField,$jssShopImagesWeb.$imagePath.$fileName,"S");	
			}	
		}
	}
	
	function addDigitalUpdate($fileField,$dbField,&$myArray) {
		global $jssShopImagesWeb,$jssShopFileSystem;
		$fileName = @$_FILES[$fileField]["name"];
		$fileType = @$_FILES[$fileField]["type"];
		$fileSize = @$_FILES[$fileField]["size"];
		$fileTmpName = @$_FILES[$fileField]["tmp_name"];
		if ($fileName == "") {
			$xFilePick = getFORM($fileField."Pick");
			if ($xFilePick != "") {
				$myArray[] = array($dbField,$xFilePick,"S");	
			}
		} else {
			move_uploaded_file($fileTmpName,retrieveOption("downloadsDirectory").$fileName);
			@chmod($jssShopImagesFileSystem.$imagePath.$fileName, 0644);
			$myArray[] = array($dbField,$fileName,"S");	
		}	
	}	
	
	$optionsArray["init"] = 1;
	
	function retrieveOption($xOptionName) {
		global $dbA,$optionsArray,$tableOptions;
		if (array_key_exists($xOptionName,$optionsArray)) {
			return $optionsArray[$xOptionName];
		} else {
			$dbA->queriesRun++;
			$lResult = $dbA->query("select * from $tableOptions where name='$xOptionName'");
			if ($dbA->count($lResult) == 0) {
				//error this out as the option appears to of been removed!!!!
				return false;
			} else {
				$lRecord = $dbA->fetch($lResult);
				$thisOption = $lRecord["value"];
				$optionsArray[$xOptionName] = $thisOption;
				return $thisOption;
			}			
		}
	}
	
	function grabAllOptions() {
		global $dbA,$optionsArray,$tableOptions;
		$result = $dbA->query("select * from $tableOptions");
		$count = $dbA->count($result);
		for ($f = 0; $f < $count; $f++) {
			$record = $dbA->fetch($result);
			$optionsArray[$record["name"]] = $record["value"];
		}
	}
	
	function updateOption($xOptionName,$xOptionValue) {
		global $dbA,$tableOptions;
		$dbA->queriesRun++;
		$lResult = $dbA->query("update $tableOptions set value=\"$xOptionValue\" where name=\"$xOptionName\"");
		return true;
	}	
?>