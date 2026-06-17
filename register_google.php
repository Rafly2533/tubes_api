<?php 
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Konfigurasi koneksi ke MySQL
$host = "localhost";
$user = "root";
$pass = "";
$db   = "laparapp_db";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal: " . $conn->connect_error]);
    exit;
}

// 1. Terima token yang dikirim dari Flutter
$idToken = $_POST["id_token"] ?? "";
if (empty($idToken)) {
    echo json_encode(["status" => "error", "message" => "Token kosong!"]);
    exit;
}

// 2. Verifikasi token ke Google
$url      = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $idToken;
$context  = stream_context_create(["http" => ["ignore_errors" => true]]);
$response = file_get_contents($url, false, $context);
$userData = json_decode($response, true);

if (isset($userData["error"])) {
    echo json_encode(["status" => "error", "message" => "Token tidak valid: " . $userData["error"]]);
    exit;
}

// 3. Token valid - ambil data dari Google
$email = $conn->real_escape_string($userData["email"]);
$nama  = $conn->real_escape_string($userData["name"]);

// 4. Cek apakah user sudah pernah daftar (berdasarkan email)
$cekUser = $conn->query("SELECT * FROM users WHERE email = '$email'");

if ($cekUser->num_rows > 0) {
    // User sudah ada
    $user = $cekUser->fetch_assoc();
    echo json_encode([
        "status" => "exists",
        "message" => "Akun sudah terdaftar. Silakan login.",
        "data" => [
            "id" => $user["id"],
            "email" => $user["email"],
            "nama" => $user["nama"]
        ]
    ]);
} else {
    // 5. User belum ada → REGISTER (tanpa password karena login Google)
    $query = "INSERT INTO users (nama, email) VALUES ('$nama', '$email')";
    
    if ($conn->query($query)) {
        $user_id = $conn->insert_id;
        $user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
        
        echo json_encode([
            "status" => "success",
            "message" => "Registrasi berhasil! Selamat datang di LaparApp!",
            "data" => [
                "id" => $user["id"],
                "email" => $user["email"],
                "nama" => $user["nama"]
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Gagal registrasi: " . $conn->error
        ]);
    }
}

$conn->close();
?>