<?php
include 'config.php';


function selectItem($items,$name) {
		print "SELECT ONE $name:\n";
		$i = 0;
		foreach ($items as $item) {
				$i++;
				print "\t$i. $item\n";
		}
		$max = $i+1;
		print "\t$max. exit program\n\n";
		system('stty -echo');
		$num = trim(fgets(STDIN));
		system('stty echo');

		if ($num == $max) {
				print "\n\n";
				exit;
		}

		if (isset($items[$num-1])) {
				return $items[$num-1];
		} else {
				print "\n";
				print "\033[41;37m INVALID CHOICE \033[40;37m\r\n";
				print "\n";
				return selectItem($items,$name);
		}
}


$table = selectItem($db->listTables(),'TABLE'); 


		$cols = array();
		foreach ($db->getMetadata($table) as $meta) {
				if (!isset($cols[$meta['column_name']])) {
						$cols[$meta['column_name']] = array();
				}
				$cols[$meta['column_name']]['name'] = $meta['column_name'];
		}

		$out = "<table><tbody>\n";


		foreach ($cols as $c) {

				if ('id' == $c['name']) {
						continue;
				}

				$out .= "<tr>";
				$name = $c['name'];

				$out .= '<th scope="row">'.Dase_Util::undirify($name)."</th>";
				$out .= '<td>{$project->'.$name.'}</td></tr>'."\n";

		}

		$out .= "</tbody></table>\n";
		print $out;
		print "\n\n";
