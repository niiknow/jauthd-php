<?php
/* Auth */
$app->post('/auth/login', 'App\Controller\AuthController:userLogin');
$app->post('/auth/logout', 'App\Controller\AuthController:userLogout')->add(new App\Middleware\Auth());
$app->post('/auth/revoke', 'App\Controller\AuthController:revoke');

/* User */
$app->post('/auth/changepassword', 'App\Controller\UserController:changepassword')->add(new App\Middleware\Auth());
$app->post('/auth/forgotpassword', 'App\Controller\UserController:forgotPassword');
$app->post('/auth/me', 'App\Controller\UserController:me')->add(new App\Middleware\Auth());
$app->post('/auth/resetpassword', 'App\Controller\UserController:resetPassword');
$app->post('/auth/signup', 'App\Controller\UserController:addUser');
$app->post('/auth/tokeninfo', 'App\Controller\AuthController:tokenInfo')->add(new App\Middleware\Auth());
$app->post('/auth/verifyemail', 'App\Controller\UserController:verifyEmail');
