<?php
/**
 * System Settings Page
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/activity_logger.php';
requireAdmin();

// Messages
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        header('Location: settings.php?error=csrf');
        exit();
    }
    
    try {
        $db = getDB();
        $stmt = $db->prepare("UPDATE settings SET setting_value = ?, updated_by = ?, updated_at = NOW() WHERE setting_key = ?");
        
        foreach ($_POST['settings'] as $key => $value) {
            $stmt->execute([$value, getCurrentStaffId(), $key]);
        }
        
        logActivity('update_settings', 'system', null, 'Updated system settings');
        header('Location: settings.php?success=updated');
        exit();
        
    } catch (PDOException $e) {
        error_log("Settings update error: " . $e->getMessage());
        header('Location: settings.php?error=server');
        exit();
    }
}

// Fetch settings
try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM settings ORDER BY setting_label");
    $settings = $stmt->fetchAll();
} catch (PDOException $e) {
    $settings = [];
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-4">
    <h1 style="font-size: var(--font-size-3xl); font-weight: 800; color: var(--white);">
        <i class="fas fa-cog" style="color: var(--primary-light);"></i>
        System Settings
    </h1>
    <p style="color: var(--gray-300); margin-top: var(--spacing-sm);">
        Configure global system parameters
    </p>
</div>

<!-- Messages -->
<?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span>Settings updated successfully</span>
        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <span>An error occurred while updating settings</span>
        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 800px;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-sliders-h"></i>
            General Configuration
        </h3>
    </div>
    <div class="card-body">
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <?php foreach ($settings as $setting): ?>
                <div class="form-group">
                    <label class="form-label" for="setting_<?php echo $setting['setting_key']; ?>">
                        <?php echo sanitize($setting['setting_label']); ?>
                    </label>
                    
                    <?php if ($setting['setting_type'] === 'boolean'): ?>
                        <select name="settings[<?php echo $setting['setting_key']; ?>]" id="setting_<?php echo $setting['setting_key']; ?>" class="form-control">
                            <option value="1" <?php echo $setting['setting_value'] == '1' ? 'selected' : ''; ?>>Enabled</option>
                            <option value="0" <?php echo $setting['setting_value'] == '0' ? 'selected' : ''; ?>>Disabled</option>
                        </select>
                    
                    <?php elseif ($setting['setting_type'] === 'number'): ?>
                        <input type="number" 
                               name="settings[<?php echo $setting['setting_key']; ?>]" 
                               id="setting_<?php echo $setting['setting_key']; ?>" 
                               class="form-control" 
                               value="<?php echo sanitize($setting['setting_value']); ?>">
                               
                    <?php else: ?>
                        <input type="text" 
                               name="settings[<?php echo $setting['setting_key']; ?>]" 
                               id="setting_<?php echo $setting['setting_key']; ?>" 
                               class="form-control" 
                               value="<?php echo sanitize($setting['setting_value']); ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
