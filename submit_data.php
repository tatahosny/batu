<?php
session_start();
require_once 'db_connection.php';
// التحقق من الاتصال
if (!$conn) {
    die(json_encode(["status" => "error", "message" => "فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error()], JSON_UNESCAPED_UNICODE));
}
mysqli_set_charset($conn, "utf8mb4");

// الحصول على البيانات المرسلة من POST
$userName = isset($_POST['userName']) ? trim($_POST['userName']) : '';
$inputWord = isset($_POST['inputWord']) ? trim($_POST['inputWord']) : '';

// التأكد من أن البيانات ليست فارغة
if (empty($userName) || empty($inputWord)) {
    echo json_encode(["status" => "error", "message" => "يرجى إدخال الاسم والكود."], JSON_UNESCAPED_UNICODE);
    exit;
}

$inputWord = strtoupper($inputWord); // تحويل الكود إلى أحرف كبيرة

// جلب جميع الجداول من CODES_TABLES
$getTableQuery = "SELECT codes_table_name, names_table_name FROM CODES_TABLES";
$result = $conn->query($getTableQuery);

if ($result->num_rows > 0) {
    $foundTable = false;

    while ($row = $result->fetch_assoc()) {
        $codesTable = $row['codes_table_name'];
        $namesTable = $row['names_table_name'];

        // التحقق إذا كان الاسم موجودًا مسبقًا
        $checkNameQuery = "SELECT * FROM `$namesTable` WHERE user_name = ?";
        $stmtCheckName = $conn->prepare($checkNameQuery);
        $stmtCheckName->bind_param("s", $userName);
        $stmtCheckName->execute();
        $nameResult = $stmtCheckName->get_result();

        if ($nameResult->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "الاسم موجود بالفعل."], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // التحقق إذا كان الكود موجودًا في الجدول الحالي
        $checkCodeQuery = "SELECT * FROM `$codesTable` WHERE code = ? AND used = 0";
        $stmtCheck = $conn->prepare($checkCodeQuery);
        $stmtCheck->bind_param("s", $inputWord);
        $stmtCheck->execute();
        $codeResult = $stmtCheck->get_result();

        if ($codeResult->num_rows > 0) {
            // إدخال الاسم والكود في جدول الأسماء
            $insertQuery = "INSERT INTO `$namesTable` (user_name, input_word) VALUES (?, ?)";
            $stmtInsert = $conn->prepare($insertQuery);
            $stmtInsert->bind_param("ss", $userName, $inputWord);

            if ($stmtInsert->execute()) {
                // تحديث حالة الكود إلى مستخدم
                $updateCodeQuery = "UPDATE `$codesTable` SET used = 1 WHERE code = ?";
                $stmtUpdate = $conn->prepare($updateCodeQuery);
                $stmtUpdate->bind_param("s", $inputWord);
                $stmtUpdate->execute();

                // توليد كود جديد وإضافته إلى نفس الجدول
                $newCode = generateRandomCode();
                $insertNewCodeQuery = "INSERT INTO `$codesTable` (code, used) VALUES (?, 0)";
                $stmtInsertNewCode = $conn->prepare($insertNewCodeQuery);
                $stmtInsertNewCode->bind_param("s", $newCode);

                if ($stmtInsertNewCode->execute()) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "تم تسجيل البيانات بنجاح! تم إضافة كود جديد: $newCode"
                    ], JSON_UNESCAPED_UNICODE);
                } else {
                    echo json_encode([ 
                        "status" => "error",
                        "message" => "فشل في إدخال الكود الجديد."
                    ], JSON_UNESCAPED_UNICODE);
                }
            } else {
                echo json_encode([ 
                    "status" => "error", 
                    "message" => "فشل في إدخال البيانات."
                ], JSON_UNESCAPED_UNICODE);
            }

            $foundTable = true;
            break;
        }
    }

    if (!$foundTable) {
        echo json_encode([ 
            "status" => "error", 
            "message" => "الكود غير صحيح أو تم استخدامه مسبقًا."
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode([ 
        "status" => "error", 
        "message" => "لم يتم العثور على جداول الأكواد والأسماء."
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();

// دالة لتوليد الكود العشوائي مع أول 4 حروف ثابتة
function generateRandomCode() {
    $fixedPart = "ABCD"; // الحروف الثابتة
    $randomPart = strtoupper(bin2hex(random_bytes(3))); // باقي الكود (6 أحرف عشوائية)
    return $fixedPart . $randomPart;
}
?>
