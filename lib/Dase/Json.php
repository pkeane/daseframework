<?php

class Dase_Json 
{ 
	public static function get($data,$format=false)
	{
		if ($format) {
			return self::json_format(json_encode($data));
		}
		return json_encode($data);
	}

	public static function toPhp($json,$assoc=true)
	{
		return json_decode($json,$assoc);
	}

	// from http://php.net/manual/en/function.json-encode.php
	public static function json_format($json)
	{
		$tab = "  ";
		$new_json = "";
		$indent_level = 0;
		$in_string = false;

		$json_obj = json_decode($json);

		if($json_obj === false)
			return false;

		$json = json_encode($json_obj);
		$len = strlen($json);

		for($c = 0; $c < $len; $c++) {
			$char = $json[$c];
			switch($char) {
			case '{':
			case '[':
				if(!$in_string) {
					$new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
					$indent_level++;
				} else {
					$new_json .= $char;
				}
				break;
			case '}':
			case ']':
				if(!$in_string) {
					$indent_level--;
					$new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
				} else {
					$new_json .= $char;
				}
				break;
			case ',':
				if(!$in_string) {
					$new_json .= ",\n" . str_repeat($tab, $indent_level);
				} else {
					$new_json .= $char;
				}
				break;
			case ':':
				if(!$in_string) {
					$new_json .= ": ";
				} else {
					$new_json .= $char;
				}
				break;
			case '"':
				if($c > 0 && $json[$c-1] != '\\') {
					$in_string = !$in_string;
				}
			default:
				$new_json .= $char;
				break;                   
			}
		}
		return $new_json;
	}
}

