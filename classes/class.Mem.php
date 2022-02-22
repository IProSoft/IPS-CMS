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

class Mem
{
	
	/**
	 * Category or action Display generators
	 */
	public $category = false;
	
	public function __construct()
	{
		remove_action( 'before_content', 'call_widget' );
		
		$up_mem = new Upload_Mem;
		
		$opts = $this->opts = $up_mem->getOptions();
	
		add_filter( 'init_css_files', function( $array ){
			return add_static_file( $array, array(
				'css/up/dropzone.css',
				'css/up/canvas.css'
			) );
		}, 10 );
		
		add_filter( 'init_js_files', function( $array ){
			return add_static_file( $array, array(
				'js/up/upload.js',
				'js/up/canvas_init.js',
				'js/up/canvas_type_abstract.js',
				'js/up/drop_lines.js',
				'js/up/drop_mem.js',
				'js/up/canvas_mem.js',
				'js/up/canvas_editor.js',
				'js/mem_generator.js'
			)  );
		}, 10 );
		
		add_filter( 'init_js_variables', function( $array ) use ( $opts ) {
			
			$array['ips_config']['up_generator_options'] = $opts;
			return $array;
		});
		
		add_action( 'after_footer', 'App::async', array( array_column( Ips_Registry::get( 'Web_Fonts' )->getFontsUrls(), 'url' ) ) );
	}

	/**
	 * The form of generator
	 *
	 * @param null
	 * 
	 * @return void
	 */
	public function addGeneratorForm()
	{
		if ( !empty( $_POST ) )
		{
			$info = $this->addGenerator( $_POST );
			
			if( $info === true )
			{
				admin_message( 'log', 'generator_admin_alert', 'mem_generator' );
				
				return ips_redirect( 'mem/', array(
					'info' => 'generator_add_success'
				));
			}
			
			return ips_redirect( false, array(
				'alert' => $info
			));
		}
		
		$mem_categories = $this->getCategories();
		
		if ( empty( $mem_categories ) )
		{
			return ips_redirect( false, 'generator_add_no_categories' );
		}
		
		$category_opts = '';
		
		foreach ( $mem_categories as $key => $cat )
		{
			$category_opts .= '<option value="' . $cat['id'] . '" ' . ( $this->category == $cat['rewrite_text'] ? 'selected="selected' : '' ) . '">' . $cat['category_text'] . '</option>';
		}
		
		
		return Templates::getInc()->getTpl( 'generator_add.html', array(
			'category_opts' => $category_opts 
		) );
	}
	
	/**
	 * Adds a generator to database and write file sent by user
	 *
	 * @param null
	 * 
	 * @return void
	 */
	public function addGenerator( $data )
	{
		if ( !isset( $data['mem_title'] ) || empty( $data['mem_title'] ) )
		{
			return 'generator_add_error_title';
		}
		
		if ( strlen( $data['mem_title'] ) > 128 )
		{
			return 'generator_add_error_title_long';
		}
		
		if ( !isset( $data['mem_category'] ) || empty( $data['mem_category'] ) )
		{
			return 'generator_add_error_category';
		}
		
		if ( !isset( $_FILES['mem_image'] ) || empty( $_FILES['mem_image']['name'] ) )
		{
			return 'generator_add_error_file';
		}

		try
		{
			$file_name = basename( $this->uploadImage( $data['mem_title'] ) );
		}
		catch ( Exception $e )
		{
			return 'generator_add_error_file_ext';
		}
		
		$generator_id = PD::getInstance()->insert( 'mem_generator', array(
			'mem_title'    => Sanitize::cleanSQL( $data['mem_title'] ),
			'mem_image'	   => $file_name,
			'mem_category' => Sanitize::cleanSQL( $data['mem_category'] ),
			'mem_date_add' => date( "Y-m-d H:i:s" ),
			'mem_activ'    => ( isset( $data['mem_activ'] ) ? $data['mem_activ'] : 0 ) 
		) );
		
		if( defined( 'IPS_ADMIN_PANEL' ) )
		{
			$this->updateLanguage( $generator_id, $data );
		}
		
		return true;
	}
	
	/**
	 * Update generator in database
	 *
	 * @param null
	 * 
	 * @return void
	 */
	public function updateGenerator( $generator_id, $data )
	{
		$data = array_merge( PD::getInstance()->select( 'mem_generator', array( 
			'id' => $generator_id
		), 1 ) , $data );
		

		try
		{
			$file_name = basename( $this->uploadImage( $data['mem_title'] ) );
		}
		catch ( Exception $e )
		{
			$file_name = $data['mem_image'];
		}
		
		PD::getInstance()->update( 'mem_generator', array( 
			'mem_title' => $data['mem_title'], 
			'mem_image' => $file_name, 
			'mem_category' => $data['mem_category'], 
			'mem_activ' => $data['mem_activ']
		), array( 
			'id' => $generator_id
		));
		
		if( defined( 'IPS_ADMIN_PANEL' ) )
		{
			$this->updateLanguage( $generator_id, $data );
		}
		
		return true;
	}
	
