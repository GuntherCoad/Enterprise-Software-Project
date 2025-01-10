<?php

//allows for dynamic connection to various databases
function db_connect($db)
{
	//ODBC string
	$dbUserName = "webuser";
	$dbPassword = "6)*z@1f.I9mjC@Jm";
	$host = "localhost";
	$dblink=new mysqli($host, $dbUserName, $dbPassword, $db);
	return $dblink;
}

//grabbed this from 
//https://stackoverflow.com/questions/2162497/efficiently-counting-the-number-of-lines-of-a-text-file-200mb
function getLines($file)
{
    $f = fopen($file, 'rb');
    $lines = 0;

    while (!feof($f)) {
        $lines += substr_count(fread($f, 8192), "\n");
    }

    fclose($f);

    return $lines;
}

//meant to set 2d arr in dealWithLoans.php to zero values
function twoDArrSetZero($twoDArr)
{
	foreach($twoDArr as $docType)
	{
		$docType['occurrence'] = 1;
	}
}

//grabbed this from https://www.php.net/manual/en/function.filesize.php
//in the comment section
function human_filesize($bytes, $decimals = 2) {
    $factor = floor((strlen($bytes) - 1) / 3);
    if ($factor > 0) $sz = 'KMGT';
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor - 1] . 'B';
}

function globalAvgDocSize($sqliLink)
{
	$sql = "SELECT `file_size` FROM `documents_info` where file_create_date > '2024-11-01 00:00:00'";
	$total = 0;
	//actual list of unique loan nums
	$resultBool = $sqliLink->query($sql);

	//total number of unique loan nums
	$numRows = mysqli_num_rows($resultBool);

	//assuming its just asking for the average size of all files queried
	//nothing to really say otherwise, Prof would have clarified

	while($data=$resultBool->fetch_array(MYSQLI_ASSOC))
	{
		$total += $data['file_size'];
	}

	//is now avg of all files
	$total = $total / $numRows;
	
	return $total;
	
}

function globalAvgDocCount($sqliLink)
{
	$sql = "SELECT count(`auto_id`) as totalDoc FROM `documents_info` where file_create_date >= '2024-11-01 00:00:00'";
	$total = 0;
	//actual list of all docs
	$resultBool = $sqliLink->query($sql);

	$data = $resultBool->fetch_array(MYSQLI_ASSOC);

	$total = $data['totalDoc'];

	$sql = "select count(distinct loan_num) as uniqCount from `documents_info` where file_create_date >= '2024-11-01 00:00:00'";

	//grabbing count of all unique loan nums for global average
	$resultBool = $sqliLink->query($sql);
	$data = $resultBool->fetch_array(MYSQLI_ASSOC);

	$count = $data['uniqCount'];
	//is now avg of all files
	$total = $total / $count;
	
	return $total;
}

