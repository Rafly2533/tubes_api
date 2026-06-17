<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
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
$name = isset($input['name']) ? trim($input['name']) : '';
$price = isset($input['price']) ? floatval($input['price']) : 0;
$description = isset($input['description']) ? $input['description'] : '';
$stock = isset($input['stock']) ? intval($input['stock']) : 0;
$image_url = isset($input['image_url']) ? $input['image_url'] : '';
$category = isset($input['category']) ? $input['category'] : '';

if ($id <= 0) {
    echo json_encode(["status" => "error", "message" => "ID produk tidak valid"]);
    exit;
}

if (empty($name)) {
    echo json_encode(["status" => "error", "message" => "Nama produk wajib diisi"]);
    exit;
}

if ($price <= 0) {
    echo json_encode(["status" => "error", "message" => "Harga harus lebih dari 0"]);
    exit;
}

$name = $conn->real_escape_string($name);
$description = $conn->real_escape_string($description);
$image_url = $conn->real_escape_string($image_url);
$category = $conn->real_escape_string($category);

$query = "UPDATE products SET 
            name = '$name',
            price = $price,
            description = '$description',
            stock = $stock,
            image_url = '$image_url',
            category = '$category'
          WHERE id = $id";

if ($conn->query($query)) {
    $result = $conn->query("SELECT * FROM products WHERE id = $id");
    $product = $result->fetch_assoc();
    
    echo json_encode([
        "status" => "success",
        "message" => "Produk berhasil diupdate",
        "data" => $product
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal update: " . $conn->error
    ]);
}

$conn->close();
?>