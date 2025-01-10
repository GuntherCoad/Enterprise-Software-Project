<?php
include("functions.php");
	

$cinfo = create_session();
//sid should be $cinfo[2] with these conditions fulfilled
if(!is_null($cinfo) && $cinfo[1] == "MSG: Session Created")
{
	$sid = $cinfo[2];
	close_session($sid);
}
elseif (is_null($cinfo))
{
	//will want to log any error
	//will want to log time taken to close session
	clear_session();
	exit(1);
}
else
{
	clear_session();
}
?>