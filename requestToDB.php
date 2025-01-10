<?php

//Behold my works, ye mighty, and despair!
//Piles of junk lay beside

include ("functions.php");
$dblink=db_connect("CS4743F2024");
$files;
$exex_start;
$exex_end;
$execution_time;
$dConnect_start = microtime(true);
//try to set the directory
try {
	
	$files = new DirectoryIterator("/var/www/files");
	
}
catch(UnexpectedValueException $e) {
	//log that, somehow
	date_default_timezone_set("America/Chicago");
	$date = date('Y/m/d H:i:s');
	$dConnect_end = microtime(true);
	$execution_time = ($dConnect_end - $dConnect_start) * 1000;
	log_sql_error("/var/www/files", $date, $execution_time, "ERROR", "/var/www/files/ does not exist");
	exit(1);
}
catch(ValueError $e)
{
	date_default_timezone_set("America/Chicago");
	$date = date('Y/m/d H:i:s');
	$dConnect_end = microtime(true);
	$execution_time = ($dConnect_end - $dConnect_start) * 1000;
	log_sql_error("/var/www/files", $date, $execution_time, "ERROR", "inputed directory is an empty string");
	exit(1);
}




foreach($files as $value)
{
	if ($value->isFile())
	{
		$filename = $value->getFilename();
		$fileType = mime_content_type("/var/www/files/$filename");
		$filesize = $value->getSize();
		$tmp = explode("-",$value);
		$loanNum = $tmp[0];
		$docTypeNum = $tmp[1];
		$docDateExt = $tmp[2];
		
		//has format "doctype,num"
		//assumes there is num, error check
		$docTypeArr = explode("_", $docTypeNum);
		if(count($docTypeArr) == 1)
		{
			$uploadNum = 1;
		}
		else
		{
			$uploadNum = (int) $docTypeArr[1] + 1;
		}
		
		
		// has format "YYYYMMDD,HH,mm,SS.ext"
		//.ext has no predfined length, use explode
		$docDateArr = explode("_", $docDateExt);
		$year = substr($docDateArr[0], 0, 4);
		$month = substr($docDateArr[0], 4, 2);
		$day = substr($docDateArr[0], 6, 2);
		
		$hour = $docDateArr[1];
		$minute = $docDateArr[2];
		$second = substr($docDateArr[3], 0, 2);
		
		//was originally UTC in filenames, changed to CDT as of 10/31/2024
		$dateString = new DateTime("$year-$month-$day $hour:$minute:$second", new DateTimeZone('America/Chicago'));
		
		//$dateString->setTimezone(new DateTimeZone('America/Chicago'));
		
		$fileCreateDate = $dateString->format('Y-m-d H:i:s');
		
		$fileContents = file_get_contents("/var/www/files/$filename");
		$contentClean = addslashes($fileContents);
		
		date_default_timezone_set("America/Chicago");
		$date = date('Y/m/d H:i:s');
		
		$sql="select `file_name` from `documents_info` where `file_name` = '$filename'";
		$resultBool = $dblink->query($sql);
		
		if(mysqli_num_rows($resultBool) >= 1)
		{
			goto checkInContent;
		}
		
		#echo $loanNum . " " . $docTypeArr[0] . " " . $uploadNum . " " . $filesize . " " . $fileCreateDate . " " . $date . " null " . $fileType . " content " . "cron" . "<br>"; 
		$exex_start = microtime(true);
		$sql="Insert into `documents_info`(`file_name`,`loan_num`,`doc_type`,`upload_num`,`file_size`,`file_create_date`,`upload_date`,`file_type`,`upload_type`) values
		('$filename','$loanNum','$docTypeArr[0]','$uploadNum','$filesize','$fileCreateDate','$date','$fileType','cron')";
		
		//best do a try catch with query
		//maybe make a new logging function while you're at it
		//intended to return True on success, False on failure
		$resultBool = $dblink->query($sql);
		$exex_end = microtime(true);
		$execution_time = ($exex_end - $exex_start) * 1000;
			//or
			//die("Something went wrong with: $sql<br>".$dblink->error);
		
		if(!$resultBool) {
			log_sql_error($filename, $date, $execution_time, "ERROR", $dblink->errno);
			continue;
		}
		
		checkInContent:
		$sql="select `file_name` from `documents_content` where `file_name` = '$filename'";
		$resultBool = $dblink->query($sql);
		
		if(mysqli_num_rows($resultBool) >= 1)
		{
			goto deleteFSF;
		}
		
		
		$sql="Insert into `documents_content`(`file_name`,`doc_content`) values
		('$filename','$contentClean')";
		
		$exex_start = microtime(true);
		$resultBool = $dblink->query($sql);
		$exex_end = microtime(true);
		$execution_time = ($exex_end - $exex_start) * 1000;
		
		if(!$resultBool) {
			log_sql_error($filename, $date, $execution_time, "ERROR", $dblink->errno);
			continue;
		}
		
		log_sql_success($filename, $date, $execution_time, "OK", "$filename was uploaded into dbadmin");
		
		
		//delete filesystem file
		deleteFSF:
		$isDeleted = unlink("/var/www/files/$filename");

		if($isDeleted == false)
		{
			$execution_time = ($exex_end - $exex_start) * 1000;
			log_sql_error($filename, $date, $execution_time, "ERROR", "/var/www/files/$filename could not be deleted");
		}
		else
		{
			$execution_time = ($exex_end - $exex_start) * 1000;
			log_sql_success($filename, $date, $execution_time, "OK", "/var/www/files/$filename has been removed");
		}
			
			
		
		
	}

	#use mime_content_type for extension
	#$sql="Insert into `received`
	#(`loan_number`,`title`,`date`,`filetype`,`filesize`,`content`) values ('$loannumber', '$title', '$date', '$filetype', '$filesize', '$contentclean')";
	#$dblink->query($sql) or
	#die("Something went wrong with $sql<br>".$dblink->error);
}
?>