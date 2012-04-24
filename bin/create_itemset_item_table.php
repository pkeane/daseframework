<?php

include 'config.php';

$sql = "
CREATE TABLE IF NOT EXISTS `itemset_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `itemset_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `created` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `sort_order` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
";

$dbh = $db->getDbh();
$sth = $dbh->prepare($sql);
print_r($sth->execute());
