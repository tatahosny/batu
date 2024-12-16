<?php
session_start();
require_once 'db_connection.php';

// تحقق من أن المستخدم هو المسؤول
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}

// معالجة النموذج عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // تشفير كلمة المرور
    $role = $_POST['role'];
    $allowed_tables = isset($_POST['tables']) ? $_POST['tables'] : [];

    // إضافة المستخدم إلى قاعدة البيانات
    $query = "INSERT INTO users (email, password, role) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $email, $password, $role);
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        // إضافة الجداول المسموح بها
        foreach ($allowed_tables as $table_name) {
            $permission_query = "INSERT INTO user_permissions (user_id, table_name) VALUES (?, ?)";
            $permission_stmt = $conn->prepare($permission_query);
            $permission_stmt->bind_param("is", $user_id, $table_name);
            $permission_stmt->execute();
        }

        $success_message = "تم إضافة المستخدم بنجاح!";
    } else {
        $error_message = "حدث خطأ أثناء إضافة المستخدم.";
    }
}

// جلب قائمة الجداول من قاعدة البيانات
$tables_result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $tables_result->fetch_array()) {
    $tables[] = $row[0];
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة مستخدم جديد</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">إضافة مستخدم جديد</h2>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?= $success_message ?></div>
    <?php elseif (isset($error_message)): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">البريد الإلكتروني</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">كلمة المرور</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">الدور</label>
            <select name="role" id="role" class="form-select" required>
                <option value="user">مستخدم</option>
                <option value="admin">مسؤول</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="tables" class="form-label">الجداول المسموح الوصول إليها</label>
            <select name="tables[]" id="tables" class="form-select" multiple>
                <?php foreach ($tables as $table): ?>
                    <option value="<?= $table ?>"><?= $table ?></option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted">اضغط مع الاستمرار على CTRL لاختيار أكثر من جدول.</small>
        </div>

        <button type="submit" class="btn btn-primary">إضافة المستخدم</button>
    </form>
</div>
</body>
</html>