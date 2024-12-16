<?php
session_start();
require_once 'db_connection.php';
// التحقق من الاتصال
if (!$conn) {
    die(json_encode(["status" => "error", "message" => "فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error()]));
}
mysqli_set_charset($conn, "utf8mb4");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userName = isset($_POST['userName']) ? trim($_POST['userName']) : '';
    $inputWord = isset($_POST['inputWord']) ? trim($_POST['inputWord']) : '';

    if (!empty($userName) && !empty($inputWord)) {
        // جلب جميع الجداول من CODES_TABLES
        $getTableQuery = "SELECT codes_table_name, names_table_name FROM CODES_TABLES";
        $result = $conn->query($getTableQuery);

        if ($result->num_rows > 0) {
            $foundTable = false;
            $nameExists = false;

            while ($row = $result->fetch_assoc()) {
                $codesTable = $row['codes_table_name'];
                $namesTable = $row['names_table_name'];

                // التحقق إذا كان الاسم موجودًا في جدول الأسماء الحالي
                $checkNameQuery = "SELECT * FROM `$namesTable` WHERE user_name = ?";
                $stmtCheckName = $conn->prepare($checkNameQuery);
                $stmtCheckName->bind_param("s", $userName);
                $stmtCheckName->execute();
                $nameResult = $stmtCheckName->get_result();

                if ($nameResult->num_rows > 0) {
                    $nameExists = true;
                    break;
                }

                // التحقق إذا كان الكود موجودًا في الجدول الحالي
                $checkCodeQuery = "SELECT * FROM `$codesTable` WHERE code = ? AND used = 0";
                $stmtCheckCode = $conn->prepare($checkCodeQuery);
                $stmtCheckCode->bind_param("s", $inputWord);
                $stmtCheckCode->execute();
                $codeResult = $stmtCheckCode->get_result();

                if ($codeResult->num_rows > 0) {
                    // تحديث حالة الكود إلى "مستخدم"
                    $updateCodeQuery = "UPDATE `$codesTable` SET used = 1 WHERE code = ?";
                    $stmtUpdate = $conn->prepare($updateCodeQuery);
                    $stmtUpdate->bind_param("s", $inputWord);
                    $stmtUpdate->execute();

                    // إدخال الاسم والكود في جدول الأسماء المرتبط
                    $insertUserQuery = "INSERT INTO `$namesTable` (user_name, input_word) VALUES (?, ?)";
                    $stmtInsert = $conn->prepare($insertUserQuery);
                    $stmtInsert->bind_param("ss", $userName, $inputWord);

                    if ($stmtInsert->execute()) {
                        echo json_encode(["status" => "success", "message" => "تم تسجيل الاسم واستخدام الكود بنجاح!"], JSON_UNESCAPED_UNICODE);
                    } else {
                        echo json_encode(["status" => "error", "message" => "حدث خطأ أثناء تسجيل الاسم."], JSON_UNESCAPED_UNICODE);
                    }

                    $foundTable = true;
                    break;
                }
            }

            if ($nameExists) {
                echo json_encode(["status" => "error", "message" => "الاسم موجود بالفعل في أحد الجداول."], JSON_UNESCAPED_UNICODE);
            } elseif (!$foundTable) {
                echo json_encode(["status" => "error", "message" => "الكود غير صحيح أو تم استخدامه مسبقًا!"], JSON_UNESCAPED_UNICODE);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "لم يتم العثور على جداول الأكواد والأسماء."], JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "يرجى إدخال الاسم والكود."], JSON_UNESCAPED_UNICODE);
    }
}

$conn->close();
?>
