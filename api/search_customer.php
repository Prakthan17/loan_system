<?php
header("Content-Type: application/json");
include "db.php";

$nic        = $_GET['nic'] ?? '';
$name       = $_GET['name'] ?? '';
$branch     = $_GET['branch'] ?? '';
$amount     = $_GET['amount'] ?? '';
$status     = $_GET['status'] ?? '';
$created_at = $_GET['created_at'] ?? '';
$loan_date  = $_GET['loan_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';

// Base query
$sql = "SELECT * FROM loans WHERE 1=1";
$params = [];
$types = "";

// NIC filter
if($nic!==''){
    $sql .= " AND (nic LIKE ? OR guarantor1_nic LIKE ? OR guarantor2_nic LIKE ?)";
    $likeNic = "%$nic%";
    $params[] = $likeNic;
    $params[] = $likeNic;
    $params[] = $likeNic;
    $types .= "sss";
}

// Name filter
if($name!==''){
    $sql .= " AND customer_name LIKE ?";
    $params[] = "%$name%";
    $types .= "s";
}

// Branch filter
if($branch!==''){
    $sql .= " AND branch LIKE ?";
    $params[] = "%$branch%";
    $types .= "s";
}

// Amount filter
if($amount!==''){
    $sql .= " AND amount = ?";
    $params[] = $amount;
    $types .= is_numeric($amount) ? "d" : "s";
}

// Status filter
if($status!==''){
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

// Created date filter
if($created_at!==''){
    $sql .= " AND DATE(created_at) = ?";
    $params[] = $created_at;
    $types .= "s";
}

// Loan date filter
if($loan_date!==''){
    $sql .= " AND DATE(loan_date) = ?";
    $params[] = $loan_date;
    $types .= "s";
}

// End date filter
if($end_date!==''){
    $sql .= " AND DATE(end_date) = ?";
    $params[] = $end_date;
    $types .= "s";
}

// Order by latest
$sql .= " ORDER BY id DESC";

// Prepare statement
$stmt = $conn->prepare($sql);
if($stmt === false){
    echo json_encode(['records'=>[], 'error'=>$conn->error]);
    exit;
}

// Bind params dynamically
if(!empty($params)){
    $stmt->bind_param($types, ...$params);
}

// Execute
$stmt->execute();
$res = $stmt->get_result();
$records = $res->fetch_all(MYSQLI_ASSOC);

echo json_encode(['records'=>$records]);
?>