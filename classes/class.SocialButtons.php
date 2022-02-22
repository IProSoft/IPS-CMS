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

class SocialButtons
{
    public static $app = array(
		'google' => false, 
		'twitter' => false, 
		'nk' => false
	);
	
    public static $_nk_login = false;
    
    public static $_info = array();
    
   	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function config()
    {
		add_action( 'after_footer', 'SocialButtons::socialScript' );
		
		$lang_social = Config::getArray( 'language_settings', 'language_locales' );
	
		self::$app = array_merge( Config::getArray( 'social_plugins' ), self::$app, array(
			'colorscheme_comments' => Config::getArray( 'comments_options', 'comments_facebook_color_scheme' ),
			'conf_language' => $lang_social[IPS_LNG],
			'short_lang' => substr( $lang_social[IPS_LNG], 0, 2 ),
		));
    }

    /**
     * Saving header info for social networking required by
     *
     * @param $site_info - the array returned by the class Site_Info
     * @param $row - table with info on the material
     * 
     * @return void
     */
    public static function initSocial( &$site_info, $row )
    {
        self::config();
		
        self::$_info['title']       = $site_info['site_title'];
        self::$_info['description'] = $site_info['site_description'];

        if ( !empty( $row ) && IPS_ACTION == 'file_page' )
		{
            self::$_info['file_thumb'] = self::getThumb( $row );
            self::$_info['current_url']= seoLink( $row['id'], $row['title'] );
            
            /* 
			
            self::$_info['sizes'] = ips_img_size( $row, 'medium' );
			
			*/
        }
		else
		{
            self::$_info['file_thumb'] = array(
				'img' => ABS_URL . 'images/logo-' . IPS_VERSION . '.png'
			);
            self::$_info['current_url']= get_current_url();
        }
    }
	
	
	public static function getThumb( $row )
	{
		$upload_data = unserialize( $row['upload_data'] );
		
		if( isset( $upload_data['og-thumb'] ) )
		{
			return array(
				'img' => ips_img( $row, 'og-thumb' ),
				'sizes' => $upload_data['og-thumb']
			);
		}
		
		return array(
			'img' => ips_img( $row, 'medium' ),
			'sizes' => $upload_data['medium']['file']
		);
	}
    /**
     * Generate header Open Graph data
     *
     * @param $page_type - specifies the type of the page for facebook
     * https://developers.facebook.com/docs/technical-guides/opengraph/built-in-objects/
     *
     * @return string $meta
     */
    public static function getHeader( $page_type = 'article' )
    {
        $apps_facebook = Config::getArray('apps_facebook_app');
		 
		$og_meta = array(
			'og:app_id' 		=> ( Facebook_UI::isAppValid() ? $apps_facebook['app_id'] : false ) ,
			'fb:admins' 		=> ( Facebook_UI::validUserId( $apps_facebook['admin_id'] ) ? $apps_facebook['admin_id'] : false ),
			'og:site_name' 	=> htmlspecialchars( __( 'meta_site_title' ) ),
			'og:type' 			=> ( isset( self::$_info['video'] ) ? 'video' : $page_type ),
			'og:url' 			=> self::$_info['current_url'],
			'og:title' 		=> self::$_info['title'],
			'og:description' 	=> ( self::$_info['description'] != self::$_info['title'] ? self::$_info['description'] : false ),
			'og:image' 		=> self::$_info['file_thumb']['img'],
			'og:video'			=> ( isset( self::$_info['video'] ) ? self::$_info['video'] : false ),
			'og:video:width'	=> ( isset( self::$_info['video'] ) ? '480' : false ),
			'og:video:height'	=> ( isset( self::$_info['video'] ) ? '480' : false ),
		);
		

		$meta = "\n";
        
		if( IPS_ACTION == 'file_page' )
		{
			$meta .= "\t" . '<link rel="canonical" href="' . ABS_URL . IPS_ACTION_GET_ID . '/" />' . "\n\n";
			
			if( isset( self::$_info['file_thumb']['sizes'] ) )
			{
				$og_meta['og:image:width'] = self::$_info['file_thumb']['sizes']['width'];
				$og_meta['og:image:height'] = self::$_info['file_thumb']['sizes']['height'];
			}
		}
		
		$og_meta = apply_filters( 'og_meta', $og_meta, self::$_info );
		
		foreach( $og_meta as $og_name => $content )
		{
			if( $content )
			{
				$meta .= "\t" . '<meta property="' . $og_name . '" content="' . $content . '" />' . "\n";
			}
		}
		
		return $meta . "\n";
    }
    
