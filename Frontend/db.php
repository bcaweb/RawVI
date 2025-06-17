<?php
$conn = new mysqli("localhost", "root", "", "rawvi");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
