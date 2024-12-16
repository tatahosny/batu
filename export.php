<?php
require_once 'db_connection.php';

$table = $_GET['table'] ?? '';
if (!$table) {
    die('اسم الجدول غير محدد.');
}

// جلب البيانات من الجدول
$query = "SELECT * FROM $table";
$result = $conn->query($query);
if (!$result) {
    die('خطأ في جلب البيانات: ' . $conn->error);
}

// إعداد ملف CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $table . '_data.csv');

// إضافة BOM في بداية الملف لدعم اللغة العربية
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

// كتابة رؤوس الأعمدة
$columns = [];
while ($field = $result->fetch_field()) {
    $columns[] = $field->name;
}
fputcsv($output, $columns);

// كتابة البيانات مع تحويل القيم إلى UTF-8 لضمان دعم اللغة العربية
while ($row = $result->fetch_assoc()) {
    // تحويل القيم إلى UTF-8 لضمان دعم اللغة العربية
    $row = array_map(function($value) {
        return mb_convert_encoding($value, 'UTF-8', 'auto');
    }, $row);
    fputcsv($output, $row);
}

fclose($output);
exit;
?>
