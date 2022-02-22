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

class Admin_Web_Fonts extends Web_Fonts
{
	/**
	 * Part of the site which you can use to change the font
	 */
	public $attrElements = array( 
		'html' => array( 
			'name' => 'Cała strona', 
			'css' => 'html body'
		), 
		'menu' => array( 
			'name' => 'Menu główne', 
			'css' => '.base-top .responsive-menu li a'
		), 
		'title' => array( 
			'name' => 'Tytuły materiałów', 
			'css' => '.item .file-title a'
		)
	);

	/**
	 * The class constructor invoked only in PA
	 *
	 * @param null
	 * 
	 * @return null
	 */
	public function __construct()
	{
		$this->all_fonts = $this->getFonts();
		
		$this->options = $this->getOptions();
		
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function fontsCodeWrite()
	{
		$fonts = $css = array();
		
		foreach ( $this->attrElements as $key => $val )
		{
			if( isset( $this->options[$key] ) )
			{
				$fonts[$key] = $this->options[$key];
				$css[] = $val['css'] . '{' . "\n\t" 
				. 'font-family: "' . str_replace( "-", " ", $this->options[$key]['font_type'] ) . '",arial,sans-serif !important;' 
				. "\n\t"
				. 'font-size: ' . $this->options[$key]['font_size'] . 'px !important;' 
				. "\n" . '}' 
				. "\n";
			}
		}
		
		if ( empty( $fonts ) )
		{
			Config::update( 'web_fonts_config', array(
				'css' => ''
			) );
			
			return false;
		}
		
		$url       = 'http://fonts.googleapis.com/css?family=';
		$subsets = array();
		
		$font_types = array();
		
		foreach ( $fonts as $key => $val )
		{
			if ( isset( $val['font_type'] ) && !empty( $val['font_type'] ) )
			{
				$variants = array();
				if ( isset( $val['font_variant'] ) && !empty( $val['font_variant'] ) )
				{
					$variants[] = $val['font_variant'];
				}
				
				$font_types[] = str_replace( '-', '+', $val['font_type'] ) . ":" . implode( ",", $variants );
				
				if ( isset( $val['subsets'] ) && !empty( $val['subsets'] ) )
				{
					foreach ( $val['subsets'] as $subset )
					{
						$subsets[] = $subset;
					}
				}
			}
		}
		
		$url .= implode( '|', array_unique( $font_types ) );
		
		if ( !empty( $subsets ) )
		{
			$url .= "&subset=" . implode( ",", array_unique( $subsets ) );
		}
		
		$css_data = '<link rel="stylesheet" type="text/css" href="' . $url . '">' . "\n" . '<style type="text/css">' . "\n" . implode( '', $css ) . '</style>' . "\n";
		
		Config::update( 'web_fonts_config', array(
			'css' => $css_data
		) );
	}

	/**
	 * Get the current font settings
	 *
	 * @param null
	 * 
	 * @return unserialized|array
	 */
	public static function getOptions()
	{
		return Config::getArray( 'web_fonts_config', 'options' );
	}
	
	/**
	 * Upgrade options
	 *
	 * @param array $data - data table fonts
	 * 
	 * @return null
	 */
	public function updateOptions( $data )
	{
		if ( !is_array( $data ) || empty( $data ) )
		{
			return false;
		}
		
		$this->options = array();
		
		foreach ( $data as $element_name => $options )
		{
			if ( is_array( $options ) )
			{
				if( isset( $options['font_type'] ) && $options['font_type'] != 'disabled' )
				{
					$this->options[$element_name] = $options;
				}
			}
		}

		Config::update( 'web_fonts_config', array(
			'options' => $this->options
		) );
		
		return ips_admin_redirect( 'template', 'action=fonts', 'settings_saved' );
	}
	
	
	/**
	 * Get single option
	 * @param 
	 * 
	 * @return 
	 */
	public function getOption( $group_name, $option, $default = array() )
	{
		if( isset( $this->options[ $group_name ][ $option ] ) )
		{
			return $this->options[ $group_name ][ $option ];
		}
		
		return $default;
	}
	/**
	 * The list of available fonts select
	 * @param 
	 * 
	 * @return 
	 */
	public function listAvailableFonts( $group_name = 'ips_fonts' )
	{
		$selected = $this->getOption( $group_name, 'font_type' );
		
		$html = '<div id="webfonts_' . $group_name . '" class="ips-fonts-container">' . "\n";
		
		$html .= '<select name="' . $group_name . '[font_type]" id="webfonts_' . $group_name . '-select" class="ips-fonts-selectable">' . "\n";
		
		$font_variant = $font_scripts = $first_element = '';
		
		foreach ( $this->all_fonts as $font )
		{
			/**
			 * The default font options
			 */
			$options = array(
				'font_variant' => false,
				'subsets' => false 
			);
			
			$font_name = $this->safeFontName( $font->family );
			
			/**
			 * It has different variants of the font.
			 */
			if ( count( $font->variants ) > 1 )
			{
				$options['font_variant'] = true;
			}
			
			/**
			 * It has a different character encoding.
			 */
			if ( count( $font->subsets ) > 1 )
			{
				$options['subsets'] = true;
			}
			
			/**
			 * The option to opt-selected set of fonts
			 */
			if ( $first_element === '' )
			{
				$first_element = false;
				$html .= '<option value="disabled" ' . $this->isChecked( 'disabled', $selected, true ) . '>' . __( 'option_off' ) . '</option>' . "\n";
			}
			
			/**
			 * HTML option for font
			 */
			$html .= '<option value="' . $font_name . '" ' . $this->isChecked( $font_name, $selected, true ) . '>' . $font->family . '</option>' . "\n";
			
			/**
			 * Font Option 700, 600 ...
			 */
			ksort( $font->variants );
			$font_variant .= '<div class="font-variant-' . $font_name . ' div-wariant' . ( $options['font_variant'] ? '' : ' ips-hide' ) . '">';
			$count = count( $font->variants );
			
			
			foreach ( $font->variants as $variant )
			{
				$font_variant .= '<input type="radio" name="' . $group_name . '[font_variant]" value="' . $variant . '" class="is-checked ' . $font_name . '"';
				
				if ( $font_name == $selected )
				{
					$font_variant .= $this->isChecked( $variant, $this->getOption( $group_name, 'font_variant' ) );
				}
				
				if ( $font_name == $selected && !$options['font_variant'] || $count == 1 )
				{
					$font_variant .= ' readonly="readonly"';
				}
				
				$font_variant .= '><label>' . ucwords( $variant ) . '</label><br />' . "\n";
				
			}
			$font_variant .= '</div><!-- end font-variant-' . $font_name . ' -->' . "\n";
			
			
			/**
			 * Character encoding options available for each font separately in the container div.
			 */
			krsort( $font->subsets );
			$font_scripts .= '<div class="subsets-' . $font_name . ' div-subsets">';
			$count = count( $font->subsets );
			
			foreach ( $font->subsets as $script )
			{
				$font_scripts .= '<input type="checkbox" name="' . $group_name . '[subsets][]" value="' . $script . '" class="is-checked ' . $font_name . '"';
				
				$subsets = $this->getOption( $group_name, 'subsets' );
				
				if ( $font_name == $selected && !empty( $subsets ) )
				{
					$font_scripts .= $this->isChecked( $script, $this->getOption( $group_name, 'subsets' ) );
				}
				
				if ( ( $font_name == $selected && !$options['subsets'] ) || $count == 1 )
				{
					$font_scripts .= ' readonly="readonly"';
				}
				
				$font_scripts .= '><label>' . ucwords( $script ) . '</label><br />' . "\n";
			}
			
			$font_scripts .= '</div><!-- end subsets-' . $font_name . ' -->' . "\n";
		}
		
		$html .= '</select>';
		
		$html .= '<span class="ips-fonts-action button ' . ( $selected && $selected != 'disabled' ? '' : 'display_none' ) . '" onclick="showAdvancedOptions( $(this),  \'' . $group_name . '\');">Rozwiń opcje</span>' . "\n";
		
		
		/*
		 * Div with additional options for the selected font
		 */
		$html .= '<div class="ips-fonts-additional">' . "\n";
		
		/*
		 * Font style, such as 200, 300, normal
		 */
		$html .= '<div class="ips-fonts-variants"><b>Wybierz styl czcionki:</b>' . $font_variant . '</div>' . "\n";
		
		/*
		 * Layers for which you can assign fonts
		 */
		$html .= '<div class="ips-fonts-layers"><b>Wybierz rozmiar czcionki:</b>';
		$html .= '<div class="ips-fonts-elements"><input class="number_ranger" name="' . $group_name . '[font_size]" value="' . $this->getOption( $group_name, 'font_size', 18 ) . '" data-min="8" data-max="36" /></div>' . "\n";
		$html .= '</div><!-- end ips-fonts-layers -->' . "\n";
		
		/*
		 * character Encoding
		 */
		$html .= '<div class="ips-fonts-subsets' . ( $options['subsets'] ? '' : ' ips-hide' ) . '">';
		$html .= '<b>Wybierz rodzaj kodowania</b>' . $font_scripts;
		$html .= '</div><!-- end ips-fonts-subsets -->' . "\n";
		
		$html .= '</div><!-- end ips-fonts-additional -->' . "\n";
		
		$html .= '<div style="clear:both;"></div>' . "\n";
		
		$html .= '</div><!-- end ips-fonts-container -->' . "\n";
		
		
		return $html;
	}

	
	/**
	 * Download ttf font
	 * @param 
	 * 
	 * @return 
	 */
	public function addWebFont( $font_name )
	{
		
		foreach ( $this->all_fonts as $font )
		{ 
			if( $font->family == $font_name  )
			{
				$installed = Config::get( 'web_fonts_config', 'installed' );
				
				$installed[ $this->safeFontName( $font->family ) ] = json_decode( json_encode( $font ), true );
				
				Config::update( 'web_fonts_config', array(
					'installed' => $installed
				) );
			}
		}

		return false;
	}
	/**
	 * Delete font from installed
	 * @param 
	 * 
	 * @return 
	 */
	public function deleteFont( $font_name )
	{
		$font_name = $this->safeFontName( $font_name );
		
		$installed = Config::get( 'web_fonts_config', 'installed' );
				
		if( isset( $installed[ $font_name ] ) )
		{
			unset( $installed[ $font_name ] );
			
			return Config::update( 'web_fonts_config', array(
				'installed' => $installed
			) );
		}
		
		return false;
	}
	/**
	 * The list of available fonts select
	 * @param 
	 * 
	 * @return 
	 */
	public function listArrayFonts()
	{
		$installed = array_keys( Config::getArray( 'web_fonts_config', 'installed' ) );
		
		$fonts = array(array(
			'current_value' => '<input class="font_prewiew_input" type="text" value="ABC def GHI" name="system_fonts_prewiew_input">',
			'option_set_text' => 'system_fonts_prewiew_input',
			'option_type' => 'value'
		));
		
		if( !is_array( $installed ) )
		{
			$installed = array();
		}
		
		foreach ( $this->all_fonts as $font )
		{
			if( !in_array( $this->safeFontName( $font->family ), $installed ) )
			{
				$font_variant = '';
				/* $font_name = $this->safeFontName( $font->family );
				
				ksort( $font->variants );
				$font_variant = '<div class="font-variant-' . $font_name . ' div-wariant">';
				$count = count( $font->variants );
				
				
				foreach ( $font->variants as $key => $variant )
				{
					$font_variant .= '<input type="checkbox" name="font_variant" value="' . $variant . '"';
					
					if ( $key == 0 || strtolower( $variant ) == '900')
					{
						$font_variant .= ' checked="checked"';
					}

					if ( $count == 1 )
					{
						$font_variant .= ' readonly="readonly"';
					}
					
					$font_variant .= '><label>' . ucwords( $variant ) . '</label><br />' . "\n";
					
				}
				$font_variant .= '</div><!-- end font-variant-' . $font_name . ' -->' . "\n";
			
				$font_variant = '<div class="ips-fonts-variants"><b>Wybierz styl czcionki:</b>' . $font_variant . '</div>'; */
				
				$fonts[] = array(
					'current_value' => '<div class="font-load"><span class="font-preview" style="font-family:' . $font->family . '"><img src="images/system-loading.gif"></span><a class="button font-import" class="font-import" data-font="' . $this->safeFontName( $font->family )  . '">' . __( 'common_add' ) . '</a>' . $font_variant . '</div>',
					'option_set_text' => $font->family,
					'option_type' => 'value'
				);
			}
		}


		return $fonts;
	}
	

	/**
	 * Porównanie dwóch wartości dla input itp
	 * Comparison of the two input values, etc.
	 *
	 * @param string|array $first - first item
	 * @param string|array $second - second member
	 * 
	 * @return string
	 */
	function isChecked( $first, $second, $select_option = false )
	{
		$checked = $select_option ? ' selected=selected' : ' checked=checked';
		
		if ( is_string( $first ) )
		{
			$first = strtolower( $first );
		}
		if ( is_string( $second ) )
		{
			$second = strtolower( $second );
		}
		
		if ( is_array( $second ) && $second )
		{
			if ( in_array( $first, $second ) )
			{
				return $checked;
			}
		}
		elseif ( $first == $second )
		{
			return $checked;
		}
		
		return '';
	}
}