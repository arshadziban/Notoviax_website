<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Basic validation
    if (empty($email) || empty($password) || empty($confirmPassword)) {
        $_SESSION['error'] = 'All fields are required';
        header('Location: ../signup.html');
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email format';
        header('Location: ../signup.html');
        exit();
    }
    
    if ($password !== $confirmPassword) {
        $_SESSION['error'] = 'Passwords do not match';
        header('Location: ../signup.html');
        exit();
    }
    
    if (strlen($password) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters long';
        header('Location: ../signup.html');
        exit();
    }
    
    // Attempt to register the user
    if (registerUser($email, $password)) {
        $_SESSION['success'] = 'Registration successful! Please log in.';
        header('Location: ../login.html');
        exit();
    } else {
        $_SESSION['error'] = 'Registration failed. Email might already be taken.';
        header('Location: ../signup.html');
        exit();
    }
} else {
    header('Location: ../signup.html');
    exit();
}