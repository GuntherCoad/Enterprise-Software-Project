<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Upload to Existing Loan</title>
<!-- BOOTSTRAP STYLES-->
<link href="assets/css/bootstrap.css" rel="stylesheet" />
<!-- FONTAWESOME STYLES-->
<link href="assets/css/font-awesome.css" rel="stylesheet" />
   <!--CUSTOM BASIC STYLES-->
<link href="assets/css/basic.css" rel="stylesheet" />
<!--CUSTOM MAIN STYLES-->
<link href="assets/css/custom.css" rel="stylesheet" />
<!-- PAGE LEVEL STYLES -->
<link href="assets/css/bootstrap-fileupload.min.css" rel="stylesheet" />
<!-- JQUERY SCRIPTS -->
<script src="assets/js/jquery-1.10.2.js"></script>
<!-- BOOTSTRAP SCRIPTS -->
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/bootstrap-fileupload.js"></script>
</head>
<body>
	<!-- lel-->
	<?php
	include("../functions.php");
	$dblink=db_connect("CS4743F2024");
	
	echo '<div id="page-inner">';
	echo '<a href="upload_main.php" class="btn btn-sm btn-block btn-success">Back</a>';
	echo '<h1 class="page-head-line">Upload a New File to Existing Loan</h1>';
	echo '<div class="panel-body">';
	
	if(isset($_GET['error']))
	{
		if (strstr($_GET['error'], "FileMimeInvalid"))
			echo '<div class="alert alert-danger" role="alert">You must upload a PDF only</div>';
		if(strstr($_GET['error'], "loanNumNull"))
			echo '<div class="alert alert-danger" role="alert">You must select a loan number from the list</div>';
		if(strstr($_GET['error'], "invalidLoanNum"))
			echo '<div class="alert alert-danger" role="alert">Loan numbers must be between 5-9 digits and only numbers</div>';
		if(strstr($_GET['error'], "queryFail"))
			echo '<div class="alert alert-danger" role="alert">Loan was unable to be uploaded</div>';
		if(strstr($_GET['error'], "failedFileUpload"))
			echo '<div class="alert alert-danger" role="alert">File was unable to be processed</div>';
		
	}
	if(isset($_GET['success']))
	{
		if(strstr($_GET['success'], "true"))
			echo '<div class="alert alert-success" role="alert">Loan was successfully uploaded</div>';
	}
	echo '<form method="post" enctype="multipart/form-data" action="">';
	echo '<input type="hidden" name="MAX_FILE_SIZE" value="10000000>"';
	echo '<div class="form-group>"';
	echo '<label for="loanNum" class="control-label">Loan Number</label>';
	echo '<select class="form-control" name="loanNum">';
	//echo '<input list="loanIDs" name="loanNum">';
	//echo '<datalist id="loanIDs">';
	
	$sql = "Select distinct `loan_num` from `documents_info` order by `loan_num` ASC";
	$resultBool = $dblink->query($sql);
	
	if(!$resultBool) {
		log_sql_error($fileName, $date, $execution_time, "ERROR", $dblink->error);
		redirect("upload_main.php?error=PageFail");
	}
	
	while($data=$resultBool->fetch_array(MYSQLI_ASSOC))
	{
		echo '<option value="'.$data['loan_num'].'">'.$data['loan_num'].'</option>';
	}
	
	echo '</select>';
	//echo '</datalist>';

	echo '</div>';
	echo '<select class="form-control" name="docType">';
	
	
	
	$sql="Select * from `doc_types`";
	$resultBool = $dblink->query($sql);
	
	if(!$resultBool) {
		log_sql_error($fileName, $date, $execution_time, "ERROR", $dblink->error);
		redirect("upload_main.php?error=PageFail");
	}
	
	while($data=$resultBool->fetch_array(MYSQLI_ASSOC))
	{
		echo '<option value="'.$data['doc_type'].'">'.$data['doc_type'].'</option>';
	}
	
	echo '</select>';
	
	echo '<div class="form-group">';
	echo '<label class="control-label col-lg-4">File Upload</label>';
	echo '<div class="">';
	echo '<div class="fileupload fileupload-new" data-provides="fileupload">';
	echo '<div class="fileupload-preview thumbnail" style="width: 200px; height: 150px;">';
	echo '</div>';
	echo '<div class="row">';
	echo '<div class="col-md-2">';
	echo '<span class="btn btn-file btn-primary">';
	echo '<span class="fileupload-new">Select File</span>';
	echo '<span class="fileupload-exists">Change</span>';
	echo '<input name="userfile" type="file"></span>';
	echo '</div>';
	echo '<div class="col-md-2">';
	echo '<a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">Remove</a>';
	echo '</div>
	</div>
	</div>
	</div>
	</div>
	<hr>';
	echo '<button type="submit" name="submit" value="submit" class="btn btn-lg btn-block btn-success">Upload File</button>';
	echo '</form>';
	
	
	echo '</div>';//end panel-body
	echo '</div>';
	
	if(isset($_POST['submit']) && $_POST['submit'] == "submit")
	{
		
		$errMsg = "";
		
		$fileMime = $_FILES['userfile']['type'];//var for file upload mime type
		if(is_null($fileMime))
			$errMsg .= "FileMimeNull";
		elseif($fileMime != "application/pdf"){
			$errMsg .= "FileMimeInvalid";
		}
		
		//No longer applicable as loans are selected from predetermined list
		//left for posterity
		$loanNumber = $_POST['loanNum'];
		if ($loanNumber == NULL)
			$errMsg .= "loanNumNull";
		elseif(!preg_match("/^[0-9]{5,9}$/", $loanNumber) )
			$errMsg .="invalidLoanNum";
		
		if($errMsg != NULL)
				redirect("upload_existing.php?error=$errMsg");
		else
		{
			
			
			$createDate = new DateTime("now", new DateTimeZone('America/Chicago'));
			$uploadCreateDate = date_format($createDate, 'Y-m-d H:i:s') . "";
			$docType = $_POST['docType'];
			$fileName = $_FILES['userfile']['name'];//var for file upload name
			$fileSize = $_FILES['userfile']['size'];
			$fileCreateDate = $createDate->format('Ymd_H_i_s');
			

			$fileContents = $_FILES['userfile']['tmp_name'];//var for holding file upload contents
			
			//wahoo error checks baby
			$fp;
			$exex_start;
			$exex_end;
			$contents;
			
			try{
				$exec_start = microtime(true);
				$fp = fopen($fileContents, "r");
				if (!$fp)
				{
					throw new Exception("Uploaded file could not be accessed");
				}
			}
			catch(Exception $e) {
				$exec_end = microtime(true);
				$execution_time=($exec_end-$exec_start) * 1000;
				date_default_timezone_set("America/Chicago");
				$createDate = date('Y/m/d H:i:s');
				echo log_write_error($user, $createDate, $execution_time, "ERROR", $e->getMessage());
				fclose($fp);
				redirect("upload_existing.php?error=failedFileUpload");
			}
			try {
				$exex_start = microtime(true);
				$contents = fread($fp, filesize($fileContents));
				
				if(!$contents)
				{
					throw new Exception("File $fileName could not be read");
				}
			}
			catch(Exception $e)
			{
				$exex_end = microtime(true);
				$execution_time=($exec_end-$exec_start) * 1000;
				date_default_timezone_set("America/Chicago");
				$createDate = date('Y/m/d H:i:s');
				echo log_write_error($user, $createDate, $execution_time, "ERROR", $e->getMessage());
				fclose($fp);
				redirect("upload_existing.php?error=failedFileUpload");
			}
			
			fclose($fp);
			
			$contentsClean = addslashes($contents);//database ready var with escape chars inserted
			
			$sql = "SELECT MAX(upload_num) as MaxUploadNum FROM `documents_info` WHERE loan_num = '$loanNumber' and `doc_type` = '$docType'";
			$result = $dblink->query($sql) or 
				die("Something went wrong with $sql <br>".$dblink->error);
			
			$uploadNum = $result->fetch_array(MYSQLI_ASSOC);
			
			$numChecked = 1;
			
			if(is_null($uploadNum['MaxUploadNum']))
				$numChecked = 1;
			else
				$numChecked = $uploadNum['MaxUploadNum'] + 1;
			
			
			$uploadName = $loanNumber ."-". $docType ."_". $numChecked . "-". $fileCreateDate;
			
			
			$exex_start = microtime(true);
			$sql="Insert into `documents_info`(`file_name`,`loan_num`,`doc_type`,`upload_num`,`file_size`,`file_create_date`,`upload_date`,`file_type`,`upload_type`) values('$uploadName', '$loanNumber', '$docType', '$numChecked', '$fileSize', '$uploadCreateDate', '$uploadCreateDate', '$fileMime', 'manual')";
			
			$resultBool = $dblink->query($sql);
			
			$exexc_end = microtime(true);
			$execution_time = ($exec_end - $exec_start) * 1000;
			
			if(!$resultBool) {
				log_sql_error($fileName, $uploadCreateDate, $execution_time, "ERROR", $dblink->error);
				redirect("upload_existing.php?error=queryFail");
			}
			
			
			$exec_start = microtime(true);
			$sql = "Insert into `documents_content`(`file_name`,`doc_content`) values ('$uploadName','$contentsClean')";
			
			$resultBool = $dblink->query($sql);
			
			$exec_end = microtime(true);
			$execution_time = ($exec_end - $exec_start) * 1000;
			
			if(!$resultBool) {
				log_sql_error($fileName, $date, $execution_time, "ERROR", $dblink->error);
				redirect("upload_existing.php?error=queryFail");
			}
			
			log_sql_success("null", $uploadCreateDate, $execution_time, "OK", "$uploadName was uploaded into dbadmin");
			redirect("upload_existing.php?success=true");

			/*echo '<h2>File upload data: </h2>';
			echo '<h3>Document type: '.$docType.'</h3>';
			echo '<h3>loan number: '.$loanNumber.'</h3>';
			echo '<h3>fileName: '.$fileName.'</h3>';
			echo '<h3>fileType: '.$fileMime.'</h3>';
			echo '<h3>file creation time: '.$fileCreateDate.'</h3>';
			echo '<h3>file upload time: '.$uploadCreateDate.'</h3>';
			echo '<h3>upload name: '.$uploadName.'</h3>';
			echo '<h3>upload number: '.$numChecked.'</h3>';*/
		}
		
		
		
	}

	?>
</body>