// Import the functions you need from the SDKs you need
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.1.0/firebase-app.js";
import { getAuth } from "https://www.gstatic.com/firebasejs/10.1.0/firebase-auth.js";
import { getFirestore } from "https://www.gstatic.com/firebasejs/10.1.0/firebase-firestore.js";

// Your web app's Firebase configuration
const firebaseConfig = {
  apiKey: "AIzaSyAzd7Jgo5HgqSUPtqcLnt2PkZE1lkxaW5s",
  authDomain: "tamaeagle-36639.firebaseapp.com",
  databaseURL: "https://tamaeagle-36639-default-rtdb.asia-southeast1.firebasedatabase.app",
  projectId: "tamaeagle-36639",
  storageBucket: "tamaeagle-36639.firebasestorage.app",
  messagingSenderId: "1067380139684",
  appId: "1:1067380139684:web:635f2edcff500b0e032831"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const db = getFirestore(app);

export { app, auth, db };