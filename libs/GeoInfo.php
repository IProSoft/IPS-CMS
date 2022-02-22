<?php

class GeoInfo
{

	public $ip = null;
	public $browser = array();
	
	public function getIp()
	{
	
			if (getenv("HTTP_CLIENT_IP"))
				$ip = getenv("HTTP_CLIENT_IP");
			elseif (getenv("HTTP_X_FORWARDED_FOR"))
				$ip = getenv("HTTP_X_FORWARDED_FOR");
			elseif (getenv("HTTP_X_FORWARDED"))
				$ip = getenv("HTTP_X_FORWARDED");
			elseif (getenv("HTTP_FORWARDED_FOR"))
				$ip = getenv("HTTP_FORWARDED_FOR");
			elseif (getenv("HTTP_FORWARDED"))
				$ip = getenv("HTTP_FORWARDED");
			else
				$ip = $_SERVER["REMOTE_ADDR"];
		
		return $ip;
			
	}
	public function getBrowser()
	{
		$u_agent = isset($_SERVER['HTTP_USER_AGENT'])
               ? $_SERVER['HTTP_USER_AGENT']
               : 'none';
		$bname = 'Unknown';
		$platform = 'Unknown';
		$ub = 'Unknown';
		$version= "";

		if (preg_match('/linux/i', $u_agent))
		{
			$platform = 'linux';
		}
		elseif (preg_match('/macintosh|mac os x/i', $u_agent))
		{
			$platform = 'mac';
		}
		elseif (preg_match('/windows|win32/i', $u_agent))
		{
			$platform = 'windows';
		}

		if( preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
		{
			$bname = 'Internet Explorer';
			$ub = "MSIE";
		}
		elseif( preg_match('/Firefox/i',$u_agent))
		{
			$bname = 'Mozilla Firefox';
			$ub = "Firefox";
		}
		elseif(preg_match('/Chrome/i',$u_agent))
		{
			$bname = 'Google Chrome';
			$ub = "Chrome";
		}
		elseif(preg_match('/Safari/i',$u_agent))
		{
			$bname = 'Apple Safari';
			$ub = "Safari";
		}
		elseif(preg_match('/Opera/i',$u_agent))
		{
			$bname = 'Opera';
			$ub = "Opera";
		}
		elseif(preg_match('/Netscape/i',$u_agent))
		{
			$bname = 'Netscape';
			$ub = "Netscape";
		}

		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>' . join('|', $known) .
		')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	   
		if ( @preg_match_all($pattern, $u_agent, $matches)) 
		{
			$i = count($matches['browser']);
			if ($i != 1)
			{
				if (strripos($u_agent,"Version") < strripos($u_agent,$ub))
				{
					$version = $matches['version'][0];
				}
				else
				{
					$version = $matches['version'][1];
				}
			}
			else
			{
				$version = $matches['version'][0];
			}
		}
		if ( $version == null || $version == "" )
		{
			$version = "?";
		}

		return array(
			'userAgent' => $u_agent,
			'name'      => $bname,
			'version'   => $version,
			'platform'  => $platform
		);
	}	
}

?>