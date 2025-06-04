<?php
// /usr/local/emhttp/plugins/zfs.dataset.converter/scripts/start_conversion.php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$plugin = "zfs.dataset.converter";
$config_file = "/boot/config/plugins/$plugin/settings.cfg";
$script_path = "/usr/local/emhttp/plugins/$plugin/scripts/zfs_converter.sh";
$status_file = "/tmp/zfs_converter_status.json";

// Parse form data
parse_str(file_get_contents('php://input'), $form_data);

// Validate required paths exist
if ($form_data['SHOULD_PROCESS_CONTAINERS'] === 'yes') {
    $container_path = "/mnt/{$form_data['SOURCE_POOL_WHERE_APPDATA_IS']}/{$form_data['SOURCE_DATASET_WHERE_APPDATA_IS']}";
    if (!is_dir($container_path)) {
        echo json_encode(['error' => "Container path does not exist: $container_path"]);
        exit;
    }
}

if ($form_data['SHOULD_PROCESS_VMS'] === 'yes') {
    $vm_path = "/mnt/{$form_data['SOURCE_POOL_WHERE_VM_DOMAINS_ARE']}/{$form_data['SOURCE_DATASET_WHERE_VM_DOMAINS_ARE']}";
    if (!is_dir($vm_path)) {
        echo json_encode(['error' => "VM path does not exist: $vm_path"]);
        exit;
    }
}

// Check if script is already running
if (file_exists($status_file)) {
    $status = json_decode(file_get_contents($status_file), true);
    if ($status['status'] === 'running') {
        echo json_encode(['error' => 'Conversion is already running']);
        exit;
    }
}

// Generate script configuration
$script_config = generateScriptConfig($form_data);
$temp_script = "/tmp/zfs_converter_" . uniqid() . ".sh";
file_put_contents($temp_script, $script_config);
chmod($temp_script, 0755);

// Prepare log file
$log_file = "/tmp/zfs_converter_" . date('Y-m-d_H-i-s') . ".log";

// Start conversion in background
$cmd = "$temp_script > $log_file 2>&1 & echo $!";
$pid = trim(shell_exec($cmd));

// Store process info
$status_data = [
    'status' => 'running',
    'pid' => $pid,
    'started' => time(),
    'log_file' => $log_file,
    'temp_script' => $temp_script,
    'config' => $form_data,
    'message' => 'Conversion started successfully'
];

file_put_contents($status_file, json_encode($status_data, JSON_PRETTY_PRINT));

echo json_encode(['success' => true, 'log_file' => $log_file, 'pid' => $pid]);

function generateScriptConfig($config) {
    $script = file_get_contents("/usr/local/emhttp/plugins/zfs.dataset.converter/scripts/zfs_converter_template.sh");
    
    // Replace configuration variables
    $replacements = [
        '{{DRY_RUN}}' => $config['DRY_RUN'],
        '{{CLEANUP}}' => $config['CLEANUP'],
        '{{REPLACE_SPACES}}' => $config['REPLACE_SPACES'],
        '{{ENABLE_NOTIFICATIONS}}' => $config['ENABLE_NOTIFICATIONS'],
        '{{NOTIFY_SCRIPT_START}}' => $config['NOTIFY_SCRIPT_START'],
        '{{NOTIFY_SCRIPT_COMPLETION}}' => $config['NOTIFY_SCRIPT_COMPLETION'],
        '{{NOTIFY_CONVERSION_SUMMARY}}' => $config['NOTIFY_CONVERSION_SUMMARY'],
        '{{NOTIFY_ERRORS}}' => $config['NOTIFY_ERRORS'],
        '{{NOTIFY_WARNINGS}}' => $config['NOTIFY_WARNINGS'],
        '{{NOTIFY_RESUME_OPERATIONS}}' => $config['NOTIFY_RESUME_OPERATIONS'],
        '{{NOTIFY_CONTAINER_VM_STOPS}}' => $config['NOTIFY_CONTAINER_VM_STOPS'],
        '{{NOTIFY_SPACE_ISSUES}}' => $config['NOTIFY_SPACE_ISSUES'],
        '{{SHOULD_PROCESS_CONTAINERS}}' => $config['SHOULD_PROCESS_CONTAINERS'],
        '{{SOURCE_POOL_WHERE_APPDATA_IS}}' => $config['SOURCE_POOL_WHERE_APPDATA_IS'],
        '{{SOURCE_DATASET_WHERE_APPDATA_IS}}' => $config['SOURCE_DATASET_WHERE_APPDATA_IS'],
        '{{SHOULD_PROCESS_VMS}}' => $config['SHOULD_PROCESS_VMS'],
        '{{SOURCE_POOL_WHERE_VM_DOMAINS_ARE}}' => $config['SOURCE_POOL_WHERE_VM_DOMAINS_ARE'],
        '{{SOURCE_DATASET_WHERE_VM_DOMAINS_ARE}}' => $config['SOURCE_DATASET_WHERE_VM_DOMAINS_ARE'],
        '{{VM_FORCESHUTDOWN_WAIT}}' => $config['VM_FORCESHUTDOWN_WAIT'],
        '{{BUFFER_ZONE}}' => $config['BUFFER_ZONE']
    ];
    
    // Handle additional datasets
    $additional_datasets = '';
    if (!empty($config['SOURCE_DATASETS'])) {
        $datasets = array_filter(array_map('trim', explode("\n", $config['SOURCE_DATASETS'])));
        foreach ($datasets as $dataset) {
            $additional_datasets .= "  \"$dataset\"\n";
        }
    }
    $replacements['{{SOURCE_DATASETS_ARRAY}}'] = $additional_datasets;
    
    return str_replace(array_keys($replacements), array_values($replacements), $script);
}
?>