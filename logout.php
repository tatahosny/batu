<?php
session_start();

// إنهاء الجلسة وتدمير البيانات الخاصة بها
session_unset();
session_destroy();

// إعادة التوجيه إلى صفحة تسجيل الدخول
header("Location: index.html");
exit();
?>
