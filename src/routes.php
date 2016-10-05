<?php

$app->add("JwtAuthentication");
//$app->add("Cors");

require __DIR__ . '/controllers/Controller.php';
require __DIR__ . '/controllers/AuthController.php';
require __DIR__ . '/controllers/HomeController.php';

?>