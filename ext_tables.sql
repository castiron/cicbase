CREATE TABLE tx_cicbase_cache (
  id int(11) NOT NULL auto_increment,
  identifier varchar(128) NOT NULL DEFAULT '',
  crdate int(11) unsigned NOT NULL DEFAULT '0',
  content mediumtext,
  lifetime int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY cache_id (`identifier`)
);

CREATE TABLE tx_cicbase_cache_tags (
  id int(11) NOT NULL auto_increment,
  identifier varchar(128) NOT NULL DEFAULT '',
  tag varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  KEY cache_id (`identifier`),
  KEY cache_tag (`tag`)
);

CREATE TABLE tx_cicbase_zipcodes (
  zipcode INT NOT NULL PRIMARY KEY,
  latitude FLOAT(12,8),
  longitude FLOAT(12,8),
  state VARCHAR(2),
  city VARCHAR(128),
  county VARCHAR(128)
);

CREATE TABLE tx_cicbase_domain_model_file (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,

  filename varchar(255) DEFAULT '' NOT NULL,
  original_filename varchar(255) DEFAULT '' NOT NULL,
  path varchar(255) DEFAULT '' NOT NULL,
  mime_type varchar(255) DEFAULT '' NOT NULL,
  size int(11) unsigned DEFAULT '0' NOT NULL,
  title varchar(255) DEFAULT '' NOT NULL,
  description text NOT NULL,

  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  starttime int(11) unsigned DEFAULT '0' NOT NULL,
  endtime int(11) unsigned DEFAULT '0' NOT NULL,


  t3ver_oid int(11) DEFAULT '0' NOT NULL,
  t3ver_id int(11) DEFAULT '0' NOT NULL,
  t3ver_wsid int(11) DEFAULT '0' NOT NULL,
  t3ver_label varchar(255) DEFAULT '' NOT NULL,
  t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
  t3ver_stage int(11) DEFAULT '0' NOT NULL,
  t3ver_count int(11) DEFAULT '0' NOT NULL,
  t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
  t3ver_move_id int(11) DEFAULT '0' NOT NULL,

  t3_origuid int(11) DEFAULT '0' NOT NULL,
  sys_language_uid int(11) DEFAULT '0' NOT NULL,
  l10n_parent int(11) DEFAULT '0' NOT NULL,
  l10n_diffsource mediumblob,

  PRIMARY KEY (uid),
  KEY parent (pid),
  KEY t3ver_oid (t3ver_oid,t3ver_wsid),
  KEY language (l10n_parent,sys_language_uid)

);