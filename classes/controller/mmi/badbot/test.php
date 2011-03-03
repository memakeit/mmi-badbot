<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Base test controller.
 *
 * @package		MMI BadBot
 * @category	controller
 * @author		Me Make It
 * @copyright	(c) 2011 Me Make It
 * @license		ISC License (ISCL)
 */
abstract class Controller_MMI_BadBot_Test extends Controller
{
	/**
	 * @var string the cache type
	 **/
	public $cache_type = NULL;

	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = TRUE;
} // End Controller_MMI_BadBot_Test
