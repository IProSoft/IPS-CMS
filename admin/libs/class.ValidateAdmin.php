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
class Validate_Admin
{	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public function getValidations()
	{
		$filemtime = filemtime( IPS_ADMIN_PATH . '/admin-options.php' );
		
		if( $filemtime && $cache = Ips_Cache::getDBCache( 'admin_validate_options', 'admin', true ) )
		{
			if( strtotime( $cache['cache_stored'] ) > $filemtime && !empty( $cache['cache_data'] ) )
			{	
				return unserialize( $cache['cache_data'] );
			}
		}
		
		$validate = array();
		
		foreach( getOptionsFile() as $k => $v )
		{
			foreach( $v as $option_key => $option )
			{
				$validate = $this->extractValidations( $validate, $option_key, $option );
			}
		}
		
		Ips_Cache::storeDBCache( 'admin_validate_options', $validate, 'admin' );
		
		return $validate;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public function extractValidations( $validate, $option_key, $option )
	{
		if( is_array( $option ) )
		{
			if( isset( $option['option_is_array'] ) )
			{
				if( is_array( $option_key ) )
				{
					foreach( $option['option_is_array'] as $k => $o )
					{
						$validate = $this->extractValidations( $validate, array(
							key($option_key) .'[' . current($option_key) . ']' => $k
						), $o );
					}
				}
				else
				{
					foreach( $option['option_is_array'] as $k => $o )
					{
						if( is_numeric( $k ) )
						{
							$k = $o;
						}
						$validate = $this->extractValidations( $validate, array(
							$option_key => $k
						), $o );
					}
				}
				return $validate;
			}
			
			if( isset( $option['opt_suboptions'] ) )
			{
				foreach( $option['opt_suboptions'] as $k => $o )
				{
					$validate = $this->extractValidations( $validate, $k, $o );
				}
				return $validate;
			}
			
			if( isset( $option['validation'] ) )
			{
				$validate[] = array(
					'option' => $option_key,
					'conditions' => $option['validation']
				);
			}
			elseif( isset( $option['option_type'] ) && $option['option_type'] == 'range' )
			{
				$validate[] = array(
					'option' => $option_key,
					'conditions' => array(
						'range_error' => true,
						'option_min' => $option['option_min'],
						'option_max' => $option['option_max']
					)
				);
			}
		}
		
		return $validate;
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public function validate( &$_post )
	{
		
		$messages = array();
		
		foreach( $this->getValidations() as $k => $validation )
		{
			
			$name = is_array( $validation['option'] ) ? key( $validation['option'] ): $validation['option'];
			
			if( isset( $_post[ $name ] ) )
			{
				$post_value = $_post[ $name ];
				
				if( is_array( $validation['option'] ) )
				{
					$post_value = has_value( current( $validation['option'] ), $post_value, null );
				}
				
				if( $post_value != null )
				{
					$status = $this->check( $post_value, $validation['conditions'] );
					
					if( $status !== true )
					{
						$messages[] = $this->getMessage( $validation['option'], $validation['conditions'] );
						
						$this->setValue( $_post, $validation['option'], $validation['conditions'] );
					}
				}
			}
		}

		return $messages;
	}
	
	/**
	 * Set/Unset $_POST values
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function setValue( &$_post, $option, $conditions )
	{
		$value = isset( $conditions['set_value'] ) ? $conditions['set_value'] : false;
		
		if( is_array( $option ) )
		{
			if( $value )
			{
				return $_post[ key( $option ) ][ current( $option ) ] = $value;
			}
			
			unset( $_post[ key( $option ) ][ current( $option ) ] );
		}
		else
		{
			if( $value )
			{
				return $_post[ $option ] = $value;
			};
			
			unset( $_post[ $option ] );
		}
	}
	/**
	 * Get error message
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getMessage( $option, $conditions )
	{
		if( isset( $conditions['range_error'] ) )
		{
			$msg = ( is_array( $option ) ? key( $option ) .'_' . current( $option ) : $option ) . '_title';
			
			if( __( $msg ) == $msg )
			{
				return;
			}
			
			return __s( 'range_field_value_error', __( $msg ) );
		}
		
		if( !isset( $conditions['msg'] ) )
		{
			$conditions['msg'] = ( is_array( $option ) ? key( $option ) .'_' . current( $option ) : $option ) .'_error';
		}

		return __( $conditions['msg'] );
	}
	/**
	 * Check validation conditions
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function check( $value, $conditions )
	{
		if( !is_array( $conditions ) )
		{
			return (bool)preg_match('~' . $conditions . '~i', $value );
		}
		elseif( isset( $conditions['function'] ) )
		{
			return (bool)call_user_func( $conditions['function'], $value );
		}
		elseif( isset( $conditions['match'] ) )
		{
			return (bool)preg_match('~' . $conditions['match'] . '~i', $value );
		}
		elseif( isset( $conditions['range_error'] ) )
		{
			return $value >= $conditions['option_min'] && $value <= $conditions['option_max'] ? true : false;
		}

		return true;
	}
}
?>