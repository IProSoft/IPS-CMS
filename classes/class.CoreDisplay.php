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

class Core_Display
{
	
	/**
	 * Preload articles, galleries
	 */
	public $preloaded = array();
	
	/**
	 * Tablica z linkami seo dla wielokrotnego użycia
	 * Blackboard with seo links for reusable
	 */
	public $seoLinks = array();
	
	/**
	 * 
	 */
	public $hidden_watermark = false;
	
	public function __construct()
	{
		$this->tpl = Templates::getInc();

		/**
		 * Checking that allow the display of categories in the materials.
		 */
		$this->initCategory = Config::get( 'categories_option' );
		
		/**
		 * Sprawdzenie czy użytkownik może przeglądać materiały dla dorosłych.
		 * Check whether the user can view the adult material.
		 */
		$this->isAdult = isAdult();
		
		/**
		 * Zapisanie wybranych opcji w tablicy aby nie były za każdym razem wywoływane.
		 * Save the selected options in the table that were not called every time.
		 */
		$this->options = array(
			'fast' => defined( 'IPS_FAST' ),
			'comments_type' => Config::getArray( 'comments_options', 'type' ),
			'vote_file_menu' => Config::get( 'vote_file_menu' ),
			'social_plugins' => Config::getArray( 'social_plugins' ),
			'upload_text_display_type' => Config::get( 'upload_text_display_type' ) ,
			'gallery_images_count' => Config::getArray('gallery_options', 'images_count' ),
			'article_view_type' => Config::get( 'article_options', 'view_type' )
		);
	}
	