	/**
     * I like Facebook button
     *
     * @param $link - link / url for the generated button
     * @param $width - the width of the container with a button on a
     * @param $layout - layout (button_count, box_count, standard)
     * @param $send - button allows you to send a link to friends outpouring
     * @param $faces - determines whether or not the plug to display the profile photos
     * 
     * @return string
     * https://developers.facebook.com/docs/reference/plugins/like/
     */
    public static function like( $link, $layout = 'button_count', $share = 'false', $send = 'false', $faces = 'false')
    {
        return sprintf('<div class="tools"><div class="fb-like" data-href="%s" data-send="%s" data-layout="%s" data-show-faces="%s" data-font="%s" data-colorscheme="%s" data-share="%s" data-action="like"></div></div>', $link, $send, $layout, $faces, self::$app['scheme_font'], self::$app['colorscheme'], $share );
 
    }
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public static function facepile($type = 'html5')
    {
        if ($type == 'html5') {
            return sprintf('<div class="fb-facepile" data-app-id="%s" data-max-rows="1" data-colorscheme="light" data-size="medium" data-show-count="true"></div>', Config::get('apps_facebook_app', 'app_id'), self::$app['colorscheme']);
        } else {
            
            return sprintf('<iframe class="fb_facepile async_iframe" scrolling="no" frameborder="0" allowtransparency="true" data-src="//www.facebook.com/plugins/facepile.php?app_id=%s&amp;size=large&amp;max_rows=1&amp;width=500&amp;colorscheme=%s&amp;locale=%s"></iframe>', Config::get('apps_facebook_app', 'app_id'), self::$app['colorscheme'], Config::get( 'social_plugins', 'language' ));
        }
        
    }
    /**
     * I like Facebook button, Iframe
     *
     * @param $link - link / url for the generated button
     * @param $width - the width of the container with a button on a
     * @param $layout - layout buttona(button_count, box_count, standard)
     * @param $send - button allows you to send a link to friends outpouring
     * @param $faces - determines whether or not the plug to display the profile photos
     * 
     * @return string
     * https://developers.facebook.com/docs/reference/plugins/like/
     */
    public static function likeIframe($link, $width, $layout = 'button_count', $send = 'false', $faces = 'false')
    {
        
        return sprintf('<iframe class="async_iframe" data-src="https://www.facebook.com/plugins/like.php?href=%s&amp;layout=%s&amp;show_faces=%s&amp;width=%s&amp;action=like&amp;font=%s&amp;colorscheme=%s&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:%spx; height:21px;" allowTransparency="true"></iframe>', $link, $layout, $faces, $width, self::$app['scheme_font'], self::$app['colorscheme'], $width);
        
    }
    
    /**
     * Share Facebook button
     *
     * @param $link - link / url for the generated button
     * @param $layout - layout buttona(button_count, box_count, standard)
     * 
     * @return string
     */
    public static function share( $link, $layout = 'button_count' )
    {
        return sprintf('<div class="tools"><fb:share-button href="%s" type="%s"></fb:share-button></div>', $link, $layout);
    }
    
    /**
     * Facebook button Udostępnij - wersja niewspierana
     * Facebook Share button - version is not supported
     *
     * @param $link - link / url for the generated button
     * @param $verb - word occurring as a link button
     * @param $layout - layout buttona(button_count, box_count, standard)
     * 
     * @return string
     */
    public static function shareOld( $link, $file_id = false, $verb = 'share_facebook_text', $layout = 'button_count' )
    {
        if ( self::$app['share_img'] )
		{
            return self::customButton( $link, '', 'facebook' );
        }
        
        return sprintf( '<div class="tools"><a data-name="fb_share" data-type="%s" data-url="%s" href="https://www.facebook.com/sharer.php">%s</a></div>', $layout, $link, __( $verb ) );
    }
    
