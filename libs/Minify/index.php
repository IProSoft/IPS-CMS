<?php
/**
 * Sets up MinApp controller and serves files
 * 
 * DO NOT EDIT! Configure this utility via config.php and groupsConfig.php
 * 
 * @package Minify
 */

header("Content-Encoding: none");
session_start();
$_SERVER['DOCUMENT_ROOT'] = dirname( dirname( dirname( __FILE__ ) ) );
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

$_GET['f'] = implode( ",", $include_files );

require __DIR__ . '/bootstrap.php';

// set config path defaults
$min_configPaths = array(
    'base'   => __DIR__ . '/config.php',
    'test'   => __DIR__ . '/config-test.php',
    'groups' => __DIR__ . '/groupsConfig.php',
);

// check for custom config paths
if (!empty($min_customConfigPaths) && is_array($min_customConfigPaths)) {
    $min_configPaths = array_merge($min_configPaths, $min_customConfigPaths);
}

// load config
require $min_configPaths['base'];

if (isset($_GET['test'])) {
    include $min_configPaths['test'];
}

// setup factories
$defaultFactories = array(
    'minify' => function (Minify_CacheInterface $cache) {
        return new Minify($cache);
    },
    'controller' => function (Minify_Env $env, Minify_Source_Factory $sourceFactory) {
        return new Minify_Controller_MinApp($env, $sourceFactory);
    },
);
if (!isset($min_factories)) {
    $min_factories = array();
}
$min_factories = array_merge($defaultFactories, $min_factories);

// use an environment object to encapsulate all input
$server = $_SERVER;
if ($min_documentRoot) {
    $server['DOCUMENT_ROOT'] = $min_documentRoot;
}
$env = new Minify_Env(array(
    'server' => $server,
));

// TODO probably should do this elsewhere...
$min_serveOptions['minifierOptions']['text/css']['docRoot'] = $env->getDocRoot();
$min_serveOptions['minifierOptions']['text/css']['symlinks'] = $min_symlinks;
// auto-add targets to allowDirs
foreach ($min_symlinks as $uri => $target) {
    $min_serveOptions['minApp']['allowDirs'][] = $target;
}

if ($min_allowDebugFlag) {
    // TODO get rid of static stuff
    $min_serveOptions['debug'] = Minify_DebugDetector::shouldDebugRequest($env);
}

if (!empty($min_concatOnly)) {
    $min_serveOptions['concatOnly'] = true;
}

if ($min_errorLogger) {
    if (true === $min_errorLogger) {
        $min_errorLogger = FirePHP::getInstance(true);
    }
    // TODO get rid of global state
    Minify_Logger::setLogger($min_errorLogger);
}

// check for URI versioning
if (null !== $env->get('v') || preg_match('/&\\d/', $env->server('QUERY_STRING'))) {
    $min_serveOptions['maxAge'] = 31536000;
}

// need groups config?
if (null !== $env->get('g')) {
    // we need groups config
    $min_serveOptions['minApp']['groups'] = (require $min_configPaths['groups']);
}

// cache defaults
if (!isset($min_cachePath)) {
    $min_cachePath = '';
}
if (!isset($min_cacheFileLocking)) {
    $min_cacheFileLocking = true;
}
if (is_string($min_cachePath)) {
    $cache = new Minify_Cache_File($min_cachePath, $min_cacheFileLocking);
} else {
    // assume it meets interface.
    $cache = $min_cachePath;
}
/* @var Minify_CacheInterface $cache */

$minify = call_user_func($min_factories['minify'], $cache);
/* @var Minify $minify */

if (!$env->get('f') && $env->get('g') === null) {
    // no spec given
    $msg = '<p>No "f" or "g" parameters were detected.</p>';
    $url = 'https://github.com/mrclay/minify/blob/master/docs/CommonProblems.wiki.md#long-url-parameters-are-ignored';
    $defaults = $minify->getDefaultOptions();
    $minify->errorExit($defaults['badRequestHeader'], $url, $msg);
}

$sourceFactoryOptions = array(
	 'checkAllowDirs' => false
);

// translate legacy setting to option for source factory
if (isset($min_serveOptions['minApp']['noMinPattern'])) {
    $sourceFactoryOptions['noMinPattern'] = $min_serveOptions['minApp']['noMinPattern'];
}
$sourceFactory = new Minify_Source_Factory($env, $sourceFactoryOptions, $cache);

$controller = call_user_func($min_factories['controller'], $env, $sourceFactory);
/* @var Minify_ControllerInterface $controller */

$minify->serve($controller, $min_serveOptions);
