<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $name = $_POST['name'] ?? '';

    if (empty($email) || empty($password) || empty($name)) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        exit;
    }

    // Check if email already exists before attempting registration
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'This email is already registered'
            ]);
            exit;
        }
    } catch(PDOException $e) {
        error_log("Email check error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Database error occurred'
        ]);
        exit;
    }

    $result = registerUser($email, $password, $name);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful'
        ]);
    } else {
        error_log("Registration failed for email: $email");
        echo json_encode([
            'success' => false,
            'message' => 'Registration failed. Please try again later.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
