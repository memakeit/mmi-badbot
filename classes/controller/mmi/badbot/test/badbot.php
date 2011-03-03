<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Bad bot test controller.
 *
 * @package		MMI BadBot
 * @category	controller
 * @author		Me Make It
 * @copyright	(c) 2011 Me Make It
 * @license		ISC License (ISCL)
 */
class Controller_MMI_BadBot_Test_BadBot extends Controller_MMI_BadBot_Test
{
	/**
	 * Test bad bot functionality.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_index()
	{
//		$msg = MMI_BadBot::whois('190.212.194.22');
//		die("<pre>{$msg}</pre>");
		
		$view = Kostache::factory('mmi/badbot/link')->render();
		die($view);
	}
} // End Controller_MMI_BadBot_Test_BadBot
