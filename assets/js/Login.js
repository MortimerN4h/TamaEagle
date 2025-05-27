// Import the functions you need from the SDKs you need
import { initializeApp } from "https://www.gstatic.com/firebasejs/11.7.3/firebase-app.js";
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
const rememberMeCheckbox = document.getElementById('rememberMe');
const googleProvider = new GoogleAuthProvider();

googleProvider.setCustomParameters({
    prompt: 'select_account'
});

//submit button
const submit = document.getElementById('submit');
submit.addEventListener("click", async function (event) {
    event.preventDefault();
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    try {
        // Set persistence mode based on remember me checkbox
        const persistenceMode = rememberMeCheckbox.checked
            ? browserLocalPersistence
            : browserSessionPersistence;

        // Set persistence before login
        await setPersistence(auth, persistenceMode);

        // Login with Firebase Auth
        const userCredential = await signInWithEmailAndPassword(auth, email, password);
        const idToken = await userCredential.user.getIdToken();        // Send request to PHP server to create session
        const response = await fetch('process-login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `idToken=${encodeURIComponent(idToken)}`
        });

        const data = await response.json();
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            // Provide more specific error messages
            let errorMsg = data.error || 'Login failed';
            if (data.isAuthError) {
                errorMsg = 'Server configuration error. Please contact administrator.';
            }
            throw new Error(errorMsg);
        }
    } catch (error) {
        let displayError = error.message;

        // Handle specific Firebase client errors
        if (error.code === 'auth/user-not-found') {
            displayError = 'No user found with this email address.';
        } else if (error.code === 'auth/wrong-password') {
            displayError = 'Incorrect password.';
        } else if (error.code === 'auth/invalid-email') {
            displayError = 'Invalid email address format.';
        } else if (error.code === 'auth/user-disabled') {
            displayError = 'This account has been disabled.';
        }

        alert("Lỗi đăng nhập: " + displayError);
        console.error("Error:", error);
    }
});

// Google Sign In
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
            let errorMsg = data.error || 'Google login failed';
            if (data.isAuthError) {
                errorMsg = 'Server configuration error. Please contact administrator.';
            }
            throw new Error(errorMsg);
        }
    } catch (error) {
        let displayError = error.message;

        // Handle specific Google Auth errors
        if (error.code === 'auth/popup-closed-by-user') {
            displayError = 'Login was cancelled by user.';
        } else if (error.code === 'auth/popup-blocked') {
            displayError = 'Popup was blocked by browser. Please allow popups and try again.';
        } else if (error.code === 'auth/cancelled-popup-request') {
            displayError = 'Multiple popup requests detected. Please try again.';
        }

        alert("Lỗi đăng nhập bằng Google: " + displayError);
        console.error("Google Auth Error:", error);
    }
});

// Password visibility toggle
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