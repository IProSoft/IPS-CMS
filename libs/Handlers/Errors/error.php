<?php
if( isset( $_GET['error'] ) )
{
	$body = '';
    
	date_default_timezone_set('Europe/Warsaw');
	$ip = getenv ("REMOTE_ADDR");				// IP Address
	$server_name = getenv ("SERVER_NAME");		// Server Name
	$request_uri = getenv ("REQUEST_URI");		// Requested URI
	$http_ref = getenv ("HTTP_REFERER");		// HTTP Referer
	$http_agent = getenv ("HTTP_USER_AGENT");	// User Agent
	$error_date = date("D M j Y g:i:s a T");
	

	$file = "---------------------------------------------------------------------- \n";
	$file .= 'Wystąpił błąd ' . $_GET['error'] . "\n";
	$file .= "Szczegóły\n";
	$file .= "IP: " . $ip . "\n";
	$file .= "Data: " . $error_date . "\n";
	$file .= "Strona:  http://" . $server_name . $request_uri . "\n";
	$file .= "Data: " . $error_date . "\n";
	$file .= "HTTP Referer: " . $http_ref . "\n";
	$file .= "User Agent: " . $http_agent . "\n";
	$file .= ( isset( $_SERVER['REQUEST_TIME_FLOAT'] ) ? 'Request Time: ' . ( microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'] ) : '' ) . "\n". "\n";
	
	foreach( $_SERVER as $key => $v )
	{
		$file .= '$_SERVER["' . $key . '"] : ' . $v . "\n";
	}
	
	$file .= "\n";
	
	foreach( $_GET as $key => $v )
	{
		$file .= '$_GET["' . $key . '"] : ' . $v . "\n";
	}
	
	$file .= "\n";
	
	foreach( $_POST as $key => $v )
	{
		$file .= '$_GET["' . $key . '"] : ' . $v . "\n";
	}
	
	$file .= "\n";
	
	$filename = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/logs/error-history.log';

	if ( file_exists($filename) )
	{
		@file_put_contents($filename, $file, FILE_APPEND);
	}
	else
	{
		if( $fp = @fopen($filename, 'w') )
		{
			fclose($fp);
			@file_put_contents($filename, $file, FILE_APPEND);
		}
	}
	switch( $_GET['error'] )
	{
		case "403":
			$error = '403';
			header( $_SERVER['SERVER_PROTOCOL'] . ' Forbidden', true, 403 );
		break;
		case "404":
			$error = '404';
			header( $_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404 );
		break;
		
		case "500":
			$error = '500';
			header( $_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500 );
		break;
		
		default:
			$error = '404';
			header( $_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404 );
		break;
	}

	echo '
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
	<head>
		<meta content="text/html;charset=utf-8" http-equiv="content-type">
		<meta content="width=device-width, initial-scale=1.0, user-scalable=no" name="viewport">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Error 404</title>
		<script>
			error_number = ' . $error . ';
		</script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
		<script type="text/javascript" src="http://' . $_SERVER['HTTP_HOST'] . '/js/error_page.js"></script>
		<link href="http://' . $_SERVER['HTTP_HOST'] . '/css/error-page.css" rel="stylesheet">
	</head>
	<body>
		<div id="content-cnt">
			<div id="content-letters">
				<ul></ul>
			</div>
		</div>
	</body>
	</html>';
}

