<?php

class Dase_ModuleHandler_Admin extends Dase_Handler {

	public $resource_map = array(
		'/' => 'list',
		'index' => 'list',
		'cache' => 'cache',
		'log' => 'log',
	);

	public function setup($r)
	{
        $this->user = $r->getUser();
        if ($this->user->is_admin) {
            //ok
        } else {
            $r->renderError(401);
        }
	}

	public function getList($r) 
	{
		$r->renderTemplate('index.tpl');
	}

	public function deleteCache($r)
	{
		$num = $r->getCache()->expunge();
		$r->renderResponse('cache deleted '.$num.' files removed');
	}

	public function deleteLog($r)
	{
		if (@unlink(LOG_FILE)) {
			$r->renderResponse('log has been truncated');
		} else {
			$r->renderError(500);
		}
	}

	public function getLog($r)
	{
			$r->renderResponse('hello log');
	}

	public function getCache($r)
	{
			$r->renderResponse('hello cache');
	}
}
