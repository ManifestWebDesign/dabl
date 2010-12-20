<?php

/**
 * @param string $route
 */
function load_controller($route) {
	$controller_route = new ControllerRoute($route);

	$controller = $controller_route->getController();
	
	if (null === $controller) {
		file_not_found($route);
	}

	$controller->doAction($controller_route->getAction(), $controller_route->getParams());
}