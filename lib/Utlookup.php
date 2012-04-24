<?php

class Utlookup 
{
	public static function lookup($query,$type) {
		$person_array = array();
		$x500 = ldap_connect('ldap.utexas.edu');
		$bind = ldap_bind($x500);
		$dn = "ou=people,dc=directory,dc=utexas,dc=edu";
		$filter = "$type=$query";
		$ldap_result = @ldap_search($x500,$dn,$filter);
		$attributes = array(
			'eid' => 'uid',
			'email' => 'mail',
			'name' => 'cn',
			'firstname' => 'givenname',
			'lastname' => 'sn',
			'office' => 'utexasedupersonofficelocation',
			'phone' => 'telephonenumber',
			'title' => 'title',
			'unit' => 'ou'
		);
		/*
		$attributes = array(
			'email' => 'mail',
			'name' => 'cn',
			'office' => 'utexasedupersonofficelocation',
			'phone' => 'telephonenumber',
			'unit' => 'ou'
		);
		 */
		if ($ldap_result) {
			$entry_array = ldap_get_entries($x500, $ldap_result);
			for ($i=0; $i < count($entry_array) - 1;$i++) {
				$person = array();
				if ($entry_array[$i]) {
					$eid = $entry_array[$i]['uid'][0];
					foreach ($attributes as $label => $att) {
						if (isset($entry_array[$i][$att])) {
							$person[$label] = $entry_array[$i][$att][0];
						} else {
							$person[$label] = '';
						}
					}
				}
				$person_array[] = $person;
			}
			ldap_close($x500);
		}
		return $person_array;
	}

	public static function getRecord($eid) {
		$person_array = Utlookup::lookup($eid,'uid');
		if (count($person_array)) {
			return $person_array[0];
		}
		return false;
	}
}
