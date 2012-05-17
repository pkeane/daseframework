<?php

class Dase_Handler_Exception extends Exception {
}

/** this class is always subclassed by a request-specific handler */

class Dase_Handler {

	protected $db;
	protected $config;
	protected $request;
    protected $path;
    protected $template;

	public function __construct($db,$config)
	{
		$this->db = $db;
		//config is passed to handler for local config settings
		$this->config = $config;
	}

	public function dispatch($r)
	{
		//if it is a module subclass, append the module resource map
		if (isset($this->module_resource_map)) {
			$this->resource_map = array_merge($this->resource_map,$this->module_resource_map);
		}

		foreach ($this->resource_map as $uri_template => $resource) {
			//first, translate resource map uri template to a regex
			$uri_template = trim($r->handler_path.'/'.$uri_template,'/');
			$uri_regex = $uri_template;

			//skip regex template stuff if uri_template is a plain string
			if (false !== strpos($uri_template,'{')) {
				//stash param names into $template_matches
				$num = preg_match_all("/{([\w]*)}/",$uri_template,$template_matches);
				if ($num) {
					$uri_regex = preg_replace("/{[\w]*}/","([\w-,.]*)",$uri_template);
				}
			}

			//second, see if uri_regex matches the request uri (a.k.a. path)
			if (preg_match("!^$uri_regex\$!",$r->path,$uri_matches)) {
				$r->log->logDebug("matched resource $resource");
				//create parameters based on uri template and request matches
				if (isset($template_matches[1]) && isset($uri_matches[1])) { 
					array_shift($uri_matches);
					$params = array_combine($template_matches[1],$uri_matches);
					$r->setParams($params);
				}
				$method = $this->determineMethod($resource,$r);
				$r->log->logDebug("try method $method");
				if (method_exists($this,$method)) {
					$r->resource = $resource;
					$this->setup($r);
					$this->{$method}($r); //should exit
					$r->renderError(501,'empty method '.$method);
				} else {
					$r->renderError(404,'no handler method');
				}
			}
		}
		$r->renderError(404,'no such resource');
	}

	protected function determineMethod($resource,$r)
	{
		if ('post' == $r->method) {
			$method = 'postTo';
		} else {
			$method = $r->method;
		}
		if (('html'==$r->format) || ('get' != $method)) {
			$format = '';
		} else {
			$format = ucfirst($r->format);
		}
		//camel case
		$resource = Dase_Util::camelize($resource);

		$handler_method = $method.$resource.$format;
		return $handler_method;
	}

	protected function setup($r)
	{
	}

	public function initTemplate($t)
	{
	}
			
}
