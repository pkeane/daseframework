<?php

class Dase_Handler_Home extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'home',
		'{id}' => 'thing',
	);

	protected function setup($r)
	{
		$this->user = $r->getUser();
		//orm
		//$r->set('footer',Dase_DBO_Itemset::getByName($this->db,$r,'footer'));
		//http
		//$r->set('footer',Dase_DBO_Itemset::get($r,'footer'));
	}

	public function getHome($r) 
	{
		//$t->assign('lab_thumbs',Dase_DBO_Itemset::getByName($this->db,$r,'lab_thumbs'));
		$r->renderTemplate('home.tpl');
	}

	public function postToHome($r) 
	{
		$user = $r->getUser();
		//do stuff
		$r->renderRedirect('home');
	}

	public function getThing($r) 
	{
		$r->renderResponse($r->get('id'));
	}
}

