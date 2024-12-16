<?php
session_start();
require_once 'db_connection.php';

// تحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_role = $_SESSION['role'];

// تحديد الجداول التي يمكن للمستخدم الوصول إليها
if ($user_role === 'admin') {
    $query = "SHOW TABLES";
    $result = $conn->query($query);
} else {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT table_name FROM user_permissions WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
}

$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}
?>
   <!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(to bottom, #6a11cb, #2575fc);
            margin: 0;
            padding: 0;
            color: #fff;
            text-align: center;
        }

        .dashboard {
            padding: 20px;
        }

        .logout-container {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .logout-btn, .add-user-btn {
            padding: 12px 24px;
            background-color: #ff4d4d;
            color: #fff;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
        }

        .logout-btn:hover, .add-user-btn:hover {
            background-color: #e63939;
            transform: scale(1.05);
        }

        .logout-btn:focus, .add-user-btn:focus {
            outline: none;
        }

        .add-user-container {
            position: absolute;
            top: 20px;
            left: 20px;
        }

        h2 {
            margin-bottom: 10px;
        }

        h3 {
            margin-bottom: 30px;
        }

        .table-list {
            list-style: none;
            padding: 0;
            max-width: 600px;
            margin: 0 auto;
        }

        .table-list li {
            background: rgba(255, 255, 255, 0.2);
            margin: 10px 0;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background 0.3s;
        }

        .table-list li:hover {
            background: rgba(255, 255, 255, 0.4);
        }

        .table-list a {
            text-decoration: none;
            color: #fff;
            font-weight: 500;
            flex: 1;
            text-align: left;
        }

        .table-list a:hover {
            text-decoration: underline;
        }

        .icon {
            font-size: 20px;
            color: #fff;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <!-- زر تسجيل الخروج -->
    <div class="logout-container">
        <form action="logout.php" method="POST">
            <button type="submit" class="logout-btn">تسجيل الخروج</button>
        </form>
    </div>

    <!-- زر إضافة مستخدمين جدد (يظهر فقط للمسؤول) -->
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <div class="add-user-container">
            <a href="add_user.php" class="add-user-btn">إضافة مستخدمين جدد</a>
        </div>
    <?php endif; ?>

    <div class="dashboard">
        <h2>لوحة التحكم</h2>
        <h3>مرحبًا، <?= $_SESSION['role'] === 'admin' ? 'المسؤول' : 'المستخدم' ?></h3>

        <ul class="table-list">
            <?php foreach ($tables as $table): ?>
                <li>
                    <i class="icon fas fa-database"></i>
                    <a href="view_table.php?table=<?= $table ?>"><?= $table ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
