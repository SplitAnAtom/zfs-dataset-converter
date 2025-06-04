<?php
// save_settings.php - Save configuration
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed";
    exit;
}

$plugin = "zfs.dataset.converter";
$config_file = "/boot/config/plugins/$plugin/settings.cfg";

parse_str(file_get_contents('php://input'), $config);

// Generate config file content
$config_content = "# ZFS Dataset Converter Configuration\n";
$config_content .= "# Generated on " . date('Y-m-d H:i:s') . "\n\n";

$config_vars = [
    'DRY_RUN',
    'CLEANUP', 
    'REPLACE_SPACES',
    'ENABLE_NOTIFICATIONS',
    'NOTIFY_SCRIPT_START',
    'NOTIFY_SCRIPT_COMPLETION',
    'NOTIFY_CONVERSION_SUMMARY',
    'NOTIFY_ERRORS',
    'NOTIFY_WARNINGS',
    'NOTIFY_RESUME_OPERATIONS',
    'NOTIFY_CONTAINER_VM_STOPS',
    'NOTIFY_SPACE_ISSUES',
    'SHOULD_PROCESS_CONTAINERS',
    'SOURCE_POOL_WHERE_APPDATA_IS',
    'SOURCE_DATASET_WHERE_APPDATA_IS',
    'SHOULD_PROCESS_VMS',
    'SOURCE_POOL_WHERE_VM_DOMAINS_ARE',
    'SOURCE_DATASET_WHERE_VM_DOMAINS_ARE',
    'VM_FORCESHUTDOWN_WAIT',
    'BUFFER_ZONE',
    'SOURCE_DATASETS'
];

foreach ($config_vars as $var) {
    $value = isset($config[$var]) ? $config[$var] : '';
    $config_content .= "$var=\"$value\"\n";
}

// Ensure config directory exists
$config_dir = dirname($config_file);
if (!is_dir($config_dir)) {
    mkdir($config_dir, 0755, true);
}

// Write config file
if (file_put_contents($config_file, $config_content) !== false) {
    echo "Settings saved successfully!";
} else {
    http_response_code(500);
    echo "Error saving settings!";
}
?>