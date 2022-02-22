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

class Mailing_Admin
{
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function send( $page_num = 0, $cron = false )
	{
		$send_to = ( $cron ? 100 : 20 );
		
		$mailing = PD::getInstance()->select( 'mailing_service', array(
			'status' => 'sending'
		), 1 );
		
		if( empty( $mailing ) )
		{
			return false;
		}
		
		if( empty( $page_num ) )
		{
			$page_num = (int)$mailing['page_num'] + 1;
		}
		
		$send = new  EmailExtender();
		
		$query = PD::getInstance()->from('users u')->join( 'users_data d' )->on( 'd.user_id', 'u.id' );

		$where = array(
			'd.setting_key' => 'newsletter',
			'd.setting_value' => 1
		);
		
		if( count( $mailing['user_language'] ) != count( Translate::codes() )  )
		{
			$query = $query->join( 'users_data d_2' )->on( 'd_2.user_id', 'u.id' );
			
			$where['d_2.setting_key'] = 'default_language';
			$where['d_2.setting_value'] = array( unserialize( $mailing['user_language'] ), 'IN' );
		}
		
		if( $mailing['only_adult'] == 1  )
		{
			$where['u.user_birth_date'] = array( date('Y-m-d', strtotime('-18 years') ), '<' );
		}

		if( $mailing['activ_status'] == '1' )
		{
			$where['u.activ'] = 1;
		}
		elseif( $mailing['activ_status'] == '0' )
		{
			$where['u.activ'] = 0;
		}
		
		$pages = xy( $page_num, $send_to );
		
		
		$rows = $query->setWhere( $where )->orderBy( array( 
			'id' => 'ASC'
		))->fields('u.*,d.setting_key,d.setting_value,d_2.setting_key,d_2.setting_value')->limit( $pages )->get();
		

		if( empty( $rows ) )
		{
			PD::getInstance()->update( 'mailing_service', array(
				'status' => 'send'
			), array(
				'mailing_id' => $mailing['mailing_id']
			) );
			
			return true;
		}
		
		if( !is_numeric( $mailing['users_send'] ) || empty(  $mailing['users_send'] ) )
		{
			$mailing['users_send'] = 0;
		}
		
		$mailing['users_send'] = (int)$mailing['users_send'];
		
		if( !is_numeric( $mailing['users_not_send'] ) || empty(  $mailing['users_not_send'] ) )
		{
			$mailing['users_not_send'] = 0;
		}
		
		$mailing['users_not_send'] = (int)$mailing['users_not_send'];
		
		$emails = new  EmailExtender();

		
		foreach( $rows as $row )
		{
			
			if( (bool)ini_get('safe_mode') == false )
			{
				@set_time_limit(0);
			}
			
			try{
				
				if( filter_var( $row['email'], FILTER_VALIDATE_EMAIL ) )
				{
					$emails->EmailTemplate( array(
						'email_to'		=> $row['email'],
						'email_content'	=> stripslashes( htmlspecialchars_decode( $mailing['content'] ) ),
						'email_title'	=> $mailing['subject'],
						'email_footer'	=> nl2br( stripslashes( htmlspecialchars_decode( $mailing['footer'] ) ) )
					) );
					
					$mailing['users_send']++;
				}
				else
				{
					throw new Exception('');
				}
				
			} catch (Exception $e){
				$mailing['users_not_send']++;  
			}
			
		}
		
		if( count( $rows ) < $send_to )
		{
			PD::getInstance()->update( 'mailing_service', array(
				'status' => 'send'
			), array(
				'mailing_id' => $mailing['mailing_id']
			) );
		}

		PD::getInstance()->update( 'mailing_service', array(
			'page_num'		=> (int)$page_num,
			'users_send'	=> $mailing['users_send'],
			'users_not_send'=> $mailing['users_not_send']
		), array(
			'mailing_id' => $mailing['mailing_id']
		) );
		
		return $mailing;
	}
		/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function test( $mailing )
	{
		$email = new  EmailExtender();
		
		try{
			
			$email->EmailTemplate( array(
				'email_to'		=> $mailing['email'],
				'email_content'	=> stripslashes( htmlspecialchars_decode( $mailing['message'] ) ),
				'email_title'	=> $mailing['subject'],
				'email_footer'	=> nl2br( stripslashes( htmlspecialchars_decode( $mailing['footer'] ) ) ),
			) );

		
		} catch (Exception $e){}
	}
}