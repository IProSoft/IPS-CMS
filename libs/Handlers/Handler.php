<?php
class Eror_Handler
{
    public static $_error_config = array(
                                'tryb_debuggowania' => false,
                                'exceptions_page' => '',
                                'log_type' => 'details', //details
                                'log_file_dir' => '', //lokalizacja zapisywania błedów
                                'log_file_suffix' => '-IPS-Log.log',
                                'server_vars' => array(
									'_GET', 
									'_POST', 
									'_SESSION', 
									'_COOKIE'
								),
                                'ignored_errors_types' => array(E_STRICT), //array( E_STRICT )
                            );
	/**
	* Przechowywanie informacji o wyjątku.
	*/
    private static $_error_info = array();
	
	/**
	* Przechowywanie informacji o zainicjowaniu statycznej moetody klasy.
	*/
    private static $_initialized = false;
    
    private static $_request_uri = null;
    
	/**
	* Wartośc dla poszczegółnych typów ostrzeżeń.
	*/
    private static $_errorText = array(
    						'1'		=> 'E_ERROR',
                            '2'		=> 'E_WARNING',
                            '4'		=> 'E_PARSE',
                            '8'		=> 'E_NOTICE',
                            '16'	=> 'E_CORE_ERROR',
                            '32'	=> 'E_CORE_WARNING',
                            '64'	=> 'E_COMPILE_ERROR',
                            '128'	=> 'E_COMPILE_WARNING',
                            '256'	=> 'E_USER_ERROR',
                            '512'	=> 'E_USER_WARNING',
                            '1024'	=> 'E_USER_NOTICE',
                            '2048'	=> 'E_STRICT',
                            '4096'	=> 'E_RECOVERABLE_ERROR',
                            '8192'	=> 'E_DEPRECATED',
                            '16384' => 'E_USER_DEPRECATED',
                          );

   	/**
	* Obsługa wyjątków
	*
	* @param 
	* 
	* @return 
	*/
   public static function exception_handler( Exception $e )
	{

        self::inital();

        $exceptionInfo = array();
        $exceptionInfo['time'] = time();
        $exceptionInfo['type'] = 'EXCEPTION';
        $exceptionInfo['name'] = get_class($e);
        $exceptionInfo['code'] = $e->getCode();
        $exceptionInfo['message'] = $e->getMessage();
        $exceptionInfo['file'] = $e->getFile();
        $exceptionInfo['line'] = $e->getLine();
        $exceptionInfo['trace'] = self::trace_format($e->getTrace());

        self::$_error_info[] = $exceptionInfo;

        if( self::$_error_config['tryb_debuggowania'] == false )
		{
            if( is_file(self::$_error_config['exceptions_page']) )
			{
                require( self::$_error_config['exceptions_page'] );
            }
        }

    }

    public static function error_handler( $err_no, $err_string, $err_file, $err_line ) {

        self::inital();
        
        if( empty(self::$_error_config['ignored_errors_types']) || !in_array($err_no, self::$_error_config['ignored_errors_types']) )
		{
            $errInfo = array();
            $errInfo['error_time'] = time();
            $errInfo['error_type'] = 'ERROR';
            
            if(!empty(self::$_errorText[$err_no]))
			{
                $errInfo['error_name'] = self::$_errorText[$err_no];
            }
			else
			{
                $errInfo['error_name'] = '_UNKNOWN_';
            }
            
            $errInfo['error_code'] = $err_no;
            $errInfo['error_message'] = $err_string;
            $errInfo['error_file'] = $err_file;
            $errInfo['error_line'] = $err_line;
            $backtrace = debug_backtrace();
            unset($backtrace[0]);
            $errInfo['error_trace'] = self::trace_format($backtrace);

            self::$_error_info[] = $errInfo;
            
        }

    	if( in_array($err_no, array( 1, 4, 16, 64, 256, 4096 )) )
		{
		    die();
	    }
	    
	    return true;
        
    }

    public static function detect_fatal_error()
	{
        $last_error = error_get_last();
        if( empty($last_error) )
		{
            return false;
        }
       
        if( !empty(self::$_error_info) )
		{
            $log_last_error = end(self::$_error_info);
            if($log_last_error['error_code'] == $last_error['error_type'] && $log_last_error['error_file'] == $last_error['error_file'] && $log_last_error['error_line'] == $last_error['error_line']){
                return false;
            }
        }
        
        self::inital();
		
        if( empty(self::$_error_config['ignored_errors_types']) || !in_array($last_error['type'], self::$_error_config['ignored_errors_types']) )
		{
            $errInfo = array();
            $errInfo['error_time'] = time();
            $errInfo['error_type'] = 'ERROR_GET_LAST';
            
            if( !empty(self::$_errorText[$last_error['type']]) )
			{
                $errInfo['error_name'] = self::$_errorText[$last_error['type']];
            }
			else
			{
                $errInfo['error_name'] = '_UNKNOWN_';
            }
            
            $errInfo['error_code'] = $last_error['type'];
            $errInfo['error_message'] = $last_error['message'];
            $errInfo['error_file'] = $last_error['file'];
            $errInfo['error_line'] = $last_error['line'];
            $errInfo['error_trace'] = array();
            self::$_error_info[] = $errInfo;
          
        }
    }

