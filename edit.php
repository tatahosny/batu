<?php
session_start();
require_once 'db_connection.php';

// التحقق من صلاحيات المسؤول
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}

$table = $_GET['table'];
$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = [];
    foreach ($_POST as $column => $value) {
        if ($column != 'table' && $column != 'id') {
            $updates[] = "$column = '$value'";
        }
    }

    $updatesStr = implode(", ", $updates);
    $query = "UPDATE $table SET $updatesStr WHERE id = $id";
    
    if ($conn->query($query)) {
        header("Location: view_table.php?table=$table");
        exit();
    } else {
        $error = "حدث خطأ أثناء تعديل السجل.";
    }
}

$query = "SELECT * FROM $table WHERE id = $id";
$result = $conn->query($query);
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل السجل</title>
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group input {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>تعديل السجل في الجدول: <?= htmlspecialchars($table) ?></h2>

        <form action="edit.php?table=<?= $table ?>&id=<?= $id ?>" method="POST">
            <?php
            // عرض الحقول الخاصة بالجدول ليقوم المسؤول بتعديلها
            $query = "DESCRIBE $table";
            $result = $conn->query($query);
            while ($field = $result->fetch_assoc()) {
                if ($field['Key'] !== 'PRI') {  // تجاهل العمود الأساسي
                    echo "<div class='form-group'>";
                    echo "<label>{$field['Field']}</label>";
                    echo "<input type='text' name='{$field['Field']}' value='" . htmlspecialchars($row[$field['Field']]) . "' required>";
                    echo "</div>";
                }
            }
            ?>
            <input type="hidden" name="id" value="<?= $id ?>">
            <button type="submit">تعديل</button>
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
