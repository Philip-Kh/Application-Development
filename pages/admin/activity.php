<?php
/**
 * Admin Activity Log Page
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/activity_logger.php';
requireAdmin();

$recentActivities = getRecentActivities(100);
$stats = getActivityStats();

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-4">
    <h1 style="font-size: var(--font-size-3xl); font-weight: 800; color: var(--white);">
        <i class="fas fa-clock-rotate-left" style="color: var(--primary-light);"></i>
        Activity Log
    </h1>
    <p style="color: var(--gray-300); margin-top: var(--spacing-sm);">
        Monitor system activities and audit audit trails
    </p>
</div>

<!-- Stats Cards -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: var(--primary);">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="stat-info">
            <h3>Today's Activities</h3>
            <p class="stat-value"><?php echo number_format($stats['today']); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
            <i class="fas fa-calendar-week"></i>
        </div>
        <div class="stat-info">
            <h3>This Week</h3>
            <p class="stat-value"><?php echo number_format($stats['week']); ?></p>
        </div>
    </div>
</div>

<!-- Activity Log Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            Recent Activities
        </h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentActivities as $log): ?>
                        <tr>
                            <td style="white-space: nowrap;">
                                <?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?>
                            </td>
                            <td>
                                <strong><?php echo sanitize($log['full_name'] ?? 'System'); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo sanitize($log['staff_id']); ?></small>
                            </td>
                            <td>
                                <span class="badge badge-info"><?php echo sanitize($log['action']); ?></span>
                            </td>
                            <td>
                                <?php echo sanitize($log['details']); ?>
                                <?php if ($log['entity_id']): ?>
                                    <br>
                                    <small class="text-muted">ID: <?php echo sanitize($log['entity_id']); ?> (<?php echo sanitize($log['entity_type']); ?>)</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small style="font-family: monospace;"><?php echo sanitize($log['ip_address']); ?></small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
