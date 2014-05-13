<?php

error_reporting(E_STRICT|E_ALL);

// Application configuration
//----------------------------------------------------------------------------------------------

/**
 * Your Application's Default Timezone
 * Syntax for your local timezone can be found at
 * http://www.php.net/timezones
 */
date_default_timezone_set('America/New_York');

// Application's Base URL
define('BASE_URL','http://localhost/dingo/0-4-0/0-4-2/');

// Does Application Use Mod_Rewrite URLs?
define('MOD_REWRITE',FALSE);

/**
 * What Should Dingo Use To Determine Page Paths?
 * This is usually $_SERVER['REQUEST_URI']
 * Try changing to one of the following if paths don't work
 * $_SERVER['SCRIPT_PATH']
 * $_SERVER['SCRIPT_NAME']
 * $_SERVER['REQUEST_URI']
 */
define('PATH_INFO',$_SERVER['PHP_SELF']);

// Dingo Location
$system = 'system';

// Application Location
$application = 'application';

// Config Location (in relation to application location)
$config = 'config';

// Allowed Characters in URL
$allowed_chars = '/^[ \!\,\~\&\.\:\+\@\-_a-zA-Z0-9]+$/';

// Turn Debugging On?
define('DEBUG',TRUE);

// Turn Error Logging On?
define('ERROR_LOGGING',FALSE);

// Error Log File Location
define('ERROR_LOG_FILE','log.txt');


// End of configuration
//----------------------------------------------------------------------------------------------
define('DINGO',1);
require_once("$system/core/DingoBoot.php");
bootstrap::run();