SET default_with_oids = false;
SET client_encoding = 'UTF8';

CREATE TABLE entity
(
	id serial PRIMARY KEY,
	created timestamp with time zone,
	updated timestamp with time zone
);

CREATE TABLE profile
(
	company_name character varying(255),
	first_name character varying(255),
	last_name character varying(255),
	PRIMARY KEY (id)
)
INHERITS (entity);

CREATE TABLE "user"
(
	profile_id integer NOT NULL PRIMARY KEY,
	created timestamp with time zone,
	updated timestamp with time zone,
	email character varying(255),
	"password" character varying(255),
	FOREIGN KEY (profile_id) REFERENCES profile (id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE "role" (
	label character varying(255) NOT NULL,
	"global" boolean NOT NULL DEFAULT false,
	PRIMARY KEY (id)
)
INHERITS (entity);

CREATE TABLE role_permission (
	role_id integer NOT NULL,
	permission_id smallint NOT NULL,
	PRIMARY KEY (role_id, permission_id)
);

CREATE TABLE profile_member
(
	profile_id integer NOT NULL,
	member_profile_id integer NOT NULL,
	role_id integer NOT NULL,
	created timestamp with time zone,
	updated timestamp with time zone,
	PRIMARY KEY (profile_id, member_profile_id, role_id),
	FOREIGN KEY (member_profile_id) REFERENCES profile (id) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (profile_id) REFERENCES profile (id) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (role_id) REFERENCES "role" (id) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE contract
(
	customer_profile_id integer NOT NULL,
	vendor_profile_id integer NOT NULL,
	customer_status smallint,
	vendor_status smallint,
	PRIMARY KEY (id),
	UNIQUE (customer_profile_id, vendor_profile_id),
	FOREIGN KEY (customer_profile_id) REFERENCES profile (id) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (vendor_profile_id) REFERENCES profile (id) ON UPDATE CASCADE ON DELETE CASCADE
)
INHERITS (entity);

CREATE TABLE "session"
(
	id serial,
	ip_address inet,
	user_profile_id integer,
	company_profile_id integer,
	user_agent character varying(255),
	started timestamp with time zone,
	ended timestamp with time zone,
	PRIMARY KEY (id),
	FOREIGN KEY (user_profile_id) REFERENCES "user" (profile_id) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (company_profile_id) REFERENCES "profile" (id) ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE TABLE session_event
(
	id serial,
	session_id integer NOT NULL,
	event_type integer NOT NULL,
	target_entity_id integer,
	created timestamp with time zone,
	PRIMARY KEY (id),
	FOREIGN KEY (session_id) REFERENCES session (id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE address
(
	street1 character varying(255),
	street2 character varying(255),
	city character varying(255),
	state char DEFAULT 2,
	zip char DEFAULT 5,
	PRIMARY KEY (id)
)
INHERITS (entity);

CREATE TABLE water_supplier
(
	profile_id integer NOT NULL PRIMARY KEY,
	created timestamp with time zone,
	updated timestamp with time zone,
	FOREIGN KEY (profile_id) REFERENCES profile (id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE backflow_tester
(
	profile_id integer NOT NULL PRIMARY KEY,
	created timestamp with time zone,
	updated timestamp with time zone,
	gauge_number character varying(255),
	gauge_calibrated date,
	certification_number character varying(255),
	FOREIGN KEY (profile_id) REFERENCES profile (id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE water_site
(
	address_id integer NOT NULL,
	water_supplier_id integer NOT NULL,
	supplier_account_number character varying(255),
	PRIMARY KEY (id),
	FOREIGN KEY (address_id) REFERENCES address (id) ON UPDATE CASCADE ON DELETE RESTRICT,
	FOREIGN KEY (water_supplier_id) REFERENCES water_supplier (profile_id) ON UPDATE CASCADE ON DELETE RESTRICT
)
INHERITS (entity);

CREATE TABLE backflow_plan
(
	"name" character varying(255),
	PRIMARY KEY (id)
)
INHERITS (entity);

CREATE TABLE water_account
(
	resident_profile_id integer,
	water_site_id integer NOT NULL,
	backflow_plan_id integer,
	supplier_account_number character varying(255),
	PRIMARY KEY (id),
	FOREIGN KEY (resident_profile_id) REFERENCES profile (id) ON UPDATE CASCADE ON DELETE RESTRICT,
	FOREIGN KEY (water_site_id) REFERENCES water_site (id) ON UPDATE CASCADE ON DELETE RESTRICT,
	FOREIGN KEY (backflow_plan_id) REFERENCES backflow_plan (id) ON UPDATE CASCADE ON DELETE RESTRICT
)
INHERITS (entity);

CREATE TABLE backflow_assembly_make
(
	"name" character varying(255) NOT NULL,
	PRIMARY KEY (id),
	UNIQUE ("name")
)
INHERITS (entity);

CREATE TABLE backflow_assembly_model
(
	"name" character varying(255) NOT NULL,
	backflow_assembly_make_id integer NOT NULL,
	PRIMARY KEY (id),
	UNIQUE ("name", backflow_assembly_make_id),
	FOREIGN KEY (backflow_assembly_make_id) REFERENCES backflow_assembly_make (id) ON UPDATE CASCADE ON DELETE RESTRICT
)
INHERITS (entity);

CREATE TABLE backflow_assembly_location
(
	"name" character varying(255),
	PRIMARY KEY (id)
)
INHERITS (entity);

CREATE TABLE backflow_assembly
(
	water_site_id integer NOT NULL,
	backflow_assembly_model_id integer,
	backflow_assembly_location_id integer,
	size character varying(255),
	serial_number character varying(255),
	backflow_assembly_type integer NOT NULL,
	status integer NOT NULL DEFAULT 1,
	feeds character varying(255),
	PRIMARY KEY (id),
	FOREIGN KEY (water_site_id) REFERENCES water_site (id) ON UPDATE CASCADE ON DELETE RESTRICT,
	FOREIGN KEY (backflow_assembly_model_id) REFERENCES backflow_assembly_model (id) ON UPDATE CASCADE ON DELETE RESTRICT,
	FOREIGN KEY (backflow_assembly_location_id) REFERENCES backflow_assembly_location (id) ON UPDATE CASCADE ON DELETE RESTRICT
)
INHERITS (entity);

CREATE TABLE "order"
(
	PRIMARY KEY (id)
)
INHERITS (entity);

CREATE TABLE job
(
	order_id integer NOT NULL,
	PRIMARY KEY (id),
	FOREIGN KEY (order_id) REFERENCES "order" (id) ON UPDATE CASCADE ON DELETE CASCADE
)
INHERITS (entity);

CREATE TABLE backflow_test
(
	job_id integer NOT NULL,
	backflow_assembly_id integer NOT NULL,
	backflow_replacement_assembly_id integer NOT NULL,
	installation_status integer,
	meter_reading character varying(255),
	pressure_drop character varying(255),
	opened_at character varying(255),
	check1 character varying(255),
	check1_status integer,
	check2 character varying(255),
	check2_status integer,
	passed boolean,
	repairs boolean,
	approved boolean,
	service_restored boolean,
	PRIMARY KEY (job_id),
	FOREIGN KEY (backflow_assembly_id) REFERENCES backflow_assembly (id) ON UPDATE CASCADE ON DELETE RESTRICT,
	FOREIGN KEY (backflow_replacement_assembly_id) REFERENCES backflow_assembly (id) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE assignment
(
	job_id integer NOT NULL,
	contract_id integer NOT NULL,
	status integer NOT NULL DEFAULT 1,
	due timestamp with time zone,
	submitted timestamp with time zone,
	PRIMARY KEY (id),
	FOREIGN KEY (job_id) REFERENCES job (id) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (contract_id) REFERENCES contract (id) ON UPDATE CASCADE ON DELETE RESTRICT
)
INHERITS (entity);

CREATE INDEX ON assignment (status);

ALTER TABLE assignment OWNER TO molitech_oms;
ALTER TABLE backflow_test OWNER TO molitech_oms;
ALTER TABLE job OWNER TO molitech_oms;
ALTER TABLE "order" OWNER TO molitech_oms;
ALTER TABLE backflow_assembly OWNER TO molitech_oms;
ALTER TABLE backflow_assembly_location OWNER TO molitech_oms;
ALTER TABLE backflow_assembly_model OWNER TO molitech_oms;
ALTER TABLE backflow_assembly_make OWNER TO molitech_oms;
ALTER TABLE water_account OWNER TO molitech_oms;
ALTER TABLE backflow_plan OWNER TO molitech_oms;
ALTER TABLE water_site OWNER TO molitech_oms;
ALTER TABLE address OWNER TO molitech_oms;
ALTER TABLE session_event OWNER TO molitech_oms;
ALTER TABLE "session" OWNER TO molitech_oms;
ALTER TABLE contract OWNER TO molitech_oms;
ALTER TABLE backflow_tester OWNER TO molitech_oms;
ALTER TABLE water_supplier OWNER TO molitech_oms;
ALTER TABLE profile_member OWNER TO molitech_oms;
ALTER TABLE "role" OWNER TO molitech_oms;
ALTER TABLE "role_permission" OWNER TO molitech_oms;
ALTER TABLE "user" OWNER TO molitech_oms;
ALTER TABLE entity OWNER TO molitech_oms;
ALTER TABLE profile OWNER TO molitech_oms;