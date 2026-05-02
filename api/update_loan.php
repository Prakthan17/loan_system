<?php
header("Content-Type: application/json");
include "db.php";

// NIC Convert function
function convertNIC($nic){
    $nic = strtoupper(trim($nic));
    $nic = preg_replace("/[^0-9VX]/", "", $nic);
    if(preg_match("/^\d{12}$/",$nic)) return $nic;
    if(preg_match("/^\d{9}[VX]$/",$nic)){
        $digits = substr($nic,0,9);
        $year = substr($digits,0,2);
        $prefix = ((int)$year > 30) ? "19" : "20";
        $firstPart = substr($digits,2,3);
        $lastPart  = substr($digits,5);
        return $prefix.$year.$firstPart."0".$lastPart;
    }
    return false;
}

// Get POST data
$id               = $_POST['id'] ?? '';
$guarantor1_nic   = $_POST['guarantor1_nic'] ?? '';
$guarantor2_nic   = $_POST['guarantor2_nic'] ?? '';
$recovery_officer = $_POST['recovery_officer'] ?? '';
$status           = $_POST['status'] ?? '';
$rating           = $_POST['rating'] ?? '';
$loan_date        = $_POST['loan_date'] ?? '';
$duration         = $_POST['loan_duration'] ?? '';
$closing_date = $_POST['closing_date'] ?? null;

// Validate
if($id === '' || empty($loan_date) || empty($duration)){
    echo json_encode(["status"=>"error","msg"=>"ID, Loan Date and Duration are required"]);
    exit;
}

// Convert NICs
$g1 = $guarantor1_nic ? convertNIC($guarantor1_nic) : '';
$g2 = $guarantor2_nic ? convertNIC($guarantor2_nic) : '';
if($g1 === false){ echo json_encode(["status"=>"error","msg"=>"Guarantor 1 NIC invalid"]); exit; }
if($g2 === false){ echo json_encode(["status"=>"error","msg"=>"Guarantor 2 NIC invalid"]); exit; }

// Recalculate end_date
$duration = intval($duration);
$date = new DateTime($loan_date);
$date->modify("+$duration months");
$end_date = $date->format("Y-m-d");

// Update loan
$stmt = $conn->prepare("
UPDATE loans SET 
guarantor1_nic=?, 
guarantor2_nic=?, 
recovery_officer=?, 
status=?,
closing_date=?,  
rating=?, 
loan_date=?, 
duration=?, 
end_date=? 
WHERE id=?
");

$stmt->bind_param(
    "ssssssssii",
    $g1,
    $g2,
    $recovery_officer,
    $status,
    $closing_date,
    $rating,
    $loan_date,
    $duration,
    $end_date,
    $id
);

if($stmt->execute()){
    echo json_encode(["status"=>"success","end_date"=>$end_date]);
}else{
    echo json_encode(["status"=>"error","msg"=>$stmt->error]);
}

$stmt->close();
$conn->close();
?>