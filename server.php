<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,300,400,600,700" rel="stylesheet" type="text/css" />
<title>Test serwera</title>
</head>

<body>
<style>
body{
	background-color: #00000;
    color: #585858;
    font-family: "Open Sans",sans-serif;
    font-size: 100%;
    font-weight: 400;
    line-height: 1.7;
}
.check{
	margin: 30px auto;
	width: 590px;
}
b{
	color: green
}
i{
	color: red;
	font-style: normal;
}

.head {
    background: none repeat scroll 0 0 #222222;
    color: #ffffff;
    font-size: 22px;
    padding: 10px;
    text-align: center;
    text-shadow: 0 1px 1px #4d4d4d;
}
.row {
    border-bottom: 1px solid #E0E0E0;
	border-left: 1px solid #E0E0E0;
	border-right: 1px solid #E0E0E0;
	float: left;
	font-weight: bold;
	width: 588px;
	background-color: #fff;
}
.row.alt{
	background-color: #F1F1F1;
}

.row-left {
    color: #222222;
	float: left;
	font-size: 11px;
	line-height: 34px;
	padding-right: 14px;
	text-align: right;
	width: 250px;
}
.row-right {
    border-left: 1px solid #E0E0E0;
	float: left;
	font-size: 11px;
	height: 34px;
	line-height: 34px;
	text-align: center;
	width: 300px;
}

html {
    background: none repeat scroll 0 0 #ff7e71;
    height: 100%;
}

.row-right > img {
    max-width: 22px;
    vertical-align: middle;
}
</style>


<div class="check">
	
	<img alt="" src="http://cdn.iprosoft.pro/codecanyon_profile_only_text_590x242.png" class="img-responsive" />
	
	<div class="head">Sprawdzanie wymaganych funkcji serwera</div>




<?php
function apacheModule($nazwa)
{
    $instaled = false;
    if ( function_exists('apache_get_modules') )
    {
        
		$instaled = in_array($nazwa, apache_get_modules());
    }
    else
    {
        ob_start();
        phpinfo( INFO_MODULES );
        $contents = ob_get_contents();
        ob_end_clean();
		
        $instaled = strpos($contents, $nazwa );
    }
    return $instaled;
}
function gdVersion($user_ver = 0)
{
    if (! extension_loaded('gd')) { return; }
    static $gd_ver = 0;// Just accept the specified setting if it's 1.
    if ($user_ver == 1) { $gd_ver = 1; return 1; }
    // Use the static variable if function was called previously.
    if ($user_ver !=2 && $gd_ver > 0 ) { return $gd_ver; }
    // Use the gd_info() function if possible.
    if (function_exists('gd_info')) {
        $ver_info = gd_info();    preg_match('/\d/', $ver_info['GD Version'], $match);    $gd_ver = $match[0];    return $match[0];}
    // If phpinfo() is disabled use a specified / fail-safe choice...
    if (preg_match('/phpinfo/', ini_get('disable_functions'))) {
        if ($user_ver == 2) {
            $gd_ver = 2;        return 2;    } else {
            $gd_ver = 1;        return 1;    }
    }
    // ...otherwise use phpinfo().
    ob_start();
	phpinfo(8);
	$info = ob_get_contents();
	ob_end_clean();
	$info = stristr($info, 'gd version');
	preg_match('/\d/', $info, $match);
	$gd_ver = $match[0];
	return $match[0];
}
	function isInstalled($loaded_extensions, $function) {
		if  (in_array  ($function, $loaded_extensions)) {
			return true;
		}
		else{
			return false;
		}
	}
	$array = get_loaded_extensions();
	$msg = array();
	
	
	if (!apacheModule("mod_rewrite"))
	{
		//$msg[] = array('name' => 'mod_rewrite', 'value' => false);
	}
	else
	{
		//$msg[] = array('name' => 'mod_rewrite', 'value' => true);
	}
	
	if (!isInstalled($array, 'exif'))
	{
		$msg[] = array('name' => 'Exif', 'value' => false);
	}
	else
	{
		$msg[] = array('name' => 'Exif ', 'value' => true);
	}
	
	if (!isInstalled($array, 'PDO'))
	{
		$msg[] = array('name' => 'PDO', 'value' => false);
	}
	else
	{
		$msg[] = array('name' => 'PDO', 'value' => true);
	}
	
	if (!isInstalled($array, 'curl'))
	{
		$msg[] = array('name' => 'CURL', 'value' => false);
	}
	else
	{
		$msg[] = array('name' => 'CURL', 'value' => true);
	}

	if (!isInstalled($array, 'gd') || gdVersion() < 2)
	{
		$msg[] = array('name' => 'GD', 'value' => false);
	}
	else
	{
		$msg[] = array('name' => 'GD', 'value' => true);
	}
	
	if (!isInstalled($array, 'mcrypt'))
	{
		$msg[] = array('name' => 'Mcrypt', 'value' => false);
	}
	else
	{
		$msg[] = array('name' => 'Mcrypt', 'value' => true);
	}
	
	if (!isInstalled($array, 'dom'))
	{
		$msg[] = array('name' => 'DOMDocument', 'value' => false);
	}
	else
	{
		$msg[] = array('name' => 'DOMDocument', 'value' => true);
	}
	
	if ( !function_exists('mb_strlen') )
	{
		$msg[] = array('name' => 'Multibyte String', 'value' => false);
	}
	else
	{
		$msg[] = array('name' => 'Multibyte String', 'value' => true);
	}
	
	
	if (version_compare(PHP_VERSION, '5.4.0', '<'))
	{
		$msg[] = array('name' => 'Wersja PHP co najmniej 5.4.0', 'value' => false);
	}
	else
	{
		$msg[] = array('name' => 'Wersja PHP co najmniej 5.4.0', 'value' => true);
	}

	if ( !ini_get('allow_url_fopen') )
	{
		$msg[] = array('name' => 'allow_url_fopen', 'value' => false);
	}
	else
	{
		$msg[] = array('name' => 'allow_url_fopen', 'value' => true);
	}
	foreach( $msg as $key => $value )
	{
		if( $value['value'] == false )
		{
			$msg[] = array('name' => 'Wynik', 'value' => '<i>Niestety serwer nie spełnia wymagań</i>');
		}
	}
	$last_key = key( array_slice( $msg, -1, 1, TRUE ) );
	if( $msg[$last_key]['name'] != 'Wynik' )
	{
		$msg[] = array('name' => 'Wynik', 'value' => '<b>Serwer spełnia podstawowe* wymagania</b>');
	}

	

	foreach( $msg as $key => $value )
	{
		echo '<div class="row'.( $key%2 == 0 ? '' : ' alt').'">';
		echo '<div class="row-left">'.$value['name'].'</div>';
		echo '<div class="row-right">';
		if( $value['value'] === true )
		{
			echo '<img src="http://cdn.iprosoft.pro/icons/ok-icon.png" />';
		}
		else
		{
			if( $value['value'] === false )
			{
				echo '<img src="http://cdn.iprosoft.pro/icons/error-icon.png" />';
			}
			else
			{
				echo $value['value'];
			}
		}
		echo '</div>';
		
		echo '</div>';
		
	}

?>

<span style="font-size: 10px; color: rgb(68, 68, 68);"> *sprawdzenia funkcji nie gwarantuje poprawności działania w wypadku niestandardowych konfiguracji serwera lub środowiska</span>
</div>
</body></html>