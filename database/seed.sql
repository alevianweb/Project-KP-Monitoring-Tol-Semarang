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
'https://livepantau.semarangkota.go.id/b7bec27d-d8de-46d7-b3af-0afa0722fc09/index.m3u8'
),

(
'Krapyak',
'Gerbang Tol Krapyak',
-6.9904119,
110.3690956,
ST_SetSRID(ST_MakePoint(110.3690956,-6.9904119),4326),
'https://livepantau.semarangkota.go.id/a3566012-8c41-4c09-9202-ff756b976d30/index.m3u8'
),

(
'Jatingaleh',
'Gerbang Tol Jatingaleh',
-7.0313342,
110.4211630,
ST_SetSRID(ST_MakePoint(110.4211630,-7.0313342),4326),
'https://livepantau.semarangkota.go.id/311aa266-c21b-4810-b37f-883a85d6b7ec/index.m3u8'
),

(
'Banyumanik',
'Gerbang Tol Banyumanik',
-7.0657095,
110.4317426,
ST_SetSRID(ST_MakePoint(110.4317426,-7.0657095),4326),
'https://livepantau.semarangkota.go.id/327fa649-61e9-4e77-815f-4708ccebd9f9/index.m3u8'
),

(
'Tembalang',
'Gerbang Tol Tembalang',
-7.0496939,
110.4335102,
ST_SetSRID(ST_MakePoint(110.4335102,-7.0496939),4326),
'https://livepantau.semarangkota.go.id/89cfecce-7f7c-4ee8-8c1a-1ebf7afd642d/index.m3u8'
);
