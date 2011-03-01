<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Bad bot link view.
 *
 * @package		MMI BadBot
 * @author		Me Make It
 * @copyright	(c) 2011 Me Make It
 * @license		ISC License (ISCL)
 */
class View_Kohana_MMI_BadBot_Link extends Kostache
{
	/**
	 * Get the trap URL
	 *
	 * @access	protected
	 * @return	string	the URL
	 */
	protected function _url_trap()
	{
		return Route::url('mmi/badbot/trap');
	}
} // End View_Kohana_MMI_BadBot_Link
