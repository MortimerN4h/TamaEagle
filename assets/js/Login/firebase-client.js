// Firebase SDK client-side support for TamaEagle
import { initializeApp } from "https://www.gstatic.com/firebasejs/11.7.3/firebase-app.js";
import {
    getAuth,
    signInWithEmailAndPassword,
    createUserWithEmailAndPassword,
    signOut,
    onAuthStateChanged,
    GoogleAuthProvider,
    signInWithPopup
} from "https://www.gstatic.com/firebasejs/11.7.3/firebase-auth.js";

// Firebase configuration
const firebaseConfig = {
    apiKey: "AIzaSyAzd7Jgo5HgqSUPtqcLnt2PkZE1lkxaW5s",
    authDomain: "tamaeagle-36639.firebaseapp.com",
    projectId: "tamaeagle-36639",
    storageBucket: "tamaeagle-36639.firebasestorage.app",
    messagingSenderId: "1067380139684",
    appId: "1:1067380139684:web:635f2edcff500b0e032831",
    databaseURL: "https://tamaeagle-36639-default-rtdb.firebaseio.com"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

// Google Auth Provider
const googleProvider = new GoogleAuthProvider();

// DOM ready function
document.addEventListener('DOMContentLoaded', function() {
    console.log('Firebase client initialized');
    
    // Handle Google Sign-in button
    const googleBtn = document.querySelector('.google-btn');
    if (googleBtn) {
        googleBtn.addEventListener('click', handleGoogleSignIn);
    }
    
    // Toggle password functionality
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                this.textContent = 'Show';
            }
        });
    });
    
    // Form validation for login
    const loginForm = document.querySelector('.Login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleEmailPasswordLogin();
        });
    }
    
    // Form validation for register
    const registerForm = document.querySelector('.register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleEmailPasswordRegister();
        });
    }
});

// Handle Google Sign-in
async function handleGoogleSignIn() {
    try {
        const result = await signInWithPopup(auth, googleProvider);
        const user = result.user;
        
        console.log('Google sign-in successful:', user);
          // Get the ID token
        const token = await user.getIdToken();
        
        // Send user data to PHP backend
        const response = await fetch('google-auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                idToken: token
            })
        });
        
        if (response.ok) {
            window.location.href = '../index.php';
        } else {
            throw new Error('Failed to authenticate with server');
        }
    } catch (error) {
        console.error('Google sign-in error:', error);
        showError('Google sign-in failed: ' + error.message);
    }
}

// Handle email/password login
async function handleEmailPasswordLogin() {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    if (!email || !password) {
        showError('Please fill in all fields');
        return;
    }
    
    try {
        const userCredential = await signInWithEmailAndPassword(auth, email, password);
        const user = userCredential.user;
        
        // Get the ID token
        const token = await user.getIdToken();
          // Send token to PHP backend
        const response = await fetch('verify-token.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                idToken: token
            })
        });
        
        if (response.ok) {
            window.location.href = '../index.php';
        } else {
            throw new Error('Failed to verify with server');
        }
    } catch (error) {
        console.error('Login error:', error);
        showError('Login failed: ' + error.message);
    }
}

// Handle email/password registration
async function handleEmailPasswordRegister() {
    const email = document.getElementById('email').value;
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    
    // Validation
    if (!email || !username || !password || !confirmPassword) {
        showError('Please fill in all fields');
        return;
    }
    
    if (password !== confirmPassword) {
        showError('Passwords do not match');
        return;
    }
    
    if (password.length < 6) {
        showError('Password must be at least 6 characters long');
        return;
    }
    
    try {
        const userCredential = await createUserWithEmailAndPassword(auth, email, password);
        const user = userCredential.user;
        
        // Get the ID token
        const token = await user.getIdToken();
          // Send user data to PHP backend
        const response = await fetch('create-user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email: email,
                password: password,
                name: username
            })
        });
        
        if (response.ok) {
            showSuccess('Registration successful! You can now login.');
        } else {
            throw new Error('Failed to create user profile');
        }
    } catch (error) {
        console.error('Registration error:', error);
        showError('Registration failed: ' + error.message);
    }
}

// Utility functions
function showError(message) {
    hideMessages();
    const errorElement = document.getElementById('error-message');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
}

function showSuccess(message) {
    hideMessages();
    const successElement = document.getElementById('success-message');
    if (successElement) {
        successElement.textContent = message;
        successElement.style.display = 'block';
    }
}

function hideMessages() {
    const errorElement = document.getElementById('error-message');
    const successElement = document.getElementById('success-message');
    
    if (errorElement) errorElement.style.display = 'none';
    if (successElement) successElement.style.display = 'none';
}

// Export for use in other scripts
window.TamaEagleAuth = {
    auth,
    handleGoogleSignIn,
    handleEmailPasswordLogin,
    handleEmailPasswordRegister
};
