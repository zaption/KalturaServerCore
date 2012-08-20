<?php

require_once(dirname(__FILE__) . '/../../../../infra/general/infraRequestUtils.class.php');

/**
 * Will hold helper functions and conventions for working with the HttpRequest object
 *
 */
class requestUtils extends infraRequestUtils
{
	const SECURE_COOKIE_PREFIX = "___";
	
	private static $s_cookies_to_be_set = array();
	
	static public function isPost ( $context )
	{
		return ($context->getRequest()->getMethod() == sfRequest::POST) ;
	}

	static public function getParameter ( $param_name , $value_if_missing = NULL , $update_request_with_value = false )
	{
		if ( array_key_exists( $param_name , $_REQUEST ) )
		{
			return $search_mode = $_REQUEST[$param_name];
		}
	 	else
	 	{
	 		if ( isset ( $value_if_missing) )
	 		{
	 			if ( $update_request_with_value )
	 			{
	 				// in this case - from this point onwards - the value will be the new value
					// TODO - this is a nasty solution - should remove ?? 
					// modifying such a parameter as if recieved from the user is very error-prone !! 
	 				$_REQUEST[$param_name] = $value_if_missing;
	 			}
	 			return $value_if_missing;
	 		}
	 		else
	 		{
	 			// the parameter does not exist and there is no default value - 
	 			// return what the trivial method would ...
	 			// TODO - do we wnat some better default value to return  ?? 
	 			return @$_REQUEST[$param_name];
	 		}
	 	}
	}
	
	// TODO - implement a generic method to be used by getGetParam, getPostParam , getCookie ...
	static private function getWithDefault ( )
	{

	}


	public static function getHost ( )
	{
		$url = 'http';
		$url .= isset ( $_SERVER['HTTPS'] ) ? ( @$_SERVER['HTTPS']=='on' ? 's' : '' ) : "";
		$url .= '://' ;
		// $url .= .$_SERVER['SERVER_PORT'];

		$host =  @$_SERVER['HTTP_HOST'];
		if ( ! $host )
			$host = @$_SERVER['argv'][1];
		$url .= $host;
		
		return $url;
	}
	
	public static function getCdnHost ($protocol = 'http')
	{
		if ($protocol == "https")
			return "$protocol://".kConf::get("cdn_host_https");
		return "$protocol://".kConf::get("cdn_host");
	}
	
	public static function getRtmpUrl ( )
	{
		return kConf::get("rtmp_url");
	}
	
	public static function getIisHost ($protocol = 'http')
	{
		return "$protocol://".kConf::get("iis_host");
	}
	
	// TODO - see how can rewrite better code for the doc-root of the application !!
	public static function  getWebRootUrl( $include_host = true )
	{
		$url = "";
		if ( $include_host )
		{
			$url = self::getHost();
			$url = preg_replace("/www\d\.kaltura\.com/", "www.kaltura.com", $url);
			$url = preg_replace("/kaldev\d\.kaltura\.com/", "kaldev.kaltura.com", $url);
			$url = preg_replace("/sandbox\d\.kaltura\.com/", "sandbox.kaltura.com", $url);
		}

		$request_url = self::requestUri();
		$pos = strpos( $request_url, '/');
		// find the second slash - that's the end of the rood dir
		$pos = strpos( $request_url, '/' , $pos +1 );
		if ( $pos > 0 )
		{
			return $url .= substr($request_url,0,$pos+1);
		}
		else
		{
			return $url . "/" ;
		}
	}

	// found bits and peaces from Ling's code (the Contact-Importer author) and http://il2.php.net/reserved.variables
	// for $_SERVER['HTTP_HOST'] to work - have to add to apache's httpd.conf : ExtendedStatus On
	private static function requestUri()
	{
		if (isset($_SERVER['REQUEST_URI']))
		{
			$uri = $_SERVER['REQUEST_URI'];
		}
		else
		{
			if (isset($_SERVER['argv']))
			{
				$uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['argv'][0];
			}
			else
			{
				$uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
			}
		}
		return $uri;
	}
	
	
	public static function setSecureCookie ( $name , $value , $iv , $expiry)
	{
/* TODO - SECURITY - encrypt data !		
		$td = mcrypt_module_open ('tripledes', '', 'ecb', '');
		mcrypt_generic_init ($td, $key, $iv);
		$c_t = mcrypt_generic ($td, $value);
		mcrypt_module_close ($td);
		$token=base64_encode($c_t);
*/		
		$token = $value;
		
		// set the value of the cookie in the static map to be found if searched in current request
		self::$s_cookies_to_be_set[$name] = $value;
		setcookie( self::getHashedName($name) , $token , time() + $expiry , "/");
	}
	
	
	public static function  getSecureCookie ( $name , $iv  )
	{
		$raw_val = @self::$s_cookies_to_be_set[$name];
		if ( empty ( $raw_val ) )
		{
			$raw_val = @$_COOKIE[self::getHashedName($name)];
			if ( empty ( $raw_val ) )
				return NULL;
		}
		
		return $raw_val;
		
/* TODO - SECURITY - encrypt data !			
		$td = mcrypt_module_open ('tripledes', '', 'ecb', '');
		mcrypt_generic_init ($td, $key, $iv);
		$c_t = mdecrypt_generic ($td, $value);
		mcrypt_module_close ($td);
		$token=base64_decode($c_t);
			
		return $token;
*/
	}
	
