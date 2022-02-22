<?php
function get_tz_options($selectedzone) {
	$all = timezone_identifiers_list();

	$i = 0;
	foreach($all AS $zone) {
		$zone = explode('/',$zone);
		$zonen[$i]['continent'] = isset($zone[0]) ? $zone[0] : '';
		$zonen[$i]['city'] = isset($zone[1]) ? $zone[1] : '';
		$zonen[$i]['subcity'] = isset($zone[2]) ? $zone[2] : '';
		$i++;
	}

	asort($zonen);
	$structure = '';
	foreach($zonen AS $zone) {
		extract($zone);
		if($continent == 'Africa' || $continent == 'America' || $continent == 'Antarctica' || $continent == 'Arctic' || $continent == 'Asia' || $continent == 'Atlantic' || $continent == 'Australia' || $continent == 'Europe' || $continent == 'Indian' || $continent == 'Pacific') {
		if(!isset($selectcontinent)) {
		  $structure .= '<optgroup label="'.$continent.'">'; // continent
		} elseif($selectcontinent != $continent) {
		  $structure .= '</optgroup><optgroup label="'.$continent.'">'; // continent
		}

		if(isset($city) != ''){
		  if (!empty($subcity) != ''){
			$city = $city . '/'. $subcity;
		  }
		  $structure .= "<option ".((($continent.'/'.$city)==$selectedzone)?'selected="selected "':'')." value=\"".($continent.'/'.$city)."\">".str_replace('_',' ',$city)."</option>"; //Timezone
		} else {
		  if (!empty($subcity) != ''){
			$city = $city . '/'. $subcity;
		  }
		  $structure .= "<option ".(($continent==$selectedzone)?'selected="selected "':'')." value=\"".$continent."\">".$continent."</option>"; //Timezone
		}

			$selectcontinent = $continent;
		}
	}
	$structure .= '</optgroup>';

	return $structure;
}

function checkDir( $dir )
{
	if( !file_exists( $dir ) )
	{
		if ( !mkdir( $dir )) 
		{
			return false;
		}
		return true;
	}
	return @chmod( $dir, 0775 );
}

function renameLogo()
{
	if( file_exists( ABS_PATH . '/install/static/upload/logo.png' ) )
	{
		@rename( ABS_PATH . '/install/static/upload/logo.png', ABS_PATH . '/images/logo-' . IPS_VERSION . '.png');
	}
}
/**
* Dodawanie listy tłumaczeń 
*/
function installTranslations( $PD )
{
	
	$options = include( dirname(__FILE__) . '/import-language.php' );
	global $install_language;
	$sql = '';
	$i = 0;
	
	$sql .= 'INSERT INTO `' . db_prefix( 'translations' ) . '` (`translation_name`, `translation_value`, `orginal`, `language`) VALUES ' . "\n";
	foreach ( $install_language as $name => $translation )
	{
		if( is_array( $translation ) )
		{
			$translation = serialize( $translation );
		}
		
		$i++;
		$sql .= "('" . $name . "', '" . $translation . "', '" . $translation . "', 'PL'),";
	}
	$sql = substr( $sql, 0, -1) . ';';
	
	if( $PD->query( $sql ) === false )
	{
		ips_log( $PD->debug() );
		return false ;
	}
	return $i;
}

/**
* Dodawanie ustawień do bazy 
*/
function installSettings( $PD )
{
	$options = include( dirname(__FILE__) . '/import-options.php' );
	$sql = '';
	$i = 0;
	
	foreach( $options as $table_name => $settings )
	{
		
		$sql .= 'INSERT INTO `' . db_prefix( $table_name ) . '` (`settings_id`, `settings_name`, `settings_value`, `autoload`) VALUES' . "\n";
		foreach( $settings as $key => $set )
		{
			$i++;
			$sql .= "(NULL, '".$set['settings_name']."', '".( is_array( $set['settings_value'] ) ? serialize( $set['settings_value'] ) : $set['settings_value'] )."', '".$set['autoload']."'), \n";
		}
		$sql = substr( $sql, 0, -3) . ';';
	}
	
	if( $PD->query( $sql ) === false )
	{
		ips_log( $PD->debug() );
		return false ;
	}
	return $i;
}

