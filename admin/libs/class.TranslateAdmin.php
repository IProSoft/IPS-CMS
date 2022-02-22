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

class Translate_Admin extends Translate
{
	/**
	 * Add translates column to specified table
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function addColumns( $table, $field, $field_length = 128 )
	{
		$languages = array_map('strtolower', Translate::codes());
		
		$prefix = $field . '_';
		
		$columns = PD::getInstance()->select( $table, false, 1 );
		
		if( !empty( $columns ) )
		{
			foreach( $columns as $column => $v )
			{
				if( strpos( $column, $prefix ) !== false )
				{
					if( !in_array( str_replace( $prefix, '', $column ) , $languages ) )
					{
						PD::getInstance()->PDQuery('ALTER TABLE ' . db_prefix( $table ) . ' DROP COLUMN ' . $column );
					}
				}
			}
			
			if( count( $languages ) )
			{
				foreach( $languages as $lang )
				{
					$column = $prefix . strtolower( $lang );
					
					if( !isset( $columns[ $column ] ) )
					{
						PD::getInstance()->PDQuery("ALTER TABLE `" . db_prefix( $table ) . "` ADD `" . $column . "` VARCHAR( " . $field_length . " ) NOT NULL DEFAULT '' AFTER `" . $field . "`");
					}
				}
			}
		}
	}
	/**
	 * Form fields to edit translated columns
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function langColumns( $column, $field_info, $row )
	{
		$columns_form = array();
		$columns_form[$column] = $field_info;
		$columns_form[$column]['current_value'] = ( isset( $row[ $column ] ) ? $row[ $column ] : '' );
		
		$languages = Translate::codes();
		
		if( count( $languages ) > 1 )
		{
			foreach( $languages as $lang )
			{
				$column_f = $column . '_' . strtolower( $lang );
				
				$columns_form[$column_f] = $field_info;
				$columns_form[$column_f]['current_value'] = ( isset( $row[ $column_f ] ) ? $row[ $column_f ] : '' );
				$columns_form[$column_f]['option_set_text'] = __( $field_info['option_set_text'] ) . ' ( ' . $lang . ' )';
			}
		}
		
		return $columns_form;
		
	}
	
	/**
	 * Update language column
	 *
	 * @param null
	 * 
	 * @return void
	 */
	public function updateColumn( $table, $data, $column_prefix, $conditions )
	{
		$languages = array_map('strtolower', Translate::codes());
		
		if( count( $languages ) > 1 )
		{
			$update = array();
			
			foreach( $languages as $lang )
			{
				$column = $column_prefix . '_' . strtolower( $lang );

				if( isset( $data[$column]) )
				{
					$update[$column] = $data[$column];
				}
			}
			
			if( !empty( $update ) )
			{
				return PD::getInstance()->update( $table, $update, $conditions );
			}
		}
	}
}
?>