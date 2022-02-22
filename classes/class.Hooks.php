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


class Hooks
{
	//action filters array   
	public $filters = array();
	public $merged_filters = array();
	
	//action hooks array   
	public $actions = array();
	
	
	
	/**
	 * Register all defined actions
	 * @param null
	 */
	 	
	public function register_actions()
	{
		/** Add cache with ad_action */
		$register_actions = $this->get_hooks();
		
		if ( empty( $register_actions ) || !is_array( $register_actions ) )
		{
			return;
		}
		
		foreach ( $register_actions as $hook => $actions )
		{
			foreach ( $actions as $action )
			{
				add_action( $action['hook'], $action['function'], $action['params'], $action['priority'] );
			}
			
			array_sort_by_column( $this->actions[$hook], 'priority', SORT_ASC );
		}
		
	}
	/**
	 * Ads a function to an action hook
	 * @param $hook
	 * @param $function
	 */
	 
	public function register_action( $hook, $function, $params, $priority = 1, $key = false )
	{
		$actions = $this->get_hooks( false );
		
		if ( !isset( $actions[$hook] ) )
		{
			$actions[$hook] = array();
		}
		
		$actions[$hook][] = array(
			'hook' => $hook,
			'function' => $function,
			'params' => $params,
			'priority' => (float) $priority,
			'key' => ( $key ? $key : time() ) 
		);
		
		return $this->update_actions_helper( $actions, $hook );
	}
	
	/**
	 * Updates a function to an action hook
	 * @param $hook
	 * @param $function
	 */
	public function update_action( $key, $hook, $function, $params, $priority = 1 )
	{
		$this->delete_action_by_key( $key );
		
		$actions = $this->get_hooks( false );
		
		if ( !isset( $actions[$hook] ) )
		{
			$actions[$hook] = array();
		}
		
		$actions[$hook][] = array(
			'hook' => $hook,
			'function' => $function,
			'params' => $params,
			'priority' => (float) $priority,
			'key' => $key 
		);
		
		return $this->update_actions_helper( $actions, $hook );
	}
	/**
	 * Delete function from hook
	 * @param $hook
	 * @param $function
	 */
	public function delete_action( $hook, $function )
	{
		$actions = $this->get_hooks( false );
		
		if ( !isset( $actions[$hook] ) )
		{
			return;
		}
		
		foreach ( $actions[$hook] as $key => $action )
		{
			if ( $action['function'] == $function )
			{
				unset( $actions[$hook][$key] );
			}
		}
		
		return $this->update_actions_helper( $actions, $hook );
	}
	
	/**
	 * Deletes a function by specific key from an action hook
	 * @param $hook
	 * @param $function
	 */
	public function delete_action_by_key( $key, $hook = false )
	{
		$actions = $this->get_hooks( false );
		
		if ( $hook && !isset( $actions[$hook] ) )
		{
			return;
		}
	
		foreach ( $actions as $hook_name => $action_content )
		{
			if ( !$hook || $hook == $hook_name )
			{
				foreach ( $action_content as $action_key => $action )
				{
					if ( $key == $action['key'] )
					{
						unset( $actions[$hook_name][$action_key] );
					}
				}
			}
		}
		
		return $this->update_actions_helper( $actions, $hook );
	}
	
	/**
	 * Helper fo update hooks
	 * @param $hook
	 * @param $function
	 */
	public function update_actions_helper( $actions, $hook )
	{
		if ( !empty( $actions[$hook] ) )
		{
			array_sort_by_column( $actions[$hook], 'priority', SORT_ASC );
		}
		else
		{
			unset( $actions[$hook] );
		}

		Config::update( 'hooks_actions_registry', serialize( $actions ) );
	}
	/**
	 * Ads a function to an action hook
	 * @param $hook
	 * @param $function
	 */
	public function add_action( $hook, $function, $params = null, $priority = 1, $conditions = null )
	{
		$hook = mb_strtolower( $hook );

		/* Create an array of function handlers if it doesn't already exist */
		if ( !$this->exists_hook( $hook ) )
		{
			$this->actions[$hook] = array();
		}
		if ( $conditions )
		{
			if ( !$this->check( $conditions ) )
			{
				return false;
			}
		}
		/* Append the current function to the list of function handlers */
		/* if ( is_callable( $function ) )
		{
			$this->actions[$hook][] = array(
				$function => ( is_array( $params ) ? $params : null ),
				'priority' => (float) $priority 
			);
			
			return true;
		} */
		if ( is_callable( $function ) || is_array( $function ) )
		{
			$this->actions[$hook][] = array(
				'function' => $function,
				'params' => ( is_array( $params ) ? $params : null ),
				'priority' => (float) $priority 
			);
			
			return true;
		}
		return false;
	}
	
