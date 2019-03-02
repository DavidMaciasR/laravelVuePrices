CREATE TABLE `temp` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sku` varchar(20),
  `account_ref` varchar(20),
  `user_ref` varchar(20),
  `quantity` int(10) unsigned DEFAULT NULL,
  `value` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `product_id` int(10) unsigned DEFAULT NULL,
  `account_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,  
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOAD DATA LOCAL INFILE 'C:\\Users\\david\\Documents\\Trabajos 02\\CodeTests\\PHPRemote\\dev-test\\import.csv' INTO TABLE `temp` FIELDS TERMINATED BY ',' IGNORE 1 LINES (sku, account_ref, user_ref, quantity, value, created_at, updated_at, deleted_at);

update temp t set t.product_id=(select p.id from temp te inner join products p on te.sku=p.sku);

update temp t inner join accounts a on t.account_ref=a.external_reference set t.account_id=a.id;

update temp t inner join users u on t.user_ref=u.external_reference set t.user_id=u.id;

insert into prices (select t.id, t.product_id, t.account_id, t.user_id, t.quantity, t.value, null, null, null from temp t);

drop table temp;
