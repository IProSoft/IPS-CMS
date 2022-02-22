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

class Pinestic
{
    /**
     * TO DO COMMENT
     *
     * @param 
     * 
     * @return 
     */
    public static function init()
    {
        if ( in_array( IPS_ACTION, array(
             'login',
            'register',
            'edit_profile' 
        ) ) )
        {
            App::minimalLayout();
        }
        
        if ( !defined( 'IPS_ONSCROLL' ) )
        {
            add_action( 'inside_content_wrapper', 'Pinestic::beforeContent', null, null, 0 );
        }
    }
    /**
     * Calling a function on the basis of the name, eg login to call_login
     */
    public static $instance = false;
    
    /**
     * TO DO COMMENT
     *
     * @param 
     * 
     * @return 
     */
    public static function getInstance()
    {
        
        if ( !self::$instance )
        {
            self::$instance = new pinestic();
        }
        return self::$instance;
    }
    /**
     * TO DO COMMENT
     *
     * @param 
     * 
     * @return 
     */
    public function index_action( $action )
    {
        if ( method_exists( $this, 'call_' . $action ) )
        {
            return $this->{'call_' . $action}();
        }
    }
    /**
     * TO DO COMMENT
     *
     * @param 
     * 
     * @return 
     */
    public function call_board()
    {
        $b = new Board();
        return $b->displayBoardPins( IPS_ACTION_GET_ID );
    }
    /**
     * TO DO COMMENT
     *
     * @param 
     * 
     * @return 
     */
    public static function displayPin()
    {
        $pin = new Pin();
        return '<div id="ips-pinover"><div id="ips-pinover-container">' . $pin->showPin( IPS_ACTION_GET_ID, getFileInfo() ) . '</div></div>';
    }
    /**
     * TO DO COMMENT
     *
     * @param 
     * 
     * @return 
     */
    public static function beforeContent()
    {
        $item = '';
        
        if ( in_array( IPS_ACTION, array(
             'profile',
            'user_boards',
            'user_pins',
            'user_repins',
            'user_likes',
            'user_followers',
            'user_follow' 
        ) ) )
        {
            $u    = new PinUser();
            $item = $u->userPanel();
        }
        elseif ( in_array( IPS_ACTION, array(
             'board' 
        ) ) )
        {
            $b    = new Board();
            $item = $b->boardPanel();
        }
        elseif ( in_array( IPS_ACTION, array(
             'source',
            'pin_repins',
            'pin_likes',
            'popular_followed',
            'popular_repinned',
            'categories' 
        ) ) )
        {
            $item = self::headerInfo();
        }
        elseif ( in_array( IPS_ACTION, array(
             'users',
            'boards',
            'pins',
            'following' 
        ) ) )
        {
            $item = self::headerSort();
        }
        else
        {
            //$item .= '<div class="item item-prepend not-visible"><ul class="main_menu">' . App::getMenu( 'pinit_menu' ) . '</ul></div>';
            //$item .= '<div class="item item-prepend not-visible"><div class="pin-item pinned">' . AdSystem::getInstance()->showAd('top_of_list) . '</div></div>';
            //$item .= '<div class="item item-append not-visible"><div class="pin-item pinned">' . AdSystem::getInstance()->showAd('bottom_of_list) . '</div></div>';
        }
        
        return $item;
    }
    
    /**
     * TO DO COMMENT
     *
     * @param 
     * 
     * @return 
     */
    public static function topMenu()
    {
        return App::getMenu( 'pinit_menu' );
        
    }
    /**
     * 
     *
     * @param 
     * 
     * @return 
     */
    public static function headerSort()
    {
        return Templates::getInc()->getTpl( 'sortable_panel_' . IPS_ACTION . '.html', array ());
    }
    
    /**
     * 
     *
     * @param 
     * 
     * @return 
     */
    public static function headerInfo()
    {
        global ${IPS_LNG};
        if ( isset( ${IPS_LNG}['pinit_meta_' . IPS_ACTION] ) )
        {
            return '<h1 class="big-header-title">' . ${IPS_LNG}['pinit_meta_' . IPS_ACTION] . '</h1>';
        }
        elseif ( IPS_ACTION == 'categories' )
        {
            $category = Categories::getCategories( IPS_ACTION_GET_ID );
            return '<h1 class="big-header-title">' . $category['category_name'] . '</h1>';
        }
        elseif ( IPS_ACTION == 'source' )
        {
            if ( empty( $_GET['source'] ) )
            {
                ips_redirect();
            }
            
            return Templates::getInc()->getTpl( 'source_pins_header.html', array(
                 'source_text' => __s( 'pinit_source_pins', $_GET['source'] ),
                'source' => $_GET['source'] 
            ) );
            
        }
    }
    /**
     * TO DO COMMENT
     *
     * @param 
     * 
     * @return 
     */
    public static function mod( $action, $id, $onpage )
    {
        $redirect = false;
        
        if ( $onpage == 'onpage' )
        {
            $redirect = 'index.html';
        }
        
        switch ( $action )
        {
			case 'delete':
            try
            {
                $pin      = new Pin();
                $pin_info = $pin->delete( array(
                     'id' => (int) $id 
                ) );
                $status   = true;
            }
            catch ( Exception $e )
            {
                $status = false;
            }
            break;
        }
        
        if ( !isset( $_GET['ips_ajax_call'] ) )
        {
            ips_redirect( $redirect );
        }
        else
        {
            return $status;
        }
    }
    
    
}
?> 