<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Bad bot model.
 *
 * @package		MMI BadBot
 * @author		Me Make It
 * @copyright	(c) 2011 Me Make It
 * @license		ISC License (ISCL)
 */
class Model_MMI_BadBot extends ORM
{
	/**
	 *@var string the table name
	 */
	protected $_table_name = 'mmi_badbots';

	/**
	 *@var array the created column auto-update settings
	 */
	protected $_created_column = array('column' => 'date_created', 'format' => TRUE);

	/**
	 *@var array the updated column auto-update settings
	 */
	protected $_updated_column = array('column' => 'date_updated', 'format' => TRUE);
	
	/**
	 *@var array the validation rules
	 */
	protected $_rules = array
	(
		'ip' => array
		(
			'not_empty' => NULL,
		),
		'method' => array
		(
			'not_empty' => NULL,
		),
		'protocol' => array
		(
			'not_empty' => NULL,
		),
		'ua' => array
		(
			'not_empty' => NULL,
		),
	);
} // End Model_MMI_BadBot
