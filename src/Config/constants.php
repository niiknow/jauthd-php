<?php 
	
	$setupDirectory = __DIR__ . '/constants.ini';

	$siteDefaults = parse_ini_file($setupDirectory,true);

	$siteMode = $siteDefaults['mode'];



	switch ($siteMode) {
		case 'development':
			$developement = (object) $siteDefaults['development'];
			define('APP_SITE_URL',				$developement->siteUrl);
			define('APP_DOCUMENT_URL',			$developement->documentUrl);
			define('APP_DATABASE_TYPE',			$developement->databaseType);
			define('APP_DATABASE_NAME',			$developement->databaseName);
			define('APP_DATABASE_SERVER',		$developement->databaseServer);
			define('APP_DATABASE_USERNAME',		$developement->databaseUsername);
			define('APP_DATABASE_PASSWORD',		$developement->databasePassword);
			define('APP_DATABASE_CHARSET',		$developement->databaseCharset);
			define('APP_MAXIMUM_IMAGE_UPLOAD',	$developement->maximumImageUpload	);
		break;

		case 'production':
			$production = (object) $siteDefaults['production'];
			define('APP_SITE_URL',				$production->siteUrl);
			define('APP_DOCUMENT_URL',			$production->documentUrl);
			define('APP_DATABASE_TYPE',			$production->databaseType);
			define('APP_DATABASE_NAME',			$production->databaseName);
			define('APP_DATABASE_SERVER',		$production->databaseServer);
			define('APP_DATABASE_USERNAME',		$production->databaseUsername);
			define('APP_DATABASE_PASSWORD',		$production->databasePassword);
			define('APP_DATABASE_CHARSET',		$production->databaseCharset);
			define('APP_MAXIMUM_IMAGE_UPLOAD',	$production->maximumImageUpload	);
		break;
		
	}