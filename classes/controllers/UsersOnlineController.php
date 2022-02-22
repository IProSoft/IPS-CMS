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

class Users_Online_Controller
{
    public function route()
    {
        if ( !Config::get( 'online_stats' ) )
		{
            return ips_redirect( 'index.html' );
        }
        
		if(  !$users_online = Ips_Cache::get( 'online_stats' ) )
		{
			$users_online = PD::getInstance()->from( array( 
				'users_online' => 'o', 
				'users' => 'u'
			) )->setWhere( array(
				'o.user_id' => array( '0', '!='),
				'o.user_id' => 'field:u.id'
			) )->get();
				
				
				
			if ( !empty( $users_online ) )
			{
				foreach ( $users_online as $key => $user )
				{
					$users_online[$key]['url'] = ABS_URL . '/profile/' . $user['login'];
					$users_online[$key]['avatar_url'] = ips_user_avatar( $user['avatar'], 'url' );
				}

				$users_online = Templates::getInc()->getTpl( 'users_online.html', array(
					'users_online' => $users_online
				) );
			} 
			else
			{
				return ips_redirect( false, array(
					'info' => 'users_online_empty'
				));
			}
			
			Ips_Cache::write( $users_online, 'online_stats' );
		}
		
		return $users_online; 
    }
}
