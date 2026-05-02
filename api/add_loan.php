<?php
header("Content-Type: application/json");
include "db.php";

/* NIC Convert / Validate */
function convertNIC($nic){
    $nic = strtoupper(trim($nic));
    $nic = preg_replace("/[^0-9VX]/", "", $nic);

    $oldPattern = "/^\d{9}[VX]$/";
    $newPattern = "/^\d{12}$/";

    if(preg_match($newPattern, $nic)) return $nic;

    if(preg_match($oldPattern, $nic)){
        $digits = substr($nic, 0, 9);
        $year = substr($digits, 0, 2);
        $prefix = ((int)$year > 30) ? "19" : "20";
        $firstPart = substr($digits, 2, 3);
        $lastPart  = substr($digits, 5);
        return $prefix . $year . $firstPart . "0" . $lastPart;
    }

    return false;
}

/* GET POST DATA */
$nic        = $_POST['nic'] ?? '';
$name       = $_POST['name'] ?? '';
$branch     = $_POST['branch'] ?? '';
$amount     = $_POST['amount'] ?? '';
$loan_date  = $_POST['loan_date'] ?? '';
$duration   = $_POST['loan_duration'] ?? '';
$end_date   = $_POST['end_date'] ?? '';
$g1         = $_POST['guarantor1_nic'] ?? '';
$g2         = $_POST['guarantor2_nic'] ?? '';
$recovery   = $_POST['recovery_officer'] ?? '';

/* VALIDATION */
if(!$nic || !$name || !$branch || !$amount || !$loan_date || !$duration || !$end_date){
    echo json_encode(["status"=>"error","message"=>"All fields are required"]);
    exit;
}

/* CONVERT NICs */
$nic = convertNIC($nic);
$g1  = $g1 ? convertNIC($g1) : '';
$g2  = $g2 ? convertNIC($g2) : '';

if(!$nic){ echo json_encode(["status"=>"error","message"=>"Customer NIC invalid"]); exit; }
if($g1 === false){ echo json_encode(["status"=>"error","message"=>"Guarantor 1 NIC invalid"]); exit; }
if($g2 === false){ echo json_encode(["status"=>"error","message"=>"Guarantor 2 NIC invalid"]); exit; }

/* CHECK ACTIVE CUSTOMER LOAN */
$stmt = $conn->prepare("SELECT branch FROM loans WHERE nic=? AND status='Active'");
$stmt->bind_param("s",$nic);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows > 0){
    $row = $res->fetch_assoc();
    echo json_encode(["status"=>"exists","branch"=>$row['branch']]);
    exit;
}
$stmt->close();

/* CHECK GUARANTOR LIMIT - max 3 active loans */
function checkGuarantorLimit($conn, $guarantor){
    if(!$guarantor) return false;
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM loans WHERE (guarantor1_nic=? OR guarantor2_nic=?) AND status='Active'");
    $stmt->bind_param("ss", $guarantor, $guarantor);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $res['cnt'] >= 3;
}

if(checkGuarantorLimit($conn, $g1)){
    echo json_encode(["status"=>"guarantor_limit","message"=>"Guarantor 1 already has 3 active loans"]);
    exit;
}

if(checkGuarantorLimit($conn, $g2)){
    echo json_encode(["status"=>"guarantor_limit","message"=>"Guarantor 2 already has 3 active loans"]);
    exit;
}

/* INSERT LOAN - all strings */
$stmt = $conn->prepare("
INSERT INTO loans 
(nic, customer_name, branch, amount, loan_date, duration, end_date, guarantor1_nic, guarantor2_nic, recovery_officer, status)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')
");

$stmt->bind_param(
    "ssssssssss", // all strings
    $nic,
    $name,
    $branch,
    $amount,
    $loan_date,
    $duration,
    $end_date,
    $g1,
    $g2,
    $recovery
);

if($stmt->execute()){
    echo json_encode(["status"=>"success","end_date"=>$end_date]);
}else{
    echo json_encode(["status"=>"error","message"=>$stmt->error]);
}

$stmt->close();
$conn->close();
?>