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

if( !defined('IPS_CRON') && ( !defined('USER_ADMIN') || !USER_ADMIN ) ) die ("Hakier?");




/**
* Zapisywanie wspólnych danych dla wszystkich typów importu
*/

if( !empty( $_GET['clear_session'] ) )
{
	clear_import_sesion();
	ips_admin_redirect('uploading');
}


if( !empty( $_POST ) && isset( $_POST['import'] ) && !defined('IPS_PINIT_AJAX') && !isset( $_POST['post_files_to_add'] ))
{
	
	/**
	* Domyślne opcje dodawania
	*/
	$sess = array
	(
		'authors' => array
			(
				0 => USER_LOGIN,
			),
		'import_add_to' => 'wait',
		'import_pages' => 1,
		'import_pages_start' => 1,
		'import_pages_direct' => 'normal',
		'import_pages_limit' => 10,
		'import_watermark_cut' => false
	);

	
	foreach( $_POST['import'] as $option => $value )
	{
		if( !isset( $sess[$option] ) || $sess[$option] != $value )
		{
			$sess[$option] = $value;
		}
	}
	
	/**
	* Przypisujemy loginy autorów dla 
	* których mają być dodane materiały
	*/
	$sess['authors'] = array();
	$authors = explode( ',', $_POST['import']['authors'] );
	
	foreach( $authors as $author )
	{
		$sess['authors'][] = trim( $author );
	}
	
	/**
	* Dodawanie do poczekalni lub na główną
	*/
	if( empty( $sess['import_add_to'] ) )
	{
		$sess['import_add_to'] = 'wait';
	}
	
	/**
	* Adresy stron
	*/
	if( isset( $_POST['import']['import_urls'] ) )
	{
		$sess['import_pages'] = array();
		
		$adresy = $_POST['import']['import_urls'];
		
		if( !is_array( $adresy  ) )
		{
			$adresy = explode( "|", $_POST['import']['import_urls'] );
		}
		
		foreach( $adresy as $url )
		{	
			$url = trim( $url );
			
			if( filter_var( $url, FILTER_VALIDATE_URL ) )
			{
				$sess['import_pages'][] = $url;
			}
		}
		
		unset( $sess['import_urls'] );
	}
	
	if( isset( $_POST['import']['import_watermark_cut'] ) )
	{
		$watermark_cut = $_POST['import']['import_watermark_cut'];
		if( isset( $watermark_cut['cut'] ) && !empty( $watermark_cut['cut'] ) )
		{
			
			$sess['import_watermark_cut'] = array(
				'cut_direct' => ( isset( $watermark_cut['cut_direct'] ) && !empty( $watermark_cut['cut_direct'] ) ? $watermark_cut['cut_direct'] : 'cut_bottom' ),
				'cut_height' => ( isset( $watermark_cut['cut_height'] ) && !empty( $watermark_cut['cut_height'] ) ? (int)$watermark_cut['cut_height'] : 40 ),
			);
		}
		else
		{
			$sess['import_watermark_cut'] = false;
		}
	}
	Session::set( 'inc', $sess );
}








/**
* Pobieranie plików z folderu i import do serwisu
*/

