<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Base bad bot view.
 *
 * @package		MMI BadBot
 * @category	view
 * @author		Me Make It
 * @copyright	(c) 2011 Me Make It
 * @license		ISC License (ISCL)
 */
abstract class View_Kohana_MMI_BadBot extends Kostache
{
	/**
	 * @var string the Twitter username
	 **/
	protected $_twitter_username;
	
	/**
	 * @var string the root URL for the site
	 **/
	protected $_url_root;

	/**
	 * Set the Twitter username.
	 * Set the root URL. 
	 * Render the view.
	 *
	 * @access	public
	 * @param	string 	template
	 * @param	mixed 	view
	 * @param	array	partials
	 * @return	void
	 */
	public function render($template = null, $view = null, $partials = null)
	{
		$this->_twitter_username = MMI_BadBot::get_config()->get('twitter_username');
		$this->_url_root = rtrim(URL::base(FALSE), '/');
		return parent::render($template, $view, $partials);
	}
} // End View_Kohana_MMI_BadBot
