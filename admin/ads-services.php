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
	
		echo admin_caption( 'ads_ads' );
		
		
		
		echo '
		<div class="ads_form tabbed_area display_none" style="padding:10px">
			<form action="' . admin_url( 'ads' ) . '" method="post">
				<div class="ads_top"></div>
				
				<textarea cols="110" rows="15" name="ad_content"></textarea>
				<br />
				
				<div class="fancy_inputs_save">
					<input class="button save" type="submit" value="' . __('save') . '" />
					<div class="fancy_radio_check">
						<label><input class="on" type="radio" name="ad_activ" value="1" />' . __( 'option_on' ) . '</label>
						<label><input class="off" type="radio" name="ad_activ" value="0" />' . __( 'option_off' ) . '</label>
					</div>
				</div>
			
				
				<input type="hidden" name="ad_unique_name" value="" />
			</form>
		
			<div class="div-info-message">
				<p></p>
			</div>
		</div>
		<br />
		';
		
		
		
		
		$pagin = new Pagin_Tool;	
				
		echo $pagin->wrap()->addJS( 'ads', 15 )->get();
		
		echo '
		<form action="admin-save.php" enctype="multipart/form-data" method="post">	
				' . displayArrayOptions(array(
					'ads_frequency' => array(
						'option_new_block'	=> __( 'ads_settings' ),
						'option_is_array' => array(
							'between_files' => array(
								'option_type' => 'input',
								'option_lenght' => 3,
								'validation' => array(
									'match' => '^[0-9]{1,2}$',
									'set_value' => ''
								),
							),
							'between_comments' => array(
								'option_type' => 'input',
								'option_lenght' => 3,
								'validation' => array(
									'match' => '^[0-9]{1,2}$',
									'set_value' => ''
								),
							),
							'between_popular_posts' => array(
								'option_type' => 'input',
								'option_lenght' => 3,
								'validation' => array(
									'match' => '^[0-9]{1,2}$',
									'set_value' => ''
								),
							),
						)
					),
					
					
					
				)) . '
			<input name="submit" type="submit" class="button" value="' . __('save') . '" />
		</form> 
		';
		
		
		
		
		
	
	echo '
	<br />'.__('ads_comments_only_banner_text').'
	<br />
	<a href="http://forum.iprosoft.pl/thread-47.html">'.__('ads_how_add_php').'</a>
	<br /><br />
	';

?>