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

class Categories
{
    /**
     * 
     * @ _default_category
     * @static
     */
    static $_default_category = false;
    
    /**
     * Generating category.
     * Verifying whether it is accessible to the current user (for adults)
     *
     * 
     * @return void
     */
    public function loadCategory( $category_id )
    {
		if ( !$this->getCategory( $category_id ) )
        {
			return ips_redirect( false, 'category_not_exists' );
        }
		
		if ( isset( $_GET['adult'] ) )
		{
			$this->imAdult();
		}
		elseif ( $this->category['only_adult'] == 1 && !isAdult() )
		{
			return $this->adultTemplate();
		}
		
		return $this->displayCategory();
    }
	    
    /**
     * Checking whether the identifier of category is valid
     *
     * @param null
     * 
     * @return bool
     */
    public function getCategory( $category_id )
    {
        $this->category = Categories::getCategories( $category_id );
       
		if ( !$this->category )
        {
            return false;
        }
		
        if ( Config::get('services_premium') && $this->category['only_premium'] == 1 )
        {
            if( !Premium::getInc()->premiumService() )
			{
				return Premium::premiumRedirect( 'category/' . $category_id . ',' . $this->category['category_link'] );
			}
        }
        
		/** Only for logged in users */
        if ( $this->category['only_logged_in'] == 1 && !USER_LOGGED )
        {
            return ips_redirect( false, 'category_only_logged' );
        }
        
       return ( !empty( $this->category['category_name'] ) );
    }
	
    /**
     * User set adult
     *
     * @param nyll
     * 
     * @return void
     */
    public function imAdult()
    {
        Cookie::set( 'adult', 'true', 3600 );
    }
	
    /**
     * Display only adult template
     *
     * @param null
     * 
     * @return string
     */
    public function adultTemplate()
    {
        return Templates::getInc()->getTpl( 'category_adult.html', array(
            'category_id' => 'category/' . $this->category['id_category'] . ',' . $this->category['category_link'] . '?adult'
        ) );
    }
    /**
     * Getting list of categories or single category.
     * 
     *
     * @param bool|int $id - identyfikator kategorii
     * 
     * @return array
     */
    public static function getCategories( $id = false, $reload = false )
    {
        if ( !$reload )
        {
			return self::getCachedCategories( $id );
        }
        
        $categories = PD::getInstance()->from( 'upload_categories' )
						->orderBy( Config::getArray( 'categories_options', 'sorting' ) )
						->get();
        
        if( empty( $categories ) )
		{
			return false;
		}
		
		$cache_data = array();
		
		foreach ( $categories as $key => $category )
        {
            $category['category_name'] = has_value( 'category_name_' . strtolower( IPS_LNG ), $category, $category['category_name'] );
			$category['category_link'] = seoLink( false, $category['category_name'] );
			
			$cache_data[ $category['id_category'] ] = $category;
        }
        
        Config::update( 'cache_data_categories', array(
			IPS_LNG => $cache_data
		) );
		
		return self::getCategories( $id );
    }
    
	/**
     * Getting list of categories or single category from cached config variable.
     * 
     *
     * @param bool|int $id - identyfikator kategorii
     * 
     * @return array
     */
    public static function getCachedCategories( $id = false )
    {
        $cache_categories = Config::getArray( 'cache_data_categories', IPS_LNG );
       
        if ( !empty( $cache_categories ) )
        {
			if ( !$id || isset( $cache_categories[$id] ) )
            {
				return $id ? $cache_categories[$id] : $cache_categories;
            }
        }
        
       return self::getCategories( $id, true );
    }

    /**
     * Wyświetlanie kategorii o konkretnym ID
     * Display category for a specific ID
     *
     * @param null
     * 
     * @return void
     */
    public function displayCategory()
    {
        return ( new Core_Query() )->init( 'category', array(
            'condition' => array(
				'category_id' => $this->category['id_category']
			),
            'pagination' => 'category/' . $this->category['id_category'] . ',' . $this->category['category_link'] . ','
        ) );
    }
	
    /**
     * Listing category in a list of fields option
     *
     * @param int|bool $id_category - ID of the selected category
     * 
     * @return string - a list of fields option category
     */
    public static function categorySelectOptions( $category_id = false )
    {
        
        if( !$category_id )
		{
			$category_id = self::defaultCategory();
		}

        $categories  = self::getCategories();
        
        foreach ( $categories as $key => $row )
        {
            $categories[$key] = '<option value="' . $row['id_category'] . '" ' . ( $row['id_category'] == $category_id ? 'selected="selected"' : '' ) . '>' . strip_tags( $row['category_name'] ) . '</option>';
        }
		
        return implode( "\n", $categories );
    }
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
    public static function getCategoriesMenu( $only_li = false )
    {
        $categories = self::getCategories();
        
        foreach ( $categories as $key => $row )
        {
            $categories[$key] = '<li><a href="' . ABS_URL . 'category/' . $row['id_category'] . ',' . $row['category_link'] . '">' . strip_tags( $row['category_name'] ) . '</a></li>';
        }
        
        if ( $only_li )
        {
            return implode( "\n", $categories );
        }
        
        return '<ul class="categories-menu-submenu">' . implode( "\n", $categories ) . '</ul>' . "\n";
    }

       /**
     * Create a default category "Uncategorized".
     *
     * Returning ID of the default category.
     *
     * return int $id 
     */
    public static function exists( $category_id )
    {
		if ( !empty( $category_id ) && is_numeric( $category_id ) )
		{
			return PD::getInstance()->cnt( 'upload_categories', array(
				'id_category' => $category_id 
			) );
		}
		
		return false;
	}
    /**
     * Create a default category "Uncategorized".
     *
     * Returning ID of the default category.
     *
     * return int $id 
     */
    public static function defaultCategory()
    {
        if ( !empty( self::$_default_category ) )
        {
            return self::$_default_category;
        }
        
        $row = PD::getInstance()->select( 'upload_categories', array(
            'is_default_category' => 1 
        ), 1 );
        
        if ( !empty( $row ) )
        {
            self::$_default_category = $row['id_category'];
        }
        else
		{
			include_once( IPS_ADMIN_PATH . '/libs/class.CategoriesAdmin.php' );
			self::$_default_category = Ips_Registry::get( 'Categories_Admin' )->category( array( 
				'is_default_category' => 1, 
				'category_name' => 'Uncategorized', 
				'only_premium' => 0,  
				'category_image' => Ips_Registry::get( 'Categories_Admin' )->defaultThumb( 'Uncategorized' ), 
				'only_adult' => 0
			));
		}
		
		return self::$_default_category;
	}
	
	public function thumbName( $category_name, $lang = false )
	{
		return str_replace( '.html', '', ( $lang ? $lang . '_' : '' ) . seoLink( false, $category_name ) );
	}
}


?>