<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Email and password are required';
        header('Location: ../login.html');
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email format';
        header('Location: ../login.html');
        exit();
    }
    
    // Attempt to login
    $user = loginUser($email, $password);
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        header('Location: ../app.html');
        exit();
    } else {
        $_SESSION['error'] = 'Invalid email or password';
        header('Location: ../login.html');
        exit();
    }
} else {
    header('Location: ../login.html');
    exit();
}