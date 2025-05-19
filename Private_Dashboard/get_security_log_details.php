<?php

session_start();
// Check if user is logged in as admin
if (!isset($_SESSION['admin_user'])) {
    exit('Unauthorized');
}

// Check if log_id is provided
if (!isset($_POST['log_id']) || empty($_POST['log_id'])) {
    exit('Missing log ID');
}

require_once("include/connection.php");
$log_id = mysqli_real_escape_string($conn, $_POST['log_id']);

// Get log details
$query = mysqli_query($conn, "SELECT sl.*, ff.name as file_name, ff.file_path, ff.file_type 
                             FROM security_logs sl
                             LEFT JOIN folder_files ff ON sl.file_id = ff.id
                             WHERE sl.id = '$log_id'") or die(mysqli_error($conn));

if (mysqli_num_rows($query) == 0) {
    exit('<div class="alert alert-danger">Log not found</div>');
}

$log = mysqli_fetch_assoc($query);

// Format the event time
$event_time = date('F d, Y g:i:s A', strtotime($log['event_time']));

// Determine file icon class based on file type
$icon_class = 'fa-file';
$type_class = '';

if ($log['file_type']) {
    $file_extension = strtolower(pathinfo($log['file_name'], PATHINFO_EXTENSION));
    
    switch ($file_extension) {
        case 'pdf':
            $icon_class = 'fa-file-pdf';
            $type_class = 'pdf';
            break;
        case 'doc':
        case 'docx':
            $icon_class = 'fa-file-word';
            $type_class = 'doc';
            break;
        case 'xls':
        case 'xlsx':
            $icon_class = 'fa-file-excel';
            $type_class = 'xls';
            break;
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            $icon_class = 'fa-file-image';
            $type_class = 'img';
            break;
        case 'txt':
        case 'csv':
            $icon_class = 'fa-file-alt';
            $type_class = 'txt';
            break;
    }
}

// Determine event type badge class
$event_badge_class = '';
$event_icon = '';

switch ($log['event_type']) {
    case 'right_click':
        $event_badge_class = 'status-right-click';
        $event_icon = 'fa-mouse';
        break;
    case 'keyboard_shortcut':
        $event_badge_class = 'status-keyboard-shortcut';
        $event_icon = 'fa-keyboard';
        break;
    case 'screenshot_attempt':
        $event_badge_class = 'status-screenshot-attempt';
        $event_icon = 'fa-camera';
        break;
    case 'text_selection':
        $event_badge_class = 'status-text-selection';
        $event_icon = 'fa-i-cursor';
        break;
    case 'drag_attempt':
        $event_badge_class = 'status-text-selection';
        $event_icon = 'fa-hand-pointer';
        break;
    case 'devtools_open':
        $event_badge_class = 'status-devtools-open';
        $event_icon = 'fa-code';
        break;
    case 'copy_attempt':
    case 'cut_attempt':
    case 'paste_attempt':
        $event_badge_class = 'status-copy-attempt';
        $event_icon = 'fa-copy';
        break;
    case 'middle_click':
        $event_badge_class = 'status-right-click';
        $event_icon = 'fa-mouse';
        break;
    default:
        $event_badge_class = 'status-badge';
        $event_icon = 'fa-exclamation-triangle';
}
?>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <i class="fas fa-file me-2"></i> File Information
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="file-icon <?php echo $type_class; ?>" style="width: 48px; height: 48px; font-size: 1.5rem;">
                        <i class="fas <?php echo $icon_class; ?>"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-1"><?php echo htmlspecialchars($log['file_name'] ?? 'Unknown File'); ?></h5>
                        <div class="text-muted">File ID: <?php echo htmlspecialchars($log['file_id']); ?></div>
                    </div>
                </div>
                
                <?php if ($log['file_path']): ?>
                <div class="mb-2">
                    <strong>File Path:</strong> 
                    <span class="text-muted"><?php echo htmlspecialchars($log['file_path']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($log['file_type']): ?>
                <div>
                    <strong>File Type:</strong> 
                    <span class="badge bg-light text-dark"><?php echo strtoupper(htmlspecialchars($log['file_type'])); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <i class="fas fa-user me-2"></i> User Information
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div style="width: 48px; height: 48px; background-color: #e1f0fa; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        <i class="fas fa-user text-primary"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-1"><?php echo htmlspecialchars($log['user_email']); ?></h5>
                    </div>
                </div>
                
                <div class="mb-2">
                    <strong>IP Address:</strong> 
                    <span class="text-muted"><?php echo htmlspecialchars($log['ip_address']); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header bg-light">
        <i class="fas fa-shield-virus me-2"></i> Event Details
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <strong>Event Type:</strong> 
                    <span class="status-badge <?php echo $event_badge_class; ?> ms-2">
                        <i class="fas <?php echo $event_icon; ?>"></i>
                        <?php echo ucwords(str_replace('_', ' ', $log['event_type'])); ?>
                    </span>
                </div>
                
                <div class="mb-3">
                    <strong>Event Time:</strong> 
                    <span class="text-muted"><?php echo $event_time; ?></span>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <strong>Event Details:</strong> 
                    <p class="mt-2"><?php echo htmlspecialchars($log['details']); ?></p>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <strong>User Agent:</strong>
            <div class="mt-2 p-2 bg-light rounded">
                <code style="word-break: break-all;"><?php echo htmlspecialchars($log['user_agent']); ?></code>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    This security event was logged on <strong><?php echo $event_time; ?></strong> when a user attempted a potentially unauthorized action.
</div>