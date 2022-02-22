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

class Canvas_Helper
{
	
	/**
	 * Convert base 64 to img
	 *
	 * @param $base64_encoded
	 * @param $img_name
	 * @param $upload
	 * 
	 * @return string
	 */
	public static function store( $base64_encoded, $img_name, $upload = null )
	{
		$img_text = str_replace( 'data:image/png;base64,', '', $base64_encoded );
		$img_text = str_replace( ' ', '+', $img_text );
		
		if( empty( $img_text ) )
		{
			return false;
		}
		
		$img_text = imagecreatefromstring( base64_decode( $img_text ) );
		
		imagealphablending( $img_text, false );
		imagesavealpha( $img_text, true );
		
		if( $upload == null )
		{
			$upload = new Upload_Extended;
		}
		
		return basename( $upload->put( $img_text, IPS_TMP_FILES . '/' . $img_name ) );
	}
	
	/**
	 * Generate canvas edit toolbar
	 *
	 * @param $type
	 * @param $line
	 * 
	 * @return string
	 */
	public static function toolbar( $type, $line = false )
	{
		
		switch( $type )
		{
			case 'text':
				
				$text = Ips_Registry::get('Upload_Text');
				$options = $text->getOptions();
				
				if( !Config::getArray( 'upload_text_options', 'user_font') )
				{
					$options['fonts'] = array(
						$options['fonts'][ $options['font']['family'] ]
					);
				}
			
			break;
			case 'demotywator':
				
				$text = new Upload_Demotywator;
				$options = $text->getOptions();
				
				$options['font_size'] = $options[ 'font_size_' . $line ];
				$options['font_color'] = $options[ 'font_color_' . $line ];
			
			break;
			case 'mem':
				
				$text = new Upload_Mem;
				$options = $text->getOptions();
				
				$options['font_size'] = $options[ 'font_size_' . $line ];
				$options['font_color'] = $options[ 'font_color_' . $line ];
			
			break;
		}

		$types = [ 'type', 'size', 'color', 'style', 'align' ];
		
		foreach ( $types as $k => $type )
		{
			unset($types[$k]);
			$types[$type] = in_array( $type, $options['user_font_changes'] );
		}
		
		$options['user_font_changes'] = $types;
		
		return Templates::getInc()->getTpl( '/upload/up_canvas_toolbar.html', array_merge( $options, array(
			'type' => $type
		) ) );
	}
}