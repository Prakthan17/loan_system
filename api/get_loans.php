<?php
header("Content-Type: application/json");
include "db.php";

$result = $conn->query("SELECT * FROM loans ORDER BY id DESC");

$data = [];

while($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);