	/**
	 * Removes a function from an action hook
	 * @param $hook
	 * @param $function
	 * @param $deep
	 */
	public function remove_action( $hook, $function, $function_callable = false )
	{
		$hook = mb_strtolower( $hook );
		
		/* Check if an array of function handler already exist */
		if ( !$this->exists_hook( $hook ) || !is_array( $this->actions[$hook] ) )
		{
			return false;
		}
		
		foreach ( $this->actions[$hook] as $hook_key => $hook_content )
		{
			//if ( is_array( $hook_content ) && key( $hook_content ) == $function )
			if ( is_array( $hook_content ) && $hook_content['function'] == $function )
			{
				if( $function_callable )
				{
					if( !is_array( $this->actions[$hook][$hook_key] ) || !in_array( $this->actions[$hook][$hook_key], $function_callable ) )
					{
						continue;
					}
				}
				unset( $this->actions[$hook][$hook_key] );
			}
		}
		
		return true;
	}
	
	/**
	 * Checks action conditions like only_lodded
	 * @param $hook
	 */
	public function check( $conditions )
	{
		if ( isset( $conditions['only_logged'] ) && !USER_LOGGED )
		{
			return false;
		}
		
		if ( isset( $conditions['ips_version'] ) && IPS_VERSION != $conditions['ips_version'] )
		{
			return false;
		}
		
		return true;
	}
	
	/**
	 * Removes whole hook
	 * @param $hook
	 */
	public function remove_hook( $hook )
	{
		if ( is_array( $hook ) )
		{
			return array_map( array(
				$this,
				'remove_hook' 
			), $hook );
		}
		
		$hook = mb_strtolower( $hook );
		
		/* Check if an array of function handler already exist */
		if ( !$this->exists_hook( $hook ) )
		{
			return false;
		}
		
		$this->actions[$hook] = array();
	
		return true;
	}
	/**
	 * Sorts by priority
	 * @param string $a
	 * @param string $b
	 * @return int 
	 */
	public function sort( $a, $b )
	{
		return $a['priority'] > $b['priority'];
	}
	/**
	 * Executes the functions for the given hook
	 * @param string $hook
	 * @param array $params
	 * @return boolean true if a hook was setted
	 */
	public function do_hook( $hook, $params = null )
	{
		$hook = mb_strtolower( $hook );
		
		if ( isset( $this->actions[$hook] ) )
		{
			usort( $this->actions[$hook], array(
				$this, 'sort' 
			) );
			
			ob_start();
			
			/* Call each function handler associated with this hook and display data */
			foreach ( $this->actions[$hook] as $call )
			{
				if ( is_array( $call['params'] ) )
				{
					echo call_user_func_array( $call['function'], $call['params'] );
				}
				elseif ( is_array( $params ) )
				{
					echo call_user_func_array( $call['function'], $params );
				}
				else
				{
					echo call_user_func( $call['function'], $params );
				}
				
				/* if ( is_array( current( $function ) ) )
				{
					echo call_user_func_array( key( $function ), current( $function ) );
				}
				elseif ( is_array( $params ) )
				{
					echo call_user_func_array( key( $function ), $params );
				}
				else
				{
					echo call_user_func( key( $function ) );
				} */
				/* Cant return anything since we are in a loop */
			}
			
			$get_contents = ob_get_contents();
			ob_end_clean();
			
			return $get_contents;
		}
	}
	
	/**
	 * Gets the functions for the given hook
	 * @param string $hook
	 * @return mixed 
	 */
	public function get_hook( $hook )
	{
		$hook = mb_strtolower( $hook );
		return ( isset( $this->actions[$hook] ) ) ? $this->actions[$hook] : false;
	}
	
	/**
	 * Check exists the functions for the given hook
	 * @param string $hook
	 * @return boolean 
	 */
	public function exists_hook( $hook )
	{
		$hook = mb_strtolower( $hook );
		return ( isset( $this->actions[$hook] ) ) ? true : false;
	}
	
