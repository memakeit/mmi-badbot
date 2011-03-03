<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Bad bot trap view.
 *
 * @package		MMI BadBot
 * @category	view
 * @author		Me Make It
 * @copyright	(c) 2011 Me Make It
 * @license		ISC License (ISCL)
 */
class View_Kohana_MMI_BadBot_Trap extends View_MMI_BadBot
{
	/**
	 * @var string the IP address
	 **/
	protected $_ip;
	
	/**
	 * @var string the WHOIS data
	 **/
	protected $_whois;
	
	/**
	 * Set a variable.
	 *
	 * @access	public
	 * @param	string	the parameter name
	 * @param	mixed	the parameter value
	 * @return	void
	 */
	public function __set($name, $value)
	{
		$name = trim(strtolower($name));
		switch ($name)
		{
			case 'ip':
			case 'whois':
				$method = "_process_{$name}";
				$this->$method($value);
			break;
		}
	}
	
	/**
	 * Process the IP address.
	 *
	 * @access	protected
	 * @param	string	the IP address
	 * @return	void
	 */
	protected function _process_ip($value)
	{
		(empty($value)) AND $value = 'UNKNOWN';
 		$this->_ip = $value; 
	}
	
	/**
	 * Process the WHOIS data.
	 *
	 * @access	protected
	 * @param	string	the WHOIS data
	 * @return	void
	 */
	protected function _process_whois($value)
	{
		$this->_whois = "\n\n{$value}";
	}
	
	/**
	 * Get the formatted date.
	 *
	 * @access	protected
	 * @return	string	the formatted date
	 */
	protected function _formatted_date()
	{
		return date('l, F jS Y @ H:i:s');
	}
} // End View_Kohana_MMI_BadBot_Trap
