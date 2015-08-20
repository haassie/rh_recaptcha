<?php


$EM_CONF[$_EXTKEY] = array(
	'title' => 'reCAPTCHA',
	'description' => 'Implements Google reCAPTCHA 2 to Powermail 2',
	'category' => 'plugin',
	'author' => 'Richard Haeser',
	'author_email' => 'richardhaeser@gmail.com',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '0.0.2',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.0-7.4.99',
			'powermail' => '2.0.0-2.3.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);