	/**
	 * Create new action from admin panel
	 * @param array $data
	 * @return boolean 
	 */
	public function create_action( array $data )
	{
		if ( isset( $data['user_action'] ) )
		{
			if ( !isset( $data['content'] ) || empty( $data['content'] ) || !isset( $data['title'] ) || empty( $data['title'] ) )
			{
				throw new Exception('fill_in_required_fields');
			}
			
			preg_match_all( '/\[include="([^"]*)"\]/', $data['content'], $code );
			
			if( !empty( $code[1] ) )
			{
				foreach( $code[1] as $file )
				{
					$inlude = ABS_PATH . '/' . trim( $file, '/' );
					
					if( !file_exists( $inlude ) )
					{
						throw new Exception( __s( 'hook_include_error', $file ) );
					}
				}
			}
			
			$data['function'] = 'App::block';
			$data['params']   = array(
				'title' => $data['title'],
				'content' => $data['content'],
				'has_include' => !empty( $code[1] ),
				'visibility' => ( isset( $data['visibility'] ) ? $data['visibility'] : 1 ) 
			);
		}
		elseif ( isset( $data['key'] ) )
		{
			$data_before = $this->find_action( $data['key'] );
			
			if ( $data_before == false )
			{
				throw new Exception( 'hook_not_exists' );
			}
			
			/** Update only if priority changed - ajax calls */
			if ( isset( $data['hook'] ) && $data_before['hook'] == $data['hook'] )
			{
				if ( $data_before['priority'] == $data['priority'] || ( (float) $data_before['priority'] == (float) $data['priority'] - 0.1 ) )
				{
					return true;
				}
			}
			
			$data             = array_merge( $data_before, $data );
			$data['priority'] = (float) $data['priority'] - 0.1;
		}
		else
		{
			throw new Exception('fill_in_required_fields');
		}
		
		$priority = ( isset( $data['priority'] ) && !empty( $data['priority'] ) ? $data['priority'] : 1 );
		
		if ( isset( $data['key'] ) )
		{
			$this->update_action( $data['key'], $data['hook'], $data['function'], $data['params'], $priority );
		}
		else
		{
			$this->register_action( $data['hook'], $data['function'], $data['params'], $priority );
		}
		
		return true;
	}
	/**
	 * Get all Hooks from DB
	 * @param 
	 * 
	 * @return 
	 */
	public function get_hooks( $cached = true )
	{
		$hooks = $cached ? Config::getArray( 'hooks_actions_registry' ) : Config::noCache( 'hooks_actions_registry' );
		
		if ( is_array( $hooks ) )
		{
			return $hooks;
		}
		
		return array();
	}
	
	/**
	 * Diff prority that already is taken by another action
	 * @param $hook
	 * @param $priority_list
	 * 
	 * @return null
	 */
	public function fill_priority( $hook, &$priority_list )
	{
		$actions = $this->get_hook( $hook );
		if ( !empty( $actions ) && is_array( $actions ) )
		{
			foreach ( $actions as $action )
			{
				if ( isset( $action['priority'] ) )
				{
					$key = array_search( ceil( $action['priority'] ), $priority_list );
					if ( $key )
					{
						unset( $priority_list[$key] );
					}
				}
			}
		}
	}
	
	/**
	 * Find action by key
	 * @param array $data
	 * @return boolean 
	 */
	public function find_action( $key = false, $hook = false )
	{
		if ( !$key )
		{
			return false;
		}
		
		$actions = $this->get_hooks( false );
	
		if ( $hook && !isset( $actions[$hook] ) )
		{
			return false;
		}
		
		foreach ( $actions as $hook_name => $action_content )
		{
			if ( !$hook || $hook == $hook_name )
			{
				foreach ( $action_content as $action_key => $action )
				{
					if ( $key == $action['key'] )
					{
						return $action;
					}
				}
			}
		}
		
		return false;
	}
	
