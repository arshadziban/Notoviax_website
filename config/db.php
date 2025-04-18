<?php
$host = 'localhost';
$dbname = 'taskflow';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function registerUser($email, $password) {
    global $pdo;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        return $stmt->execute([$email, $hashedPassword]);
    } catch(PDOException $e) {
        return false;
    }
}

function loginUser($email, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    } catch(PDOException $e) {
        return false;
    }
}

function createTask($userId, $title, $description, $dueDate) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, due_date) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $title, $description, $dueDate]);
    } catch(PDOException $e) {
        return false;
    }
}

function getTasks($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date ASC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}