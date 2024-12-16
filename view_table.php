<?php
session_start();
require_once 'db_connection.php';

$table = $_GET['table'];
$limit = 10;  // عدد السجلات التي سيتم عرضها في كل صفحة
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// البحث
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$whereClause = '';
if (!empty($searchQuery)) {
    $whereClause = "WHERE CONCAT_WS(' ', ";

    // الحصول على أسماء الأعمدة للبحث
    $columnsResult = $conn->query("SHOW COLUMNS FROM $table");
    $columns = [];
    while ($column = $columnsResult->fetch_assoc()) {
        $columns[] = $column['Field'];
    }
    $whereClause .= implode(', ', $columns) . ") LIKE '%" . $conn->real_escape_string($searchQuery) . "%'";
}

// الاستعلام الرئيسي
$query = "SELECT * FROM $table $whereClause LIMIT $offset, $limit";
$result = $conn->query($query);

// حساب إجمالي عدد السجلات في الجدول
$totalQuery = "SELECT COUNT(*) as total FROM $table $whereClause";
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalRecords = $totalRow['total'];
$totalPages = ceil($totalRecords / $limit);

// تحقق إذا كان المستخدم مسؤول
$isAdmin = $_SESSION['role'] === 'admin';
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض الجدول</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
      <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f4f7; /* لون الخلفية الهادئ */
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            background-color: #fff; /* خلفية بيضاء داخل الـ container */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* إضافة تأثير الظل */
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
            font-size: 24px;
        }

        table {
            width: 100%;
            margin: 20px 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .btn-custom {
            background-color: #007bff;
            color: white;
            border-radius: 5px;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }

        .pagination {
            justify-content: center;
            margin-top: 20px;
        }

        .pagination a {
            margin: 0 5px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .pagination a:hover {
            background-color: #0056b3;
        }

        .back-btn {
            margin: 20px 0;
            text-align: center;
        }

        .back-btn a {
            padding: 12px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-btn a:hover {
            background-color: #5a6268;
        }

        .loading {
            font-size: 18px;
            color: #007bff;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="loading text-center my-4">جاري تحميل البيانات...</div>

    <h2>الجدول: <?= htmlspecialchars($table) ?></h2>
<div class="mb-3 d-flex justify-content-between">
    <?php if ($isAdmin): ?>
        <a href="add.php?table=<?= $table ?>" class="btn btn-success btn-custom">إضافة سجل</a>
    <?php endif; ?>
    <a href="export.php?table=<?= $table ?>" class="btn btn-info btn-custom">تصدير إلى Excel</a>
</div>
    <!-- شريط البحث -->
    <form method="GET" action="" class="mb-4">
        <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="ابحث في الجدول" value="<?= htmlspecialchars($searchQuery) ?>">
            <button type="submit" class="btn btn-primary">بحث</button>
        </div>
    </form>

    <div class="back-btn">
        <a href="dashboard.php" class="btn btn-secondary">العودة إلى لوحة التحكم</a>
    </div>

    <?php if ($isAdmin): ?>
        <div class="mb-3">
            <a href="add.php?table=<?= $table ?>" class="btn btn-success btn-custom">إضافة سجل</a>
        </div>
    <?php endif; ?>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <?php while ($field = $result->fetch_field()): ?>
                    <th><?= htmlspecialchars($field->name) ?></th>
                <?php endwhile; ?>
                <?php if ($isAdmin): ?>
                    <th>الإجراءات</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <?php foreach ($row as $cell): ?>
                        <td><?= htmlspecialchars($cell) ?></td>
                    <?php endforeach; ?>
                    <?php if ($isAdmin): ?>
                        <td>
                            <a href="edit.php?table=<?= $table ?>&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">تعديل</a>
                            <a href="delete.php?table=<?= $table ?>&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm">حذف</a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="view_table.php?table=<?= $table ?>&page=1&search=<?= urlencode($searchQuery) ?>" class="btn btn-primary btn-sm">أولاً</a>
            <a href="view_table.php?table=<?= $table ?>&page=<?= $page - 1 ?>&search=<?= urlencode($searchQuery) ?>" class="btn btn-primary btn-sm">السابق</a>
        <?php endif; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="view_table.php?table=<?= $table ?>&page=<?= $page + 1 ?>&search=<?= urlencode($searchQuery) ?>" class="btn btn-primary btn-sm">التالي</a>
            <a href="view_table.php?table=<?= $table ?>&page=<?= $totalPages ?>&search=<?= urlencode($searchQuery) ?>" class="btn btn-primary btn-sm">آخر</a>
        <?php endif; ?>
    </div>
</div>

<script>
    $(document).ready(function () {
        $(".loading").show();
        setTimeout(function() {
            $(".loading").fadeOut();
        }, 500);
    });
</script>

</body>
</html>
