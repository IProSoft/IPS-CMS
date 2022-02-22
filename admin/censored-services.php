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
	
	echo admin_caption( 'censored_caption' );
	
	/* $import = json_decode( file_get_contents('http://api.iprosoft.pro/vulgarisms/?language=pl') );
	
	$exists = array_column( $censorship, 'word' );
	
	foreach( $import as $word => $change_to )
	{
		if( !in_array( $word, $exists ) )
		{
			$censorship[] = array(
				'word' => $word,
				'change_to' => $change_to
			);
		}
	}
	
	uasort($censorship, function ($a, $b) {
		return strcmp($a['word'], $b['word']);
	});
	
	
	Config::updateConfigValue( 'censorship', serialize($censorship) ); */
	
	
	$action = isset( $_GET['action'] ) ? $_GET['action'] : false;
	
	if( $action == 'settings' )
	{
		echo '
		<form method="post" enctype="multipart/form-data" action="admin-save.php">
			' . displayArrayOptions( array( 
					'adult_files' => array(
						'option_new_block' => true,
					),
					'add_is_adult_field' => array(
						'option_depends' => array( 
							'adult_files' => 1
						),
					)
				)) . '
			<input type="submit" value="' . __( 'save' ) . '" class="button" />
			<a role="button" class="button" href="' . admin_url( 'censored' ) . '">' . __('common_back') . '</a>
		</form>';
	}
	else
	{
		echo responsive_menu( array(
			'settings' => 'settings'
		), admin_url( 'censored', 'action=' ) ) . '
		<br />
		';
		
		$censored_words = Config::getArray('censored_words');
		
		if( isset( $_GET['delete'] ) )
		{
			unset( $censored_words[ $_GET['delete'] ] );
			
			Config::update( 'censored_words', serialize( $censored_words ) );
			
			return ips_admin_redirect('censored', false, 'censored_deleted' );
		}
		
		if( isset( $_POST['censored_words'] ) )
		{
			if( !empty( $_POST['censored_words']['word'] ) && !empty( $_POST['censored_words']['change_to'] ) )
			{
				unset( $_POST['censored_words']['form'] );
				
				if( !is_array( $censored_words )  )
				{
					$censored_words = array();
				}
				if( !isset( $_POST['censored_words']['id'] ) )
				{
					$censored_words[] = $_POST['censored_words'];
				}
				else
				{
					$censored_words[$_POST['censored_words']['id']] = $_POST['censored_words'];
				}
				
				Config::update( 'censored_words', serialize( $censored_words ) );
				
				if( isset( $_POST['censored_words']['id'] ) )
				{
					return ips_admin_redirect( 'censored', false, 'censored_edited' );
				}
			}
		}

		$censored_words_edit = isset( $_GET['edit'] ) && isset( $censored_words[ $_GET['edit'] ] ) ? $censored_words[ $_GET['edit'] ] : array();
		
		echo '<form action="" enctype="multipart/form-data" method="post">
			' . displayArrayOptions( array(	
				'censored_words[word]' => array(
					'current_value' => ( isset( $censored_words_edit['word'] ) ? $censored_words_edit['word'] : '' ),
					'option_set_text' =>  __( 'censored_word' ),
					'option_type' => 'input',
					'option_lenght' => 10,
				),
				'censored_words[change_to]' => array(
					'current_value' => ( isset( $censored_words_edit['change_to'] ) ? $censored_words_edit['change_to'] : '' ),
					'option_set_text' => __( 'censored_change_to' ),
					'option_type' => 'input',
					'option_lenght' => 10
				)
			)) ;
			
		if( isset( $censored_words_edit['change_to'] ) )
		{
			echo' <input type="hidden" name="censored_words[id]" value="' . $_GET['edit'] . '" />';
		}		
		echo '
			<input type="submit" name="censored_words[form]" class="button" value="' . __( isset( $censored_words_edit['change_to'] ) ? 'save' : 'common_add' ) .'" />
		</form>
		
		<div class="nice-blocks features-table-actions-div cenzored-service" style="max-width: 900px">
			<div class="blocks-header">' . __( 'censored_table' ) . '</div>
			<div class="blocks-content nice-blocks features-table-actions-div blocks-count-4">
				<div class="stats-blocks" style="width: 100%;border: 0;">
					<span class="table-header">' . __( 'censored_word' ) . '</span><span class="table-header">' . __( 'censored_change_to' ) . '</span><span class="table-header last">' . __( 'censored_action' ) . '</span>
					<div class="table-body" style="padding: 0px;">
						<table width="100%" cellspacing="0" cellpadding="0" class="blocks-table">
							<thead></thead>
							<tbody>';
					
					foreach( $censored_words as $id => $word )
					{			
						
						echo '		
							<tr>
								<td>' . $word['word'] . '</td>
							
								<td>' . $word['change_to'] . '</td>
							
								<td><a href="route-censored?edit=' . $id . '">' . __( 'common_edit' ). '</a> | <a href="route-censored?delete=' . $id . '">' . __( 'common_delete' ). '</a></td>
							</tr>
						';
					}				
			echo '
							</tbody>
						</table>
					</div>
				</div>
				

				<div class="clear"></div>
			</div>
		</div>
		<div class="div-info-message"><p>' . __('censored_words_info') . '</p></div>
		';
	}