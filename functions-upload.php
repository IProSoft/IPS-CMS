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
	if( !defined('IPS_VERSION') ) die('IPS');
	
	if ( isset( $_POST['upload_subtype'] ) )
	{
		Session::setChild( 'upload_tmp', 'upload_subtype', $_POST['upload_subtype'] );
	};
	
	function ips_escape( &$val, $index )
	{
		if( !is_array( $val ) )
		{
			$val = trim( stripslashes( $val ) );
		}
		else
		{
			array_walk_recursive( $val, 'ips_escape');
		}
	}
	array_walk_recursive( $_POST, 'ips_escape' );
	
	function upload_clear()
	{
		Session::clear( 'upload_tmp' );
	}

	function get_directory_files( $dir )
	{
		if( !file_exists( ABS_PATH. '/' .$dir ) )
		{
			if( !file_exists( ABS_PATH. '/' . $dir. '/' ) )
			{
				return false;
			}
			else
			{
				$dir .= '/';
			}
		}
			
		$zestaw = $files = array();
		$iterator = new DirectoryIterator( ABS_PATH. '/' .$dir );

		foreach ( $iterator as $info )
		{
			if ( $info->isFile())
			{
				$files[] = $info->getPathname();
			}
		}
		
		foreach( $files as $file )
		{
			$mime = getimagesize($file);
			if(in_array($mime['mime'] , array(IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG)) 
			|| in_array($mime['mime'] , array('image/gif' , 'image/jpeg' ,'image/png')))
			{
				$ext = pathinfo($file, PATHINFO_EXTENSION);

				$file_name = before_decode( basename( $file ) );
				
				if( function_exists('mb_convert_encoding') )
				{
					$nazwa = mb_convert_encoding( $file_name, 'UTF-8', 'ISO-8859-2' );
				}
				elseif( function_exists('iconv') )
				{
					$current_encoding = mb_detect_encoding( $file, 'auto' );
					$nazwa = iconv( ( $current_encoding== false ? 'ISO-8859-2' : $current_encoding ), 'UTF-8',$file_name );
				}
				else
				{
					$nazwa = utf8_encode( clearAscii( $file_name ) );
				}
				
				$file_name = after_decode( $file_name );

				$zestaw[] = array(
					'plik' => $file,
					'ext' => $ext,
					'nazwa' => substr( str_replace('š','ą', $file_name ), 0, - ( strlen( $ext ) + 1 ) )
				);
				
			}
		}		
		return $zestaw;
	}
	
	
	function check_image_url( $url )
	{
			$ch = curl_init( $url ); 
			curl_setopt( $ch, CURLOPT_HEADER, 1 );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );		
			$c = curl_exec( $ch ); 
			$info = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
			curl_close ( $ch );

		return $info == 200 ? true : false ;
		/* if (is_array(@get_headers($url)))
			return true;
		else
			return false; */
	}
	function is_val_link( $url, $timeout = 2, $only_regex = false )
	{
        if( $only_regex && preg_match( '!http(?:s)://.+?\.(?:jpe?g|png|gif)!i', $url ) )
		{
			return true;
		}
		
		$ch = curl_init();

			$opts = array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_AUTOREFERER => true,
				CURLOPT_REFERER => 'http://google.com/',
				CURLOPT_URL => $url, 
				CURLOPT_NOBODY => true,
				CURLOPT_TIMEOUT => $timeout
			);
			curl_setopt_array($ch, $opts); 
			curl_exec($ch);
			
			$retval = curl_getinfo( $ch, CURLINFO_HTTP_CODE ) == 200; 
			curl_close($ch); 
		
		return $retval;
	}


	function up_push( &$array, $element, $value )
	{
		if( !isset( $array[ $element ] ) )
		{
			$array[$element] = '';
		}
		$array[$element] = $value;
	}
	
	function chosen_tags( $tags )
	{
		if( !empty( $tags ) )
		{
			$tags = explode( ',', $tags );
			return implode( '', array_map( function( &$value ){
				$value = '<option value="' . $value . '">' . $value . '</option>';
			}, $tags ) );
		}
		
		return '<option value=""></option>';
	}
	function up_edit_field( $field, $default = '' )
	{
		return Ips_Registry::get('Edit')->field( $field, $default );
	}
	
	function make_comparer()
	{
		// Normalize criteria up front so that the comparer finds everything tidy
		$criteria = func_get_args();
		foreach ($criteria as $index => $criterion) {
			$criteria[$index] = is_array($criterion)
				? array_pad($criterion, 3, null)
				: array($criterion, SORT_ASC, null);
		}

		return function($first, $second) use (&$criteria) {
			foreach ($criteria as $criterion) {
				// How will we compare this round?
				list($column, $sortOrder, $projection) = $criterion;
				$sortOrder = $sortOrder === SORT_DESC ? -1 : 1;

				// If a projection was defined project the values now
				if ($projection) {
					$lhs = call_user_func($projection, $first[$column]);
					$rhs = call_user_func($projection, $second[$column]);
				}
				else {
					$lhs = $first[$column];
					$rhs = $second[$column];
				}

				// Do the actual comparison; do not return if equal
				if ($lhs < $rhs) {
					return -1 * $sortOrder;
				}
				else if ($lhs > $rhs) {
					return 1 * $sortOrder;
				}
			}

			return 0; // tiebreakers exhausted, so $first == $second
		};
	}
	/**
	* Extract HTML tag from page content
	* 
	*/
	function extract_html_tags( $html_content, $html_tag )
	{

		$encoding = Session::get( 'encoding', 'UTF-8' );
		
		$charset = strpos( $html_content, 'charset=');
		$charset = substr( $html_content, $charset - 150, $charset + 150 );

		if( strpos( $charset, 'content-type') )
		{
			preg_match( '@charset=([0-9A-Za-z-]*)@i', $html_content, $match );
			if( isset( $match[1] ) && !empty( $match[1] ) )
			{
				$encoding = $match[1];
			}
		}
		
		libxml_use_internal_errors( true );

		$dom = new DOMDocument( '1.0', $encoding );
		$dom->recover = true;
		$html_content = preg_replace( '/&(?![A-Za-z0-9#]{1,7};)/', '', $html_content );

		if( $html_tag == 'body' || strpos( $html_tag, '#' ) !== false )
		{
			$html_content = mb_convert_encoding( $html_content, 'HTML-ENTITIES', $encoding );
		}
		
		if ( @$dom->loadHTML( '<?xml encoding="'.$encoding.'">' . $html_content ) )
		{
			if( $html_tag == 'body' )
			{
				$xpath = new DOMXpath( $dom );
				$result = $xpath->query("//body");
				$docSave = new DOMDocument( '1.0', $encoding );
				
				foreach ( $result as $node )
				{
					$domNode = $docSave->importNode( $node, true );
					$docSave->appendChild( $domNode );
				}
				return $docSave->saveHTML();
			}
			elseif( strpos( $html_tag, '#' ) !== false )
			{
				$xpath = new DOMXPath( $dom );
				$result = $xpath->query("//*[@id='" . str_replace( '#', '', $html_tag ) . "']");
				$docSave = new DOMDocument( '1.0', $encoding );
				
				foreach ( $result as $node )
				{
					$domNode = $docSave->importNode( $node, true );
					$docSave->appendChild( $domNode );
				}
				return $docSave->saveHTML();
				
				/* $result = $dom->getElementById( str_replace( '#', '', $html_tag ) );
				$docSave = new DOMDocument( '1.0', $encoding );
				$domNode = $docSave->importNode( $result, true );
				$docSave->appendChild( $domNode );
				
				return $docSave->saveHTML(); */
			}

			return @$dom->getElementsByTagName( $html_tag );
		}
		
		return false;
	}
	
	/**
	* Crawl any page with sub pages to get content
	* EX: 
	* Function can crawl pages like www.page.com/page/1 to www.page.com/page/X
	*/
	function get_link_content( $urls, $sub_pages_regexp = 0, $direct = 0, $start_page = 0, $pages = 0 )
	{
		$content = '';
		$urls_subpages = array();
		
		if( $pages > 1 && !empty( $sub_pages_regexp ) )
		{
			foreach( $urls as $n => $link )
			{
			
				if( $direct == 'normal')
				{
					for( $i = $start_page ; $i <= $pages ; $i++ )
					{
						$urls_subpages[] = $link . str_replace('//', '/', preg_replace("/\[0\]/", $i, $sub_pages_regexp ) );
					}
				}
				elseif( $direct == 'back' )
				{
					for( $i = $start_page ; $i > $start_page - $pages ; $i-- )
					{
						$urls_subpages[] = $link . str_replace('//', '/', preg_replace("/\[0\]/", $i, $sub_pages_regexp ) );
					}
				}
			}
		}
		
		if( !empty( $urls_subpages ) )
		{
			$urls = $urls_subpages;
		}
		;
		foreach( $urls as $n => $link )
		{
			$options = array(
				'cookie' => true,
				'refferer' => 'http://' . parse_url( $link, PHP_URL_HOST )
			);

			$data = curlIPS( $link, $options );
		
			$content .= ( ( defined('IPS_SELF') && strpos( parse_url( $link, PHP_URL_HOST ), 'youtube.com' ) !== false ) ? $data : extract_html_tags( $data, 'body') );
			
		}

		return $content;
	}
	
	function before_decode( $text )
	{
		return strtr( $text, array(
			"\xb9" => "|-a-|", "\xa5" => "|-A-|", "\xe6" => "|-c-|", "\xc6" => "|-C-|",
			"\xea" => "|-e-|", "\xca" => "|-E-|", "\xb3" => "|-l-|", "\xa3" => "|-L-|",
			"\xf3" => "|-o-|", "\xd3" => "|-O-|", "\x9c" => "|-s-|", "\x8c" => "|-S-|",
			"\x9f" => "|-z-|", "\xaf" => "|-Z-|", "\xbf" => "|-z-|", "\xac" => "|-Z-|",
			"\xf1" => "|-n-|", "\xd1" => "|-N-|",

			"\xc4\x85" => "|-a-|", "\xc4\x84" => "|-A-|", "\xc4\x87" => "|-c-|", "\xc4\x86" => "|-C-|",
			"\xc4\x99" => "|-e-|", "\xc4\x98" => "|-E-|", "\xc5\x82" => "|-l-|", "\xc5\x81" => "|-L-|",
			"\xc3\xb3" => "|-o-|", "\xc3\x93" => "|-O-|", "\xc5\x9b" => "|-s-|", "\xc5\x9a" => "|-S-|",
			"\xc5\xbc" => "|-z-|", "\xc5\xbb" => "|-Z-|", "\xc5\xba" => "|-z-|", "\xc5\xb9" => "|-Z-|",
			"\xc5\x84" => "|-n-|", "\xc5\x83" => "|-N-|",

			"\xb1" => "|-a-|", "\xa1" => "|-A-|", "\xe6" => "|-c-|", "\xc6" => "|-C-|",
			"\xea" => "|-e-|", "\xca" => "|-E-|", "\xb3" => "|-l-|", "\xa3" => "|-L-|",
			"\xf3" => "|-o-|", "\xd3" => "|-O-|", "\xb6" => "|-s-|", "\xa6" => "|-S-|",
			"\xbc" => "|-z-|", "\xac" => "|-Z-|", "\xbf" => "|-z-|", "\xaf" => "|-Z-|",
			"\xf1" => "|-n-|", "\xd1" => "|-N-|",
		) );
	}
	
	function after_decode( $text )
	{

		return strtr( $text, array(
			"|-a-|" => "ą", "|-A-|" => "Ą", "|-c-|" => "ć", "|-C-|" => "Ć",
			"|-e-|" => "ę", "|-E-|" => "Ę", "|-l-|" => "ł", "|-L-|" => "Ł",
			"|-o-|" => "ó", "|-O-|" => "Ó", "|-s-|" => "ś", "|-S-|" => "Ś",
			"|-z-|" => "ź", "|-Z-|" => "Ź", "|-z-|" => "ż", "|-Z-|" => "Ż",
			"|-n-|" => "ń", "|-N-|" => "Ń"

		) );
	} 
	
	

	/* 
	* Currently GD does not provide a method
	* to draw a rounded filled rectangle - ie a rectangle with rounded edges.
	* http://www.web-max.ca/PHP/misc_10.php
	*/
	function imagefillroundedrect( $im, $x, $y, $cx, $cy, $rad, $col )
	{

		// Draw the middle cross shape of the rectangle

		imagefilledrectangle($im,$x,$y+$rad,$cx,$cy-$rad,$col);
		imagefilledrectangle($im,$x+$rad,$y,$cx-$rad,$cy,$col);

		$dia = $rad*2;

		// Now fill in the rounded corners

		imagefilledellipse($im, $x+$rad, $y+$rad, $rad*2, $dia, $col);
		imagefilledellipse($im, $x+$rad, $cy-$rad, $rad*2, $dia, $col);
		imagefilledellipse($im, $cx-$rad, $cy-$rad, $rad*2, $dia, $col);
		imagefilledellipse($im, $cx-$rad, $y+$rad, $rad*2, $dia, $col);
	}
	

	function upload_error( $err, $upload = null )
	{
		if ( Session::getChild( 'inc', 'import_add_to') )
		{
			return false;
		}
		
		if ( isset( $_POST['ajax_post'] ) )
		{
			die( ips_json( array(
				'error' => ips_message( array(
					'alert' => $err
				), true ),
				'token' => App::getToken()
			) ) );
		}
		
		Session::set( 'upload_tmp', $_POST );
		
		if( $upload != null )
		{
			errorFileUpload();
		}
		
		return ips_redirect( Session::get( 'up-redirect' ), array(
			'alert' => $err
		) );
	}

	function getmax( $array, $cur, $curmax )
	{
		return $cur >= count( $array ) ? $curmax : getmax( $array, $cur + 1, strlen( $array[$cur] ) > strlen( $array[$curmax] ) ? $cur : $curmax );
	}
	
	
	
	function imagepalettetotruecolor_wrapper( $src )
	{
		if( !imageistruecolor( $src ) )
		{
			$dst = imagecreatetruecolor(imagesx($src), imagesy($src));

			imagecopy($dst, $src, 0, 0, 0, 0, imagesx($src), imagesy($src));

			return $dst;
		}
		return $src;
	}
	function debug_img( $image, $type = 'png', $path = null )
	{
		switch( $type ){
			case 'jpeg':
				header('Content-Type: image/jpeg');
				imagejpeg( $image, $path, 100 );
			break;
			case 'png':
				header('Content-Type: image/png');
				imagepng( $image, $path, 0 );
			break;
		}
		die();
	}
	

	
	

	