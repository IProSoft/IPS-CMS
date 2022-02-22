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

	function ips_crypt($ciag) {return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5(AUTH_KEY), $ciag, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)))); }
	function ips_decrypt($ciag) {return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(AUTH_KEY), base64_decode($ciag), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))); }
	

	/**
	 * Przekierowanie do innej strony
	 *
	 * @param bool|string $location - adres przekierowania
	 * @param bool|string $message -wiadmośc wyświetlana po przekierowaniu
	 *
	 * @param bool $header
	 * @return void
	 */
	function ips_redirect( $location = false, $message = false, $header = false )
	{
		if( $message )
		{
			/**
			* Zapisanie wiadomości w sesji.
			*/
			ips_message( $message );
		}
		
		if( empty( $location ) )
		{
			$parsed = isset( $_SERVER['HTTP_REFERER'] ) ?  parse_url( $_SERVER['HTTP_REFERER'] ) : false;
			
			if( $parsed && $parsed['host'] == $_SERVER['HTTP_HOST'] && strpos( $_SERVER['HTTP_REFERER'], 'login' ) == false && $parsed['path'] != '/' )
			{
				$location = ABS_URL . ltrim( $parsed['path'], '/' );
			}
			else
			{	
				$location = ABS_URL;
			}
		}
		else
		{
			$location = ( substr( $location, 0, 4 ) !== 'http' ? ABS_URL : '' ) . trim( $location );
		}
		
		if( $header )
		{
			switch( $header )
			{
				case '301':
					header( 'HTTP/1.0 301 Moved Permanently' );
				break;
				case '404':
					header( 'HTTP/1.0 404 Not Found' );
				break;
			}
		}
		
		header( 'Location: ' . $location );
		
		exit(0);
	}
	
	function ips_admin_redirect( $route, $path = false, $message = false )
	{
		return ips_redirect( 
			admin_url( $route, $path ), 
			$message
		);
	}
	
	function ips_error_redirect( $route, $message = false, $log_file = 'php.log', $global = false )
	{
		ips_log( ips_backtrace( $global ), 'logs/' . basename( $log_file ) );
		
		return ips_redirect( 
			$route, 
			$message
		);
	}
	
	function admin_url( $route, $path = false )
	{
		return ABS_URL 
			. 'admin/' 
			. ( $route != '/' ? 'route-' . $route : 'admin.php' )  
			. ( $path ? '?' . $path   : '' );
	}
	/**
	* Get nice alert formatted message
	*/
	
	function ips_message( $msg_content, $return_message = false )
	{
		$msg_type = 'normal';
		
		if( is_array( $msg_content ) )
		{
			$msg_type = key( $msg_content );
			$msg_content = current( $msg_content );
		}
		
		if( $msg_type != 'user_defined' )
		{
			$msg_content = '<div class="ips-message msg-' . $msg_type . '"><span>' . __( $msg_content ) .' </span></div>';
		}

		if( $return_message )
		{
			return $msg_content;
		}
		
		Session::setFlash( $msg_content );
	}
	
	function ips_search_array( $key, $value, $array )
	{
		foreach ( $array as $k => $val )
		{
			if ( $val[$key] == $value )
			{
				return $val;
			}
		}
		return null;
	}
	
	/**
	* Sprawdzanie czy użytkownik ma ukończone 18 lat
	*/
	function isAdult()
	{				
		if( Cookie::exists('adult') )
		{
			return true;
		}
		
		if( $user_personalize = Cookie::get('user_personalize') )
		{
			$cookie = json_decode( $user_personalize, true );
			if( isset( $cookie['show_adult'] ) && $cookie['show_adult'] )
			{
				return true;
			}
		}
		
		if( USER_LOGGED )
		{		
			if( Session::has('adult') )
			{		
				return true;		
			}
			else
			{
				$user = getUserInfo( USER_ID, true );

				if( !isset( $user['user_birth_date'] ) )
				{
					error_log( (is_array( $user ) ? serialize( $user ) : $user ) . '_' . USER_ID );
					return true;
				}
				if( $user['user_birth_date'] < ( date('Y-m-d', strtotime('-18 years') ) ) )
				{
					return true;
				}	
			}
		}			
		return false;
	}
	
	function ips_user_avatar( $img, $type = 'url' )
	{
		switch( $type )
		{
			case 'url':
				return ABS_URL . 'upload/img_avatar/' . $img;
			break;
			case 'file':
				return ABS_PATH . '/upload/img_avatar/' . $img;
			break;
		}
	};
	/*
	* Listowanie czcionek dla pól input itp
	*/
	function fontList( $font_selected )
	{
		$fonts = Config::get( 'web_fonts_config', 'installed' );
		
		$options = '';
			
		foreach ( $fonts as $font_name => $font )
		{
			$options .= '<option value="'.$font_name.'" '.( $font_selected == $font_name ? 'selected="selected"' : '' ).'>'.str_replace( array('-', '_' ), ' ', $font_name ).'</option>';
		}
			
		return $options;	
	}

	
	/*
	* Listowanie czcionek dla pól input itp
	*/
	function getFonts()
	{
		$fonts = Config::get( 'web_fonts_config', 'installed' );
		
		$options = array();
		
		ksort( $fonts );
		
		foreach ( $fonts as $font_name => $font )
		{
			$options[ $font_name ] = str_replace( array('-', '_' ), ' ', $font_name );
		}
		
		return $options;	
	}
	

	
	/**
	* For IPS-SELF SITES
	*/
	function ips_time_formatter( $date )
	{

		  $timeStrings = array(   
				'przed chwilą',          
				'sekundę', 
				array(
						'sekund',
						'sekundy',
					),    
				'minutę', 
					array(
						'minut',
						'minuty'
					), 
				'godzinę', 
					array(
						'godzin',
						'godziny'
					),
				'dzień', 
				'dni',         
				'tydzień', 
					array(
						'tygodni',
						'tygodnie'
					),      
				'miesiąc', 
					array(
						'miesięcy',
						'miesiące'
					),      
				'rok',
				array(
						'lat',
						'lata',
					)
			);      
		  $debug = true;
		  $sec = time() - (( strtotime($date)) ? strtotime($date) : $date);
		  
		  if ( $sec <= 0) return $timeStrings[0];
		  
		  if ( $sec < 2) return $timeStrings[1];
		  if ( $sec < 60) return ips_time_formatter_helper( floor($sec+ 0.5), $timeStrings[2] );
		  
		  $min = $sec / 60;
		  if ( floor($min+0.5) < 2) return $timeStrings[3];
		  if ( $min < 60) return ips_time_formatter_helper( floor($min+ 0.5), $timeStrings[4] );

		  $hrs = $min / 60;
		  if ( floor($hrs+0.5) < 2) return $timeStrings[5];
		  if ( $hrs < 24) return ips_time_formatter_helper( floor($hrs+ 0.5), $timeStrings[6] );
		  
		  $days = $hrs / 24;

		  if ( floor($days+0.5) < 2) return $timeStrings[7];
		  if ( $days < 7) return floor($days+0.5)." ".$timeStrings[8];
		  
		  $weeks = $days / 7;
		  if ( floor($weeks+0.5) < 2) return $timeStrings[9];
		  if ( $weeks < 4) return ips_time_formatter_helper( floor($weeks+ 0.5), $timeStrings[10] );
		  
		  $months = $weeks / 4;
		  if ( floor($months+0.5) < 2) return $timeStrings[11];
		  if ( $months < 12) return ips_time_formatter_helper( floor($months+ 0.5), $timeStrings[12] );
		  
		  $years = $weeks / 51;
		  if ( floor($years+0.5) < 2) return $timeStrings[13];
		  
		return ips_time_formatter_helper( floor($years+ 0.5), $timeStrings[14] );
	} 
	
	function ips_time_formatter_helper( $minutes, $transl )
	{
		switch( $minutes )
		{
			case ( $minutes >= 0 && $minutes <= 4 ):
			case ( $minutes >= 22 && $minutes <= 24 ):
			case ( $minutes >= 32 && $minutes <= 34 ):
			case ( $minutes >= 42 && $minutes <= 44 ): 
			case ( $minutes >= 52 && $minutes <= 54 ): 
				return $minutes .' ' . $transl[1]; 
			break;
			default: 
				return $minutes .' ' . $transl[1]; 
			break;
		}
	} 

	
	function getMinutes($minutes)
	{
		switch( $minutes )
		{
			case 0: 
				return __a( 'date_time_minutes', 'zero' ); 
			break;
			case 1: 
				return __a( 'date_time_minutes', 'one' ); 
			break;
			case ( $minutes >= 2 && $minutes <= 4 ):
			case ( $minutes >= 22 && $minutes <= 24 ):
			case ( $minutes >= 32 && $minutes <= 34 ):
			case ( $minutes >= 42 && $minutes <= 44 ): 
			case ( $minutes >= 52 && $minutes <= 54 ): 
				return $minutes . ' ' . __a( 'date_time_minutes', 'more' ); 
			break;
			default: 
				return $minutes . ' ' . __a( 'date_time_minutes', 'couple' ); 
			break;
		}
		return -1;
	}

	function formatDate( $data )
	{
		$timestamp = strtotime( $data );

        $diff = time() - $timestamp;

        $minutes = floor( $diff/60	);  

        if ( $minutes <= 60 )
		{
			return getMinutes( $minutes );  
        }
		
		$data = date_parse_from_format( "Y-m-d H:i:s", $data );

		return $data['day'] . ' ' . __a( 'date_time_months', $data['month'] ) . ' ' . $data['year'] . ' ' . str_pad( $data['hour'], 2, 0, STR_PAD_LEFT). ':' . str_pad( $data['minute'], 2, 0, STR_PAD_RIGHT);
	}
	
	/**
	* Funkcja generująca przyjazne linki
	*/
	function seoLink( $id = false, $link = false, $rewrited_link = null )
	{
		if( is_numeric( $id ) && IPS_VERSION == 'pinestic' )
		{
			$id = 'pin/' . $id;
		}
		
		if( $rewrited_link )
		{
			return ABS_URL . $id . '/' . $rewrited_link;
		}
		
		$link = preg_replace( array( 
				"/[^ \w]+/", 
				"/(\s\s+|\t|\n)/", 
				"/_+/", "/\-+/", 
				"/\s+/"
			), 
			IPS_LINK_FORMAT_PCT, 
			toAscii( $link )
		);

		if( empty( $link ) )
		{
			$link = md5( $id );
		}
		
		$link = substr( $link, 0, 100 );
		
		if( is_numeric( $id ) )
		{
			return strtolower( ABS_URL . $id . '/' . $link . IPS_LINK_FORMAT );
		}
		else
		{
			return strtolower( $link . IPS_LINK_FORMAT );
		}
	}
	
	function xy( $page, $limit )
	{
		return array
		(
			1 => ( empty( $page ) ? 0 : $page * $limit - $limit ),
			2 => $limit
		);
	}	
		
	

		
	function toAscii( $str )
	{
		$charsArray = array(
            'a'    => array(
                            'à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ',
                            'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ', 'ä', 'ā', 'ą',
                            'å', 'α', 'ά', 'ἀ', 'ἁ', 'ἂ', 'ἃ', 'ἄ', 'ἅ', 'ἆ',
                            'ἇ', 'ᾀ', 'ᾁ', 'ᾂ', 'ᾃ', 'ᾄ', 'ᾅ', 'ᾆ', 'ᾇ', 'ὰ',
                            'ά', 'ᾰ', 'ᾱ', 'ᾲ', 'ᾳ', 'ᾴ', 'ᾶ', 'ᾷ', 'а', 'أ'),
            'b'    => array('б', 'β', 'Ъ', 'Ь', 'ب'),
            'c'    => array('ç', 'ć', 'č', 'ĉ', 'ċ'),
            'd'    => array('ď', 'ð', 'đ', 'ƌ', 'ȡ', 'ɖ', 'ɗ', 'ᵭ', 'ᶁ', 'ᶑ',
                            'д', 'δ', 'د', 'ض'),
            'e'    => array('é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ',
                            'ệ', 'ë', 'ē', 'ę', 'ě', 'ĕ', 'ė', 'ε', 'έ', 'ἐ',
                            'ἑ', 'ἒ', 'ἓ', 'ἔ', 'ἕ', 'ὲ', 'έ', 'е', 'ё', 'э',
                            'є', 'ə'),
            'f'    => array('ф', 'φ', 'ف'),
            'g'    => array('ĝ', 'ğ', 'ġ', 'ģ', 'г', 'ґ', 'γ', 'ج'),
            'h'    => array('ĥ', 'ħ', 'η', 'ή', 'ح', 'ه'),
            'i'    => array('í', 'ì', 'ỉ', 'ĩ', 'ị', 'î', 'ï', 'ī', 'ĭ', 'į',
                            'ı', 'ι', 'ί', 'ϊ', 'ΐ', 'ἰ', 'ἱ', 'ἲ', 'ἳ', 'ἴ',
                            'ἵ', 'ἶ', 'ἷ', 'ὶ', 'ί', 'ῐ', 'ῑ', 'ῒ', 'ΐ', 'ῖ',
                            'ῗ', 'і', 'ї', 'и'),
            'j'    => array('ĵ', 'ј', 'Ј'),
            'k'    => array('ķ', 'ĸ', 'к', 'κ', 'Ķ', 'ق', 'ك'),
            'l'    => array('ł', 'ľ', 'ĺ', 'ļ', 'ŀ', 'л', 'λ', 'ل'),
            'm'    => array('м', 'μ', 'م'),
            'n'    => array('ñ', 'ń', 'ň', 'ņ', 'ŉ', 'ŋ', 'ν', 'н', 'ن'),
            'o'    => array('ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ',
                            'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ', 'ø', 'ō', 'ő',
                            'ŏ', 'ο', 'ὀ', 'ὁ', 'ὂ', 'ὃ', 'ὄ', 'ὅ', 'ὸ', 'ό',
                            'ö', 'о', 'و', 'θ'),
            'p'    => array('п', 'π'),
            'r'    => array('ŕ', 'ř', 'ŗ', 'р', 'ρ', 'ر'),
            's'    => array('ś', 'š', 'ş', 'с', 'σ', 'ș', 'ς', 'س', 'ص'),
            't'    => array('ť', 'ţ', 'т', 'τ', 'ț', 'ت', 'ط'),
            'u'    => array('ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ',
                            'ự', 'ü', 'û', 'ū', 'ů', 'ű', 'ŭ', 'ų', 'µ', 'у'),
            'v'    => array('в'),
            'w'    => array('ŵ', 'ω', 'ώ'),
            'x'    => array('χ'),
            'y'    => array('ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'ÿ', 'ŷ', 'й', 'ы', 'υ',
                            'ϋ', 'ύ', 'ΰ', 'ي'),
            'z'    => array('ź', 'ž', 'ż', 'з', 'ζ', 'ز'),
            'aa'   => array('ع'),
            'ae'   => array('æ'),
            'ch'   => array('ч'),
            'dj'   => array('ђ', 'đ'),
            'dz'   => array('џ'),
            'gh'   => array('غ'),
            'kh'   => array('х', 'خ'),
            'lj'   => array('љ'),
            'nj'   => array('њ'),
            'oe'   => array('œ'),
            'ps'   => array('ψ'),
            'sh'   => array('ш'),
            'shch' => array('щ'),
            'ss'   => array('ß'),
            'th'   => array('þ', 'ث', 'ذ', 'ظ'),
            'ts'   => array('ц'),
            'ya'   => array('я'),
            'yu'   => array('ю'),
            'zh'   => array('ж'),
            '(c)'  => array('©'),
            'A'    => array('Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ',
                            'Ặ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ', 'Ä', 'Å', 'Ā',
                            'Ą', 'Α', 'Ά', 'Ἀ', 'Ἁ', 'Ἂ', 'Ἃ', 'Ἄ', 'Ἅ', 'Ἆ',
                            'Ἇ', 'ᾈ', 'ᾉ', 'ᾊ', 'ᾋ', 'ᾌ', 'ᾍ', 'ᾎ', 'ᾏ', 'Ᾰ',
                            'Ᾱ', 'Ὰ', 'Ά', 'ᾼ', 'А'),
            'B'    => array('Б', 'Β'),
            'C'    => array('Ç','Ć', 'Č', 'Ĉ', 'Ċ'),
            'D'    => array('Ď', 'Ð', 'Đ', 'Ɖ', 'Ɗ', 'Ƌ', 'ᴅ', 'ᴆ', 'Д', 'Δ'),
            'E'    => array('É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ',
                            'Ệ', 'Ë', 'Ē', 'Ę', 'Ě', 'Ĕ', 'Ė', 'Ε', 'Έ', 'Ἐ',
                            'Ἑ', 'Ἒ', 'Ἓ', 'Ἔ', 'Ἕ', 'Έ', 'Ὲ', 'Е', 'Ё', 'Э',
                            'Є', 'Ə'),
            'F'    => array('Ф', 'Φ'),
            'G'    => array('Ğ', 'Ġ', 'Ģ', 'Г', 'Ґ', 'Γ'),
            'H'    => array('Η', 'Ή'),
            'I'    => array('Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị', 'Î', 'Ï', 'Ī', 'Ĭ', 'Į',
                            'İ', 'Ι', 'Ί', 'Ϊ', 'Ἰ', 'Ἱ', 'Ἳ', 'Ἴ', 'Ἵ', 'Ἶ',
                            'Ἷ', 'Ῐ', 'Ῑ', 'Ὶ', 'Ί', 'И', 'І', 'Ї'),
            'K'    => array('К', 'Κ'),
            'L'    => array('Ĺ', 'Ł', 'Л', 'Λ', 'Ļ'),
            'M'    => array('М', 'Μ'),
            'N'    => array('Ń', 'Ñ', 'Ň', 'Ņ', 'Ŋ', 'Н', 'Ν'),
            'O'    => array('Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ',
                            'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ', 'Ö', 'Ø', 'Ō',
                            'Ő', 'Ŏ', 'Ο', 'Ό', 'Ὀ', 'Ὁ', 'Ὂ', 'Ὃ', 'Ὄ', 'Ὅ',
                            'Ὸ', 'Ό', 'О', 'Θ', 'Ө'),
            'P'    => array('П', 'Π'),
            'R'    => array('Ř', 'Ŕ', 'Р', 'Ρ'),
            'S'    => array('Ş', 'Ŝ', 'Ș', 'Š', 'Ś', 'С', 'Σ'),
            'T'    => array('Ť', 'Ţ', 'Ŧ', 'Ț', 'Т', 'Τ'),
            'U'    => array('Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ',
                            'Ự', 'Û', 'Ü', 'Ū', 'Ů', 'Ű', 'Ŭ', 'Ų', 'У'),
            'V'    => array('В'),
            'W'    => array('Ω', 'Ώ'),
            'X'    => array('Χ'),
            'Y'    => array('Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ', 'Ÿ', 'Ῠ', 'Ῡ', 'Ὺ', 'Ύ',
                            'Ы', 'Й', 'Υ', 'Ϋ'),
            'Z'    => array('Ź', 'Ž', 'Ż', 'З', 'Ζ'),
            'AE'   => array('Æ'),
            'CH'   => array('Ч'),
            'DJ'   => array('Ђ'),
            'DZ'   => array('Џ'),
            'KH'   => array('Х'),
            'LJ'   => array('Љ'),
            'NJ'   => array('Њ'),
            'PS'   => array('Ψ'),
            'SH'   => array('Ш'),
            'SHCH' => array('Щ'),
            'SS'   => array('ẞ'),
            'TH'   => array('Þ'),
            'TS'   => array('Ц'),
            'YA'   => array('Я'),
            'YU'   => array('Ю'),
            'ZH'   => array('Ж'),
            ' '    => array("\xC2\xA0", "\xE2\x80\x80", "\xE2\x80\x81",
                            "\xE2\x80\x82", "\xE2\x80\x83", "\xE2\x80\x84",
                            "\xE2\x80\x85", "\xE2\x80\x86", "\xE2\x80\x87",
                            "\xE2\x80\x88", "\xE2\x80\x89", "\xE2\x80\x8A",
                            "\xE2\x80\xAF", "\xE2\x81\x9F", "\xE3\x80\x80"),
        );
		
		foreach ( $charsArray as $key => $value )
		{
            $str = str_replace( $value, $key, $str );
        }
		
		return $str;
	}

	
	function clearAscii( $string )
	{
			// usuń wszystko co jest niedozwolonym znakiem
			//$string = str_replace('/[^_0-9a-zA-Z\-]+/', '', toAscii( $string ) );
			
			// Remove all characters that are not the separator, letters, numbers, or whitespace.
			$string = preg_replace('![^\-\pL\pN\s]+!u', '', mb_strtolower( toAscii( $string ) ));
			
			$string = preg_replace('/[\-]+/', '-', $string );
			
			$string = preg_replace('/\s+/', '-', $string );
			
			$string = preg_replace('/\s/', '-', $string );
    
			$string = trim( $string, '-' );

			$string = stripslashes( $string );
    
				// na wszelki wypadek
			//$string = urlencode($string);
    
		return $string;
	}

	/**
	 * Przycinanie tekstu przed określoną ilością znaków
	 *
	 * @param string $text - tekst
	 * @param int $ile - przed iloma znakami uciąć tekst
	 * @param bool $strip_tags - usuwanie tagów HTML
	 *
	 * @param string $read_more
	 * @return string - tekst po obcięciu
	 */
	function cutWords( $text, $ile, $strip_tags = false, $read_more = '' ){
		
		if( $strip_tags )
		{
			$text = strip_tags( $text );
		}
		
		if( !isset( $text[$ile] ) )
		{
			return $text;
		}
		
		$return_text = substr( $text, 0, strrpos( substr( $text, 0, $ile ), ' ') );
		
		if( empty( $return_text ) )
		{
			$return_text = substr( $text, 0, $ile );	
		}
		
		return $return_text . $read_more;
	}
	
	function get_input( $var, $default = false )
	{
		return ( isset( $_GET[ $var ] ) && !empty( $_GET[ $var ] ) ? $_GET[ $var ] : ( isset( $_POST[ $var ] ) && !empty( $_POST[ $var ] ) ? $_POST[ $var ] : $default ) );
	}
	
	function has_value( $var, &$data, $default = null )
	{
		return ( isset( $data[ $var ] ) && $data[ $var ] != '' ? $data[ $var ] : $default  );
	}
	/** Unset from array/object **/
	function ips_unset( $key, &$array, $default = null )
	{
		if( isset( $array[ $key ] ) )
		{
			$default = $array[ $key ];
			$array[ $key ] = null;
			unset( $array );
		}
		
		return $default;
	}
	
	function is_image($path)
	{
		$a = getimagesize( $path );

		if( in_array( $a[2] , array( IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG ) ) || in_array( $a[2] , array('image/gif' , 'image/jpeg' ,'image/png') ) )
		{
			return true;
		}
		
		return false;
	}
	
	function get_current_url()
	{
		return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		
		$host = $_SERVER['HTTP_HOST'];
			
		if( IPS_ACTION_GET_ID )
			$url = IPS_ACTION_GET_ID . '/' . seoLink(false, (isset($_GET['name']) ? $_GET['name'] : 'php'));
		elseif(!empty($_GET['file_category']))
			$url = 'file_category/'.$_GET['file_category'].'/'.$_GET['nazwa'];
		else
			$url = '';
		
		return 'http://'.$host.'/'.$url;
	
	}

	function ips_redirect_js( $url, $timeout = 1 )
	{
		return '
		<script>
			setTimeout(function(){
				window.location.href = "' . $url . '";
			}, ' . ( $timeout * 1000 ) . ' );
		</script>';
	}
	
	

	
	/*
	* Generujemy unikalną nazwę dla pliku Cache 
	* na podstawie wybranych parametrów czyli sposobu ładowania 
	* galerii i artykułów, ustawień automatycznego 
	* startu animacji GIF i włączonego preloadera obrazków
	*/
	
	function uniqueCacheID( $id, $additional = '' ){
		return md5( 
			$id 
			. Config::get('gif_auto_play') 
			. Config::get('img_preloader') 
			. Config::getArray('gallery_options', 'load') 
			. Config::get( 'article_options', 'view_type' )
			. IPS_ACTION_LAYOUT
			. $additional
		);
	}
	
	function cronIPSCall( $url, $timeout = 0.001 )
	{
		$options = array(
          'http' => array(
            'method'=>"GET",
             'header'=>"Accept-language: pl\r\n",
             'timeout' => $timeout
            )
        );
		$context = stream_context_create( $options );
		return @file_get_contents( $url , false, $context, 0, 5 );
	
	}

	/**
	 * Funkcja służąca do pobierania zawartości linku.
	 *
	 * @param string $url - link z którego pobrana zostanie treść
	 * @param array $options - opcje dla ustawienia w funkcji CURL
	 *
	 * @return bool|mixed
	 */
	function curlIPS( $url, array $options = array() )
	{
		if( empty( $url ) )
		{
			return false;
		}
		if( !is_array( $options ) )
		{
			$options = array();
		}
		/**
		* Domyslne opcje wywołania CURL
		* Podmiana opcji jesli zostały przesłane w zmiennej $options;
		*/
		
		$defaults = array_merge( array(
			'cookie' => false,
			'timeout' => 3,
			'useragent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:22.0) Gecko/20100101 Firefox/22.0',
			'file' => false,
			'refferer' => 'http://google.com/',
			'header' => array(),
			'post_data' => false,
			'other' => false
		), $options );
		
		$url = str_replace( "&amp;", "&", urldecode( trim( $url ) ) );

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_USERAGENT, $defaults['useragent'] );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $defaults['header'] );
		curl_setopt( $ch, CURLOPT_URL, $url);
		
		
		if( $defaults['cookie'] )
		{
			$cookie = tempnam( ABS_PATH . '/upload/tmp', 'CURLCOOKIE' );
			curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
		}
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_REFERER, $defaults['refferer'] );
		//curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $defaults['timeout'] );
		curl_setopt( $ch, CURLOPT_TIMEOUT, $defaults['timeout'] );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 2 );
		if( $defaults['other'] )
		{	
			foreach( $defaults['other'] as $opt => $value )
			{
				curl_setopt( $ch, $opt, $value );
			}
		}
		/**
		* Zapisywanie pliku poprzez wywołanie CURL
		*/
		if( $defaults['file'] )
		{
			curl_setopt( $ch, CURLOPT_BINARYTRANSFER, 1 );
			$fp = fopen( $defaults['file'], 'w' );
			curl_setopt( $ch, CURLOPT_FILE, $fp );
			curl_exec( $ch );
			curl_close ( $ch );
			fclose($fp);
			if( !file_exists( $defaults['file'] ) )
			{
				return false;
			}
			return true;
		}
		elseif( $defaults['post_data'] )
		{
			curl_setopt ($ch, CURLOPT_POST, 1);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, ( is_array( $defaults['post_data'] ) ? http_build_query( $defaults['post_data'] ) : $defaults['post_data'] ) );
		}
		
		
		$content = curl_exec( $ch );
		$response = curl_getinfo( $ch );
		
		curl_close ( $ch );
		
		if( $defaults['cookie'] )
		{
			@unlink( $cookie );
		}
		/**
		* Jeśli strona zawiera przekierowanie podążamy z nim.
		*/
		if ( $response['http_code'] == 301 || $response['http_code'] == 302 || $response['http_code'] == 303 )
		{
			ini_set("user_agent", "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:22.0) Gecko/20100101 Firefox/22.0");
	
			if ( $headers = @get_headers($response['url']) )
			{
			   foreach( $headers as $value )
			   {
					if ( substr( strtolower($value), 0, 9 ) == "location:" )
					{
						return curlIPS( trim( substr( $value, 9, strlen($value) ) ), $options );
					}
			   }
			}
		}

		return $content;
		
		//return array( 'data' => $content, 'resp' => $response );
		  
	}
		

	function getTranslations(){}

	/**
	* Pobieranie info na temat obecnego użytkownika lub użytkownika o podanym ID.
	* Stała $user przyjmuje wartość jedynie dla obecnego użytkownika
	*/
	function getUserInfo( $user_id, $current_user = false, $user_login = false, $field = false )
	{
		static $user = null;
		if( !empty( $user ) && ( isset( $user[ $user_id ] ) || isset( $user[ $user_login ] ) ) )
		{
			if( !$user_id )
			{
				$user_id = $user[ $user_login ];
			}
			return $field ? $user[ $user_id ][$field]: $user[ $user_id ];
		}
		
		if( !USER_LOGGED && !$user_id && !$user_login )
		{
			/**
			* Jeśli użytkownik nie jest zalogowany zwracamy Anonim
			*/
			$row = Config::anonymousInfo();
			
		}
		else
		{
			if( !$user_id && !$user_login )
			{
				return array();
			}
			
			$condition = ( $user_id ? array(
				'u.id' => $user_id
			) : array(
				'u.login' => $user_login
			) );
			
			$db = PD::getInstance();
			
			if( Config::get('services_premium') )
			{
				$row = $db->from( 'users u' )
					->join( 'premium_users p')
					->on( 'u.id', 'p.user_id' )
					->setWhere( $condition )
					->fields("u.*, p.premium_from, p.days, CONCAT( first_name,' ',last_name ) as full_name")
					->getOne();
			}
			else
			{
				$row = $db->from( 'users u' )
					->setWhere( $condition )
					->fields("u.*, CONCAT( first_name,' ',last_name ) as full_name")
					->getOne();
			}
		}
		
		if( empty( $row ) )
		{
			return array();
		}

		if( !isset( $row['full_name'] ) || strlen( $row['full_name'] ) < 2 )
		{
			$row['full_name'] = $row['login'];
		}
			
		$row['avatar_link'] = ips_user_avatar( $row['avatar'], 'url' );
		$user[ $row['id'] ] = $row;
		$user[ $row['login'] ] = $row['id'];
		
		return $field ? $row[$field] : $row;
	}
	
	/**
	* Pobieranie info na temat aktualnie wyświetlanego materiału.
	* Stała $file przyjmuje wartość jedynie dla obecnego materiału
	*/
	function getFileInfo( $file_id = false, $update = false )
	{
		if( $file_id == false )
		{
			$file_id = IPS_ACTION_GET_ID;
		}
		
		static $file = null;
		
		if( $update || $file == null || !is_array( $file ) )
		{
			/**
			* Dodatkowo pobieranie tagów dla materiału do użycia w meta
			* keywords i dodatku pod materiałem.
			*/
			/* 
			$file = PD::getInstance()->query("SELECT " . IPS__FILES . ".*, GROUP_CONCAT(upload_tags.tag SEPARATOR ',') as upload_tags 
												FROM " . IPS__FILES . " 
												LEFT JOIN upload_tags ON upload_tags.id_tag IN ( 
													SELECT `id_tag` FROM `upload_tags_post` WHERE `upload_id` = '" . $file_id . "' 
												) WHERE " . IPS__FILES . ".id = '" . $file_id . "' GROUP BY " . IPS__FILES . ".id LIMIT 1");
												*/
												
			$tags = PD::getInstance()->from( 'upload_tags t' )->join( 'upload_tags_post tr' )->on( 'tr.id_tag', 't.id_tag' )->where( 'tr.upload_id', $file_id )->fields(
				"GROUP_CONCAT( t.tag SEPARATOR ',' )"
			)->getQuery();
	
			
			$file = PD::getInstance()->from( IPS__FILES )->where( 'id', $file_id )->fields( array(
				'*',
				'(' . $tags . ') as tags'
			))->getOne();

			if( !$file )
			{
				ips_log( ips_backtrace() );
				ips_log($_SERVER);
				exit;
			}
			
			Tags::formatTags( $file['tags'] );
		}
		
		if( empty( $file ) || !isset( $file['seo_link'] )  )
		{
			return IPS_ACTION == 'file_page' ? ips_redirect( false, 'item_not_exists' ) : false;
		}
		
		if( IPS_VERSION != 'pinestic' && IPS_ACTION == 'file_page' )
		{
			if( !preg_match('/^\/([0-9]{1,})\/([A-Za-z0-9-_]{1,})' . IPS_LINK_FORMAT . '($|\?)/D', $_SERVER['REQUEST_URI'] ) )
			{
				if( empty( $file['title'] ) ) 
				{
					$file['title'] = ( !empty( $file['top_line'] ) ? $file['top_line'] : md5( $file_id ) );
				}
				
				ips_redirect( $file['id'] . '/' .seoLink( false, $file['title'] ), false, 301 );
			}
		}
		
		return $file;
	}

	
	/**
	* Sprawdzanie poprawności ciastka i zmiennej sesji.
	*/
	function ips_check_user_id()
	{
		$ssid_global = Cookie::get('ssid_global', false );
		
		if( $ssid_global  )
		{
			if( Session::has('user_name') && Session::has('user_id') )
			{
				$id = ips_decrypt( $ssid_global );

				if( is_numeric( $id ) && (int)$id === (int)Session::get('user_id') )
				{
					return $id;
				}
				Cookie::destroy();
			}
		}
		return false;
	}
	
	function ips_site_url()
	{
	
		$site_url_prefix = Config::get('site_url_prefix');

		if( ( strpos( $_SERVER['HTTP_HOST'], 'www' ) === false && $site_url_prefix == 1 ) || ( strpos( $_SERVER['HTTP_HOST'], 'www' ) !== false && $site_url_prefix == 0 ) )
		{
			header("HTTP/1.1 301 Moved Permanently");
			header( 'Location: http://' . ( $site_url_prefix == 1 ? 'www.' : '' ) . str_replace( 'www.', '', $_SERVER['HTTP_HOST'] ). '/' . substr( $_SERVER['REQUEST_URI'], 1 ) );
		}
	
		return 'http://' . $_SERVER['HTTP_HOST'] . '/';
	}
	/**
	* Sprawdzanie poprawności ciastka i zmiennej sesji dla admina i moda.
	*/
	function ips_check_user_role( $user_role )
	{
		if( USER_ID )
		{
			switch( $user_role )
			{
				case 'admin':
				case 'moderator':
					return ( Session::get('user_' . $user_role)  === md5( sha1( AUTH_KEY . md5( AUTH_KEY . USER_ID ) ) . '_' . $user_role ) );
				break;
			}
		}
		return false;
	}
	
	function ips_log( $data, $file_name = 'error_log.log', $erase = false )
	{
		if( is_resource($data) )
		{
			$data = var_dump($data, true);
		}
		elseif( !is_string($data)  )
		{
			$data = var_export( $data, true );
		}
		
		$error_log = ini_get('error_log');
		
		if( empty( $error_log ) || $file_name != 'error_log.log' )
		{
			if( !file_exists(ABS_PATH . '/' . $file_name ) || $erase )
			{
				@file_put_contents( ABS_PATH . '/' . $file_name, '' );
			}
			error_log( $data . "\n", 3, ABS_PATH . '/' . $file_name );
		}
		else
		{
			error_log( $data . "\n" );
		}
		return true;
	}

	/**
	* Hashowanie hasła usera
	*/
	function hashPassword( $password )
	{
		return md5( crypt( $password, AUTH_KEY ) );
	}
	
	/**
	* Get user token hash
	*/
	function getSecureToken( $id )
	{
		return md5( crypt( $_SERVER['HTTP_USER_AGENT'] . $id, AUTH_KEY ) );
	}

	
	/**
	* This file is part of the array_column library
	*
	* For the full copyright and license information, please view the LICENSE
	* file that was distributed with this source code.
	*
	* @copyright Copyright (c) 2013 Ben Ramsey <http://benramsey.com>
	* @license http://opensource.org/licenses/MIT MIT
	*/

	if (!function_exists('array_column')) {

		/**
		* Returns the values from a single column of the input array, identified by
		* the $columnKey.
		*
		* Optionally, you may provide an $indexKey to index the values in the returned
		* array by the values from the $indexKey column in the input array.
		*
		* @param array $input A multi-dimensional array (record set) from which to pull
		* a column of values.
		* @param mixed $columnKey The column of values to return. This value may be the
		* integer key of the column you wish to retrieve, or it
		* may be the string key name for an associative array.
		* @param mixed $indexKey (Optional.) The column to use as the index/keys for
		* the returned array. This value may be the integer key
		* of the column, or it may be the string key name.
		* @return array
		*/
		function array_column($input = null, $columnKey = null, $indexKey = null)
		{
			// Using func_get_args() in order to check for proper number of
			// parameters and trigger errors exactly as the built-in array_column()
			// does in PHP 5.5.
			$argc = func_num_args();
			$params = func_get_args();

			if ($argc < 2) {
				trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
				return null;
			}

			if (!is_array($params[0])) {
				trigger_error('array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given', E_USER_WARNING);
				return null;
			}

			if (!is_int($params[1])
				&& !is_float($params[1])
				&& !is_string($params[1])
				&& $params[1] !== null
				&& !(is_object($params[1]) && method_exists($params[1], '__toString'))
			) {
				trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
				return false;
			}

			if (isset($params[2])
				&& !is_int($params[2])
				&& !is_float($params[2])
				&& !is_string($params[2])
				&& !(is_object($params[2]) && method_exists($params[2], '__toString'))
			) {
				trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
				return false;
			}

			$paramsInput = $params[0];
			$paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;

			$paramsIndexKey = null;
			if (isset($params[2])) {
				if (is_float($params[2]) || is_int($params[2])) {
					$paramsIndexKey = (int) $params[2];
				} else {
					$paramsIndexKey = (string) $params[2];
				}
			}

			$resultArray = array();

			foreach ($paramsInput as $row) {

				$key = $value = null;
				$keySet = $valueSet = false;

				if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
					$keySet = true;
					$key = (string) $row[$paramsIndexKey];
				}

				if ($paramsColumnKey === null) {
					$valueSet = true;
					$value = $row;
				} elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
					$valueSet = true;
					$value = $row[$paramsColumnKey];
				}

				if ($valueSet) {
					if ($keySet) {
						$resultArray[$key] = $value;
					} else {
						$resultArray[] = $value;
					}
				}

			}

			return $resultArray;
		}

	}
	function __log( $phrase )
	{
		if( preg_match( '/(^[a-z\_]+)$/i', $phrase ) )
		{
			ips_log( $phrase, 'logs/missing-lang.log' );
			ips_log( ips_backtrace(), 'logs/missing-lang.log' );
		}
	}
	/**
	* Translate functions
	*/
	function __( $phrase, $default = '' )
	{
		global ${IPS_LNG};
		
		if( IPS_DEBUG && !isset( ${IPS_LNG}[ $phrase ] ) && empty( $default ) )
		{
			__log( $phrase );
		}
 
		return ( isset( ${IPS_LNG}[ $phrase ] ) ? ${IPS_LNG}[ $phrase ] : ( strlen( $phrase ) > 0 && is_string( $phrase ) && empty( $default ) ? $phrase : $default ) );
	}
	
	function __s( $phrase )
	{
		global ${IPS_LNG};
		
		if( IPS_DEBUG && !isset( ${IPS_LNG}[ $phrase ] ) )
		{
			__log( $phrase );
		}
		
		$phrase =  isset( ${IPS_LNG}[ $phrase ] ) ? ${IPS_LNG}[ $phrase ] : ( strlen( $phrase ) > 0 && is_string( $phrase ) ? $phrase : '---' ) ;

		$args = array_slice( func_get_args(), 1);
		
		if( count( $args ) > 0 )
		{
			array_unshift( $args, $phrase );
			return call_user_func_array( 'sprintf', $args );
		}
		
		return $phrase;
	}
	function __a( $phrase, $key )
	{
		global ${IPS_LNG};
		
		if( !isset( ${IPS_LNG}[ $phrase ] ) )
		{
			if( IPS_DEBUG )
			{
				__log( $phrase );
			}
		}
		elseif( is_serialized( ${IPS_LNG}[ $phrase ] ) )
		{
			${IPS_LNG}[ $phrase ] = unserialize( ${IPS_LNG}[ $phrase ] );
		}
		
		if( is_array( ${IPS_LNG}[ $phrase ] ) && isset( ${IPS_LNG}[ $phrase ][$key] ) )
		{
			return ${IPS_LNG}[ $phrase ][ $key ];
		}
		
		return $key;
	}
	
	function __replace( $phrase, $replace )
	{
		global ${IPS_LNG};
		
		$phrase =  isset( ${IPS_LNG}[ $phrase ] ) ? ${IPS_LNG}[ $phrase ] : ( strlen( $phrase ) > 0 && is_string( $phrase ) ? $phrase : '---' ) ;

		return str_replace( array_keys( $replace ), array_values( $replace ), $phrase );
	}
	
	function __admin( $phrase )
	{
		static $merged = false;
		
		if( !$merged )
		{
			Translate::loadAdminTranslations();
			$merged = true;
		}
		
		return call_user_func_array( '__s', func_get_args() );

	}
	
	/**
	* Change URL to clickable <a href>
	*/
	function makeClicableURL( $text )
	{
		return stripslashes( preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1">$1</a>', $text) );
	}
	
	function waitCounterUpdate()
	{
		$count = IPS_VERSION == 'pinestic' ? 0 : PD::getInstance()->cnt( IPS__FILES, array(
			'upload_status' => 'public',
			'upload_activ' => 0
		));
		Config::update( 'wait_counter', $count );		
	}
	
	function array_sort_by_column( &$arr, $col, $dir = SORT_DESC )
	{
		$sort_col = array();
		foreach ( $arr as $key=> $row )
		{
			$sort_col[$key] = $row[$col];
		}

		array_multisort( $sort_col, $dir, $arr );
	}
	
	function is_serialized( $value, &$result = null)
	{
		// Bit of a give away this one
		if (!is_string($value) || empty($value) )
		{
			return false;
		}

		// Serialized false, return true. unserialize() returns false on an
		// invalid string or it could return false if the string is serialized
		// false, eliminate that possibility.
		if ($value === 'b:0;')
		{
			$result = false;
			return true;
		}

		$length	= strlen($value);
		$end	= '';

		switch ($value[0])
		{
			case 's':
				if ($value[$length - 2] !== '"')
				{
					return false;
				}
			case 'b':
			case 'i':
			case 'd':
				// This looks odd but it is quicker than isset()ing
				$end .= ';';
			case 'a':
			case 'O':
				$end .= '}';

				if ($value[1] !== ':')
				{
					return false;
				}

				switch ($value[2])
				{
					case 0:
					case 1:
					case 2:
					case 3:
					case 4:
					case 5:
					case 6:
					case 7:
					case 8:
					case 9:
					break;

					default:
						return false;
				}
			case 'N':
				$end .= ';';

				if ($value[$length - 1] !== $end[0])
				{
					return false;
				}
			break;

			default:
				return false;
		}

		if (($result = @unserialize($value)) === false)
		{
			$result = null;
			return false;
		}
		return true;
	}
	
	/** **/
	function isMobile()
	{
		if( !$ips_mobile = Session::get('ips_mobile') )
		{
			require( LIBS_PATH . '/MobileDetect/Mobile_Detect.php' );
			$detect = new Mobile_Detect;
			$ips_mobile = Session::set('ips_mobile', $detect->isMobile() );
		}
		
		return $ips_mobile;
	}
	/** 
	* Set folder name from date, ex: 2013/03/ 
	**/
	function getFolderByDate( $base_path = null, $date )
	{
		$upload_folder = date( 'Y', strtotime( $date ) ) . '/'. date( 'm', strtotime( $date ) );
		
		if( $base_path == null )
		{
			return $upload_folder;
		}
		
		return $base_path . '/' . $upload_folder;
	}
	/** 
	* Sets and creates if not exists folder name from date, ex: 2013/03/ 
	**/
	function createFolderByDate( $base_path, $date, $full_path = 'base_path' )
	{
		if( ABS_PATH != '' && strpos( $base_path, ABS_PATH ) === false )
		{
			$base_path = ABS_PATH . '/' . $base_path;
		}
		
		$upload_folder = getFolderByDate( $base_path, $date );
		
		if( !file_exists( $upload_folder ) )
		{
			/* Create dir's recursivly */
			mkdir( $upload_folder, 0777, true );
			chmod( $upload_folder, 0777 );
		}
		
		switch( $full_path )
		{
			case 'url':
				return str_replace( ABS_PATH . '/', '', $upload_folder );
			break;
			case 'path':
				return $upload_folder;
			break;
			default:
				return str_replace( $base_path . '/', '', $upload_folder );
			break;
		}
	}
	
	/* 
	'.ips_img( $row, 'large' ).'
	' . ips_img( $row, 'thumb' ) . '
	*/
	
	
	/** 
	* Return img from array
	**/
	function ips_img( $res, $img = 'medium' )
	{
		switch( $img )
		{
			case 'gif':
				return IMG_LINK . '/gif/' . substr( ( is_string( $res ) ? $res : $res['upload_image'] ), 0, -4 ). '.gif';
			break;
			case 'backup':
				return ABS_URL . 'upload/img_backup/' . substr( ( is_string( $res ) ? $res : $res['upload_image'] ), 0, -4 ). '.gif';
			break;
			default:
				return IMG_LINK . '/' . $img . '/' . ( is_string( $res ) ? $res : $res['upload_image'] );
			break;
		}
		
		return 'false';
	}
	
	function ips_img_path( $res, $img = 'medium' )
	{
		switch( $img )
		{
			case 'gif':
				return IMG_LINK . '/gif/' . substr( ( is_string( $res ) ? $res : $res['upload_image'] ), 0, -4 ). '.gif';
			break;
			case 'backup':
				return IMG_PATH_BACKUP . '/' . ( is_string( $res ) ? $res : $res['upload_image'] );
			break;
			default:
				return IMG_PATH . '/' . $img . '/' . ( is_string( $res ) ? $res : $res['upload_image'] );
			break;
		}
		
		return 'false';
	}
	
	function ips_img_size( $res, $size = 'medium' )
	{
		$sizes = unserialize( is_string( $res ) ? $res : $res['upload_data'] );
		
		return $sizes[$size]['file'];
	}

	function ips_img_cache( $res, $size = '1x1' )
	{
		return ABS_URL . 'cache/img_cache/' . $size . '/' . ( is_string( $res ) ? $res : $res['upload_image'] );
	}
	
	function ips_json( $content )
	{
		if( !is_array( $content ) )
		{
			return $content;
		}
		
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		
		return json_encode( str_replace( array( "\n", "\t", "\r" ), '', $content ) );
	}
	
	function user_ip()
    {
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        elseif (getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        elseif (getenv("HTTP_X_FORWARDED"))
            $ip = getenv("HTTP_X_FORWARDED");
        elseif (getenv("HTTP_FORWARDED_FOR"))
            $ip = getenv("HTTP_FORWARDED_FOR");
        elseif (getenv("HTTP_FORWARDED"))
            $ip = getenv("HTTP_FORWARDED");
        elseif (getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else
            $ip = "127.0.0.1";
        
        return $ip;
    }
	
	/* Get file ID from ips url */
	function link_get_id( $url )
	{
		if( preg_match( "@/([0-9]{1,})@siU", $url, $matches ) )
		{
			if( !empty( $matches[1] ) )
			{
				return (int)$matches[1];
			};
		};
		
		return false;
	}
	
	function is_json( $string )
	{
		if( !is_string( $string ) )
		{
			return false;
		}
		
		$json = json_decode( $string );
	  
		return ( is_object( $json ) || is_array( $json ) ) ? true : false;
	}
	
	/** Push element to array child */
	function array_push_assoc( $array, $key, $value )
	{
		if( is_array( $value ) )
		{
			$array[$key] = array_merge( $array[$key], $value );
		}
		else
		{
			$array[$key] = $value;
		}
		
		return $array;
	}
	
	/** HOOK helper */
	function ips_html( $content )
	{
		return $content;
	}
	
	function ips_template_helper( $template, $variables )
	{
		return Templates::getInc()->getTpl( $template, $variables );
	}
	
	/** Add Prefix to table names */
	function db_prefix( $t, $t_as = null )
	{
		return DB_PREFIX . $t . ( $t_as ? ' as ' . $t_as : '' );
	}
	
	/** Better than debug_backtrace **/
	function ips_backtrace( $global = false )
	{
		$e = new Exception;
		return 
		( $global ? 
		'$_GET : '
		. var_export( $_GET, true ) . "\n"
		. '$_POST : '
		. var_export( $_POST, true ) . "\n"
		. 'Trace : ' . "\n"
		: '' ) . str_replace( array("\n", "\'", '\\\\'), array( "<br />\n", "'", '\\' ), var_export( $e->getTraceAsString(), true ) );
	}
	
	/* Generate truly "random" alpha-numeric string. */
	function str_random( $length )
	{
		return substr( str_replace( array('/', '+', '='), '', base64_encode( mt_rand() . microtime( true ) ) ), 0, $length );
	}
	
	/* Get session token. */
	function csrf_token()
	{
		return App::getToken();
	}
	
	function csrf_token_verify( $_token )
	{
		return App::getToken() === (string)$_token;
	}
	
	/* Push js/css file to array stack */
	function add_static_file( $files, $file )
	{
		if( !is_array( $file ) )
		{
			$file = array( $file );
		}
		
		foreach( $file as $f )
		{
			$key = md5($f);
			$files['minify'][$key] = $f;
			$files['path'][$key] = $f;
		}
		
		return $files;
	}
	/* Get path inf */
	function ips_pathinfo( $file, $option = false )
	{
		if( strpos( $file, '?' ) !== false)
		{
			$file = strstr( $file, '?', true );
		}
		
		if( $option )
		{
			return pathinfo( $file, $option );
		}
		
		return pathinfo( $file );
	}
	/** Store message to send admin */
	/* log, success, error */
	function admin_message( $type, $msg, $key = false )
	{
		$alerts = Config::getArray( 'admin_alerts' );
		
		$data = array(
			'type' => $type,
			'message' => $msg
		);
		
		if( !$key )
		{
			$key = count($alerts);
		}
		
		$alerts[$key] = array(
			'type' => $type,
			'message' => __( $msg )
		);
		
		return Config::update( 'admin_alerts', $alerts );
	}
	/* Set numeric type of variables */
	function castTypes( $array )
	{
		array_walk_recursive( $array, function( &$item, $key ){
			if ( is_numeric( $item ) && $item < 9999999999999 )
			{
				return $item = $item + 0;
			}
		});
		
		return $array;
	}
	/* Merge two arrays recursivly one level */
	function array_merge_one_deep( array $array, array $merge )
	{
		foreach( $array as $key => $value )
		{
			if( is_array( $value ) && isset( $merge[$key] ) && is_array( $merge[$key] ) )
			{
				$merge[$key] = array_merge( $array[$key], $merge[$key] );
			}
		}
		return array_merge( $array, $merge );
	}
	
	function getmicrotime( $diff = false )
	{
		return $diff ? microtime( true ) - $diff : microtime( true ) ;
	}
	
	/*
	* Retrieve a URL within the plugins directory.
	*/
	
	function plugin_url( $path, $file = '/', $type = 'path' )
	{
		$path = dirname( $path );
		
		if( ABS_PATH != '/' )
		{
			$path = str_replace( '\\', '/', str_replace( ABS_PATH, '', $path ) );
		}
		
		return ( $type == 'path' ? '' : trim( ABS_URL, '/') . '/' ) . trim( $path, '/' ) . '/' . trim( $file, '/' );
	}
	
?>