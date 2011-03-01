<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Bad bot test controller.
 *
 * @package		MMI BadBot
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
// 		echo '<br/>notice = '.(Kohana::debug($notice));*/
		MMI_BadBot::check();
		
		if (Kohana::$environment !== Kohana::PRODUCTION)
		{
			$this->request->response .= View::factory('profiler/stats');
		}
		$deny = MMI_BadBot::deny();
		echo '<br/>deny = '.(Kohana::debug($deny));
	}
} // End Controller_MMI_BadBot_Test_BadBot
