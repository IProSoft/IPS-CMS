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

class Top
{
    /**
     *
     */
    private $date_sort_condition = false;
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function __construct()
    {
        
        
    }

    /**
     * 
     *
     * @param 
     * 
     * @return 
     */
    public function actions()
    {
        $routes = App::routes( array(
			'sort_by', 'date_add', 'page'
		));
       
		$routes['sort_by']  = preg_match( '/(votes_opinion|votes_count|comments|users)/', $routes['sort_by'] ) ? $routes['sort_by'] : 'votes_opinion';
        $routes['date_add'] = preg_match( '/(day|week|month|year)/', $routes['date_add'] ) ? $routes['date_add'] : Config::getArray( 'template_settings', 'sorting_top' );
        
        
		$conditions = array(
			'sorting' => $routes['sort_by'],
			'pagination' => 'top/' . $routes['sort_by'] . '/' . strtolower( $routes['date_add'] ) . '/',
			'page' => ( !empty( $routes['page'] ) ? $routes['page'] : 1 ),
		);
		
		if( $routes['sort_by'] == 'users' )
		{
			return array_merge( $conditions, array(
				'table' => 'users' ,
				'columns' => '*',
				'condition' => array(
					'activ' => 1
				),
				'sorting' => 'user_uploads',
				'premium' => false,
				'pagination' => 'top/users/alltime/',
				'users' => true,
			));
		}

		if( $routes['date_add'] != 'alltime' )
		{
			$conditions['condition'] = array(
				'date_add' => array( 'field:DATE_SUB(CURDATE(),INTERVAL 1 ' . strtoupper( $routes['date_add']) . ' )', '>=' )
			);
		}
		
		return $conditions;
    }
    
    /**
     * 
     *
     * @param 
     * 
     * @return 
     */
    public function getMenu()
    {
        return Templates::getInc()->getTpl('top_menu.html', array());
    }

    /**
     * 
     *
     * @param 
     * 
     * @return 
     */
    public static function loadStats( &$users_data )
    {
        $user_ids = array_column( $users_data, 'id', 'login' );
        
        $up_prefix = db_prefix( IPS__FILES . ' up' );
		
        $users_data = PD::getInstance()->query("
			SELECT ( " . $users_data[0]['COUNTED_DATA'] . " ) as COUNTED_DATA,
			( SELECT COALESCE( SUM( up.upload_activ=1 ), 0 ) FROM  " . $up_prefix . " WHERE user_id = u.id ) as added_main, 
			( SELECT COALESCE( SUM( up.upload_activ=0 ), 0 ) FROM  " . $up_prefix . " WHERE user_id = u.id ) as added_wait,
			( SELECT COALESCE( SUM( comment_opinion ), 0 ) FROM " . db_prefix( 'upload_comments' ) . " WHERE user_id = u.id  ) as comments_opinion,
			( SELECT COALESCE( SUM( votes_opinion ), 0 ) FROM " . $up_prefix . " WHERE user_id = u.id  ) as posts_opinion,
			u.* FROM " . db_prefix( 'users', 'u' ) . " WHERE u.id IN ( " . implode(',', $user_ids ) . ")
		");
    }
}
?>