		/**
	 * Hook a function or method to a specific filter action.
	 *
	 * IPS-CMS offers filter hooks to allow plugins to modify
	 * various types of internal data at runtime.
	 *
	 *
	 * @param string   $tag             The name of the filter to hook the $function_to_add callback to.
	 * @param callback $function_to_add The callback to be run when the filter is applied.
	 * @param int      $priority        Optional. Used to specify the order in which the functions
	 *                                  associated with a particular action are executed. Default 10.
	 *                                  
	 * @return boolean true
	 */
	public function add_filter( $tag, $function_to_add, $priority )
	{
		$this->filters[$tag][$priority][ $this->filter_unique_id( $tag, $function_to_add, $priority ) ] = array(
			'function' => $function_to_add
		);
		
		if ( isset( $this->merged_filters[ $tag ] ) )
		{
			unset( $this->merged_filters[ $tag ] );
		}
		
		return true;
	}
	/**
	 * Removes a function from a specified filter hook.
	 *
	 * This function removes a function attached to a specified filter hook. 
	 *
	 * To remove a hook, the $function_to_remove argument must match
	 * when the hook was added. 
	 *
	 * @param string   $tag                The filter hook to which the function to be removed is hooked.
	 * @param callback $function_to_remove The name of the function which should be removed.
	 * @return boolean Whether the function existed before it was removed.
	 */
	public function remove_filter( $tag, $function_to_remove, $priority )
	{
		$function_to_remove = $this->filter_unique_id( $tag, $function_to_remove, $priority );

		$r = isset( $this->filters[$tag][$priority][$function_to_remove] );

		if ( true === $r )
		{
			unset( $this->filters[$tag][$priority][$function_to_remove] );
			
			if ( empty( $this->filters[$tag][$priority] ) )
			{
				unset( $this->filters[$tag][$priority] );
			}
			
			if ( isset( $this->merged_filters[ $tag ] ) )
			{
				unset( $this->merged_filters[ $tag ] );
			}
		}
		
		return $r;
    }
	/**
	 * Remove all of the hooks from a filter.
	 *
	 * @param string   $tag      The filter to remove hooks from.
	 * @param int|bool $priority Optional. The priority number to remove. Default false.
	 * @return bool True when finished.
	 */
	function remove_all_filters( $tag, $priority = false ) {
	
		if ( isset( $this->filters[ $tag ] ) ) {
			if ( false !== $priority && isset( $this->filters[ $tag ][ $priority ] ) ) {
				$this->filters[ $tag ][ $priority ] = array();
			} else {
				$this->filters[ $tag ] = array();
			}
		}

		if ( isset( $this->merged_filters[ $tag ] ) )
		{
			unset( $this->merged_filters[ $tag ] );
		}

		return true;
	}
	/**
	 * Call the functions added to a filter hook.
	 *
	 * The callback functions attached to filter hook $tag are invoked by calling
	 * this function. 
	 *
	 * The function allows for additional arguments to be added and passed to hooks.
	 *
	 * @param string $tag   The name of the filter hook.
	 * @param mixed  $value The value on which the filters hooked to `$tag` are applied on.
	 * @param mixed  $var   Additional variables passed to the functions hooked to `$tag`.
	 * @return mixed The filtered value after all hooked functions are applied to it.
	 */
	public function apply_filters( $tag, $args )
	{

		if ( !isset($this->filters[$tag]) )
		{
			return $args[0];
		}

		// Sort
		if ( !isset( $this->merged_filters[ $tag ] ) )
		{
			ksort( $this->filters[$tag] );
			$this->merged_filters[ $tag ] = true;
		}

		reset( $this->filters[ $tag ] );

		do {
			foreach( (array)current( $this->filters[$tag] ) as $the_ )
			{
				if ( !is_null( $the_['function'] ) )
				{
					$args[0] = call_user_func_array( $the_['function'], $args );
				}
			}

		} while ( next( $this->filters[$tag] ) !== false );

		return $args[0];
    }
	/**
	 * Build Unique ID for storage and retrieval.
	 *
	 * Functions and static method callbacks are just returned as strings and
	 * shouldn't have any speed penalty.
	 *
	 * @param string   $tag      Used in counting how many hooks were applied
	 * @param callback $function Used for creating unique id
	 * @param int|bool $priority Used in counting how many hooks were applied.
	 * @return string|bool Unique ID for usage as array key or false if $priority === false
	 *                     and $function is an object reference, and it does not already have
	 *                     a unique id.
	 */
	public function filter_unique_id( $tag, $function, $priority )
	{
		static $filter_id_count = 0;

		if ( is_string( $function ) )
		{
			return $function;
		}

		if ( is_object( $function ) ) 
		{
			// Closures are currently implemented as objects
			$function = array( $function, '' );
		}
		else
		{
			$function = (array) $function;
		}

		if ( is_object( $function[0]) )
		{
			// Object Class Calling
			if ( function_exists('spl_object_hash') )
			{
				return spl_object_hash( $function[0] ) . $function[1];
			}
			else
			{
				$obj_idx = get_class( $function[0] ) . $function[1];
				
				if ( !isset( $function[0]->filter_id ) )
				{
					if ( false === $priority )
					{
						return false;
					}
					$obj_idx .= isset( $this->filters[$tag][$priority] ) ? count((array)$this->filters[$tag][$priority]) : $filter_id_count;
					$function[0]->filter_id = $filter_id_count;
					++$filter_id_count;
				}
				else
				{
					$obj_idx .= $function[0]->filter_id;
				}

				return $obj_idx;
			}
		}
		else if ( is_string( $function[0] ) )
		{
			// Static Calling
			return $function[0] . $function[1];
		}
	}
}