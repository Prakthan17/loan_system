<?php
header("Content-Type: application/json");
include __DIR__ . "/db.php";

// Get POST data
$username = $_POST['username'] ?? '';
$old      = $_POST['old'] ?? '';
$new      = $_POST['new'] ?? '';

if (!$username || !$old || !$new) {
    echo json_encode(["status" => "error", "msg" => "All fields are required"]);
    exit;
}

// Hash passwords with MD5 (since your login uses MD5)
$old_hash = md5($old);
$new_hash = md5($new);

// Check if old password is correct
$sql = "SELECT id FROM admins WHERE username='$username' AND password='$old_hash' LIMIT 1";
$result = $conn->query($sql);

if (!$result || $result->num_rows !== 1) {
    echo json_encode(["status" => "error", "msg" => "Old password is incorrect"]);
    exit;
}

// Update to new password
$update = "UPDATE admins SET password='$new_hash' WHERE username='$username'";
if ($conn->query($update)) {
    echo json_encode(["status" => "success", "msg" => "Password updated successfully"]);
} else {
    echo json_encode(["status" => "error", "msg" => "Failed to update password"]);
}

?>