	public static function getSecureCookieName ( $name )
	{
		 return self::getHashedName($name) ;
	}

	public static function removeAllSecureCookies ( )
	{
		$cookies = $_COOKIE;
		$name = null;
		foreach ( $cookies as $name => $value )
		{
			if ( kString::beginsWith( $name , self::SECURE_COOKIE_PREFIX ) )
			{
				self::removeSecureCookieByName ( $name );
			}
		}
		if ( $name )
		{
			setcookie( self::getHashedName($name) , "" , 0 , "/" );
		}		
	}
	
	public static function removeSecureCookie ( $name )
	{
		setcookie( self::getHashedName($name) , "" , 0 , "/" );		
	}
	
	public static function removeSecureCookieByName ( $real_name )
	{
		setcookie( $real_name , "" , 0 , "/" );		
	}
	
	private static function getHashedName( $name ) 
	{
		// TODO- security
		return self::SECURE_COOKIE_PREFIX . $name;
//		return self::SECURE_COOKIE_PREFIX . md5("bigbag$name);
	}
	
	public static function getRequestProtocol()
	{
		$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https" : "http";
		return $protocol;
	}
	
	public static function getRequestHost()
	{
		$protocol = self::getRequestProtocol();
		return "$protocol://".kConf::get("www_host");
	}
	
	public static function getRequestHostId()
	{
		$domainId = kConf::get("www_host");
		
		if ( $domainId == 'localhost')
			$domainId = 2;
		elseif ($domainId ==  'kaldev.kaltura.com')
			$domainId = 0;
		elseif ($domainId ==  'sandbox.kaltura.com')
			$domainId = 3;
		elseif ($domainId ==  'www.kaltura.com')
			$domainId = 1;
			
		return $domainId;
	}
	
	public static function getStreamingServerUrl()
	{
		$domain = self::getRequestHost();
		$protocol = self::getRequestProtocol();
				
		$rtmp_host = str_replace ( $protocol.':' , "rtmp:" , $domain );
		return "$rtmp_host/oflaDemo"; 
	}
	
	public static function handleConditionalGet()
	{
		// limelight sends conditional gets even after receiving errors on previous call so we cant assume they already have a good cached content
		/*
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
				while(FALSE !== ob_get_clean());
			header('HTTP/1.0 304 Not Modified');
			header("Cache-Control: public, max-age=604800");
			die;
		}
		*/
	}
		
	public static function getRemoteUserAgent()
	{
		if(isset($_SERVER['HTTP_USER_AGENT']))
			return $_SERVER['HTTP_USER_AGENT'];
			
		return null;
	}
	
	public static function validateIp( $required_ip , &$remote_addr)
	{
		$remote_addr = self::getRemoteAddress();
		$longIP = ip2long( $remote_addr );// to convert back, use long2ip
		return  ( $required_ip == $remote_addr || $required_ip == $longIP );
	}
	
	public static function getIpCountry ( )
	{
		$remote_addr = self::getRemoteAddress();
		$ip_geo = new myIPGeocoder();
		$country = $ip_geo->iptocountry( $remote_addr );
		return $country;
	}

	// $ip_country_list - string separated by ','.
	// the current ip should be one of the countries in the list for the ip to be vlaid
	public static function matchIpCountry ( $ip_country_list_str , &$current_country )
	{
		$ip_country_list = explode ( "," , $ip_country_list_str );
		$current_country = self::getIpCountry() ;
		return ( in_array ( $current_country , $ip_country_list ) );
	}
	
	//
	// allow access only via cdn or via proxy from secondary datacenter
	//
	public static function enforceCdnDelivery($partnerId)
	{
		$host = requestUtils::getHost();
		$cdnHost = myPartnerUtils::getCdnHost($partnerId);

		$dc = kDataCenterMgr::getCurrentDc();
		$external_url = $dc["external_url"];

		// allow access only via cdn or via proxy from secondary datacenter
		if ($host != $cdnHost && $host != $external_url)
		{
			$uri = $_SERVER["REQUEST_URI"];
			if (strpos($uri, "/forceproxy/true") === false)
				$uri .= "/forceproxy/true/";
			
			header('Location:'.$cdnHost.$uri);
			header("X-Kaltura:enforce-cdn");
			
			die;
		}
	}
	
	public static function getRequestParams()
	{
    	$scriptParts = explode('/', $_SERVER['SCRIPT_NAME']);
    	$pathParts = explode('/', $_SERVER['PHP_SELF']);
    	$pathParts = array_diff($pathParts, $scriptParts);
    	
    	$params = array();
    	reset($pathParts);
    	while(current($pathParts))
    	{
    		$key = each($pathParts);
    		$value = each($pathParts);
    		$params[$key['value']] = $value['value'];
    	}
    		
		return array_merge($params, $_GET, $_POST, $_FILES);
    }
	
}
