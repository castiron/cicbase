CREATE TABLE tx_cicservices_cache (
   id int(11) NOT NULL auto_increment,
   identifier varchar(128) NOT NULL DEFAULT '',
   crdate int(11) unsigned NOT NULL DEFAULT '0',
   content mediumtext,
   lifetime int(11) unsigned NOT NULL DEFAULT '0',
   PRIMARY KEY (id),
   KEY cache_id (`identifier`)
);

CREATE TABLE tx_cicservices_cache_tags (
   id int(11) NOT NULL auto_increment,
   identifier varchar(128) NOT NULL DEFAULT '',
   tag varchar(128) NOT NULL DEFAULT '',
   PRIMARY KEY (id),
   KEY cache_id (`identifier`),
   KEY cache_tag (`tag`)
);

CREATE TABLE tx_cicservices_zipcodes (
	zipcode INT NOT NULL PRIMARY KEY,
	latitude FLOAT(12,8),
	longitude FLOAT(12,8),
	state VARCHAR(2),
	city VARCHAR(128),
	county VARCHAR(128)
);