/**
* Dodawanie tabel do bazy
*/
function installTables( $PD, $tables )
{
	foreach( $tables as $table_name => $sql )
	{
		if( $PD->query( $sql ) === false )
		{
			return array( $table_name ) ;
		}
	}
	return true;
}

function clearDatabase( $PD, $tables, $db_name )
{
	$table_names = array_keys( $tables );
	foreach( $table_names as $table )
	{
		$result = $PD->query( "SELECT COUNT(*) as tabele_exists FROM information_schema.tables  WHERE table_schema = '" . $db_name . "'  AND table_name = '" . db_prefix( $table ) . "';");
		
		if( $result[0]['tabele_exists'] > 0 )
		{
			$PD->query('DROP TABLE IF EXISTS ' . db_prefix( $table ) );
		}
	}
	return true;
}

/**
* Cofanie instalacji.
*/
function reverseInstall(  $PD, $tables, $to_table )
{
	$table_names = array_keys( $tables );
	foreach( $table_names as $table_name )
	{
		if( $table_name == $to_table )
		{
			return false;
		}
		$PD->query('DROP TABLE IF EXISTS ' . $table_name );
	}
	return true;
}
function isInstalled($loaded_extensions, $function)
{
	if  (in_array  ($function, $loaded_extensions))
	{
		return true;
	}
	else{
		return false;
	}
}
function getUrl()
{
	if(!empty($_SERVER['SERVER_NAME']))
		$host = $_SERVER['SERVER_NAME'];
	else
		$host = $_SERVER['HTTP_HOST'];
	
	if(substr($host, 0, 4) == "www.")
	{
		return 'http://' . $host . '/';	
	}
	else
	{
		return 'http://www.' . $host . '/';
	}
}
function checkPHP( $f )
{
	
	$array = get_loaded_extensions();
	
	switch( $f )
	{
		case 'mcrypt':
		case 'exif':
		case 'gd':
		case 'curl':
			if ( isInstalled( $array, $f ) )
			{
				return 'true';
			}
		break;
		case 'pdo':
			if ( isInstalled( $array, 'PDO' ) )
			{
				return 'true';
			}
		break;
		case 'mbstring':
			if ( function_exists('mb_internal_encoding') )
			{
				return 'true';
			}
		break;
		case 'php':
			if ( version_compare( PHP_VERSION, '5.4.0', '<' ) )
			{
				return 'Skrypt wymaga wersji PHP co najmniej 5.4.0, serwer używa wersji: ' . PHP_VERSION . '';
			}
			return 'true';
		break;
	}
	
	return 'Brak biblioteki <b>'.ucfirst( $f ).'</b';
}

