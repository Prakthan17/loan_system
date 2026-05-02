<?php
header("Content-Type: application/json");
include "db.php";

$id = $_POST['id'] ?? '';

if($id===''){
    echo json_encode(["status"=>"error","msg"=>"Missing ID"]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM loans WHERE id=?");
$stmt->bind_param("i", $id);

if($stmt->execute()){
    echo json_encode(["status"=>"success"]);
}else{
    echo json_encode(["status"=>"error","msg"=>$conn->error]);
}
?>
