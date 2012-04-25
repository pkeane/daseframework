<?php


class Dase_Handler_Directory extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'search_form',
	);

	protected function setup($r)
	{
		$this->user = $r->getUser();
	}

}

