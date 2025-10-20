CREATE SCHEMA IF NOT EXISTS rail AUTHORIZATION CURRENT_USER;
SET search_path TO rail, public;

CREATE TABLE rail.stations (
  station_id SERIAL PRIMARY KEY,
  name TEXT NOT NULL,
  inn VARCHAR(12) UNIQUE,
  address TEXT NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  CHECK (inn IS NULL OR inn ~ '^[0-9]{10}$' OR inn ~ '^[0-9]{12}$')
);

CREATE TABLE rail.train_types (
  train_type_id SERIAL PRIMARY KEY,
  name TEXT NOT NULL UNIQUE,
  description TEXT
);

CREATE TABLE rail.brigades (
  brigade_id SERIAL PRIMARY KEY,
  name TEXT NOT NULL UNIQUE,
  member_count INTEGER NOT NULL DEFAULT 0 CHECK (member_count >= 0)
);

CREATE TABLE rail.positions (
  position_id SERIAL PRIMARY KEY,
  name TEXT NOT NULL UNIQUE
);

CREATE TABLE rail.personnel (
  person_id BIGSERIAL PRIMARY KEY,
  station_id INTEGER NOT NULL REFERENCES rail.stations(station_id) ON UPDATE CASCADE ON DELETE RESTRICT,
  inn VARCHAR(12) UNIQUE,
  full_name TEXT NOT NULL,
  position_id INTEGER NOT NULL REFERENCES rail.positions(position_id) ON UPDATE CASCADE ON DELETE RESTRICT,
  brigade_id INTEGER REFERENCES rail.brigades(brigade_id) ON UPDATE CASCADE ON DELETE SET NULL,
  hire_date DATE NOT NULL DEFAULT CURRENT_DATE,
  fired_at DATE,
  CHECK (inn IS NULL OR inn ~ '^[0-9]{10}$' OR inn ~ '^[0-9]{12}$'),
  CHECK (fired_at IS NULL OR fired_at >= hire_date)
);

CREATE TABLE rail.trains (
  train_id BIGSERIAL PRIMARY KEY,
  owner_station_id INTEGER NOT NULL REFERENCES rail.stations(station_id) ON UPDATE CASCADE ON DELETE RESTRICT,
  train_number VARCHAR(10) NOT NULL,
  train_type_id INTEGER NOT NULL REFERENCES rail.train_types(train_type_id) ON UPDATE CASCADE ON DELETE RESTRICT,
  name TEXT,
  UNIQUE (train_number)
);

CREATE TABLE rail.routes (
  route_id BIGSERIAL PRIMARY KEY,
  route_code VARCHAR(20) NOT NULL UNIQUE,
  owner_station_id INTEGER NOT NULL REFERENCES rail.stations(station_id) ON UPDATE CASCADE ON DELETE RESTRICT,
  train_id BIGINT NOT NULL REFERENCES rail.trains(train_id) ON UPDATE CASCADE ON DELETE RESTRICT,
  departure_station_id INTEGER NOT NULL REFERENCES rail.stations(station_id) ON UPDATE CASCADE ON DELETE RESTRICT,
  arrival_station_id INTEGER NOT NULL REFERENCES rail.stations(station_id) ON UPDATE CASCADE ON DELETE RESTRICT,
  scheduled_departure TIMESTAMPTZ,
  scheduled_arrival TIMESTAMPTZ,
  brigade_id INTEGER REFERENCES rail.brigades(brigade_id) ON UPDATE CASCADE ON DELETE SET NULL,
  CHECK (scheduled_arrival IS NULL OR scheduled_departure IS NULL OR scheduled_arrival > scheduled_departure)
);

CREATE TABLE rail.route_stops (
  stop_id BIGSERIAL PRIMARY KEY,
  route_id BIGINT NOT NULL REFERENCES rail.routes(route_id) ON UPDATE CASCADE ON DELETE CASCADE,
  stop_no INTEGER NOT NULL CHECK (stop_no > 0),
  station_id INTEGER NOT NULL REFERENCES rail.stations(station_id) ON UPDATE CASCADE ON DELETE RESTRICT,
  arrival_time TIMESTAMPTZ,
  departure_time TIMESTAMPTZ,
  CHECK (arrival_time IS NULL OR departure_time IS NULL OR departure_time >= arrival_time),
  UNIQUE (route_id, stop_no)
);

CREATE INDEX idx_personnel_station ON rail.personnel(station_id);
CREATE INDEX idx_personnel_brigade ON rail.personnel(brigade_id);
CREATE INDEX idx_trains_type ON rail.trains(train_type_id);
CREATE INDEX idx_routes_train ON rail.routes(train_id);
CREATE INDEX idx_routes_dep_station ON rail.routes(departure_station_id);
CREATE INDEX idx_routes_arr_station ON rail.routes(arrival_station_id);
CREATE INDEX idx_route_stops_route ON rail.route_stops(route_id);
CREATE INDEX idx_route_stops_station ON rail.route_stops(station_id);

CREATE OR REPLACE VIEW rail.v_current_personnel AS
SELECT person_id, full_name, hire_date, station_id, brigade_id, position_id
FROM rail.personnel
WHERE fired_at IS NULL;

CREATE OR REPLACE VIEW rail.v_route_overview AS
SELECT
  r.route_id,
  r.route_code,
  t.train_number,
  tt.name AS train_type,
  s_dep.name AS departure_station,
  s_arr.name AS arrival_station,
  r.scheduled_departure,
  r.scheduled_arrival,
  b.name AS brigade
