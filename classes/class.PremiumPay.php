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

//define('_premium', false);
//define('_statusAccess', 'none');

class PremiumPay
{
    
    public $_oplata;
    
    public $_premium;
    
    public function __construct()
    {
        if ( !USER_LOGGED )
		{
            ips_redirect('login/', array( 
				'alert' => 'premium_login_pay'
			));
        }
    }
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function getPayForm()
    {
        
        $premium_services  = PD::getInstance()->select( 'premium_services' );
		
        foreach ( $premium_services as $key => $service )
		{
            $premium_services[$key]['fees'] = '
				<div id="serviceid-' . $service['service_id'] . '" class="sms-main" style="display:none;">
					' . str_replace( array(
						'{sms_number}',
						'{sms_content}',
						'{sms_price}',
						'{sms_extend_premium}',
						'{sms_description}'
					), array(
						'<span class="sms-number">' . $service['sms_number'] . '</span>',
						'<span class="sms-content">' . $service['sms_content'] . '</span>',
						'<span class="sms-price">' . $service['sms_price'] . '</span>',
						'<span class="sms-extend">' . $service['sms_extend_premium'] . '</span>',
						'<div class="sms-description">' . $service['sms_description'] . '</div>'
					), __( 'premium_fees' ) ) . '
				</div>
			';
        }

        return Templates::getInc()->getTpl( 'premium.html', array(
            'premium_services' => $premium_services,
            'admin_description' => str_replace( '{email}', Config::get('email_admin_user'), __('premium_decription_bottom') )
        ));
    }
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function checkCode( $code, $service_id )
    {
        $service = PD::getInstance()->select("premium_services", array( 
			'service_id' => $service_id
		), 1);
        
		if ( empty( $service ) )
		{
			return ips_redirect( 'premium.html', array( 'alert' => 'premium_service_invalid' ) );
		}
		
		$verified = $this->checkBase( $code, $service_id );
		
		if ( !$verified && $service['sms_codes_verify'] == 0 )
		{
			$verified = $this->checkDotpay( $code, $service );
		}
		
		if ( !$verified )
		{
			return ips_redirect( 'premium.html', array( 
				'alert' => 'premium_code_invalid'
			) );
		} 
       
		$premium = new Premium();
		$created = $premium->createPremum( $service['sms_extend_premium'], USER_ID );
		
		if ( $created )
		{
			$this->expiryCode( $code, $service );
			
			ips_message( array( 
				'info' =>  __s( 'premium_code_ok', $service['sms_extend_premium'] )
			) );
		}
		else
		{
			ips_message( array( 
				'alert' => 'err_unknown'
			) );
		}
		
		return ips_redirect( Cookie::get( 'ips-redir', 'index.html' ) );
    }
	/* 
     * Set code to expired
     */
    public function expiryCode( $code, $service )
    {
        if( $service['delete_codes'] && $service['sms_codes_verify'] == 1 )
		{
			return PD::getInstance()->delete( 'premium_codes', array( 
				'code' => $code, 
				'service_id' => $service['service_id']
			) );
		}
		
		return PD::getInstance()->insertUpdate( 'premium_codes', array( 
			'code' => $code, 
			'service_id' => $service['service_id'], 
			'code_activ' => 0
		) );
    }
    /* 
     * Check selected text code in the database and return a true if there is false if not found sms
     */
    public function checkBase( $code, $service_id )
    {
        $sms = PD::getInstance()->select( 'premium_codes', array( 
			'code' => $code, 
			'service_id' => $service_id, 
			'code_activ' => 1
		), 1);
        
		return !empty( $sms ) && $sms['code_activ'] == 1;
    }
    /* 
     * Sprawdzamy wybrany kod SMS w serwisie Dotpay
     * i zwracamy true jeśli taki istnieje
     * false jeśli sms nie został znaleziony.
     * Check selected text code in Dotpay and turn true if one exists sms false if not found.
     */
    public function checkDotpay( $code, $service )
    {
        $data = curlIPS('https://ssl.dotpay.pl/check_code.php', array(
            'timeout' => 10,
            'post_data' => array(
				'check' => $code,
				'code' => $service['sms_service_name'],
				'id' => $service['provider_id'],
				'type' => 'sms,c1',
				'del' => 1
				//'del' => $service['delete_codes']
			)
        ));
		
		$data = explode("\n", $data );
        
        return $data[0] == 1;
    }
    /* 
     * Adding SMS code to the pool of available codes
     */
    public function addSMSCode( $code, $service_id )
    {
		PD::getInstance()->increase( 'premium_services', array( 
			'codes_used' => 1
		), array( 
			'service_id' => $service_id
		));
		
		$sms = PD::getInstance()->select( 'premium_codes', array( 
			'service_id' => $service_id,
			'code' => $code
		), 1);
		
        if ( empty( $sms ) )
		{
           PD::getInstance()->insert( 'premium_codes', array(
                'service_id' => $service_id,
                'code' => $code,
                'code_activ' => 1
            ));
        }
    }
    
    /* 
     * Adding SMS (number and price)
     */
    public function addService( $service_data )
    {
        $data = array(
			'sms_number' => $service_data['sms_number'],
			'sms_price' => $service_data['sms_price'],
			'sms_service_name' => $service_data['sms_service_name'],
			'sms_content' => $service_data['sms_content'],
			'sms_extend_premium' => $service_data['sms_extend_premium'],
			'sms_codes_verify' => $service_data['sms_codes_verify'],
			'sms_description' => $service_data['sms_description'],
			'delete_codes' => $service_data['delete_codes'],
			'provider_id' => $service_data['provider_id']
		);
		
		$data = array_map( 'trim', $data );
	
		if ( !empty( $service_data['service_id'] ) )
		{
            PD::getInstance()->update( 'premium_services', $data, array(
				'service_id' => $service_data['service_id']
			));
			
			return $service_data['service_id'];
        }
		else
		{
            return PD::getInstance()->insert( 'premium_services', $data );
        }
    }
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	
	public static function form( $service_id = false )
	{
		if( $service_id )
		{
			$service = PD::getInstance()->select( 'premium_services', array( 
				'service_id' => $service_id
			), 1 );
			
			Config::tmp( 'services_premium', array_merge( array( 
				'sms_extend_premium' => 0
			), ( $service ? $service : array() ) ));
		}
		
		return '
				<form action="" enctype="multipart/form-data" method="post">
					' . displayArrayOptions(array(
						'services_premium' => array(
							'option_depends' => 'services_premium',
							'option_new_block' => __('services_premium_service_list'),
							'option_is_array' => array(
								'sms_codes_verify' => array(
									'option_set_text' => 'services_premium_sms_verification',
									'option_select_values' => array(
										0 => __('services_premium_sms_verification_dotpay'),
										1 => __('services_premium_sms_import')
									),
									'option_css' => 'dotpay_change'
								),
								'codes_list' => array(
									'option_type' => 'textarea',
									'option_css' => ( Config::getArray( 'services_premium', 'sms_codes_verify' ) == 0 ? 'display_none ' : '' ) . 'sms-codes'
								),
								'codes_file' => array(
									'option_type' => 'text',
									'option_value' => '<input type="file" name="codes_file" />',
									'option_css' => ( Config::getArray( 'services_premium', 'sms_codes_verify' ) == 0 ? 'display_none ' : '' ) . 'sms-codes'
								),
								'delete_codes' => array(
									'option_css' => ( Config::getArray( 'services_premium', 'sms_codes_verify' ) == 0 ? 'display_none ' : '' ) . 'sms-codes'
								),
								'provider_id' => array(
									'option_type' => 'input',
									'option_lenght' => 50,
									'option_css' => ( Config::getArray( 'services_premium', 'sms_codes_verify' ) == 0 ? '' : 'display_none ' ) . 'sms-codes'
								),
								'sms_service_name' => array(
									'option_type' => 'input',
									'option_lenght' => 50,
									'option_css' => ( Config::getArray( 'services_premium', 'sms_codes_verify' ) == 0 ? '' : 'display_none ' ) . 'sms-codes'
								),
								'sms_number' => array(
									'option_type' => 'input',
									'option_lenght' => 50
								),
								'sms_content' => array(
									'option_type' => 'input',
									'option_lenght' => 50
								),
								'sms_price' => array(

									'option_type' => 'input',
									'option_lenght' => 50
								),
								'sms_description' => array(
									'option_type' => 'textarea',
								),
								'sms_extend_premium' => array(
									'option_type' => 'range',
									'option_min' => 1,
									'option_max' => 365
								)
							)
						),
					)) . '
					<input type="hidden" name="services_premium[service_id]" value="' . Config::getArray( 'services_premium', 'service_id' ) . '" />
					<input name="sms_services_form" type="submit" class="button" value="' . __('save') . '" />
				</form>';
	}
    /*
     * Removing Premium SMS
     */
    public function deleteService( $service_id )
    {
        $sms = PD::getInstance()->select( 'premium_services', array( 
			'service_id' => $service_id
		) );
		
        if ( !empty( $sms ) )
		{
            PD::getInstance()->delete( array( 'premium_services', 'premium_codes' ), array( 
				'service_id' => $service_id
			));
        }
    }
}
?>