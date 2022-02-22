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

class Pagin_Tool 
{
	public $content = '';
	public $sorts = '';
	public $thumbs = '';
	
	public function addJS( $adress, $pages )
	{
		$pages = ceil( $pages/20 );
		if( $pages == 0 )
		{
			$pages = 1;
		}
		
		$this->content .= '
		<script type="text/javascript">
			paginationAdres = "' . $adress . '";
			paginationTotal = ' . $pages . ';
		</script>
		<script src="js/paging.js" type="text/javascript"></script>
		';
		
		return $this;
	}
	public function addMessage( $message )
	{
		$this->content .= '
		<div class="div-info-message">
			' . __ ( $message ) . ' 
		</div>
		';
		
		return $this;
	}
	public function wrap()
	{
		$this->content = '
		<div id="paginates">
		<div id="containerPaginate"></div>
		' . $this->content . '
		</div>
		<div id="wrapper"></div>';
		
		return $this;
	}
	
	public function addCaption( $message )
	{
		$this->content = admin_caption( $message ) . $this->content;
		
		return $this;
	}
	
	public function wrapSelects( $message = 'field_filter' )
	{
		$this->content = '
		<div class="nice-blocks features-table-actions-div">
			<div class="blocks-header">
				<div class="sorPagin">
					'. $this->sorts .'
					<div class="chosen-container"><button class="button chosen-btn" onclick="sortowanieLoad(1);return false;">' . __( $message ) . '</button></div>
				</div>
				' . $this->thumbs . '
			</div>
		</div>';
		
		return $this;
	}
	
	public function addSelect( $name, $options )
	{
		$this->sorts.= '<select name="' . $name . '" id="' . $name . '">';
		
		if( is_array( $options ) )
		{
			$selected = false;
			foreach( $options as $value => $text )
			{
				$this->sorts.= '<option value="' . $value . '" ' . ( $selected ? '' : 'selected="selected"'). '>' . __( $text ) . '</option>';
				$selected = true;
			}
		}
		else
		{
			$this->sorts.= $options;
		}
		
		$this->sorts.= '</select>';
		
		return $this;
	}
	
	public function addInputOption( $name, $text )
	{
		$this->sorts.= '<div class="chosen-container"><input type="text" name="' . $name . '" id="' . $name . '" placeholder="' . $text . '" /></div>';
		return $this;
	}
	
	public function addSortOpt( $load_action )
	{
		if( in_array( $load_action, array( 'main', 'wait', 'archive', 'pins' ) ) )
		{
			$this->thumbs = '
			<div class="table-view">
				<b>' . __('file_view') . '</b>
					<a href="' . admin_url( 'change_view', 'thumbs=none' ) . '" class="table-view-list '.( Session::has('admin_thumbs') ? '' : 'activ') . '" title= ' . __('display_titles_materials') . ' ></a>
					<a href="' . admin_url( 'change_view', 'thumbs=thumbs' ) . '" class="table-view-thumbs '.( Session::get('admin_thumbs') == 'thumbs' ? 'activ' : '') . '" title=' . __('display_file_thumbnails') . '></a>
					<a href="' . admin_url( 'change_view', 'thumbs=preview' ) . '" class="table-view-preview '.( Session::get('admin_thumbs') == 'preview' ? 'activ' : '') . '" title=' . __('display_file_thumbnails') . '></a>
			</div>';
		}
		return $this;
	}
	public function get()
	{
		return $this->content;
	}
	
}
?>