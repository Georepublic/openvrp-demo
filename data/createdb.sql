-- Table: account

-- DROP TABLE account;

CREATE TABLE account
(
  id serial NOT NULL,
  "name" text NOT NULL,
  pass text NOT NULL,
  created timestamp without time zone DEFAULT now(),
  updated timestamp without time zone DEFAULT now()
)
WITH (
  OIDS=FALSE
);
ALTER TABLE account OWNER TO georepublic;


-- Table: depot

-- DROP TABLE depot;

CREATE TABLE depot
(
  id serial NOT NULL,
  "name" text NOT NULL,
  created timestamp without time zone DEFAULT now(),
  updated timestamp without time zone DEFAULT now(),
  the_geom geometry,
  CONSTRAINT enforce_dims_the_geom CHECK (st_ndims(the_geom) = 2),
  CONSTRAINT enforce_geotype_the_geom CHECK (geometrytype(the_geom) = 'POINT'::text OR the_geom IS NULL),
  CONSTRAINT enforce_srid_the_geom CHECK (st_srid(the_geom) = 4326)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE depot OWNER TO georepublic;


-- Table: orders

-- DROP TABLE orders;

CREATE TABLE orders
(
  "name" text NOT NULL,
  account_id integer,
  created timestamp without time zone DEFAULT now(),
  updated timestamp without time zone DEFAULT now(),
  the_geom geometry,
  pick_after interval,
  pick_before interval,
  drop_after interval,
  drop_before interval,
  size double precision,
  pickup timestamp with time zone,
  dropoff timestamp with time zone,
  id integer,
  order_id serial NOT NULL,
  CONSTRAINT enforce_dims_the_geom CHECK (st_ndims(the_geom) = 2),
  CONSTRAINT enforce_geotype_the_geom CHECK (geometrytype(the_geom) = 'LINESTRING'::text OR the_geom IS NULL),
  CONSTRAINT enforce_srid_the_geom CHECK (st_srid(the_geom) = 4326)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE orders OWNER TO georepublic;



-- Table: vehicle

-- DROP TABLE vehicle;

CREATE TABLE vehicle
(
  id serial NOT NULL,
  "name" text NOT NULL,
  created timestamp without time zone DEFAULT now(),
  updated timestamp without time zone DEFAULT now(),
  the_geom geometry,
  capacity double precision,
  depot_id integer,
  vehicle_id serial NOT NULL,
  CONSTRAINT enforce_dims_the_geom CHECK (st_ndims(the_geom) = 2),
  CONSTRAINT enforce_geotype_the_geom CHECK (geometrytype(the_geom) = 'POINT'::text OR the_geom IS NULL),
  CONSTRAINT enforce_srid_the_geom CHECK (st_srid(the_geom) = 4326)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE vehicle OWNER TO georepublic;

-- Index: vid_idx

-- DROP INDEX vid_idx;

CREATE INDEX vid_idx
  ON vehicle
  USING btree
  (id);


-- View: darp_orders

-- DROP VIEW darp_orders;

CREATE OR REPLACE VIEW darp_orders AS 
         SELECT orders.id, orders.order_id, 0 AS depot_id, orders.size, 
         x(st_startpoint(orders.the_geom)) AS from_x, 
         y(st_startpoint(orders.the_geom)) AS from_y, 
         x(st_endpoint(orders.the_geom)) AS to_x, 
         y(st_endpoint(orders.the_geom)) AS to_y, 
         orders.pickup AS pu_time, orders.dropoff AS do_time, 
         orders.pick_before AS pu_lt, orders.drop_before AS do_lt, 
         orders.pick_after AS pu_ut, orders.drop_after AS do_ut, 
         st_startpoint(orders.the_geom) AS startpoint, 
         st_endpoint(orders.the_geom) AS endpoint
           FROM orders
UNION 
         SELECT 0 AS id, 0 AS order_id, depot.id AS depot_id, 0 AS size, 
         x(depot.the_geom) AS from_x, y(depot.the_geom) AS from_y, 
         x(depot.the_geom) AS to_x, y(depot.the_geom) AS to_y, 
         now() AS pu_time, now() AS do_time, '00:00:00' AS pu_lt, 
         '00:00:00' AS do_lt, '00:00:00' AS pu_ut, '00:00:00' AS do_ut, 
         depot.the_geom AS startpoint, depot.the_geom AS endpoint
           FROM depot
  ORDER BY 1;

ALTER TABLE darp_orders OWNER TO georepublic;



-- View: darp_points

-- DROP VIEW darp_points;

CREATE OR REPLACE VIEW darp_points AS 
         SELECT darp_orders.id, darp_orders.order_id, darp_orders.depot_id, 
         astext(transform(darp_orders.startpoint, 900913)) AS geom_meter
           FROM darp_orders
UNION 
         SELECT darp_orders.id + ((( SELECT count(*) AS count
                   FROM orders))::integer + 0) AS id, darp_orders.order_id, 
                   darp_orders.depot_id, 
                   astext(transform(darp_orders.endpoint, 900913)) AS geom_meter
           FROM darp_orders
          WHERE darp_orders.depot_id = 0;

ALTER TABLE darp_points OWNER TO georepublic;


-- View: darp_report

-- DROP VIEW darp_report;

CREATE OR REPLACE VIEW darp_report AS 
         SELECT orders.id, orders.order_id, 0 AS depot_id, orders.size, 
         orders.pickup AS pu_time, orders.dropoff AS do_time, 
         orders.pick_before AS pu_lt, orders.drop_before AS do_lt, 
         orders.pick_after AS pu_ut, orders.drop_after AS do_ut, 
         st_asgeojson(st_transform(st_startpoint(orders.the_geom), 900913)) AS startpoint, 
         st_asgeojson(st_transform(st_endpoint(orders.the_geom), 900913)) AS endpoint
           FROM orders
UNION 
         SELECT 0 AS id, 0 AS order_id, depot.id AS depot_id, 0 AS size, 
         now() AS pu_time, now() AS do_time, '00:00:00' AS pu_lt, 
         '00:00:00' AS do_lt, '00:00:00' AS pu_ut, '00:00:00' AS do_ut, 
         st_asgeojson(st_transform(depot.the_geom, 900913)) AS startpoint, 
         st_asgeojson(st_transform(depot.the_geom, 900913)) AS endpoint
           FROM depot
  ORDER BY 1;

ALTER TABLE darp_report OWNER TO georepublic;



-- View: darp_vehicles

-- DROP VIEW darp_vehicles;

CREATE OR REPLACE VIEW darp_vehicles AS 
 SELECT vehicle.id, vehicle.vehicle_id, vehicle.capacity, vehicle.depot_id
   FROM vehicle
  ORDER BY vehicle.id;

ALTER TABLE darp_vehicles OWNER TO georepublic;



-- View: json_depots

-- DROP VIEW json_depots;

CREATE OR REPLACE VIEW json_depots AS 
 SELECT depot.id, st_asgeojson(st_transform(depot.the_geom, 900913)) AS geometry, 
 st_astext(st_transform(depot.the_geom, 900913)) AS wkt, depot.name, 
 depot.created, depot.updated
   FROM depot;

ALTER TABLE json_depots OWNER TO georepublic;



-- View: json_orders

-- DROP VIEW json_orders;

CREATE OR REPLACE VIEW json_orders AS 
 SELECT orders.id, orders.order_id, orders.account_id, 
 st_asgeojson(st_transform(orders.the_geom, 900913)) AS geometry, 
 orders.name, orders.created, orders.updated, 
 st_asgeojson(st_transform(st_startpoint(orders.the_geom), 900913)) AS geom_start, 
 st_asgeojson(st_transform(st_endpoint(orders.the_geom), 900913)) AS geom_goal, 
 st_astext(st_transform(st_startpoint(orders.the_geom), 900913)) AS wkt_start, 
 st_astext(st_transform(st_endpoint(orders.the_geom), 900913)) AS wkt_goal, 
 to_char(orders.pickup, 'YYYY-MM-DD HH24:MI:SS'::text) AS pickup, 
 to_char(orders.dropoff, 'YYYY-MM-DD HH24:MI:SS'::text) AS dropoff, 
 date_part('epoch'::text, orders.pick_after) / 3600::double precision AS pick_after, 
 date_part('epoch'::text, orders.pick_before) / 3600::double precision AS pick_before, 
 date_part('epoch'::text, orders.drop_after) / 3600::double precision AS drop_after, 
 date_part('epoch'::text, orders.drop_before) / 3600::double precision AS drop_before, 
 orders.size
   FROM orders;

ALTER TABLE json_orders OWNER TO georepublic;



-- View: json_vehicles

-- DROP VIEW json_vehicles;

CREATE OR REPLACE VIEW json_vehicles AS 
 SELECT vehicle.id, vehicle.vehicle_id, 
 st_asgeojson(st_transform(vehicle.the_geom, 900913)) AS geometry, 
 st_astext(st_transform(vehicle.the_geom, 900913)) AS wkt, 
 vehicle.capacity, vehicle.name, vehicle.created, vehicle.updated
   FROM vehicle;

ALTER TABLE json_vehicles OWNER TO georepublic;




