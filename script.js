 // التحقق من البيانات عند الضغط على زر "تحقق"
 function checkWords(event) {
    event.preventDefault();  // لمنع إعادة تحميل الصفحة عند إرسال النموذج

    const userName = document.getElementById("userName").value.trim();
    const inputWord = document.getElementById("inputWords").value.trim();

    // التحقق من القيم
    if (userName && inputWord) {
        fetch('check_code.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                userName: userName,
                inputWord: inputWord,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                showSuccessMessage();
            } else {
                showErrorMessage();
            }
        })
        .catch(error => {
            console.error("Error:", error);
            showErrorMessage();
        });
    } else {
        alert("يرجى ملء جميع الحقول.");
    }
}

// عرض رسالة النجاح
function showSuccessMessage() {
    document.getElementById('overlay').style.display = "block";
    document.getElementById('successMessage').style.display = "block";
    document.getElementById('errorMessage').style.display = "none";
    document.querySelector('.check-icon').classList.add('draw');
}

// عرض رسالة الخطأ
function showErrorMessage() {
    document.getElementById('overlay').style.display = "block";
    document.getElementById('errorMessage').style.display = "block";
    document.getElementById('successMessage').style.display = "none";
    document.querySelector('.error-icon').classList.add('draw');
}

// إغلاق الرسائل عند الضغط على "حسناً"
function closeMessage() {
    document.getElementById('overlay').style.display = "none";
    document.getElementById('successMessage').style.display = "none";
    document.getElementById('errorMessage').style.display = "none";
    document.querySelector('.check-icon').classList.remove('draw');
    document.querySelector('.error-icon').classList.remove('draw');
}

// التحكم في القائمة العلوية
function toggleDropdown() {
    const menu = document.getElementById("dropdownMenu");
    menu.classList.toggle("show");
}

// إظهار النافذة المنبثقة
function showSupportInfo() {
    document.getElementById('supportPopup').style.display = 'block';
    document.getElementById('popupOverlay').style.display = 'block';
}

// إغلاق النافذة المنبثقة
function closePopup() {
    document.getElementById('supportPopup').style.display = 'none';
    document.getElementById('popupOverlay').style.display = 'none';
}
// التبديل بين القائمة و الثلاث نقاط
function toggleDropdown(icon) {
    icon.classList.toggle('open');  // تغيير الأيقونة
    document.getElementById('dropdownMenu').classList.toggle('show');  // إظهار/إخفاء القائمة
}
