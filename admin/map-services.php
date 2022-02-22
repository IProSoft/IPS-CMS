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
	if( !defined('USER_ADMIN') || !USER_ADMIN ) die ("Hakier?");
	
	
	
	$main_action = isset( $_GET['action'] ) && !empty( $_GET['action'] ) ? $_GET['action'] : 'other' ;
	
	$maps_customize = Config::getArray('apps_google_maps_customize');

	if( empty( $maps_customize ) || !is_array( $maps_customize ) || !isset( $maps_customize['customized_styles'] ) || !is_array( json_decode( $maps_customize['customized_styles'], true ) ) )
	{
		Config::update('apps_google_maps_customize', array(
			'default_position' => 'Poland',
			'customized_styles' => '[{"featureType":"water","elementType":"geometry.fill","stylers":[{"color":"#588991"}]},{"featureType":"landscape.natural","stylers":[{"color":"#f3f1e3"},{"gamma":2.61},{"saturation":-68},{"hue":"#3bff00"}]},{"featureType":"administrative.country","elementType":"geometry","stylers":[{"color":"#3fbfbf"},{"weight":1.2}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#dddddd"}]},{"featureType":"water","elementType":"labels.text","stylers":[{"invert_lightness":true},{"weight":1.3},{"lightness":-84}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"road.highway","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"landscape.natural.terrain","elementType":"geometry.fill","stylers":[{"color":"#a8ddbf"}]},{},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#a8ddbf"}]},{}]'
		));
	}
	
	echo admin_caption( 'caption_map' );
	
	echo '
	
	<form action="admin-save.php" enctype="multipart/form-data" method="post">		
		<div class="content_tabs tabbed_area">
			
			<div class="caption_small"><span class="caption">' .  __( 'maps_style_info' ) . '</span></div>
			
			<div class="option-cnt">
				<span>Snazzy Maps</span>
				<div class="option-inputs">
					<div class="one_half">
						' .  __( 'maps_snazzy_info' ) . '
					</div>
					<div class="one_half">
						<a target="_blank" class="button" href="http://snazzymaps.com/">'.__( '' ).'</a>
					</div>
				</div>
			</div>
			
			<div class="option-cnt">
				<span>Styled Maps Wizard</span>
				<div class="option-inputs">
					<div class="one_half">
						' .  __( 'maps_styled_info' ) . '
					</div>
					<div class="one_half">
						<a target="_blank" class="button" href="http://gmaps-samples-v3.googlecode.com/svn/trunk/styledmaps/wizard/index.html">' .  __( 'maps_goto_styled' ) . '</a>
					</div>
				</div>
			</div>
			
		</div>
		
		' . displayArrayOptions( getOptionsFile()['options_map'] ) . '
		
		<input type="submit" class="button" value="' . __('save_all') . '" />
		<script>
			$(document).ready(function() {
				
				try{
					var json = JSON.parse( $(".maps_customize textarea").val() );
					$(".maps_customize textarea").val( JSON.stringify( json, null, "\t") );
				}catch( e ){
				
				}
			});
		</script>
		<div class="div-info-message">
			<p>' . __( 'map_info' ) . '</p>
		</div>
	</form>
	';
	 			
		
		
	Session::set( 'admin_redirect', admin_url( 'map' ) );
		
?>