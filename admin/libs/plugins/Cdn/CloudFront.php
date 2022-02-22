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
	
class CloudFront
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
		
		$aws_service_id = Config::getArray('plugin_cdn_config', 'aws_service_id');
		
		$action = isset( $_GET['cdn_action'] ) ? $_GET['cdn_action'] : false;
		
	
		switch ( $action )
		{
			
			case 'add':
				
				$client = $this->getClient();
				
				$result = false;
				
				if( $aws_service_id )
				{
					try{
					
						$result = $client->getDistribution( array(
							'Id' => $aws_service_id,
						) )->toArray();
						
					} catch ( Exception $e ) {}
				}
				
				if( !empty( $result ) )
				{
					ips_admin_redirect( 'plugins', 'plugin=Cdn', __s( 'plugin_cdn_already_associated_service', PLUGIN_CDN_URL ) );
				}
				else
				{
					$service = $this->createService( $client );
					
					if( $service === true )
					{
						ips_admin_redirect( 'plugins', 'plugin=Cdn', 'plugin_cdn_service_created');
					}
					else
					{
						ips_admin_redirect( 'plugins', 'plugin=Cdn', __s('plugin_cdn_an_error_occurred', $service ) );
					}
				}
			break;
			
			case 'clear_cache':
				
				$client = $this->getClient();
				
				$service = $this->deleteService( $aws_service_id, $client );
				
				if( $service === true )
				{
					$service = $this->createService( $client );
					
					if( $service === true )
					{
						ips_admin_redirect( 'plugins', 'plugin=Cdn', 'plugin_cdn_service_created');
					}
					else
					{
						ips_admin_redirect( 'plugins', 'plugin=Cdn', __s( 'plugin_cdn_an_error_occurred', $service ) );
					}
				}

				ips_admin_redirect( 'plugins', 'plugin=Cdn', __( 'plugin_cdn_error_cache_cleaning' ) . '<br />' . $service );
				
			break;
			
			case 'delete':
				
				$client = $this->getClient();
				
				$service = $this->deleteService( $aws_service_id, $client );
				
				if( $service === true )
				{
					ips_admin_redirect( 'plugins', 'plugin=Cdn', 'plugin_cdn_the_service_has_been_removed');
				}
				
				ips_admin_redirect( 'plugins', 'plugin=Cdn', 'plugin_cdn_error_removal_services');
				
			break;
			default:

				$cdn_url = Config::tmp( 'ips_upload_url' );
				
				if( !empty( $_POST ) )
				{
					if( !empty( $_POST['key'] ) )
					{
						Config::update( 'plugin_cdn_config', array(
							'aws_key' => $_POST['key'],
							'aws_secret' => ( !empty( $_POST['secret'] ) ? $_POST['secret'] : Config::getArray('plugin_cdn_config', 'aws_secret') )
						) );
					}
					
					if( !empty( $_POST['cdn_url'] ) )
					{
						$aws_host = $this->hostVerifyID( $_POST['cdn_url'] );
						
						if( !$aws_host )
						{
							ips_admin_redirect( 'plugins', 'plugin=Cdn', 'plugin_cdn_add_valid_host_name');
						}
						
						Config::update( 'plugin_cdn_config', array(
							'aws_host' => $aws_host
						) );
						
						Config::update( 'ips_upload_url', 'http://' . $aws_host . '.cloudfront.net' );
					}

					ips_admin_redirect( 'plugins', 'plugin=Cdn', 'plugin_cdn_saved');
				}
					
				echo '
		
					<form action="" enctype="multipart/form-data" method="post">	
					' . displayArrayOptions(array(
						'key' => array(
							'option_new_block' => 'AWS Cloud Front <a href="https://aws.amazon.com/cloudfront/">https://aws.amazon.com/cloudfront/</a>',
							'current_value' => Config::getArray('plugin_cdn_config', 'aws_key'),
							'option_set_text' => 'Key',
							'option_type' => 'input',
							'option_lenght' => 10
						),
						'secret' => array(
							'current_value' => '',
							'option_set_text' => 'Secret',
							'option_type' => 'password',
							'option_lenght' => 10,
							'option_placeholder' => __('plugin_cdn_change')
						),
						'cdn_url' => array(
							'current_value' => Config::getArray('plugin_cdn_config', 'aws_host'),
							'option_set_text' => __('plugin_cdn_host_create_info'),
							'option_type' => 'text',
							'option_value' => '<input class="with_url" type="text" name="cdn_url" value="' . Config::getArray('plugin_cdn_config', 'aws_host') . '" /><i class="with_url">.cloudfront.net</i>'
						)
						,
						'aws_service_id' => array(
							'current_value' => '',
							'option_set_text' => __('plugin_cdn_or'),
							'option_type' => 'text',
							'option_value' => ( empty( $aws_service_id ) ? '<a href="' . PLUGIN_CDN_URL . '&cdn_action=add" class="button">' . __( 'plugin_cdn_create_service' ) . '</a>' : '<a href="' . PLUGIN_CDN_URL . '&cdn_action=delete" class="button">' . __( 'plugin_cdn_remove_service' ) . '</a>' )
						),
					)) . '

					
					<button class="button">' . __( 'save' ) . ' </button>
					</form>

				<div class="div-info-message">
					<p><strong>Key/Secret:</strong><a href="http://blogs.aws.amazon.com/security/post/Tx1R9KDN9ISZ0HF/Where-s-my-secret-access-key">http://blogs.aws.amazon.com/security/post/Tx1R9KDN9ISZ0HF/Where-s-my-secret-access-key</a></p>
				</div>
				';	
				
			break;
		}
	}
	
	public function deleteService( $aws_service_id, $client )
	{

		if( $aws_service_id )
		{
			try{
			
				$result = $client->getDistribution( array(
					'Id' => $aws_service_id,
				) )->toArray();
	
				if( $result['DistributionConfig']['Enabled'] == true )
				{
				
					$result['DistributionConfig']['Id'] = $aws_service_id;
					$result['DistributionConfig']['Enabled'] = false;
					$result['DistributionConfig']['IfMatch'] = $result['ETag'];
					$result['DistributionConfig']['Origin'] = array(
						'Quantity' => 0,
						'Items' => array()
					);
					
					
					$result = $client->updateDistribution( $result['DistributionConfig'] )->toArray();
					
					Config::remove( 'ips_upload_url' );
				}
				
				$result = $client->deleteDistribution(array(
					'Id' => $aws_service_id,
					'IfMatch' => $result['ETag'],
				));
				
				Config::remove( 'ips_upload_url' );
				
				
			
			} catch ( Aws\CloudFront\Exception\NoSuchDistributionException $e ) {
				
				Config::remove( 'ips_upload_url' );

			} catch ( Exception $e ) {
				
				return $e->getMessage();
			}
			
			Config::update( 'plugin_cdn_config', array(
				'aws_service_id' => false
			) );
			
			return true;
		}
	}
	
	public function getClient()
	{
		$key = Config::getArray('plugin_cdn_config', 'aws_key');
		$secret = Config::getArray('plugin_cdn_config', 'aws_key');
		
		if( empty( $key ) || empty( $secret ) )
		{
			ips_admin_redirect( 'plugins', 'plugin=Cdn', 'plugin_cdn_enter_key_secret_aws' );
		}
		
		try {
		
			$client = include_once( dirname( __FILE__ ) . '/Aws.php');
		
			if( is_object( $client ) )
			{
				$client->listDistributions();
				
				return $client;
			}
		
		} catch ( Exception $e ) {}
		
		ips_admin_redirect( 'plugins', 'plugin=Cdn', 'plugin_cdn_error_authentication' );
	}
	
	
	public function createService( $client )
	{	
		
		$domain_name = parse_url( ABS_URL, PHP_URL_HOST );
		
		try {
		
			$originId = 'IPS-' . $domain_name;

			$result = $client->createDistribution(array(
				'Aliases' => array('Quantity' => 0),
				'Origins' => array(
					'Quantity' => 1,
					'Items' => array(
						array(
							'Id' => $originId,
							'DomainName' => $domain_name,
							'CustomOriginConfig' => array(
								'HTTPPort' => 80,
								'HTTPSPort' => 443,
								'OriginProtocolPolicy' => 'http-only',
							),
						)
					)
				),
				'CacheBehaviors' => array('Quantity' => 1,
					'Items' => array(
						array(
							'PathPattern' => '/upload/*',
							'MinTTL' => 86400,
							'ViewerProtocolPolicy' => 'allow-all',
							'TargetOriginId' => $originId,
							'TrustedSigners' => array(
								'Enabled'  => false,
								'Quantity' => 0
							),
							'ForwardedValues' => array(
								'QueryString' => false,
								'Cookies' => array(
									'Forward' => 'none'
								)
							)
						)
					)
				),
				'Comment' => 'IPS - ' . $domain_name ,
				'Enabled' => true,
				'CallerReference' => 'BazBar-' . time(),
				'DefaultCacheBehavior' => array(
					'MinTTL' => 86400,
					'ViewerProtocolPolicy' => 'allow-all',
					'TargetOriginId' => $originId,
					'TrustedSigners' => array(
						'Enabled'  => false,
						'Quantity' => 0
					),
					'ForwardedValues' => array(
						'QueryString' => false,
						'Cookies' => array(
							'Forward' => 'none'
						)
					)
				),
				'DefaultRootObject' => 'index.html',
				'Logging' => array(
					'Enabled' => false,
					'Bucket' => '',
					'Prefix' => '',
					'IncludeCookies' => true,
				),
				
				'PriceClass' => 'PriceClass_All',
			))->toArray();
	
			if( $result['Id'] )
			{
				Config::update( 'plugin_cdn_config', array(
					'aws_service_id' => $result['Id'],
					'aws_host' => str_replace( '.cloudfront.net', '', $result['DomainName'] )
				) );
				
				Config::update( 'ips_upload_url', 'http://' . $result['DomainName'] );
				
				return true;
			}
		
		} catch ( Exception $e ) {
			
			return $e->getMessage();
		}
	}
	public function hostVerifyID( $id )
	{
		if( !preg_match( "/^[0-9a-zA-Z]+$/", $id ) )
		{
			return false;
		}
		
		return $id;
	}
}




?>