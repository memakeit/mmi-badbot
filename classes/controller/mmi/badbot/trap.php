<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Bad bot trap controller.
 *
 * @package		MMI BadBot
 * @category	controller
 * @author		Me Make It
 * @copyright	(c) 2011 Me Make It
 * @license		ISC License (ISCL)
 */
class Controller_MMI_BadBot_Trap extends Controller
{
	/**
	 * Process the bad bot and render the trap page.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_index()
	{
		MMI_BadBot::process($ip, $whois);
		$this->request->response = Kostache::factory('mmi/badbot/trap')->set(array(
			'ip' => $ip,
			'whois' => $whois,
		))->render();
	}
} // End Controller_MMI_BadBot_Trap
