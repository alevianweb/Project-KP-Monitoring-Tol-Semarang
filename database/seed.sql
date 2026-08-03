-- =====================================================
-- Seed Data CCTV Gerbang Tol Semarang
-- =====================================================

INSERT INTO cameras
(name, location, latitude, longitude, geom, video_source)
VALUES

(
'Kaligawe',
'Gerbang Tol Kaligawe',
-6.9731404,
110.4500822,
ST_SetSRID(ST_MakePoint(110.4500822,-6.9731404),4326),
'datasets/kaligawe.mp4'
),

(
'Krapyak',
'Gerbang Tol Krapyak',
-6.9904119,
110.3690956,
ST_SetSRID(ST_MakePoint(110.3690956,-6.9904119),4326),
'datasets/krapyak.mp4'
),

(
'Jatingaleh',
'Gerbang Tol Jatingaleh',
-7.0313342,
110.4211630,
ST_SetSRID(ST_MakePoint(110.4211630,-7.0313342),4326),
'datasets/jatingaleh.mp4'
),

(
'Banyumanik',
'Gerbang Tol Banyumanik',
-7.0657095,
110.4317426,
ST_SetSRID(ST_MakePoint(110.4317426,-7.0657095),4326),
'datasets/banyumanik.mp4'
),

(
'Tembalang',
'Gerbang Tol Tembalang',
-7.0496939,
110.4335102,
ST_SetSRID(ST_MakePoint(110.4335102,-7.0496939),4326),
'datasets/tembalang.mp4'
);