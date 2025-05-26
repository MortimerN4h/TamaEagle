// Import the functions you need from the SDKs you need
import { initializeApp } from "https://www.gstatic.com/firebasejs/11.7.3/firebase-app.js";
// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries
import {
    getAuth,
    signInWithEmailAndPassword,
    setPersistence,
    browserLocalPersistence,
    browserSessionPersistence,
    GoogleAuthProvider,
    signInWithPopup,
} from "https://www.gstatic.com/firebasejs/11.7.3/firebase-auth.js";

// Your web app's Firebase configuration
const firebaseConfig = {
    apiKey: "AIzaSyAzd7Jgo5HgqSUPtqcLnt2PkZE1lkxaW5s",
    authDomain: "tamaeagle-36639.firebaseapp.com",
    projectId: "tamaeagle-36639",
    storageBucket: "tamaeagle-36639.firebasestorage.appspot.com",
    messagingSenderId: "1067380139684",
    appId: "1:1067380139684:web:635f2edcff500b0e032831"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const rememberMeCheckbox = document.getElementById('rememberMe'); // Lấy checkbox
const googleProvider = new GoogleAuthProvider();

// Cài đặt tùy chọn (ví dụ: yêu cầu chọn tài khoản mỗi lần)
googleProvider.setCustomParameters({
    prompt: 'select_account'
});


//submit button
const submit = document.getElementById('submit');
submit.addEventListener("click", function (event) {
    event.preventDefault()
    //input
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    // Xác định chế độ persistence dựa trên trạng thái checkbox
    const persistenceMode = rememberMeCheckbox.checked
        ? browserLocalPersistence // Nếu tích chọn, duy trì cục bộ (LOCAL)
        : browserSessionPersistence; // Nếu không tích chọn, duy trì theo phiên (SESSION)

    // Đặt chế độ persistence TRƯỚC khi đăng nhập
    setPersistence(auth, persistenceMode)
        .then(() => {
            // Sau khi đặt persistence, tiến hành đăng nhập
            return signInWithEmailAndPassword(auth, email, password);
        })
        .then((userCredential) => {
            // Đăng nhập thành công
            const user = userCredential.user;
            console.log("User:", user);
            window.location.href = "../../index.html"; // Chuyển hướng
        })
        .catch((error) => {
            alert("Lỗi đăng nhập: " + error.message);
            console.error("Firebase error:", error);
        });
})

const googleSignInBtn = document.querySelector('.google-btn');
googleSignInBtn.addEventListener('click', () => {
    signInWithPopup(auth, googleProvider)
        .then((result) => {
            // Đăng nhập/đăng ký thành công bằng Google
            const user = result.user;
            console.log("User signed in with Google:", user);
            window.location.href = "../../index.html"; // Ví dụ chuyển hướng
        })
        .catch((error) => {
            // Xử lý lỗi
            const errorCode = error.code;
            const errorMessage = error.message;
            alert("Lỗi đăng nhập bằng Google: " + errorMessage);
            console.error("Google Auth Error:", errorCode, errorMessage);
        });
});

document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
        const targetId = btn.getAttribute('data-target');
        const input = document.getElementById(targetId);
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = 'Hide';
        } else {
            input.type = 'password';
            btn.textContent = 'Show';
        }
    });
});