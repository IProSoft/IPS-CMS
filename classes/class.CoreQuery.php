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

class Core_Query extends Core_Display
{
	/**
	 * Default args
	 */
	public $files = null;
	
	public $args = array();
	
	public $defaultActions = array(
		'columns' => '*', 
		'condition' => false, 
		//"upload_status = 'public'",
		'sorting' => 'date_add', 
		'pagination' => '/page/', 
		'premium' => true, 
		'count_records' => true
	);
	
	/**
	 * Kontrolery akcji, które można zapisać w cache.
	 * Controllers of shares that can be stored in the cache.
	 */
	public $cacheable = array( 'main', 'waiting', 'archive', 'smilar', 'share', 'nk', 'google', 'load_file', 'pinit_pin', 'top' );
	
	/**
	 * Odwołanie do odpowiednich akcji sprawdzających wywoływany kontroler
	 * A reference to the relevant shares called controller checks
	 *
	 * @param $controller - data display controller
	 * @param $args - dodatkowe argumenty decydujące o sposobie wyświetlania
	 * lub nadpisują domyślne metody    
	 * @param $args - additional arguments that determine how to display or override the default method
	 * 
	 * @return void
	 */
	public function init( $controller, $args = array() )
	{
		$this->args = is_array( $args ) ? array_merge( $this->args, $args ) : $this->args;
		
		/**
		 * PinIt template has different action
		 */
		if ( IPS_VERSION == 'pinestic' )
		{
			$controller = 'pinit_' . $controller;
			$this->controller = Core_Registry_Pinit::init();
		}
		else
		{
			$this->controller = Core_Registry::init();
		}
		
		$this->controller = $this->controller->call_action( $controller );
		
		if ( is_array( $this->controller )  )
		{
			apply_filters( 'core_init_before', $this );
			/**
			 * Method allowing data/arguments modification before function call
			 */
			if ( isset( $this->controller['callable_before'] ) )
			{
				if ( method_exists( $this, $this->controller['callable_before'] ) )
				{
					$this->{$this->controller['callable_before']}( $controller );
				}
				elseif( is_callable( $this->controller['callable_before'] ) )
				{
					call_user_func( $this->controller['callable_before'] );
				}
			}

			$this->controller = $this->setControllers( $this->args );

			/** Checking whether caching is turned on and the controller can be stored in the cache.* */
			if ( !IPS_FILE_CACHE || !in_array( $controller, $this->cacheable ) )
			{
				return $this->appDispatch( $controller ) . $this->setPaginator( false );
				
			}
			else
			{
				return $this->appCached( $controller );
			}
		}
		else
		{
			$this->debugAction( $controller );
		}
		
	}

	/**
	 * Merge controller from Core_Registry and args
	 *
	 * @param $args
	 * 
	 * @return 
	 */
	public function setControllers( $args )
	{
		array_walk( $this->controller, function( &$value, $key, $args ){
			if( isset( $args[$key] ) )
			{
				$value = is_array( $args[$key] ) && is_array( $value ) ? array_merge( $args[$key], $value ) : $args[$key];
				unset( $args[$key] );
			}
		}, $args );

		return array_merge( array(
			'table' => IPS__FILES . ' up',
			'page' 	=> IPS_ACTION_PAGE,
			'limit' => xy( IPS_ACTION_PAGE, Config::get('files_on_page') )
		), $this->controller, $args );
	}
	/**
	 * Cachowanie listy wszystkich plików.
	 * Caching a list of all files.
	 * Sprawdzanie cache i wyświetlanie użytkownikowi.
	 * Checking cache and display to the user.
	 *
	 * @param $controller - data display controller
	 * 
	 * @return 
	 */
	public function appCached( $controller )
	{
		$CacheFilename = md5( $controller . serialize( $this->args ) . $this->appActionData( 'page' ) );
		
		if ( !$cache = Ips_Cache::get( $CacheFilename, true ) )
		{
			$cache = array();
			ob_start();
			
			echo $this->appDispatch( $controller );
			
			$cache['content'] = ob_get_contents();
			ob_end_clean();
			
			$cache['paginator'] = $this->getPaginator( $controller );
			
			Ips_Cache::write( $cache, $CacheFilename );
			
		}
		
		$this->setPaginator( $cache['paginator'], $controller );
		
		return $cache['content'];
		
		unset( $CacheFilename, $cache );
	}
	
	
	/**
	 * definitions of data
	 *
	 * @param $controller - data display controller
	 * 
	 * @return 
	 */
	public function appDispatch( $controller )
	{
		/**
		 * As argument where passed only function, not controls
		 */
		if ( is_string( $this->controller ) )
		{
			if ( is_callable( $this->controller ) )
			{
				return call_user_func( $this->controller );
			}
		}
		
		/**
		 * The table from which data will be collected
		 */
		$table = $this->appActionData( 'table' );
		
		/**
		 * Condition retrieve data from the database
		 */
		$conditions = $this->appActionData( 'condition' );
		
		/**
		 * Additional conditions such as Premium materials
		 */
		$this->appActionConditions( $conditions, $table );

		/**
		 * Select limit, eg 0,10
		 */
		$limit = $this->appGetLimit( 'limit' );
		
		/**
		 * Sort by a particular column
		 */
		$sorting = $this->appActionData( 'sorting' );
		
		$this->getData( $table, $conditions, $sorting, $limit );
		
		if ( !isset( $this->args['display'] ) )
		{
			return $this->displayData( $controller );
		}
		
	}
	
