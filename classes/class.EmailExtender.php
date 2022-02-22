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

class EmailExtender
{
	
	public $errorInfo = false;
	
	public function Email( $email_title, $email_content, $email_to )
	{
		$admin_mail = Config::get( 'email_admin_user' );
		
		/* 
		require_once "Mail.php";
		$smtp = Mail::factory('sendmail', array (
		'host' => Config::getArray( 'email_smtp_options', 'host' ),
		'port' => Config::getArray( 'email_smtp_options', 'port' ),
		'debug' => Config::getArray( 'email_smtp_options', 'auth_on' ),
		'username' => Config::getArray( 'email_smtp_options', 'login' ),
		'password' => Config::getArray( 'email_smtp_options', 'password' ),
		'debug' => true,
		));
		
		$mail = $smtp->send( $email_to, array (
		'From' => $admin_mail,
		'To' => $email_to,
		'Subject' => $email_title
		), $email_content );
		
		return !PEAR::isError( $mail );
		*/
		
		
		//ini_set ( "SMTP", "smtp-htdocs.iprosoft.pl" ); 
		ini_set( "sendmail_from", $admin_mail );
		
		require_once( LIBS_PATH . '/PHPMailer/class.phpmailer.php' );
		
		$mail = new PHPMailer( true );
		
		try
		{
			
			$mail->PluginDir = LIBS_PATH . '/PHPMailer/';
			
			if ( USER_ADMIN )
			{
				//$mail->SMTPDebug = true;
			}
			
			if ( Config::get( 'email_smtp' ) == 1 )
			{
				$mail->IsSMTP();
				$mail->Mailer        = 'smtp';
				$mail->Host          = Config::getArray( 'email_smtp_options', 'host' );
				$mail->Port          = Config::getArray( 'email_smtp_options', 'port' );
				$mail->SMTPKeepAlive = true;
				$mail->SMTPAuth      = Config::getArray( 'email_smtp_options', 'auth_on' );
				$mail->SMTPSecure    = "tls";
				$mail->Username      = Config::getArray( 'email_smtp_options', 'login' );
				$mail->Password      = Config::getArray( 'email_smtp_options', 'password' );
				if ( Config::getArray( 'email_smtp_options', 'ssl' ) )
				{
					$mail->SMTPSecure = 'ssl';
				}
			}
			else
			{
				$mail->Mailer = "mail";
			}
			
			$mail->CharSet     = 'UTF-8';
			$mail->ContentType = 'text/html';
			
			
			$mail->AddReplyTo( $admin_mail, 'Admin ' . str_replace( array(
				'http:',
				'/' 
			), array(
				'',
				'' 
			), ABS_URL ) );
			$mail->SetFrom( $admin_mail, 'Admin ' . str_replace( array(
				'http:',
				'/' 
			), array(
				'',
				'' 
			), ABS_URL ) );
			
			global ${IPS_LNG};
			$mail->Subject = !empty( $email_title ) ? $email_title : ${IPS_LNG}['meta_site_title'];
			
			$mail->IsHTML( true );
			$mail->Body    = $email_content;
			$mail->AltBody = strip_tags( str_replace( "<br />", "\n", $email_content ) );
			$emails        = !is_array( $email_to ) ? array(
				$email_to 
			) : $email_to;
			
			foreach ( $emails as $email_to )
			{
				$mail->AddAddress( $email_to );
			}
			
			if ( $mail->Send() )
			{
				$mail->ClearAddresses();
				$mail->SmtpClose();
				return true;
			}
			
		}
		catch ( phpmailerException $e )
		{
			
			$this->errorInfo = $e->errorMessage();
			return false;
		}
		catch ( Exception $e )
		{
			$this->errorInfo = $e->getMessage();
			return false;
		}
		
		return false;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function EmailTemplate( $email_data, $footer_text = 'email_default' )
	{
		
		/* 
		array(
		'email_content'	=> '',
		'email_title'	=> '',
		'email_footer'	=> ''
		) 
		*/
		
		if ( !isset( $email_data['email_header_title'] ) )
		{
			$email_data['email_header_title'] = $email_data['email_title'];
		}
		
		if ( !isset( $email_data['email_footer'] ) )
		{
			$email_data['email_footer'] = '<br />' . __( $footer_text . '_footer' );
		}
		
		$email_content = Templates::getInc()->getTpl( 'email.html', $email_data );

		return $this->Email( $email_data['email_header_title'], $email_content, $email_data['email_to'] );
	}
}
?>
