<?php
session_start();
require_once '../config/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Log registration attempt
        error_log("Registration attempt for email: $email");
        
        // Basic validation
        if (empty($email) || empty($name) || empty($password) || empty($confirmPassword)) {
            throw new Exception('All fields are required');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        if ($password !== $confirmPassword) {
            throw new Exception('Passwords do not match');
        }
        
        if (strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters long');
        }
        
        // Attempt to register the user
        if (registerUser($email, $password, $name)) {
            error_log("Registration successful for email: $email");
            $_SESSION['success'] = 'Registration successful! Please log in.';
            header('Location: ../login.html');
            exit();
        } else {
            throw new Exception('Registration failed. Email might already be taken.');
        }
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header('Location: ../signup.html');
        exit();
    }
} else {
    header('Location: ../signup.html');
    exit();
}