    /**
     * Share Facebook button Fast mode
     *
     * @param $link - link / url for the generated button
     * @param $verb - word occurring as a link button
     * @param $layout - layout buttona(button_count, box_count, standard)
     * 
     * @return string
     */
    public static function shareForFastSlider( $link, $verb = 'Share', $layout = 'button_count' )
    {
        return sprintf('<div class="tools"><a class="ips-share-link" name="ips_share" type="%s" share_url="%s" href="https://www.facebook.com/sharer.php">%s</a></div>', $layout, $link, $verb );
    }
    
    /**
     * Facebook comments box
     *
     * @param $link - link / url for the generated button
     * @param $width - the width of the plug comments
     * @param $posty - the number of comments displayed at a time
     * 
     * @return string
     * https://developers.facebook.com/docs/reference/plugins/comments
     */
    public static function comments( $link, $width, $posty = '10' )
    {
        return sprintf('<div class="fb-comments" data-href="%s" data-num-posts="%d" data-width="%d" data-colorscheme="%s"></div>', $link, $posty, $width, self::$app['colorscheme_comments']);
        
    }
    /**
     * Facebook box Like
     * Wyświetla miniatury fanów Fanpage i liczbę polubień
     * Displays thumbnails and the number of fans like Fanpage
     *
     * @param $link - link / url to Fanpage
     * @param $width - the width of the plug
     * @param $height - the height of the plug
     * @param $faces - determines whether or not the plug to display the profile photos
     * @param $border - frame color in HEX format
     * @param $stream - stream of the latest posts from the array Fanpage
     * 
     * @return string
     * https://developers.facebook.com/docs/reference/plugins/like-box/
     */
    public static function likeBox( $href, $width, $height, $header = 'true', $color_sheme = false, $border = 'true', $border_color = '#000000', $faces = 'true', $stream = 'false' )
    {
        
        return sprintf('<div class="fb-like-box" data-href="%s" data-width="%d" data-height="%d" data-show-faces="%s" data-header="%s" data-border-color="%s" data-stream="%s" data-colorscheme="%s" data-font="%s" data-show-border="%s"></div>', $href, $width, $height, $faces, $header, $border_color, $stream, ($color_sheme ? $color_sheme : self::$app['colorscheme']), self::$app['scheme_font'], $border);
    }
    
    /**
     * Facebook box recommendations
     * Wyświetla treści z serwisu, które są im polecane
     * Displays the content of the site, which is the recommended
     *
     * @param $domena - domain, which is recommended
     * @param $width - the width of the plug
     * @param $height - the height of the plug
     * @param $border -  frame color in HEX format
     * @param $header - determines whether Facebook will display the header
     * @param $linktarget - how you want to open links
     * 
     * @return string
     * https://developers.facebook.com/docs/reference/plugins/recommendations
     */
    public static function recommendations($domena, $width, $height, $border = '#000000', $header = 'true', $linktarget = '_blank')
    {
        
        return sprintf('<div class="fb-recommendations" data-site="%s" data-width="%d" data-height="%d" data-header="%s" data-colorscheme="%s" data-linktarget="%s" data-border-color="%s" data-font="%s"></div>', $domena, $width, $height, $header, self::$app['colorscheme'], $linktarget, $border, self::$app['scheme_font']);
        
    }
    
