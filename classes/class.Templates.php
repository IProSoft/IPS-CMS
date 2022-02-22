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

require_once(LIBS_PATH . '/Rain.tpl/RainTpl.php');

class Templates extends RainTpl
{
    public $tplVars = array();
    
    public $_padding;
    
    public static $loopTplFile = false;
    
    private static $instance = false;
    
    static $cache_expire = 3600; // default cache expire time = hour
    /*
     * Konstruktor klasy nadaje odpowiednie ustawienia dla klasy
     * RainTpl. cache_expire nadaje czas przetrzymywania cache
     * pod warunkiem, że zostało one włączone. 0 - oznacza brak cachowania.
     * The class constructor suitable settings for the class RainTpl. cache_expire cache suitable retention time, provided that they have been included. 0 - means no caching.
     */
    public function __construct()
    {
        RainTpl::configure(array(
            'tpl_dir' => ABS_TPL_PATH . '/',
            'cache_dir' => ABS_PATH . '/cache/tpl_cache/',
            'debug' => IPS_DEBUG,
            'path_replace_list' => array(),
            'base_url' => ABS_URL,
            'cache_expire' => ( IPS_TPL_CACHE ? Config::get( 'system_cache', 'templates_expiry') : 0 ),
            'cache' => IPS_TPL_CACHE
        ));
        
        self::$cache_expire = (IPS_TPL_CACHE ? Config::get( 'system_cache', 'templates_expiry' ) : false);
        
        
        return $this;
    }
    
    /**
     * 
     *
     * @param 
     * 
     * @return 
     */
    public static function getInc()
    {
        if (!self::$instance) {
            self::$instance = new Templates();
        }
        
        return self::$instance;
    }
    
    /**
     * Method only for Core_Display
     *
     * @param 
     * 
     * @return 
     */
    public function getThisTpl($tpl, &$tpl_vars)
    {
        
        $this->var = array();
        
        parent::assign( $tpl_vars );
        
        /*
         * We provide translation of phrases in the template files
         */
        
        return Translate::getInstance()->translate( parent::draw( $tpl ) );
        
    }

  
    /**
     * 
     *
     * @param 
     * 
     * @return 
     */
    public function getTpl( $tpl_name, $tpl_vars = null, $cache_id = null )
    {
        if ( self::$cache_expire && $cache_id !== null )
		{
            return $this->cache( $tpl_name, $tpl_vars, $cache_id );
        }
        
        if ( $tpl_vars !== null )
		{
            /* $this->var = array(); */
            
            parent::assign($tpl_vars);
        }

        return parent::draw($tpl_name);
    }
    
    /**
     * If exists a valid cache for this template it returns the cache
     *
     * @param string $tpl_name Name of template (set the same of draw)
     * @param string $tpl_vars Set  associative array name/value
     * @return return a string
     */
    
    
    public function cache( $tpl_name, $tpl_vars, $cache_id )
    {
        
        $cache_id = '/tpl/' . $tpl_name . '_' . md5($cache_id);
        
        if (!$content = Ips_Cache::get($cache_id, false, self::$cache_expire)) {
            /* ips_log('Get cached template ' . $tpl_name ); */
            Ips_Cache::write( $this->getTpl( $tpl_name, $tpl_vars ), $cache_id );
        }
        
        /* ips_log('Used cached template ' . $tpl_name ); */
        
        return $content;
    }
    /**
     * Cant use templates with Loop
     *
     * @param 
     * 
     * @return 
     */
    public function getLoopTpl($tpl_name)
    {
        if (!isset(self::$loopTplFile[$tpl_name . 'compiled_filename']) || self::$loopTplFile[$tpl_name . 'compiled_filename'] == false) {
            $this->check_template($tpl_name);
            self::$loopTplFile[$tpl_name . 'compiled_filename'] = $this->tpl['compiled_filename'];
            $raintpl_contents                                   = Translate::getInstance()->translate(file_get_contents($this->tpl['compiled_filename']));
            file_put_contents($this->tpl['compiled_filename'], $raintpl_contents, LOCK_EX);
        }
        ob_start();
        extract($this->tplVars);
        include self::$loopTplFile[$tpl_name . 'compiled_filename'];
        $raintpl_contents = ob_get_clean();
        unset($this->tpl);
        
        return $raintpl_contents;
    }
    
    
}
?>