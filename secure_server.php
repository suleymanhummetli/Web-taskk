<?php
// ============================================================
// secure_server.php — SQL INJECTION QARŞI TƏHLÜKƏSİZ VERSİYA
// ✅ Bütün boşluqlar bağlanmışdır
// ✅ Bu faylı real proyektdə istifadə edin
// ============================================================

// ============================================================
// VERİTABANI BAĞLANTISI — PDO (PHP Data Objects)
// PDO, mysqli-dən daha güclü və təhlükəsizdir.
// Prepared Statement dəstəyi var.
// ============================================================
$host   = "localhost";
$dbname = "cyberauth_db";
$user   = "root";
$pass   = "";

try {
    // PDO bağlantısı qur
    // charset=utf8mb4 — Azərbaycan hərfləri üçün vacibdir
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            // ✅ FİX #1: Xətalarda istisna (exception) atacaq
            // vulnerable versiyada xəta birbaşa göstərilirdi
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

            // ✅ FİX #2: Nəticələr assoc array kimi gəlir
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    // ✅ FİX #3: Xəta mesajını istifadəçiyə GÖSTƏRMƏ!
    // Loga yaz, amma ekrana çıxarma (hücumçu məlumat ala bilər)
    error_log("DB Xətası: " . $e->getMessage()); // Server loguna yaz
    die("Sistem xətası. Zəhmət olmasa bir az sonra cəhd edin.");
}

// Əməliyyat növünü oxu
$action = $_POST['action'] ?? '';

// ============================================================
// QEYDİYYAT — SECURE REGISTER
// ============================================================
if ($action === 'register') {

    // Məlumatları oxu
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    // ✅ FİX #4: GİRİŞ VALİDASİYASI
    // Boş sahələr olmamalıdır
    if (empty($username) || empty($email) || empty($password)) {
        header("Location: login.html?msg=Butun+saheleri+doldurun&status=err");
        exit;
    }

    // ✅ FİX #5: Email formatını yoxla
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: login.html?msg=Email+formati+yanlisdir&status=err");
        exit;
    }

    // ✅ FİX #6: Şifrə minimum uzunluq
    if (strlen($password) < 6) {
        header("Location: login.html?msg=Sifre+en+az+6+simvol+olmalidir&status=err");
        exit;
    }

    // ✅ FİX #7: ŞİFRƏNİ HASH ET!
    // password_hash() — PHP-nin daxili, güclü hash funksiyası
    // BCRYPT algoritmi istifadə edir, avtomatik salt əlavə edir
    // Hücumçu veritabanını əldə etsə belə şifrəni tapa bilməz
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // ✅ FİX #8: PREPARED STATEMENT — SQL Injection mümkün deyil!
    // :username, :email, :password — placeholder-lər
    // İstifadəçi məlumatı SQL sorğusuna qarışmır, ayrıca göndərilir.
    // Hücumçu ' OR '1'='1 yazsa belə, bu sadəcə string kimi saxlanılır.
    $stmt = $pdo->prepare(
        "INSERT INTO users (username, email, password)
         VALUES (:username, :email, :password)"
    );

    // Placeholder-lərə dəyər ver (bind)
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':email',    $email,    PDO::PARAM_STR);
    $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

    try {
        $stmt->execute();
        header("Location: login.html?msg=Qeydiyyat+ugurlu!&status=ok");
    } catch (PDOException $e) {
        // Duplicate username/email xətası (UNIQUE constraint)
        if ($e->getCode() === '23000') {
            header("Location: login.html?msg=Bu+istifadeci+adi+artiq+movcuddur&status=err");
        } else {
            error_log("Register xətası: " . $e->getMessage());
            header("Location: login.html?msg=Sistem+xetasi&status=err");
        }
    }
}

// ============================================================
// GİRİŞ — SECURE LOGIN
// ============================================================
elseif ($action === 'login') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password']      ?? '';

    // Boş yoxlaması
    if (empty($username) || empty($password)) {
        header("Location: login.html?msg=Butun+saheleri+doldurun&status=err");
        exit;
    }

    // ✅ FİX #9: PREPARED STATEMENT ilə sorğu
    // Yalnız username-i sorğula; şifrəni PHP tərəfindən yoxlayacağıq
    // (veritabanında şifrə hash kimi saxlanılıb, birbaşa müqayisə olmur)
    $stmt = $pdo->prepare(
        "SELECT id, username, email, password
         FROM users
         WHERE username = :username
         LIMIT 1"
    );
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(); // Nəticəni oxu

    // ✅ FİX #10: password_verify() ilə şifrəni yoxla
    // Bu funksiya, daxil edilən şifrəni veritabanındakı hash ilə müqayisə edir.
    // Hətta eyni şifrə 2 dəfə hash edilsə belə, verify() hər ikisini tanıyır (salt sayəsində).
    if ($user && password_verify($password, $user['password'])) {

        // ✅ FİX #11: SESSION istifadə et
        // İstifadəçi məlumatlarını URL-də göndərmə, session-da saxla
        session_start();
        session_regenerate_id(true); // Session fixation hücumunu önlə

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['logged_in'] = true;

        // ✅ FİX #12: Məlumatı göstərərkən XSS-i önlə
        // htmlspecialchars() — HTML teqlərini zərərsizləşdirir
        $safeUsername = htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8');
        echo "<h2 style='color:lime;font-family:monospace'>
                ✅ XOŞ GƏLDİN, $safeUsername!
              </h2>";
        // Qeyd: Həssas məlumatlar (ID, email) artıq göstərilmir

    } else {
        // ✅ FİX #13: Eyni xəta mesajı — username yanlış mı, şifrə yanlış mı?
        // Bunu ayırd etmə! Hücumçu hansı istifadəçi adının mövcud olduğunu öyrənə bilər.
        header("Location: login.html?msg=Istifadeci+adi+veya+sifre+yanlisdir&status=err");
    }
}

// Bağlantı PDO-da avtomatik bağlanır, amma bunu da aydın göstərək
$pdo = null;
?>
