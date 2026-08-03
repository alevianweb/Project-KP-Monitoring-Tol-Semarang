-- =====================================================
-- Vehicle Monitoring Database Schema
-- Project KP - WebGIS Monitoring Kendaraan Gerbang Tol
-- PostgreSQL 17 + PostGIS
-- =====================================================

CREATE EXTENSION IF NOT EXISTS postgis;

-- ==========================================
-- TABLE CAMERAS
-- ==========================================

CREATE TABLE IF NOT EXISTS cameras (

    id SERIAL PRIMARY KEY,

    name VARCHAR(100) NOT NULL,

    location VARCHAR(255),

    latitude DOUBLE PRECISION,

    longitude DOUBLE PRECISION,

    geom GEOMETRY(Point,4326),

    video_source TEXT,

    status BOOLEAN DEFAULT TRUE,

    line_start_x INT DEFAULT 0,

    line_start_y INT DEFAULT 650,

    line_end_x INT DEFAULT 1920,

    line_end_y INT DEFAULT 650,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- TABLE VEHICLE DETECTIONS
-- ==========================================

CREATE TABLE IF NOT EXISTS vehicle_detections (

    id BIGSERIAL PRIMARY KEY,

    camera_id INT REFERENCES cameras(id),

    vehicle_type VARCHAR(50),

    direction VARCHAR(20),

    confidence FLOAT,

    tracking_id INT,

    detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- DAILY STATISTICS
-- ==========================================

CREATE TABLE IF NOT EXISTS vehicle_statistics_daily (

    id BIGSERIAL PRIMARY KEY,

    camera_id INT REFERENCES cameras(id),

    statistic_date DATE,

    motorcycle INT DEFAULT 0,

    car INT DEFAULT 0,

    bus INT DEFAULT 0,

    truck INT DEFAULT 0,

    total INT DEFAULT 0,

    CONSTRAINT uq_camera_date UNIQUE (camera_id, statistic_date)
);

-- ==========================================
-- WEEKLY STATISTICS
-- ==========================================

CREATE TABLE IF NOT EXISTS vehicle_statistics_weekly (

    id BIGSERIAL PRIMARY KEY,

    camera_id INT REFERENCES cameras(id),

    week INT,

    year INT,

    motorcycle INT DEFAULT 0,

    car INT DEFAULT 0,

    bus INT DEFAULT 0,

    truck INT DEFAULT 0,

    total INT DEFAULT 0,

    CONSTRAINT uq_camera_week UNIQUE (camera_id, week, year)
);

-- ==========================================
-- MONTHLY STATISTICS
-- ==========================================

CREATE TABLE IF NOT EXISTS vehicle_statistics_monthly (

    id BIGSERIAL PRIMARY KEY,

    camera_id INT REFERENCES cameras(id),

    month INT,

    year INT,

    motorcycle INT DEFAULT 0,

    car INT DEFAULT 0,

    bus INT DEFAULT 0,

    truck INT DEFAULT 0,

    total INT DEFAULT 0,

    CONSTRAINT uq_camera_month UNIQUE (camera_id, month, year)
);

-- ==========================================
-- TRIGGERS & FUNCTIONS FOR AUTOMATED STATS
-- ==========================================

CREATE OR REPLACE FUNCTION update_vehicle_statistics()
RETURNS TRIGGER AS $$
DECLARE
    v_date DATE;
    v_week INT;
    v_month INT;
    v_year INT;
    v_col VARCHAR;
BEGIN
    v_date := DATE(NEW.detected_at);
    v_week := EXTRACT(WEEK FROM NEW.detected_at);
    v_month := EXTRACT(MONTH FROM NEW.detected_at);
    v_year := EXTRACT(YEAR FROM NEW.detected_at);

    -- Map vehicle_type to column name
    CASE LOWER(NEW.vehicle_type)
        WHEN 'car' THEN v_col := 'car';
        WHEN 'motorcycle' THEN v_col := 'motorcycle';
        WHEN 'bus' THEN v_col := 'bus';
        WHEN 'truck' THEN v_col := 'truck';
        ELSE RETURN NEW;
    END CASE;

    -- Update Daily
    INSERT INTO vehicle_statistics_daily (camera_id, statistic_date, motorcycle, car, bus, truck, total)
    VALUES (NEW.camera_id, v_date, 
            CASE WHEN v_col = 'motorcycle' THEN 1 ELSE 0 END,
            CASE WHEN v_col = 'car' THEN 1 ELSE 0 END,
            CASE WHEN v_col = 'bus' THEN 1 ELSE 0 END,
            CASE WHEN v_col = 'truck' THEN 1 ELSE 0 END,
            1)
    ON CONFLICT (camera_id, statistic_date) DO UPDATE
    SET 
        motorcycle = vehicle_statistics_daily.motorcycle + EXCLUDED.motorcycle,
        car = vehicle_statistics_daily.car + EXCLUDED.car,
        bus = vehicle_statistics_daily.bus + EXCLUDED.bus,
        truck = vehicle_statistics_daily.truck + EXCLUDED.truck,
        total = vehicle_statistics_daily.total + EXCLUDED.total;

    -- Update Weekly
    INSERT INTO vehicle_statistics_weekly (camera_id, week, year, motorcycle, car, bus, truck, total)
    VALUES (NEW.camera_id, v_week, v_year, 
            CASE WHEN v_col = 'motorcycle' THEN 1 ELSE 0 END,
            CASE WHEN v_col = 'car' THEN 1 ELSE 0 END,
            CASE WHEN v_col = 'bus' THEN 1 ELSE 0 END,
            CASE WHEN v_col = 'truck' THEN 1 ELSE 0 END,
            1)
    ON CONFLICT (camera_id, week, year) DO UPDATE
    SET 
        motorcycle = vehicle_statistics_weekly.motorcycle + EXCLUDED.motorcycle,
        car = vehicle_statistics_weekly.car + EXCLUDED.car,
        bus = vehicle_statistics_weekly.bus + EXCLUDED.bus,
        truck = vehicle_statistics_weekly.truck + EXCLUDED.truck,
        total = vehicle_statistics_weekly.total + EXCLUDED.total;

    -- Update Monthly
    INSERT INTO vehicle_statistics_monthly (camera_id, month, year, motorcycle, car, bus, truck, total)
    VALUES (NEW.camera_id, v_month, v_year, 
            CASE WHEN v_col = 'motorcycle' THEN 1 ELSE 0 END,
            CASE WHEN v_col = 'car' THEN 1 ELSE 0 END,
            CASE WHEN v_col = 'bus' THEN 1 ELSE 0 END,
            CASE WHEN v_col = 'truck' THEN 1 ELSE 0 END,
            1)
    ON CONFLICT (camera_id, month, year) DO UPDATE
    SET 
        motorcycle = vehicle_statistics_monthly.motorcycle + EXCLUDED.motorcycle,
        car = vehicle_statistics_monthly.car + EXCLUDED.car,
        bus = vehicle_statistics_monthly.bus + EXCLUDED.bus,
        truck = vehicle_statistics_monthly.truck + EXCLUDED.truck,
        total = vehicle_statistics_monthly.total + EXCLUDED.total;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_vehicle_detection_inserted
AFTER INSERT ON vehicle_detections
FOR EACH ROW
EXECUTE FUNCTION update_vehicle_statistics();