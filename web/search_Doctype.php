<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Search Document Type</title>
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
	
	
	if($_GET['criteria'] == "")
	{
		//add in error log
		redirect("search_main.php?error=noCriteria");
	}
	elseif(is_null($_GET['criteria']))
		redirect("search_main.php?error=noCriteria");
	
	if(!isset($_POST['submit']))
	{
		echo '<div id="page-inner">';
		echo '<h1 class="page-head-line">Select search criteria</h1>';
		echo '<div class="panel-body">';
		
		echo '<div id="page-inner">';
		echo '<a href="search_main.php" class="btn btn-sm btn-block btn-success">Back</a>';
		echo '</div>';

		if(isset($_GET['error']))
		{
			//searchFail
			if (strstr($_GET['error'], "searchFail"))
				echo '<div class="alert alert-danger" role="alert">A problem occurred while searching</div>';
		}

		
		if($_GET['criteria'] == "docType")
		{
			//loanNumError
			//noResult
			//AllnoResult
			if(isset($_GET['error']))
			{
				if (strstr($_GET['error'], "loanNumNull"))
					echo '<div class="alert alert-danger" role="alert">please select a loan number</div>';
				elseif (strstr($_GET['error'], "noResult"))
					echo '<div class="alert alert-danger" role="alert">That loan has no documents of that particular type</div>';
				elseif (strstr($_GET['error'], "AllnoResult"))
					echo '<div class="alert alert-danger" role="alert">A major disruption has occured, please wait until further notice</div>';
			}

			echo '<form action="" method="post">';
			echo '<hr>';
			//check if want to search docType with particular loan or not
			
			echo '<label for="loanNum" class="control-label">Search with a loan or all loans:</label>';
			
			echo '<select class="form-control" name="loanNum">';
			echo '<option value="none">Search from all loans</option>';
		
			$sql="Select distinct `loan_num` from `documents_info` order by `loan_num` asc";
			$resultBool = $dblink->query($sql);

			if(!$resultBool) {
				//log_sql_error("null", $date, $execution_time, "ERROR", $dblink->error);
				redirect("search_main.php?error=LoanNumFail");
			}

			while($data=$resultBool->fetch_array(MYSQLI_ASSOC))
			{
				echo '<option value="'.$data['loan_num'].'">'.$data['loan_num'].'</option>';
			}

			echo '</select>';
			
			echo '<label for="docType" class="control-label">Document type</label>';
			echo '<select class="form-control" name="docType">';


			$sql="Select * from `doc_types`";
			$resultBool = $dblink->query($sql);

			if(!$resultBool) {
				//log_sql_error("null", $date, $execution_time, "ERROR", $dblink->error);
				redirect("upload_main.php?error=DocTypeFail");
			}
			
			if(mysqli_num_rows($resultBool) == 0)
				redirect("upload_main.php?error=DocTypeFail");

			while($data=$resultBool->fetch_array(MYSQLI_ASSOC))
			{
				echo '<option value="'.$data['doc_type'].'">'.$data['doc_type'].'</option>';
			}

			echo '</select>';
			echo '<hr>';
			echo '<button type="submit" name="submit" value="submit" class="btn btn-lg btn-block btn-success">Search</button>';
			echo '</form>';

			echo '</div>';//end panel-body}
		}
		elseif($_GET['criteria'] == "loanNum")
		{	
			echo '<form action="" method="post">';

			echo '<label for="loanNum" class="control-label">Document type</label>';
			echo '</div>';
			echo '<select class="form-control" name="loanNum">';
			
			$sql="Select distinct `loan_num` from `documents_info` order by `loan_num` asc";
			$resultBool = $dblink->query($sql);

			if(!$resultBool) {
				//log_sql_error("null", $date, $execution_time, "ERROR", $dblink->error);
				redirect("search_main.php?error=LoanNumFail");
			}
			
			if(mysqli_num_rows($resultBool) == 0)
				redirect("search_main.php?error=LoanNumFail");
 
			while($data=$resultBool->fetch_array(MYSQLI_ASSOC))
			{
				echo '<option value="'.$data['loan_num'].'">'.$data['loan_num'].'</option>';
			}

			echo '</select>';
			echo '<hr>';
			echo '<button type="submit" name="submit" value="submit" class="btn btn-lg btn-block btn-success">Search</button>';
			echo '</form>';

			echo '</div>';//end panel-body
		}
		
		elseif($_GET['criteria'] == "date")
		{
			//InvalidRange
			//InvalidDate
			//DuplicateDates
			//someDateNull
			if(isset($_GET['error']))
			{
				if (strstr($_GET['error'], "InvalidRange"))
					echo '<div class="alert alert-danger" role="alert">Please enter a valid date range</div>';
				elseif (strstr($_GET['error'], "InvalidDate"))
					echo '<div class="alert alert-danger" role="alert">Please enter a valid date</div>';
				elseif (strstr($_GET['error'], "DuplicateDates"))
					echo '<div class="alert alert-danger" role="alert">Date ranges must be at least one second apart</div>';
				elseif (strstr($_GET['error'], "someDateNull"))
					echo '<div class="alert alert-danger" role="alert">One or both dates were not set in the range of dates</div>';
			}
			//checks if want date or date range
			
			echo '<div class="alert alert-warning" role="alert">Due to some constraints, please only use the left date picker for single date searches</div>';
			
			echo '<form action="" method="post">';

			echo '<label for="from">single date or range selection</label>';
			echo '<select class="form-control" name=dateType id="dateType">';
			echo '<option value="single">Single date and time</option>';
			echo '<option value="range">date-time range</option>';
			echo '</select>';

			echo '<hr>';
			echo '<label for="from">From</label>';
			echo '<input type="datetime-local" step="1" id="from" name="from">';


			echo '<label for="to">To</label>';
			echo '<input type="datetime-local" step="1" id="to" name="to">';


			echo '<hr>';
			echo '<button type="submit" name="submit" value="submit" class="btn btn-lg btn-block btn-success">Search</button>';
			echo '</form>';
		}
		
		elseif($_GET['criteria'] == "AllDocs")
		{
			echo '<div id="page-inner">';
			echo '<h1 class="page-head-line">Results</h1>';
			echo '<div class="panel-body">';

			//echo '<div>'.$docNameID.'</div>';

			$sql = "Select `file_name`,`doc_type`,`loan_num`,`file_size`,`last_access`,`auto_id` 
					from `documents_info`  
					order by `loan_num` ASC";

			$resultBool = $dblink->query($sql);

			if(!$resultBool) {
			//log_sql_error("null", $date, $execution_time, "ERROR", $dblink->error);
			redirect("search_Doctype.php?error=searchFail");
			}
			
			if(mysqli_num_rows($resultBool) === 0)
				redirect("search_Doctype.php?criteria=docType&error=AllnoResult");

			echo '<table class="table table-hover">';
			echo '<tbody>';
			
			echo '<tr>
					<td>File Name</td>
					<td>Loan ID</td>
					<td>Document Type</td>
					<td>File Size</td>
					<td>Last Access</td>
					<td>View File</td>
				</tr>';

			while($data=$resultBool->fetch_array(MYSQLI_ASSOC))
			{
				echo '<tr>
					<td>'.$data['file_name'].'</td>
					<td>'.$data['loan_num'].'</td>
					<td>'.$data['doc_type'].'</td>
					<td>'.human_filesize($data['file_size']).'</td>
					<td>'.$data['last_access'].'</td>
					<td><a href="view_doc.php?id='.$data['auto_id'].'" target="_blank">Click here to view file</a></td>
				</tr>';
			}
			echo '</tbody>';
			echo '</table>';
			echo '</div>';//end panel-body
			echo '</div>';
		}
	}
	
	//whatever criteria form was submitted, now search results
	else
	{
		
		//checking results by document type(and loan number, depending on option selected)
		if(isset($_POST['docType']))
		{
			echo '<div id="page-inner">';
			echo '<h1 class="page-head-line">Results</h1>';
			echo '<div class="panel-body">';
			
			echo '<div id="page-inner">';
			echo '<a href="search_Doctype.php?criteria=docType" class="btn btn-sm btn-block btn-success">Back</a>';
			echo '</div>';

			$docTypeID = $_POST['docType'];
			$sql;
			$loanCheck;
			
			if(isset($_POST['loanNum']))
				$loanCheck = $_POST['loanNum'];
			else
				redirect("search_Doctype.php?criteria=docType&error=loanNumNull");
			
			if($loanCheck == "none")
			{
				$sql = "Select `file_name`,`doc_type`,`loan_num`,`file_size`,`last_access`,`auto_id` 
						from `documents_info` 
						where `doc_type` = '$docTypeID' 
						order by `loan_num` ASC";
			}
			else
			{
				$sql = "Select `file_name`,`doc_type`,`loan_num`,`file_size`,`last_access`,`auto_id` 
						from `documents_info` 
						where `doc_type` = '$docTypeID'
						and `loan_num` = '$loanCheck'
						order by `loan_num` ASC";
			}

			//echo '<div>'.$docTypeID.'</div>';
			//echo '<div>'.$loanCheck.'</div>';

			

			$resultBool = $dblink->query($sql);

			if(!$resultBool) {
				//log_sql_error("null", $date, $execution_time, "ERROR", $dblink->error);
				redirect("search_Doctype.php?error=searchFail");
			}

			echo '<table class="table table-hover">';
			echo '<tbody>';
			
			echo '<tr>
					<td>File Name</td>
					<td>Loan ID</td>
					<td>Document Type</td>
					<td>File Size</td>
					<td>Last Access</td>
					<td>View File</td>
				</tr>';
			
			if(mysqli_num_rows($resultBool) === 0)
				redirect("search_Doctype.php?criteria=docType&error=noResult");

			while($data=$resultBool->fetch_array(MYSQLI_ASSOC))
			{
				echo '<tr>
					<td>'.$data['file_name'].'</td>
					<td>'.$data['loan_num'].'</td>
					<td>'.$data['doc_type'].'</td>
					<td>'.human_filesize($data['file_size']).'</td>
					<td>'.$data['last_access'].'</td>
					<td><a href="view_doc.php?id='.$data['auto_id'].'" target="_blank">Click here to view file</a></td>
				</tr>';
			}
			echo '</tbody>';
			echo '</table>';
			echo '</div>';//end panel-body
			echo '</div>';
		}
		
		//checking results by loan number
		elseif(isset($_POST['loanNum']))
		{
			echo '<div id="page-inner">';
			echo '<h1 class="page-head-line">Results</h1>';
			echo '<div class="panel-body">';
			
			echo '<div id="page-inner">';
			echo '<a href="search_Doctype.php?criteria=loanNum" class="btn btn-sm btn-block btn-success">Back</a>';
			echo '</div>';

			$loanNumID = $_POST['loanNum'];

			//echo '<div>'.$loanNumID.'</div>';

			$sql = "Select `file_name`,`doc_type`,`loan_num`,`file_size`,`last_access`,`auto_id` 
					from `documents_info` 
					where `loan_num` = '$loanNumID' 
					order by `loan_num` ASC";

			$resultBool = $dblink->query($sql);

			if(!$resultBool) {
			//log_sql_error("null", $date, $execution_time, "ERROR", $dblink->error);
			redirect("search_Doctype.php?error=searchFail");
			}
			
			if(mysqli_num_rows($resultBool) === 0)
				redirect("search_Doctype.php?criteria=docType&error=noResult");

			echo '<table class="table table-hover">';
			echo '<tbody>';
			
			echo '<tr>
					<td>File Name</td>
					<td>Loan ID</td>
					<td>Document Type</td>
					<td>File Size</td>
					<td>Last Access</td>
					<td>View File</td>
				</tr>';

			while($data=$resultBool->fetch_array(MYSQLI_ASSOC))
			{
				echo '<tr>
					<td>'.$data['file_name'].'</td>
					<td>'.$data['loan_num'].'</td>
					<td>'.$data['doc_type'].'</td>
					<td>'.human_filesize($data['file_size']).'</td>
					<td>'.$data['last_access'].'</td>
					<td><a href="view_doc.php?id='.$data['auto_id'].'" target="_blank">Click here to view file</a></td>
				</tr>';
			}
			echo '</tbody>';
			echo '</table>';
			echo '</div>';//end panel-body
			echo '</div>';
		}
		
		//checking for date 
		elseif( (isset($_POST['to']) && isset($_POST['from']) ) || isset($_POST['from']) )
		{
			echo '<div id="page-inner">';
			echo '<h1 class="page-head-line">Results</h1>';
			echo '<div class="panel-body">';
			
			echo '<div id="page-inner">';
			echo '<a href="search_Doctype.php?criteria=date" class="btn btn-sm btn-block btn-success">Back</a>';
			echo '</div>';
			
			if(isset($_POST['dateType']) && $_POST['dateType'] == 'single')
			{
				$dateID = strtotime($_POST['from']);

				$date = date('Y-m-d H:i:s', $dateID);
				$now = date('Y-m-d H:i:s');

				//echo '<div>from '.$date.' </div>';

				if ($date > $now)
					redirect("search_Doctype.php?criteria=date&error=InvalidDate");

				$sql = "Select `file_name`,`doc_type`,`loan_num`,`file_size`,`last_access`,`auto_id` 
						from `documents_info` 
						where `file_create_date` = '$date' order by `file_create_date` ASC";

				$resultBool = $dblink->query($sql);

				if(!$resultBool) {
				//log_sql_error("null", $date, $execution_time, "ERROR", $dblink->error);
				redirect("search_Doctype.php?error=searchFail");
				}
				
				if(mysqli_num_rows($resultBool) === 0)
					redirect("search_Doctype.php?criteria=docType&error=noResult");

				echo '<table class="table table-hover">';
				echo '<tbody>';
				
				echo '<tr>
					<td>File Name</td>
					<td>Loan ID</td>
					<td>Document Type</td>
					<td>File Size</td>
					<td>Last Access</td>
					<td>View File</td>
				</tr>';

				while($data=$resultBool->fetch_array(MYSQLI_ASSOC))
				{
					echo '<tr>
					<td>'.$data['file_name'].'</td>
					<td>'.$data['loan_num'].'</td>
					<td>'.$data['doc_type'].'</td>
					<td>'.human_filesize($data['file_size']).'</td>
					<td>'.$data['last_access'].'</td>
					<td><a href="view_doc.php?id='.$data['auto_id'].'" target="_blank">Click here to view file</a></td>
				</tr>';
				}
				echo '</tbody>';
				echo '</table>';
				echo '</div>';//end panel-body
				echo '</div>';
			}
			elseif(isset($_POST['dateType']) && $_POST['dateType'] == 'range')
			{
				//checking if either date in range isn't set
				if( (!isset($_POST['to']) || !isset($_POST['from']) ) )
					redirect("search_Doctype.php?criteria=date&error=someDateNull");
					
				//converts to usable timestamps
				$toID = strtotime($_POST['to']);
				$fromID = strtotime($_POST['from']);
				
				if($fromID == 0)
					redirect("search_Doctype.php?criteria=date&error=someDateNull");

				$to = date('Y-m-d H:i:s', $toID);
				$from = date('Y-m-d H:i:s', $fromID);

				echo '<div>from '.$from.' to '.$to.'</div>';

				if ($from > $to)
					redirect("search_Doctype.php?criteria=date&error=InvalidRange");
				elseif($from == $to)
					redirect("search_Doctype.php?criteria=date&error=DuplicateDates");

				$sql = "Select `file_name`,`doc_type`,`loan_num`,`file_size`,`last_access`,`auto_id` 
						from `documents_info` 
						where `file_create_date` between '$from' and '$to' order by `file_create_date` ASC";

				$resultBool = $dblink->query($sql);

				if(!$resultBool) {
				//log_sql_error("null", $date, $execution_time, "ERROR", $dblink->error);
				redirect("search_Doctype.php?error=searchFail");
				}

				echo '<table class="table table-hover">';
				echo '<tbody>';
				
				echo '<tr>
					<td>File Name</td>
					<td>Loan ID</td>
					<td>Document Type</td>
					<td>File Size</td>
					<td>Last Access</td>
					<td>View File</td>
				</tr>';

				while($data=$resultBool->fetch_array(MYSQLI_ASSOC))
				{
					echo '<tr>
					<td>'.$data['file_name'].'</td>
					<td>'.$data['loan_num'].'</td>
					<td>'.$data['doc_type'].'</td>
					<td>'.human_filesize($data['file_size']).'</td>
					<td>'.$data['last_access'].'</td>
					<td><a href="view_doc.php?id='.$data['auto_id'].'" target="_blank">Click here to view file</a></td>
					
				</tr>';
				}
				echo '</tbody>';
				echo '</table>';
				echo '</div>';//end panel-body
				echo '</div>';
			}


		}
	}

	?>
</body>
</html>