//Creates a web session to a predefined server
function create_session()
{
	$username="ano523";
	$password="dgWYMT#J32ymrpLP";
	$data="username=$username&password=$password";
	$result;
	$createDate;
	$counter = 0;
	
	//try a goto loop 10 times; 5 minutes
	loop:
	$curl_handler=curl_init('https://cs4743.professorvaladez.com/api/create_session');
	
	curl_setopt($curl_handler, CURLOPT_POST, 1);
	curl_setopt($curl_handler, CURLOPT_POSTFIELDS, $data); 
	curl_setopt($curl_handler, CURLOPT_LOW_SPEED_TIME, intval(30));
	curl_setopt($curl_handler, CURLOPT_LOW_SPEED_LIMIT, intval(60));
	curl_setopt($curl_handler, CURLOPT_FRESH_CONNECT, true);
	curl_setopt($curl_handler, CURLOPT_FORBID_REUSE, true);
	curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_handler, CURLOPT_TCP_KEEPALIVE, 1);
	curl_setopt($curl_handler, CURLOPT_TCP_KEEPINTVL, 30);
	curl_setopt($curl_handler, CURLOPT_HTTPHEADER, array(
		'content-type: application/x-www-form-urlencoded', 
		'content-length: '. strlen($data)));
	
	

	//logging only response time from api server
	$time_start=microtime(true);
	try {
		$result=curl_exec($curl_handler);
		//error checking
		//echo $result . "\r\n";
		if(curl_errno($curl_handler) == 28)
		{
			//echo 'Curl error: ' . curl_error($curl_handler);
			throw new Exception("Session creation timeout");
		}
		
		
		
		if(strpos($result, "404 Not Found") != false)
		{
			throw new Exception("No session could be created");
		}
		elseif(is_null($result))
		{
			throw new Exception("Failed to receive data from API.");
		}
	}
	catch(Exception $e)
	{
		$time_end=microtime(true);
		date_default_timezone_set("America/Chicago");
		$createDate = date('Y-m-d H:i:s');
		$execution_time=($time_end-$time_start) * 1000;
		//log_session_error("null", $createDate, $execution_time, "ERROR", $e->getMessage());
		write_session("null", $execution_time, "ERROR", $e->getMessage());
		curl_close($curl_handler);
		$counter += 1;
		if ($counter <= 10)
		{
			goto loop;
		}
		return null;
	}
	$time_end=microtime(true);
	//left as is, displays in milliseconds
	$execution_time=($time_end-$time_start) * 1000;
	curl_close($curl_handler);
	date_default_timezone_set("America/Chicago");
	$createDate = date('Y/m/d H:i:s');
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
		//log_session_error("null", $createDate, 0, "ERROR", $e->getMessage());
		write_session("null", $execution_time, "ERROR", $e->getMessage());
		$counter += 1;
		if ($counter <= 10)
		{
			goto loop;
		}
		return null;
	}
	
	 
	$tmp=explode(":",$cinfo[0]);
	$status =$tmp[1];
	$user = $cinfo[2];
	$message = explode(":", $cinfo[1]);
	//slightly common issue where 
	//echo $cinfo[0] . " " . $cinfo[1] . " " . $cinfo[2] . "\r\n";
	
	//checking for successful session create, logging session metadata
	if($cinfo[0] == "Status: OK" && $cinfo[1] == "MSG: Session Created")
	{	
		//log_session_success($user, $createDate, $execution_time, $status, $message[1]);
		//write_session($user, $execution_time, $status, $message[1]);
	}
	//logging failed session creation for some reason or another
	//Status: ERROR | MSG: Previous Session Found | Action: Must clear session first
	//if previous session found for any reason, purge it and move on
	elseif($cinfo[2] == "Action: Must clear session first")
	{
		
		//message should have content portion of cinfo[1]
		//log_session_error("null", $createDate, $execution_time, "ERROR", $message[1]);
		clear_session();
		$counter += 1;
		goto loop;

	}
	
	
	
	return $cinfo;
}

function clear_session()
{
	$username="ano523";
	$password="dgWYMT#J32ymrpLP";
	$data="username=$username&password=$password";
	$counter = 0;
	
	loop:
	$curl_handler=curl_init('https://cs4743.professorvaladez.com/api/clear_session');
	$result;
	curl_setopt($curl_handler, CURLOPT_POST, 1);
	curl_setopt($curl_handler, CURLOPT_POSTFIELDS, $data);
	curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_handler, CURLOPT_HTTPHEADER, array(
		'content-type: application/x-www-form-urlencoded', 
		'content-length: '. strlen($data)));

	//logging only response time from api server
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
		$time_end=microtime(true);
		date_default_timezone_set("America/Chicago");
		$createDate = date('Y-m-d H:i:s');
		$execution_time=($time_end-$time_start) * 1000;
		//log_session_error("null", $createDate, $execution_time, "ERROR", $e->getMessage());
		write_session("null", $execution_time, "ERROR", $e->getMessage());
		curl_close($curl_handler);
		$counter += 1;
		if ($counter <= 10)
		{
			goto loop;
		}
		
	}
	$time_end=microtime(true);
	//left as is, displays in milliseconds
	$execution_time=($time_end-$time_start) * 1000;
	curl_close($curl_handler);
	
	date_default_timezone_set("America/Chicago");
	$createDate = date('Y/m/d H:i:s');
	
	//always give info, even if error data
	$cinfo=json_decode($result,true);
	
	$tmp=explode(":",$cinfo[0]);
	$status = $tmp[1];
	$message = explode(":", $cinfo[1]);
	
	if($cinfo[0] == "Status: OK" && $cinfo[1] == "MSG: Previous Session Found")
	{
		//might need to query database for user in future
		//log_session_success("null", $createDate, $execution_time, $status, $message[1]);
		//write_session("null", $execution_time, $status, $message[1]);
	}
	else
	{
		//log error
		//log_session_error("null", $createDate, $execution_time, $status, $message[1]);
		write_session("null", $execution_time, $status, $message[1]);
		$counter += 1;
		if ($counter <= 10)
		{
			goto loop;
		}
	}
}

