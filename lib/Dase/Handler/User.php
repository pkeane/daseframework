<?php

class Dase_Handler_User extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'user',
		'settings' => 'settings',
		'email' => 'email'
	);

	protected function setup($r)
	{
		$this->user = $r->getUser();
	}

	public function getSettings($r) 
	{
		$t = new Dase_Template($r);
		$r->renderResponse($t->fetch('framework/user_settings.tpl'));
	}

	public function getUser($r) 
	{
		$t = new Dase_Template($r);
		$r->renderResponse($t->fetch('framework/home.tpl'));
	}

	public function postToEmail($r)
	{
		$this->user->email = $r->get('email');
		$this->user->update();
		$r->renderRedirect('user/settings');
	}
}

