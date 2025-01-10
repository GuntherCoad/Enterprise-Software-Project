<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Upload Main Page</title>
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

	echo '<div id="page-inner">';
	echo '<h1 class="page-head-line">Upload a New File to Database</h1>';
	echo '<div class="panel-body">';
	
	if(isset($_GET['error']))
	{
		if (strstr($_GET['error'], "PageFail"))
			echo '<div class="alert alert-danger" role="alert">The page you were previously on encountered an error</div>';
	}
	
	echo '<p><a class="btn btn-primary" href="upload_new.php">Upload New Loan</a></p>';
	echo '<p><a class="btn btn-primary" href="upload_existing.php">Upload Existing Loan</a></p>';
	echo '</div>';//end panel-body
	echo '</div>';

	?>
</body>