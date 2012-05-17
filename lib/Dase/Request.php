<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Dase_Request 
{
	public static $types = array(
		'atom' =>'application/atom+xml',
		'cats' =>'application/atomcat+xml',
		'css' =>'text/css',
		'csv' =>'text/csv',
		'default' =>'text/html',
		'doc' =>'application/msword',
		'docx' =>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'gif' =>'image/gif',
		'html' =>'text/html',
		'jpg' =>'image/jpeg',
		'json' =>'application/json',
		'kml' =>'application/vnd.google-earth.kml+xml',
		'mov' =>'video/quicktime',
		'mp3' =>'audio/mpeg',
		'mp4' =>'video/mp4',
		'oga' => 'audio/ogg',
		'ogv' => 'video/ogg',
		'png' =>'image/png',
		'pdf' =>'application/pdf',
		'txt' =>'text/plain',
		'uris' =>'text/uri-list',
		'uri' =>'text/uri-list',
		'wav' =>'audio/wav',
		'xhtml' =>'application/xhtml+xml',
		'xml' =>'application/xml',
	);

	private $auth_config;
	private $cache;
	private $config;
	private $db;
	private $default_handler;
	private $eid_cookie;
	private $eid_is_serviceuser;
	private $eid_is_superuser;
	private $null_user;
	private $params;
	private $serviceusers = array();
    private $sf_request;
    private $sf_response;
	private $superusers = array();

    public $app_root;
    public $ext;
    public $format;
    public $handler;
    public $handler_path;
    public $log;
    public $method;
    public $mime;
    public $module;
    public $path;
    public $request_uri;
    public $template;
	public $user;
    public $user_is_null_user; //needed to allow Twig to NOT force login

	public function __construct()
	{
        $this->sf_request = Request::createFromGlobals();

        list ($this->path,$this->ext) = $this->getPathAndExtension();

        $this->request_uri = $this->sf_request->getRequestUri();
        $this->host = $this->sf_request->getHttpHost();
        $this->format = $this->sf_request->getRequestFormat($this->ext);
        $this->method = strtolower($this->sf_request->getMethod());
        $this->mime = $this->sf_request->getMimeType($this->format);
				$this->htuser = $this->sf_request->server->get('PHP_AUTH_USER');
				$this->htpass = $this->sf_request->server->get('PHP_AUTH_PW');
        $this->handler = $this->getHandler($this->path);
        $this->handler_path = $this->handler;
        $this->app_root =
            $this->sf_request->getScheme().'://'.$this->sf_request->getHttpHost().$this->sf_request->getBaseUrl();
        $this->module = $this->getModule();
		$this->module_root = $this->app_root.'/modules/'.$this->module;

        $this->log = Dase_Logger::instance(LOG_DIR,LOG_LEVEL);
	}

    public function getContentType()
    {
        $ct = $this->sf_request->headers->get('content_type');
        $parts = explode(';',$ct);
        $content_type = array_shift($parts);
        return $content_type;
    }

	public function init($db,$config,$template)
	{
		$this->template = $template;
		$this->db = $db;
		$this->config = $config;
        $this->checkForceHttps($config);
		$this->initDefaultHandler();
		$this->initUser();
		$this->initCache();
		$this->initCookie();
		$this->initAuth();
		$this->initPlugin();
		$this->logRequest();
	}

    //todo: needs work -- set by handler route var matches
    public function setParams($params) 
    {
        $this->params = $params; 
    }

    public function getAppSettings()
    {
        return $this->config->getAppSettings();
    }

    public function has($key)
    {
        if ($this->sf_request->request->has($key)) {
            return true;
        }
        if ($this->sf_request->query->has($key)) {
            return true;
        }
        if (isset($this->params[$key])) {
            return true;
        }
        return false;
    }

    public function hasFile($key)
    {
        if ($this->sf_request->files->has($key)) {
            return true;
        }
        return false;
    }

    public function getFile($key)
    {
        if ($this->sf_request->files->has($key)) {
            return $this->sf_request->files->get($key);
        }
    }

    //todo: work on returning arrays when appropriate
    public function get($key)
    {
        if ($this->sf_request->request->has($key)) {
            return trim($this->sf_request->request->filter($key));
        }
        if ($this->sf_request->query->has($key)) {
            return trim($this->sf_request->query->filter($key));
        }
        if (isset($this->params[$key])) {
            return trim($this->params[$key]);
        }
    }

    public function getBody()
    {
        return trim($this->sf_request->getContent());
    }

    public function __call( $method,$args ) 
    {
        return $this->sf_request->$method($args);
    }

    public function assign($key,$val)
    {
        $this->template->assign($key,$val);
    }

    public function checkForceHttps($config)
    {
        if ($config->getAppSettings('force_https')) {
            if (!$this->sf_request->isSecure()) {
                $secure_url = "https://" . $this->host . $this->request_uri;
                $this->renderRedirect($secure_url);
            }
        }
    }

	public function initDefaultHandler() 
	{
		$default_handler = $this->config->getAppSettings('default_handler');

        if (!$this->handler) {
            $this->renderRedirect($default_handler);
        } else {
            $this->default_handler = $default_handler;
        }
	}

    public function getPathAndExtension()
    {
        $pathinfo = $this->sf_request->getPathInfo();
        if (strpos($pathinfo,'.')) {
            $exploded = explode('.',$this->sf_request->getPathInfo());
            $ext = array_pop($exploded);
        } else {
            $ext = 'html'; 
        }
        $path = trim(str_replace('.'.$ext,'',$pathinfo),'/');
        return array($path,$ext);
    }

	public function initAuth()
	{
		$auth_config = $this->config->getAuth();
		$this->token = $auth_config['token'];
		$this->ppd_token = $auth_config['ppd_token'];
		$this->service_token = $auth_config['service_token'];
		$this->superusers = isset($auth_config['superuser']) ? $auth_config['superuser'] : array();
		$this->serviceusers = isset($auth_config['serviceuser']) ? $auth_config['serviceuser'] : array();
		$this->auth_config = $auth_config;
	}

	public function checkUrlAuth()
	{
		$url = $this->app_root.'/'.$this->path;
		$expires = $this->get('expires');
		$auth_token = $this->get('auth_token');

		if (!$expires || !$auth_token ) {
			return false;
		}
		if (time() > $expires) {
			return false;
		}
		if ($auth_token == md5($url.$expires.$this->token)) {
			return true;
		}
		return false;
	}

	public function getAuthConfig()
	{
		return $this->auth_config;
	}

	public function getSuperusers()
	{
		return $this->superusers;
	}

	public function getServiceusers()
	{
		return $this->serviceusers;
	}

	public function getAuthToken()
	{
		return $this->token;
	}

	public function initPlugin()
	{
		$custom_handlers = $this->config->getCustomHandlers();
		if ($this->module) { 
			return; 
		}
		$h = $this->handler;
		//simply reimplement any handler as a module
		if (isset($custom_handlers[$h])) {
			if(!file_exists(BASE_PATH.'/modules/'.$custom_handlers[$h])) {
				$this->renderError(404,'no such module');
			}
			$this->log->logInfo('**PLUGIN ACTIVATED**: handler:'.$h.' module:'.$custom_handlers[$h]);
			$this->setModule($custom_handlers[$h]);
		}
	}

	public function initModule($config)
	{
		if (!$this->module) {
			return;
		}
		//modules, by convention, have one handler in a file named
		$handler_file = BASE_PATH.'/modules/'.$this->module.'/handler.php';
		if (file_exists($handler_file)) {
			include "$handler_file";

			//module can set/override configurations
			$handler_config_file = BASE_PATH.'/modules/'.$this->module.'/inc/config.php';
			$config->load($handler_config_file);

			//modules can carry their own libraries
			$new_include_path = ini_get('include_path').':modules/'.$this->module.'/lib'; 
			ini_set('include_path',$new_include_path); 

			//would this allow module names w/ underscores???
			//$classname = 'Dase_ModuleHandler_'.Dase_Util::camelize($r->module);
			$classname = 'Dase_ModuleHandler_'.ucfirst($this->module);
		} else {
			$this->renderError(404,"no such handler: $handler_file");
		}
		return $classname;
	}

	public function getHandlerObject()
	{
        $classname = $this->initModule($this->config);
        if (!$classname) {
            $classname = 'Dase_Handler_'.Dase_Util::camelize($this->handler);
        }
        if (class_exists($classname,true)) {
            return new $classname($this->db,$this->config,$this->path);
        } else {
			$this->renderRedirect($this->default_handler);
        }
	}

	public function getElapsed()
	{
		$now = Dase_Util::getTime();
		return round($now - START_TIME,4);
	}	


	public function logRequest()
	{
        $data = array(
            'path' => $this->path,
            'method' => $this->method,
            'format' => $this->format,
            'mime' => $this->mime,
            'ext' => $this->ext,
            'handler' => $this->handler,
            'app_root' => $this->app_root,
            'module' => $this->module,
        );
		$this->log->logDebug('request',$data);
	}

	public function initCookie()
	{
		$token = $this->config->getAuth('token');
		$this->eid_cookie = new Dase_EidCookie($this->app_root,$this->module,$token);
	}

	public function setCookie($cookie_type,$value)
	{
		$this->eid_cookie->set($cookie_type,$value);
	}

	public function getCookie($cookie_type)
	{
		return $this->eid_cookie->get($cookie_type,$_COOKIE);
	}

	public function clearCookies()
	{
		$this->eid_cookie->clear();
	}

	public function initCache()
	{
		$this->cache = Dase_Cache::get($this->config);
	}

	public function getCache()
	{
		return $this->cache;
	}

	public function getCacheId()
	{
		//cache buster deals w/ aggressive browser caching.  Not to be used on server (so normalized).
		$query_string = preg_replace("!cache_buster=[0-9]*!i",'cache_buster=stripped',$this->query_string);
		//allows us to pass in a ttl 
		$query_string = preg_replace("!(&|\?)ttl=[0-9]*!i",'',$query_string);
		$this->log->logDebug('cache id is '. $this->method.'|'.$this->path.'|'.$this->format.'|'.$query_string);
		return $this->method.'|'.$this->path.'|'.$this->format.'|'.$query_string;
	}

	public function checkCache($ttl=null)
	{
		//so you can pass in 'ttl' query param
		if ($this->get('ttl')) {
			$ttl = $this->get('ttl');
		}
		$content = $this->cache->getData($this->getCacheId(),$ttl);
		if ($content) {
			$this->renderResponse($content,false);
		}
	}

	public function setModule($module) 
	{
		$this->module = $module;
		$this->module_root = $this->app_root.'/modules/'.$this->module;
	}

	public function getModule()
	{
		$parts = explode('/',trim($this->path,'/'));
		$first = array_shift($parts);
		if ('modules' == $first) {
			if(!isset($parts[0])) {
				$this->renderError(404,'no module specified');
			}
			if(!file_exists(BASE_PATH.'/modules/'.$parts[0])) {
				$this->renderError(404,'no such module');
			}
			return $parts[0];
		} else {
			return '';
		}
	}

	public function getHandler($path)
	{
		$parts = explode('/',trim($path,'/'));
		$first = array_shift($parts);
		if ('modules' == $first && isset($parts[0])) {
			//so dispatch matching works
			return 'modules/'.$parts[0];
		} else {
			return $first;
		}
	}

	public function initUser()
	{
		$this->null_user = Dase_User::get($this->db,$this->config);

        //so Twig template does not force login
		$this->user = Dase_User::get($this->db,$this->config);
        $this->user_is_null_user = 1;
	}

	public function setUser($user)
	{
		$this->user = $user;
	}

	public function getUser($auth='cookie',$force_login=true)
	{
		if ($this->user && !$this->user_is_null_user) {
			return $this->user;
		}

		//allow auth type to be forced w/ query param
		if ($this->sf_request->query->has('auth')) {
			$auth = $this->sf_request->query->get('auth');
		}

		switch ($auth) {
		case 'cookie':
			$eid = $this->eid_cookie->getEid($_COOKIE);
			break;
		case 'http':
			$eid = $this->_authenticate();
			break;
		case 'service':
			$eid = $this->_authenticate(true);
			break;
		case 'none':
			//returns a null user
			return $this->null_user;
		default:
			$eid = $this->eid_cookie->getEid($this->_cookie);
		}

		//eids are always lowercase
		$eid = strtolower($eid);

		if ($eid) {
			$u = clone $this->null_user;
			$this->user = $u->retrieveByEid($eid);
            $this->user_is_null_user = 0;
		}

		if ($eid && $this->user) {
			if (isset($this->serviceusers[$eid])) {
				$this->user->is_serviceuser = true;
			}
			if (isset($this->superusers[$eid])) {
				$this->user->is_superuser = true;
			}
			//set http password
			$this->user->setHttpPassword($this->token);
			return $this->user;
		} else {
			if (!$force_login) { return false; }
			if ('html' == $this->format) {
				$params['target'] = $this->sf_request->getUri();
				$this->renderRedirect('login/form',$params);
			} else {
				//last chance, check url auth but it 
				//ONLY works to override cookie auth
				if ('cookie' == $auth && $this->checkUrlAuth()) {
					return $this->null_user;
				}
				$this->renderError(401,'unauthorized');
			}
		}
	}

	/** this function authenticates Basic HTTP and returns EID */

	private function _authenticate($check_db=false)
	{
		$request_headers = apache_request_headers();
		$passwords = array();

		if ($this->htuser && $this->htpass) {
			$eid = $this->htuser;
			$this->log->logDebug('adding password '.substr(md5($this->token.$eid.'httpbasic'),0,12));
			$this->log->logDebug('token is '.$this->token);
			$passwords[] = substr(md5($this->token.$eid.'httpbasic'),0,12);

			//for service users:
			//if eid is among service users, get password w/ service_token as salt
			if (isset($this->serviceusers[$eid])) {
				$this->log->logDebug('serviceuser request from '.$eid);
				$passwords[] = md5($this->service_token.$eid);
			}

			//lets me use the superuser passwd for http work
			if (isset($this->superusers[$eid])) {
				$passwords[] = $this->superusers[$eid];
			}

			//this is used for folks needing a quick service pwd to do uploads
			if ($check_db) {
				$u = clone $this->null_user;
				if ($u->retrieveByEid($eid)) {
					$pass_md5 = md5($this->htpass);
					if ($pass_md5 == $u->service_key_md5) {
						$this->log->logDebug('accepted user '.$eid.' using password '.$this->htpass);
						return $eid;
					}
				}
			}

			if (in_array($this->htpass,$passwords)) {
				$this->log->logDebug('accepted user '.$eid.' using password '.$this->htpass);
				return $eid;
			} else {
				$this->log->logDebug('rejected user '.$eid.' using password '.$this->htpass);
			}
		} else {
			$this->log->logDebug('PHP_AUTH_USER and/or PHP_AUTH_PW not set');
		}
		header('WWW-Authenticate: Basic realm="DASe"');
		header('HTTP/1.1 401 Unauthorized');
		echo "sorry, authorized users only";
		exit;
	}

	public function renderResponse($content,$set_cache=true,$status_code=null)
	{
		$response = new Response($content);
        $response->headers->set('Content-Type',$this->mime);
		if ('get' != $this->method) {
			$set_cache = false;
		}
        $response->send();
		exit;
	}

	public function renderTemplate($path)
	{
        $content = $this->template->fetch($path);
		$response = new Response($content);
        $response->headers->set('Content-Type',$this->mime);
		if ('get' != $this->method) {
			$set_cache = false;
		}
        $response->send();
		exit;
	}

	public function renderOk($msg='ok')
	{
		$response = new Response();
        $response->setStatusCode(200);
        $response->setContent($msg);
        $response->headers->set('Content-Type','text/plain');
		$response->send();
		exit;
	}

	public function serveFile($path,$mime_type,$download=false)
	{
        //unclear if Symfony Resp Obj supports this
		if (!file_exists($path)) {
            $this->renderError(404);
			header('Content-Type: image/jpeg');
			readfile(BASE_PATH.'/www/images/unavail.jpg');
			exit;
		}
		$filename = basename($path);
		//from php.net
		$headers = apache_request_headers();
		// Checking if the client is validating its cache and if it is current.
		if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($path))) {
			// Client's cache IS current, so we just respond '304 Not Modified'.
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($path)).' GMT', true, 304);
		} else {
			// Image not cached or cache outdated, we respond '200 OK' and output the image.
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($path)).' GMT', true, 200);
			header('Content-Length: '.filesize($path));
			header('Content-Type: '.$mime_type);

			//hack to deal w/ iPad that only wants byte ranges
			if ('video/mp4' == $mime_type) {
				Dase_Util::rangeDownload($path);
				exit;
			}

			if ('audio/mpeg' == $mime_type) {
				Dase_Util::rangeDownload($path);
				exit;
			}

			if ($download) {
				header("Content-Disposition: attachment; filename=$filename");
				//from http://us.php.net/fread
				$total     = filesize($path);
				$blocksize = (2 << 20); //2M chunks
				$sent      = 0;
				$handle    = fopen($path, "r");
				// Now we need to loop through the file and echo out chunks of file data
				while($sent < $total){
					echo fread($handle, $blocksize);
					$sent += $blocksize;
				}
			} else {
				header("Content-Disposition: inline; filename=$filename");
				//print file_get_contents($path);
				Dase_Util::readfileChunked($path);
			}
		}
		exit;
	}

	public function renderRedirect($path='',$params=null)
	{
        $query_array = array();
        if (isset($params) && is_array($params)) {
            foreach ($params as $key => $val) {
                $query_array[] = urlencode($key).'='.urlencode($val);
            }
        }
        if ('http' != substr($path,0,4)) {
            $redirect_path = trim($this->app_root,'/') . "/" . trim($path,'/');
        } else {
            $redirect_path = $path;
        }
        if (count($query_array)) {
            //since path is allowed to have some query params already
            if (false !== strpos($path,'?')) {
                $redirect_path .= '&'.join("&",$query_array);
            } else {
                $redirect_path .= '?'.join("&",$query_array);
            }
        }
        $response = new RedirectResponse($redirect_path);
        $response->send();
        exit;
	}

	public function renderError($code,$msg='',$log_error=true)
	{
		$response = new Response();
        $response->setStatusCode($code);
        if (!$msg) {
            $msg = Response::$statusTexts[$code];
        }
        $response->setContent($msg);
        $response->headers->set('Content-Type','text/plain');
		$response->send();
		exit;
	}
}

