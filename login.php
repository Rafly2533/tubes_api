<?php 
header("Content-Type: application/json"); 
 
// Konfigurasi koneksi ke MySQL 
$host = "localhost"; 
$user = "root";   // sesuaikan jika ada password XAMPP 
$pass = ""; 
$db   = "laparapp_db"; 
$conn = new mysqli($host, $user, $pass, $db); 
 
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
    echo json_encode(["status" => "error", "message" => "Token tidak valid!"]); 
} else { 
    // 3. Token asli — ambil data dari Google 
    $email = $conn->real_escape_string($userData["email"]); 
    $nama  = $conn->real_escape_string($userData["name"]); 
 
    // 4. Cek apakah user sudah pernah daftar 
    $cekUser = $conn->query("SELECT id FROM users WHERE email = '$email'"); 
 
    if ($cekUser->num_rows == 0) { 
        // Belum ada → insert data baru 
        $conn->query("INSERT INTO users (nama, email) VALUES ('$nama', '$email')"); 
        $pesan = "Akun baru berhasil didaftarkan di LaparApp!"; 
    } else { 
        // Sudah ada → login biasa 
        $pesan = "Selamat datang kembali!"; 
    } 
 
    echo json_encode(["status" => "success", "message" => $pesan]);

}