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

class Gd
{
	/**
	 * Get image from file
	 */
	static function as_resource( $img )
	{
		if ( !is_resource( $img ) )
		{
			try
			{
				$img = imagecreatefromstring( file_get_contents( $img ) );
			}
			catch ( Exception $e ){}
		}
		
		return $img;
	}
	/**
	 * Get GD as string
	 */
	static function as_string( $image, $ext = 'png' )
	{
		ob_start();
		
		if( $ext == 'jpg' )
		{
			imagejpeg( $image, null, 100 );
		}
		else
		{
			imagepng( $image, null, 0 );
		}
		
		return ( ob_get_clean() );
	}
	
	/**
	 * Get true color image colorated
	 */
	static function img_colored( $s, $img = null )
	{
		if( !is_resource( $img ) )
		{
			$img = imagecreatetruecolor( $s['width'], $s['height'] );
		}
		
		$color = Gd::hex_convert( $s['color'] );
		
		return imagecolorallocate( $img, $color['r'], $color['g'], $color['b'] );
	}
	
	/**
	 * Get true color image transparent
	 */
	static function img_transparent( $s, $img = null )
	{
		if( !is_resource( $img ) )
		{
			$img = imagecreatetruecolor( $s['width'], $s['height'] );
		}
		
		imagealphablending( $img, true );
		imagesavealpha( $img, true );
		$transparency = imagecolorallocatealpha( $img, 0, 0, 0, 127 );
		
		return $transparency;
	}
	
	/**
	 * Get true color image colorated and fill with color
	 */
	static function img_colored_fill( $s )
	{
		$img = imagecreatetruecolor( $s['width'], $s['height'] );

		imagefill( $img, 0, 0, ( isset( $s['transparent'] ) ? self::img_transparent( $s, $img ) : self::img_colored( $s, $img ) ) );
			
		return $img;
	}
	
	/**
	* Simple function that calculates the *exact* bounding box (single pixel precision).
	* The function returns an associative array with these keys:
	* left, top:  coordinates you will pass to imagettftext
	* width, height: dimension of the image you have to create
	**/
	static function calculate_text_box( $fontSize, $fontFile, $text, $x = true )
	{
		
		if( is_array( $text ) || !is_file( $fontFile )  )
		{
			var_dump(debug_backtrace());exit;
		}
		$rect = imagettfbbox( $fontSize, 0, $fontFile, $text );
		$minX = min(array($rect[0],$rect[2],$rect[4],$rect[6]));
		$maxX = max(array($rect[0],$rect[2],$rect[4],$rect[6]));
			if($x){
				if( function_exists('mb_convert_case') )
				{
					$text = mb_convert_case( $text, MB_CASE_UPPER, "UTF-8" );
				}
				else
				{
					$text = strtoupper( $text );
				}
				$rect = imagettfbbox($fontSize,0,$fontFile,$text);
			}
		$minY = min( array( $rect[1], $rect[3], $rect[5], $rect[7]) );
		$maxY = max( array( $rect[1], $rect[3], $rect[5], $rect[7]) );
		unset( $rect );
		
		return array(
			'left'   => abs($minX) - 1,
			'top'    => abs($minY) - 1,
			'width'  => $maxX - $minX,
			'height' => (($maxY - $minY)*1.2)
		);
	}
	
	/** 
	* Convert HEX color to rgb array
	*/
	public static function hex_convert( $hex )
	{
		$hex = str_replace( '#', '', $hex );
		
		if(strlen($hex) == 6)
        {
            list($r, $g, $b) = array($hex[0].$hex[1],$hex[2].$hex[3],$hex[4].$hex[5]);
        }
        elseif(strlen($hex) == 3)
        {
            list($r, $g, $b) = array($hex[0].$hex[0],$hex[1].$hex[1],$hex[2].$hex[2]);
        }
        else if(strlen($hex) == 2)
        {
            list($r, $g, $b) = array($hex[0].$hex[1],$hex[0].$hex[1],$hex[0].$hex[1]);
        }
        else if(strlen($hex) == 1)
        {
            list($r, $g, $b) = array($hex.$hex,$hex.$hex,$hex.$hex);
        }
        else
        {
            return false;
        }
		
		$color = array();
        
		$color['r'] = hexdec($r);
        $color['g'] = hexdec($g);
        $color['b'] = hexdec($b);

        return $color;

	}
	
	/*
		Calculates restricted dimensions with a maximum of $max_width and $max_height 
	*/
	
	public static function scale_dimensions( $width, $height, array $max )
	{ 
		$ratio = 1; 
		$max_width  = isset( $max['width'] ) ? $max['width'] : $width;
		$max_height = isset( $max['height'] ) ? $max['height'] : $height;
		
		$scale = $max_width > $width || $max_height > $height;
		
		if( $scale  )
		{
			if( $width != $max_width )
			{
				$ratio = $max_width / $width;
			}
			elseif( $height != $max_height )
			{
				$ratio = $max_height / $height;
			}
			
			$width *= $ratio;
			$height *= $ratio;
		}
		else
		{
			if( $width > $max_width )
			{
				$ratio_width = $max_width / $width;
				$width *= $ratio_width;
				$height *= $ratio_width;
			}
			
			if( $height > $max_height )
			{
				$ratio_height = $max_height / $height;
				$width *= $ratio_height;
				$height *= $ratio_height;
			}
		}
		
		return [
			'width' => $width,
			'height' => $height
		];
	}
	
	/*
	* Set image opacity
	* PHP.NET
	* Function to change the transparency of a png image on the fly. Works only with PNG, and with a browser supporting alpha channel.
	* The function stretches the opacity-range of the image, so that the most opaque pixel(s) will be set to the given opacity. (Other opacity values in pixels are modified accordingly.)
	* Returns success or failure.
	* params: image resource id, opacity in percentage (eg. 80)
	*/
	public static function opacity( $img, $opacity )
	{ 
		if( !isset( $opacity ) )
		{
			return false;
		}
		
		$im = !is_resource( $img ) ? Gd::as_resource( $img ) : $img;
		
		$opacity /= 100;
	   
		//get image width and height
		$w = imagesx( $im );
		$h = imagesy( $im );
	   
		//turn alpha blending off
		imagealphablending( $im, false );
	   
		//find the most opaque pixel in the image (the one with the smallest alpha value)
		$minalpha = 127;
		for( $x = 0; $x < $w; $x++ )
		{
			for( $y = 0; $y < $h; $y++ )
				{
					$alpha = ( imagecolorat( $im, $x, $y ) >> 24 ) & 0xFF;
					if( $alpha < $minalpha )
						{ $minalpha = $alpha; }
				}
		}
		//loop through image pixels and modify alpha for each
		for( $x = 0; $x < $w; $x++ )
		{
			for( $y = 0; $y < $h; $y++ )
				{
					//get current alpha value (represents the TANSPARENCY!)
					$colorxy = imagecolorat( $im, $x, $y );
					$alpha = ( $colorxy >> 24 ) & 0xFF;
					//calculate new alpha
					if( $minalpha !== 127 )
						{ $alpha = 127 + 127 * $opacity * ( $alpha - 127 ) / ( 127 - $minalpha ); }
					else
						{ $alpha += 127 * $opacity; }
					//get the color index with new alpha
					$alphacolorxy = imagecolorallocatealpha( $im, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha );
					//set pixel with the new color + opacity
					if( !imagesetpixel( $im, $x, $y, $alphacolorxy ) )
					{
						return false;
					}
				}
		}
	
		imagealphablending( $im, true );
		imagesavealpha( $im, true );
			
		return $im;
	
	}
}