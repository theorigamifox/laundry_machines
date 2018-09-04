<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$recordType = "Newsletter";
	$tableName = $tableNewsletters;
	$linkBackLink = "newsletter_list.php";

	$xAction=getFORM("xAction");
	if ($xAction == "insert") {
		$tDate = date("Ymd");
		$rArray[] = array("subject",getFORM("xSubject"),"S");
		$rArray[] = array("content",getFORM("xContent"),"S");
		$rArray[] = array("contentHTML",getFORM("xContentHTML"),"S");
		$rArray[] = array("date",$tDate,"S");
		$rArray[] = array("status",0,"N");
		$rArray[] = array("currentpage",-1,"N");
		$rArray[] = array("recipList",getFORM("xRecipList"),"S");
		$dbA->insertRecord($tableName,$rArray);
		userLogActionAdd($recordType,getFORM("xSubject"));
		doRedirect("$linkBackLink?".userSessionGET());
	}
	if ($xAction == "delete") {
		$xNewsletterID = getFORM("xNewsletterID");
		if (!$dbA->doesIDExist($tableName,"newsletterID",$xNewsletterID,$uRecord)) {
			setupProcessMessage($recordType,getFORM("xSubject"),"error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"newsletterID",$xNewsletterID);
			userLogActionDelete($recordType,$uRecord["subject"]);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$xNewsletterID = getFORM("xNewsletterID");
		if (!$dbA->doesIDExist($tableName,"newsletterID",$xNewsletterID,$uRecord)) {
			setupProcessMessage($recordType,getFORM("xSubject"),"error_existance","BACK","");	
		} else {
			$rArray[] = array("subject",getFORM("xSubject"),"S");
			$rArray[] = array("content",getFORM("xContent"),"S");
			$rArray[] = array("contentHTML",getFORM("xContentHTML"),"S");
			$rArray[] = array("recipList",getFORM("xRecipList"),"S");
			$dbA->updateRecord($tableName,"newsletterID=$xNewsletterID",$rArray,0);
			userLogActionUpdate($recordType,getFORM("xSubject"));
			doRedirect("$linkBackLink?".userSessionGET());
		}		
	}
	if ($xAction == "send") {
		$xNewsletterID = getFORM("xNewsletterID");
		$result = $dbA->query("select * from $tableNewsletters where newsletterID = $xNewsletterID");
		if ($dbA->count($result) != 1) {
			setupProcessMessage($recordType,$xNewsletterID,"error_existance","BACK","");
		} else {
			$newsRecord = $dbA->fetch($result);
			$batchLimit = retrieveOption("newsletterBatchSize");
			$xPage = $newsRecord["currentpage"];
			if ($xPage == "") { $xPage = 0; }
			if (getFORM("xPre") == "Y") {
				echo "<b>Sending first email batch...</b>";
				doRedirect("newsletter_process.php?xAction=send&xNewsletterID=$xNewsletterID&".userSessionGET());
			}
			$result = $dbA->query("update $tableNewsletters set status=1 where newsletterID = $xNewsletterID");
			@ignore_user_abort(true);
			$theSubject = $newsRecord["subject"];
			$theContent = $newsRecord["content"];
			$theContentHTML = $newsRecord["contentHTML"];
			if ($newsRecord["recipList"] == "") {
				$newsRecord["recipList"] = "C:0";
			}
			$recipBits = explode(":",$newsRecord["recipList"]);
			if ($recipBits[0] == "C") {
				if ($recipBits[1] == 0) {
					$result = $dbA->query("select * from $tableNewsletter");
				} else {
					$result = $dbA->query("select $tableNewsletter.*,$tableCustomers.accTypeID from $tableNewsletter,$tableCustomers where $tableCustomers.email = $tableNewsletter.emailaddress and $tableCustomers.accTypeID = ".$recipBits[1]);
				}
			}
			if ($recipBits[0] == "A") {
				if ($recipBits[1] == 0) {
					$result = $dbA->query("select * from $tableAffiliates");
				} else {
					$result = $dbA->query("select affiliateID from $tableAffiliates where groupID=".$recipBits[1]);
				}
			}
			$fullcount = $dbA->count($result);
			$xStart = $xPage + 1;
			if ($recipBits[0] == "C") {
				if ($recipBits[1] == 0) {
					$result = $dbA->query("select * from $tableNewsletter order by recipientID limit $xStart,$batchLimit");
				} else {
					$result = $dbA->query("select $tableNewsletter.*,$tableCustomers.accTypeID from $tableNewsletter,$tableCustomers where $tableCustomers.email = $tableNewsletter.emailaddress and $tableCustomers.accTypeID = ".$recipBits[1]." order by recipientID limit $xStart,$batchLimit");
				}
			}
			if ($recipBits[0] == "A") {
				if ($recipBits[1] == 0) {
					$result = $dbA->query("select affiliateID, aff_Email as emailaddress from $tableAffiliates order by affiliateID limit $xStart,$batchLimit");
				} else {
					$result = $dbA->query("select affiliateID, aff_Email as emailaddress from $tableAffiliates where groupID=".$recipBits[1]." order by affiliateID limit $xStart,$batchLimit");
				}
			}
			$count = $dbA->count($result);			
			for ($f = 0; $f < $count; $f++) {
				@set_time_limit(30);
				$eRecord = $dbA->fetch($result);
				$thisContent = $theContent;
				$thisContentHTML = $theContentHTML;
				if ($recipBits[0] == "C") {
					$thisContent = str_replace("{removelink}",$jssStoreWebDirHTTP."newsletter.php?xCmd=unsubscribe&xEmailAddress=".$eRecord["emailaddress"],$thisContent);
					$thisContentHTML = str_replace("{removelink}",$jssStoreWebDirHTTP."newsletter.php?xCmd=unsubscribe&xEmailAddress=".$eRecord["emailaddress"],$thisContentHTML);
				}
				@sendEmailWithType($eRecord["emailaddress"],retrieveOption("emailNewsletterFrom"),$theSubject,$thisContent,$thisContentHTML);
				$dbA->query("update $tableNewsletters set currentpage=$xStart where newsletterID = $xNewsletterID");
				$xStart++;
			}
			$totalRecipients = $xStart;
			@set_time_limit(30);
			if ($totalRecipients >= $fullcount) {
				userLog("Sent Newsletter ID $xNewsletterID");
				$result = $dbA->query("update $tableNewsletters set status=2,currentpage=0 where newsletterID = $xNewsletterID");
				echo "<b>Email Newsletter sent to $fullcount recipients successfully</b>";
				echo "<p><a href='javascript:self.close();'>Close Window</a>";
				exit;
			} else {
				if (retrieveOption("newsletterBatchAuto") == 1) {
					echo "<b>Email Newsletter so far sent to $totalRecipients of $fullcount<p>Now sending next batch...</b>\n";
					flush();
					sleep(3);
					doRedirect("newsletter_process.php?xAction=send&xNewsletterID=$xNewsletterID&".userSessionGET());
				} else {
					echo "<b>Email Newsletter so far sent to $totalRecipients of $fullcount</b>\n";
					echo "<p><a href=\"newsletter_process.php?xAction=send&xNewsletterID=$xNewsletterID&".userSessionGET()."\">Click here to process next batch</a>";
					exit;
				}
			}
		}
	}
	
	if ($xAction == "test") {
		$xNewsletterID = getFORM("xNewsletterID");
		$result = $dbA->query("select * from $tableNewsletters where newsletterID = $xNewsletterID");
		if ($dbA->count($result) != 1) {
			setupProcessMessage($recordType,$xNewsletterID,"error_existance","BACK","");
		} else {
			if (@$safeMode == 0) {
				$newsRecord = $dbA->fetch($result);
				//@ignore_user_abort(true);
				$theSubject = $newsRecord["subject"];
				$theContent = $newsRecord["content"];
				$theContentHTML = $newsRecord["contentHTML"];				
				@set_time_limit(30);
				$thisContent = $theContent;
				$thisContentHTML = $theContentHTML;
				$thisContent = str_replace("{removelink}",$jssStoreWebDirHTTP."newsletter.php?xCmd=unsubscribe&xEmailAddress=testing@newsletter",$thisContent);
				$thisContentHTML = str_replace("{removelink}",$jssStoreWebDirHTTP."newsletter.php?xCmd=unsubscribe&xEmailAddress=testing@newsletter",$thisContentHTML);
				@sendEmailWithType(retrieveOption("emailNewsletterTest"),retrieveOption("emailNewsletterFrom"),$theSubject,$thisContent,$thisContentHTML);
				userLog("Test Newsletter ID $xNewsletterID");
				//exit;
				doRedirect("$linkBackLink?".userSessionGET());
			} else {
				doRedirect("newsletter_options.php?".userSessionGET());
			}
		}
	}	
	
	if ($xAction == "reset") {
		$xNewsletterID = getFORM("xNewsletterID");
		$result = $dbA->query("update $tableNewsletters set status=0,currentpage=-1 where newsletterID=$xNewsletterID");
		userLog("Reset Newsletter ID $xNewsletterID Status to NOT SENT");
		doRedirect("$linkBackLink?".userSessionGET());
	}
	if ($xAction == "options") {
		updateOption("emailNewsletterFrom",getFORM("xEmailNewsletterFrom"));
		updateOption("emailNewsletterTest",getFORM("xEmailNewsletterTest"));
		updateOption("newsletterEmailsList",getFORM("xNewsletterEmailsList"));
		updateOption("newsletterBatchSize",getFORM("xNewsletterBatchSize"));
		updateOption("newsletterBatchAuto",getFORM("xNewsletterBatchAuto"));
		updateOption("newsConvertToBR",getFORM("xNewsConvertToBR"));
		userLog("Updated Newsletter Settings");
		doRedirect("newsletter_options.php?".userSessionGET());
	}
	
	function sendEmailWithType($to, $from, $subject, $textMessage, $htmlMessage) { 

		$separator = "JSS__Newsletter__Send"; 
		
		$header = "From: $from\n"; 
		$header .= "Reply-To: $from\n"; 
		//$header .= "To: $to\n"; 
		$header .= "MIME-Version: 1.0\n"; 
		$header .= "X-Mailer: JSS\n"; 
		$header .= "Content-Type: multipart/alternative; boundary=\"$separator\";\n\n";
		
		$message =""; 
		if (chop($textMessage) != "") {
			$message .= "--$separator\n"; 
			$message .= "Content-Type: text/plain\n";
			$message .= "Content-Transfer-Encoding: 7bit\n\n";
			$message .= $textMessage."\n\n"; 
		}
		
		if (chop($htmlMessage) != "") {		
			$message .= "--$separator\n"; 
			$message .= "Content-Type: text/html\n";
			$message .= "Content-Transfer-Encoding: 7bit\n\n";
			$message .= $htmlMessage."\n\n";
		}
		
		$message .= "--$separator--\n\n\n"; 
		
		@mail ( $to, $subject, $message, $header); 
		return true; 
	} 


	class emailSocket {
		var $emailConnection;
		var $seperator;
		
		function emailSocket() {
		}
		
		function openSocket() {
 			$this->emailConnection = fsockopen (ini_get("SMTP"), 25, $errno, $errstr, 30) or die("Could not talk to the sendmail server!"); 
 			$this->setTimeout();
   			$rcv = fgets($this->emailConnection, 1024);
   			$this->checkTimeout();
   			$this->seperator = "JSS__Newsletter__Send"; 
   			flush();
		}
		
		function setTimeout() {
			//Different versions of PHP have a different name for this function.
			if (function_exists('socket_set_timeout')) {
				socket_set_timeout($this->emailConnection, 2,0);
			}
			if (function_exists('set_socket_timeout')) {
				set_socket_timeout($this->emailConnection, 2,0);
			}
			if (function_exists('stream_set_timeout')) {
				stream_set_timeout($this->emailConnection, 2,0);
			}
		}
		
		function checkTimeout() {
			$socket_status = socket_get_status($this->emailConnection);
			if ($socket_status["timed_out"]) {
				$this->closeSocket();
				$this->openSocket();
				return true;
			} else {
				return false;
			}
		}
		
		function sendEmail($to, $from, $subject, $textMessage, $htmlMessage) {
   			fputs ($this->emailConnection, "MAIL FROM:$from"."\r\n"); 
			if ($this->checkTimeout()) { return false; }
     		$rcv = fgets ($this->emailConnection, 1024); 
			if ($this->checkTimeout()) { return false; }
			fputs ($this->emailConnection, "RCPT TO:$to\r\n"); 
			if ($this->checkTimeout()) { return false; }
     		$rcv = fgets ($this->emailConnection, 1024); 
     		if (substr($rcv,0,3) != "250") { $this->closeSocket(); $this->openSocket(); return false; }
			if ($this->checkTimeout()) { return false; }
   			fputs ($this->emailConnection, "DATA\r\n"); 
			if ($this->checkTimeout()) { return false; }
     		$rcv = fgets ($this->emailConnection, 1024); 
			if ($this->checkTimeout()) { return false; }
   			fputs ($this->emailConnection, "Subject: $subject" . "\r\n" ); 
			if ($this->checkTimeout()) { return false; }
   			fputs ($this->emailConnection, "From: \"$from\" <$from>" . "\r\n" ); 
			if ($this->checkTimeout()) { return false; }
   			fputs ($this->emailConnection, "Reply-To: $from" . "\r\n" ); 
			if ($this->checkTimeout()) { return false; }
   			fputs ($this->emailConnection, "To: $to" . "\r\n" ); 
			if ($this->checkTimeout()) { return false; }
   			fputs ($this->emailConnection, "MIME-Version: 1.0" . "\r\n" ); 
			if ($this->checkTimeout()) { return false; }
   			fputs ($this->emailConnection, "X-Mailer: JSS" . "\r\n" ); 
			if ($this->checkTimeout()) { return false; }
   			fputs ($this->emailConnection, "Content-Type: multipart/alternative; boundary=\"".$this->seperator."\";" . "\r\n" ); 
			if ($this->checkTimeout()) { return false; }			
   			fputs ($this->emailConnection, "\r\n" ); 
			if ($this->checkTimeout()) { return false; }

			$message =""; 
			if (chop($textMessage) != "") {
				$message .= "--".$this->seperator."\r\n"; 
				$message .= "Content-Type: text/plain\r\n";
				$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
				$message .= $textMessage."\r\n\r\n"; 
			}
			
			if (chop($htmlMessage) != "") {		
				$message .= "--".$this->seperator."\r\n"; 
				$message .= "Content-Type: text/html\r\n";
				$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
				$message .= $htmlMessage."\r\n\r\n";
			}
			
			$message .= "--".$this->seperator."--\r\n\r\n\r\n"; 

   			fputs ($this->emailConnection, "$message " . "\r\n" ); 
			if ($this->checkTimeout()) { return false; }
   			fputs ($this->emailConnection, ".\r\n"); 
			if ($this->checkTimeout()) { return false; }
     		$rcv = fgets ($this->emailConnection, 1024); 
			if ($this->checkTimeout()) { return false; }
   			fputs ($this->emailConnection, "RSET\r\n"); 
			if ($this->checkTimeout()) { return false; }
     		$rcv = fgets ($this->emailConnection, 1024); 
			if ($this->checkTimeout()) { return false; }
		}
		
		function closeSocket() {
 			fputs ($this->emailConnection, "QUIT\r\n"); 
   			$rcv = fgets ($this->emailConnection, 1024); 
 			fclose($this->emailConnection);		
		}
	}
	
?>
