<?php

include("functions.php");

$cinfo=create_session();

//query file code

//this needs to be logged
if(isset($cinfo) && ($cinfo[0] == "Status: OK" && $cinfo[1] == "MSG: Session Created"))
{
	
	$sid=$cinfo[2];
	$username='ano523';
	$data="uid=$username&sid=$sid";
	$curl_handler=curl_init('https://cs4743.professorvaladez.com/api/request_all_loans');
	$result;

	echo "<h3>Payload for query_files: $data</h3>";
	curl_setopt($curl_handler, CURLOPT_POST, 1);
	curl_setopt($curl_handler, CURLOPT_POSTFIELDS, $data);
	curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_handler, CURLOPT_HTTPHEADER, array(
		'content-type: application/x-www-form-urlencoded', 
		'content-length: '. strlen($data)));

	$time_start=microtime(true);
	try {
			$result=curl_exec($curl_handler);
			if($result == null)
			{
				throw new Exception("Failed to receive data from API.");
			}
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
		
	}
	
	$time_end=microtime(true);
	//left as is, displays in milliseconds
	$execution_time=($time_end-$time_start)/60;
	curl_close($curl_handler);
	
	if(isset($result))
	{
		//always give info, even if error data
		//start by checking status
		$cinfo=json_decode($result,true);//converts JSON to array
		
		if($cinfo[0] == "Status: OK" )
		{
			
			//MSG: No new files found
			//this is the message needed to check for no files error(?)
			
			echo "<pre>";
			print_r($cinfo);
			echo "</pre>";

			$tmp=explode(":",$cinfo[1]);
			$payload=json_decode($tmp[1]);
			
			$fp=fopen("/var/www/toProcess/allIDs.txt", "a");
			
			foreach($payload as $key=>$value)
			{
				
				//write file names to toProcess
				$time_start = microtime(true);
				try {
					
					if (fwrite($fp, $value . "\n") == false )
					{
						throw new Exception("Filename $value unable to be written to /var/www/toProcess/allIDs.txt");
					}
				}
				catch(Exception $e) {
					date_default_timezone_set("America/Chicago");
					$createDate = date('Y/m/d H:i:s');
					//echo log_write_error($user, $createDate, $execution_time, "null", "ERROR", $e->getMessage());
					fclose($fp);
					//close_session($sid);
					continue;
				}
			
				$time_end=microtime(true);
				//fclose($fp);
				date_default_timezone_set("America/Chicago");
				$createDate = date('Y/m/d H:i:s');
				$execution_time=($time_end-$time_start) * 1000;


				//log_file_write_success($user, $createDate, $execution_time, strlen($value), "OK", "$value written to /var/www/toProcess/allDocName.txt");
			}
		}
		else
		{
			//log error
			echo "<pre>";
			print_r($cinfo);
			echo "</pre>";
		}
		
		
	}
	else
	{
		//log unexpected error for data not coming through
		echo "<h2>No files were able to be queried for use</h2>";
		echo "<p>$cinfo[2]<";
	}
	
	close_session($sid);
	
}
else
{
	//session wasn't created, log error
}



?>