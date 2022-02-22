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
require_once LIBS_PATH . '/Bench/Timer.php';

class IPS_Benchmark extends Benchmark_Timer
{
	
	private static $instance = false;
	private static $time_start = false;
	private $marker = array();
	
	public function __construct()
	{
		
		define( 'LOAD_START_TIME', microtime( true ) );
		define( 'LOAD_START_USAGE', memory_get_usage( true ) );
		
		error_reporting( E_ALL );
		ini_set( 'display_errors', 1 );
		$this->start();
		//$this->sessionUser();
		
		register_shutdown_function( 'get_usage' );
	}
	
	public function sessionUser()
	{
		if( !isset( $_COOKIE['ssid_global'] ) )
		{
			$_COOKIE = array_merge( $_COOKIE, array (
				'ips-popup' => 'true',
				'ips_cookie_policy' => 'checked',
				'ssid_global' => 'F9/xJJOWSmbkQxUGkuN2IXsDvlshE7IM6SZcvVz97zY=',
			));
			if ( session_status() === PHP_SESSION_NONE )
			{
				session_start();
			}
			$_SESSION = array_merge( $_SESSION, array (
				'user_name' => 'Admin',
				'user_id' => 1,
				'user_admin' => 'cbab36227635f75bf7c3358f355e29b2',
				'user_moderator' => 'a5fa35694bbf4f87d2fcfd9e51c99a3f',
				'msg' => '',
				'system_cache' => array(
					'config' => false,
					'config_expiry' => 0,
					'css_js' => false,
					'css_js_expiry' => 0,
				),

				'connect_nk' => 0,
				'connect_facebook' => 0,
				'ips_mobile' => false,
				'current_admin_login' => true
			) );
		}
	}
	
	public static function getCI()
	{
		if ( !self::$instance )
		{
			self::$instance = new IPS_Benchmark();
		}
		return self::$instance;
	}
	
	function mark( $name )
	{
		$this->setMarker( $name );
	}
	
	function elapsed_time( $point1 = '', $point2 = '' )
	{
		if ( !isset( $this->markers[$point1] ) )
		{
			return '';
		}
		
		if ( !isset( $this->markers[$point2] ) )
		{
			$this->setMarker( $point2 );
		}
		return $timer->timeElapsed( $point1, $point2 );
	}
	function end_benchmark()
	{
		$this->stop();
		//$this->display( true );
		
		return array(
			'php_time' => microtime( true ) - LOAD_START_TIME,
			'php_memory' => substr( number_format( memory_get_usage( true ) - LOAD_START_USAGE ), 0, 5 ),
			'php_memory_limit' => ini_get( 'memory_limit' ),
			'php_gzip' => ( function_exists( 'ob_gzhandler' ) && ini_get( 'zlib.output_compression' ) ) ? 'Enabled' : 'Disabled' 
		);
		
	}
	
	public static function initTests()
	{
		return Templates::getInc()->getTpl( '/__admin/unit_tests.html' );
	}
}

