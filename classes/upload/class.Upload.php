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

class Upload extends Upload_Single_File
{
	
	public $tags = '';
	/**
	 * Required fields for material - image.
	 */
	public $config_image = array( 
		'title' => 'err_title'
	);
	
	/**
	 * Required fields for material - video.
	 */
	public $config_video = array( 
		'title' => 'err_title'
	);
	
	/**
	 * Required fields for material - text.
	 */
	public $config_text = array( 
		'title' => 'err_title'
	);
	/**
	 * Required fields for material - demotywator.
	 */
	public $config_demotywator = array( 
		'top_line' => 'err_title'
	);
	
	/**
	 * Required fields for material - mem.
	 */
	public $config_mem = array( 
		'title' => 'err_title'
	);
	
	/**
	 * Required fields for material - animation.
	 */
	public $config_animation = array( 
		'title' => 'err_title'
	);
	
	/**
	 * Required fields for material - article.
	 */
	public $config_article = array( 
		'title' => 'err_title', 
		'long_text' => 'err_article'
	);
	
	/**
	 * Required fields for material - gallery.
	 */
	public $config_gallery = array( 
		'title' => 'err_title', 
		'upload_images' => 'err_multiple_files'
	);
	
	/**
	 * Required fields for material- ranking.
	 */
	public $config_ranking = array( 
		'title' => 'err_title', 
		'upload_images' => 'err_multiple_files'
	);
	/**
	 * Required fields for material- mp4.
	 */
	public $config_mp4 = array( 
		'title' => 'err_title'
	);
	
	/**
	 * Saving the configuration file to be added
	 *
	 * @param $type - type of material
	 * @param $upload_subtype - file Type
	 *
	 * @return void
	 *
	 */
	public function makeFileConfig( $type, $upload_subtype )
	{
		if ( isset( $this->{'config_' . $type} ) )
		{
			$this->config   = $this->{'config_' . $type};
			$this->type     = $type;
			$this->upload_subtype = $upload_subtype;
			
			$this->checkConditions();
			
			$this->data = date( "Y-m-d H:i:s" );
			$this->fileName();
			
		}
		else
		{
			throw new Exception( 'Brak ustawień dla tego typu plików:' . $type );
		}
	}
	
	
	public function getTitle()
	{
		if( isset( $this->title ) )
		{
			return $this->title;
		}
		
		switch ( $this->type )
		{
			case 'demotywator':
				$title = get_input('top_line');
			break;
			default:
				$title = get_input('title');
			break;
		}
		
		$this->title = strip_tags( Sanitize::cleanXss( $title ) );
		
		return $this->title;
	}
	/**
	 * Upload pictorial material
	 *
	 * @param string $text - text
	 * 
	 * @return void
	 */
	public function uploadFile( $image = null )
	{
		parent::setConfig( array(
			'files' => 'file',
			'post' => 'upload_url',
			'url' => $image
		) );
	}

