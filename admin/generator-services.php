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

	if( !defined('USER_ADMIN') || !USER_ADMIN ) die ("Hakier?");
	
	$default_action = isset( $_GET['mem-action'] ) && Config::get('mem_generator') ? $_GET['mem-action'] : false ;
	
	$languages = Translate::codes();
	
	if( !$default_action )
	{
		Ips_Registry::get( 'Mem_Admin' )->updateTranslations();
		
		echo admin_caption( 'generator_mems' );
		
		$generator_menu = array(
			'add' => 'generators_add_new',
			'add_category' => 'admin_add_category',
			'view' => 'generator_browse',
			'settings' => 'settings',
			'categories' => 'generator_category'
		);
		if( count( $languages ) > 1 )
		{
			//$generator_menu['translation'] = 'language_translation';
		}
	
		echo responsive_menu( $generator_menu, admin_url( 'generator', 'mem-action=' ) );
	}
	elseif( $default_action == 'view' )
	{
		
		$pagin = new Pagin_Tool;	
					
		echo $pagin->addSelect( 'sort_by', array(
			'mem_activ' => 'generators_is_active',
			'mem_date_add' => 'date_added',
			'mem_generated' => 'generator_uploaded_mems'
		) )->addSelect( 'sort_by_order', array(
			'DESC' => 'desc',
			'ASC' => 'asc'
		) )->wrapSelects()->wrap()->addJS( 'generator', PD::getInstance()->cnt("mem_generator") )->addMessage('')->addCaption( 'generators_list' )->get();	
		echo '<a role="button" class="button" href="' . admin_url( 'generator' ) . '">' . __('common_back') . '</a>';
	
	}
	elseif( $default_action == 'add' )
	{
		$mem_categories = Ips_Registry::get( 'Mem_Admin' )->getCategories();
		
		if( count( $mem_categories ) == 0 )
		{
			return ips_admin_redirect( 'generator', 'mem-action=categories&category-action=add', 'generator_categories_empty' );
		}
		
		echo admin_caption( 'generators_add_new' );
		
		if( isset( $_POST['mem_category'] ) )
		{
			$info = Ips_Registry::get( 'Mem_Admin' )->addGenerator( $_POST );
			
			if( $info === true )
			{
				return ips_admin_redirect( 'generator', 'mem-action=view', array(
					'info' => 'generator_add_admin_success'
				));
			}
			
			return ips_admin_redirect( 'generator', 'mem-action=add', array(
				'alert' => $info
			));
		}
		
		echo Ips_Registry::get( 'Mem_Admin' )->generatorForm( $_POST );
		
	}
	elseif( $default_action == 'mem_activ' )
	{
		if( $_GET['mem_activ'] != '' && !empty( $_GET['id'] ) )
		{
			PD::getInstance()->update("mem_generator", array( 
				'mem_activ' => (int)$_GET['mem_activ']
			), array( 
				'id' => (int)$_GET['id']
			));
			
			ips_message( array(
				'normal' => 'generator_changed'
			) );
		}

		ips_admin_redirect( 'generator', 'mem-action=view' );
	}
	elseif( $default_action == 'edit' )
	{

		if( isset( $_POST['mem_category'] ) )
		{
			$info = Ips_Registry::get( 'Mem_Admin' )->updateGenerator( (int)$_GET['id'], $_POST );
			
			if( $info === true )
			{
				return ips_admin_redirect( 'generator', 'mem-action=view', array(
					'info' => 'generator_changed'
				));
			}
			
			return ips_admin_redirect( 'generator', 'mem-action=view', array(
				'alert' => $info
			));
		}
		
		echo admin_caption( 'generators_edit_mem' );
		
		echo Ips_Registry::get( 'Mem_Admin' )->generatorForm( (int)$_GET['id'] );
		
	}
	elseif( $default_action == 'settings' )
	{
		echo admin_caption( 'settings' );
		echo '
		<form action="admin-save.php" enctype="multipart/form-data" method="post">
			' . 
				displayArrayOptions(array(
					'mem_generator' => array(
						'option_set_text' => 'generator_mem',
					)
				)) 
			. '
		<input name="mem_generator_submit" type="submit" class="button" value="' . __('save') . '" />
		<a role="button" class="button" href="' . admin_url( 'generator' ) . '">' . __('common_back') . '</a>
		</form>
		
		';
	}
	elseif( $default_action == 'delete' )
	{
		Ips_Registry::get( 'Mem_Admin' )->delete( (int)$_GET['id'] );
		
		return ips_admin_redirect( 'generator', 'mem-action=view', 'deleted' );
	}
	elseif(  $default_action == 'add_category'  )
	{
		if( isset( $_POST['category_text'] ) )
		{
			echo Ips_Registry::get( 'Mem_Admin' )->addCategory( $_POST );
		}
		
		echo admin_caption( 'admin_add_category' );
		echo Ips_Registry::get( 'Mem_Admin' )->categoryForm();
	}
	elseif(  $default_action == 'categories'  )
	{
		
		
		$default_category_action = isset( $_GET['category-action'] ) ? $_GET['category-action'] : false ;
		
		if( $default_category_action == 'edit' )
		{
			if( isset( $_POST['category_text'] ) )
			{
				echo Ips_Registry::get( 'Mem_Admin' )->addCategory( $_POST );
			}
			echo admin_caption( 'generators_edit_category' );
			
			$row = Ips_Registry::get( 'Mem_Admin' )->getCategory( (int)$_GET['id'], false );
			
			if( !empty( $row ) )
			{
				echo Ips_Registry::get( 'Mem_Admin' )->categoryForm( $row );
			}
		}
		elseif(  $default_category_action == 'delete'  )
		{
			$category_id = (int)$_GET['id'];
			
			$count = PD::getInstance()->cnt( 'mem_generator', array( 
				'mem_category' => $category_id
			));
			
			if( $count > 0 && !isset( $_GET['confirm'] ) )
			{
				echo ips_message( array(
					'alert' =>  __s( 'generators_has_files', $count, admin_url( 'generator', 'mem-action=categories&category-action=delete&confirm=t&id=' . $category_id ))
				), true );
			}
			else
			{
				PD::getInstance()->delete( 'mem_generator_categories', array( 
					'id' => $category_id
				), 1);
				
				return ips_admin_redirect( 'generator', 'mem-action=categories', 'category_removed' );
			}
		}
		else
		{
			
			$pagin = new Pagin_Tool;	
					
			echo $pagin->addSelect( 'sort_by', array(
				'mem_date_add' => 'date_added',
				'mem_generated' => 'generator_uploaded_mems'
			) )->addSelect( 'sort_by_order', array(
				'DESC' => 'desc',
				'ASC' => 'asc'
			) )->wrapSelects()->wrap()->addJS( 'generator-cats', count( Ips_Registry::get( 'Mem_Admin' )->getCategories() ) )->addMessage('')->addCaption( 'generator_category' )->get();	
			echo '<a role="button" class="button" href="' . admin_url( 'generator' ) . '">' . __('common_back') . '</a>';
		}
	}
	echo '
	<div class="div-info-message">
		<p>' . __( 'generator_info' ) . '</p>
	</div>';
?>