<?php
// ============================================================
// vulnerable_server.php — SQL İNJECTION BOŞLUĞU OLAN FAYL
// ⚠️  BU FAYL YALNIZ TƏHSİL MƏQSƏDİLƏ YAZILMIŞ!
// ⚠️  REAL PROYEKTDƏ BU KODU İSTİFADƏ ETMƏYİN!
// ============================================================

// ============================================================
// VERİTABANI BAĞLANTISI
// mysqli_connect(server, user, password, database)
// XAMPP/WAMP üçün default dəyərlər istifadə edilir
// ============================================================
$conn = mysqli_connect("localhost", "root", "", "cyberauth_db");

// Bağlantı xətasını yoxla
if (!$conn) {
    die("Bağlantı xətası: " . mysqli_connect_error());
}

// ============================================================
// HANSİ ƏMƏLIYYAT OLDUĞUNU ÖYRƏN
// HTML formdakı hidden input: name="action"
// ============================================================
$action = $_POST['action'] ?? '';

// ============================================================
// QEYDİYYAT — REGISTER
// ============================================================
if ($action === 'register') {

    // İstifadəçinin daxil etdiyi məlumatları oxu
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];

    // ⚠️  BOŞLUQ #1: Şifrə açıq mətn kimi saxlanılır!
    // Düzgün olsaydı: $password = password_hash($password, PASSWORD_BCRYPT);
    // Amma biz burada QƏSDƏN şifrəni hash etmirik.

    // ⚠️  BOŞLUQ #2: SQL INJECTION — məlumatlar birbaşa sorğuya yapışdırılır!
    // Hücumçu username sahəsinə belə mətn daxil edə bilər:
    //   test', 'hack@evil.com', 'pass123') -- 
    // Bu, sorğunun strukturunu dəyişdirir!
    $sql = "INSERT INTO users (username, email, password)
            VALUES ('$username', '$email', '$password')";

    // Sorğunu icra et
    if (mysqli_query($conn, $sql)) {
        // Uğurlu qeydiyyatdan sonra login səhifəsinə yönləndir
        header("Location: login.html?msg=Qeydiyyat+ugurlu!&status=ok");
    } else {
        // Xəta — SQL sintaksis xətası da buraya düşür
        // ⚠️  BOŞLUQ #3: Xəta mesajı istifadəçiyə göstərilir!
        // Bu, hücumçuya veritabanı strukturu haqqında məlumat verir.
        header("Location: login.html?msg=" . urlencode(mysqli_error($conn)) . "&status=err");
    }
}

// ============================================================
// GİRİŞ — LOGIN
// ============================================================
elseif ($action === 'login') {

    $username = $_POST['username'];
    $password = $_POST['password'];

    // ⚠️  BOŞLUQ #4: ANA SQL INJECTION HƏSSASLIĞI!
    //
    // Bu sorğu istifadəçi daxil etdiyi məlumatları BİRBAŞA birləşdirir.
    //
    // Normal istifadə:
    //   username = "ali"
    //   password = "1234"
    //   Sorğu → SELECT * FROM users WHERE username='ali' AND password='1234'
    //
    // Hücum nümunəsi (Classic SQLi bypass):
    //   username = ' OR '1'='1' --
    //   password = (istənilən şey)
    //   Sorğu → SELECT * FROM users WHERE username='' OR '1'='1' --' AND password='...'
    //   '1'='1' HƏMİŞƏ DOĞRUDUR, -- isə qalan hissəni şərh edir.
    //   Nəticə: Şifrəsiz daxil olur!
    //
    // Digər hücum nümunəsi (admin olaraq daxil ol):
    //   username = admin' --
    //   password = (istənilən şey)
    //   Sorğu → SELECT * FROM users WHERE username='admin' --' AND password='...'
    //   password yoxlaması tamamilə atlanır!

    $sql = "SELECT * FROM users
            WHERE username = '$username'
            AND password = '$password'";

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        // ⚠️  BOŞLUQ #5: Session istifadə edilmir, token yoxdur
        // Real sistemdə session_start() + $_SESSION lazımdır
        $user = mysqli_fetch_assoc($result);
        echo "<h2 style='color:lime;font-family:monospace'>
                ✅ XOŞ GƏLDİN, " . $user['username'] . "!<br>
                <small>ID: " . $user['id'] . " | Email: " . $user['email'] . "</small>
              </h2>";
        // ⚠️  BOŞLUQ #6: İstifadəçi məlumatları birbaşa echo ilə göstərilir
        // Bu XSS (Cross-Site Scripting) hücumuna yol açır
    } else {
        header("Location: login.html?msg=Yanlis+istifadeci+adi+ve+ya+sifre&status=err");
    }
}

// ============================================================
// BAĞLANTINI BAG
// ============================================================
mysqli_close($conn);
?>