FROM rail.routes r
JOIN rail.trains t ON r.train_id = t.train_id
JOIN rail.train_types tt ON t.train_type_id = tt.train_type_id
JOIN rail.stations s_dep ON r.departure_station_id = s_dep.station_id
JOIN rail.stations s_arr ON r.arrival_station_id = s_arr.station_id
LEFT JOIN rail.brigades b ON r.brigade_id = b.brigade_id;

CREATE OR REPLACE VIEW rail.v_busy_departure_stations AS
SELECT
  s.station_id,
  s.name AS station_name,
  COUNT(r.route_id) AS routes_departing
FROM rail.stations s
LEFT JOIN rail.routes r ON r.departure_station_id = s.station_id
GROUP BY s.station_id, s.name
HAVING COUNT(r.route_id) >= 1;

CREATE OR REPLACE FUNCTION rail.fn_sync_brigade_member_count()
RETURNS TRIGGER AS $$
BEGIN
  IF TG_OP = 'INSERT' THEN
    IF NEW.brigade_id IS NOT NULL THEN
      UPDATE rail.brigades SET member_count = member_count + 1
      WHERE brigade_id = NEW.brigade_id;
    END IF;
    RETURN NEW;
  ELSIF TG_OP = 'DELETE' THEN
    IF OLD.brigade_id IS NOT NULL THEN
      UPDATE rail.brigades SET member_count = GREATEST(0, member_count - 1)
      WHERE brigade_id = OLD.brigade_id;
    END IF;
    RETURN OLD;
  ELSIF TG_OP = 'UPDATE' THEN
    IF NEW.brigade_id IS DISTINCT FROM OLD.brigade_id THEN
      IF OLD.brigade_id IS NOT NULL THEN
        UPDATE rail.brigades SET member_count = GREATEST(0, member_count - 1)
        WHERE brigade_id = OLD.brigade_id;
      END IF;
      IF NEW.brigade_id IS NOT NULL THEN
        UPDATE rail.brigades SET member_count = member_count + 1
        WHERE brigade_id = NEW.brigade_id;
      END IF;
    END IF;
    RETURN NEW;
  END IF;
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_personnel_brigade_count
AFTER INSERT OR UPDATE OR DELETE ON rail.personnel
FOR EACH ROW EXECUTE FUNCTION rail.fn_sync_brigade_member_count();

CREATE OR REPLACE FUNCTION rail.fn_update_route_times()
RETURNS TRIGGER AS $$
DECLARE
  rid BIGINT;
BEGIN
  rid := COALESCE(NEW.route_id, OLD.route_id);
  UPDATE rail.routes r
  SET
    scheduled_departure = (
      SELECT MIN(rs.departure_time) FROM rail.route_stops rs WHERE rs.route_id = rid
    ),
    scheduled_arrival = (
      SELECT MAX(rs.arrival_time) FROM rail.route_stops rs WHERE rs.route_id = rid
    )
  WHERE r.route_id = rid;
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_route_stops_sync_times
AFTER INSERT OR UPDATE OR DELETE ON rail.route_stops
FOR EACH ROW EXECUTE FUNCTION rail.fn_update_route_times();



INSERT INTO rail.stations (name, inn, address) VALUES
  ('Москва Ленинградский', '7700000000', 'Москва, Комсомольская площадь, 3'),
  ('Санкт-Петербург Московский', '7800000000', 'Санкт-Петербург, пл. Восстания, 2'),
  ('Владимир', '3300000000', 'Владимир, Привокзальная площадь, 1');

INSERT INTO rail.train_types (name, description) VALUES
  ('Пассажирский', 'Обычный пассажирский поезд'),
  ('Скоростной', 'Сокращенное время в пути');

INSERT INTO rail.brigades (name) VALUES
  ('Бригада №1'),
  ('Бригада №2');

INSERT INTO rail.positions (name) VALUES
  ('Машинист'),
  ('Проводник'),
  ('Дежурный по станции');

INSERT INTO rail.trains (owner_station_id, train_number, train_type_id, name) VALUES
  (1, '001A', 2, 'Сапсан-001'),
  (2, '102B', 1, 'Пассажир-102');

INSERT INTO rail.personnel (station_id, inn, full_name, position_id, brigade_id)
VALUES
  (1, '7711111111', 'Иванов Иван Иванович', 1, 1),
  (1, '7722222222', 'Петров Петр Петрович', 2, 1),
  (2, '7801234567', 'Сидорова Анна Сергеевна', 3, NULL);

INSERT INTO rail.routes (route_code, owner_station_id, train_id, departure_station_id, arrival_station_id, brigade_id)
VALUES
  ('R-001', 1, 1, 1, 2, 1),
  ('R-002', 2, 2, 2, 3, NULL);

INSERT INTO rail.route_stops (route_id, stop_no, station_id, arrival_time, departure_time) VALUES
  (1, 1, 1, NULL, now() + interval '1 day 08:00'),
  (1, 2, 3, now() + interval '1 day 10:00', now() + interval '1 day 10:10'),
  (1, 3, 2, now() + interval '1 day 13:40', NULL),
  (2, 1, 2, NULL, now() + interval '2 days 09:00'),
  (2, 2, 3, now() + interval '2 days 12:00', NULL);

