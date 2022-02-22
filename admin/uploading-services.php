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
	
	echo admin_caption( 'uploading_select_typ' );
	

	echo '
	<a href="' . admin_url( 'import-services' ) . '" class="button" role="button">' . __( 'uploading_imports_from_other_sites') . '</a>
	<div class="div-info-message with-margin">
		' . __( 'uploading_info_1' ) . '
	</div>
	<a href="' . admin_url( 'import-youtube-playlist' ) . '" class="button" role="button">' . __( 'uploading_import_from_youtube') . '</a>
	<div class="div-info-message with-margin">
		' . __( 'uploading_info_2' ) . '
	</div>
	<a href="' . admin_url( 'import-folder' ) . '" class="button" role="button">' . __( 'uploading_upload_folder' ) . '</a>
	<div class="div-info-message with-margin">
		' . __( 'uploading_info_3' ) . '
	</div>

	<a href="' . admin_url( 'import-youtube-links' ) . '" class="button" role="button">' . __( 'uploading_links_youtube' ) . '</a><br />
	<div class="div-info-message with-margin">
		' . __( 'uploading_info_4' ) . '
	</div>
		';

		
		if( defined('IPS_SELF') )
		{
			echo base64_decode('PGJyIC8+PGEgaHJlZj0iYWRtaW4ucGhwP3JvdXRlPWltcG9ydC1tdWx0aS1nYWxlcmlhIiBjbGFz
			cz0iYnV0dG9uIiByb2xlPSJidXR0b24iPk11bHRpR2FsZXJpYTwvYT48YnIgLz48YnIgLz48YSBo
			cmVmPSJhZG1pbi5waHA/cm91dGU9aW1wb3J0LWZ1bmRpciIgY2xhc3M9ImJ1dHRvbiIgcm9sZT0i
			YnV0dG9uIj5GdW5kaXI8L2E+PGJyIC8+PGJyIC8+PGEgaHJlZj0iYWRtaW4ucGhwP3JvdXRlPWlt
			cG9ydC1qZWphIiBjbGFzcz0iYnV0dG9uIiByb2xlPSJidXR0b24iPkpFSkE8L2E+PGJyIC8+PGJy
			IC8+PGEgaHJlZj0iYWRtaW4ucGhwP3JvdXRlPWltcG9ydC1taWxhbm9zLXBsIiBjbGFzcz0iYnV0
			dG9uIiByb2xlPSJidXR0b24iPk1JTEFOT1M8L2E+PGJyIC8+PGJyIC8+PGEgaHJlZj0iYWRtaW4u
			cGhwP3JvdXRlPWltcG9ydC1mdW5ueWp1bmsiIGNsYXNzPSJidXR0b24iIHJvbGU9ImJ1dHRvbiI+
			RnVubnlqdW5rPC9hPjxiciAvPjxiciAvPjxhIGhyZWY9ImFkbWluLnBocD9yb3V0ZT1pbXBvcnQt
			YWxsIiBjbGFzcz0iYnV0dG9uIiByb2xlPSJidXR0b24iPldzenlzdGtpZTwvYT4=');
		}
	

?>