<?php

require realpath(dirname(__FILE__) .'/includes/master.inc.php');

$router = Router::getRouter(); // create router instance
// Add custom routes here
// e.g. $router->map('/about', array('controller' => 'node', 'action' => 'view', 'id' => 'about')); // Effectively executes Node::View('about');

$router->default_routes(); // These routes have lower precedence than the custom routes.
$router->execute(); // Commits the mapped routes.
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
	$HTTPError->trigger('404'); // The route is invalid, so the page can not be found.
}
?>
