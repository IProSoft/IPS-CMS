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
//define( 'DEBUG_IMG_ADDING',  true );

class Ips_Embed extends Core_Query
{
	public $settings = array();
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function embed( array $settings )
	{
		$this->settings['display']    = false;
		$this->settings['pagination'] = false;
		$this->settings['condition'] = array();
		
		$this->setTable( $settings );
		$this->setConditions( $settings );
		$this->setSorting( $settings );
		$this->setLimit( $settings );
		
		$this->init( 'ips_embed', $this->settings );
		
		return $this->display( $settings );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function setTable( &$settings )
	{
		$this->settings['table'] = ( $settings['source_order'] == 'shares' ? array( IPS__FILES . ' as up' , 'shares as s' ) : IPS__FILES . ' as up' );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function setConditions( &$settings )
	{
		switch ( $settings['source'] )
		{
			case 'wait':
				$this->settings['condition'] = array( 'up.upload_activ' => 0 );
			break;
			case 'main':
				$this->settings['condition'] = array( 'up.upload_activ' => 1 );
			break;
			case 'category':
				$this->settings['condition'] = array( 'up.category_id' => $settings['category_id'] );
			break;
		}
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function setSorting( &$settings )
	{
		switch ( $settings['source_order'] )
		{
			case 'date_add':
				$this->settings['sorting'] = 'up.date_add';
				break;
			case 'votes_opinion':
				$this->settings['sorting'] = 'up.votes_opinion';
				break;
			case 'shares':
				$this->settings['sorting']   = 's.share';
				$this->settings['condition'] = array_merge( $this->settings['condition'], array( 's.upload_id' => 'field:up.id' ))
				break;
			default:
				$this->settings['sorting'] = 'RAND()';
				break;
		}
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function setLimit( &$settings )
	{
		$this->settings['limit'] = $settings['limit'];
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function display( &$settings )
	{
		if ( !file_exists( LIBS_PATH . '/galleries/bxslider/jquery.bxslider.compiled.css' ) )
		{
			$this->compileCss();
		}
		if ( !empty( $this->files ) )
		{
			foreach ( $this->files as $key => $file )
			{
				$this->files[$key]['image'] = ips_img( $file, 'thumb' );
				$this->files[$key]['url']   = ABS_URL . $file['id'] . '/' . $file['seo_link'];
			}

			return Templates::getInc()->getTpl( 'ips_embed.html', array(
				'css_class' => 'embed-images-' . $settings['img_size'],
				'embed' => $this->files,
				'captions' => ( isset( $settings['captions'] ) ? $settings['captions'] : false ) 
			) );
		}
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function embedCode()
	{
		$rand = rand();
		$tpl  = Templates::getInc();
		$tpl->assign( array(
			'categories' => Categories::categorySelectOptions(),
			'rand' => rand(),
			'max_size' => Config::getArray( 'add_thumb_size', 'width' ),
			'code' => htmlspecialchars( '<script id="ips_' . $rand . '" type="text/javascript">
var ips_embed = {
	source		: "{source}",
	rand_id		: "ips_' . $rand . '",
		embed_url	: "' . ABS_URL . '",
		img_size	: {img_size},
		category_id	: {category_id},
		source_order	: "{source_order}",
		limit		: 10,
		mode		: "{mode}",
		template	: "{template}",
		captions	: {captions}
	};
</script>
<script type="text/javascript" src="' . ABS_URL . 'js/embed.js"></script>' ) 
		) );
		return $tpl->getTpl( 'ips_embed_wizard.html' );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function compileCss()
	{
		try
		{
			
			$contents = File::read( LIBS_PATH . '/galleries/bxslider/jquery.bxslider.css' );
			$contents = str_replace( 'images/', ABS_URL . 'libs/galleries/bxslider/images/', $contents );
			File::put( LIBS_PATH . '/galleries/bxslider/jquery.bxslider.compiled.css', $contents );
			
		}
		catch ( Exception $e )
		{
			
		}
	}
}