	/**
	 * Sprawdzanie domyślnych parametrów kontrolera.
	 * Check the default parameters of the controller.
	 * Nadpisanie lub podmiana parametrów lub zwrócenie parametru domyslnego
	 * Overwrite or substitution parameters or return the default parameter
	 *
	 * @param $controller - data display controller
	 * @param $data - parameter name
	 * 
	 * @return 
	 */
	public function appActionData( $data )
	{
		if ( isset( $this->controller[$data] ) && $this->controller[$data] != false )
		{
			/* if ( strpos( $this->controller[$data], "{" ) !== false && !empty( $this->args ) )
			{
				return str_replace( array_keys( $this->args ), array_values( $this->args ), $this->controller[$data] );
			} */
			return $this->controller[$data];
		}
		
		/* if ( isset( $this->args[$data] ) && $this->args[$data] !== false )
		{
			return $this->args[$data];
		} */
		
		if ( isset( $this->defaultActions[$data] ) )
		{
			return $this->defaultActions[$data];
		}
		
		return false;
	}
	
	
	/**
	 * Dodatkowe warunki pobierania danych.
	 * Additional terms of data collection.
	 * Obsługa kategorii Premium i wyświetlania wybranych materiałów
	 * Service category Premium and display of selected materials
	 *
	 * @param $conditions - obecene data retrieval conditions
	 * 
	 * @return 
	 */
	public function appActionConditions( &$conditions, $table )
	{
		$show_files = Session::getNonEmpty( 'show_files' );
		
		if ( $show_files && Config::get( 'widget_sort_box' ) == 1 && is_array( $show_files ) )
		{
			if ( strpos( $table, IPS__FILES ) !== false && $show_files[0] != 'all' )
			{
				$conditions['upload_type'] = array( $show_files, 'IN' );
			}
		}
		
		if ( Config::get('services_premium') )
		{
			$premium = $this->appActionData( 'premium' );
			
			if ( $premium )
			{
				$premium = Premium::getInc();
				
				if ( !$premium->premiumService( 'category' ) && $categories_id = $premium->premiumCategories() )
				{
					$conditions['category_id'] = array( $categories_id, 'NOT IN' );
				}
			}
		}
		
		if ( defined( 'IPS_WAIT_BLOCKED' ) && !USER_ADMIN )
		{
			if ( strpos( $table, IPS__FILES ) !== false )
			{
				$conditions['upload_activ'] = 1;
			}
		}
		
		return $conditions;
	}
	/**
	 * Displaying Data
	 *
	 * @param $controller - data display controller
	 * @return 
	 */
	public function displayData( $controller )
	{
		ob_start();

		/**
		 * Calls defined functions before content
		 */
		echo do_action( 'before_files_display' );

		if ( IPS_VERSION == 'pinestic' )
		{
			echo '
				<script type="text/javascript">
					if( typeof ips_items == "undefined" )
					{
						var ips_items = []
					}
					ips_items.push(' . json_encode( $this->pinitContent( 'compile' ) ) . ');
					
				</script>
				';
		}
		elseif( $msg = $this->checkForFiles() )
		{
			echo $msg;
		}
		elseif ( $this->appActionData( 'comments' ) )
		{
			/**
			 * Displaying only user comments
			 */
			echo $this->displayComments();
		}
		elseif ( $this->appActionData( 'users' ) )
		{
			/**
			 * Displaying Top user list
			 */
			Top::loadStats( $this->files );
			
			echo $this->displayUsers( $this->appActionData( 'page' ) );
		}
		elseif ( $this->appActionData( 'user_mod' ) )
		{
			/**
			 * Displaying mod panel
			 */
			echo $this->modPanel( $controller );
		}
		else
		{
			/**
			 * Displaying only files
			 */
			echo $this->displayFiles();
		}
		
		/**
		 * Calls defined functions after content
		 */
		echo do_action( 'after_files_display' );
		
		$data = ob_get_contents();
		ob_end_clean();
		
		return $data;
	}
	
