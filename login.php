<?php
session_start();
require_once 'db_connection.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($email && $password) {
    // تحقق من صحة المستخدم
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // تحقق من كلمة المرور
        if (password_verify($password, $user['password'])) {
            // إنشاء جلسة وتخزين معلومات المستخدم
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "كلمة المرور غير صحيحة.";
        }
    } else {
        $error = "البريد الإلكتروني غير موجود.";
    }
} else {
    $error = "يرجى ملء جميع الحقول.";
}

if (isset($error)) {
    $_SESSION['error'] = $error;
    header("Location: login.html");
    exit();
}
