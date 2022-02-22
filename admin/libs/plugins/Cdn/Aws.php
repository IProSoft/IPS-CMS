<?php
include_once( dirname( __FILE__ ) . '/AwsAmazon/aws-autoloader.php');
use Aws\Common\Aws;
use Aws\Common\Credentials\Credentials;
use Aws\CloudFront\CloudFrontClient;
	
try {
	
	$aws = Aws::factory( array(
		'key' => Config::getArray('plugin_cdn_config', 'aws_key'),
		'secret' => Config::getArray('plugin_cdn_config', 'aws_secret')
	) );
	
	return $aws->get('CloudFront');
	
} catch ( Exception $e ) {
	
	return false;
}
