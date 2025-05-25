// Import các hàm bạn cần từ SDKs bạn cần
import { initializeApp } from "https://www.gstatic.com/firebasejs/11.7.3/firebase-app.js";
// Import sendPasswordResetEmail cho chức năng đặt lại mật khẩu
import { getAuth, sendPasswordResetEmail } from "https://www.gstatic.com/firebasejs/11.7.3/firebase-auth.js";

// Cấu hình Firebase cho ứng dụng web của bạn (sao chép từ các file khác của bạn)
const firebaseConfig = {
    apiKey: "AIzaSyAzd7Jgo5HgqSUPtqcLnt2PkZE1lkxaW5s",
    authDomain: "tamaeagle-36639.firebaseapp.com",
    projectId: "tamaeagle-36639",
    storageBucket: "tamaeagle-36639.firebasestorage.appspot.com",
    messagingSenderId: "1067380139684",
    appId: "1:1067380139684:web:635f2edcff500b0e032831"
};

// Khởi tạo Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

// Nút gửi liên kết đặt lại mật khẩu
const resetPasswordBtn = document.getElementById('resetPassword');

resetPasswordBtn.addEventListener("click", function (event) {
    event.preventDefault(); // Ngăn chặn form gửi đi mặc định

    const email = document.getElementById('email').value;

    if (!email) {
        alert("Vui lòng nhập địa chỉ email của bạn.");
        return;
    }

    sendPasswordResetEmail(auth, email)
        .then(() => {
            // Email đặt lại mật khẩu đã được gửi
            alert("Chúng tôi đã gửi một liên kết đặt lại mật khẩu đến email của bạn. Vui lòng kiểm tra hộp thư đến và thư mục spam.");
            console.log("Password reset email sent!");
            setTimeout(() => {
                window.location.href = "../../html/Login/Login.html";
            }, 2000); // Chuyển hướng sau 2 giây
        })
        .catch((error) => {
            const errorCode = error.code;
            const errorMessage = error.message;
            alert("Lỗi khi gửi liên kết đặt lại mật khẩu: " + errorMessage);
            console.error("Firebase error:", errorCode, errorMessage);
        });
});