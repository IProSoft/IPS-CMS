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
	
/*
Name: Cdn
DisplayName: Content CDN
Description: Plugin umożliwia cachowanie tresci na serwerze zwenętrznym Amazon CloudFront / CDN77 <br /> Wymaga PHP 5.4+, cURL 7.16.2+ skompilowanego z OpenSSL i zlib
Data: 2012-01-16
*/

class Cdn
{
	public $license = '[license_number]';
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function install()
	{
		Config::update( 'plugin_cdn_config', array(
			'cdn_storage' => 'cloud_front',
			'aws_key' => '',
			'aws_secret' => '',
			'aws_service_id' => '',
			'aws_host' => '',
			'cdn77_host' => '',
		) );
		
		Config::update( 'plugin_cdn_config', array(
			'aws_key' => 'AKIAJUHN6CJIKZJUJZBA',
			'aws_secret' => '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'
		) );
	}

	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/			
	public function uninstall()
	{
		Config::remove( 'ips_upload_url' );
		Config::remove( 'plugin_cdn_config' );
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function info()
	{
		return array(
			'pl_PL' => array(
				'plugin_name' => 'Cdn',
				'plugin_description' => 'Plugin umożliwia cachowanie tresci na serwerze zwenętrznym Amazon CloudFront / CDN77 <br /> Wymaga PHP 5.4+, cURL 7.16.2+ skompilowanego z OpenSSL i zlib',
				'plugin_display_name' => 'Content CDN',
			),
			'en_US' => array(
				'plugin_name' => 'Cdn',
				'plugin_description' => 'Plugin ads ability to store files in cloud server Amazon / CloudFront / CDN77 <br /> Requires PHP 5.4+, cURL 7.16.2+ compiled with OpenSSL and zlib',
				'plugin_display_name' => 'Content CDN',
			)
		);
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function language()
	{
		return array(
			'pl_PL' => array(
				'plugin_cdn_saved_settings' => 'Zapisano ustawienia',
				'plugin_cdn_service' => 'Serwis CDN',
				'plugin_cdn_set_type' => 'Wybierz rodzaj serwisu CDN',
				'plugin_cdn_add_valid_host_name' => 'Podaj poprawną nazwę hosta',
				'plugin_cdn_saved' => 'Zapisano',
				'plugin_cdn_info' => '<strong>Zakładanie konta:</strong>Zakładając konto na stronie cdn77 wystarczy uzupełnić dane wpisując Origin: %s, a jako CNAMEs subdomenę: cdn.%s',
				'plugin_cdn_already_associated_service' => 'Masz już powiązaną usługę z tą stroną. <a href="%s&cdn_action=delete" class="button">Usuń usługę</a>',
				'plugin_cdn_service_created' => 'Usługa została utworzona. Zapisanie w cache może potrwać kilkadziesiąt minut.',
				'plugin_cdn_an_error_occurred' => 'Wystąpił błąd: %s',
				'plugin_cdn_error_cache_cleaning' => 'Wystąpił błąd podczas czyszczenia cache. Poczekaj kilkadziesiąt minut i spróbuj ponownie. ',
				'plugin_cdn_the_service_has_been_removed' => 'Usługa została usunięta',
				'plugin_cdn_error_removal_services' => 'Wystąpił błąd podczas usuwania usługi. Poczekaj kilkadziesiąt minut gdy status usługi zmieni się na "Deployed" i spróbuj ponownie.',
				'plugin_cdn_change' => 'Kliknij aby zmienić/wprowadzić',
				'plugin_cdn_host_create_info' => 'Host CDN ( wprowadź, jeśli masz już utworzoną usługę poza skryptem )',
				'plugin_cdn_or' => 'lub',
				'plugin_cdn_create_service' => 'Utwórz usługę',
				'plugin_cdn_remove_service' => 'Usuń powiązaną usługę',
				'plugin_cdn_enter_key_secret_aws' => 'Wprowadź Key / Secret AWS',
				'plugin_cdn_error_authentication' => 'Wystąpił błąd podczas autoryzacji danych Key\Secret',
			),
			'en_US' => array(
				'plugin_cdn_saved_settings' => 'Zapisano ustawienia',
				'plugin_cdn_service' => 'Serwis CDN',
				'plugin_cdn_set_type' => 'Wybierz rodzaj serwisu CDN',
				'plugin_cdn_add_valid_host_name' => 'Podaj poprawną nazwę hosta',
				'plugin_cdn_saved' => 'Zapisano',
				'plugin_cdn_info' => '<strong>Zakładanie konta:</strong>Zakładając konto na stronie cdn77 wystarczy uzupełnić dane wpisując Origin: %s, a jako CNAMEs subdomenę: cdn.%s',
				'plugin_cdn_already_associated_service' => 'Masz już powiązaną usługę z tą stroną. <a href="%s&cdn_action=delete" class="button">Usuń usługę</a>',
				'plugin_cdn_service_created' => 'Usługa została utworzona. Zapisanie w cache może potrwać kilkadziesiąt minut.',
				'plugin_cdn_an_error_occurred' => 'Wystąpił błąd: %s',
				'plugin_cdn_error_cache_cleaning' => 'Wystąpił błąd podczas czyszczenia cache. Poczekaj kilkadziesiąt minut i spróbuj ponownie. ',
				'plugin_cdn_the_service_has_been_removed' => 'Usługa została usunięta',
				'plugin_cdn_error_removal_services' => 'Wystąpił błąd podczas usuwania usługi. Poczekaj kilkadziesiąt minut gdy status usługi zmieni się na "Deployed" i spróbuj ponownie.',
				'plugin_cdn_change' => 'Kliknij aby zmienić/wprowadzić',
				'plugin_cdn_host_create_info' => 'Host CDN ( wprowadź, jeśli masz już utworzoną usługę poza skryptem )',
				'plugin_cdn_or' => 'lub',
				'plugin_cdn_create_service' => 'Utwórz usługę',
				'plugin_cdn_remove_service' => 'Usuń powiązaną usługę',
				'plugin_cdn_enter_key_secret_aws' => 'Wprowadź Key / Secret AWS',
				'plugin_cdn_error_authentication' => 'Wystąpił błąd podczas autoryzacji danych Key\Secret',
			)
		);
	}
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function init()
	{
		
		if( strpos( base64_decode('[license_code]'), str_replace( array( 'www.', 'http://', '/' ), '', ABS_URL ) ) === false && !defined('IPS_SELF') )
		{
			die( '<br />' . __( base64_decode( 'aXBzX2xpY2Vuc2Vfbm90X2Fzc2lnbmVk' ) ) );
		}
		
		$cdn_storage = Config::getArray('plugin_cdn_config', 'cdn_storage');
		
		define( 'PLUGIN_CDN_URL', admin_url( 'plugins', 'plugin=Cdn&cdn_storage=' . $cdn_storage ));
		
		if( !empty( $_POST ) )
		{
			if( !empty( $_POST['cdn_storage'] ) )
			{
				Config::update( 'plugin_cdn_config', array(
					'cdn_storage' => $_POST['cdn_storage']
				) );
				
				Config::remove( 'ips_upload_url' );
				
				ips_admin_redirect( 'plugins', 'plugin=Cdn', 'plugin_cdn_saved_settings' );
			}
		}
		
		echo '
			<form action="" enctype="multipart/form-data" method="post">	
				' . displayArrayOptions(array(
					'cdn_storage' => array(
						'option_new_block' => __( 'plugin_cdn_service' ),
						'current_value' => $cdn_storage,
						'option_set_text' => __( 'plugin_cdn_set_type' ),
						'option_select_values' => array(
							'cloud_front' => 'Cloud Front',
							'cdn77' => 'CDN77',
						)
					)
				)) .'
				<button class="button">' . __( 'save' ) . ' </button>
			</form>

		';	
		
		switch ( $cdn_storage )
		{
			case 'cdn77':
				include_once( dirname( __FILE__ ) . '/CDN77.php');
				$cloud = new CDN77();
			break;
			case 'cloud_front':
			default:
				include_once( dirname( __FILE__ ) . '/CloudFront.php');
				$cloud = new CloudFront();
			break;
		}
		
		$cloud->init();
		
	}
	
}




?>