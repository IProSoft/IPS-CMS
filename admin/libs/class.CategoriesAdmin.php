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

class Categories_Admin extends Categories
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
		Ips_Registry::get( 'Translate_Admin' )->addColumns( 'upload_categories', 'category_name', 255 );
	}
	/**
	 * Check if all categories has thumb for each language
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function updateThumbs()
	{
		$categories = Categories::getCategories();
		
		if( !empty( $categories ) )
		{
			foreach( $categories as $category )
			{
				$this->updateThumb( $category['category_image'] );
			}
		}
	}
	
	
	
	/**
	 * Update category data
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function category( $data )
	{
		if( !has_value( 'category_name', $data ) )
		{
			return 'category_name_error';
		}
		
		$id_category = has_value( 'id_category', $data, 'false' );
			
		if( !empty( $_FILES["file"]["tmp_name"] ) )
		{
			if( $id_category != 'false' )
			{
				$row = Categories::getCategories( $id_category );
				File::deleteFiles( ABS_PATH . '/upload/category_images', true, false, $row['category_image'] );
			}
			
			$file_name = uploadAdminImage( $this->thumbName( $data['category_name'] ), array(
				'resize' => Config::get( 'categories_options', 'thumb_size' )
			), ABS_PATH . '/upload/category_images' );
		}
		elseif( !isset( $data['category_image'] ) || empty( $data['category_image'] ) )
		{
			return 'category_image_error';
		}

		/** Remove current default category */
		if( isset( $data['is_default_category'] ) && $data['is_default_category'] == 1 )
		{
			PD::getInstance()->update( 'upload_categories', array(
				'is_default_category' => 0 
			), array(
				'is_default_category' => 1 
			));
		}

		$update = array(
			'category_name'			=> $data['category_name'],
			'is_default_category' 	=> has_value( 'is_default_category', $data, 0 ),
			'only_premium' 			=> has_value( 'only_premium', $data, 0 ),
			'only_adult' 			=> has_value( 'only_adult', $data, 0 ),
			'only_logged_in' 		=> has_value( 'only_logged_in', $data, 0 ),
			'category_image' 		=> has_value( 'category_image', $data, '' )
		);
		
		if( $id_category == 'false' )
		{
			$id_category = PD::getInstance()->insert( 'upload_categories', $update );
		}
		else
		{
			PD::getInstance()->update( 'upload_categories', $update, array( 
				'id_category' => $id_category
			));
		}
		
		Ips_Registry::get( 'Translate_Admin' )->updateColumn( 'upload_categories', $data, 'category_name', array( 
			'id_category' => $id_category
		) );
		
		if( isset( $file_name ) )
		{
			$this->updateThumb( $file_name );
			
			PD::getInstance()->update( 'upload_categories', array( 
				'category_image' => $file_name
			), array( 
				'id_category' => $id_category
			));
		}
		
		if( !function_exists( 'clearCache' ) )
		{
			require_once( ABS_PATH .'/admin/admin-functions.php' );
		}
		clearCache( 'tpl' );
		
		return true;
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function defaultThumb( $category_name )
	{
		$file_name = $this->thumbName( $category_name ) . '.png';

		$up = new Upload_Single_File;
		$img = $up->simpleResizeImage( imagecreatefrompng( ABS_PATH . '/images/no-image.png' ), Config::get( 'categories_options', 'thumb_size' ) );
		
		imagepng( $img, ABS_PATH . '/upload/category_images/' . $file_name );
		
		$this->updateThumb( $file_name );
		
		return $file_name;
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function updateThumb( $category_image )
	{
		$languages = array_map('strtolower', Translate::codes());
		
		foreach( $languages as $lang )
		{
			$file_name = $lang . '_' . $category_image;
			
			if( !file_exists( ABS_PATH . '/upload/category_images/' . $file_name ) )
			{
				copy( ABS_PATH . '/upload/category_images/' . $category_image, ABS_PATH . '/upload/category_images/' . $file_name);
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
	public function changeFileCategory( $id, $change_to )
	{
		$row = Categories::getCategories( (int)$change_to );
		
		if( !empty( $row ) )
		{
			PD::getInstance()->update( IPS__FILES, array( 
				'category_id' => $row['id_category']
			), array( 
				'category_id' => (int)$id
			), false );
			
			return true;
		}
		else
		{
			return false;
		}
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function delete( $ids, $removeFiles = false )
	{
		
		if( is_array( $ids ) )
		{
			foreach( $ids as $id )
			{
				$this->delete( $id, $removeFiles );
			}
			
			return;
		}
		
		Widgets::widgetCachedClear( 'categoryPanel' );
		
		if( is_numeric( $ids ) )
		{
			if( PD::getInstance()->delete( 'upload_categories', array( 'id_category' => (int)$ids )) )
			{
				PD::getInstance()->delete( 'translations', "translation_name = 'category_text_" . (int)$ids . "'");
				
				if( $removeFiles == 'true' )
				{
					$row = PD::getInstance()->select( IPS__FILES, array(
						'category_id' => (int)$ids
					));
					
					$opts = new Operations;
					
					foreach( $row as $r )
					{
						$opts->move( $r['id'], 'delete' );
					}
				}
				else
				{
					PD::getInstance()->update( IPS__FILES, array( 
						'category_id' => Categories::defaultCategory()
					), array( 
						'category_id' => (int)$ids
					));
				}
				
				return true;
			}
		}
		
		return false;
	}
}