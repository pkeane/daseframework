<?php

require_once 'Dase/DBO.php';

/*
 * DO NOT EDIT THIS FILE
 * it is auto-generated by the
 * script 'bin/class_gen.php
 * 
 */

class Dase_DBO_Autogen_Value extends Dase_DBO 
{
	public function __construct($db,$assoc = false) 
	{
		parent::__construct($db,'value', array('attribute_id','item_id','text'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->fields[$key] = $value;
			}
		}
	}
    public function getAttribute_id() { return $this->fields["attribute_id"]; }
    public function getItem_id() { return $this->fields["item_id"]; }
    public function getText() { return $this->fields["text"]; }
}