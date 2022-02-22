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

class Page
{
	public static $instance = false;
	
	public static function getInstance()
	{
		if ( !self::$instance )
		{
			self::$instance = new Page();
		}
		return self::$instance;
	}
	
	/**
	 * Make ability to get sub pages
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function routes( $string )
	{
		$routes = array();
		
		if ( !$string )
		{
			return array(
				'post_permalink' => false 
			);
		}
		
		if ( strpos( $string, '/' ) === false )
		{
			return array(
				'post_permalink' => $string 
			);
		}
		
		list( $routes['post_permalink'], $page_type_fix ) = array_reverse( explode( '/', $string ) );
		
		return $routes;
	}
	/**
	 * Display list
	 *
	 * @param $post_type : news. posts, pages
	 * 
	 * @return 
	 */
	public function displayAll( $post_type )
	{
		$data = $this->getPages( array(
			'post_type' => $post_type
		), false );
		
		if ( empty( $data ) )
		{
			ips_redirect( 'index.html', 'page_empty' );
		}
		
		return Templates::getInc()->getTpl( '/page_types/' . $post_type . '_list.html', array(
			'data' => $data,
			'current_permalink' => false
		) );
	}
	
	/**
	 * Display 
	 *
	 * @param $post_type : news. posts, pages
	 * 
	 * @return 
	 */
	public function display( $post_permalink )
	{
		$data = $this->getSingle( $post_permalink );
		
		if ( empty( $data ) )
		{
			return ips_redirect( 'index.html', 'page_empty' );
		}
		
		return Templates::getInc()->getTpl( '/page_types/' . $data['post_type'] . '.html', array(
			'post' => $data,
			'data' => $this->getPages( array(
				'post_type' => $data['post_type'],
				'id' => array( $data['id'], 'NOT IN' )
			), false ),
			'current_permalink' => $post_permalink 
		));
	}
	