function importFolderImages( $import_directory, $default_name, $upload_tags, $count, $import_category = false, $authors, $watermark_cut = false )
{	
	$dir = 'upload/';

	if( empty( $import_directory ) )
	{
		return array( 
			'error' => admin_msg( array(
				'alert' => 'Brak folderu o nazwie ' . $import_directory . ' lub folder jest pusty'
			) )
		);
		
	}
	if( !is_array( $import_directory ) )
	{
		$import_directory = array( 0 => $import_directory );
	}
	
	$lista_pikow  = array();
	
	foreach( $import_directory as $key => $dir )
	{

		$files = get_directory_files( trim( $dir, '/' ) . '/' );
		
		if( !empty( $files ) )
		{
			$lista_pikow = array_merge( $lista_pikow, $files );
		}
	}
	
	if( !$lista_pikow || empty($lista_pikow) )
	{
		return array( 
			'error' => admin_msg( array(
				'alert' => __('nothing_to_add')
			) )
		);
	}	
	
	$i = 1;
	
	shuffle( $lista_pikow );
	
	if( defined( 'IPS_IMPORT_FOLDER_SORT') )
	{
		foreach( $lista_pikow as $key => $plik )
		{
			preg_match_all('@([0-9]*)?(-)?([0-9]*)$@', $plik['nazwa'], $matches );

			$lista_pikow[$key]['new'] = $matches[1][0];
		}
		usort( $lista_pikow, make_comparer('new') );
		$lista_pikow = array_reverse( $lista_pikow );
	}
	
	$added = array();		
	foreach( $lista_pikow as $plik )
	{

		$file_name = basename(strtolower($plik['plik']));
		
		if( file_exists( ABS_PATH . '/upload/import/' . $file_name ) )
		{
			$file_name = rand().$file_name;
		}	
		
		if( copy( $plik['plik'], ABS_PATH . '/upload/import/' . $file_name ) )
		{
			
			$type = mime_content_type( ABS_PATH . '/upload/import/' . $file_name );
			
			$mime = getimagesize( ABS_PATH . '/upload/import/' . $file_name );
				
			if( is_array( $watermark_cut ) )
			{
				cut_watermark( ABS_PATH . '/upload/import/' . $file_name, $mime['mime'], $watermark_cut['cut_height'], $watermark_cut['cut_direct'] );
			} 	
			
			if ( strstr( $type, 'image/') !== false )
			{
				try{
					
					
					if( !empty( $default_name ) )
					{
						$nazwa = str_replace( '%', $i, $default_name );
					}
					elseif( !empty( $plik['nazwa'] ) )
					{
						$nazwa = $plik['nazwa'];
					}
					else
					{
						$nazwa = $i;
					}
					$i++;
					
					$_POST['upload_url'] = ABS_PATH . '/upload/import/' . $file_name;
					$data = $plik;
					$data['title'] = $nazwa;
					$data['upload_type'] = 'image';
					$data['upload_tags'] = extractCommonWords( $upload_tags );
					$data['top_line'] = $nazwa;
					$data['bottom_line'] = $nazwa;
					$data['user_login'] = $authors[rand(0, count( $authors ) - 1)];
					$data['upload_source'] = '';
					$data['import_source'] = false;
					$data['import_category'] = ( $import_category ? $import_category : false );
					
					$file_id = addImportedFile( $data );
					
					if( $file_id )
					{
						$row = PD::getInstance()->select( IPS__FILES,array(
							'id' => $file_id
						), 1);
						
						if( !file_exists( ips_img_path( $row, 'large' ) ) )
						{
							throw new Exception('file');
						}
					}
					$added[] = admin_msg( array(
						'success' => __s( 'import_file_succes', $nazwa ) 
					) ) ;
					
					@unlink( $plik['plik'] );
					
				} catch (Exception $e) {	
					
					if( !defined('IPS_CRON') )
					{
						$added[] = admin_msg( array(
							'alert' => __s( 'import_file_error', $nazwa ) . '<br >' . __( $e->getMessage() ) 
						) );
					}
					
					@unlink( $plik['plik'] );
				}
			}
			@unlink( ABS_PATH . '/upload/import/' . $file_name );

			if( isset( $count ) && !empty( $count ) && $i > $count )
			{
				return array( 
					'success' => admin_msg( array(
						'success' => __('import_files_added')
					) ),
					'files' => $added
				);
			}
		}
	}
	return array( 
		'success' => admin_msg( array(
			'success' => __('import_files_added')
		) ),
		'files' => $added
	);	
}	

/***
* Extract words/tags from string
*/
function extractCommonWords( $string )
{

	$string = preg_replace( array( 
		'/(?!\s)(?!,)(?!([0-9]+))[\P{L}+]/u', '/\s\s+/i' 
	), array(
		'',' '
	), trim( $string ) );

	$string = mb_strtolower( $string, 'utf-8' );

	$matchWords = array_unique( explode( ' ', str_replace( ',', ' ', $string ) ) );

	foreach ( $matchWords as $key => $item )
	{
	    if ( $item == '' || !isset( $item[3] ) || is_numeric($item) || isset( $item[25] ) ) 
		{
			unset( $matchWords[$key] );
	    }
		if( strlen( $item ) < 4  )
		{
			unset( $matchWords[$key] );
		}
	}  

	$wordCountArr = array();
	if ( is_array( $matchWords ) ) 
	{
		foreach ( $matchWords as $key => $val )
		{
			$wordCountArr[$val] = isset( $wordCountArr[$val] ) ? $wordCountArr[$val] + 1 : 1 ;
		}
	}
	arsort( $wordCountArr );
	
	$wordCountArr = array_slice( $wordCountArr, 0, 10 );

	return is_numeric( current( $wordCountArr ) ) ? array_keys( $wordCountArr ) : $wordCountArr;
}

