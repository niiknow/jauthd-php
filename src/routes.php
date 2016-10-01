<?php 
	/*Login*/
	$app->post('/auth/login','Controller\Login:userLogin');
	$app->post('/auth/logout','Controller\Login:userLogout')->add( new Middleware\Auth() );
	
	/*User Register or add*/
	$app->post('/auth/signup','Controller\User:addUser');