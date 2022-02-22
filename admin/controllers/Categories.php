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
	
	
	
class Categories extends AdminController implements InterfaceAdminController
{
	/**
	*
	*/
	public $action = 'preview';
	/**
	*
	*/
	public $db_key = 'id';
	
	/**
	 * Constructor
	 *
	 * @param null
	 * 
	 * @return void
	 */
	public function __contruct()
	{
		Ips_Registry::get( 'Categories_Admin' )->updateTranslations();
	}
	
	/**
	 * Initialize class variables and check conditions
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function init( $params, $post )
	{
		if( $this->action() != 'settings' && Config::get('categories_option') == 0 )
		{
			return ips_admin_redirect( 'categories', 'category_action=settings' );
		}
	}
	

}
	
	
	
	if( !defined('USER_ADMIN') || !USER_ADMIN ) die ("Hakier?");
	
	Ips_Registry::get( 'Categories_Admin' )->updateTranslations();
		
	$category_action = isset( $_GET['category_action'] ) ? $_GET['category_action'] : 'preview';
	
	if( $category_action != 'settings' && Config::get('categories_option') == 0 )
	{
		return ips_admin_redirect( 'categories', 'category_action=settings' );
	}
	
	if( !empty( $_POST['ids'] ) || ( $category_action == 'delete' && isset( $_GET['id_category'] ) ) )
	{
	
		if( Ips_Registry::get( 'Categories_Admin' )->delete( $_GET['id_category'] ) )
		{
			ips_message( array(
				'info' =>  __('category_removed')
			) );
		}
		return ips_admin_redirect('categories');
	}
	
	if( isset( $_POST['id_category'] ) )
	{
		Widgets::widgetCachedClear( 'categoryPanel' );
		
		if( isset( $_POST['to_id_category'] ) )
		{
			if( Ips_Registry::get( 'Categories_Admin' )->changeFileCategory( $_POST['id_category'], $_POST['to_id_category'] ) )
			{
				return ips_admin_redirect( 'categories',false,  __( 'files_moved' ) );
			}
			
			return ips_admin_redirect( 'categories', 'category_action=move&id=' . $_POST['id_category'],  __( 'category_not_exists' ) );
		}
		
		if( !empty( $_POST['category_name'] ) )
		{
			$info = Ips_Registry::get( 'Categories_Admin' )->category( $_POST );
			
			if( $info === true )
			{
				return ips_admin_redirect( 'categories', false, __('category_saved') );
			}
			
			return ips_admin_redirect( 'categories', 'category_action=' . $category_action, $info );
		}
		else
		{
			echo admin_msg( array(
				'alert' => __('category_name_field_is_required') 
			) );	
		}
	}

	
	updateCategoryOption();
	updateMenu();
	

	echo admin_caption( 'caption_categories' );
	
	echo responsive_menu( array(
		'browse' => 'browse',
		'add' => 'admin_add_category',
		'files' => 'browse_files',
		'settings' => 'settings',
	), admin_url( 'categories', 'category_action=' ) ) ;
	
	
	if( $category_action == 'settings' )
	{
		echo '
			<form action="admin-save.php" enctype="multipart/form-data" method="post">
				<!-- Categories -->
				' . displayArrayOptions( getOptionsFile()['options_categories'] ) . '
				<!-- End Categories -->
				<input name="categories" type="submit" class="button" value="' . __('save') . '" />
			</form>
		';
		Session::set( 'admin_redirect', admin_url( 'categories', 'category_action=settings' ) );
		
	}
	elseif(  $category_action == 'add' ||  $category_action == 'edit' )
	{

		$row = array();
			if( isset( $_GET['id_category']) )
			{
				$row = Categories::getCategories( (int)$_GET['id_category'] );
			}
			
			$options = array(
				'category_name' => array(
					'current_value' => ( isset( $row['category_name'] ) ? $row['category_name'] : '' ),
					'option_set_text' => 'category_name',
					'option_type' => 'input'
				),
				'only_premium' => array(
					'current_value' => ( isset( $row['only_premium'] ) && $row['only_premium'] == 1 ? 1 : 0 ),
					'option_set_text' => 'premium_category',
					'option_names' => 'yes_no',
					'opt_display' => ( Config::get('services_premium') && Config::getArray( 'services_premium_options', 'category' ) )
				),
				'only_adult' => array(
					'current_value' => ( isset($row['only_adult']) && $row['only_adult'] == 1 ? 1 : 0 ),
					'option_set_text' => 'categories_only_adult',
					'option_names' => 'yes_no'
				),
				'only_logged_in' => array(
					'current_value' => ( isset($row['only_logged_in']) && $row['only_logged_in'] == 1 ? 1 : 0 ),
					'option_set_text' => 'category_only_logged',
					'option_names' => 'yes_no'
				),
				'is_default_category' => array(
					'current_value' => ( !isset( $row['is_default_category'] ) || $row['is_default_category'] == 0 ? 0 : 1 ),
					'option_set_text' => 'default_category',
					'option_names' => 'yes_no'
				),
				'thumb' => array(
					'current_value' => ( !isset( $row['is_default_category'] ) || $row['is_default_category'] == 0 ? 0 : 1 ),
					'option_set_text' => 'categories_thumb',
					'option_type' => 'text',
					'option_value' => '<input id="file" type="file" name="file" value="" />'
				)
			);

			if( isset( $row['category_image'] ) && !empty( $row['category_image'] ) )
			{
				$options['thumb']['option_set_text'] = __( 'categories_thumb' ) 
				. '<span class="category_image"><img src="' . ABS_URL . '/upload/category_images/' . $row['category_image'] . '" /></span>';
			}
			
			
			$options = array_merge( Ips_Registry::get( 'Translate_Admin' )->langColumns( 'category_name', array(
				'option_set_text' => 'category_name',
				'option_type' => 'input',
				'option_lenght' => 10
			), $row ), $options );
				
			echo '
			<form enctype="multipart/form-data" action="" method="post">
			' . displayArrayOptions( $options ) . '
			<input type="hidden" name="id_category" value="'.( isset( $row['id_category'] ) ? $row['id_category'] : 'false' ) . '" />
			<input id="category_image" type="hidden" name="category_image" value="'.( isset( $row['category_image'] ) ? $row['category_image'] : '' ) . '" />
			<input type="submit" class="button" value="' . __('save') . '" />
			</form>
			
			<div class="div-info-message">
				<p>' . __('categories_one_default_category') . '</p>
			</div>
		';
	}
	elseif( $category_action == 'move' )
	{
		echo '
		
			<form enctype="multipart/form-data" action="" method="post">
				' . admin_caption( "categories_move_files" ) . '
				<div class="features-table-actions-div">
					
						
					<div class="option-cnt">
						<label>' . __('category') . '</label>
						<div class="option-inputs">
							<select name="to_id_category">
								'.Categories::categorySelectOptions() . '
							</select>
						</div>
					</div>
				</div>
				<input type="hidden" name="id_category" value="'.(int)$_GET['id_category'].'" />
				<input type="submit" class="button" value="' . __('files_move') . '" />
			</form>
		';
	}
	elseif( $category_action == 'files' )
	{

		$pagin = new Pagin_Tool;	
				
		echo $pagin->addSelect( 'file_category', Categories::categorySelectOptions() )->addSelect( 'sort_by', array(
			'date_add' => 'date',
			'votes_opinion' => 'votes_opinion',
			'comments' => 'comments',
			'comments_facebook' => 'comments_facebook'
		) )->addSelect( 'sort_by_order', array(
			'DESC' => 'desc',
			'ASC' => 'asc'
		) )->wrapSelects()->wrap()->addJS( ( IPS_VERSION == 'pinestic' ? 'pinit_category_files' : 'category_files' ), $PD->cnt( IPS__FILES ) )->addMessage('')->get();
		
	}
	elseif( $category_action == 'browse' )
	{
		$pagin = new Pagin_Tool;	
				
		echo $pagin->addSelect( 'sort_by', array(
			'added' => 'added',
			'upload_categories.only_adult' => 'only_adult',
			'upload_categories.only_premium' => 'premium'
		) )->addSelect( 'sort_by_order', array(
			'DESC' => 'desc',
			'ASC' => 'asc'
		) )->wrapSelects()->wrap()->addJS( 'categories', $PD->cnt( 'upload_categories' ) )->addMessage('')->get();
		
	}
	else
	{
		Ips_Registry::get( 'Categories_Admin' )->updateThumbs();
	}
?>