function get_usage()
{
	if( defined( 'IPS_AJAX' ) )
	{
		return;
	}
	
	if ( !isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) )
	{
		if ( isset( $_GET['debug'] ) )
		{
			$content = ob_get_clean();
		}
		
		
		$mysql_data = PD::getInstance()->debug( true );
		
		foreach ( $mysql_data['queries'] as $key => $sql_data )
		{
			if ( preg_match( '/^(select|describe|pragma|show)/i', $sql_data['sql'] ) )
				$mysql_data['queries'][$key]['query_type'] = 'Select Query';
			elseif ( preg_match( '/^(delete)/i', $sql_data['sql'] ) )
				$mysql_data['queries'][$key]['query_type'] = 'Delete Query';
			elseif ( preg_match( '/^(update)/i', $sql_data['sql'] ) )
				$mysql_data['queries'][$key]['query_type'] = 'Update Query';
			elseif ( preg_match( '/^(insert)/i', $sql_data['sql'] ) )
				$mysql_data['queries'][$key]['query_type'] = 'Insert Query';
			else
				$mysql_data['queries'][$key]['query_type'] = 'Unknown Query';
			
			$mysql_data['queries'][$key]['explain'] = PD::getInstance()->explain( $sql_data['sql'] );
		}
		
		$php_data = IPS_Benchmark::getCI()->end_benchmark();
		
		$tpl_data = Templates::$templatesUsed;
		
		$tpl_data['cached_count'] = ( isset( $tpl_data['cached'] ) ? count( $tpl_data['cached'] ) : 0 );
		$tpl_data['normal_count'] = ( isset( $tpl_data['normal'] ) ? count( $tpl_data['normal'] ) : 0 );
		$tpl_data['all_count']    = $tpl_data['cached_count'] + $tpl_data['normal_count'];
		
		
		$tpl_data['all'] = array();
		
		if( isset( $tpl_data['normal'] ) )
		{
			$tpl_data['all'] = array_merge( $tpl_data['all'], $tpl_data['normal'] );
		}
		
		if( isset( $tpl_data['cached'] ) )
		{
			$tpl_data['all'] = array_merge( $tpl_data['all'], $tpl_data['cached'] );
		}
		
		$tpl_data['all_names'] = '';
		
		foreach( $tpl_data['all'] as $tpl => $tpl_name )
		{
			$tpl_data['all_names'] .= '<div class="tpl-stats"><span>'.$tpl_name.'</span><span>' . $tpl_data['calls'][$tpl] . '</style></div>';
		}
		
		
		
		
		echo Templates::getInc()->getTpl( '/__admin/debug_stats.html', array(
			'db' => $mysql_data,
			'php' => $php_data,
			'templates' => $tpl_data,
			'stats' => array(
				'php_load' => getServerLoad(),
				'php_time' => $php_data['php_time'] - $mysql_data['queries_time'],
				'all_time' => $php_data['php_time'],
				'db_time' => $mysql_data['queries_time'],
				'php_time_percents' => round( ( ( $php_data['php_time'] - $mysql_data['queries_time'] ) * 100 ) / $php_data['php_time'], 2 ),
				'db_time_percents' => round( ( $mysql_data['queries_time'] * 100 ) / $php_data['php_time'], 2 ) 
			) 
		) );
		
	}
	
}

function getServerLoad( $windows = false )
{
	$os = strtolower( PHP_OS );
	if ( strpos( $os, 'win' ) === false )
	{
		if ( file_exists( '/proc/loadavg' ) )
		{
			$load = file_get_contents( '/proc/loadavg' );
			$load = explode( ' ', $load, 1 );
			$load = $load[0];
		}
		elseif ( function_exists( 'shell_exec' ) )
		{
			$load = explode( ' ', `uptime` );
			$load = $load[count( $load ) - 1];
		}
		else
		{
			return false;
		}
		
		if ( function_exists( 'shell_exec' ) )
			$cpu_count = shell_exec( 'cat /proc/cpuinfo | grep processor | wc -l' );
		
		return array(
			'load' => $load,
			'procs' => $cpu_count 
		);
	}
	elseif ( $windows )
	{
		if ( class_exists( 'COM' ) )
		{
			$wmi       = new COM( 'WinMgmts:\\\\.' );
			$cpus      = $wmi->InstancesOf( 'Win32_Processor' );
			$load      = 0;
			$cpu_count = 0;
			if ( version_compare( '4.50.0', PHP_VERSION ) == 1 )
			{
				while ( $cpu = $cpus->Next() )
				{
					$load += $cpu->LoadPercentage;
					$cpu_count++;
				}
			}
			else
			{
				foreach ( $cpus as $cpu )
				{
					$load += $cpu->LoadPercentage;
					$cpu_count++;
				}
			}
			return array(
				'load' => $load,
				'procs' => $cpu_count 
			);
		}
		return false;
	}
	return false;
}
?>