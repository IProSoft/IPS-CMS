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

class Premium_Controller
{
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public function route()
	{
		$premium = new PremiumPay();
		
		if ( isset( $_POST['code'] ) && !empty( $_POST['code'] ) )
		{
			$premium->checkCode( $_POST['code'], $_POST['service'] );
		}
		
		return $premium->getPayForm();
	}
}
?>