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

	require_once( IPS_ADMIN_PATH .'/update-functions.php' );

	echo admin_caption( 'caption_pages' );
	echo '
	<div style="margin-top: 10px; margin-bottom: 10px;">
		<a href="' . admin_url( 'pages', 'action=list' ) . '" class="button">'.__('browse').'</a>
		<a href="' . admin_url( 'pages', 'action=add' ) . '" class="button">'.__('common_add').'</a>
	</div>';
	
	$pages = new Page();
	/**
	* Zapisywanie lub edycja po przesłaniu danych
	*/
	if( !empty($_POST) )
	{
		try{
			
			$pages->save( $_POST );
			
		} catch (Exception $e) {
			
			ips_message( array(
				'alert' =>  $e->getMessage()
			) );
			ips_admin_redirect( 'pages', 'action=add');
			
		}
	}
	
	if( !isset( $_GET['action'] ) || $_GET['action'] == 'list' )
	{
		
		$pagin = new Pagin_Tool;	
				
		echo $pagin->addSelect( 'sort_by', array(
			'id' => 'ID',
			'post_date' => 'date_added',
			'post_type' => 'status',
			'post_visibility' => 'visibility'
		) )->addSelect( 'sort_by_order', array(
			'ASC' => 'asc',
			'DESC' => 'desc',
		) )->wrapSelects()->wrap()->addJS( 'pages-list', $PD->cnt("posts") )->addMessage( __('post_info') )->get();	

	}
	elseif( $_GET['action'] == 'add' )
	{
		echo $pages->form();
	}
	elseif( $_GET['action'] == 'delete' )
	{
		$row = null;
		if( isset($_GET['id']) )
		{
			$id = intval( $_GET['id'] );
		}
		$pages->delete( $id );
	}
	elseif( $_GET['action'] == 'edit' )
	{
		$row = array();
		if( isset($_GET['id']) )
		{
			$row = $pages->getPages( array(
				'id' => $_GET['id']
			) );
		}
		echo $pages->form( $row );
	}
	
?>
	<script type="text/javascript" src="/libs/tinyeditor/tiny.editor.packed.js"></script>
	<link rel="stylesheet" href="/libs/tinyeditor/tinyeditor.css">
	<script type="text/javascript">
	//<![CDATA[
		var editor = new TINY.editor.edit('editor', {
			id: 'post_content',
			width: 'auto',
			height: 175,
			cssclass: 'tinyeditor',
			controlclass: 'tinyeditor-control',
			rowclass: 'tinyeditor-header',
			dividerclass: 'tinyeditor-divider',
			controls: ['bold', 'italic', 'underline', 'strikethrough', '|', 'subscript', 'superscript', '|',
				'orderedlist', 'unorderedlist', '|', 'outdent', 'indent', '|', 'leftalign',
				'centeralign', 'rightalign', 'blockjustify', '|', 'unformat', '|', 'undo', 'redo', 'n',
				'font', 'size', 'style', '|', 'image', 'hr', 'link', 'unlink', '|', 'print'],
			footer: true,
			fonts: ['Verdana','Arial','Georgia','Trebuchet MS'],
			xhtml: true,
			bodyid: 'editor',
			footerclass: 'tinyeditor-footer',
			toggle: {text: 'source', activetext: 'wysiwyg', cssclass: 'toggle'},
			resize: {cssclass: 'resize'}
		});
		$("#post_submit").on("click", function(){
			editor.post();
		});
	//]]>
						
	</script>