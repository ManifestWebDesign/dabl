CREATE TABLE `address`
(
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`street1` VARCHAR(255),
	`street2` VARCHAR(255),
	`city` VARCHAR(255),
	`state` VARCHAR(2),
	`zip` VARCHAR(5),
	`created` INTEGER UNSIGNED COMMENT 'integer_ts',
	`updated` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`id`)
);

CREATE TABLE `assignment`
(
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`job_id` INTEGER UNSIGNED NOT NULL,
	`contract_id` INTEGER UNSIGNED NOT NULL,
	`status` INTEGER UNSIGNED DEFAULT 1 NOT NULL,
	`due` INTEGER UNSIGNED COMMENT 'integer_ts',
	`submitted` INTEGER UNSIGNED COMMENT 'integer_ts',
	`created` INTEGER UNSIGNED COMMENT 'integer_ts',
	`updated` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`id`)
);

CREATE INDEX `assignment_status_idx` ON `assignment` (`status`);

CREATE TABLE `backflow_assembly`
(
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`water_site_id` INTEGER UNSIGNED NOT NULL,
	`backflow_assembly_model_id` INTEGER UNSIGNED,
	`backflow_assembly_location_id` INTEGER UNSIGNED,
	`size` VARCHAR(255),
	`serial_number` VARCHAR(255),
	`backflow_assembly_type` INTEGER UNSIGNED NOT NULL,
	`status` INTEGER UNSIGNED DEFAULT 1 NOT NULL,
	`feeds` VARCHAR(255),
	`created` INTEGER UNSIGNED COMMENT 'integer_ts',
	`updated` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`id`)
);

CREATE TABLE `backflow_assembly_location`
(
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255),
	`created` INTEGER UNSIGNED COMMENT 'integer_ts',
	`updated` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`id`)
);

CREATE TABLE `backflow_assembly_make`
(
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`created` INTEGER UNSIGNED COMMENT 'integer_ts',
	`updated` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`id`)
);

CREATE UNIQUE INDEX `backflow_assembly_make_name_key` ON `backflow_assembly_make` (`name`);

CREATE TABLE `backflow_assembly_model`
(
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`backflow_assembly_make_id` INTEGER UNSIGNED NOT NULL,
	`created` INTEGER UNSIGNED COMMENT 'integer_ts',
	`updated` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`id`)
);

CREATE UNIQUE INDEX `backflow_assembly_model_name_bac` ON `backflow_assembly_model` (`name`,`backflow_assembly_make_id`);

CREATE TABLE `backflow_plan`
(
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255),
	`created` INTEGER UNSIGNED COMMENT 'integer_ts',
	`updated` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`id`)
);

CREATE TABLE `backflow_test`
(
	`job_id` INTEGER UNSIGNED NOT NULL,
	`backflow_assembly_id` INTEGER UNSIGNED NOT NULL,
	`backflow_replacement_assembly_id` INTEGER UNSIGNED NOT NULL,
	`installation_status` INTEGER UNSIGNED,
	`meter_reading` VARCHAR(255),
	`pressure_drop` VARCHAR(255),
	`opened_at` VARCHAR(255),
	`check1` VARCHAR(255),
	`check1_status` INTEGER UNSIGNED,
	`check2` VARCHAR(255),
	`check2_status` INTEGER UNSIGNED,
	`passed` BOOLEAN,
	`repairs` BOOLEAN,
	`approved` BOOLEAN,
	`service_restored` BOOLEAN,
	PRIMARY KEY (`job_id`)
);

CREATE TABLE `backflow_tester`
(
	`profile_id` INTEGER UNSIGNED NOT NULL,
	`gauge_number` VARCHAR(255),
	`gauge_calibrated` DATE,
	`certification_number` VARCHAR(255),
	`created` INTEGER UNSIGNED COMMENT 'integer_ts',
	`updated` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`profile_id`)
);

CREATE TABLE `contract`
(
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`customer_profile_id` INTEGER UNSIGNED NOT NULL,
	`vendor_profile_id` INTEGER UNSIGNED NOT NULL,
	`customer_status` INT2,
	`vendor_status` INT2,
	`created` INTEGER UNSIGNED COMMENT 'integer_ts',
	`updated` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`id`)
);

CREATE UNIQUE INDEX `contract_customer_profile_id_ven` ON `contract` (`customer_profile_id`,`vendor_profile_id`);

CREATE TABLE `entity`
(
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`created` INTEGER UNSIGNED COMMENT 'integer_ts',
	`updated` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`id`)
);

CREATE TABLE `job`
(
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`order_id` INTEGER UNSIGNED NOT NULL,
	`created` INTEGER UNSIGNED COMMENT 'integer_ts',
	`updated` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`id`)
);

CREATE TABLE `order`
(
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`created` INTEGER UNSIGNED COMMENT 'integer_ts',
	`updated` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`id`)
);

CREATE TABLE `profile`
(
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`company_name` VARCHAR(255),
	`first_name` VARCHAR(255),
	`last_name` VARCHAR(255),
	`created` INTEGER UNSIGNED COMMENT 'integer_ts',
	`updated` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`id`)
);

