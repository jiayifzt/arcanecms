<?php

require realpath(dirname(__FILE__) .'/includes/master.inc.php');

$req = explode('/', $_SERVER['REQUEST_URI']);
$scriptloc = explode('/',$_SERVER['SCRIPT_NAME']);
 
for($i= 0;$i < sizeof($scriptloc);$i++)
{
    if ($req[$i]==$scriptloc[$i])
    {
        unset($req[$i]);
    }
}
 
$req = array_values($req);

if(empty($req[0]))
{
	$page = ARCANE_DEFAULT_PAGE;
} else {
	$page = $req[0];
}

if($page == 'login') {
	if($Auth->loggedIn()) redirect(ARCANE_SITE_URL);

    if(!empty($_POST['username']))
    {
        if($Auth->login($_POST['username'], $_POST['password']))
        {
            if(isset($_REQUEST['r']) && strlen($_REQUEST['r']) > 0)
                redirect($_REQUEST['r']);
            else
                redirect(ARCANE_SITE_URL.'/admin/');
        }
        else
            $Error->add('username', "We're sorry, you have entered an incorrect username and password. Please try again.");
    }

    // Clean the submitted username before redisplaying it.
    $username = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';
	// Tell ThemeEngine to start buffering the page, set the page's title
	$ThemeEngine->go(ARCANE_SITE_NAME.' - Login');
	echo '<div id="loginForm">
    <form action="'.ARCANE_SITE_URL.'/login/" method="post">
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
		<div class="clear"></div>
		<p><input type="submit" name="btnlogin" value="Login" id="btnlogin" class="button" /></p>
		<input type="hidden" name="r" value="'.htmlspecialchars(@$_REQUEST['r']).'" id="r">
    </form>
	</div>';
} else if($page == 'logout') {
	$Auth->logout();
} else if($page == 'error400') {
	$HTTPError->load("400");
} else if($page == 'error401') {
	$HTTPError->load("401");
} else if($page == 'error403') {
	$HTTPError->load("403");
} else if($page == 'error404') {
	$HTTPError->load("404");
} else if($page == 'error500') {
	$HTTPError->load("500");
} else {
	$db = Database::getDatabase(true);

	$page = $db->escape($page);
	$result = $db->query("SELECT * FROM pages WHERE shortname='$page'");
	$row = $db->getRow($result);
	
	if($row['data']==null) {
		$HTTPError->trigger("404");
	} else {
		// Tell ThemeEngine to start buffering the page, set the page's title
		$ThemeEngine->go(ARCANE_SITE_NAME.' - '.ucwords($row['title']));
	
		echo $row['data'];
	}
}
?>
