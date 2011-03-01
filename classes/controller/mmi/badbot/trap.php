<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Bad bot trap controller.
 *
 * @package		MMI BadBot
 * @author		Me Make It
 * @copyright	(c) 2011 Me Make It
 * @license		ISC License (ISCL)
 */
class Controller_MMI_BadBot_Trap extends Controller
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
	 * Process the bad bot and render the trap page.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_index()
	{
		$this->_process();
		$this->request->response = Kostache::factory('mmi/badbot/trap')->set(array(
			'ip' => $this->_ip,
			'whois' => $this->_whois,
		))->render();
	}
	
	/**
	 * Process the bad bot and render the trap page.
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _process()
	{
		extract($_POST);
		extract($_GET);
		extract($_SERVER);
		extract($_ENV);
		$ip = $REMOTE_ADDR;
		
		// Check IP
		if ( ! MMI_BadBot::ip_valid($ip))
		{
			$this->_whois = "You did not specify a valid target host or IP.";
			return;
		}
		$this->_ip = $ip;

		// Check whitelist
		if (MMI_BadBot::in_whitelist(Request::$user_agent))
		{
 			$this->_whois = "Luckily your user-agent was found in the whitelist.";
 			return;
		}
		
		// Do the WHOIS lookup and log the IP address
		$this->_whois = MMI_BadBot::whois($ip);
		MMI_BadBot::log($ip);
		if ( ! MMI_BadBot::exists($ip))
		{
			// If the IP address is new, alert by email
			MMI_BadBot::send_email($ip, $this->_whois);
		}
	}
} // End Controller_MMI_BadBot_Trap