    public static function inital(){
        if( self::$_initialized == false )
		{
            self::$_error_config['log_file_dir'] = dirname( dirname( dirname(__FILE__) ) ) . '/error_log.txt';
			self::$_error_config['log_file_name'] = date( "Y-m-d", time() ). self::$_error_config['log_file_suffix'];
			register_shutdown_function( array('Eror_Handler', 'write_log_file') );
            register_shutdown_function( array('Eror_Handler', 'diaplay_log_error') );
            self::$_request_uri = self::actual_request_uri();
            self::$_initialized = true;
        }
    }
    
    protected static function actual_request_uri()
	{
    	if( isset($_SERVER['REQUEST_URI']))
		{
    		return $_SERVER['REQUEST_URI'];
    	}
        if( isset($_SERVER['PHP_SELF']) )
		{
	        if( isset($_SERVER['argv'][0]) )
			{
                return $_SERVER['PHP_SELF']. '?'. $_SERVER['argv'][0];
	        }
			elseif( isset($_SERVER['QUERY_STRING']) )
			{
		        return $_SERVER['PHP_SELF']. '?'. $_SERVER['QUERY_STRING'];
	        }
			else
			{
		        return $_SERVER['PHP_SELF'];
	        }
        }
		else
		{
	        return '_UNKNOWN_URI_';
        }
    }

    private static function trace_format($trace){
        
		$traceInfo = array();

        foreach ( $trace as $stack => $detail )
		{
            if(!empty($detail['args']))
			{
                $args_string = self::format_args($detail['args']);
            }
			else
			{
                $args_string = '';
            }

            $traceInfo[$stack]['class'] = isset($trace[$stack]['class']) ? $trace[$stack]['class'] : '';
            $traceInfo[$stack]['type'] = isset($trace[$stack]['type']) ? $trace[$stack]['type'] : '';

            $traceInfo[$stack]['function'] = isset($trace[$stack]['function']) ? $trace[$stack]['function'].'('.$args_string.')' : '';
            $traceInfo[$stack]['file']=isset($trace[$stack]['file']) ? $trace[$stack]['file'] :'' ;
            $traceInfo[$stack]['line']=isset($trace[$stack]['line']) ? $trace[$stack]['line'] :'' ;
        }
        return $traceInfo;
    }


    private static function format_args($args)
	{
        $string = '';
        $formatedArgs = array();
        foreach ( $args as $key => $value )
		{
            if( is_object($value) == true )
			{
                $formatedArgs[$key] = 'Object('.get_class($value).')';
            }
			elseif( is_numeric($value) == true )
			{
                $formatedArgs[$key] = $value;
            }
			elseif( is_string($value) == true )
			{
                $tmp = $value;
                if( !extension_loaded('mbstring') )
				{
                    if(strlen($tmp) > 300)
					{
                        $tmp = substr( $tmp, 0 , 300 ) . '...';
                    }
                }
				else
				{
                    if(mb_strlen($tmp) > 300)
					{
                        $tmp = mb_substr( $tmp, 0 , 300 ) . '...';
                    }
                }
                $formatedArgs[$key] = "'{$tmp}'";
                $tmp = null;
            }
			elseif( is_bool($value) == true )
			{
                if( $value == true )
				{
                    $formatedArgs[$key] = 'true';
                }
				else
				{
                    $formatedArgs[$key] = 'false';
                }
            }
			else
			{
                $formatedArgs[$key] = gettype($value);
            }
        }
        $string = implode(',', $formatedArgs);
        return $string;
    }


    public static function write_log_file()
	{
		if( ( (bool)self::$_error_config['log_type'] == true ) && !empty(self::$_error_info) )
		{
            $log = '';

            foreach ( self::$_error_info as $key => $errInfo )
			{
               
                $log .= date("Y-m-d H:i:s", $errInfo['error_time']). "\t".
                self::$_request_uri."\t".
                $errInfo['error_type']. "\t".
                $errInfo['error_name']. "\t".
                'Code '. $errInfo['error_code']. "\t".
                $errInfo['error_message']. "\t".
                $errInfo['error_file']. "\t".
                'Line '. $errInfo['error_line']. "\n";

                if( self::$_error_config['log_type'] == 'details' && !empty($errInfo['error_trace']) )
				{
                    $prefix = "TRACE\t#";
                    foreach ( $errInfo['error_trace'] as $stack => $trace )
					{
                        $log .= $prefix . $stack. "\t". $trace['file']. "\t". $trace['line']. "\t". $trace['class']. $trace['type']. $trace['function']. "\n";
                    }
                }

            }

            if( empty(self::$_error_config['log_file_dir']) || is_dir(self::$_error_config['log_file_dir']) == false )
			{
                error_log($log);
            }
			else
			{
                error_log( $log, 3, self::$_error_config['log_file_dir']. DIRECTORY_SEPARATOR . self::$_error_config['log_file_neme'] );
            }
        }
    }

