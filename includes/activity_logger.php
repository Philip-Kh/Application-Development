<?php
/**
 * Activity Logger
 * Logs all system activities for audit trail
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Log an activity to the database
 */
function logActivity($action, $entityType = null, $entityId = null, $details = null) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            INSERT INTO activity_log (staff_id, action, entity_type, entity_id, details, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $_SESSION['staff_id'] ?? null,
            $action,
            $entityType,
            $entityId,
            is_array($details) ? json_encode($details) : $details,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Activity logging error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get recent activities
 */
function getRecentActivities($limit = 50, $staffId = null) {
    try {
        $db = getDB();
        
        $sql = "SELECT a.*, s.full_name 
                FROM activity_log a 
                LEFT JOIN staff s ON a.staff_id = s.staff_id 
                WHERE 1=1";
        $params = [];
        
        if ($staffId) {
            $sql .= " AND a.staff_id = ?";
            $params[] = $staffId;
        }
        
        $sql .= " ORDER BY a.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Get activities error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get activity statistics
 */
function getActivityStats() {
    try {
        $db = getDB();
        
        // Today's activities
        $stmt = $db->query("SELECT COUNT(*) as count FROM activity_log WHERE DATE(created_at) = CURDATE()");
        $todayCount = $stmt->fetch()['count'];
        
        // This week's activities
        $stmt = $db->query("SELECT COUNT(*) as count FROM activity_log WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $weekCount = $stmt->fetch()['count'];
        
        // Activities by type
        $stmt = $db->query("
            SELECT action, COUNT(*) as count 
            FROM activity_log 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY action 
            ORDER BY count DESC 
            LIMIT 10
        ");
        $byType = $stmt->fetchAll();
        
        return [
            'today' => $todayCount,
            'week' => $weekCount,
            'byType' => $byType
        ];
    } catch (PDOException $e) {
        error_log("Activity stats error: " . $e->getMessage());
        return ['today' => 0, 'week' => 0, 'byType' => []];
    }
}
?>
