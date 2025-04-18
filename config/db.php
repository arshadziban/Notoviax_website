<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$dbname = 'notoviax';
$username = 'root'; // default XAMPP username
$password = ''; // default XAMPP password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Connection failed: " . $e->getMessage());
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch(PDOException $e) {
    error_log("Table creation failed: " . $e->getMessage());
    die("Table creation failed: " . $e->getMessage());
}

function registerUser($email, $password, $name): bool {
    global $pdo;
    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            error_log("Registration failed: Email already exists - " . $email);
            return false;
        }

        // Insert new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password, name) VALUES (:email, :password, :name)");
        
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':name', $name);
        
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("Registration failed: Database error for email - " . $email);
            return false;
        }
        
        return true;
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