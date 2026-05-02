<?php
$conn = new mysqli(
  "sql100.infinityfree.com",  // hostname
  "if0_41318962",               // username
  "4ooXgYBIaOChAk",            // password
  "if0_41318962_loan"         // database
);

if ($conn->connect_error) {
  die("Connection failed");
}
?>
