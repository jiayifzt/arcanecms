<?php
    // Application flag
    define('ARCANE', true);

    date_default_timezone_set('America/Chicago'); //TODO: Make this a config option.

    // Determine our absolute document root
    define('DOC_ROOT', realpath(dirname(__FILE__) . '/../'));

    // Global include files
    require_once DOC_ROOT . '/includes/functions.inc.php';  // spl_autoload_register() is contained in this file
    require_once DOC_ROOT . '/includes/class.dbobject.php'; // DBOBject...
    require_once DOC_ROOT . '/includes/class.objects.php';  // and its subclasses

    // Fix magic quotes
    if(get_magic_quotes_gpc())
    {
        $_POST    = fix_slashes($_POST);
        $_GET     = fix_slashes($_GET);
        $_REQUEST = fix_slashes($_REQUEST);
        $_COOKIE  = fix_slashes($_COOKIE);
    }

    // Load our config settings
    $Config = Config::getConfig();

    // Store session info in the database?
    if(Config::get('useDBSessions') === true)
        DBSession::register();

    // Initialize our session
    session_name('arcanesesh');
    session_start();
	
    // Initialize current user
    $Auth = Auth::getAuth();

    // Object for tracking and displaying error messages
    $Error = Error::getError();

    // Initialize ThemeEngine (Creates a singleton ThemeEngine)
    $ThemeEngine = ThemeEngine::getThemeEngine();
	
	// Initialize HTTPError for handling HTTP status codes such as 404.
	$HTTPError = HTTPError::getHTTPError();

    // If you need to bootstrap a first user into the database, you can run this line once
    //Auth::createNewUser('admin', 'password', 'admin'); //DEBUG purposes only, obviously not a safe option.
?>
