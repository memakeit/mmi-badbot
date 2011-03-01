<?php defined('SYSPATH') or die('No direct script access.');

// MMI badbot configuration
return array(
	'email' => array(
		'from' => 'badbot@yoursite.com',
		'to' => 'webmaster@yoursite.com',
	),
	'twitter_username' => 'badbot',
	'whitelist' => array('bingbot', 'googlebot', 'msnbot', 'slurp', 'teoma', 'yandex'),
);
