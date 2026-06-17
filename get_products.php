<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// DEBUG - log user_id
error_log("=== GET PRODUCTS ===");
error_log("user_id: $user_id");

if ($user_id <= 0) {
    echo json_encode(["status" => "error", "message" => "user_id tidak valid: $user_id"]);
    exit;
}

$query = "SELECT * FROM products WHERE user_id = $user_id ORDER BY created_at DESC";
error_log("Query: $query");

$result = $conn->query($query);

if (!$result) {
    echo json_encode([
        "status" => "error",
        "message" => "Query error: " . $conn->error
    ]);
    exit;
}

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

error_log("Total products found: " . count($products));

echo json_encode([
    "status" => "success",
    "data" => $products
]);

$conn->close();
?>