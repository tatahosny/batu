<?php
session_start();
require_once 'db_connection.php';

// التحقق من صلاحيات المسؤول
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}

$table = $_GET['table'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $columns = [];
    $values = [];
    foreach ($_POST as $column => $value) {
        if ($column != 'table') {
            $columns[] = $column;
            $values[] = "'$value'";
        }
    }

    $columnsStr = implode(", ", $columns);
    $valuesStr = implode(", ", $values);
    
    $query = "INSERT INTO $table ($columnsStr) VALUES ($valuesStr)";
    if ($conn->query($query)) {
        header("Location: view_table.php?table=$table");
        exit();
    } else {
        $error = "حدث خطأ أثناء إضافة السجل.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة سجل</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: 500;
            margin-bottom: 8px;
        }

        input[type="text"] {
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            color: #555;
        }

        input[type="text"]:focus {
            border-color: #007bff;
            outline: none;
        }

        button {
            padding: 14px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
        }

        .back-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .loading {
            text-align: center;
            font-size: 20px;
            color: #007bff;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>إضافة سجل جديد إلى الجدول: <?= htmlspecialchars($table) ?></h2>

        <form action="add.php?table=<?= $table ?>" method="POST">
            <?php
            // عرض الحقول الخاصة بالجدول ليقوم المسؤول بإدخالها
            $query = "DESCRIBE $table";
            $result = $conn->query($query);
            while ($row = $result->fetch_assoc()) {
                if ($row['Key'] !== 'PRI') {  // تجاهل العمود الأساسي
                    echo "<label>{$row['Field']}</label>";
                    echo "<input type='text' name='{$row['Field']}' required><br>";
                }
            }
            ?>
            <button type="submit">إضافة</button>
        </form>

        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <div class="back-link">
            <a href="view_table.php?table=<?= $table ?>">الرجوع إلى الجدول</a>
        </div>

        <div class="loading" id="loading">جاري التحميل...</div>
    </div>

    <script>
        // عرض الرسالة أثناء إرسال البيانات
        document.querySelector("form").addEventListener("submit", function() {
            document.getElementById("loading").style.display = "block";
        });
    </script>
</body>
</html>