    public static function diaplay_log_error()
	{
        if( self::$_error_config['tryb_debuggowania'] != false && !empty(self::$_error_info) )
		{
            $htmlText = '';
            foreach( self::$_error_info as $key => $errInfo )
			{

                $htmlText .= '<div class="error-block">
    							<div class="error-block-title">['.$errInfo['error_name'].'][Kod '.$errInfo['error_code'].'] '.$errInfo['error_message'].'</div>
    							<div class="error-block-subtitle">Linia '.$errInfo['error_line'].' w <a href="'.$errInfo['error_file'].'">'.$errInfo['error_file'].'</a></div>
    							<div class="error-block-content">
							';

                if( empty($errInfo['error_trace']) )
				{
                    $htmlText .= 'Brak informacji o wywołaniu.';
                }
				else
				{
                    $htmlText .= '<table width="100%" border="1" cellpadding="1" cellspacing="1" rules="rows">
									<tr>
										<th scope="col">#</th>
										<th scope="col">File</th>
										<th scope="col">Line</th>
										<th scope="col">Class::Method(Args)</th>
									</tr>';
                    foreach ( $errInfo['error_trace'] as $stack => $trace )
					{
                        $htmlText .= '<tr>
										<td>'.$stack.'</td>
										<td><a href="'.$trace['file'].'">'.$trace['file'].'</a></td>
										<td>'.$trace['line'].'</td>
										<td>'.$trace['class']. $trace['type']. htmlspecialchars($trace['function']) .'</td>
									</tr>';
                    }
                    $htmlText .= '</table>';
                }

                $htmlText .= '</div></div>';
            }

            echo '
			<style type="text/css">
			.error-block{background-color:#FFF;border-collapse:collapse;font-size:12pt;text-align:left;vertical-align:middle;width:900px;word-break:break-all;margin:0 auto;padding:3px}.error-block-title{background:linear-gradient(top,#ffffff0%,#F0F0F0100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#ffffff\',endColorstr=\'#F0F0F0\',GradientType=0);color:#404040;font-family:Arial;font-size:16px;font-weight:400;text-shadow:0 1px 0 #FFF;padding:3px}.error-block-subtitle{font-weight:700;color:red;padding:3px}.error-block-content{font-size:11pt;color:#000;background-color:#FFF;padding:3px}.error-block-content table{font-size:14px;word-break:break-all;background-color:#D4D0C8;border-color:#000}.error-block a:link,.error-block table a:link{color:#00F;text-decoration:none}.error-block a:visited,.error-block a:active,.error-block table a:visited,.error-block table a:active{text-decoration:none;color:#00F}.error-block a:hover,.error-block table a:hover{text-decoration:underline;color:#00F}pre {white-space: pre-wrap;white-space: -moz-pre-wrap; white-space: -pre-wrap;white-space: -o-pre-wrap;word-wrap: break-word;       /* Internet Explorer 5.5+ */}
			</style>
			'.$htmlText;

        self::show_variables();
        }
    }
    
    public static function show_variables()
	{
        $variables_link = '';
        $variables_content = '';
        foreach( self::$_error_config['server_vars'] as $key )
		{
        	
            $variables_link .= '<a href="#variables'.$key.'">$'.$key.'</a>&nbsp;';
            $variables_content .= '<div class="error-block-backtrace-title"><a name="variables'.$key.'" id="variables'.$key.'"></a><strong>$'.$key.'</strong></div>
						  <div class="variablescontent">';
            if( !isset($GLOBALS[$key]) )
			{
                $variables_content .= '$'. $key .' IS NOT SET.';
            }
			else
			{
                $variables_content .= '<pre>'.nl2br(htmlspecialchars(var_export($GLOBALS[$key], true))).'</pre>';
            }
             $variables_content .= '</div>';
        }

            echo '
			<style type="text/css">
			.error-block-backtrace {background-color: #EEEEEE;border-collapse: collapse;color: #000000;display: block;font-size: 12pt;margin: 0 auto;padding: 3px;text-align: left;vertical-align: middle;width: 900px;word-break: break-all;
			}.error-block-backtrace a:link{color:#000;text-decoration:none}.error-block-backtrace a:hover{text-decoration:underline;color:#000}.error-block-backtrace-title{font-weight:700;border:1px solid #FFF;padding:3px}.variablescontent{font-size:11pt;color:#000;background-color:#FFF;padding:3px}.error-block-backtrace a:visited,.error-block-backtrace a:active{text-decoration:none;color:#000}
			</style>
			<div class="error-block-backtrace">
				<div class="error-block-backtrace-title">Zmienne tablicowe: '.$variables_link.'</div>
				'.$variables_content.'
			</div>';

    }

}
set_error_handler('Eror_Handler::error_handler');
Eror_Handler::$_error_config['tryb_debuggowania'] = true;
?>