    /**
     * Facebook box user activity
     * Wyświetla aktywności innych użytkowników na stronie
     * Displays the activity of other users on the site
     *
     * @param $domena - domena, do której odnoszą się aktywności   
     * @param $width - the width of the plug
     * @param $height - the height of the plug
     * @param $border - frame color in HEX format
     * @param $header - determines whether Facebook will display the header
     * @param $linktarget - how you want to open links
     * 
     * @return string
     * https://developers.facebook.com/docs/reference/plugins/send/
     */
    public static function activityFeed($domena, $width, $height, $border = '#000000', $header = 'true', $linktarget = '_blank')
    {
        
        return sprintf('<div class="fb-activity" data-site="%s" data-width="%d" data-height="%d" data-header="%s" data-colorscheme="%s" data-linktarget="%s" data-border-color="%s" data-font="%s"></div>', $domena, $width, $height, $header, self::$app['colorscheme'], $linktarget, $border, self::$app['scheme_font']);
        
    }
    /**
     * Facebook button Send
     * Allows you to send a link to another user Facebook
     *
     * @param $link - link / url for the generated button
     *
     * @return string
     * https://developers.facebook.com/docs/reference/plugins/send/
     */
    public static function sendButton($link)
    {
        
        return sprintf('<div class="fb-send" data-href="%s" data-font="%s" data-colorscheme="%s"></div>', $link, self::$app['scheme_font'], self::$app['colorscheme']);
        
    }
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public static function fanBox($id, $width, $height, $stream = 'false', $header = 'false')
    {
        
        
        //return sprintf('<div class="fb-like-box" data-href="%s" data-width="%s" data-height="%s" data-show-faces="true" data-stream="%s" data-header="%s"></div>', $id, $width, $height, $stream, $header );
        return sprintf('<fb:fan profile_id="%s" stream="0" connections="24" logobar="0" width="%d" height="%d" border="1"></fb:fan>', $id, $width, $height);
        
    }
    
    /*
     * Facebook login button
     * @param null
     * @return string
     */
    public static function getConnectButtonFB( $type = false )
    {
        
        if ( Config::get('apps_login_enabled', 'facebook') || $type == 'force' )
		{
           if ( $type == 'url' )
			{
                return '<a href="/connect/facebook/" class="login-link-facebook">' . self::getConnectPhrase('connect_facebook', 'Facebook') . '</a>';
            }
            
            return '<div class="fb-login-button" onlogin="facebookLogin();" scope="' . implode( ',', Config::getArray( 'apps_facebook_app', 'previliges' ) ) . '">' . self::getConnectPhrase('connect_facebook', 'Facebook') . '</div>';
        }
    }
    
    /*
     *  n-k.pl login button or log if you have authorized;
     * @param null
     * @return string
     */
    public static function getConnectButtonNK($type = false)
    {
        
        if (Config::get('apps_login_enabled', 'nk')) {
            if ($type == 'url') {
                return '<a href="/connect/nk/" class="login-link-nk">' . self::getConnectPhrase('connect_nk', 'NK') . '</a>';
            }
            
            /* All validation and generate in ajax_api_nk() */
            return '<div class="nk-login"></div>';
        }
    }
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public static function getConnectPhrase($phrase, $type)
    {
        return !USER_LOGGED ? __( $phrase ) : __s('user_connect_with', $type);
    }
   
	
    /**
     * Button NK Fajne
     *
     * @param $link - link do materiału
     * @param $title - tytuł materiału
     * @param $thumb_img - thumb_img materiału
     * 
     * @return string
     */
    public static function nkButton( $link, $title, $image = false, $type = false, $scheme = false )
    {
        
        if ( empty( $title ) )
		{
            $title = self::$_info['title'];
        }
        
        if ( self::$app['share_img'] )
		{
            return self::customButton( $link, $title, 'nk' );
        }
		
        self::$app['nk'] = true;
        
        return '<div class="tools"><span class="button_nk">' .
		'<div class="nk-fajne tools-nk" ' .
			 'data-nk-url="' . $link . '" ' .
			 'data-nk-type="' . ( empty( $type ) ? self::$app['nk_type'] : $type ) . '" ' .
			 'data-nk-color="' . ( empty( $scheme ) ? self::$app['nk_scheme'] : $scheme ) . '" ' .
			 'data-nk-title="' . $title . '" ' .
			 'data-nk-image="' . $image . '" ' .
			 'data-nk-description = "' . ( isset( self::$_info['description'] ) ? self::$_info['description'] : $title ) . '">' .
		'</div>' .
		'</span></div>';
        
    }
    
