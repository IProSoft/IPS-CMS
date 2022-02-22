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
session_start();
ob_start();
//error_reporting(E_ALL);
define('IPS_AJAX', true);
require_once( dirname(__FILE__) . '/config.php');
require_once( dirname(__FILE__) . '/admin-functions.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta content="pl" http-equiv="Content-Language" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="author" content="IProSoft" />

<title>Panel zarządzania portalem internetowym</title>
</head>
<body>
<style>
html, body, div, span, applet, object, iframe,h1, h2, h3, h4, h5, h6, p, blockquote, pre,a, abbr, acronym, address, big, cite, code,del, dfn, em, img, ins, kbd, q, s, samp,small, strike, strong, sub, sup, tt, var,b, u, i, center,dl, dt, dd, ol, ul, li,fieldset, form, label, legend,table, caption, tbody, tfoot, thead, tr, th, td,article, aside, canvas, details, embed,figure, figcaption, footer, header, hgroup,menu, nav, output, ruby, section, summary,time, mark, audio, video {  margin: 0;  padding: 0;  border: 0;  font-size: 100%;  font: inherit;  vertical-align: baseline;}article, aside, details, figcaption, figure,footer, header, hgroup, menu, nav, section {  display: block;}body {  line-height: 1;}ol, ul {  list-style: none;}blockquote, q {  quotes: none;}blockquote:before, blockquote:after,q:before, q:after {  content: '';  content: none;}table {  border-collapse: collapse;  border-spacing: 0;}.about {  margin: 70px auto 40px;  padding: 8px;  width: 360px;  font: 10px/18px 'Lucida Grande', Arial, sans-serif;  color: #666;  text-align: center;  text-shadow: 0 1px rgba(255, 255, 255, 0.25);  background: #eee;  background: rgba(250, 250, 250, 0.8);  border-radius: 4px;  background-image: -webkit-linear-gradient(top, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.1));  background-image: -moz-linear-gradient(top, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.1));  background-image: -o-linear-gradient(top, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.1));  background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.1));  -webkit-box-shadow: inset 0 1px rgba(255, 255, 255, 0.3), inset 0 0 0 1px rgba(255, 255, 255, 0.1), 0 0 6px rgba(0, 0, 0, 0.2);  box-shadow: inset 0 1px rgba(255, 255, 255, 0.3), inset 0 0 0 1px rgba(255, 255, 255, 0.1), 0 0 6px rgba(0, 0, 0, 0.2);}.about a {  color: #333;  text-decoration: none;  border-radius: 2px;  -webkit-transition: background 0.1s;  -moz-transition: background 0.1s;  -o-transition: background 0.1s;  transition: background 0.1s;}.about a:hover {  text-decoration: none;  background: #fafafa;  background: rgba(255, 255, 255, 0.7);}.about-links {  height: 30px;}.about-links > a {  float: left;  width: 50%;  line-height: 30px;  font-size: 12px;}.about-author {  margin-top: 5px;}.about-author > a {  padding: 1px 3px;  margin: 0 -1px;}/* * Copyright (c) 2012-2013 Thibaut Courouble * http://www.cssflow.com * * Licensed under the MIT License: * http://www.opensource.org/licenses/mit-license.php */body, .login-submit, .login-submit:before, .login-submit:after {  background: #373737 url("images/login/bg.png") 0 0 repeat;}body {  font: 14px/20px 'Helvetica Neue', Helvetica, Arial, sans-serif;  color: #404040;}a {  color: #ff7e71;  text-decoration: none;}a:hover {  text-decoration: underline;}.login {  position: relative;  margin: 80px auto;  width: 400px;  padding-right: 32px;  font-weight: 300;  color: #a8a7a8;  text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.8);}.login p {  margin: 0 0 10px;}input, button, label {  font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;  font-size: 15px;  font-weight: 300;  -webkit-box-sizing: border-box;  -moz-box-sizing: border-box;  box-sizing: border-box;}input[type=text], input[type=password] {  padding: 0 10px;  width: 300px;  height: 40px;  color: #bbb;  text-shadow: 1px 1px 1px black;  background: rgba(0, 0, 0, 0.16);  border: 0;  border-radius: 5px;  -webkit-box-shadow: inset 0 1px 4px rgba(0, 0, 0, 0.3), 0 1px rgba(255, 255, 255, 0.06);  box-shadow: inset 0 1px 4px rgba(0, 0, 0, 0.3), 0 1px rgba(255, 255, 255, 0.06);}input[type=text]:focus, input[type=password]:focus {  color: white;  background: rgba(0, 0, 0, 0.1);  outline: 0;}label {  float: left;  width: 100px;  line-height: 40px;  padding-right: 10px;  font-weight: 100;  text-align: right;  letter-spacing: 1px;}.forgot-password {  padding-left: 100px;  font-size: 13px;  font-weight: 100;  letter-spacing: 1px;}.login-submit {  position: absolute;  top: 12px;  right: 0;  width: 48px;  height: 48px;  padding: 8px;  border-radius: 32px;  -webkit-box-shadow: 0 0 4px rgba(0, 0, 0, 0.35);  box-shadow: 0 0 4px rgba(0, 0, 0, 0.35);}.login-submit:before, .login-submit:after {  content: '';  z-index: 1;  position: absolute;}.login-submit:before {  top: 28px;  left: -4px;  width: 4px;  height: 10px;  -webkit-box-shadow: inset 0 1px rgba(255, 255, 255, 0.06);  box-shadow: inset 0 1px rgba(255, 255, 255, 0.06);}.login-submit:after {  top: -4px;  bottom: -4px;  right: -4px;  width: 36px;}.login-button {  position: relative;  z-index: 2;  width: 48px;  height: 48px;  padding: 0 0 48px;  /* Fix wrong positioning in Firefox 9 & older (bug 450418) */  text-indent: 120%;  white-space: nowrap;  overflow: hidden;  background: none;  border: 0;  border-radius: 24px;  cursor: pointer;  -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.2), 0 1px rgba(255, 255, 255, 0.1);  box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.2), 0 1px rgba(255, 255, 255, 0.1);  /* Must use another pseudo element for the gradient background because Webkit */  /* clips the background incorrectly inside elements with a border-radius.     */}.login-button:before {  content: '';  position: absolute;  top: 5px;  bottom: 5px;  left: 5px;  right: 5px;  background: #ff7e71;  border-radius: 24px;  background-image: -webkit-linear-gradient(top, #ff7e71, #ff4f3c);  background-image: -moz-linear-gradient(top, #ff7e71, #ff4f3c);  background-image: -o-linear-gradient(top, #ff7e71, #ff4f3c);  background-image: linear-gradient(to bottom, #ff7e71, #ff4f3c);  -webkit-box-shadow: inset 0 0 0 1px #ff7e71, 0 0 0 5px rgba(0, 0, 0, 0.16);  box-shadow: inset 0 0 0 1px #ff7e71, 0 0 0 5px rgba(0, 0, 0, 0.16);}.login-button:active:before {  background: #ff4f3c;  background-image: -webkit-linear-gradient(top, #ff4f3c, #ff7e71);  background-image: -moz-linear-gradient(top, #ff4f3c, #ff7e71);  background-image: -o-linear-gradient(top, #ff4f3c, #ff7e71);  background-image: linear-gradient(to bottom, #ff4f3c, #ff7e71);}.login-button:after {  content: '';  position: absolute;  top: 15px;  left: 12px;  width: 25px;  height: 19px;  background: url("images/login/arrow.png") 0 0 no-repeat;}::-moz-focus-inner {  border: 0;  padding: 0;}.lt-ie9 input[type=text], .lt-ie9 input[type=password] {  line-height: 40px;  background: #282828;}.lt-ie9 .login-submit {  position: absolute;  top: 12px;  right: -28px;  padding: 4px;}.lt-ie9 .login-submit:before, .lt-ie9 .login-submit:after {  display: none;}.lt-ie9 .login-button {  line-height: 48px;}.lt-ie9 .about {  background: #313131;}
.ips-message {background: #ff7e71 none repeat scroll 0 0;padding: 1%;text-align: center;color: #fff;}
</style>

<?php
if( isset( $_POST['action_login'] ) )
{
	if( !empty( $_POST['login'] ) && !empty( $_POST['password'] ) )
	{
		$login = new Users();
		$msg = $login->userLogin( $_POST['login'], $_POST['password'], ( isset($_POST['user_remember']) ? true : false) );
		
		if( is_int( $msg ) )
		{
			return ips_redirect( admin_url( '/' ) );
		}
	}
	else
	{
		$msg = 'user_error_empty_fields';
	}
	echo admin_msg( [
		'alert' => __( $msg )
	]);
}
?>
  <form method="post" action="<?php echo 'http://'.$_SERVER['HTTP_HOST'];?>/admin/login.php" class="login">
    <p>
      <label for="login">Email:</label>
      <input type="text" name="login" id="login" value="name@example.com">
    </p>

    <p>
      <label for="password">Hasło:</label>
      <input type="password" name="password" id="password" value="4815162342">
    </p>

    <p class="login-submit">
      <button type="submit" class="login-button">Login</button>
    </p>

    <p class="forgot-password"><a href="<?php echo 'http://'.$_SERVER['HTTP_HOST'].'/';?>">Wróć</a></p>
	<input type="hidden" name="action_login" value="true" />
  </form>

  <section class="about">
    <p class="about-author">Skrypt autorstwa firmy <a target="_blank" style="color:#363636" href="http://www.iprosoft.pl">IProSoft</a>. Wszelkie prawa zastrzeżone.</p>
  </section>

</body>
</head>
</html>