	/**
	 * Sprawdzanie czy zostały pobrane / istnieją jakieś pliki
	 * To check whether you have downloaded / there are any files
	 *
	 * @param null
	 * 
	 * @return bool
	 */
	public function checkForFiles()
	{
		if ( empty( $this->files ) )
		{
			if ( defined( 'IPS_ONSCROLL' ) )
			{
				if ( IPS_ACTION_PAGE > 1 )
				{
					return true;
				}
			}
			
			return '<div class="item no-files">' . __( 'nothing_to_display' ) . '</div>';
		}
		
		return false;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function paginator( $limit, $page_url, $page = IPS_ACTION_PAGE )
	{
		if ( defined( 'IPS_ONSCROLL' ) )
		{
			return false;
		}
		
		$html = "\n" . '<!-- #pagination -->' . "\n" . '<div id="bottom-pagination">';
		
		$page = ( $page > $limit ? $limit : $page );
		
		if ( Config::get( 'widget_navigation_bottom_box' ) == 1 && IPS_ACTION != 'search' )
		{
			$html .= Widgets::navigationPageBox( $page, $page_url, $limit );
		}
		
		if ( Config::get( 'pagin_css' ) != 'none' )
		{
			$pagin = new Pagination();
			
			$html .= "\n" . $pagin->getPagin( $page, $limit, $page_url, ( $this->appActionData( 'count_records' ) && count( $this->files ) < Config::get( 'files_on_page' ) ) );
		}
		
		if ( IPS_ACTION != 'search' )
		{
			if ( Config::get( 'widget_go_to' ) )
			{
				$html .= Widgets::goToPage( $page_url );
			}
			
			if ( Config::get( 'widget_navigation_bottom' ) )
			{
				$html .= Widgets::navigationPageLinks( $page, $page_url, $limit );
			}
		}
		
		$html .= '<!-- #pagination --></div>';
		
		
		return $html;
	}
	
	
	/**
	 * Zwracanie informacji na temat materiału.
	 * Returning information about the material.
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getFileInfo( $file_id, $category_id, $upload_adult )
	{
		
		$category = '';
		
		if ( $this->initCategory )
		{
			$category = Categories::getCategories( $category_id );
			
			if ( !isset( $category['category_name'] ) )
			{
				$category_id = Categories::defaultCategory();
				
				PD::getInstance()->update( IPS__FILES, array(
					'category_id' => $category_id 
				), array(
					'id' => $file_id
				) );
				
				$category = Categories::getCategories( $category_id );
				
			}
			
			$category['category_name'] = strip_tags( $category['category_name'] );
		}
		
		if ( ( Config::get( 'adult_files' ) && $upload_adult == 1 ) || ( is_array( $category ) && $category['only_adult'] == 1 ) )
		{
			if ( !$this->isAdult )
			{
				$file_is_adult = true;
			}
		}
		
		return array(
			'upload_adult' => isset( $file_is_adult ),
			'file_category' => $category 
		);
	}
	/**
	 * Rozmiary pliku i video dla poprawnego wyświetlenia w ramce.
	 * File Sizes and video to display properly in the frame.
	 *
	 * @param array $row file array
	 * @param bool $onpage - on file page
	 * 
	 * @return array
	 */
	public function getFileDimension( $row, $onpage = false )
	{
		$dimensions_array = unserialize( $row['upload_data'] );
		
		$dimensions = ( $onpage ? $dimensions_array['large'] : $dimensions_array['medium'] );
		
		if ( !isset( $dimensions['file'] ) )
		{
			/** Don't delete, saved dimensions with first display */
			
			$upload = new Upload_Extended;
			
			$dimensions_array = $upload->mergeFinalDimensions( $dimensions_array, $row['upload_image'] );
				
			PD::getInstance()->update( IPS__FILES, array(
				'upload_data' => serialize( $dimensions_array ) 
			), array(
				'id' => $row['id']
			) );
			
			$dimensions = ( $onpage ? $file_dims['large'] : $file_dims['medium'] );
			
		}
		
		$this->hidden_watermark = isset( $dimensions_array['stick'] );
		
		return array(
			'height' => $dimensions['file']['height'],
			'width' => $dimensions['file']['width'],
			'padding' => $dimensions['top'],
			'orginal_height' => $dimensions['height'],
			'orginal_width' => $dimensions['width'] 
		);
	}
	
	public function additionalDescription( $row )
	{
		if ( !isset( $row['long_text'] ) )
		{
			$row = $this->getPreloaded( $row['id'] );
		}
		
		if ( isset( $row['long_text'] ) )
		{
			return '<div class="file-additional-descript">' . nl2br( $row['long_text'] ) . '</div>';
		}
		
	}
	/**
	 * Insert Ad in gallery Middle
	 *
	 * @param 
	 * 
	 * @return 
	 */
	
	public function galleryAd()
	{
		return AdSystem::getInstance()->showAd( 'gallery_ad' );
	}
	/*
	 * Generate a gallery to display on file page
	 * 
	 * @param array $res
	 */
	public function getGallery( &$res )
	{
		$row = $this->getPreloaded( $res['id'] );
		
		$gallery_load = Config::getArray( 'gallery_options', 'load' );
		
		/* 
		* simple, 
		* pretty_photo:  http://www.no-margin-for-errors.com/projects/prettyphoto-jquery-lightbox-clone/documentation/, 
		* lightbox: http://lokeshdhakar.com/projects/lightbox2/, 
		* pirobox: http://www.pirolab.it/pirobox/, 
		* kwejk, 
		* demot, 
		* pinit
		*/
		$images = $this->parseImages( $row['long_text'], ( $gallery_load == 'simple' ) );
		
		$gallery = Templates::getInc()->getTpl( '/gallery/' . $gallery_load . '.html', array(
			'intro_text' => $row['intro_text'],
			'gallery_images' => $images,
			'image_start' => ( isset( $images[0]['src'] ) ? $images[0]['src'] : '' ),
			'seo_link' => $this->getSeoLink( $res['id'] )
		));
		
		if( Config::getArray('gallery_options', 'load_img_before' ) && IPS_VERSION != 'pinestic' )
		{
			$gallery = '<img src="' . $res['ips_img'] . '" />' . $gallery;
		}
		
		return $gallery;
		
	}
	
	/*
	 * Generate a gallery to display on the pages.
	 * 
	 * @param array $res
	 */
	public function getGalleryList( &$res)
	{
		$row = $this->getPreloaded( $res['id'] );
		
		$file_dims = $this->getFileDimension( $res );
		
		return Templates::getInc()->getTpl( 'item_gallery.html', array(
			'id' => $res['id'],
			'img' => $res['ips_img'],
			'intro_text' => cutWords( strip_tags( $row['intro_text'] ), 100 ),
			'url' => $res['url'],
			'width' => $file_dims['width'] 
		) );
	}
	
	/**
	* Add gallery images count to title 
	*/
	public function addImageCount( $row )
	{
		$upload_data = unserialize( $row['upload_data'] );
		
		if( isset( $upload_data['images_count'] ) )
		{
			return $row['title'] . __s( 'gallery_images_count', $upload_data['images_count'] );	
		}
		
		return $row['title'];
	}
	
	
	/*
	 * Generating rankings for display on the sub-pages and on file page.
	 * 
	 * @param array $res
	 */
	public function getRanking( &$res )
	{
		$row = PD::getInstance()->select( 'upload_ranking_files', array(
			'upload_id' => $res['id']
		), null, 'upload_ranking_files.id,upload_ranking_files.src,upload_ranking_files.votes_opinion', array(
			'upload_ranking_files.votes_opinion' => 'DESC'
		) );
		
		return Templates::getInc()->getTpl( 'item_ranking.html', array(
			'id' => $res['id'],
			'title' => $res['title'],
			'images' => $row,
			'row_text' => $res['url'],
			'add_more' => $res['user_id'] == USER_ID || USER_MOD 
		) );
	}
	/*
	 * Generating text material
	 * 
	 * @param array $res
	 */
	public function getTextFile( &$res )
	{
		$row = $this->getPreloaded( $res['id'] );
		
		if ( !$res['onpage'] && Config::get( 'upload_text_display_cut_intro' ) )
		{
			$row['long_text'] = $row['intro_text'] . __s( 'text_read_more', $res['url'] );
		}
		
		if ( $res['upload_type'] == 'demotywator' )
		{
			$file_dims = $this->getFileDimension( $res, $res['onpage'] );
			
			$return_file = '
			<div style="position: relative;">
				<img src="' . $res['ips_img'] . '">
				<div style="top:' . $file_dims['padding'] . 'px;width:' . ( $file_dims['orginal_width'] + 1 ) . 'px;height:' . $file_dims['orginal_height'] . 'px;position: absolute;margin: 0px auto 0px -' . ( $file_dims['orginal_width'] / 2 ) . 'px; left: 50%;">
					<div class="text-file-wrapper">
						' . $row['long_text'] . '
					</div>
				</div>
			</div>';
			
			if ( !$res['onpage'] )
			{
				$return_file = '<a href="' . $res['url'] . '" title="' . $res['title'] . '">' . $return_file . '</a>';
			}
			
			return $return_file;
		}
		
		return '<div class="text-file-wrapper-normal">' . $row['long_text'] . '</div>';
	}
	

	/*
	 * Generating articles for display on the sub-pages and on file page.
	 * 
	 * @param array $res
	 */
	public function getArticle( &$res )
	{
		$row = $this->getPreloaded( $res['id'] );
		
		$row['long_text'] = stripslashes( str_replace( array(
			'%5C',
			'%22' 
		), array(
			'',
			'' 
		), $row['long_text'] ) );
		
		return htmlspecialchars_decode( Templates::getInc()->getTpl( 'item_article.html', array(
			'article_style' => 1,
			'img' => $res['ips_img'],
			'url' => $res['url'],
			'article' => $row,
			'img_intro' => Config::get( 'article_options', 'img_intro' ) == 1,
		) ) );
	}
	
	/*
	 * Generating articles for display on the sub-pages and on file page.
	 * 
	 * @param array $res
	 */
	public function getArticleList( &$res )
	{
		
		$row = $this->getPreloaded( $res['id'] );
		
		if ( $this->options['article_view_type'] == 5 )
		{
			$file_dims    = $this->getFileDimension( $res );
			$row['intro_text'] = cutWords( $row['intro_text'], 100, true );
		}
		
		return htmlspecialchars_decode( Templates::getInc()->getTpl( 'item_article.html', array(
			'upload_id' => $res['id'],
			'article_style' => $this->options['article_view_type'],
			'img' => $res['ips_img'],
			'url' => $res['file_url'],
			'article' => $row,
			'img_intro' => Config::get( 'article_options', 'img_intro' ) == 1 && $this->options['article_view_type']  != 5,
			'width' => ( isset( $file_dims ) ? $file_dims['width'] : '' ) 
		) ) );
		
	}
	
	/**
	 * Get simple image
	 * 
	 * @param array $res
	 */
	public function getImage( $row, $lazyload = true )
	{
		$file_dims = $this->getFileDimension( $row, $row['onpage'] );
		
		$gif_span  = '';
		
		if ( $row['upload_subtype'] == 'animation' )
		{
			$gif_span = '<span class="gif_player ' . ( Config::get( 'gif_auto_play' ) == 1 || $row['onpage'] ? 'init' : '' ) . '" data-gif="' . ips_img( $row, 'gif' ) . '"></span>';
			
			if ( $row['upload_type'] == 'demotywator' )
			{
				/* if ( $this->getLayout() == 'two' )
				{
					array_walk( $file_dims, create_function('&$val', '$val *= 0.5;') ); 
				} */
				
				$return_file = $this->wrap( array_merge( $file_dims, $row ), $gif_span );
			}
		}
		
		if ( !isset( $return_file ) )
		{
			if ( Config::get( 'img_preloader' ) == '1' && $lazyload && !defined( 'IPS_AJAX' ) )
			{
				$return_file = '
					<div class="img-preload" style="width:' . $file_dims['width'] . 'px; height:' . $file_dims['height'] . 'px">
						<img class="img-preload-this" src="/images/svg/spinner-grey.svg" title="' . $row['title'] . '" data-original="' . $row['ips_img'] . '" />
					</div>';
			}
			else
			{
				$return_file = '<img width="' . $file_dims['width'] . '" height="' . $file_dims['height'] . '" src="' . $row['ips_img'] . '" alt="' . $row['title'] . '" title="' . $row['title'] . '" />';
			}
		}
		
		if ( !$row['onpage'] && Config::get( 'scroll_long_files' ) && $file_dims['height'] > Config::get( 'scroll_long_files_height' ) && $this->getLayout() == 'one' && !$this->options['fast'] )
		{
			$return_file = '
				<div style="height: ' . Config::get( 'scroll_long_files_height' ) . 'px; overflow: hidden">
					<a class="little_href" href="' . $row['url'] . '" title="' . $row['title'] . '">
						' . $return_file . '
					</a>
				</div>
				<div class="slide-down font_bolder img_slide_down">' . __( 'item_expand_height' ) . '</div>';
		}
		else
		{
			if ( $row['upload_type'] != 'demotywator' )
			{
				$return_file = $gif_span . $return_file;
			}
			
			if ( !$row['onpage'] )
			{
				$return_file = '<a href="' . $row['url'] . '" title="' . $row['title'] . '">' . $return_file . '</a>';
			}
		}
		
		unset( $file_dims, $gif_span );
		
		return $return_file;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function loadFile( &$row, $onpage = false, $lazyload = true )
	{
		$row = array_merge( $row, [
			'ips_img'	=> ips_img( $row, ( $onpage ? 'large' : 'medium' ) ),
			'onpage'	=> $onpage,
			'size'		=> $onpage ? 'large' : 'medium',
			'url'		=> $this->getSeoLink( $row['id'] ),
		]);
		
		if ( $row['upload_type'] == 'video' || in_array( $row['upload_subtype'], array( 'video', 'mp4', 'swf' ) ) )
		{
			return $this->getVideo( $row );
		}
		elseif ( $row['upload_type'] == 'article' )
		{
			if ( $onpage )
			{
				return $this->getArticle( $row );
			}
			else
			{
				return $this->getArticleList( $row );
			}
		}
		elseif ( $row['upload_type'] == 'gallery' )
		{
			if ( $onpage )
			{
				return $this->getGallery( $row );
			}
			else
			{
				return $this->getGalleryList( $row );
			}
		}
		elseif ( $row['upload_type'] == 'text' && $this->options['upload_text_display_type'] == 'display_as_text' )
		{
			return $this->getTextFile( $row );
		}
		elseif ( $onpage && $row['upload_type'] == 'ranking' )
		{
			return $this->getRanking( $row );
		}
		else
		{
			return $this->getImage( $row, $lazyload );
		}
	}

	/**
	 * Generate a seo link to the item
	 *
	 * @return mixed|null return link
	 *
	 */
	public function getSeoLink( $id, $seo_link = null )
	{
		if ( !isset( $this->seoLinks[$id] ) )
		{
			$this->seoLinks[$id] = ( !empty( $seo_link ) ? ABS_URL . $id . '/' . $seo_link : seoLink( $id ) );
		}
		return $this->seoLinks[$id];
	}
	/**
	 * This function is designed to display a thumbnail of the material in the case of a template consisting of three columns
	 */
	public function thumbnailLoad( $row )
	{
		global ${IPS_LNG};
		
		$upload_type = ${IPS_LNG}['common_type_image'];
		
		if ( isset( ${IPS_LNG}['common_type_' . $row['upload_type']] ) )
		{
			$upload_type = ${IPS_LNG}['common_type_' . $row['upload_type']];
		}
		
		return array(
			'img' => ips_img( $row, 'thumb' ),
			'upload_type_text' => $upload_type
		);
		
	}
	
	/**
	 * Displaying only user/board followers/following /users list
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function pinitUsers( $action )
	{
		if ( $msg = $this->checkForFiles() )
		{
			return $msg;
		}
		
		$u = new PinUser();
		return $u->displayUserBlocks( $this->files, $action );
	}
	
	/**
	 * Display files for Pinit template
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function pins( $action )
	{
		if ( $msg = $this->checkForFiles() )
		{
			return $msg;
		}
		
		$pins = new Pin();
		return $pins->displayFiles( $this->files, $action );
	}
	
	/**
	 * Display boards for Pinit template
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function boards( $action )
	{
		if ( $msg = $this->checkForFiles() )
		{
			return $msg;
		}
		
		$b = new Board();
		return $b->displayBoardBlocks( $this->files, 'public', $action );
	}
	
	/**
	 * Load articles, galeries with one query
	 */
	public function preloadFiles()
	{
		foreach ( $this->files as $file )
		{
			$this->getSeoLink( $file['id'], $file['seo_link'] );
			
			if ( $file['upload_adult'] == 0 && ( in_array( $file['upload_type'], array(
				'article',
				'gallery',
				'ranking' 
			) ) || ( $file['upload_type'] == 'text' && $this->options['upload_text_display_type'] == 'display_as_text' ) ) )
			{
				$this->preloaded[ $file['id'] ] = $file['id'];
			}
		}
			
		if ( !empty( $this->preloaded ) )
		{
			$rows = PD::getInstance()->select( 'upload_text', array(
				'upload_id' => array( $this->preloaded , 'IN' )
			) );
			
			foreach ( $rows as $file )
			{
				$this->preloaded[ $file['upload_id'] ] = $file;
			}
		}
	}
	
	/**
	 * Load articles, galeries with one query
	 */
	public function getPreloaded( $upload_id )
	{
		if ( !isset( $this->preloaded[ $upload_id ] ) )
		{
			$this->preloaded[ $upload_id ] = PD::getInstance()->select( 'upload_text', array(
				'upload_id' => $upload_id 
			), 1 );
		}
		
		return $this->preloaded[ $upload_id ];
	}
	
	/**
	 * Display files in all templates : NOT pinestic
	 */
	public function displayFiles()
	{
		if ( $msg = $this->checkForFiles() )
		{
			return $msg;
		}
		
		if ( !App::ver( array(
			'gag',
			'bebzol',
			'vines' 
		) ) && Config::get( 'page_fast' ) == 1 )
		{
			echo Widgets::fastButton();
		}
		
		if ( !isset( $this->args['on_widget'] ) )
		{
			echo AdSystem::getInstance()->showAd( 'top_of_list' );
		}
		
		$tpl_name = 'item' . ( $this->files[0]['upload_status'] == 'archive' ? '_' . $this->files[0]['upload_status'] : '' ) . '.html';
		
		/**
		 * Load articles, galeries with one query
		 */
		if ( $this->getLayout() !== 'three' )
		{
			$this->preloadFiles();
		}
		
		/**
		 * Display files
		 */
		foreach ( $this->files as $key => $row )
		{
			$row['file_url'] = $this->getSeoLink( $row['id'], $row['seo_link'] );
			
			if ( !isset( $this->args['on_widget'] ) )
			{
				echo AdSystem::getInstance()->showAd( 'between_files', $key );
			}
			
			$info = $this->getFileInfo( $row['id'], $row['category_id'], $row['upload_adult'] );
	
			/**
			 * Three column layout - thumbnail
			 */
			if ( $this->getLayout() == 'three' )
			{
				$row['file_container'] = $this->thumbnailLoad( $row );
			}
			elseif ( $info['upload_adult'] )
			{
				$row['file_container'] = $this->adultFile( $row );
			}
			else
			{
				$row['file_container'] = $this->loadFile( $row, false, true );
			}
			
			$row['title']          = stripslashes( $row['title'] );
			
			if ( $row['upload_type'] == 'gallery' && $this->options['gallery_images_count'] )
			{
				$row['title'] = $this->addImageCount( $row );
			}
			
			$row['comments']        = $this->commentsCount( $row );
			$row['vote_type']       = $this->options['vote_file_menu'];
			$row['upload_source']   = '';
			$row['report_file']     = ( $row['upload_activ'] == 0 );
			$row['file_category']   = $info['file_category'];
			$row['nav']             = array(
										'box' => '',
										'link' => '' 
									);
			$row['ads']             = array(
										'above_file' => '',
										'under_file' => '',
										'under_image' => ''
									);
			
			$row['button_nk']       = '';
			$row['button_like']     = '';
			$row['button_share']    = '';
			$row['button_twitter']  = '';
			$row['button_google']   = '';
			
			$row['widget_url_copy'] = '';
			$row['upload_tags']     = '';
			$row['description_box'] = '';
			$row['mod_links']       = '';
			
			$row['file_class_css'] = ( $this->hidden_watermark ? 'image-hidden-watermark' : '' ) . ' file-' . $row['upload_type'];
			
			if ( $this->getLayout() == 'three' )
			{
				$row['thumb_count'] = Config::getArray( 'template_settings', 'thumb_count');
				
				echo $this->tpl->getTpl( 'item_thumb.html', $row );
			}
			else
			{
				if ( $this->options['social_plugins']['share'] )
				{
					$row['button_share'] = SocialButtons::shareOld( $row['file_url'], $row['id'] );
				}
				if ( $this->options['social_plugins']['like'] )
				{
					$row['button_like'] = SocialButtons::like( $row['file_url'], $this->options['social_plugins']['like_template'], $this->options['social_plugins']['like_add_share'] );
				}
				if ( $this->options['social_plugins']['twitter'] )
				{
					$row['button_twitter'] = SocialButtons::twitterButton( $row['file_url'], $row['title'] );
				}
				
				if ( !isset( $this->args['on_widget'] ) )
				{
					if ( $this->options['social_plugins']['nk'] )
					{
						$row['button_nk'] = SocialButtons::nkButton( $row['file_url'], $row['title'], ips_img( $row, 'thumb' ) );
					}
					if ( $this->options['social_plugins']['google'] )
					{
						$row['button_google'] = SocialButtons::googleButton( $row['file_url'] );
					}
				}
				
				echo $this->tpl->getTpl( $tpl_name, $row );
				
				if ( !isset( $this->args['on_widget'] ) )
				{
					if ( USER_MOD )
					{
						echo Operations::fileModeration( $row );
					}
					elseif ( $row['user_login'] == USER_LOGIN && $row['upload_status'] == 'private' )
					{
						echo Operations::editFileButtons( $row['id'] );
					}
				}
			}
		}
		
		echo AdSystem::getInstance()->showAd( 'bottom_of_list' );
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function commentsCount( &$row )
	{
		if ( $this->options['comments_type'] == 'facebook' )
		{
			return (int) $row['comments_facebook'];
		}
		elseif ( $this->options['comments_type'] == 'ajax_facebook' )
		{
			return ( (int) $row['comments'] + (int) $row['comments_facebook'] );
		}
		
		return (int) $row['comments'];
	}

	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function displayComments()
	{
		foreach ( $this->files as $key => $res )
		{
			$this->files[$key]['avatar'] = ips_user_avatar( $res['avatar'], 'url' );
		}
		
		return Templates::getInc()->getTpl( 'user_comments.html', array(
			'comments' => $this->files
		));
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function displayUsers( $page_num )
	{
		$i = ( $page_num - 1 ) * Config::get( 'files_on_page' );
		
		foreach ( $this->files as $key => $res )
		{
			$this->files[$key] = array_merge( $res, array(
				'user_position' => $i + 1,
				'user_top_stats' => str_replace( array_map( function( $v ){
					return '{' . $v . '}';
				}, array_keys( $res ) ), array_values( $res ), __( 'user_top_stats' ) ) 
			) );
			$i++;
		}
		
		return Templates::getInc()->getTpl( 'top_users.html', array(
			'users' => $this->files
		));
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function modPanel( $type )
	{
		foreach ( $this->files as $key => $res )
		{
			$this->files[$key]['upload_image'] = ips_img( $res, 'medium' );
		}
		
		return Templates::getInc()->getTpl( 'moderator' . ( strstr( $type, '_' ) ) . '.html', array(
			'items' => $this->files
		));
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getVideo( &$res )
	{
		$file_dims = $this->getFileDimension( $res, $res['onpage'] );
		
		if ( $res['upload_subtype'] == 'swf' )
		{
			$video = Ips_Registry::get( 'Swf' )->get( $res['upload_video'], $file_dims );
		}
		elseif ( $res['upload_subtype'] == 'mp4' )
		{
			$video = Ips_Registry::get( 'Mp4', isset( $this->args['on_widget'] ) )->get( $res['upload_video'], array_merge( $file_dims, array(
				'id' 		=> $res['id'],
				'onpage' 	=> $res['onpage'],
				'video_url' => $res['url'],
				'video_title' => $res['title'],
				'upload_image' => $res['upload_image'],
			) ) );
		}
		else
		{
			$video = Ips_Registry::get( 'Video' )->get( $res['upload_video'], array_merge( $file_dims, array(
				'id' 		=> $res['id'],
				'onpage' 	=> $res['onpage'],
				'loop' 		=> $res['onpage'] ? Config::getArray( 'video_player', 'loop' ) : false,
				'autoplay' 	=> $res['onpage'] ? Config::getArray( 'video_player', 'autoplay' ) : false,
			) ) );
		}
		
		return $this->wrap( array_merge( $file_dims, $res ), $video  );
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function wrap( $row, $media )
	{
		return Templates::getInc()->getTpl( 'item_media_wrap.html', array(
			'id' => $row['id'],
			'image' => $row['ips_img'],
			'media' => $media,
			'width' => $row['width'],
			'max_w' => ( $row['orginal_width'] / $row['width'] ) * 100,
			'max_h' => ( $row['orginal_height'] / $row['height'] ) * 100,
			'padding' => ( $row['padding'] / $row['height'] ) * 100
		) );
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public function adultFile( $row, $onpage = false )
	{
		$file_dims = $display->getFileDimension( $row, $onpage );
		
		return Templates::getInc()->getTpl( 'item_adult.html', array(
			'id' => $row['id'],
			'width' => $file_dims['width'],
			'height' => ( $file_dims['height'] > 500 ? 500 : $file_dims['height'] )
		) );
	}
	
	/**
	 * Pull all img text.
	 *
	 * @param string $images - text string with images
	 * @param string $html - return as img list
	 * 
	 * @return array $images - Shorting array of all the pictures in the text
	 */
	public function parseImages( $images, $html = false )
	{
		$images = unserialize( $images );
		
		if ( $html )
		{
			foreach ( $images as $key => $image )
			{
				$images[$key] = '<img src="' . ( $image['url'] ? $image['src'] : IPS_GELLERY_IMG . '/' . $image['src'] ) . '" class="gallery_image" />';
			}
			
			array_splice( $images, round( count( $images ) / 2 ), 0, $this->galleryAd() );
			
			$images = implode( '<br />', $images );
		}
		else
		{
			foreach ( $images as $key => $image )
			{
				$images[$key]['src'] = ( $image['url'] ? $image['src'] : IPS_GELLERY_IMG . '/' . $image['src'] );
			}
		}
		
		return $images;
	}
	/**
	 * Zwracanie typu layoutu, pozwala na obejście domyślnych ustawień
	 * Return type of layout, allows you to bypass the default settings
	 *
	 * @param null
	 * 
	 * @return string
	 */
	public function getLayout()
	{
		return isset( $this->args['widget_layout'] ) ? $this->args['widget_layout'] : IPS_ACTION_LAYOUT;
	}
	
}
?>
