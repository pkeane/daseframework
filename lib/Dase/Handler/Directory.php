<?php

function sortByName($a,$b)
{
	$a_str = strtolower($a['name']);
	$b_str = strtolower($b['name']);
	if ($a_str == $b_str) {
		return 0;
	}
	return ($a_str < $b_str) ? -1 : 1;
}


class Dase_Handler_Directory extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'search_form',
	);

	protected function setup($r)
	{
		$this->user = $r->getUser();
	}

	public function getSearchForm($r) 
	{
		$t = new Dase_Template($r);
		if ($r->get('lastname')) {
			$results = Utlookup::lookup($r->get('lastname'),'sn');
			usort($results,'sortByName');
			$t->assign('lastname',$r->get('lastname'));
			$t->assign('results',$results);
		}
		$r->renderResponse($t->fetch('framework/directory.tpl'));
	}
}

