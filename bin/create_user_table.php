<?php

include 'config.php';

$sql = "
CREATE TABLE `user` (
	`id` int(11) NOT NULL auto_increment,
	`eid` varchar(40) collate utf8_unicode_ci NOT NULL,
	`name` varchar(40) collate utf8_unicode_ci default NULL,
	`email` varchar(200) collate utf8_unicode_ci default NULL,
	`is_admin` tinyint(1) default NULL,
	PRIMARY KEY  (`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
";

$dbh = $db->getDbh();
$sth = $dbh->prepare($sql);
print_r($sth->execute());
