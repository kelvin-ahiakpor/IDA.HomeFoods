<?php
class ApplicationModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function getApplicationStatus($userId) {
        $status = [
            'status' => 'none',
            'canReapply' => false,
            'daysRemaining' => 0
        ];
        
        $stmt = $this->conn->prepare("
            SELECT status, submitted_at 
            FROM ida_consultant_applications 
            WHERE user_id = ? 
            ORDER BY submitted_at DESC 
            LIMIT 1
        ");
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($application = $result->fetch_assoc()) {
            $status['status'] = $application['status'];
            
            if ($application['status'] === 'Rejected') {
                $submittedDate = new DateTime($application['submitted_at']);
                $currentDate = new DateTime();
                $daysSinceRejection = $currentDate->diff($submittedDate)->days;
                
                if ($daysSinceRejection >= 7) {
                    $status['canReapply'] = true;
                } else {
                    $status['daysRemaining'] = 7 - $daysSinceRejection;
                }
            }
        }
        
        return $status;
    }
}