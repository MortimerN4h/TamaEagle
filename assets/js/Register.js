// Import the functions you need from the SDKs you need
import { initializeApp } from "https://www.gstatic.com/firebasejs/11.7.3/firebase-app.js";
// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries
import {
    getAuth,
    createUserWithEmailAndPassword,
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

const googleProvider = new GoogleAuthProvider();
// Cài đặt tùy chọn (ví dụ: yêu cầu chọn tài khoản mỗi lần)
googleProvider.setCustomParameters({
    prompt: 'select_account'
});

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

//submit button
const submit = document.getElementById('submit');
submit.addEventListener("click", function (event) {
    event.preventDefault()
    //input
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    createUserWithEmailAndPassword(auth, email, password)
        .then((userCredential) => {
            //Signed up
            const user = userCredential.user;
            window.location.href = "../../auth/login.html";
            console.log("User:", user);
        })
        .catch((error) => {
            alert("Lỗi: " + error.message);
            console.error("Firebase error:", error);
        });
})

const googleSignInBtn = document.querySelector('.google-btn');
googleSignInBtn.addEventListener('click', async () => {
    try {
        // Sign in with Google popup
        const result = await signInWithPopup(auth, googleProvider);
        // Get the ID token
        const idToken = await result.user.getIdToken();

        // Send ID token to server to create session
        const response = await fetch('../auth/process-login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `idToken=${encodeURIComponent(idToken)}&google=true`
        });

        const data = await response.json();
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            // Provide more specific error messages
            let errorMsg = data.error || 'Google registration failed';
            if (data.isAuthError) {
                errorMsg = 'Server configuration error. Please contact administrator.';
            }
            throw new Error(errorMsg);
        }
    } catch (error) {
        let displayError = error.message;

        // Handle specific Google Auth errors
        if (error.code === 'auth/popup-closed-by-user') {
            displayError = 'Registration was cancelled by user.';
        } else if (error.code === 'auth/popup-blocked') {
            displayError = 'Popup was blocked by browser. Please allow popups and try again.';
        } else if (error.code === 'auth/cancelled-popup-request') {
            displayError = 'Multiple popup requests detected. Please try again.';
        } else if (error.code === 'auth/account-exists-with-different-credential') {
            displayError = 'An account already exists with this email. Please login instead.';
        }

        alert("Lỗi đăng ký bằng Google: " + displayError);
        console.error("Google Auth Error:", error);
    }
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