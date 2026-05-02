<?php
header("Content-Type: application/json");
include "db.php";

$id     = $_POST['id'] ?? '';
$status = $_POST['status'] ?? '';
$rating = $_POST['rating'] ?? '';
$name   = $_POST['name'] ?? '';
$branch = $_POST['branch'] ?? '';
$amount = $_POST['amount'] ?? '';

if($id===''){
    echo json_encode(["status"=>"error","msg"=>"Missing ID"]);
    exit;
}

// Update all editable fields
$stmt = $conn->prepare(
    "UPDATE loans 
     SET customer_name=?, branch=?, amount=?, status=?, rating=?
     WHERE id=?"
);

$stmt->bind_param("ssdsis", $name, $branch, $amount, $status, $rating, $id);

if($stmt->execute()){
    echo json_encode(["status"=>"success"]);
}else{
    echo json_encode(["status"=>"error","msg"=>$conn->error]);
}
?>
