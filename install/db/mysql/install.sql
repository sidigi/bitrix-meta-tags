create table if not exists shantilab_metatags_keys
(
	ID int(18) not null auto_increment,
	TIMESTAMP_X datetime,
	MODIFIED_BY int(18),
	DATE_CREATE datetime,
	CREATED_BY int(18),
	ACTIVE char(1) not null default 'Y',
	ACTIVE_FROM datetime,
	ACTIVE_TO datetime,
	SORT int(18) not null default '500',
	CODE varchar(255) not null,
	NAME varchar(255) default null,
	VALUE varchar(255) default null,
	C_SITE varchar(255) default null,
	C_TEMPLATE varchar(255) default null,
	C_REQUEST varchar(255) default null,
	C_PHP varchar(255) default null,
	primary KEY (ID),
	UNIQUE KEY (CODE),
	index ix_shanti_key_1 (CODE)
) ENGINE=InnoDB;

create table if not exists shantilab_metatags_keys_pages
(
	ID int(18) not null auto_increment,
	KEY_ID int(18) not null,
	PAGE varchar(255) not null,
	SHOW_ON_PAGE char(1) not null default 'Y',
	primary key (ID),
	FOREIGN KEY (KEY_ID) REFERENCES shantilab_metatags_keys(ID) ON DELETE CASCADE
) ENGINE=InnoDB;

create table if not exists shantilab_metatags_rules
(
	ID int(18) not null auto_increment,
	TIMESTAMP_X datetime,
	MODIFIED_BY int(18),
	DATE_CREATE datetime,
	CREATED_BY int(18),
	ACTIVE char(1) not null default 'Y',
	ACTIVE_FROM datetime,
	ACTIVE_TO datetime,
	SORT int(18) not null default '500',
	NAME varchar(255) not null,
	C_ALL_KEYS char(1) default null,
	PRIORITY char(1) default null,
	C_REQUIRED_KEYS varchar(255) default null,
	C_SITE varchar(255) default null,
	C_TEMPLATE varchar(255) default null,
	C_REQUEST varchar(255) default null,
	C_PHP varchar(255) default null,
	SETTED_KEYS varchar(255) default null,
	primary KEY (ID)
) ENGINE=InnoDB;

create table if not exists shantilab_metatags_rules_pages
(
	ID int(18) not null auto_increment,
	RULE_ID int(18) not null,
	PAGE varchar(255) not null,
	SHOW_ON_PAGE char(1) not null default 'Y',
	primary key (ID),
	FOREIGN KEY (RULE_ID) REFERENCES shantilab_metatags_rules(ID) ON DELETE CASCADE
) ENGINE=InnoDB;

create table if not exists shantilab_metatags_rules_templates
(
	ID int(18) not null auto_increment,
	RULE_ID int(18) not null,
	CODE varchar(255) not null,
	VALUE TEXT NOT NULL,
	PAGEN_ON char(1) not null default 'Y',
	PAGEN varchar(255) default null,
	primary key (ID),
	FOREIGN KEY (RULE_ID) REFERENCES shantilab_metatags_rules(ID) ON DELETE CASCADE
) ENGINE=InnoDB;