	/**
	 * Upload video
	 *
	 * @param null
	 * 
	 * @return void
	 */
	public function uploadVideoImage( $image )
	{
		if( empty( $image ) )
		{
			return $this->uploadFile();
		}

		parent::setConfig( array(
			'url' => $image
		) );
	}
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function makeWatermark( $image, $return = false )
	{
		$img = Gd::as_resource( $image );
		
		$margin = Config::getArray( 'upload_margin', 'default' );
		$watermark     = new Watermark();
		$img = $watermark->get( $img, $margin['top'], $margin['side'] );
		
		if ( $return )
		{
			return $img;
		}
		
		switch ( ips_pathinfo( $image, PATHINFO_EXTENSION ) )
		{
			case 'jpg':
			case 'jpeg':
				imagejpeg( $img, $image, Config::get( 'images_compress', 'jpg' ) );
			break;
			case 'png':
				imagepng( $img, $image, Config::get( 'images_compress', 'png' ) );
			break;
		}
		
	}
	/**
	 * Cleaning content from illegal HTML characters
	 *
	 * @param $text
	 * 
	 * @return string
	 */
	public function clearHtml( $text )
	{
		/* $intro_text = cutWords( strip_tags( preg_replace( '/(<\/[^>]+?>)(<[^>\/][^>]*?>)/', '$1 $2', $intro_text ) ), Config::get( 'ranking_options', 'intro_length' ) ); */
		
		$selfClosing = array_merge( explode( ',', 'html,font,body,head,br' ), Config::getArray( 'upload_allowed_html_tags' ) );
		
		$DOM = new DOMDocument();
		@$DOM->loadHTML( '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>' . $text . '</body></html>' );
		
		$els = $DOM->getElementsByTagName( '*' );
		
		foreach ( $els as $el )
		{
			$nodeName = strtolower( $el->nodeName );
			if ( !in_array( $nodeName, $selfClosing ) )
			{
				$tags   = $DOM->getElementsByTagName( $nodeName );
				$length = $tags->length;
				/*
				 * For each tag is not included in the permitted remove content
				 */
				for ( $i = 0; $i < $length; $i++ )
				{
					if ( is_object( $tags->item( $i ) ) )
					{
						$tags->item( $i )->parentNode->removeChild( $tags->item( $i ) );
					}
				}
			}
		}
		$D = new DOMDocument();
		@$D->loadHTML( '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>' . $this->extractBody( $DOM->saveHTML() ) . '</body></html>' );
		
		return $this->extractBody( $D->saveHTML() );
	}
	
	
	/**
	 * Assigning and "cleaning" the content of the intro to the gallery.
	 * Saving the contents of the intro.
	 *
	 * @param string $text - content of the article
	 * 
	 * @return void
	 */
	public function prepareArticle( $text )
	{
		$text = $this->clearHtml( $text );
		
		if ( !isset( $text[ 1 ] ) )
		{
			throw new Exception( 'err_article_x' );
		}
		
		unset( $DOM );
		
		if ( Config::get( 'article_options', 'allow_video' ) )
		{
			$text = $this->findYoutube( $text );
		}
	
		if ( is_numeric( Config::get( 'article_options', 'intro_length' ) ) )
		{
			$intro = cutWords( strip_tags( preg_replace( '/(<\/[^>]+?>)(<[^>\/][^>]*?>)/', '$1 $2', $text ) ), Config::get( 'article_options', 'intro_length' ) );
			/* $DOM   = new DOMDocument();
			@$DOM->loadHTML( '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>' . $intro . '</body></html>' );
			$intro = $this->extractBody( $DOM->saveHTML() );
			unset( $DOM ); */
		}
		else
		{
			$intro = '';
		}
		
		//echo mb_convert_encoding( $intro, 'UTF-8', 'HTML-ENTITIES' )
		return array(
			'long_text' => htmlspecialchars_decode( $text ),
			'intro_text' => htmlspecialchars_decode( $intro ) 
		);
	}
	/**
	 * Returning only the content of the body tag
	 *
	 * @param $html
	 * 
	 * @return strin
	 */
	public function extractBody( $html )
	{
		$start = strpos( $html, '<body>' );
		if ( $start === false )
		{
			return '';
		}
		$start = $start + strlen( '<body>' );
		return substr( $html, $start, strpos( $html, '</body>' ) - $start );
	}
	/**
	 * Wyszukiwanie i podmiana linków Youtube na kod embed.
	 * Search and swapping links Youtube embed code.
	 *
	 * @param string $string - text
	 * @param int $autoplay - autoplay 
	 * @param int $width - width
	 * @param int $height - height
	 * 
	 * @return string
	 */
	public function findYoutube( $string, $autoplay = 0, $width = 480, $height = 390 )
	{
		preg_match_all( '#(?:https?://)?(?:www\.)?(?:youtube\.com/(?:v/|watch\?v=)|youtu\.be/)([\w-]+)(?:\S+)?#', $string, $match );
		
		foreach ( $match[ 0 ] as $key => $video )
		{
			$embed  = '
			<div align="center">
			  <iframe title="YouTube" width="' . $width . '" height="' . $height . '" src="http://www.youtube.com/embed/' . $match[ 1 ][ $key ] . '?autoplay=' . $autoplay . '" frameborder="0" allowfullscreen></iframe>
			</div>';
			$string = str_replace( $video, $embed, $string );
		}
		
		return $string;
	}
	/**
	 * Generate a name for the image file.
	 *
	 * @param null
	 * 
	 * @return void
	 */
	public function fileName( $rand = false )
	{
		$this->name = seoLink( false, cutWords( $this->getTitle(), 40 ) );
		
		if ( IPS_LINK_FORMAT != '' )
		{
			$this->name = substr( $this->name, 0, -5 );
		}
		
		$this->name = preg_replace( '/[^A-Za-z0-9_-]/', '', $this->name ) . ( Config::get( 'add_filename_date' ) ? '_' . date( "Y-m-d_H-i-s" ) : '' ) . ( $rand ? '_' . rand() : '' );
		
		if ( file_exists( IMG_PATH . '/' . $this->name . '.png' ) )
		{
			return $this->fileName( true );
		}
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function checkConditions()
	{
		foreach ( $this->config as $key => $val )
		{
			if ( !isset( $_POST[ $key ] ) || empty( $_POST[ $key ] ) )
			{
				throw new Exception( $val );
			}
		}
		
		if ( Config::getArray( 'upload_demotywator_type', 'image' ) != 1 && $this->upload_subtype == 'image' )
		{
			throw new Exception( 'err_up_image' );
		}
		elseif ( Config::getArray( 'upload_demotywator_type', 'video' ) != 1 && $this->upload_subtype == 'video' )
		{
			throw new Exception( 'err_up_video' );
		}
		elseif ( Config::getArray( 'upload_demotywator_type', 'text' ) != 1 && $this->upload_subtype == 'text' )
		{
			throw new Exception( 'err_up_text' );
		}
		
	}
	
	/**
	 * Save tags for added material.
	 * If the tags have been added and is enabled in the PA,
	 * Will be pulled out of the title and description of the material
	 *
	 * @param string $tags - tags
	 * @param string $additional - additional description (bottom line in demotywatorze, lines mem)
	 * 
	 * @return void
	 */
	public function setUserTags( $tags, $additional = '' )
	{
		if ( is_array( $tags ) )
		{
			$tags = implode( ',', $tags );
		}
		
		if ( empty( $tags ) )
		{
			if ( Config::get( 'upload_tags_options', 'extract' ) == 0 )
			{
				return false;
			}
			
			$title = $this->getTitle();
			$tags = str_replace( ' ', ',', mb_strtolower( ( empty( $title ) ? $additional : $title ), 'UTF-8' ) );
		}
		
		$tags = explode( ',', $tags );
		
		foreach ( $tags as $key => $tag )
		{
			$tags[ $key ] = trim( Sanitize::tag( $tag ) );
		}
		
		$this->tags = implode( ',', $tags );
	}
	
	/**
	 * Move uploaded video file to video folder
	 *
	 * @param $data
	 * 
	 * @return bool
	 */
	public static function handleVideoFile( $file, $date_add )
	{
		$video_file = basename( urldecode( $file ) );
		
		if ( file_exists( IPS_TMP_FILES . '/' . $video_file ) )
		{
			$date_add_folder = createFolderByDate( IPS_VIDEO_PATH, $date_add );
			
			if ( rename( IPS_TMP_FILES . '/' . $video_file, IPS_VIDEO_PATH . '/' . $date_add_folder . '/' . $video_file ) )
			{
				return $date_add_folder . '/' . $video_file;
			}
		}
		
		throw new Exception( 'Wystąpił błąd podczas kopiowania pliku video' );
	}
	
	/**
	 * Resize video image to fit prefered size
	 *
	 * @param $image
	 * @param $video_info
	 * 
	 * @return string
	 */
	public function adaptCover( $image, $video_info )
	{
		if( empty( $image ) )
		{
			try{
				$upload = new Upload_Single_File();
				
				$upload->setConfig( array(
					'files' => 'upload_cover_file',
					'post' => 'upload_cover_url'
				) );
				
				$upload->_file = $upload->setImage( $upload->_file );
				
				$image = $upload->storeFile( IPS_TMP_FILES . '/' . str_random( 10 ) );
			}catch( Exception $e ){
				if( !isset( $video_info['cover'] ) )
				{
					throw new Exception('up_video_cover_error');
				}
				
				$image = $video_info['cover'];
			}
		}
		
		if( !file_exists( IPS_TMP_FILES . '/' . basename( $image ) ) )
		{
			$image = File::putUrl( $image, IPS_TMP_FILES .'/' . str_random( 10 ) );
		}
		
		if( isset( $video_info['width'] ) && isset( $video_info['height'] ) )
		{
			$image = IPS_TMP_FILES . '/' . basename( $image );
			
			list( $image_width, $image_height ) = getimagesize( $image );
			
			$video_ratio = round( $video_info['width'] / $video_info['height'], 4 );
			$image_ratio = round( $image_width / $image_height, 4 );
			
			if ( $image_ratio != $video_ratio )
			{
				if ( $video_info['width'] != $image_width )
				{
					$resize = new Upload_Extended();
					
					if ( $image_width < $image_height )
					{
						$resized = $resize->cropImage( $image, false, $video_info['height'] );
					}
					else
					{
						$resized = $resize->cropImage( $image, $video_info['width'] );
					}
					
					$new_image = imagecreatetruecolor( $video_info['width'], $video_info['height'] );
					
					$x_1 = imagesx( $resized ) < $video_info['width'] ? ( $video_info['width'] - imagesx( $resized ) ) / 2 : 0;
					$x_2 = imagesx( $resized ) > $video_info['width'] ? ( imagesx( $resized ) - $video_info['width'] ) / 2 : 0;
					$y_1 = imagesy( $resized ) < $video_info['height'] ? ( $video_info['height'] - imagesy( $resized ) ) / 2 : 0;
					$y_2 = imagesy( $resized ) > $video_info['height'] ? ( imagesy( $resized ) - $video_info['height'] ) / 2 : 0;
					
					imagecopy( $new_image, $resized, $x_1, $y_1, $x_2, $y_2, imagesx( $resized ), imagesy( $resized ) );
					
					$image = $resize->put( $new_image, IPS_TMP_FILES . '/' . str_random( 10 ) );
				}
			}
		}
		
		return IPS_TMP_URL . '/' . basename( $image );
	}
	
	public function uploadMultiple( $images, $upload_path, $options )
	{
		$images = !is_array( $images ) ? json_decode( stripslashes( $images ), true ) : $images;
		
		if ( !is_array( $images ) || count( $images ) < 1 )
		{
			throw new Exception( 'err_multiple_files' );
		}
		
		$ready_files = array();
		
		$upload_path_name = getFolderByDate( null, date( "Y-m-d H:i:s" ) ) . '/' . date( "d-H-") . str_random( 5 );
		
		if ( !mkdir( $upload_path . '/' . $upload_path_name . '/', 0777, true ) )
		{
			throw new Exception( 'err_create_folder' );
		}
		
		foreach ( $images as $image )
		{
			try
			{
				if ( is_val_link( $image, 3, true ) )
				{
					$ready_files[] = [
						'src' => $image,
						'url' => true,
						'upload_path_name' => $upload_path_name 
					];
				}
				elseif ( file_exists( IPS_TMP_FILES . '/' . basename( $image ) ) )
				{
					$ready_files[] = [
						'src' => IPS_TMP_FILES . '/' . basename( $image ),
						'url' => false,
						'upload_path_name' => $upload_path_name,
					];
				}
			}catch ( Exception $e ){}
		}
		
		if ( count( $ready_files ) < $options['limit'] )
		{
			throw new Exception( 'err_multiple_files' );
		}
		
		if ( $options['watermark'] == 1 )
		{
			foreach( $ready_files as $k => $up )
			{
				if( !$up['url'] || $options['local'] )
				{
					$ready_files[$k]['watermark'] = ( !$up['url'] || $options['local'] );
				}
			}
		}
		
		list( $min, $max ) = explode( ',', $options['items_range'] );
	
		if ( count( $images ) < $min || count( $images ) > $max )
		{
			throw new Exception( __s( 'err_multiple_files_count', count( $images ), $min, $max ) );
		}

		return array(
			'files' => $ready_files,
			'path' => $upload_path_name
		);
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function storeMultiple( $images, $upload_path, $upload_path_name, $options )
	{
		set_time_limit( 0 );
		
		$upload = new Upload_Single_File();

		foreach ( $images as $key => $image )
		{
			if( !$image['url'] )
			{
				$file = $upload_path_name . '/' . basename( $image['src'] );
				rename( $image['src'], $upload_path . '/' . $file );
			}
			elseif ( $options['local'] == 1 && $image['url'] )
			{
				try{
					$upload->_file = $upload->setImage( $image['src'] );
					$file = $upload_path_name . '/' . $upload->storeFile( $upload_path . '/' . $upload_path_name . '/' . str_random( 10 ) );
				}catch( Exception $e ){
					continue;
				}
			}
			else
			{
				$file = $image['src'];
			}
			
			$images[ $key ]['src'] = $file;
			
			if( isset( $image['watermark'] ) && $image['watermark'] )
			{
				$this->makeWatermark( $upload_path . '/' . $file );
			}
			
		}
		
		return $images;
	}
	
	public static function getPaths()
	{
		return array(
			IMG_PATH_LARGE,
			IMG_PATH_MEDIUM,
			IMG_PATH_THUMB,
			IMG_PATH_OG_THUMB,
			IMG_PATH_THUMB_SMALL,
			IMG_PATH_THUMB_MINI,
			IMG_PATH_SQUARE,
			IMG_PATH_GIF,
			IMG_PATH_BACKUP,
			IMG_PATH_MEDIA_POSTER 
		);
	}
}