/**
* 100% unique array
*/
function arrayUnique($array, $key_sort)
{
    $arrayRewrite = array();
    foreach( $array as $item )
	{
		$key = md5( $item[ $key_sort ] );
		if( !isset( $arrayRewrite[ $key ] ) )
		{
			$arrayRewrite[$key] = $item;
		}
    }
    return array_values( $arrayRewrite );
}

/**
* Pobieranie X bitów obrazka w celu weryfikacji.
*/	
function ranger( $url )
{
    $headers = array(
		"Range: bytes=0-32768"
    );
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);
    curl_close($curl);
    return $data;
}

/**
* Obcinanie obrazka w celu usunięcia znaku wodnego
*/
function cut_watermark( $path, $mime, $ile = 30, $kierunek )
{
	list($width, $height) = getimagesize($path);
		
		$cut_height = 0;
		
		if( $kierunek == 'cut_top' )
		{
			$cut_height = $ile;
		}
		
		switch( $mime )
		{
			case 'image/gif':
				return true;
			break;
			case 'image/png':
				$image = @imagecreatefrompng($path);
				$new_image = imagecreatetruecolor($width, $height - $ile);
				imagecopy($new_image, $image, 0, 0, 0, $cut_height, $width, $height);
				unlink($path);
				imagepng($new_image, $path, Config::get('images_compress', 'png') );
			break;
			case 'image/jpeg':
				$image = @imagecreatefromjpeg($path);
				$new_image = imagecreatetruecolor($width, $height - $ile);
				imagecopy($new_image, $image, 0, 0, 0, $cut_height, $width, $height);
				unlink($path);
				imagejpeg( $new_image, $path,  Config::get('images_compress', 'jpg') );
			break;
			default:
				return false;
			break;
		}
	
	unset( $image, $new_image );
	
	return true;
}


/*** Dla konkretnych serwisów z obrazkami ***/
function funnyjunk( $match )
{
	
	if($match[2] = 'pictures')
	{
		return 'http://static.fjcdn.com/pictures/string_'.$match[5].'.'.$match[6];
	}
	elseif($match[2] = 'gifs')
	{
		return 'http://static.fjcdn.com/gifs/string_'.$match[5].'.'.$match[6];
	}
	return false;
}

function forgifs($match){
	
	$fix = explode("-", $match[2]);
	if((int)$fix[1] > 9) return false;
	$fix = ((int)$fix[0] - 1).'-1';
	return 'http://forgifs.com/gallery/d/'.$fix.'/'.$match[3].'.'.$match[4];
}
/*** ***/


function sprawdz_image($img){
    
	static $imgExts = array( "gif", "jpg", "jpeg", "png" );
	
	if ( in_array( pathinfo($url, PATHINFO_EXTENSION), $imgExts ) )
	{
		return true;
	}
	$hdrs = @get_headers($img);
    return is_array($hdrs) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/',$hdrs[0]) : false;
}

