<?php
$host = 'localhost';
$dbname = 'notoviax';
$username = 'root'; // default XAMPP username
$password = ''; // default XAMPP password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Connection failed: " . $e->getMessage());
}

function registerUser($email, $password, $name): bool {
    global $pdo;
    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return false; // Email already exists
        }

        // Insert new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password, name) VALUES (?, ?, ?)");
        return $stmt->execute([$email, $hashedPassword, $name]);
    } catch(PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return false;
    }
}

function loginUser($email, $password): mixed {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare(query: "SELECT * FROM users WHERE email = ?");
        $stmt->execute(params: [$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify(password: $password, hash: $user['password'])) {
            return $user;
        }
        return false;
    } catch(PDOException $e) {
        return false;
    }
}

function createTask($userId, $title, $description, $dueDate): bool {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare(query: "INSERT INTO tasks (user_id, title, description, due_date) VALUES (?, ?, ?, ?)");
        return $stmt->execute(params: [$userId, $title, $description, $dueDate]);
    } catch(PDOException $e) {
        return false;
    }
}

function getTasks($userId): array {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare(query: "SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date ASC");
        $stmt->execute(params: [$userId]);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}