<?php
// get_logs.php - Live log streaming
$log_file = $_GET['file'] ?? '';

if (empty($log_file) || !file_exists($log_file)) {
    echo "Log file not found or not specified.";
    exit;
}

// Security check - ensure log file is in allowed directory
if (strpos(realpath($log_file), '/tmp/') !== 0) {
    echo "Access denied.";
    exit;
}

$lines = file($log_file, FILE_IGNORE_NEW_LINES);
$output = '';

// Show last 100 lines
$start = max(0, count($lines) - 100);
for ($i = $start; $i < count($lines); $i++) {
    $line = htmlspecialchars($lines[$i]);
    
    // Color coding for different message types
    if (strpos($line, 'ERROR') !== false || strpos($line, 'FAILED') !== false) {
        $line = '<span style="color: #ff6b6b;">' . $line . '</span>';
    } elseif (strpos($line, 'WARNING') !== false) {
        $line = '<span style="color: #ffa500;">' . $line . '</span>';
    } elseif (strpos($line, 'SUCCESSFUL') !== false || strpos($line, 'completed') !== false) {
        $line = '<span style="color: #51cf66;">' . $line . '</span>';
    } elseif (strpos($line, 'Step ') === 0) {
        $line = '<span style="color: #74c0fc; font-weight: bold;">' . $line . '</span>';
    }
    
    $output .= $line . "\n";
}

echo $output;
?>