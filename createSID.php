<?php

$username="ano523";
$password="dgWYMT#J32ymrpLP";
$data="username=$username&password=$password";
$curl_handler=curl_init('https://cs4743.professorvaladez.com/api/create_session');
curl_setopt($curl_handler, CURLOPT_POST, 1);
curl_setopt($curl_handler, CURLOPT_POSTFIELDS, $data);
curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl_handler, CURLOPT_HTTPHEADER, array(
	'content-type: application/x-www-form-urlencoded', 
	'content-length: '. strlen($data)));

//logging only response time from api server
$time_start=microtime(true);
$result=curl_exec($curl_handler);
$time_end=microtime(true);
//left as is, displays in milliseconds
$execution_time=($time_end-$time_start)/60;
curl_close($curl_handler);
//always give info, even if error data
$cinfo=json_decode($result,true);
echo "<pre>";
print_r($cinfo);
echo "</pre>"
?>