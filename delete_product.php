<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

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

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if ($input === null) {
    $input = $_POST;
}

$id = isset($input['id']) ? intval($input['id']) : 0;

if ($id <= 0) {
    echo json_encode(["status" => "error", "message" => "ID produk tidak valid"]);
    exit;
}

$query = "DELETE FROM products WHERE id = $id";

if ($conn->query($query)) {
    echo json_encode([
        "status" => "success",
        "message" => "Produk berhasil dihapus"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal hapus: " . $conn->error
    ]);
}

$conn->close();
?>