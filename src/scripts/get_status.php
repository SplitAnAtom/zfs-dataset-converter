<?php
// get_status.php - Real-time status monitoring
header('Content-Type: application/json');

$status_file = "/tmp/zfs_converter_status.json";
$default_status = ['status' => 'idle', 'message' => 'Ready for conversion'];

if (!file_exists($status_file)) {
    echo json_encode($default_status);
    exit;
}

$status = json_decode(file_get_contents($status_file), true) ?: $default_status;

// Check if process is still running
if ($status['status'] === 'running' && isset($status['pid'])) {
    $pid = $status['pid'];
    $running = false;
    
    // Check if PID exists
    if (file_exists("/proc/$pid")) {
        $running = true;
    }
    
    if (!$running) {
        // Process finished, update status
        $status['status'] = 'completed';
        $status['message'] = 'Conversion completed';
        file_put_contents($status_file, json_encode($status, JSON_PRETTY_PRINT));
    } else {
        // Parse log for progress updates
        if (isset($status['log_file']) && file_exists($status['log_file'])) {
            $log_content = file_get_contents($status['log_file']);
            
            // Extract current operation
            if (preg_match('/Processing folder (.+)\.\.\./', $log_content, $matches)) {
                $status['current_folder'] = trim($matches[1]);
                $status['message'] = 'Processing: ' . basename($status['current_folder']);
            }
            
            // Check for completion messages
            if (strpos($log_content, 'Script execution completed successfully') !== false) {
                $status['status'] = 'completed';
                $status['message'] = 'Conversion completed successfully';
            }
            
            // Check for errors
            if (preg_match('/VALIDATION FAILED|ERROR|Failed to create/', $log_content)) {
                $status['status'] = 'error';
                $status['message'] = 'Conversion encountered errors - check logs';
            }
        }
    }
}

echo json_encode($status);
?>