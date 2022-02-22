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

class Users_Online
{
    protected $interval = 100;
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function generateStats( $return_text )
    {
        $this->add();
        $this->clear();
        
        $query = PD::getInstance()->query("
			SELECT  (
						SELECT count(*) FROM " . db_prefix( IPS__FILES ) . "
					) AS up_all,
					(
						SELECT count(*) FROM " . db_prefix( IPS__FILES ) . " WHERE `date_add` > DATE_ADD(CURDATE(), INTERVAL -1 DAY) && `date_add` < CURDATE()
					) AS up_yesterday,
					(
						SELECT count(*) FROM " . db_prefix( IPS__FILES ) . " WHERE `date_add` >= CURDATE()
					) AS up_today,
					(
						SELECT count(id) FROM " . db_prefix( 'users_online' ) . " WHERE `user_id` != 0
					) AS online_users,
					(
						SELECT count(id) FROM " . db_prefix( 'users_online' ) . "
					) AS is_online"
		);
        
	
		$stats = array_merge( $query[0], array(
			'online_guest' => $query[0]['is_online'] - $query[0]['online_users']
		));
		
		return str_replace( array_map( function( $v ){
			return '{' . $v . '}';
		}, array_keys( $stats ) ), $stats, $return_text );
    }

	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */ 
	public function add()
    {
        $user_id = USER_LOGGED ? USER_ID : 0;
        
		$sql = PD::getInstance()->insertOn( 'users_online', array(
			'timestamp' => 'NOW()', 
			'ip' => "INET_ATON('" . user_ip() . "')", 
			'user_id' => $user_id
		));

		if ( !$sql )
		{
            PD::getInstance()->repair( 'users_online' );
        }
    }
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */    
	public function clear()
    {
        PD::getInstance()->from( 'users_online' )->where( 'timestamp', 'field:DATE_SUB(NOW(), INTERVAL ' . $this->interval . ' SECOND)', '<' )->remove();
    }
    
}
