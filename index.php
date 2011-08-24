<?php

require 'includes/master.inc.php';

$req = explode('/', $_SERVER['REQUEST_URI']);

if(empty($req[1]))
{
	$page = 'home';
} else {
	$page = $req[1]; //1: store GET var in a new var
}

if($page == 'login') {
	if($Auth->loggedIn()) redirect(WEB_ROOT);

    if(!empty($_POST['username']))
    {
        if($Auth->login($_POST['username'], $_POST['password']))
        {
            if(isset($_REQUEST['r']) && strlen($_REQUEST['r']) > 0)
                redirect($_REQUEST['r']);
            else
                redirect(WEB_ROOT);
        }
        else
            $Error->add('username', "We're sorry, you have entered an incorrect username and password. Please try again.");
    }

    // Clean the submitted username before redisplaying it.
    $username = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';
	// Tell ThemeEngine to start buffering the page, set the page's title
	$themeengine->go(ARCANE_SITE_NAME.' - Login');
	echo '<div id="loginForm">
    <form action="login" method="post">
        '.$Error.'
		<h1 class="title"><span class="title_color1">Login to</span> <span class="title_color2">Arcane</span></h1>
		<br />
		<div class="left">
			<p><label for="username">Username:</label></p>
			<br />
			<br />
			<p><label for="password">Password:</label></p>
			<br />
			<br />
		</div>
		<div class="right">
			<p><input type="text" name="username" value="'.$username.'" id="username" class="textinput" /></p>
			<br />
			<p><input type="password" name="password" value="" id="password" class="textinput" /></p>
		</div>
		<div style="clear:both;"></div>
		<p><input type="submit" name="btnlogin" value="Login" id="btnlogin" class="button" /></p>
		<input type="hidden" name="r" value="'.htmlspecialchars(@$_REQUEST['r']).'" id="r">
    </form>
	</div>';
} else if($page == 'logout') {
	$Auth->logout();
} else if($page == '404') {
	$themeengine->go(ARCANE_SITE_NAME.' - 404');
	echo'<div class="errorpage">
	<h1><span class="title_color1">Error 404:</span> <span class="title_color2">File Not Found</span></h1><br />
	The requested file was not found on the server. It may have been moved or deleted. If you believe this was in error, please contact us and we will check it out. Also, contact the site from which you came to report a broken link.</div>';
} else if($page == '403') {
	$themeengine->go(ARCANE_SITE_NAME.' - 403');
	echo'<div class="errorpage">
	<h1><span class="title_color1">Error 403:</span> <span class="title_color2">Forbidden</span></h1><br />
	What you are doing is not allowed. Maybe we should call the cops. If you believe this was in error, please contact us and we will check it out.</div>';
} else if($page == '401') {
	$themeengine->go(ARCANE_SITE_NAME.' - 401');
	echo'<div class="errorpage">
	<h1><span class="title_color1">Error 401:</span> <span class="title_color2">Authorization Required</span></h1><br />
	An authorized session was not found. Please try again. If you believe this was in error, please contact us and we will check it out.</div>';
} else if($page == '400') {
	$themeengine->go(ARCANE_SITE_NAME.' - 400');
	echo'<div class="errorpage">
	<h1><span class="title_color1">Error 400:</span> <span class="title_color2">Bad Request</span></h1><br />
	The URL you were trying to access was malformed and the server was unable to process it. If you believe this was in error, please contact us and we will check it out.</div>';
} else if($page == '500') {
	$themeengine->go(ARCANE_SITE_NAME.' - 500');
	echo'<div class="errorpage">
	<h1><span class="title_color1">Error 500:</span> <span class="title_color2">Internal Server Error</span></h1><br />
	Something went wrong on our end. Probably something misconfigured. This is usually temporary. Please try again later. If you believe this was in error, please contact us and we will check it out.</div>';
} else {
	$db = Database::getDatabase(true);

	$page = $db->escape($page);
	$result = $db->query("SELECT * FROM pages WHERE shortname='$page'");
	$row = $db->getRow($result);
	
	if($row['data']==NULL) {
		redirect(DOC_ROOT . '/404');
	} else {
		// Tell ThemeEngine to start buffering the page, set the page's title
		$themeengine->go(ARCANE_SITE_NAME.' - '.ucwords($row['title']));
	
		echo $row['data'];
	}
}
?>
