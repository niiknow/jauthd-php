<?php
// this is like a RouteConfig in .NET MVC/WebAPI

/* add middlewares */
$app->add("JwtAuthentication");
$app->add("Cors");

/* add routes */
require __DIR__ . '/controllers/Controller.php';
require __DIR__ . '/controllers/AuthController.php';
require __DIR__ . '/controllers/HomeController.php';

?>