<?php

include 'config.php';


$sql = "
    CREATE TABLE IF NOT EXISTS `attribute` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
        `ascii_id` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
        `created` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `created_by` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
";

$dbh = $db->getDbh();
$sth = $dbh->prepare($sql);
if ($sth->execute()) {
    print "created attribute table\n";
}

$sql = "
    CREATE TABLE IF NOT EXISTS `item` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
        `title` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
        `body` text COLLATE utf8_unicode_ci,
        `url` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
        `file_url` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
        `thumbnail_url` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
        `view_url` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
        `filesize` int(11) DEFAULT NULL,
        `mime` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
        `width` int(11) DEFAULT NULL,
        `height` int(11) DEFAULT NULL,
        `lat` float DEFAULT NULL,
        `lng` float DEFAULT NULL,
        `created` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `created_by` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        `updated` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `updated_by` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
";

$dbh = $db->getDbh();
$sth = $dbh->prepare($sql);
if ($sth->execute()) {
    print "created item table\n";
}

$sql = "
    CREATE TABLE IF NOT EXISTS `user` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `eid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
        `name` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
        `email` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
        `is_admin` tinyint(1) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
";

$dbh = $db->getDbh();
$sth = $dbh->prepare($sql);
if ($sth->execute()) {
    print "created user table\n";
}

$sql = "
    CREATE TABLE IF NOT EXISTS `value` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `attribute_id` int(11) NOT NULL,
        `item_id` int(11) NOT NULL,
        `text` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
";

$dbh = $db->getDbh();
$sth = $dbh->prepare($sql);
if ($sth->execute()) {
    print "created value table\n";
}