function pasuje($img, $class){

	$urlmatch = array(
		array(
			'preg' => '([\/a-zA-Z]{1,9})?d\/([-\/0-9]{1,9})\/([a-zA-Z0-9\-]*?).(gif|jpg|jpeg|png)',
			'link' => 'http://forgifs.com',
			'source_url' => '4GIFS',
		),
		array(
			'preg' => 'http:\/\/(?:www\.)?([a-zA-Z0-9]{1,18}).cloudfront.net\/photo\/([.-_a-zA-Z0-9\-]*?).(gif|jpg|jpeg|png)',
			'link' => '',
			'source_url' => '9GaG',
		),
		array(
			'preg' => 'http:\/\/(?:www\.)?([a-zA-Z0-9]*).files.wordpress.com\/([0-9]{4})\/([0-9]{1,2})\/([a-zA-Z\-]*).(gif|jpg|jpeg|png)$',
			'link' => '',
			'source_url' => 'SenorGif',
		),
		array(
			'preg' => 'http:\/\/(?:www\.)?iseeahappyface.com\/upload\/([0-9a-zA-Z\-]*).(gif|jpg|jpeg|png)$',
			'link' => '',
			'source_url' => 'IsHappyFace',
		),
		array(
			'preg' => 'http:\/\/(?:www\.)?([0-9a-z]*).fjcdn.com\/thumbnails\/(pictures|gifs)\/([0-9a-zA-Z]{2})\/([0-9a-zA-Z]{2})\/([_0-9a-zA-Z\-]*).(gif|jpg|jpeg|png)$',
			'link' => '',
			'source_url' => 'Funnyjunk',
		),
		array(
			'preg' => '(http\:|htts\:)?(//([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)([^\s]+(\.(?i)(jpe?g|png|gif|bmp)))',
			'link' => '',
			'source_url' => 'Inne',
		),
		
	);

	foreach ( $urlmatch as $stub )
	{ 
		if( preg_match('~'.$stub['preg'].'~imu', $img, $match) )
		{
			if(strpos($match[0], 'fjcdn'))
				return array('img' => funnyjunk($match), 'site' => $stub['source_url']);
			elseif($stub['link'] == 'http://forgifs.com')
				return array('img' => forgifs($match), 'site' => $stub['source_url']);
			else
				return array('img' => $stub['link'].$match[0], 'site' => $stub['source_url']);
				
				//return sprawdz_image($stub['link'].$match[0]) ? array('img' => $stub['link'].$match[0], 'site' => $stub['source_url']) : false;
		}
	}
	return false;
}



function playlistLink($link)
{

	$url = parse_url( $link );
	
	parse_str( $url['query'], $q );
	
	if( isset($q['list']) && isset($q['list'][10]) )
	{
		if( strtolower(substr($q['list'], 0, 2)) == 'pl' )
		{
			$q['list'] = substr($q['list'], 2);
		}
		
		return 'http://gdata.youtube.com/feeds/api/playlists/' . $q['list'];
	//http://gdata.youtube.com/feeds/api/playlists/
	}
	return false;
}
/**
* Usuwanie pliuków z folderu tymczasowego importu.
*/
function usun_pliki($dirname)
{
    
	if (is_dir($dirname)) $dir_handle = opendir($dirname);
    if (!$dir_handle) return false;
    
	while($file = readdir($dir_handle))
	{
		if ( $file != "." && $file != ".." )
		{
			if ( !is_dir( $dirname . '/' . $file ) && strpos( $file, 'import_draft_' ) === false )
			{
				unlink($dirname . '/' . $file);
			}
			else
			{
				//usun_pliki( $dirname . '/' . $file);
			}  
		}
	}
	closedir($dir_handle);
	return true;
}


/**
* Czyszczenie sesji importu
*/
function clear_import_sesion()
{
	Session::clear( 'inc' );
	Session::clear( 'ready_files' );
	usun_pliki( ABS_PATH . '/upload/import/' );
}
/**
* Wyszukiwanie linkw Zoutube w treści
*/
function extract_youtube_links( $text )
{
	
	/* preg_match_all('~
        # Match non-linked youtube URL in the wild. (Rev:20111012)
        https?://         # Required scheme. Either http or https.
        (?:[0-9A-Z-]+\.)? # Optional subdomain.
        (?:               # Group host alternatives.
          youtu\.be/      # Either youtu.be,
        | youtube\.com    # or youtube.com followed by
          \S*             # Allow anything up to VIDEO_ID,
          [^\w\-\s]       # but char before ID is non-ID char.
        )                 # End host alternatives.
        ([\w\-]{11})      # $1: VIDEO_ID is exactly 11 chars.
        (?=[^\w\-]|$)     # Assert next char is non-ID or EOS.
        (.*)~ix',
        $text, $match ); */
		
	preg_match_all('~(?:https?://(?:[0-9A-Z-]+\.)?(?:youtu\.be/|youtube\.com\S*[^\w\-\s]))([\w\-]{11})(?=[^\w\-]|$)(.*)~ix', $text, $match );
	
	preg_match_all('~(/watch\?v=[\w\-]{11})~ix', $text, $match_2 );
	
    
	$text = implode(" ", array_merge( $match[0], $match_2[0] ) );
	
	preg_match_all('~(?:v/|watch\?v=|embed/|youtu\.be/)([\w\-\_]{11})~ix', $text, $match );
	
	return array_unique( $match[1] );
}

