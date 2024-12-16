<?php
require_once 'db_connection.php';

$email = 'tatahosny@gmail.com';
$password = password_hash('36200600', PASSWORD_DEFAULT);
$role = 'user';

$query = "INSERT INTO users (email, password, role) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $email, $password, $role);
$stmt->execute();
echo "Admin added!";
?>