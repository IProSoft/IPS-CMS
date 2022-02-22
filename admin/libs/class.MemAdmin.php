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

class Mem_Admin extends Mem
{
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function updateTranslations()
	{
		Ips_Registry::get( 'Translate_Admin' )->addColumns( 'mem_generator', 'mem_title', 128 );
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function addCategory( $data )
	{
		if( !isset( $data['category_text'] ) || empty( $data['category_text'] ) )
		{
			return ips_message( array(
				'alert' => 'category_name_error'
			), true );
		}

		$category = array( 
			'category_text' => $data['category_text'], 
			'category_description' => $data['category_description'], 
			'rewrite_text' => strtolower( str_replace( '.html', '', seoLink( false, $data['category_text'] ) ) )
		);
		
		if( isset( $data['category_id'] ) )
		{
			ips_message( array(
				'normal' =>  __('category_updated')
			) );
			
			PD::getInstance()->update( 'mem_generator_categories', $category, array( 
				'id' => $data['category_id']
			) );
		}
		else
		{
			$exists = PD::getInstance()->cnt( 'mem_generator_categories', array( 
				'category_text' => $category['category_text']
			));
			
			if( $exists )
			{
				return ips_message( array(
					'alert' => 'category_exists_error'
				), true );
			}
			
			PD::getInstance()->insert( 'mem_generator_categories', $category );
			
			ips_message( array(
				'normal' =>  __('category_added')
			) );
		}
		
		return ips_admin_redirect( 'generator', 'mem-action=categories');
	}
	
	
	public function categoryForm( $row = array() )
	{
		
		$form = '
		<form action="" enctype="multipart/form-data" method="post">
			<div  class="features-table-actions-div">
				' . 
				displayOptionField( 'category_text', array(
					'current_value' => ( isset( $row['category_text'] ) ? $row['category_text'] : '' ),
					'option_set_text' => 'category_name',
					'option_type' => 'input',
					'option_lenght' => 10
				)). 
				displayOptionField( 'category_description', array(
					'current_value' => ( isset( $row['category_description'] ) ? $row['category_description'] : '' ),
					'option_set_text' => 'category_description',
					'option_type' => 'textarea'
				));
				
			if( isset( $row['id'] ) )
			{
				$form .= '<input type="hidden" name="category_id" value="'.$row['id'].'" />';
			}
			
			$form .= '
			</div>
			<input type="submit" name="category_form" class="button" value="' . __('save') . '" />
			<a role="button" class="button" href="' . admin_url( 'generator' ) . '">' . __('common_back') . '</a>
		</form>';
		
		return $form;
	}
	
	/**
	 * Add generator image
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function generatorForm( $id = false )
	{
		
		$row = $id ? PD::getInstance()->select( 'mem_generator', array(
			'id' => $id
		), 1) : array() ;
		
		$options = array(
			'mem_image' => array(
				'option_value' => '<input type="file" name="mem_image" /><input value="'.( isset( $row['mem_image'] ) ? $row['mem_image'] : '' ).'" type="hidden" name="mem_image">',
				'option_set_text' => 'generators_image',
				'option_type' => 'text',
			),
			'mem_category' => array(
				'current_value' => ( isset( $row['mem_category'] ) ? $row['mem_category'] : false ),
				'option_set_text' => 'generators_category',
				'option_select_values' => array_column( $this->getCategories(), 'category_text', 'id')
			),
			'mem_activ' => array(
				'current_value' => 1,
				'option_names' => 'yes_no'
			)
		);
		
		$options = array_merge( Ips_Registry::get( 'Translate_Admin' )->langColumns( 'mem_title', array(
			'option_set_text' => 'generators_title',
			'option_type' => 'input',
			'option_lenght' => 10
		), $row ), $options );
		
		
		$form = '
			<form action="" enctype="multipart/form-data" method="post">
			
			' . displayArrayOptions( $options );
		
			if( isset( $row['id'] ) )
			{
				$form .= '<input type="hidden" name="category_id" value="'.$row['id'].'" />';
			}

			$form .= '
			<input type="submit" name="category_form" class="button" value="' . __('save') . '" />
			<a role="button" class="button" href="' . admin_url( 'generator' ) . '">' . __('common_back') . '</a>
			</form>';
			
			return $form;
	}
	
	/**
	 * Get category
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getCategory( $id, $return_text = true )
	{
		$mem_categories = $this->getCategories();
		
		foreach( $mem_categories as $key => $category )
		{
			if( $category['id'] == $id )
			{
				if( $return_text )
				{
					return $category['category_text'];
				}
				return $category;
			}
		}

		return __('generators_list_empty_category');
		
	}
	/**
	 * Delete mem image
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function delete( $id )
	{
		$row = PD::getInstance()->select( 'mem_generator', array(
			'id' => $id
		), 1 );
		
		if( !empty( $row ) )
		{
			PD::getInstance()->delete( 'mem_generator', array( 
				'id' => $id
			), 1 );
			
			File::deleteFile( ABS_PATH . '/upload/upload_mem/' . $row['mem_image'] );
			File::deleteFile( ABS_PATH . '/upload/upload_mem/thumbs/' . $row['mem_image'] );
		}
		
	}
	
	/**
	 * Update generator in database
	 *
	 * @param null
	 * 
	 * @return void
	 */
	public function updateLanguage( $generator_id, $data )
	{
		Ips_Registry::get( 'Translate_Admin' )->updateColumn( 'mem_generator', $data, 'mem_title', array( 
			'id' => $generator_id
		) );
	}
	
	/**
	 * Delete mem category
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function deleteCategory( $id )
	{
		$row = PD::getInstance()->select( 'mem_generator', array(
			'id' => $id
		), 1 );
		
		if( !empty( $row ) )
		{
			PD::getInstance()->delete( 'mem_generator', array( 
				'id' => $id
			), 1);
			
			File::deleteFile( ABS_PATH . '/upload/upload_mem/' . $row['mem_image'] );
			File::deleteFile( ABS_PATH . '/upload/upload_mem/thumbs/' . $row['mem_image'] );
		}
		
	}
}
?>