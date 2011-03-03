<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Bad bot helper class.
 *
 * @package		MMI BadBot
 * @author		Me Make It
 * @copyright	(c) 2011 Me Make It
 * @license		ISC License (ISCL)
 */
class Kohana_MMI_BadBot
{
	// Constants
	const ARIN = 'whois.arin.net';
	
	/**
	 * @var Kohana_Config site settings
	 */
	protected static $_config;
	
	/**
	 * @var array an associative array of search strings and server settings
	 */
  	protected static $_server_map = array(
    	'nic.ad.jp' => array('server' => 'whois.nic.ad.jp', 'extra' => '/e'),
     	'nic.or.kr' => array('server' => 'whois.nic.or.kr'),
    	'whois.afrinic.net' => array('server' => 'whois.afrinic.net'),
    	'whois.apnic.net' => array('server' => 'whois.apnic.net'),
     	'whois.lacnic.net' => array('server' => 'whois.lacnic.net'),
     	'whois.registro.br' => array('server' => 'whois.registro.br'),
     	'RIPE.NET' => array('server' => 'whois.ripe.net'),
  	);
	
	/**
	 * If it is a bad bot, redirect to the denied page.
	 *
	 * @access	public
	 * @return	void
	 */
	public static function check()
	{
		if (self::_deny())
		{
			$url = Route::url('mmi/badbot/denied', NULL, TRUE);
			Request::instance()->redirect($url);
		}
	}
	
	/**
	 * Get the IP address, do the WHOIS lookup, and log the bad bot to the database.
	 * If the bot is new, notify the webmaster.
	 *
	 * @access	protected
	 * @param	string	the IP address
	 * @param	string	the WHOIS data
	 * @return	void
	 */
	public static function process(& $ip, & $whois)
	{
		extract($_POST);
		extract($_GET);
		extract($_SERVER);
		extract($_ENV);
		$ip = $REMOTE_ADDR;
		
		// Check the IP
		if ( ! self::_ip_valid($ip))
		{
			$whois = "You did not specify a valid target host or IP.";
			return;
		}

		// Check the whitelist
		if (self::_in_whitelist(Request::$user_agent))
		{
 			$whois = "Luckily your user-agent was found in the whitelist.";
 			return;
		}
		
		// Do the WHOIS lookup and log the IP address
		$whois = self::_whois($ip);
		$exists = self::_exists($ip);
		self::_log($ip);
		if ( ! $exists)
		{
			// If the bot is new, notify the webmaster
			self::_send_email($ip, $whois);
		}
	}
	
	/**
	 * Get the configuration settings.
	 *
	 * @access	public
	 * @param	boolean	return the configuration as an array?
	 * @return	mixed
	 */
	public static function get_config($as_array = FALSE)
	{
		(self::$_config === NULL) AND self::$_config = Kohana::config('mmi-badbot');
		if ($as_array)
		{
			return self::$_config->as_array();
		}
		return self::$_config;
	}
	
	/**
	 * Check if an IP address is valid.
	 *
	 * @access	protected
	 * @param	string	the IP address
	 * @return	boolean	is the address valid?
	 */
	protected static function _ip_valid($ip)
	{
		return ($ip AND Validate::ip($ip)); 
	}

