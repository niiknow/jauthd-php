<?php 

	return [
				'settings'	=> [
					/*slim Settings*/
					'determineRouteBeforeAppMiddleware' => false,
					'displayErrorDetails' => true,

					/*twig view*/
					'view' => [
						'template_path' =>  __DIR__ . '/../Templates',
						],
					    /*app logger*/
					'logger' => [
						'name' => 'eventmngmnt',
						'path' => __DIR__ . '/../log/app.log',
					    ],

					 'eventmngmntDB' => [
					 	'database_type' 	=> APP_DATABASE_TYPE,
					 	'database_name' 	=> APP_DATABASE_NAME,
					 	'server' 			=> APP_DATABASE_SERVER,
					 	'username' 			=> APP_DATABASE_USERNAME,
					 	'password' 			=> APP_DATABASE_PASSWORD,
					 	'charset' 			=> APP_DATABASE_CHARSET,

					 ]
				]
			];