function extract_youtube_links_ips_self( $text, $videos )
{
	
	$divs = extract_html_tags( $text, 'div' );
	
	$videos_ready = $exists = array();
	foreach ( $divs as $key => $div )
	{
		
		if( $div->getAttribute('class') == 'file_page' )
		{
			$docSave = new DOMDocument( '1.0',  Session::getNonEmpty( 'encoding', false, 'UTF-8' ) );
			
			$domNode = $docSave->importNode( $div, true );
			$docSave->appendChild( $domNode );
			$html = $docSave->saveHTML();
			
			preg_match_all('/<div class="file-title">([^<]+)<a([^>]+)>([^<]+)/smD', $html, $matches );
			if( isset( $matches[3] ) && !empty( $matches[3] ) )
			{
				$title = $matches[3];
			}
			
			$video_id = extract_youtube_links( $html );
			
			if( isset( $title[0] ) && !empty( $video_id ) )
			{

				$exists[] = $video_id[0];
				$videos_ready[] = array(
					'title' => mb_convert_encoding( $title[0], 'UTF-8', 'HTML-ENTITIES' ),
					'link' => $video_id[0]
				);
			};
		}
		elseif( strpos( $div->getAttribute('class'), 'demot_pic') !== false )
		{
			
			$docSave = new DOMDocument( '1.0',  Session::getNonEmpty( 'encoding', false, 'UTF-8' ) );
			
			$domNode = $docSave->importNode( $div, true );
			$docSave->appendChild( $domNode );
			$html = $docSave->saveHTML();
			
			preg_match_all('/alt="([^"]+)"/smD', $html, $matches );
			
			if( isset( $matches[1] ) && !empty( $matches[1] ) )
			{
				$title = $matches[1];
			}
			;
			$video_id = extract_youtube_links( $html );
			
			if( isset( $title[0] ) && !empty( $video_id ) )
			{

				$exists[] = $video_id[0];
				$videos_ready[] = array(
					'title' => mb_convert_encoding( $title[0], 'UTF-8', 'HTML-ENTITIES' ),
					'link' => $video_id[0]
				);
			};
			
		}
		
	}
	
	preg_match_all('~"\/watch\?v=([\w\-]{11})"~ix', $text, $match );

	foreach( $videos as $key => $video )
	{
		if( in_array( $video, $exists ) )
		{
			unset( $videos[$key] );
		}
	}

	return array_merge( $videos_ready, $videos );
}




function uploadBoardList( $board_id = false )
{
	$board = new Board();
	$boards = $board->userBoards( USER_ID );
	
	if( empty( $boards ) )
	{
		ips_redirect( false, 'import_no_boards');
	}
	
	foreach( $boards as $key => $row )
	{
		$boards[$key] = '<option '. ( $board_id == $row['board_id'] ? 'selected="selected"' : '' ) .' value="' . $row['board_id'] . '">' . $row['board_title'] . '</option>';
	}
	
	return implode( '', $boards );
}

function addImportForm( $route, $import_type )
{
	echo Templates::getInc()->getTpl( '/__admin/import_template.html', array(
		'boards_list' => ( IPS_VERSION == 'pinestic' ? uploadBoardList() : '' ),
		'import_type' => $import_type,
		'form_action' => ( $route ? admin_url( $route, 'auto-add=start' ) : '' ) 
	) );
}


function addImportedFilePinit( $data )
{
	$data['pin_description'] = $data['top_line'];
	
	$data['upload_image'] = $data['img'];
	
	$data['tags'] = $data['upload_tags'];
	
	$data['pin_from_url'] = true;
	
	$pin = new Pin();
	$response = $pin->create( $data );
}

