<?php

//Behold my works, ye mighty, and despair!
//Piles of junk lay beside

include("functions.php");
retrySession:
$cinfo=create_session();
//query file code

//the case where creaste_session() gives back null, failed session create; error already logged, exit here
if(is_null($cinfo))
{
	exit(1);
}
elseif(isset($cinfo) && ($cinfo[0] == "Status: OK" && $cinfo[1] == "MSG: Session Created"))
{
	tryAgain:
	$tmp=explode(":",$cinfo[0]);
	$status =$tmp[1];
	$user = $cinfo[2];
	$tryCounter = 0;
	$sid=$cinfo[2];
	$username='ano523';
	$data="uid=$username&sid=$sid";
	$curl_handler=curl_init('https://cs4743.professorvaladez.com/api/query_files');
	$result;

	curl_setopt($curl_handler, CURLOPT_POST, 1);
	curl_setopt($curl_handler, CURLOPT_POSTFIELDS, $data);
	curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_handler, CURLOPT_HTTPHEADER, array(
		'content-type: application/x-www-form-urlencoded', 
		'content-length: '. strlen($data)));

	$time_start=microtime(true);
	try {
		$result=curl_exec($curl_handler);
		if(curl_errno($curl_handler))
		{
			echo 'Curl error: ' . curl_error($curl_handler) . '\r\n';
		}
		//echo $result;
		if($result == null)
		{
			throw new Exception("Failed to receive data from API.");
		}
	}
	catch(Exception $e)
	{
		//make query logging functions, feck
		$time_end=microtime(true);
		//left as is, displays in milliseconds
		$execution_time=($time_end-$time_start) * 1000;
		date_default_timezone_set("America/Chicago");
		$createDate = date('Y-m-d H:i:s');
		
		//logging session logs to different file
		//trying to open sessions
		write_session($user, $execution_time, "ERROR", $e->getMessage());
		curl_close($curl_handler);
		$tryCounter += 1;
		if($tryCounter <= 10)
		{
			
			//10 tries to connect to query_files
			goto tryAgain;
		}
		else
		{
			close_session($sid);
			exit(1);
		}
		
		
		
	}
	
	$time_end=microtime(true);
	//left as is, displays in milliseconds
	$execution_time=($time_end-$time_start) * 1000;
	$APIFetchTime = $execution_time;
	curl_close($curl_handler);
	
	if(isset($result) && !is_null($result))
	{
		//always give info, even if error data
		//start by checking status
		$cinfo;
		$decode_start = microtime(true);

		//always give info, even if error data
		try {
			$cinfo=json_decode($result,true);

			if(is_null($cinfo))
			{
				throw new Exception("Could not decode the returned JSON array");
			}
		}
		catch(Exception $e) {
			$decode_end = microtime(true);
			$execution_time = ($decode_end - $decode_start) * 1000;
			date_default_timezone_set("America/Chicago");
			$createDate = date('Y/m/d H:i:s');
			log_query_error("null", $createDate, 0, 0, "ERROR", $e->getMessage());
			if($tryCounter <= 10)
			{

				//10 tries to connect to query_files
				$tryCounter += 1;
				goto tryAgain;
			}
			else
			{
				close_session($sid);
				exit(1);
			}
			
		}
		$decode_end = microtime(true);
		//echo $cinfo;
		
		if($cinfo[0] == "Status: OK" && $cinfo[1] != "MSG: No new files found")
		{
			
			
			

			$tmp=explode(":",$cinfo[1]);
			$payload=json_decode($tmp[1]);
			$payloadLength = count($payload);
			try {
				$fp=fopen("/var/www/toProcess/toProcess.txt", "a");

				if(!$fp)
				{
					throw new Exception("Could not access file /var/www/toProcess/toProcess.txt");
				}
			}
			catch (Exception $e)
			{
				date_default_timezone_set("America/Chicago");
				$createDate = date('Y/m/d H:i:s');
				echo log_write_error($user, $createDate, $execution_time, "ERROR", $e->getMessage());
				fclose($fp);
			}
			$execution_start = microtime(true);
			foreach($payload as $key=>$value)
			{
				
				//write file names to toProcess
				$time_start = microtime(true);
				try {
					
					if (fwrite($fp, $value . "\n") == false )
					{
						throw new Exception("Filename $value unable to be written to /var/www/toProcess/toProcess.txt");
					}
				}
				catch(Exception $e) {
					date_default_timezone_set("America/Chicago");
					$createDate = date('Y/m/d H:i:s');
					echo log_write_error($user, $createDate, $execution_time, "null", "ERROR", $e->getMessage());
					fclose($fp);
					//close_session($sid);
					continue;
				}
			
				$time_end=microtime(true);
				//fclose($fp);
				date_default_timezone_set("America/Chicago");
				$createDate = date('Y/m/d H:i:s');
				$execution_time=($time_end-$time_start) * 1000;


				log_file_write_success($user, $createDate, $execution_time, strlen($value), "OK", "$value written to /var/www/toProcess/toProcess.txt");
			}
			$execution_end = microtime(true);
			$execution_time = ($execution_end - $execution_start) * 1000;
			fclose($fp);
			date_default_timezone_set("America/Chicago");
			$createDate = date('Y/m/d H:i:s');
			
			log_query_success($user, $createDate, $execution_time, $APIFetchTime, $payloadLength, $status, "$payloadLength files were queried");
		}
		elseif ($cinfo[1] == "MSG: No new files found")
		{
			//log error
			date_default_timezone_set("America/Chicago");
			$createDate = date('Y/m/d H:i:s');
			log_query_success($user, $createDate, 0, $APIFetchTime, 0, $status, "No files were queried");
		}
		
		
	}
	
	close_session($sid);
	
}

//the case where a previous session is somehow still running, clear and log
elseif ($cinfo[2] == "Action: Must clear session first")
{
	clear_session();
	goto retrySession;
	
}


?>