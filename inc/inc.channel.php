<?php
/**
 * IPS-CMS
 *
 * Copyright (c) IPROSOFT
 * Licensed under the Commercial License
 * http://www.iprosoft.pro/ips-license/	
 *
 * Project home: http://iprosoft.pro
 *
 * Version:  2.0
 */ 
	$cache_expire = 60*60*24*365;
	header("Pragma: public");
	header("Cache-Control: maxage=" . $cache_expire );
	header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cache_expire ).' GMT');
	
	echo '<script src="//connect.facebook.net/' . $_GET['lang'] . '/all.js"></script>'
?>