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


//$table = selectItem($db->listTables(),'TABLE'); 
//
foreach ($db->listTables() as $table) {

    $cols = array();
    foreach ($db->getMetadata($table) as $meta) {
        if (!isset($cols[$meta['column_name']])) {
            $cols[$meta['column_name']] = array();
        }
        $cols[$meta['column_name']]['name'] = $meta['column_name'];
        $cols[$meta['column_name']]['type'] = $meta['data_type'];
        $cols[$meta['column_name']]['maxlen'] = $meta['character_maximum_length'];
        $cols[$meta['column_name']]['is_nullable'] = $meta['is_nullable'];
        $cols[$meta['column_name']]['default'] = $meta['column_default'];
    }

    $form = "<form method=\"post\" class=\"well form-horizontal\">\n";


    foreach ($cols as $c) {

        if ('id' == $c['name']) {
            continue;
        }

        if ('created' == $c['name']) {
            continue;
        }

        if ('created_by' == $c['name']) {
            continue;
        }

        $display = Dase_Util::undirify($c['name']);
        $name = $c['name'];

        $form .= "<div class=\"control-group\">
            <label class=\"control-label\" for=\"input-$name\">$display</label>
            <div class=\"controls\">
            <input type=\"text\" class=\"span4\" name=\"$name\" id=\"input-$name\">
            </div>
            </div>\n";
    }

    $form .= "<div class=\"controls\"><input type=\"submit\" value=\"submit\" class=\"btn btn-primary\"></div>\n";
    $form .= "</form>\n";
    print $form;
    print "\n\n";

    $filename = $table.'-form.html';
    file_put_contents($filename,$form);
}
