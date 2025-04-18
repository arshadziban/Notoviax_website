<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user_id'];

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Get all tasks for the user
        $tasks = getTasks($userId);
        echo json_encode(['tasks' => $tasks]);
        break;
        
    case 'POST':
        // Create a new task
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['title']) || empty($data['title'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Title is required']);
            exit();
        }
        
        $title = $data['title'];
        $description = $data['description'] ?? '';
        $dueDate = $data['due_date'] ?? null;
        
        if (createTask($userId, $title, $description, $dueDate)) {
            http_response_code(201);
            echo json_encode(['message' => 'Task created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create task']);
        }
        break;
        
    case 'PUT':
        // Update a task
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Task ID is required']);
            exit();
        }
        
        $taskId = $data['id'];
        $completed = isset($data['completed']) ? (bool)$data['completed'] : null;
        
        try {
            $stmt = $pdo->prepare("UPDATE tasks SET completed = ? WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$completed, $taskId, $userId])) {
                echo json_encode(['message' => 'Task updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update task']);
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error']);
        }
        break;
        
    case 'DELETE':
        // Delete a task
        $taskId = $_GET['id'] ?? null;
        
        if (!$taskId) {
            http_response_code(400);
            echo json_encode(['error' => 'Task ID is required']);
            exit();
        }
        
        try {
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$taskId, $userId])) {
                echo json_encode(['message' => 'Task deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete task']);
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}