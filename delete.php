<?php
session_start();
require_once 'db_connection.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}

$table = $_GET['table'];
$id = $_GET['id'];

$query = "DELETE FROM $table WHERE id = $id";
if ($conn->query($query)) {
    header("Location: view_table.php?table=$table");
    exit();
} else {
    echo "حدث خطأ أثناء حذف السجل.";
}
