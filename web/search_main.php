<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Search Main</title>
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
	echo '<h1 class="page-head-line">Select search criteria</h1>';
	echo '<div class="panel-body">';
	//God will punish me for my transgressions, but I have a due date so whatever
	if(isset($_GET['error']))
	{
		//updateFail
		//NoFileErr
		//LoanNumFail
		//DocTypeFail
		if (strstr($_GET['error'], "PageFail"))
			echo '<div class="alert alert-danger" role="alert">The page you were previously on encountered an error.</div>';
		elseif (strstr($_GET['error'], "noCriteria"))
			echo '<div class="alert alert-danger" role="alert">Please select a criteria to search from.</div>';
		elseif (strstr($_GET['error'], "updateFail"))
			echo '<div class="alert alert-danger" role="alert">Unable to update time last accessing file.</div>';
		elseif (strstr($_GET['error'], "NoFileErr"))
			echo '<div class="alert alert-danger" role="alert">Unable to retrieve file for viewing.</div>';
		elseif (strstr($_GET['error'], "LoanNumFail"))
			echo '<div class="alert alert-danger" role="alert">Unable to get loan number options.</div>';
		elseif (strstr($_GET['error'], "DocTypeFail"))
			echo '<div class="alert alert-danger" role="alert">Unable to get Document Type options.</div>';
	}
	
	echo '<p><a class="btn btn-primary" href="search_Doctype.php?criteria=loanNum">By Loan ID</a></p>';
	echo '<p><a class="btn btn-primary" href="search_Doctype.php?criteria=docType">By Document Type</a></p>';
	echo '<p><a class="btn btn-primary" href="search_Doctype.php?criteria=date">By Date</a></p>';
	echo '<p><a class="btn btn-primary" href="search_Doctype.php?criteria=AllDocs">All Documents</a></p>';
	echo '</div>';//end panel-body
	echo '</div>';

	?>
</body>