function close_session($sid)
{
	$username="ano523";
	$password="dgWYMT#J32ymrpLP";
	$data="sid=$sid";
	$counter = 0;
	
	loop:
	$curl_handler=curl_init('https://cs4743.professorvaladez.com/api/close_session');
	$result;
	curl_setopt($curl_handler, CURLOPT_POST, 1);
	curl_setopt($curl_handler, CURLOPT_POSTFIELDS, $data);
	curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_handler, CURLOPT_HTTPHEADER, array(
		'content-type: application/x-www-form-urlencoded', 
		'content-length: '. strlen($data)));

	//logging only response time from api server
	$time_start=microtime(true);
	try {
		$result=curl_exec($curl_handler);
		if(is_null($result))
		{
			throw new Exception("Failed to receive data from API.");
		}
	}
	catch(Exception $e)
	{
		$time_end=microtime(true);
		//date_default_timezone_set("America/Chicago");
		//$createDate = date('Y-m-d H:i:s');
		$execution_time=($time_end-$time_start) * 1000;
		//log_session_error("null", $createDate, $execution_time, "ERROR", $e->getMessage());
		write_session("null", $execution_time, "ERROR", $e->getMessage());
		curl_close($curl_handler);
		$counter += 1;
		if ($counter <= 10)
		{
			goto loop;
		}
		
	}
	$time_end=microtime(true);
	//left as is, displays in milliseconds
	$execution_time=($time_end-$time_start) * 1000;
	curl_close($curl_handler);
	
	date_default_timezone_set("America/Chicago");
	$createDate = date('Y/m/d H:i:s');
	
	//always give info, even if error data
	$cinfo=json_decode($result,true);
	$tmp=explode(":",$cinfo[0]);
	$status = $tmp[1];
	$messageArr = explode(":", $cinfo[1]);
	
	if($cinfo[0] == "Status: OK" && $cinfo[1] == "MSG: SID closed successfully")
	{
		
		
		//log_session_success($sid, $createDate, $execution_time, $status, $messageArr[1]);
		//write_session($sid, $execution_time, $status, $messageArr[1]);
	}
	else
	{
		//log_session_error($sid, $createDate, $execution_time, $status, $messageArr[1]);
		write_session($sid, $execution_time, $status, $messageArr[1]);
		$counter += 1;
		if ($counter <= 10)
		{
			goto loop;
		}
	}
	
}

function request_file($sid, $username, $value)
{
	$data="sid=$sid&uid=$username&fid=$value";
	$curl_handler=curl_init('https://cs4743.professorvaladez.com/api/request_file');
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
		echo $result . "\r\n";
		if(is_null($result))
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
	$execution_time=($time_end-$time_start) * 1000;
	curl_close($curl_handler);

	if(strstr($result, "Status"))
	{
		$payload = json_decode($result, true);
		$statusArr = explode(":", $payload[0]);
		$errMsgArr = explode(":", $payload[1]);
		
		date_default_timezone_set("America/Chicago");
		$createDate = date('Y/m/d H:i:s');
		
		//log_session_error($sid, $createDate, $execution_time, $statusArr[1], $errMsgArr[1]);
		write_session($sid, $execution_time, $statusArr[1], $errMsgArr[1]);
		//continue;
		//was giving errors, initially part of looping through queried files

	}
	else
	{
		$content=$result;
		if(strlen($content) == 0)
		{
			echo "<h2> file $value received zero length</h2>";
		}
		//$fp=fopen("/var/www/files/$value", "wb");
		//writing to file
		//this needs try-catch
		//fwrite($fp, $content);
		//fclose($fp);
		echo "<h3>file $value was written to filesSystem</h3>";
	}
}

