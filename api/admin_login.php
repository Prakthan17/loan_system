<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

include __DIR__ . "/db.php";

/* ---------- LOGIN DATA ---------- */

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    echo json_encode([
        "status" => "error",
        "msg" => "Username and password required"
    ]);
    exit;
}

/* ⚠️ password hash */
$password = md5($password);

$sql = "SELECT id FROM admins WHERE username='$username' AND password='$password' LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows === 1) {
    echo json_encode([
        "status" => "success"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "msg" => "Wrong username or password"
    ]);
}
?>
