<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Bad bot denied controller.
 *
 * @package		MMI BadBot
 * @author		Me Make It
 * @copyright	(c) 2011 Me Make It
 * @license		ISC License (ISCL)
 */
class Controller_MMI_BadBot_Denied extends Controller
{
	/**
	 * Render the denied notice.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_index()
	{
		$this->request->response = Kostache::factory('mmi/badbot/denied')->render();
	}
} // End Controller_MMI_BadBot_Denied
