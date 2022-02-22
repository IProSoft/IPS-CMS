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
	

class CDN77
{
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function init()
	{
		
		if( !empty( $_POST ) )
		{
			if( !empty( $_POST['cdn_url'] ) )
			{
				$cdn77_host = $this->hostVerifyID( $_POST['cdn_url'] );
				
				if( !$cdn77_host )
				{
					ips_admin_redirect( 'plugins', 'plugin=Cdn', 'plugin_cdn_add_valid_host_name');
				}
				
				Config::update( 'plugin_cdn_config', array(
					'cdn77_host' => $cdn77_host
				) );
				
				Config::update( 'ips_upload_url', 'http://' . $cdn77_host . '.r.cdn77.net' );
			}

			ips_admin_redirect( 'plugins', 'plugin=Cdn', 'plugin_cdn_saved' );
		}
		
		$path = str_replace( 'www.', '', parse_url( ABS_URL, PHP_URL_HOST ) );
		
		echo '
		<div class="content_tabs tabbed_area smart_options">
			<div class="caption_small">
				<span class="caption">CDN77 <a href="http://cdn77.com/">http://cdn77.com/</a></span>
			</div>
			
			<form action="" enctype="multipart/form-data" method="post">	
				' . displayOptionField('cdn_url', array(
					'option_set_text' => 'Host CDN',
					'option_type' => 'text',
					'option_value' => '<input class="with_url" type="text" name="cdn_url" value="' . Config::getArray('plugin_cdn_config', 'cdn77_host') . '" /><i class="with_url">.r.cdn77.net</i>'
				)) .'

				<button class="button">' . __( 'save' ) . ' </button>
			</form>


		</div>
		<div class="div-info-message">
			<p>' . __( 'plugin_cdn_info', $path, $path ) . '</p>
		</div>
		';	
	}
	
	public function hostVerifyID( $id )
	{
		if( !preg_match( "/^[0-9]+$/", $id ) )
		{
			return false;
		}
		
		return $id;
	}
}




?>