	/**
	 * Upload mem generator image
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function uploadImage( $title )
	{
		$file_name = str_replace( '.html', '', seoLink( false, $title ) );
		
		$path = ABS_PATH . '/upload/upload_mem';
		
		$exists = glob( $path . '/' . $file_name . '.*' );
		
		if( !empty( $exists ) )
		{
			$file_name = $file_name . '_' . time();
		}
		
		try
		{
			$up = new Upload_Single_File();
			
			$file = $up->setFileName( $file_name )->setConfig( array(
				'files' => 'mem_image' 
			) )->Load( $path . '/' . $file_name, array(
				'max_width' => 700,
				'extension' => array(
					'png', 'jpg'
				)
			) );
	
			$file_name = $file_name . '.' . $file['extension'];
		}
		catch ( Exception $e )
		{
			
			$file_name = null;
		}
		
		if ( empty( $file_name ) )
		{
			throw new Exception( '' );
		}
		
		return $file_name;
	}
	
	/**
	 * Wyświetlanie menu i listy generatorów.
	 * Menu listy generatorów. Podświetla aktualną kategorię.
	 * Display the menu and the list of generators. List menu generators. Highlights the current category.
	 * 
	 * @param null
	 * 
	 * @return void
	 */
	public function memGenerators()
	{
		add_action( 'before_content', 'ips_html', array(
			'<div class="fancy_head_title"><div class="box-max"><h2>' . __( 'generator_set_image' ) . '</h2></div></div>'
		), 10 );
		
		$this->categories = PD::getInstance()->select( 'mem_generator_categories' );
		
		$query = PD::getInstance()->from( array( 
			'mem_generator_categories' => 'mc', 
			'mem_generator' => 'mg'
		) )->where( 'mg.mem_category', 'field:mc.id' )->where( 'mg.mem_activ', 1 );
		
		if ( $this->category != 'all' )
		{
			$query = $query->where( 'mc.rewrite_text', $this->category );
		}
		
		$generators = $query->get();

		if( !empty( $generators ) )
		{
			foreach( $generators as $key => $generator )
			{
				if( has_value( 'mem_title_' . strtolower( IPS_LNG ), $generator ) )
				{
					$generators[$key]['mem_title'] = $generator[ 'mem_title_' . strtolower( IPS_LNG ) ];
				}
			}
		}
		
		$category_info = PD::getInstance()->select( 'mem_generator_categories', array(
			'rewrite_text' => $this->category
		), 1 );

		return Templates::getInc()->getTpl( 'generator_list.html', array_merge( array(
			'categories' => PD::getInstance()->select( 'mem_generator_categories' ),
			'category' => $this->category,
			'count' => $this->countGenerators( has_value( 'id', $category_description, 'all' ) ),
			'category_description' => has_value( 'category_description', $category_info, '' ),
			'generators' => $generators,
			'actual_category' => $this->category ,
		), $this->opts ) );
	}
	
	/**
	 * Mem generators search
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function search( $phrase, $category )
	{
		if( !empty( $phrase ) )
		{
			$conditions = array(
				'mg.mem_category' => 'field:mc.id',
				'mg.mem_activ' => 1,
				'mg.mem_title' => array( $phrase, 'LIKE' ),
			);
			
			if( !empty( $category ) && $category != 'all' )
			{
				$conditions['mc.rewrite_text'] = $category;
			}
			
			return array(
				'content' => Templates::getInc()->getTpl( 'generator_list_img.html', array(
					'generators' => PD::getInstance()->from( array( 
						'mem_generator_categories' => 'mc', 
						'mem_generator' => 'mg'
					) )->setWhere( $conditions )->get()
				) )
			);
		}
		
		return array(
			'content' => ''
		);
	}
	
	
	
	/**
	 * Put text on mem transparent image
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function countGenerators( $category_id )
	{
		$conditions = array(
			'mem_activ' => 1
		);
		
		if( !empty( $category_id ) && $category_id != 'all' )
		{
			$conditions['mem_category'] =  $category_id;
		}
		
		return PD::getInstance()->optRand( 'mem_generator', $conditions, 1 );
	}
	
	/**
	 * Get categories
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getCategories()
	{
		if( !isset( $this->categories ) )
		{
			$categories = PD::getInstance()->select( 'mem_generator_categories' );
			
			if( !is_array( $categories ) )
			{
				return array();
			}
			
			$this->categories = array();
			
			foreach( $categories as $category )
			{
				$this->categories[ $category['id'] ] = $category;
			}
		}
		
		return $this->categories;
	}
	/** Update uses statistic
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function used( $generator_id )
	{
		return PD::getInstance()->increase( 'mem_generator', array( 
			'mem_generated' => 1
		), array( 
			'id' => $generator_id
		));
	}
}
?>