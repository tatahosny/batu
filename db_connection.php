<?php
$host = 'sql209.infinityfree.com';  // أو عنوان الاستضافة
$username = 'if0_37754829';  // اسم المستخدم في قاعدة البيانات
$password = '9Wj3quX1AGP';  // كلمة المرور لقاعدة البيانات (إذا كانت فارغة اتركها كما هي)
$database = 'if0_37754829_users_db';  // اسم قاعدة البيانات

// الاتصال بقاعدة البيانات
$conn = new mysqli($host, $username, $password, $database);
    // تعيين الترميز إلى UTF-8
$conn->set_charset("utf8");
// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}
?>