	public function setPaginator( $content = false )
	{
		if ( ( !isset( $this->args['pagination'] ) || $this->args['pagination'] != false ) && !defined( 'IPS_AJAX' ) )
		{
			if ( !$content )
			{
				$content = $this->getPaginator();
			}
			add_action( 'display_paginator', 'ips_html', array(
				$content 
			), null, 0 );
		}
	}
	
	public function getPaginator()
	{
		
		/**
		 * Count all records for pagination.
		 */
		$data_limit = isset( $this->files[0]['COUNTED_DATA'] ) ? $this->appPagesLimit( $this->files[0]['COUNTED_DATA'] ) : 1;
		
		$page_url = $this->appActionData( 'pagination' );
		
		return $this->paginator( $data_limit, $page_url, $this->appActionData( 'page' ) );
		
	}
	/**
	 * Wrapper - ajax/normal use
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function pinitContent( $option )
	{
		if ( isset( $this->controller['pinit_users'] ) )
		{
			/**
			 * Displaying only user/board followers/following /users list
			 */
			return $this->pinitUsers( $option );
		}
		elseif ( !isset( $this->controller['pinit_boards'] ) )
		{
			/**
			 * Display pins
			 */
			return $this->pins( $option );
		}
		else
		{
			/**
			 * Display boards
			 */
			return $this->boards( $option );
		}
	}
	/**
	 * Retrieving data from a MySQL database
	 *
	 * @param $table - table name
	 * @param $columns - column names
	 * @param $conditions - download condition
	 * @param $sort_by - sort by a particular column
	 * @param $limit - download limit
	 * 
	 * @return void
	 */
	public function getData( $table, $conditions, $orderBy, $limit )
	{
		/**
		 * Nazwy kolumn, które ma zwierać zapytanie + zliczanie rekordów.
		 * The names of the columns + count records.
		 */
		
		$columns = $this->appActionData( 'columns' );

		$db = PD::getInstance();
		
		$query = $db->from( $table );
		
		if( $joins = $this->appActionData( 'join' ) )
		{
			/** Allow multi JOIN */
			if( !isset( $joins[0]['table'] ) )
			{
				$joins = array( $joins );
			}

			foreach( $joins as $join )
			{
				$query = $query->join( $join['table'], ( isset( $join['type'] ) ? $join['type'] : 'LEFT' ) )->on( $join['on'] );
			}
		}
		
		if( $use = $this->appActionData( 'use' ) )
		{
			$query = $query->useIndex( $use );
		}

		$query = $query->setWhere( $conditions );
		
		$columns = $this->appColumns( $columns, $conditions, $query );
		
		$this->files = $query->replaceQuery( 'fields', array( $columns ) )->orderBy( $orderBy )->get( $limit );
		
		if ( $this->files == false )
		{
			return false;
		}
	}
	
	
	/**
	 * Wywołanie funkcji przed wyświetleniem danych
	 * Calling the function before displaying data
	 *
	 * @param $controller - data display controller
	 * @param $data - the name of the function call (before, after)
	 * 
	 * @return 
	 */
