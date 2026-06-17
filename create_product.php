<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

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

// PASTIKAN SEMUA DATA DI CAST KE TIPE YANG BENAR
$user_id = isset($input['user_id']) ? intval($input['user_id']) : 0;
$name = isset($input['name']) ? trim($input['name']) : '';
$price = isset($input['price']) ? floatval($input['price']) : 0;
$description = isset($input['description']) ? $input['description'] : '';
$stock = isset($input['stock']) ? intval($input['stock']) : 0;
$image_url = isset($input['image_url']) ? $input['image_url'] : '';
$category = isset($input['category']) ? $input['category'] : '';

// DEBUG - log data yang diterima
error_log("=== CREATE PRODUCT ===");
error_log("user_id: $user_id (type: " . gettype($user_id) . ")");
error_log("name: $name");
error_log("price: $price");
error_log("stock: $stock");

// CEK USER ID - PASTIKAN ANGKA
if ($user_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "User ID tidak valid: $user_id"
    ]);
    exit;
}

// CEK APAKAH USER ADA
$cekUser = $conn->query("SELECT id FROM users WHERE id = $user_id");
if ($cekUser->num_rows == 0) {
    echo json_encode([
        "status" => "error",
        "message" => "User dengan ID $user_id tidak ditemukan"
    ]);
    exit;
}

// CEK DATA WAJIB
if (empty($name)) {
    echo json_encode([
        "status" => "error",
        "message" => "Nama produk wajib diisi"
    ]);
    exit;
}

if ($price <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Harga harus lebih dari 0"
    ]);
    exit;
}

// ESCAPE STRING
$name = $conn->real_escape_string($name);
$description = $conn->real_escape_string($description);
$image_url = $conn->real_escape_string($image_url);
$category = $conn->real_escape_string($category);

// QUERY INSERT
$query = "INSERT INTO products (user_id, name, price, description, stock, image_url, category) 
          VALUES ($user_id, '$name', $price, '$description', $stock, '$image_url', '$category')";

error_log("Query: $query");

if ($conn->query($query)) {
    $product_id = $conn->insert_id;
    $result = $conn->query("SELECT * FROM products WHERE id = $product_id");
    $product = $result->fetch_assoc();
    
    echo json_encode([
        "status" => "success",
        "message" => "Produk berhasil ditambahkan",
        "data" => $product
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal insert: " . $conn->error
    ]);
}

$conn->close();
?>