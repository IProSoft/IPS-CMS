<?php
/**
 * Front controller for default Minify implementation
 * 
 * DO NOT EDIT! Configure this utility via config.php and groupsConfig.php
 * 
 * @package Minify
 */
header("Content-Encoding: none");
session_start();
$_SERVER['DOCUMENT_ROOT'] = dirname(dirname(dirname(__FILE__)));
include( $_SERVER['DOCUMENT_ROOT'] . '/config.php');
include( $_SERVER['DOCUMENT_ROOT'] . '/classes/class.App.php');
@ini_set('zlib.output_compression', 'Off');
@ini_set('output_buffering', 'Off');
@ini_set('output_handler', '');
/**
* Ścieżki dla plików CSS i JS
*/
$include_path_minify = App::minifyPaths();

ini_set('include_path', get_include_path() 
	. implode( PATH_SEPARATOR, $include_path_minify )
);

if( !isset($_GET['f']) || empty($_GET['f']) )
{
	error_log( implode( ' | ', $include_files ) . "\n", 3, ABS_PATH . '/logs/minify.log');
}
/**
* Pobieranie listy plików ze zmiennej $_GET['f'] i przekształcanie
* na tablicę z pełną ścieżką pliku
*/
$to_minify = ( strpos($_GET['f'], ',') == false ? array($_GET['f']) : explode(",", $_GET['f']) );

$include_files = App::findToMinify( $to_minify );	



if( defined('IPS_MINIFY_STATIC') && IPS_MINIFY_STATIC )
{
	ob_start();
	header('Content-Type: text/'.( strpos( $include_files[0], '.css' ) !== false ? 'css' : 'javascript' ).'; charset=UTF-8');
	header('Cache-Control: max-age=86400');
	header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 60 * 60 * 24) . ' GMT');	

	foreach( $include_files as $plik )
	{		
		if( file_exists( ABS_PATH . '/' . $plik ) )
		{
			include( ABS_PATH . '/' . $plik );
		}
	}
	ob_flush();
}

$_GET['f'] = implode(",", $include_files);
define('MINIFY_MIN_DIR', dirname(__FILE__));

// load config
require MINIFY_MIN_DIR . '/config.php';
if( !IPS_DEBUG )
{
	$min_serveOptions['quiet'] = true;
}
require "$min_libPath/Minify/Loader.php";
Minify_Loader::register();

Minify::$uploaderHoursBehind = $min_uploaderHoursBehind;
Minify::setCache(
    isset($min_cachePath) ? $min_cachePath : ''
    ,$min_cacheFileLocking
);

if ($min_documentRoot) {
    $_SERVER['DOCUMENT_ROOT'] = $min_documentRoot;
    Minify::$isDocRootSet = true;
}

$min_serveOptions['minifierOptions']['text/css']['symlinks'] = $min_symlinks;
// auto-add targets to allowDirs
foreach ($min_symlinks as $uri => $target) {
    $min_serveOptions['minApp']['allowDirs'][] = $target;
}

if ($min_allowDebugFlag) {
    $min_serveOptions['debug'] = Minify_DebugDetector::shouldDebugRequest($_COOKIE, $_GET, $_SERVER['REQUEST_URI']);
}

if ($min_errorLogger) {
    if (true === $min_errorLogger) {
        $min_errorLogger = FirePHP::getInstance(true);
    }
    Minify_Logger::setLogger($min_errorLogger);
}

// check for URI versioning
if (preg_match('/&\\d/', $_SERVER['QUERY_STRING'])) {
    $min_serveOptions['maxAge'] = 31536000;
}
if (isset($_GET['g'])) {
    // well need groups config
    $min_serveOptions['minApp']['groups'] = (require MINIFY_MIN_DIR . '/groupsConfig.php');
}
if (isset($_GET['f']) || isset($_GET['g'])) {
    // serve!   

    if (! isset($min_serveController)) {
        $min_serveController = new Minify_Controller_MinApp();
    }
    $minify = Minify::serve($min_serveController, $min_serveOptions);
    
	if( !IPS_DEBUG )
	{
		foreach ($minify['headers'] as $name => $val) {
			header($name . ': ' . $val);
		}var_dump($minify);exit;
		echo $minify['content'];
	}	
} elseif ($min_enableBuilder) {
    header('Location: builder/');
    exit();
} else {
    header("Location: /");
    exit();
}