CREATE TABLE tx_formconsent_domain_model_consent(
		email varchar(255) DEFAULT '' NOT NULL,
		date int(11) DEFAULT '0' NOT NULL,
		data BLOB,
		form_persistence_identifier text,
		approved tinyint(4) DEFAULT '0' NOT NULL,
		approval_date int(11) DEFAULT '0' NOT NULL,
		valid_until int(11),
		validation_hash varchar(255) DEFAULT '' NOT NULL
);
