<?php


$EM_CONF[$_EXTKEY] = array(
	'title' => 'reCAPTCHA for Powermail',
	'description' => 'Implements Google reCAPTCHA 2 to Powermail 2 and 3',
	'category' => 'plugin',
	'author' => 'Richard Haeser',
	'author_email' => 'richardhaeser@gmail.com',
	'state' => 'stable',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '1.1.0',
	'constraints' => 
	array (
		'depends' => 
		array (
			'typo3' => '6.2.0-8.7.99',
			'powermail' => '2.0.0-3.99.99',
		),
		'conflicts' => 
		array (
		),
		'suggests' => 
		array (
		),
	),
	'clearcacheonload' => false,
	'author_company' => NULL,
);