/* 	public function appCallFunctions( $controller, $data )
	{
		$functions = $this->appActionData( $data );
		
		if ( !empty( $functions ) )
		{
			if ( strpos( $functions, ',' ) !== false )
			{
				$return    = '';
				$functions = explode( ',', $functions );
				foreach ( $functions as $function )
				{
					if ( is_callable( $function ) )
					{
						$return .= call_user_func( $function );
					}
				}
				return $return;
			}
			if ( is_callable( $functions ) )
			{
				return call_user_func( $functions );
			}
			
		}
	} */
	/**
	 * Określaniue ilości stron do wyświetlenia w paginacji
	 * Determining the number of pages to display in pagination
	 *
	 * @param $total_count - all materials
	 * 
	 * @return integer
	 */
	public function appPagesLimit( $total_count )
	{
		return ceil( $total_count / Config::get( 'files_on_page' ) );
	}
	/**
	 * Przygotowanie zapytania zliczającego do wykorzystania w paginacjach
	 * Preparing a query that counts for use in pagination
	 *
	 * @param $columns - column
	 * @param $condition - condition
	 * @param $table - The table from which the data are collected
	 * 
	 * @return 
	 */
	public function appColumns( $columns, $condition, &$db )
	{
		if ( isset( $this->args['counted_data'] ) )
		{
			return $columns . ', (' . $this->args['counted_data'] . ') AS COUNTED_DATA';
		}
		elseif ( ( !isset( $this->args['pagination'] ) || $this->args['pagination'] != false ) && $this->appActionData( 'count_records' ) == true )
		{
			return $columns . ', ( ' . $db->fields( 'COUNT(*)' )->getQuery() . ' ) AS COUNTED_DATA';
		}
		else
		{
			return $columns;
		}
	}
	
	
	/**
	 * Określenie limitu zapytania. Jeśli został określony domyslnie 
	 * zostaje wysłany w niezmienionej formie
	 * Specifying a query limit. If you specify a default is sent unchanged
	 *
	 * @param $controller - data display controller
	 * @param $data - parameter name
	 * 
	 * @return 
	 */
	public function appGetLimit( $data )
	{
		$limit = $this->appActionData( $data );
		
		if ( empty( $limit ) )
		{
			$on_page = $this->appActionData( 'on_page' );
			$pages   = xy( $this->appActionData( 'page' ), $on_page ? $on_page : Config::get( 'files_on_page' ) );
			
			return $pages[1] . ',' . $pages[2];
		}
		
		return $limit;
	}
	
	
	
	
	
	
	
	
	
	
	/************** Functions called in addition, do not belong to the main class ***************/
	
	public function getRandomId()
	{
		$rows = PD::getInstance()->optRand( IPS__FILES, array(
			'upload_status' => 'public',
		), 1 );
		
		$this->args = array_merge( $this->args, array(
			'conditions' => array(
				'id' => has_value( 'id', $rows ) 
			)
		));
	}

	/**
	 * Additional conditions for pages NK, Google, Facebook
	 *
	 * @param null
	 * 
	 * @return null
	 */
	public function socialApp()
	{
		
		$routes = App::routes( array(
			'route', 'time', 'page'
		) );

		add_action('before_files_display', 'ips_template_helper', array( 'top_menu_social.html', array(
			'route' => $routes['route'],
			'current_sort' => ( !empty( $routes['time'] ) ? $routes['time'] : false ) 
		)), 10 );
		
		$condition = array();

		$pagination = ( !empty( $routes['time'] ) ? $routes['time'] : Config::getArray( 'template_settings', 'sorting_top' ) );
				
		if( preg_match( '/(day|week|month|year)/', $pagination ) )
		{
			$condition['up.date_add'] = array( 'DATE_SUB(NOW(), INTERVAL 1 ' . strtoupper( $pagination ), '>');
		}
		
		$this->args = array_merge( $this->args, array(
			'columns' => 'up.*, s.'. $routes['route'],
			'condition' => $condition,
			'sorting' => 's.'. $routes['route'],
			'pagination' => '/' . $routes['route'] . '/' . $pagination . '/',
			'limit' => xy( ( !empty( $routes['page'] ) ? $routes['page'] : 1 ), Config::get('files_on_page') ),
		) );
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function filterFiles()
	{
		$filter = get_input( 'filter' );
		if ( in_array( $filter, Config::get( 'allowed_types' ) ) )
		{
			$this->args = array_merge( $this->args, array(
				'condition' => array(
					'upload_type' => $filter
				),
				'pagination' => '/filter/' . $filter . '/'
			));
		}
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function boardBefore()
	{
		$this->args = array_merge( $this->args, array(
			'{board_id}' => IPS_ACTION_GET_ID,
			'{pagination}' => '/board/' . IPS_ACTION_GET_ID . '/page/' 
		) );
		
		if ( isset( $_GET['action'] ) )
		{
			if ( $_GET['action'] == 'followers' )
			{
				$this->controller['table']       = 'pinit_users_follow_board LEFT JOIN users ON users.id = pinit_users_follow_board.user_id';
				$this->controller['condition']   = 'pinit_users_follow_board.board_id = {board_id}';
				$this->controller['columns']     = "pinit_users_follow_board.*, users.*, CONCAT( users.first_name,' ',users.last_name ) as full_name";
				$this->controller['pinit_users'] = true;
				
				$this->args['pagination'] = '/board/' . IPS_ACTION_GET_ID . '/' . $_GET['action'] . '/';
				$this->args['sorting']      = 'users.date_add';
				
			}
			elseif ( $_GET['action'] == 'pins' )
			{
				
			}
		}
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	
	public function topFiles()
	{
		$top = new Top();
		
		add_action( 'before_files_display', 'ips_html', array(
			$top->getMenu() 
		), 10 );
		
		$this->controller = array_merge( $this->controller, $top->actions() );
	}

	public function debugAction( $controller )
	{
		ips_log( "\n" . 'Empty controller Core_Query: ' . $controller 
		. "\n\tIPS_ACTION : " . IPS_ACTION 
		. "\n\tIPS_ACTION_GET_ID : " . IPS_ACTION_GET_ID  
		. ( isset( $_SERVER['SCRIPT_FILENAME'] ) ? "\n\tSCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] : '' ) 
		. ( isset( $_SERVER['REQUEST_URI'] ) ? "\n\tREQUEST_URI: " . $_SERVER['REQUEST_URI'] : '' ) );
		ips_log( ips_backtrace() );
		ips_log( $_SERVER );
	}
}
?>
