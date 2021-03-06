<?php

require_once 'Dase/DBO/Autogen/Attribute.php';

class Dase_DBO_Attribute extends Dase_DBO_Autogen_Attribute 
{
    public $values = array();

    public static function findUniqueAsciiId($db,$name,$iter=0)
    {
        $ascii_id = Dase_Util::dirify($name);
        if ($iter) {
            $checkname = $ascii_id.'_'.$iter;
        } else {
            $checkname = $ascii_id;
        }
        $att = new Dase_DBO_Attribute($db);
        $att->ascii_id = $checkname;
        if (!$att->findOne()) {
            return $checkname;
        } else {
            $iter++;
            return Dase_DBO_Attribute::findUniqueAsciiId($db,$name,$iter);
        }
    }

    public static function findOrCreate($r,$db,$att_ascii) 
    {
        $att = new Dase_DBO_Attribute($db);
        $att->ascii_id = $att_ascii;
        if ($att->findOne()) {
            return $att;
        } else {
            $att->name = Dase_Util::undirify($att->ascii_id);;
            $att->input_type = 'text';
            $att->created = date(DATE_ATOM);
            $att->created_by = $r->getUser()->eid;
            $att->insert();
            return $att;
        }
    }

}
