<?php if(!defined('DINGO')){die('External Access to File Denied');}

/**
 * Dingo Framework Configuration File
 *
 * @Author          Evan Byrne
 * @Copyright       2008 - 2009
 * @Project Page    http://www.dingoframework.com
 */


/* Database Settings */
config::set('db',array(
	'driver'=>'mysql',       // Driver
	'host'=>'localhost',     // Host
	'username'=>'root',      // Username
	'password'=>'pass',      // Password
	'database'=>'test'       // Database
));


/* Auto Load Classes */
config::set('autoload_libraries',array('db'));

/* Auto Load Helpers */
config::set('autoload_helpers',array('url'));

/* Sessions */
config::set('session',array(
	'table'=>'sessions',
	'cookie'=>array('path'=>'/','expire'=>'+1 months')
));


/* Application Folder Locations */
config::set('folder_views','views');             // Views
config::set('folder_controllers','controllers'); // Controllers
config::set('folder_models','models');           // Models
config::set('folder_helpers','helpers');         // Helpers
config::set('folder_plugins','plugins');         // Plugins
config::set('folder_cache','cache');             // Cache
config::set('folder_languages','languages');     // Languages
config::set('folder_errors','errors');           // Errors