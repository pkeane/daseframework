<?php

class Dase_Handler_Home extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'home',
	);

	protected function setup($r)
	{
		$this->user = $r->getUser();
	}

	public function getHome($r) 
	{
        $item = Dase_DBO_Item::getBySernum($this->db,'home');
        if ($item) {
            $r->assign('notes',$item->body);
        }
		$r->renderTemplate('home.tpl');
	}

}