	/**
	 * Getting the content of the news or a list of news / pages.
	 *
	 * @param int $id - id
	 * @param string $status - status 
	 * @param string $visibility - visibility
	 * @param int $limit - data limit
	 * @param string $order - sort by that column
	 * 
	 * @return 
	 */
	public function getPages( array $data = array(), $limit = 1, $order = 'post_date' )
	{
		$data = array_map( function( $value ){
			return !is_array( $value ) ? preg_replace( "/[^0-9a-zA-Z_.-]/si", '', $value ) : $value ;
		}, $data );
		
		$data['post_language'] = array( array( 
			'all', 
			IPS_LNG
		), 'IN' );

		$row = PD::getInstance()->from( 'posts' )->setWhere( $data )->limit( intval( $limit ) )->orderBy( $order );
		
		$rows = $limit == 1 ? array( $row->getOne() ) : $row->get();
	
		if( empty( $rows ) )
		{
			return !ips_log( func_get_args(), 'logs/pages.log' );
		}
		
		array_walk( $rows, function( &$data, $key ){
			if( isset( $data['post_content'] ) )
			{
				$data['post_content'] = nl2br( stripslashes( $data['post_content'] ) );
			}
		});

		return $limit == 1 ? $rows[0] : $rows;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getSingle( $post_permalink )
	{
		$data = $this->getPages( array(
			'post_permalink' => $post_permalink 
		), 1 );
		
		if ( empty( $data ) )
		{
			return ips_redirect( 'index.html', 'page_empty' );
		}
		
		if ( $data['post_visibility'] == 'private' && !USER_LOGGED )
		{
			return ips_redirect( 'index.html', 'page_private' );
		}
		
		App::$base_args['title'] = $data['post_title'];
		
		return $data;
	}

	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function delete( $id )
	{
		if ( empty( $id ) )
		{
			ips_admin_redirect( 'pages', 'action=list', 'Podaj poprawne ID' );
		}
		
		$status = $this->getPages( array(
			'id' => $id 
		) );
		
		if ( !in_array( $status['post_uid'], $this->deleteNotAllowed() ) )
		{
			PD::getInstance()->delete( 'posts', array(
				'id' => $id 
			) );
			
			ips_message( array(
				'normal' =>  'Materiał został usunięty.'
			) );
		}
		else
		{
			ips_message( array(
				'normal' =>  'Nie możesz usuwać domyślnych podstron takich jak np Regulamin'
			) );
		}
		
		ips_admin_redirect( 'pages', 'action=list' );
	}

	
	/**
	 * A form to edit and add news / article
	 *
	 * @param array $row - Post edited data
	 * 
	 * @return string
	 */
	public function form( $row = array() )
	{
		include_once( IPS_ADMIN_PATH .'/language-functions.php' );
		
		return Templates::getInc()->getTpl( '/__admin/add_page.html', array(
			'post_title' => isset( $row['post_title'] ) ? $row['post_title'] : '',
			'post_content' => isset( $row['post_content'] ) ? $row['post_content'] : '',
			'post_type' => isset( $row['post_type'] ) ? $row['post_type'] : '',
			'post_visibility' => isset( $row['post_visibility'] ) ? $row['post_visibility'] : '',
			'post_id' => isset( $row['id'] ) ? $row['id'] : false,
			'post_language' => isset( $row['post_language'] ) ? $row['post_language'] : IPS_LNG,
			'languages' => Translate::codes()
		) );
		
	}
	
	/**
	 * Saving data to a mysql database and verification of correctness.
	 *
	 * @param array $data - data sent from a form
	 * 
	 * @return mixed
	 */
	public function save( $data )
	{
		if ( !is_array( $data ) || empty( $data ) )
		{
			throw new Exception( 'Uzupełnij dane formularza' );
		}
		
		if ( !isset( $data['post_content'] ) || empty( $data['post_content'] ) )
		{
			throw new Exception( 'Wpisz treśc strony lub newsa' );
		}
		
		if ( !isset( $data['post_title'] ) || empty( $data['post_title'] ) )
		{
			throw new Exception( 'Wpisz tytuł strony lub newsa' );
		}
		
		$page_data = array(
			'post_author' => USER_LOGIN,
			'post_date' => date( "Y-m-d H:i:s" ),
			'post_content' => convert_line_breaks( $data['post_content'] ),
			'post_title' => Sanitize::cleanXSS( $data['post_title'] ),
			'post_type' => Sanitize::onlyAlphanumeric( $data['post_type'] ),
			'post_visibility' => Sanitize::onlyAlphanumeric( $data['post_visibility'] ),
			'post_language' => Sanitize::onlyAlphanumeric( $data['post_language'] ),
			'post_permalink' => seoLink( false, $data['post_title'] ) 
		);
		
		if ( strlen( $page_data['post_permalink'] ) > 254 )
		{
			$page_data['post_permalink'] = substr( $page_data['post_permalink'], 0, 250 ) . '.html';
		}
		
		
		
		if ( isset( $data['post_id'] ) )
		{
			if ( !empty( $data['post_id'] ) )
			{
				$page_id = intval( $data['post_id'] );
				
				PD::getInstance()->update( 'posts', $page_data, array(
					'id' => $page_id
				) );
			}
			
			ips_message( 'Pomyślnie zmieniono wpis' );
		}
		elseif ( $page_id = PD::getInstance()->insert( 'posts', $page_data ) )
		{
			ips_message( 'Pomyślnie dodano wpis' );
		}
		else
		{
			throw new Exception( 'Wystąpił błąd podczas zapytania do bazy MySQL' );
		}
		
		Config::update( 'cache_data_posts', array(
			$page_id => array(
				'post_permalink' => $page_data['post_permalink'],
				'post_title' => $page_data['post_title'],
				'post_type' => $page_data['post_type'],
				'post_visibility' => $page_data['post_visibility']
			)
		) );
		
		ips_admin_redirect( 'pages', 'action=list' );
		
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function updateLinks()
	{
		$row = PD::getInstance()->select( 'posts' );
		
		foreach ( $row as $post_data )
		{
			if ( !empty( $post_data['post_id'] ) )
			{
				$post_data['post_permalink'] = seoLink( false, $post_data['post_title'] );
				
				if ( strlen( $post_data['post_permalink'] ) > 254 )
				{
					$post_data['post_permalink'] = seoLink( false, substr( $post_data['post_permalink'], 0, 150 ) );
				}
				
				PD::getInstance()->update( 'posts', array(
					'post_permalink' => $post_data['post_permalink'] 
				), 'id = ' . intval( $post_data['post_id'] ) );
				
			}
		}
		
		Config::update( 'cache_data_posts', array() );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function cache( $page_id, $field )
	{
		$page_id = preg_replace( "/[^0-9]/si", '', $page_id );
		
		$page = Config::getArray('cache_data_posts', $page_id );
		
		if( !$page || !isset( $page[ $field ] ) )
		{
			$page = PD::getInstance()->select( 'posts', array(
				'id' => $page_id
			), 1, 'post_title,post_permalink,post_type,post_visibility' );
			
			if( empty( $page ) )
			{
				return '';
			}
			
			Config::update( 'cache_data_posts', array(
				$page_id => $page
			) );
		}
		
		return $page[ $field ]; 
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function url( $data )
	{
		$page = self::getInstance()->getPages( $data );
		
		if( isset( $page['post_type'] ) )
		{
			return '/' . $page['post_type'] . '/' . $page['post_permalink'];
		}
		
		return '/';
	}
	
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function deleteNotAllowed()
	{
		return array( 
			'uid_rules', 
			'uid_news'
		);
	}
}
?>