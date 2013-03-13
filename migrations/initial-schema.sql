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

CREATE TABLE water_supplier
(
	profile_id integer NOT NULL PRIMARY KEY,
	created timestamp with time zone,
	updated timestamp with time zone,
	FOREIGN KEY (profile_id) REFERENCES profile (id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE water_tester
(
	profile_id integer NOT NULL PRIMARY KEY,
	created timestamp with time zone,
	updated timestamp with time zone,
	gauge_number character varying(255),
	gauge_calibrated date,
	certification_number character varying(255),
	FOREIGN KEY (profile_id) REFERENCES profile (id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE profile_contract
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

ALTER TABLE session_event OWNER TO molitech_oms;
ALTER TABLE "session" OWNER TO molitech_oms;
ALTER TABLE profile_contract OWNER TO molitech_oms;
ALTER TABLE water_tester OWNER TO molitech_oms;
ALTER TABLE water_supplier OWNER TO molitech_oms;
ALTER TABLE profile_member OWNER TO molitech_oms;
ALTER TABLE "role" OWNER TO molitech_oms;
ALTER TABLE "role_permission" OWNER TO molitech_oms;
ALTER TABLE "user" OWNER TO molitech_oms;
ALTER TABLE entity OWNER TO molitech_oms;
ALTER TABLE profile OWNER TO molitech_oms;