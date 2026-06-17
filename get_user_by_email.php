<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "laparapp_db";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Koneksi gagal: " . $conn->connect_error]);
    exit;
}

$email = isset($_GET['email']) ? $conn->real_escape_string($_GET['email']) : '';

if (empty($email)) {
    echo json_encode(["status" => "error", "message" => "Email diperlukan"]);
    exit;
}

$query = "SELECT * FROM users WHERE email = '$email'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode([
        "status" => "success",
        "data" => [
            "id" => intval($user["id"]),
            "email" => $user["email"],
            "nama" => $user["nama"]
        ]
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "User tidak ditemukan"
    ]);
}

$conn->close();
?>