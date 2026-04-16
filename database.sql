
-- ============================================================
-- ADDIM 1: Databaza yarat 
-- Əvvəlcə bu Databazani seç
-- ============================================================
CREATE DATABASE IF NOT EXISTS cyberauth_db
  CHARACTER SET utf8mb4          -- Azərbaycan hərflərini dəstəkləyir
  COLLATE utf8mb4_unicode_ci;    -- Böyük/kiçik hərf həssaslığı

-- Bu databazani istifadə et
USE cyberauth_db;

-- ============================================================
-- ADDIM 2: İSTİFADƏÇİ CƏDVƏLİ (users table)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (

    -- id: Hər istifadəçinin unikal nömrəsi
    -- AUTO_INCREMENT — avtomatik artar (1, 2, 3 ...)
    -- PRIMARY KEY — cədvəlin əsas açarı, dublikat olmaz
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- username: İstifadəçi adı
    -- VARCHAR(50) — maksimum 50 simvol
    -- NOT NULL — boş ola bilməz
    -- UNIQUE — eyni username 2 nəfərdə olmaz
    username VARCHAR(50) NOT NULL UNIQUE,

    -- email: E-poçt ünvanı
    -- VARCHAR(100) — maksimum 100 simvol
    -- UNIQUE — eyni email 2 nəfərdə olmaz
    email VARCHAR(100) NOT NULL UNIQUE,

    -- password: Şifrə
    -- vulnerable_server.php-də: açıq mətn saxlanılır (TƏHLÜKƏLİ!)
    -- secure_server.php-də: bcrypt hash saxlanılır (uzunluq ~60 simvol)
    -- VARCHAR(255) — hash üçün kifayət qədər yer
    password VARCHAR(255) NOT NULL,

    -- created_at: Qeydiyyat tarixi
    -- TIMESTAMP — tarix-vaxt formatı
    -- DEFAULT CURRENT_TIMESTAMP — avtomatik indi olan vaxtı yazır
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ADDIM 3: TEST MƏLUMATLARI (İSTƏĞƏ BAĞLI)
-- 
-- ⚠️  vulnerable_server.php üçün: Şifrə açıq mətn
-- Bu sətri yalnız test üçün istifadə et:
-- ============================================================

-- Test istifadəçisi — vulnerable server üçün (şifrə: 1234)
-- INSERT INTO users (username, email, password)
-- VALUES ('admin', 'admin@test.com', '1234');

-- ============================================================
-- ✅  secure_server.php üçün: Şifrə BCRYPT hash
-- PHP-dən əldə edilmiş hash (şifrə: 1234)
-- Real hash: password_hash('1234', PASSWORD_BCRYPT)
-- ============================================================

-- Test istifadəçisi — secure server üçün (şifrə: 1234)
-- INSERT INTO users (username, email, password)
-- VALUES ('admin', 'admin@test.com', '$2y$10$ORNEK_HASH_BURAYA_YAZILIR');

-- ============================================================
-- ADDIM 4: FAYDALIYOXLAMALAR
-- Bu sorğularla veritabanı vəziyyətini yoxlaya bilərsən
-- ============================================================

-- Bütün istifadəçiləri gör:
-- SELECT id, username, email, password, created_at FROM users;

-- Cədvəl strukturuna bax:
-- DESCRIBE users;

-- Bütün məlumatları sil (sıfırlamaq üçün):
-- TRUNCATE TABLE users;

-- Veritabanını tamamilə sil (nəzərdən keçir!):
-- DROP DATABASE cyberauth_db;
