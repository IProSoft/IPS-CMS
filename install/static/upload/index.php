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
	$path = dirname( dirname( dirname( dirname( __FILE__ ) ) ) );

	if( isset( $_FILES['file_data'] ) )
	{
		if ( $_FILES['file_data']['error'] == 0 )
		{
			move_uploaded_file( $_FILES['file_data']['tmp_name'], dirname( __FILE__ ) . '/logo.png'  );
			
		}
	}
	header('Cache-Control: no-cache, must-revalidate');
	header('Content-type: application/json');
	echo json_encode(array());
?>