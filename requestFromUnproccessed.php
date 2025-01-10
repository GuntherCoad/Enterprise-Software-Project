<?php

//Behold my works, ye mighty, and despair!
//Piles of junk lay beside

include("functions.php");


tryAgain:
$cinfo=create_session();


//the case where creaste_session() gives back null, failed session create; error already logged, exit here
if(is_null($cinfo))
{
	exit(1);
}
//this needs to be logged
elseif(isset($cinfo) && ($cinfo[0] == "Status: OK" && $cinfo[1] == "MSG: Session Created"))
{
	//open up /var/www/files/toProcess.txt
	//loop through file
	//each instance, request file and send to /var/www/files
	//once done, empty /var/www/files/toProcess.txt
	
	$sid=$cinfo[2];
	$username='ano523';
	$timeStart = microtime(true);
	$timeEnd;
	$fpName = "/var/www/files/toProcess.txt";
	$fp;
	try {
		$fp=fopen($fpName, "r");

		if(!$fp)
		{
			throw new Exception("Could not access file $fpName");
		}
	}
	catch (Exception $e)
	{
		$timeEnd = microtime(true);
		date_default_timezone_set("America/Chicago");
		$createDate = date('Y/m/d H:i:s');
		$execution_time = ($timeEnd - $timeStart) * 1000;
		echo log_write_error($sid, $createDate, $execution_time, "ERROR", $e->getMessage());
		fclose($fp);
	}
	$currSid = $sid;
	while(!feof($fp))
	{
		//remove new line from lines
		$line = fgets($fp);
		$fileName = preg_replace('~[\r\n]+~', '', $line);
		
		//should have counter for 3 retries of requesting single file
		$requestCounter = 0;
		if ($fileName == "")
		{
			continue;
		}
		
		requestLoop:
		$data="sid=$sid&uid=$username&fid=$fileName";
		$result;
		$curl_handler=curl_init('https://cs4743.professorvaladez.com/api/request_file');

		
		curl_setopt($curl_handler, CURLOPT_POST, 1);
		curl_setopt($curl_handler, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_handler, CURLOPT_HTTPHEADER, array(
			'content-type: application/x-www-form-urlencoded', 
			'content-length: '. strlen($data)));
		$time_start=microtime(true);
		
		try{
			$result=curl_exec($curl_handler);
			//echo $result . "\r\n";
			if (is_null($result))
			{
				throw new Exception("Failed to receive data from API");
			}
		}
		catch(Exception $e)
		{
			$time_end=microtime(true);
			//left as is, displays in milliseconds
			$execution_time=($time_end-$time_start) * 1000;
			date_default_timezone_set("America/Chicago");
			$createDate = date('Y-m-d H:i:s');

			log_session_error($currSid, $createDate, $execution_time, "ERROR", $e->getMessage());
			curl_close($curl_handler);
			close_session($currSid);
		}
		$time_end=microtime(true);
		//left as is, displays in milliseconds
		$execution_time=($time_end-$time_start) * 1000;
		$APIFetchTime = $execution_time;
		$createDate;
		$user = $sid;
		curl_close($curl_handler);
		
		//will exit script if hit currently
		//likely functions properly with removal of exit() in favor of continue;
		if(strstr($result, "does not exist"))
		{
			$payload = json_decode($result, true);
			$statusArr = explode(":", $payload[0]);
			$errMsgArr = explode(":", $payload[1]);

			date_default_timezone_set("America/Chicago");
			$createDate = date('Y/m/d H:i:s');

			
			//close_session($sid);
			//log_session_error($sid, $createDate, $execution_time, $statusArr[1], $errMsgArr[1]);
			write_session($sid, $execution_time, $statusArr[1], $errMsgArr[1]);
			continue;
			
		}
		elseif(strpos($result, "SID not found") != false)
		{
			//checking for sudden session closure while in loop
			date_default_timezone_set("America/Chicago");
			$createDate = date('Y/m/d H:i:s');
			log_session_error($currSid, $createDate, $execution_time, "ERROR", "Session unexpectedly closed");
			goto tryAgain;
		}
		
		//TODO another file size check for suspicious payload
		elseif(strlen($result) == 0)
		{
			date_default_timezone_set("America/Chicago");
			$createDate = date('Y/m/d H:i:s');
			echo log_write_error($sid, $createDate, 0, 0, "ERROR", "file request $fileName received 0 bytes");
			$requestCounter += 1;
			if($requestCounter <= 3)
			{
				goto requestLoop;
			}
			
			continue;
		}
		else
		{
			//open $line as a wb file 
			//try writing $content into file
			//close file connection
			
			$content = $result;
			
			
			//trying to create/open file to new file for short term storage
			try {
				$writePointer=fopen("/var/www/files/$fileName", "wb");

				if(!$writePointer)
				{
					throw new Exception("Could not access file /var/www/files/$fileName");
				}
			}
			catch (Exception $e)
			{
				date_default_timezone_set("America/Chicago");
				$createDate = date('Y/m/d H:i:s');
				echo log_write_error($user, $createDate, $execution_time, "ERROR", $e->getMessage());
				fclose($writePointer);
			}
			//trying to write received api content to aforementioned file
			$time_start=microtime(true);
			try {
				if (fwrite($writePointer, $content) == false )
				{
					throw new Exception("File unable to be written to file system.");
				}
			}
			catch(Exception $e) {
				date_default_timezone_set("America/Chicago");
				$createDate = date('Y/m/d H:i:s');
				echo log_write_error($user, $createDate, $execution_time, "ERROR", $e->getMessage());
				fclose($writePointer);
				//close_session($sid);
				continue;
			}
			
			$time_end=microtime(true);
			fclose($writePointer);
			$createDate = date('Y/m/d H:i:s');
			$execution_time=($time_end-$time_start) * 1000;
			
			
			log_write_success($user, $createDate, $execution_time, strlen($content), "OK", "$fileName: written file to system");
		}
	}
	//need to clear the toProcess.txt list
	
	fclose($fp);
	
	$clear = fopen("$fpName", "w");
	fwrite($clear, "");
	fclose($clear);
	
	
	close_session($sid);
}

//the case where a previous session is somehow still running, clear and log
elseif ($cinfo[2] == "Action: Must clear session first")
{
	clear_session();
	goto tryAgain;
}
?>