    /**
     * Button Google Plus One
     *
     * @param $link - a link to the material
     * 
     * @return 
     */
    public static function googleButton( $link, $size = false )
    {
        
		if ( self::$app['share_img'] )
		{
            return self::customButton($link, '', 'google');
        }
		
        if ( empty( $size ) )
		{
            $size = self::$app['size_google'];
        }
		
        self::$app['google'] = true;
        
        return '<div class="tools"><span class="button_google"><g:plusone size="' . $size . '" href="' . $link . '" callback="plusOneCount"></g:plusone></span></div>';
        
    }
    
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public static function twitterButton( $link, $title, $count = 'horizontal' )
    {
        if ( self::$app['share_img'] )
		{
            return self::customButton($link, $title, 'tweet');
        }
        
        self::$app['twitter'] = true;
        
        return '<div class="tools"><a href="https://twitter.com/share" class="twitter-share-button" data-count="' . $count . '" data-url="' . $link . '" data-text="' . $title . '" data-lang="' . self::$app['short_lang'] . '"></a></div>';
    }
    /**
     * Custom share buttons
     *
     * @param 
     * 
     * @return 
     */
    public static function customButton( $url, $title, $type )
    {
        global ${IPS_LNG};
        return '<div class="tools"><a class="ips_image_share ' . $type . '" data-title="' . $title . '" data-href="' . $url . '"><i></i><span>' . ${IPS_LNG}['share_' . $type . '_text'] . '</span></a></div>';
    }
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public static function twitterFollow($twitter_username)
    {
        self::$app['twitter'] = true;
        
        return '<a href="https://twitter.com/' . $twitter_username . '" class="twitter-follow-button" data-show-count="false" data-show-screen-name="false">Follow @' . $twitter_username . '</a>';
    }
    
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public static function twitterTimeline($twitter_username, $widget_id)
    {
        self::$app['twitter'] = true;
        return '<a class="twitter-timeline" data-dnt="true" href="https://twitter.com/' . $twitter_username . '"  data-widget-id="' . $widget_id . '">@' . $twitter_username . '</a>';
    }
    
	/**
     * JS code required for the proper operation of the widgets on Facebook
     *
     * @param null
     * 
     * @return string
     */
    public static function jsFacebook()
    {
        return '<div id="root"></div>
		<script type="text/javascript">
		/* All Facebook functions should be included in this function, or at least initiated from here */
		window.fbAsyncInit = function() {
			IpsApp._FB.init();
		};
		</script>
		<script id="facebook-jssdk" async="async" defer="defer" src="//connect.facebook.net/' . self::$app['conf_language'] . '/' . ( IPS_DEBUG ? 'sdk/debug.js' : 'sdk.js' ) . '"></script>';
    }
    
    /**
     * Widgets with NK.PL
     * Kod JS wymagany do poprawnego działania widgetów NK
     * JS code required for the proper operation of NK widgets
     *
     * @param null
     * 
     * @return string
     */
    public static function jsNK()
    {
        if (self::$app['nk'] && ( Config::get( 'social_plugins', 'nk' ) == '1' || IPS_ACTION_GET_ID ) )
		{
            return 'IpsApp._asyncScript( \'http://0.s-nk.pl/script/packs/nk_widgets_all.js\', null, \'nk-widget-sdk\' );';
        }
    }
     /**
	 * TO DO COMMENT
	 *
	 * @param null
	 * 
	 * @return 
	 */
    public static function jsTwitter()
    {
        if( self::$app['twitter'] )
		{
			return 'IpsApp._asyncScript( \'http://platform.twitter.com/widgets.js\', null, \'twitter-widget-sdk\' );';
        }
    }
    /**
     * Kod JS wymagany do poprawnego działania buttonów Google
     * JS code required for the proper functioning buttonów Google
     *
     * @param null
     * 
     * @return string
     */
    public static function jsGoogle()
    {
        if ( self::$app['google'] )
		{
            return 'IpsApp._asyncScript( \'https://apis.google.com/js/plusone.js\', null, \'nk-widget-sdk\' );';
        }
    }
    /**
     * Generowanie całego kod buttonów społecznościowych
     * wymaganego do działani buttonów i widgetów.
     * Generating code buttonów whole community to action required to buttonów and widgets.
     *
     * @param null
     * 
     * @return string
     */
    public static function socialScript()
    {
        
        $js = $old = '';
        
        $js .= self::jsGoogle();
        
        $js .= self::jsTwitter();
        
        $js .= self::jsNK();
        
        return self::jsFacebook() . '
		<script type="text/javascript">
		' . $js . '
		</script>
		';
        
    }
}
?>