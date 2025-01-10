<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>View Document</title>
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
<?php
	include("../functions.php");
	$dblink=db_connect("CS4743F2024");
	
	echo '<div id="page-inner">';
	echo '<a href="search_Doctype.php" class="btn btn-sm btn-block btn-success">Back</a>';
	echo '</div>';
	
	echo '<div id="page-inner">';
	echo '<h1 class="page-head-line">View Document</h1>';
	echo '<div class="panel-body">';
	$docID = $_GET['id'];
	
	$sql = "select * 
			from `documents_info` 
			join `documents_content`
			on `documents_info`.`file_name` = `documents_content`.`file_name`
			where `auto_id` = $docID";
	
	$resultBool = $dblink->query($sql);

	if(!$resultBool) {
		//log_sql_error("null", $date, $execution_time, "ERROR", $dblink->error);
		redirect("search_main.php?error=PageFail");
	}
	$data=$resultBool->fetch_array(MYSQLI_ASSOC);
	
	if(!isset($data['doc_content']))
	{
		redirect("search_main.php?error=NoFileErr");
	}
		
	
	//this displays the file without downloading it into the filesystem
	//no need for cronjob
	//file can only be viewed during view_doc search
	header('Content-type: '.$data['file_type'].'');
	echo $data['doc_content'];
	
	date_default_timezone_set("America/Chicago");
	$now = date('Y-m-d H:i:s');
	
	$sql = "update `documents_info`
			set `last_access`='$now'
			where `auto_id`='$docID'";
	
	$resultBool = $dblink->query($sql);

	if(!$resultBool) {
		//log_sql_error("null", $date, $execution_time, "ERROR", $dblink->error);
		redirect("search_main.php?error=updateFail");
	}
	
	echo '</div>';
	echo '</div>';
	
?>
</body>
</html>