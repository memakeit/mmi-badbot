<?php defined('SYSPATH') or die('No direct script access.');

// Denied route
Route::set('mmi/badbot/denied', 'mmi/badbot/denied')
->defaults(array(
	'controller' => 'denied',
	'directory' => 'mmi/badbot',
));

// Trap route
Route::set('mmi/badbot/trap', 'mmi/badbot/trap')
->defaults(array(
	'controller' => 'trap',
	'directory' => 'mmi/badbot',
));

// Test routes
if (Kohana::$environment !== Kohana::PRODUCTION)
{
	Route::set('mmi/badbot/test', 'mmi/badbot/test/<controller>(/<action>)')
	->defaults(array(
		'directory' => 'mmi/badbot/test',
	));
}
