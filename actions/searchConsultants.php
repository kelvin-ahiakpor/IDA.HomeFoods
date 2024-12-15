<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';

header('Content-Type: application/json');

// Add error handling
function handleError($message) {
    http_response_code(500);
    echo json_encode(['error' => $message]);
    exit;
}

function searchConsultants($searchTerm) {
    global $conn;
    
    try {
        $searchTerm = "%$searchTerm%";
        
        $query = "SELECT u.user_id, CONCAT(u.first_name, ' ', u.last_name) as name, 
                         u.email, u.profile_picture, c.expertise, c.total_clients, 
                         c.rating, c.status
                  FROM ida_users u
                  INNER JOIN ida_consultants c ON u.user_id = c.consultant_id
                  WHERE (u.first_name LIKE ? 
                     OR u.last_name LIKE ? 
                     OR u.email LIKE ? 
                     OR c.expertise LIKE ?)
                  ORDER BY c.status ASC, u.first_name ASC
                  LIMIT 50"; // Add limit for safety
                  
        if (!($stmt = $conn->prepare($query))) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        if (!$stmt->bind_param('ssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm)) {
            throw new Exception("Binding parameters failed: " . $stmt->error);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception("Getting result failed: " . $stmt->error);
        }
        
        $results = $result->fetch_all(MYSQLI_ASSOC);
        
        // Format the results
        return array_map(function($row) {
            return [
                'user_id' => $row['user_id'],
                'name' => $row['name'],
                'email' => $row['email'],
                'expertise' => $row['expertise'],
                'total_clients' => $row['total_clients'],
                'rating' => $row['rating'],
                'status' => $row['status'],
                'image' => $row['profile_picture']
            ];
        }, $results);
        
    } catch (Exception $e) {
        handleError($e->getMessage());
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}

try {
    if (!isset($_GET['term'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Search term is required']);
        exit;
    }

    $term = trim($_GET['term']);
    if (empty($term)) {
        echo json_encode([]);
        exit;
    }

    $results = searchConsultants($term);
    echo json_encode($results);
    
} catch (Exception $e) {
    handleError("An error occurred while searching: " . $e->getMessage());
} 