function write_session($user, $execution_time, $status, $Msg)
{
	$sessionLogPoint;
	try {
			$sessionLogPoint = fopen("/var/www/logs/sessions.log", "a");

			if(!$sessionLogPoint)
			{
				throw new Exception("Could not access file /var/www/logs/sessions.log");
			}
		}
		catch (Exception $e)
		{
			date_default_timezone_set("America/Chicago");
			$createDate = date('Y/m/d H:i:s');
			echo log_write_error($user, $createDate, 0, 0, "ERROR", $e->getMessage());
			fclose($sessionLogPoint);
		}
		
		try {
			date_default_timezone_set("America/Chicago");
			$createDate = date('Y/m/d H:i:s');
			$didWrite = fwrite($sessionLogPoint, log_session_error($user, $createDate, $execution_time, $status, $Msg));
			if ($didWrite == false )
			{
				throw new Exception("log unable to be written to /var/www/logs/sessions.log");
			}
		}
		catch(Exception $e) {
			date_default_timezone_set("America/Chicago");
			$createDate = date('Y/m/d H:i:s');
			echo log_write_error($user, $createDate, 0, 0, "ERROR", $e->getMessage());
			fclose($fp);
			//close_session($sid);
		}
	fclose($sessionLogPoint);
}

function log_session_success($user, $creationDate, $executionTime, $status, $message)
{
	$retMsg = "user=$user, date=$creationDate, execution time=$executionTime, status=$status, message=$message\r\n";
	echo $retMsg;
	return $retMsg;
	
}

function log_session_error($user, $creationDate, $executionTime, $status, $message)
{
	$retMsg = "user=$user, date=$creationDate, execution time=$executionTime, status=$status, message=$message\r\n";
	echo $retMsg;
	return $retMsg;
}

function log_file_write_success($user, $creationDate, $executionTime, $fileSize, $status, $message)
{
	$retMsg = "user=$user, date=$creationDate, execution time=$executionTime, filename length=$fileSize, status=$status, message=$message\r\n";
	echo $retMsg;
	return $retMsg;
}

function log_write_success($user, $creationDate, $executionTime, $fileSize, $status, $message)
{
	$retMsg = "user=$user, date=$creationDate, execution time=$executionTime, file size=$fileSize, status=$status, message=$message\r\n";
	echo $retMsg;
	return $retMsg;
}

function log_write_error($user, $creationDate, $executionTime, $fileSize, $status, $message)
{
	$retMsg = "user=$user, date=$creationDate, execution time=$executionTime, file size=$fileSize, status=$status, message=$message\r\n";
	return $retMsg;
}

function log_query_success($user, $creationDate, $executionTime, $APIFetchTime, $filesQueried, $status, $message)
{
	$retMsg = "user=$user, date=$creationDate, execution time=$executionTime, API fetch time=$APIFetchTime, files queried=$filesQueried, status=$status, message=$message\r\n";
	echo $retMsg;
	return $retMsg;
}

function log_query_error($user, $creationDate, $executionTime, $filesQueried, $status, $message)
{
	$retMsg = "user=$user, date=$creationDate, execution time=$executionTime, files queried=$filesQueried, status=$status, message=$message\r\n";
	echo $retMsg;
	return $retMsg;
	
}


//logging info:
			//	filename
			//	date of insert
			//	time of execution
			//	sql success/error msg
function log_sql_success($filename, $insert_date, $executionTime, $status, $message)
{
	$retMsg = "user=$filename, date=$insert_date, execution time=$executionTime, status=$status, message=$message\r\n";
	echo $retMsg;
	return $retMsg;
}

function log_sql_error($filename, $insert_date, $executionTime, $status, $message) {
	$retMsg = "user=$filename, date=$insert_date, execution time=$executionTime, status=$status, message=$message\r\n";
	echo $retMsg;
	return $retMsg;
}

function redirect ($uri)
{?>
	<script type="text/javascript">
		document.location.href="<?php echo $uri; ?>";
	</script>
<?php
}
?>