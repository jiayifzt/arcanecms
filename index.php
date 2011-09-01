<?php

require realpath(dirname(__FILE__) .'/includes/master.inc.php');

$router = Router::getRouter(); // create router instance
// START MF>>>
$router->map('/about', array('controller' => 'node', 'action' => 'view', 'id' => 'about'));
$router->map('/portfolio', array('controller' => 'node', 'action' => 'view', 'id' => 'portfolio'));
$router->map('/projects', array('controller' => 'node', 'action' => 'view', 'id' => 'projects'));
// <<<END MF
$router->default_routes();
$router->execute();
if($router->route_found)
{
	$controller = $router->controller; // will return name as it appears in url, ex: 'user_images'
	$action = $router->action;
	$params = $router->params; // array(...)
	$controller = ucfirst($controller);
	$contConstruct = 'get'.$controller;
	$rtobj = $controller::$contConstruct();
	if(!empty($router->id)) {
		$id = $router->id; // if parameter :id presents
		$rtobj->$action($id);
	} else {
		$rtobj->$action();
	}
	
} else {
	$HTTPError->trigger('404');
}
?>