function installAds( $PD )
{
	
	$ads = array(
		'under_menu' => '<img src=\"http://placehold.it/750x100\">',
		'between_files' => '<img src=\"http://placehold.it/728x90\">',
		'under_file' => '<img src=\"http://placehold.it/728x90\">',
		'between_comments' => '<img src=\"http://placehold.it/728x90\">',
		'left_side_list' => '<img src=\"http://placehold.it/160x600\">',
		'right_side_list' => '<img src=\"http://placehold.it/160x600\">',
		'top_of_list' => '<img src=\"http://placehold.it/740x100\">',
		'bottom_of_list' => '<img src=\"http://placehold.it/740x100\">',
		'side_block_top' => '<img src=\"http://placehold.it/135x200\">',
		'side_block_bottom' => '<img src=\"http://placehold.it/300x200\">',
		'bottom_slide_ad' => '<img src=\"http://placehold.it/780x200\">',
		'above_file' => '<img src=\"http://placehold.it/740x100\">',
		'gallery_ad' => '<img src=\"http://placehold.it/300x200\">',
		'under_comments' => '<img src=\"http://placehold.it/740x100\">',
		'pin_side_block_top' => '<img src=\"http://placehold.it/236x200\">',
		'pin_side_block_bottom' => '<img src=\"http://placehold.it/236x300\">',
		'pin_side_block_middle' => '<img src=\"http://placehold.it/236x300\">',
		'under_image_file' => '<img src=\"http://placehold.it/728x90\">',
		'between_popular_posts' => '<img src=\"http://placehold.it/300x236\">'
	);
	
	foreach( $ads as $ad_id => $ad_content )
	{
		if( !$PD->insert( 'ads', array( 'unique_name' => $ad_id, 'ad_content' => $ad_content ) ) )
		{
			return false;
		}
	}	
	
	return true;	
}
function setAnswer( $msg, $type = 'error'  )
{
	die(json_encode(array(
		$type => $msg
	)));
}
global $language_phrases;
$language_phrases = array(
	'finish' => array(
		'pl_PL' => 'Zakończ',
		'en_US' => 'Finish'
	),
	'fill_field' => array(
		'pl_PL' => 'Wypełnij to pole',
		'en_US' => 'Fill this field'
	),
	'long_loading_refresh' => array(
		'pl_PL' => 'Ładowanie trwa zbyt długo, ponów instalację odświeżając stronę...',
		'en_US' => 'Script install takes too long, try again after page refresh...'
	),
	'language_version' => array(
		'pl_PL' => 'Wersja językowa',
		'en_US' => 'Language version'
	),
	'server_req_error' => array(
		'pl_PL' => '<strong>Uwaga!</strong> Serwer nie spełnia wszystkich wymagań i instalacja nie może być kontynuowana.',
		'en_US' => '<strong>Warning!</strong> The server does not meet all the requirements and installation can not continue.'
	),
	'server_wait_checking' => array(
		'pl_PL' => '<strong>Uwaga!</strong> Zaczekaj na sprawdzenie serwera.',
		'en_US' => '<strong>Warning!</strong> Wait for the server to verify.'
	),
	'server_req' => array(
		'pl_PL' => 'Wymagania serwera',
		'en_US' => 'Server requirements'
	),
	'php_version' => array(
		'pl_PL' => 'Wersja PHP',
		'en_US' => 'PHP Version'
	),
	'mysql_db' => array(
		'pl_PL' => 'Baza danych',
		'en_US' => 'Database'
	),
	'enter_mysql_db' => array(
		'pl_PL' => 'Wpisz dane bazy MySQL',
		'en_US' => 'Enter the MySQL informations'
	),
	'mysql_db_error' => array(
		'pl_PL' => '<strong>Uwaga!</strong> Dane do bazy MySQL nie są poprawne i instalacja nie może być kontynuowana.',
		'en_US' => '<strong>Warning!</strong> MySQL informations are not correct and the installation can not continue.'
	),
	'mysql_db_host' => array(
		'pl_PL' => 'Host, zazwyczaj localhost',
		'en_US' => 'Host, usually localhost'
	),
	'mysql_db_port' => array(
		'pl_PL' => 'Port, zazwyczaj 3306',
		'en_US' => 'Port, usually 3306'
	),
	'mysql_db_user' => array(
		'pl_PL' => 'Nazwa użytkownika',
		'en_US' => 'MySQL username'
	),
	'mysql_db_pass' => array(
		'pl_PL' => 'Hasło do bazy',
		'en_US' => 'MySQL password'
	),
	'mysql_db_name' => array(
		'pl_PL' => 'Nazwa bazy',
		'en_US' => 'MySQL database name'
	),
	'mysql_db_prefix' => array(
		'pl_PL' => 'Prefix tabeli',
		'en_US' => 'Table prefix'
	),
	'php_timezone' => array(
		'pl_PL' => 'Strefa Czasowa',
		'en_US' => 'Time Zone'
	),
	'php_timezone_info' => array(
		'pl_PL' => 'Wprowadź nazwę strefy czasowej Twojego serwera, instalator wybrał już wersję domyslną i prawdopodobnie nie musisz tego zmieniać.',
		'en_US' => "'Enter the name of your server's time zone, the installer has chosen the default version."
	),
	'admin_data' => array(
		'pl_PL' => 'Dane administratora',
		'en_US' => 'Admin informations'
	),
	'admin_data_enter' => array(
		'pl_PL' => 'Wpisz dane administratora witryny',
		'en_US' => 'Enter the site administrator informations'
	),
	'email_admin_user' => array(
		'pl_PL' => 'Adres e-mail',
		'en_US' => 'E-mail address'
	),
	'email_admin_user_error' => array(
		'pl_PL' => 'Wpisz poprawny adres email',
		'en_US' => 'Please enter a valid email address'
	),
	'admin_username' => array(
		'pl_PL' => 'Nazwa użytkownika',
		'en_US' => 'Username'
	),
	'admin_username_error' => array(
		'pl_PL' => 'Wpisz poprawną nazwę użytkownika (a-zA-Z)(2-20)',
		'en_US' => 'Please enter a valid username (a-zA-Z)(2-20)'
	),
	'admin_password' => array(
		'pl_PL' => 'Hasło',
		'en_US' => 'Password'
	),
	'admin_password_error' => array(
		'pl_PL' => 'Wpisz poprawne hasło (a-zA-Z)(2-20)',
		'en_US' => 'Enter valid password (a-zA-Z)(2-20)'
	),
	'admin_password_random' => array(
		'pl_PL' => 'Losowe',
		'en_US' => 'Random'
	),
	'admin_password_repeat' => array(
		'pl_PL' => 'Powtórz hasło',
		'en_US' => 'Repeat password'
	),
	'admin_password_repeat_error' => array(
		'pl_PL' => 'Hasła różnią się',
		'en_US' => 'Passwords are different'
	),
	'set_template' => array(
		'pl_PL' => 'Wybierz szablon witryny',
		'en_US' => 'Choose a site template'
	),
	'template' => array(
		'pl_PL' => 'Szablon',
		'en_US' => 'Template'
	),
	'get_template' => array(
		'pl_PL' => 'Wybierz szablon',
		'en_US' => 'Choose a template'
	),
	'site_data' => array(
		'pl_PL' => 'Dane witryny',
		'en_US' => 'Site informations'
	),
	'site_data_logo' => array(
		'pl_PL' => 'Dodaj logo witryny',
		'en_US' => 'Add your site logo'
	),
	'set_meta' => array(
		'pl_PL' => 'Uzupełnij meta',
		'en_US' => 'Complete meta'
	),
	'site_title' => array(
		'pl_PL' => 'Tytuł strony',
		'en_US' => 'Site title'
	),
	'site_title_example' => array(
		'pl_PL' => 'Moja witryna',
		'en_US' => 'My Site'
	),
	'site_description' => array(
		'pl_PL' => 'Opis strony',
		'en_US' => 'Site description'
	),
	'site_description_example' => array(
		'pl_PL' => 'Najlepsza treść',
		'en_US' => 'Top Content'
	),
	'site_keywords' => array(
		'pl_PL' => 'Słowa kluczowe',
		'en_US' => 'Keywords'
	),
	'site_keywords_example' => array(
		'pl_PL' => 'funny, śmieszne, memy',
		'en_US' => 'funny, images, memes'
	),
	'all_fields_valid' => array(
		'pl_PL' => 'Wszystkie dane zostały wypełnione poprawnie',
		'en_US' => 'All data filled correctly'
	),
	'script_ready_install' => array(
		'pl_PL' => 'IPS-CMS może zostać bezpiecznie zainstalowany na serwerze.',
		'en_US' => 'IPS-CMS may be safely installed on the server.'
	),
	'installing_tables' => array(
		'pl_PL' => 'Instalacja tabeli',
		'en_US' => 'Installing tables'
	),
	'installing_settings' => array(
		'pl_PL' => 'Instalacja ustawień',
		'en_US' => 'Installing settings'
	),
	'installing_translations' => array(
		'pl_PL' => 'Instalacja tłumaczeń',
		'en_US' => 'Installation translations'
	),
	'installing_error' => array(
		'pl_PL' => '<strong>Wystąpił problem</strong> podczas instalacji systemu.',
		'en_US' => '<strong>Error occurred</strong> while system install.'
	),
	'server_response' => array(
		'pl_PL' => 'Odpowiedź serwera:',
		'en_US' => 'Server response:'
	),
	'installing_critical_error' => array(
		'pl_PL' => '<strong>Wystąpił krytyczny błąd</strong> podczas instalacji systemu.',
		'en_US' => '<strong>Critical error occurred</strong> while system install'
	),
	'installing_try_again' => array(
		'pl_PL' => 'Spróbuj ponownie',
		'en_US' => 'Try again'
	),
	'installing_valid' => array(
		'pl_PL' => 'IPS-CMS zainstalowany pomyślnie.',
		'en_US' => 'IPS-CMS installed successfully.',
	),
	'admin_before_site' => array(
		'pl_PL' => 'Przed przejściem do witryny wykonaj następujące czynności:',
		'en_US' => 'Before moving on to the site, do the following:'
	),
	'admin_go_site' => array(
		'pl_PL' => 'Przejdź do witryny',
		'en_US' => 'Go to Website'
	),
	'wizard_cancel' => array(
		'pl_PL' => 'Anuluj',
		'en_US' => 'Cancel'
	),
	'wizard_next' => array(
		'pl_PL' => 'Kolejny etap',
		'en_US' => 'Next step'
	),
	'wizard_back' => array(
		'pl_PL' => 'Wstecz',
		'en_US' => 'Back'
	),
	'wizard_install' => array(
		'pl_PL' => 'Instaluj',
		'en_US' => 'Install'
	),
	'wizard_install_progress' => array(
		'pl_PL' => 'Instalacja w trakcie...',
		'en_US' => 'Install in progress...'
	),
	
	
	
	'error_config_lost' => array(
		'pl_PL' => 'Brak pliku config-sample.php na serwerze!',
		'en_US' => 'File config-sample.php does not exists'
	),
	'error_config_create' => array(
		'pl_PL' => 'Nie mogę utworzyć pliku!. Utwórz pusty plik config.php w folderze głównym i nadaj mu uprawnienia 777',
		'en_US' => 'Error creating file! Create empty file called config.php in main folder with chmod 777'
	),
	'error_pdo' => array(
		'pl_PL' => 'Sprawdź czy biblioteka PDO została poprawnie zainstalowana na serwerze.',
		'en_US' => 'Check if PDO extension was installed.'
	),
	'error_tables_create' => array(
		'pl_PL' => 'Błąd podczas tworzenia tabeli SQL',
		'en_US' => 'Error while MySQL tables install'
	),
	'error_settings_create' => array(
		'pl_PL' => 'Błąd podczas dodawania ustawień do bazy MySQL',
		'en_US' => 'Error while MySQL settings install'
	),
	'error_translations_create' => array(
		'pl_PL' => 'Błąd podczas dodawania listy tłumaczeń',
		'en_US' => 'Error while MySQL translations install'
	),
	'error_config_read_file' => array(
		'pl_PL' => 'Nie mogę otworzyć pliku!. Nadaj uprawnienia 777 dla pliku config-sample.php',
		'en_US' => 'Error opening file! Chmod file /install/config-sample.php with 777'
	),
	'error_user_create' => array(
		'pl_PL' => 'Błąd podczas tworzenia użytkownika.',
		'en_US' => 'Error while creating user account'
	),
	'error_category_create' => array(
		'pl_PL' => 'Błąd podczas tworzenia domyślnej kategorii.',
		'en_US' => 'Error while creating categories'
	),
	'error_ads_create' => array(
		'pl_PL' => 'Błąd podczas tworzenia pól reklamowych.',
		'en_US' => 'Error while creating ads fields'
	),
	'error_pages_create' => array(
		'pl_PL' => 'Błąd podczas tworzenia domyślnych podstron.',
		'en_US' => 'Error while creating custom pages'
	),
	'error_lock' => array(
		'pl_PL' => 'Utworz plik installed.lock w folderze install/',
		'en_US' => 'Create file installed.lock in install/ folder'
	),
	'error_undefined' => array(
		'pl_PL' => 'Nieokreślony błąd',
		'en_US' => 'Undefined error'
	),
	
	'pages_rules_post_content' => array(
		'pl_PL' => 'Treść regulaminu',
		'en_US' => 'Rules page content'
	),
	'pages_rules_post_title' => array(
		'pl_PL' => 'Regulamin',
		'en_US' => 'Rules'
	),
	'pages_rules_post_permalink' => array(
		'pl_PL' => 'regulamin.html',
		'en_US' => 'rules.html'
	),
	
	'pages_ads_post_content' => array(
		'pl_PL' => 'Treść podstrony Reklama',
		'en_US' => 'Ads page content'
	),
	'pages_ads_post_title' => array(
		'pl_PL' => 'Reklama',
		'en_US' => 'Ads'
	),
	'pages_ads_post_permalink' => array(
		'pl_PL' => 'reklama.html',
		'en_US' => 'ads.html'
	),
);
function translate( $phrase )
{
	global $language_phrases;
	return isset( $language_phrases[$phrase][INSTALL_LANG] ) ? $language_phrases[$phrase][INSTALL_LANG] : $phrase;
}
?>