/**
* Funkcja odpowiedzialna za dodawanie pliku z importu bez względu na rodzaj
*/
function addImportedFile( $data )
{
	if( IPS_VERSION == 'pinestic' )
	{
		return addImportedFilePinit( $data );
	}
	
	$upload = new Upload_Extended();
	
	$_POST['title'] = $data['title'];
	
	$_POST['top_line'] = isset( $data['top_line'] ) ? $data['top_line'] : $data['title'];

	if( !isset( $data['upload_subtype'] ) )
	{
		$data['upload_subtype'] = 'image';
	}
	
	$upload->makeFileConfig( $data['upload_type'], $data['upload_subtype'] );
	
	if( Config::get('upload_tags') )
	{
		$upload->setUserTags( $data['upload_tags'], '');
	}
	
	if( $data['upload_subtype'] == 'mp4' || $data['upload_type'] == 'video' )
	{	
		/**
		* Import pliku Video
		*/
		if( $data['upload_subtype'] == 'mp4' )
		{
			$upload_mp4 = new Upload_Mp4();

			$video_data = $upload_mp4->getUpload( array(
				'url' => $data['upload_video'],
			));
			
			if( empty( $video_data['upload_video'] ) )
			{
				throw new Exception('err_video');
			}
		}
		else
		{
			$video = new Upload_Video();
			
			$upload_video = $data['upload_video'];
			/* 
			* Obekt Embed zawsze zwraca 
			* image(z serwisu video lub z serwera). 
			*/
			$video_data = $video->videoParseUrl( $data['upload_video'] );
			
		}
	
		if ( !$video_data )
		{
			/* 
			* Zwracamy błąd ponieważ link 
			* video nie pasuje do żadnego schematu. 
			*/
			throw new Exception('err_video_link');
		}

		
		$upload->uploadVideoImage( $video_data['image'] );
		
		/* 
		* Przypisujemy link z klasy Embed w formie 
		* odpowiedniej do bezpośredniego umieszczenia w <embed> 
		*/
		$upload_video = $video_data['upload_video'];
		
		$data['import_source'] = $video_data['upload_video'];
	}
	
	
	if( isset( $data['link'] ) )
	{
		$upload->uploadFile( $data['link'] );
	}
	
	elseif( isset( $_POST['upload_url'] ) )
	{
		$upload->uploadFile();
	}
	
	$file = $upload->Load( IMG_PATH . "/" . $upload->getName() );
	
	$file['upload_type'] = $data['upload_type'];
	
	if( isset( $data['import_add_to'] ) )
	{
		$file['IPS_IMPORT_ACTIV'] = ( $data['import_add_to'] == 'main' ? 1 : 0 );
	}
	elseif( isset( $data['IPS_IMPORT_ACTIV'] ) )
	{
		$file['IPS_IMPORT_ACTIV'] = $data['IPS_IMPORT_ACTIV'];
	}
	
	$file['font_color'] = Config::getArray( 'upload_demotywator_text', 'font_color' );
	$file['font'] = Config::getArray( 'upload_demotywator_text', 'font' );
	$file['title'] = $data['title'];
	$file['top_line'] = has_value( 'top_line', $data );
	$file['bottom_line'] = has_value( 'bottom_line', $data );
	$file['upload_adult'] = 0;
	$file['upload_source'] = has_value( 'upload_source', $data );
	$file['private'] = false;
	$file['user_login'] = $data['user_login'];
	$file['category_id'] = has_value( 'import_category', $data );
	
	if( $data['upload_subtype'] == 'mp4' )
	{
		$upload_mp4->mp4Image( $video_data, $file );
	}
	
	$upload->configuration( $file );
	$upload->initCreateImage();

	if( $upload->addUploadFile( $file, $upload_video ) )
	{
		$file_id = $upload->file_add_id;
		$upload = null;
		
		if( !empty( $data['import_source'] ) && isset( $data['site'] ) )
		{
			global $PD;
		
			$PD->insert( 'upload_imported', array(
				'title' => $file['title'], 
				'link' => $data['import_source'], 
				'source_url' => htmlentities( $data['site'] ), 
				'upload_id' => $file_id
			) );
		}
		
		return $file_id;
	}
	
	$upload = null;
	
	return false;
}
if( !function_exists( 'mime_content_type' ) )
{

    function mime_content_type($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );
		
        $ext = strtolower( substr(strrchr($filename, '.'), 1) );
		
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        elseif (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        else {
            return 'application/octet-stream';
        }
    }
}

?>