CREATE TABLE `profile_member`
(
	`profile_id` INTEGER UNSIGNED NOT NULL,
	`member_profile_id` INTEGER UNSIGNED NOT NULL,
	`role_id` INTEGER UNSIGNED NOT NULL,
	`created` INTEGER UNSIGNED COMMENT 'integer_ts',
	`updated` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`profile_id`,`member_profile_id`,`role_id`)
);

CREATE TABLE `role`
(
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`label` VARCHAR(255) NOT NULL,
	`created` INTEGER UNSIGNED COMMENT 'integer_ts',
	`updated` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`id`)
);

CREATE TABLE `role_permission`
(
	`role_id` INTEGER UNSIGNED NOT NULL,
	`permission_id` INT2 NOT NULL,
	PRIMARY KEY (`role_id`,`permission_id`)
);

CREATE TABLE `session`
(
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`ip_address` VARCHAR(100),
	`user_profile_id` INTEGER UNSIGNED,
	`company_profile_id` INTEGER UNSIGNED,
	`user_agent` VARCHAR(255),
	`started` INTEGER UNSIGNED COMMENT 'integer_ts',
	`ended` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`id`)
);

CREATE TABLE `session_event`
(
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`session_id` INTEGER UNSIGNED NOT NULL,
	`event_type` INTEGER UNSIGNED NOT NULL,
	`target_entity_id` INTEGER UNSIGNED,
	`created` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`id`)
);

CREATE TABLE `user`
(
	`profile_id` INTEGER UNSIGNED NOT NULL,
	`email` VARCHAR(255),
	`password` VARCHAR(255),
	`created` INTEGER UNSIGNED COMMENT 'integer_ts',
	`updated` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`profile_id`)
);

CREATE TABLE `water_account`
(
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`resident_profile_id` INTEGER UNSIGNED,
	`water_site_id` INTEGER UNSIGNED NOT NULL,
	`backflow_plan_id` INTEGER UNSIGNED,
	`supplier_account_number` VARCHAR(255),
	`created` INTEGER UNSIGNED COMMENT 'integer_ts',
	`updated` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`id`)
);

CREATE TABLE `water_site`
(
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`address_id` INTEGER UNSIGNED NOT NULL,
	`water_supplier_id` INTEGER UNSIGNED NOT NULL,
	`supplier_account_number` VARCHAR(255),
	`created` INTEGER UNSIGNED COMMENT 'integer_ts',
	`updated` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`id`)
);

CREATE TABLE `water_supplier`
(
	`profile_id` INTEGER UNSIGNED NOT NULL,
	`created` INTEGER UNSIGNED COMMENT 'integer_ts',
	`updated` INTEGER UNSIGNED COMMENT 'integer_ts',
	PRIMARY KEY (`profile_id`)
);

ALTER TABLE `assignment` ADD CONSTRAINT `assignment_contract_id_fkey`
	FOREIGN KEY (`contract_id`)
	REFERENCES `contract` (`id`)
	ON UPDATE CASCADE
	ON DELETE RESTRICT;

ALTER TABLE `assignment` ADD CONSTRAINT `assignment_job_id_fkey`
	FOREIGN KEY (`job_id`)
	REFERENCES `job` (`id`)
	ON UPDATE CASCADE
	ON DELETE CASCADE;

ALTER TABLE `backflow_assembly` ADD CONSTRAINT `backflow_assembly_backflow_assembly_location_id_fkey`
	FOREIGN KEY (`backflow_assembly_location_id`)
	REFERENCES `backflow_assembly_location` (`id`)
	ON UPDATE CASCADE
	ON DELETE RESTRICT;

ALTER TABLE `backflow_assembly` ADD CONSTRAINT `backflow_assembly_backflow_assembly_model_id_fkey`
	FOREIGN KEY (`backflow_assembly_model_id`)
	REFERENCES `backflow_assembly_model` (`id`)
	ON UPDATE CASCADE
	ON DELETE RESTRICT;

ALTER TABLE `backflow_assembly` ADD CONSTRAINT `backflow_assembly_water_site_id_fkey`
	FOREIGN KEY (`water_site_id`)
	REFERENCES `water_site` (`id`)
	ON UPDATE CASCADE
	ON DELETE RESTRICT;

ALTER TABLE `backflow_assembly_model` ADD CONSTRAINT `backflow_assembly_model_backflow_assembly_make_id_fkey`
	FOREIGN KEY (`backflow_assembly_make_id`)
	REFERENCES `backflow_assembly_make` (`id`)
	ON UPDATE CASCADE
	ON DELETE RESTRICT;

ALTER TABLE `backflow_test` ADD
	FOREIGN KEY (job_id)
	REFERENCES job (id)
	ON UPDATE CASCADE
	ON DELETE RESTRICT;

ALTER TABLE `backflow_test` ADD CONSTRAINT `backflow_test_backflow_assembly_id_fkey`
	FOREIGN KEY (`backflow_assembly_id`)
	REFERENCES `backflow_assembly` (`id`)
	ON UPDATE CASCADE
	ON DELETE RESTRICT;

