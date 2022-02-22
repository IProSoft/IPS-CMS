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


final class X_Cache
{
    
    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected static $prefix = 'IPS_';
    
    protected static $_defaultLifeTime = 3600, $_defaultHash = '3e2:4^Sr+D34ra2a';
    
    /**
     * Check if cache allowed.
     *
     * @access  public
     * @param   array   $config  Configuration
     */
    
    public static function enabled()
    {
        if (function_exists('xcache_get') === false) {
            return false;
        }
        
        self::$_defaultHash = md5(self::$_defaultHash);
        
        return true;
    }
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public static function getLifeTime($expire)
    {
        if (empty($expire)) {
            return self::$_defaultLifeTime;
        }
        return (int) $expire;
    }
    
    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $expire
     * @return void
     */
    public static function set($key, $value, $expire = null)
    {
        xcache_set(self::key($key), array(
            'cache' => $value,
            'hash' => self::getHash($value, self::$_defaultHash)
        ), self::getLifeTime($expire));
    }
    
    protected static function getHash($data, $hash)
    {
        return md5(serialize($data) . $hash);
    }
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public static function key($key)
    {
        return self::$prefix . '_' . md5($key);
    }
    
    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @return mixed
     */
    public static function get($key)
    {
        
        $key = self::key($key);
        
        if (!xcache_isset($key)) {
            return false;
        }
        
        $cache = xcache_get($key);
        
        if (empty($cache['hash']) || !isset($cache['cache'])) {
            return false;
        }
        
        /* if ( $cache['hash'] != self::getHash( $cache['cache'], self::$_defaultHash ) )
        {
        self::delete( $key );
        return false;
        } */
        
        return $cache['cache'];
    }
    
    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return void
     */
    public static function delete($key)
    {
        xcache_unset(self::key($key));
    }
    
    /**
     * Remove all items from the cache.
     *
     * @return void
     */
    public function flush()
    {
        xcache_clear_cache(XC_TYPE_VAR);
    }
    
}
?>