	/**
	 * Check if a user-agent is in the whitelist.
	 *
	 * @access	protected
	 * @param	string	the user-agent
	 * @return	boolean	is it in the whitelist?
	 */
	protected static function _in_whitelist($user_agent)
	{
		$whitelist = self::get_config()->get('whitelist');
		if (is_array($whitelist))
		{
			$whitelist = implode('|', $whitelist);
	 		if (preg_match("/({$whitelist})/i", $user_agent)) 
	 		{
	 			return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	 * Check if an IP address is in the bad bot database and is not allowed.
	 *
	 * @access	protected
	 * @param	string	the IP address
	 * @return	boolean	deny the address?
	 */
	protected static function _deny($ip = NULL)
	{
		(empty($ip)) AND $ip = $_SERVER['REMOTE_ADDR'];
		if ( ! empty($ip))
		{
			$ip = trim($ip);
			return ORM::factory('mmi_badbot', array('ip' => $ip, 'allowed' => 0))->loaded();
		}
		return FALSE;
	}

	/**
	 * Check if an IP address is in the bad bot database.
	 *
	 * @access	protected
	 * @param	string	the IP address
	 * @return	boolean	does the address exist?
	 */
	protected static function _exists($ip = NULL)
	{
		(empty($ip)) AND $ip = $_SERVER['REMOTE_ADDR'];
		if ( ! empty($ip))
		{
			$ip = trim($ip);
			RETURN ORM::factory('mmi_badbot', array('ip' => $ip))->loaded();
		}
		return FALSE;
	}
	
	/**
	 * Lookup the WHOIS data.
	 * @link	http://perishablepress.com/press/2010/07/14/blackhole-bad-bots/
	 * 
	 * @access	protected
	 * @param	string	the IP address
	 * @param	string	the WHOIS server
	 * @param	string	extra parameters for the WHOIS request
	 * @return	string	the WHOIS data
	 */
	protected static function _whois($ip, $server = NULL, $extra = '') 
	{
		$code = '';
		$msg = '';
		if (empty($server))
		{
			$code = 'n ';
			$extra = '';
			$server = self::ARIN;
		}
		
		if ( ! $ip = gethostbyname($ip)) 
		{
			return "Can't do a WHOIS lookup without an IP address.";
		} 
		
		$buffer = '';
		$sock = NULL;
		try
		{
			$sock = fsockopen($server, 43, $num, $error, 20);
		}
		catch (Exception $e)
		{
			unset($sock);
			Kohana::$log->add(Kohana::ERROR, Kohana::exception_text($e));
		}
		if ( ! $sock) 
		{
			unset($sock);
			return "Timed out connecting to {$server} (port 43).";
		} 
		
		try
		{
			fputs($sock, "{$code}{$ip}{$extra}\n");
			while ( ! feof($sock))
			{
				$buffer .= fgets($sock, 10240);
			}
			fclose($sock);
		}
		catch (Exception $e)
		{
			unset($sock);
			Kohana::$log->add(Kohana::ERROR, Kohana::exception_text($e));
		}
		
		if ( ! empty($buffer))
		{
			$buffer = mb_convert_encoding($buffer, 'UTF-8', mb_detect_encoding($buffer, 'UTF-8, ISO-8859-1', TRUE));
			$msg .= nl2br($buffer);	
		}
		
		// Check if an additional WHOIS lookup is necessary
		if ($server === self::ARIN)
		{
			$extra = '';
			$server = '';
			foreach (self::$_server_map as $search => $server_settings)
			{
				if (preg_match("/{$search}/", $buffer)) 
				{
					$extra = Arr::get($server_settings, 'extra', '');
					$server = $server_settings['server'];
					break;
				}
			} 
			if ( ! empty($server)) 
			{
				$msg .= "\nDeferred to specific whois server: {$server} ...\n\n";
				$msg .= self::whois($ip, $server, $extra);
			}
		}
		return trim(preg_replace("/#/", " ", strip_tags($msg)));
	}

	/**
	 * Log a bad bot to the database.
	 *
	 * @access	protected
	 * @param	string	the IP address
	 * @return	void
	 */
	protected static function _log($ip = NULL)
	{
		(empty($ip)) AND $ip = $_SERVER['REMOTE_ADDR'];
		$badbot = ORM::factory('mmi_badbot', array('ip' => $ip));
		if ($badbot->loaded())
		{
			// Increment the visit count
			$badbot->visits = DB::expr('`visits` + 1');
			try
			{
				$saved = $badbot->save();
			}
			catch (Exception $e)
			{
				Kohana::$log->add(Kohana::ERROR, Kohana::exception_text($e));
			}
		}
		else
		{
			$method = $_SERVER['REQUEST_METHOD']; 
			(empty($method)) AND $method = 'GET';
			$protocol = $_SERVER['SERVER_PROTOCOL'];
			(empty($protocol)) AND $protocol = 'HTTP/1.1';
			$ua = trim(Request::$user_agent, 255);
			(empty($ua)) AND $ua = 'unknown';
		
			// Create a new bad bot entry 
			$badbot->values(array(
				'ip' => $ip,
				'method' => $method,
				'protocol' => $protocol,
				'ua' => $ua,
			));
			if ($valid = $badbot->check())
			{
				try
				{
					$badbot->save();
				}
				catch (Exception $e)
				{
					Kohana::$log->add(Kohana::ERROR, Kohana::exception_text($e));
				}
			}
			else
			{
				Kohana::$log->add(Kohana::ERROR, 'Invalid bad bot:'.Kohana::debug($badbot->as_array()));
			}
		}
	}

	/**
	 * Send an email containing details about the bad bot.
	 *
	 * @access	protected
	 * @param	string	the IP address
	 * @param	string	the WHOIS data
	 * @return	void
	 */
	protected static function _send_email($ip, $whois)
	{
		$datestamp = date("l, F jS Y @ H:i:s");
		$msg =<<<EOL
Your IP Address is {$ip}
WHOIS Lookup for {$ip}
{$datestamp}

{$whois}
EOL;
		
		$settings = self::get_config()->get('email', array());
		$from = Arr::get($settings, 'from');
		$subject   = "Bad Bot Alert!";
		$to = Arr::get($settings, 'to');
		$url = $_SERVER['REQUEST_URI'];
		$user_agent = Request::$user_agent;
		
		$msg =<<<EOL
{$datestamp}

URL Request: {$url}
IP Address: {$ip}
User Agent: {$user_agent}
Whois Lookup:

{$msg}
EOL;
		try
		{
			mail($to, $subject, $msg, "From: {$from}");
		}
		catch (Exception $e) 
		{
			Kohana::$log->add(Kohana::ERROR, Kohana::exception_text($e));
		}
	}
} // End Kohana_MMI_BadBot
