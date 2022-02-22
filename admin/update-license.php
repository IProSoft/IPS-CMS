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
	require_once( dirname( dirname( __FILE__ ) ) . '/php-load.php' );
	require_once( IPS_ADMIN_PATH .'/update-functions.php' );

	/** 
	* WERYFIKACJA - START
	* Weryfikacja licencji użytkownika.
	* Zapisywanie hash'a licencji.
	*/	
	if( !empty( $_GET ) && isset( $_GET['license_reset'] ) && isset( $_GET['license_reset_hash'] ) )
	{
		updateLicenseReset( $_GET['license_reset'], $_GET['license_reset_hash'] );
	}

?>