ALTER TABLE `backflow_test` ADD CONSTRAINT `backflow_test_backflow_replacement_assembly_id_fkey`
	FOREIGN KEY (`backflow_replacement_assembly_id`)
	REFERENCES `backflow_assembly` (`id`)
	ON UPDATE CASCADE
	ON DELETE RESTRICT;

ALTER TABLE `backflow_tester` ADD CONSTRAINT `backflow_tester_profile_id_fkey`
	FOREIGN KEY (`profile_id`)
	REFERENCES `profile` (`id`)
	ON UPDATE CASCADE
	ON DELETE CASCADE;

ALTER TABLE `contract` ADD CONSTRAINT `contract_customer_profile_id_fkey`
	FOREIGN KEY (`customer_profile_id`)
	REFERENCES `profile` (`id`)
	ON UPDATE CASCADE
	ON DELETE CASCADE;

ALTER TABLE `contract` ADD CONSTRAINT `contract_vendor_profile_id_fkey`
	FOREIGN KEY (`vendor_profile_id`)
	REFERENCES `profile` (`id`)
	ON UPDATE CASCADE
	ON DELETE CASCADE;

ALTER TABLE `job` ADD CONSTRAINT `job_order_id_fkey`
	FOREIGN KEY (`order_id`)
	REFERENCES `order` (`id`)
	ON UPDATE CASCADE
	ON DELETE CASCADE;

ALTER TABLE `profile_member` ADD CONSTRAINT `profile_member_member_profile_id_fkey`
	FOREIGN KEY (`member_profile_id`)
	REFERENCES `profile` (`id`)
	ON UPDATE CASCADE
	ON DELETE CASCADE;

ALTER TABLE `profile_member` ADD CONSTRAINT `profile_member_profile_id_fkey`
	FOREIGN KEY (`profile_id`)
	REFERENCES `profile` (`id`)
	ON UPDATE CASCADE
	ON DELETE CASCADE;

ALTER TABLE `profile_member` ADD CONSTRAINT `profile_member_role_id_fkey`
	FOREIGN KEY (`role_id`)
	REFERENCES `role` (`id`)
	ON UPDATE CASCADE
	ON DELETE RESTRICT;

ALTER TABLE `role_permission` ADD
	FOREIGN KEY (`role_id`)
	REFERENCES `role` (`id`)
	ON DELETE CASCADE
	ON UPDATE CASCADE;

ALTER TABLE `session` ADD CONSTRAINT `session_company_profile_id_fkey`
	FOREIGN KEY (`company_profile_id`)
	REFERENCES `profile` (`id`)
	ON UPDATE CASCADE
	ON DELETE SET NULL;

ALTER TABLE `session` ADD CONSTRAINT `session_user_profile_id_fkey`
	FOREIGN KEY (`user_profile_id`)
	REFERENCES `user` (`profile_id`)
	ON UPDATE CASCADE
	ON DELETE SET NULL;

ALTER TABLE `session_event` ADD CONSTRAINT `session_event_session_id_fkey`
	FOREIGN KEY (`session_id`)
	REFERENCES `session` (`id`)
	ON UPDATE CASCADE
	ON DELETE CASCADE;

ALTER TABLE `user` ADD CONSTRAINT `user_profile_id_fkey`
	FOREIGN KEY (`profile_id`)
	REFERENCES `profile` (`id`)
	ON UPDATE CASCADE
	ON DELETE CASCADE;

ALTER TABLE `water_account` ADD CONSTRAINT `water_account_backflow_plan_id_fkey`
	FOREIGN KEY (`backflow_plan_id`)
	REFERENCES `backflow_plan` (`id`)
	ON UPDATE CASCADE
	ON DELETE RESTRICT;

ALTER TABLE `water_account` ADD CONSTRAINT `water_account_resident_profile_id_fkey`
	FOREIGN KEY (`resident_profile_id`)
	REFERENCES `profile` (`id`)
	ON UPDATE CASCADE
	ON DELETE RESTRICT;

ALTER TABLE `water_account` ADD CONSTRAINT `water_account_water_site_id_fkey`
	FOREIGN KEY (`water_site_id`)
	REFERENCES `water_site` (`id`)
	ON UPDATE CASCADE
	ON DELETE RESTRICT;

ALTER TABLE `water_site` ADD CONSTRAINT `water_site_address_id_fkey`
	FOREIGN KEY (`address_id`)
	REFERENCES `address` (`id`)
	ON UPDATE CASCADE
	ON DELETE RESTRICT;

ALTER TABLE `water_site` ADD CONSTRAINT `water_site_water_supplier_id_fkey`
	FOREIGN KEY (`water_supplier_id`)
	REFERENCES `water_supplier` (`profile_id`)
	ON UPDATE CASCADE
	ON DELETE RESTRICT;

ALTER TABLE `water_supplier` ADD CONSTRAINT `water_supplier_profile_id_fkey`
	FOREIGN KEY (`profile_id`)
	REFERENCES `profile` (`id`)
	ON UPDATE CASCADE
	ON DELETE CASCADE;