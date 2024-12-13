<?php
// metrics_update.php
require_once '../../db/config.php';

function updateConsultantMetrics($consultantId = null) {
    global $conn;
    
    // Base query
    $sql = "INSERT INTO ida_consultant_metrics
            SELECT 
                c.consultant_id,
                u.first_name,
                u.last_name,
                c.status,
                COUNT(DISTINCT s.client_id) as total_clients,
                COUNT(DISTINCT CASE 
                    WHEN s.start_time >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                    THEN s.client_id 
                    END) as active_clients_30d,
                COUNT(s.session_id) as total_sessions,
                COUNT(CASE 
                    WHEN s.status = 'Completed' 
                    THEN s.session_id 
                    END) as completed_sessions,
                COUNT(CASE 
                    WHEN s.status = 'In Progress' 
                    THEN s.session_id 
                    END) as ongoing_sessions,
                COUNT(CASE 
                    WHEN s.status = 'Scheduled' 
                    THEN s.session_id 
                    END) as upcoming_sessions,
                COALESCE(AVG(r.rating), 0) as average_rating,
                COUNT(r.rating_id) as total_ratings,
                c.joined_date,
                c.last_active,
                COUNT(CASE 
                    WHEN s.start_time >= DATE_FORMAT(NOW() ,'%Y-%m-01')
                    THEN s.session_id 
                    END) as sessions_this_month,
                COUNT(CASE 
                    WHEN s.start_time >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH) ,'%Y-%m-01')
                    AND s.start_time < DATE_FORMAT(NOW() ,'%Y-%m-01')
                    THEN s.session_id 
                    END) as sessions_last_month
            FROM 
                ida_consultants c
                INNER JOIN ida_users u ON c.consultant_id = u.user_id
                LEFT JOIN ida_consultant_sessions s ON c.consultant_id = s.consultant_id
                LEFT JOIN ida_ratings r ON c.consultant_id = r.consultant_id";

    // Add WHERE clause if updating specific consultant
    if ($consultantId) {
        $sql .= " WHERE c.consultant_id = ?";
    }

    $sql .= " GROUP BY 
                c.consultant_id, u.first_name, u.last_name, c.status, c.joined_date, c.last_active
              ON DUPLICATE KEY UPDATE
                first_name = VALUES(first_name),
                last_name = VALUES(last_name),
                status = VALUES(status),
                total_clients = VALUES(total_clients),
                active_clients_30d = VALUES(active_clients_30d),
                total_sessions = VALUES(total_sessions),
                completed_sessions = VALUES(completed_sessions),
                ongoing_sessions = VALUES(ongoing_sessions),
                upcoming_sessions = VALUES(upcoming_sessions),
                average_rating = VALUES(average_rating),
                total_ratings = VALUES(total_ratings),
                last_active = VALUES(last_active),
                sessions_this_month = VALUES(sessions_this_month),
                sessions_last_month = VALUES(sessions_last_month)";

    try {
        if ($consultantId) {
            $stmt = $conn->prepare($sql);
            $stmt->execute([$consultantId]);
        } else {
            $conn->query($sql);
        }
        return true;
    } catch (Exception $e) {
        error_log("Metrics Update Error: " . $e->getMessage());
        return false;
    }
}

// Different trigger points to call the update:

// 1. After session completion
function updateMetricsAfterSession($sessionId) {
    global $conn;
    
    // Get consultant ID from session
    $sql = "SELECT consultant_id FROM ida_consultant_sessions WHERE session_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$sessionId]);
    $consultantId = $stmt->fetch(PDO::FETCH_COLUMN);
    
    if ($consultantId) {
        updateConsultantMetrics($consultantId);
    }
}

// 2. After rating submission
function updateMetricsAfterRating($consultantId) {
    updateConsultantMetrics($consultantId);
}

// 3. Scheduled update (can be called via cron job)
function scheduleMetricsUpdate() {
    updateConsultantMetrics(); // Updates all consultants
}

// Usage examples:

// After session ends
// include_once 'metrics_update.php';
// updateMetricsAfterSession($sessionId);

// After rating
// include_once 'metrics_update.php';
// updateMetricsAfterRating($consultantId);

// For cron job (create a separate file like cron_metrics_update.php):
/*
<?php
require_once 'metrics_update.php';
scheduleMetricsUpdate();
*/
# In crontab
# 0 0 * * * php /path/to/your/cron_metrics_update.php

// We can also update metrics when:
// 1. Consultant logs in (update last_active)
// 2. New booking is made (update upcoming_sessions)
// 3. Session status changes
// 4. End of day/week/month for reports
?>