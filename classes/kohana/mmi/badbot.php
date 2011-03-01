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
	 * Check if an IP address is valid.
	 *
	 * @access	public
	 * @param	string	the IP address
	 * @return	boolean	is the address valid?
	 */
	public static function ip_valid($ip)
	{
		return ($ip AND Validate::ip($ip)); 
	}

	/**
	 * Check if a user-agent is in the whitelist.
	 *
	 * @access	public
	 * @param	string	the user-agent
	 * @return	boolean	is it in the whitelist?
	 */
	public static function in_whitelist($user_agent)
	{
		$whitelist = MMI_BadBot::get_config()->get('whitelist', array());
		if ($whitelist)
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
	 * If it is a bad bot, redirect to the denied page.
	 *
	 * @access	public
	 * @return	void
	 */
	public static function check()
	{
		if (self::deny())
		{
			$url = Route::url('mmi/badbot/denied', NULL, TRUE);
			Request::instance()->redirect($url);
		}
	}
	
	/**
	 * Check if an IP address is a bad bot.
	 *
	 * @access	public
	 * @param	string	the IP address
	 * @return	boolean	deny the address?
	 */
	public static function deny($ip = NULL)
	{
		$deny = FALSE;
		(empty($ip)) AND $ip = $_SERVER['REMOTE_ADDR'];
		if ( ! empty($ip))
		{
			$ip = trim($ip);
			$deny = ORM::factory('mmi_badbot', array('ip' => $ip, 'allowed' => 0))->loaded();
		}
		return $deny;
	}

	/**
	 * Check if an IP address exists in the database.
	 *
	 * @access	public
	 * @param	string	the IP address
	 * @return	boolean	does the address exist?
	 */
	public static function exists($ip = NULL)
	{
		$exists = FALSE;
		(empty($ip)) AND $ip = $_SERVER['REMOTE_ADDR'];
		if ( ! empty($ip))
		{
			$ip = trim($ip);
			$exists = ORM::factory('mmi_badbot', array('ip' => $ip))->loaded();
		}
		return $exists;
	}
	
	/**
	 * Log a bad bot.
	 *
	 * @access	public
	 * @param	string	the IP address
	 * @return	void
	 */
	public static function log($ip = NULL)
	{
		(empty($ip)) AND $ip = $_SERVER['REMOTE_ADDR'];
		$badbot = ORM::factory('mmi_badbot', array('ip' => $ip));
		if ($badbot->loaded())
		{
			$badbot->hits = DB::expr('`hits` + 1');
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
		
			$badbot = ORM::factory('mmi_badbot')->values(array(
				'ip' => $ip,
				'method' => $method,
				'protocol' => $protocol,
				'ua' => $ua,
			));
			$saved = FALSE;
			if ($valid = $badbot->check())
			{
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
				Kohana::$log->add(Kohana::ERROR, 'Invalid bad bot:'.Kohana::debug($badbot->as_array()));
			}
		}
	}
	
	/**
	 * Lookup the WHOIS data.
	 * 
	 * @link	http://perishablepress.com/press/2010/07/14/blackhole-bad-bots/
	 * 
	 * @access	public
	 * @param	string	the IP address
	 * @param	string	the WHOIS server
	 * @param	string	extra parameters for the WHOIS request
	 * @return	string	the WHOIS data
	 */
	public static function whois($ip, $server = NULL, $extra = '') 
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
			$msg .= "Can't do a WHOIS lookup without an IP address.";
		} 
		else 
		{
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
				$msg .= "Timed out connecting to {$server} (port 43).";
			} 
			else 
			{
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
		}
		return trim(preg_replace("/#/", " ", strip_tags($msg)));
	}
		
	/**
	 * Send an email containing details about the bad bot.
	 *
	 * @access	public
	 * @param	string	the IP address
	 * @param	string	the WHOIS data
	 * @return	void
	 */
	public static function send_email($ip, $whois)
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
} // End Kohana_MMI_BadBot
