<?php
session_start();
include( dirname(__FILE__) . '/install-functions.php' ); 
if( isset( $_GET['lang'] ) && in_array( $_GET['lang'], array( 'pl_PL', 'en_US' ) ) )
{
	$_SESSION['install-lang'] = $_GET['lang'];
}
if( !isset( $_SESSION['install-lang'] ) )
{
	if ( isset( $_SERVER["HTTP_ACCEPT_LANGUAGE"] ) )
	{
		$languages = strtolower( $_SERVER["HTTP_ACCEPT_LANGUAGE"] );
		$_SESSION['install-lang'] = substr( $languages,0,2) == 'pl' ? 'pl_PL' : 'en_US';
	}
	else
	{
		$_SESSION['install-lang'] = 'en_US';
	}
}

define( 'INSTALL_LANG', $_SESSION['install-lang'] );
?>
<!DOCTYPE html>
<html>
	<head>
		<title>IPS-CMS</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,300,400,600,700" rel="stylesheet" type="text/css" />
		<link href="static/bootstrap/bootstrap.min.css" rel="stylesheet" />
		<link href="static/src/bootstrap-wizard.css" rel="stylesheet" />
		<link href="static/chosen/chosen.css" rel="stylesheet" />
		<link href="static/install.css" rel="stylesheet" />
		
		<link href="static/css/fileinput.min.css" media="all" rel="stylesheet" type="text/css" />

	
		<script type="text/javascript">
		var lang_buttons = {
			cancelText: "<?php echo translate( 'wizard_cancel' ) ?>",
			nextText: "<?php echo translate( 'wizard_next' ) ?>",
			backText: "<?php echo translate( 'wizard_back' ) ?>",
			submitText: "<?php echo translate( 'wizard_install' ) ?>",
			submittingText: "<?php echo translate( 'wizard_install_progress' ) ?>"
		}
		</script>
		<style type="text/css">
			.wizard-modal p {
				margin: 0 0 10px;
				padding: 0;
			}

			#wizard-ns-detail-servers, .wizard-additional-servers {
				font-size: 12px;
				margin-top: 10px;
				margin-left: 15px;
			}
			#wizard-ns-detail-servers > li, .wizard-additional-servers li {
				line-height: 20px;
				list-style-type: none;
			}
			#wizard-ns-detail-servers > li > img {
				padding-right: 5px;
			}

			.wizard-modal .chzn-container .chzn-results {
				max-height: 150px;
			}
			.wizard-addl-subsection {
				margin-bottom: 40px;
			}
			.create-server-agent-key {
				margin-left: 15px; 
				width: 90%;
			}
		</style>
		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		<script src="static/js/html5shiv-3.7.0.js"></script>
		<script src="static/js/respond-1.3.0.min.js"></script>
		<![endif]-->
	</head>
	<body style="padding:30px;">
		<img class="img-responsive" src="http://cdn.iprosoft.pro/codecanyon_profile_only_text_590x242.png" alt="" style="margin: 0px auto; height: auto; max-width: 400px;">
		<div class="hide input-error-text lng-fill_field"><?php echo translate( 'fill_field' ) ?></div>
		<div class="hide input-loading-text">long_loading_refresh</div>
		
		<div class="wizard" id="install-wizard" data-title="IPS-CMS">
			<div class="wizard-card" data-cardname="name" data-validate="validateLanguage">
				<h3 class="lng-language_version"><?php echo translate( 'language_version' ) ?></h3>

				
				
				<div id="language" class="fancy-groups wizard-input-section">
					<div class="form-group-lng">
						<a class="btn btn-primary btn-lg <?php echo INSTALL_LANG != 'pl_PL' ? 'notActive' : ''  ?>" href="/install/?lang=pl_PL">Polski</a>
						<a class="btn btn-primary btn-lg <?php echo INSTALL_LANG != 'en_US' ? 'notActive' : ''  ?>" href="/install/?lang=en_US">English</a>
					</div>
				</div>

				
			</div>
			<div class="wizard-card" data-cardname="name" data-validate="validateServer">
				<h3><?php echo translate( 'server_req' ) ?></h3>

				<div class="alert alert-danger alert-dismissible hide server-error" role="alert">
					<button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					<?php echo translate( 'server_req_error' ) ?>
				</div>
				
				<div class="alert alert-warning alert-dismissible hide server-check" role="alert">
					<button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					<?php echo translate( 'server_wait_checking' ) ?>
				</div>
	
				<div id="server-stats" class="fancy-groups wizard-input-section">

					<div class="btn-group" role="group" aria-label="..." data-check="php">
						<button type="button" class="btn btn-default"><?php echo translate( 'php_version' ) ?></button>
						<button type="button" class="btn btn-default info">&nbsp;</button>
						<button type="button" class="btn btn-default"><img src="static/images/spinner-pinit.svg"></button>
					</div>
					
					<div class="btn-group" role="group" aria-label="..." data-check="pdo">
						<button type="button" class="btn btn-default">PDO</button>
						<button type="button" class="btn btn-default info">&nbsp;</button>
						<button type="button" class="btn btn-default"><img src="static/images/spinner-pinit.svg"></button>
					</div>
					
					<div class="btn-group" role="group" aria-label="..." data-check="curl">
						<button type="button" class="btn btn-default">CURL</button>
						<button type="button" class="btn btn-default info">&nbsp;</button>
						<button type="button" class="btn btn-default"><img src="static/images/spinner-pinit.svg"></button>
					</div>
					
					<div class="btn-group" role="group" aria-label="..." data-check="gd">
						<button type="button" class="btn btn-default">GD</button>
						<button type="button" class="btn btn-default info">&nbsp;</button>
						<button type="button" class="btn btn-default"><img src="static/images/spinner-pinit.svg"></button>
					</div>
					
					<div class="btn-group" role="group" aria-label="..." data-check="mbstring">
						<button type="button" class="btn btn-default">Multibyte String</button>
						<button type="button" class="btn btn-default info">&nbsp;</button>
						<button type="button" class="btn btn-default"><img src="static/images/spinner-pinit.svg"></button>
					</div>
					
					<div class="btn-group" role="group" aria-label="..." data-check="exif">
						<button type="button" class="btn btn-default">Exif</button>
						<button type="button" class="btn btn-default info">&nbsp;</button>
						<button type="button" class="btn btn-default"><img src="static/images/spinner-pinit.svg"></button>
					</div>
					
					<div class="btn-group" role="group" aria-label="..." data-check="mcrypt">
						<button type="button" class="btn btn-default">Mcrypt</button>
						<button type="button" class="btn btn-default info">&nbsp;</button>
						<button type="button" class="btn btn-default"><img src="static/images/spinner-pinit.svg"></button>
					</div>
					
				</div>

				
			</div>
	
			<div class="wizard-card" data-cardname="mysql">
				<h3><?php echo translate( 'mysql_db' ) ?></h3>

				<div class="wizard-input-section server-mysql">
					<p>
						<?php echo translate( 'enter_mysql_db' ) ?>
					</p>
					
					<div class="alert alert-info alert-dismissible hide mysql-loader" role="alert">
						<img src="static/images/spinner-blue.svg">
					</div>
					
					<div class="alert alert-danger alert-dismissible hide mysql-error" role="alert">
						<button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						<?php echo translate( 'mysql_db_error' ) ?>
					</div>
					
					<div class="input-group form-group">
						<span class="input-group-addon"><?php echo translate( 'mysql_db_host' ) ?></span>
						<input name="database[host]" type="text" class="form-control" value="localhost">
					</div>
					
					<div class="input-group form-group">
						<span class="input-group-addon"><?php echo translate( 'mysql_db_port' ) ?></span>
						<input name="database[port]" type="text" value="3306" class="form-control">
					</div>
					
					<div class="input-group form-group">
						<span class="input-group-addon"><?php echo translate( 'mysql_db_user' ) ?></span>
						<input name="database[username]" type="text" class="form-control">
					</div>
					
					<div class="input-group form-group">
						<span class="input-group-addon"><?php echo translate( 'mysql_db_pass' ) ?></span>
						<input name="database[password]" type="password" class="form-control">
					</div>
					
					<div class="input-group form-group">
						<span class="input-group-addon"><?php echo translate( 'mysql_db_name' ) ?></span>
						<input name="database[name]" type="text" class="form-control">
					</div>
					
					<div class="input-group form-group">
						<span class="input-group-addon"><?php echo translate( 'mysql_db_prefix' ) ?></span>
						<input name="database[prefix]" type="text" class="form-control no-validate" value="ips_">
					</div>
					
				</div>
			</div>
	
			<div class="wizard-card wizard-card-overlay" data-cardname="timezone">
				<h3><?php echo translate( 'php_timezone' ) ?></h3>

				<div class="wizard-input-section">
					<p>
						<?php echo translate( 'php_timezone_info' ) ?>
					</p>

					<select id="timezone" name="timezone" data-placeholder="Monitor nodes" style="width:350px;" class="chzn-select form-control">
						<option value=""></option>
						<?php echo get_tz_options( date_default_timezone_get() ); ?>
					</select>

				</div>
			</div>
	

			<div class="wizard-card wizard-card-overlay" data-cardname="admin">
				<h3><?php echo translate( 'admin_data' ) ?></h3>

				<div class="wizard-input-section server-admin">
					<p>
						<?php echo translate( 'admin_data_enter' ) ?>
					</p>
					
				
					
					<div class="input-group form-group">
						<span class="input-group-addon"><?php echo translate( 'email_admin_user' ) ?></span>
						<input name="admin[email]" type="text" class="form-control" data-validate="validateEmail">
						<span class="hide input-validation"><?php echo translate( 'email_admin_user_error' ) ?></span>
					</div>

					<div class="input-group form-group">
						<span class="input-group-addon"><?php echo translate( 'admin_username' ) ?></span>
						<input name="admin[username]" type="text" class="form-control" data-validate="validateUsername">
						<span class="hide input-validation"><?php echo translate( 'admin_username_error' ) ?></span>
					</div>
					
					<div class="input-group form-group">
						<span class="input-group-addon"><?php echo translate( 'admin_password' ) ?></span>
						<span class="hide input-validation"><?php echo translate( 'admin_password_error' ) ?></span>
						<input name="admin[password]" type="password" class="password-input rand-password-input form-control" data-validate="validatePassword">
						<span class="input-group-btn">
							<button class="btn btn-default rand-password" type="button"><?php echo translate( 'admin_password_random' ) ?></button>
						</span>
						
					</div>
					
					<div class="input-group form-group">
						<span class="input-group-addon"><?php echo translate( 'admin_password_repeat' ) ?></span>
						<input name="admin[password-rep]" type="password" class="rand-password-input form-control" data-validate="validatePasswordRepeat">
						<span class="hide input-validation"><?php echo translate( 'admin_password_repeat_error' ) ?></span>
					</div>
					
					<p>
						<?php echo translate( 'set_template' ) ?>
					</p>
		
					<div class="input-group form-group">
						<span class="input-group-addon"><?php echo translate( 'template' ) ?></span>
						<select name="admin[template]"  class="form-control" data-validate="validateTemplate">
							<option value="gag">Gag</option>
							<option value="kwejk" >Kwejk</option>
							<option value="demotywator">Demotywator</option>
							<option value="bebzol">Bebzol</option>
							<option value="vines">Vines</option>
						</select>
						<span class="hide input-validation"><?php echo translate( 'get_template' ) ?></span>
					</div>
	
				</div>
			</div>
			
		
	
			<div class="wizard-card wizard-card-overlay" data-cardname="site">
				<h3><?php echo translate( 'site_data' ) ?></h3>

				<div class="wizard-input-section server-site">
					<p>
						<?php echo translate( 'site_data_logo' ) ?>
					</p>
					
				
					
					<input id="input-id" type="file" data-preview-file-type="text" >

					<p>
						<?php echo translate( 'set_meta' ) ?>
					</p>
					
					<div class="input-group form-group">
						<span class="input-group-addon"><?php echo translate( 'site_title' ) ?></span>
						<input name="site[title]" type="text" class="form-control" value="<?php echo translate( 'site_title_example' ) ?>" >
					</div>
					
					<div class="input-group form-group">
						<span class="input-group-addon"><?php echo translate( 'site_description' ) ?></span>
						<input name="site[description]" type="text" class="form-control" value="<?php echo translate( 'site_description_example' ) ?>">
					</div>
					
					<div class="input-group form-group">
						<span class="input-group-addon"><?php echo translate( 'site_keywords' ) ?></span>
						<input name="site[keywords]" type="text" class="form-control" value="<?php echo translate( 'site_keywords_example' ) ?>">
					</div>
				</div>
			</div>
			
		
			<div class="wizard-card">
				<div class="wizard-loader"><img src="static/images/finish-loader.gif"></div>
				<h3><?php echo translate( 'finish' ) ?></h3>

				<div class="wizard-input-section">
					<p>
						<h3><?php echo translate( 'all_fields_valid' ) ?></h3>
						<h4><?php echo translate( 'script_ready_install' ) ?></h4>
					</p>
					<div class="fancy-groups finish">
						
						<div class="btn-group progres-tables" role="group">
							<button type="button" class="btn btn-default"><?php echo translate( 'installing_tables' ) ?></button>
							<button type="button" class="btn btn-default info">&nbsp;</button>
							<button type="button" class="btn btn-default btn-counter">0</button>
						</div>
						
						<div class="btn-group progres-settings" role="group">
							<button type="button" class="btn btn-default"><?php echo translate( 'installing_settings' ) ?></button>
							<button type="button" class="btn btn-default info">&nbsp;</button>
							<button type="button" class="btn btn-default btn-counter">0</button>
						</div>
							
						<div class="btn-group progres-translations" role="group">
							<button type="button" class="btn btn-default"><?php echo translate( 'installing_translations' ) ?></button>
							<button type="button" class="btn btn-default info">&nbsp;</button>
							<button type="button" class="btn btn-default btn-counter">0</button>
						</div>

					</div>
					<input name="install" type="hidden" value="true">
				</div>
		
	
				<div class="wizard-error">
					<div class="alert alert-warning install-error">
						<?php echo translate( 'installing_error' ) ?>
						<div></div>
					</div>
					<div class="alert alert-warning install-error">
						<strong><?php echo translate( 'server_response' ) ?> </strong> <span></span>
					</div>
				</div>
				</div>
	
				<div class="wizard-failure">
					<div class="alert alert-danger">
						<?php echo translate( 'installing_critical_error' ) ?>
					</div>
					
					<div class="alert alert-danger install-failure">
						<strong><?php echo translate( 'server_response' ) ?> </strong> <span></span>
					</div>
					
					<a class="btn btn-success im-done"><?php echo translate( 'installing_try_again' ) ?></a>
				</div>
	
				<div class="wizard-success">
					<div class="alert alert-success">
						<h4><?php echo translate( 'installing_valid' ) ?></h4>
					</div>
				
					<div class="media">
						<a href="#" class="media-left">
							<img src="static/images/user-icon.png" alt="Admin">
						</a>
						<div class="media-body admin-info">
							<h4 class="media-heading"><?php echo translate( 'admin_data' ) ?></h4>
							<div class="media-data email"><b><?php echo translate( 'email_admin_user' ) ?>:</b> <span></span></div>
							<div class="media-data login"><b><?php echo translate( 'admin_username' ) ?>:</b> <span></span></div>
							<div class="media-data password"><b><?php echo translate( 'admin_password' ) ?>:</b> <span></span></div>
						</div>
					</div>
					
					<div class="alert alert-warning hide install-problems" role="alert">
						<h4><?php echo translate( 'admin_before_site' ) ?></h4>
						<div></div>
					</div>
					
					<a href="/" class="btn btn-default btn-finish"><?php echo translate( 'admin_go_site' ) ?></a>
				</div>
			</div>
			
		</div>

		<script src="static/js/jquery-2.0.3.min.js" type="text/javascript"></script>
		<script src="static/chosen/chosen.jquery.js"></script>
		<script src="static/js/bootstrap.min.js" type="text/javascript"></script>
		<script src="static/js/prettify.js" type="text/javascript"></script>
		<script src="static/src/bootstrap-wizard.js" type="text/javascript"></script>
		<script src="static/js/fileinput.min.js" type="text/javascript"></script>
		
		
		<script src="static/install.js" type="text/javascript"></script>
		
		
	</body>
</html>
