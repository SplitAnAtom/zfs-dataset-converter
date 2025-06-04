<?php
// scan_folders.php - Scan for convertible folders
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Method not allowed";
    exit;
}

parse_str(file_get_contents('php://input'), $config);

$folders_to_convert = [];
$total_size = 0;

// Helper function to check if path is a ZFS dataset
function is_zfs_dataset($path) {
    $output = shell_exec("zfs list -H -o name,mountpoint 2>/dev/null | grep -F '$path'");
    return !empty($output);
}

// Helper function to get folder size
function get_folder_size($path) {
    $output = shell_exec("du -sb " . escapeshellarg($path) . " 2>/dev/null | cut -f1");
    return intval(trim($output));
}

// Helper function to format bytes
function format_bytes($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } else {
        return number_format($bytes / 1024, 2) . ' KB';
    }
}

// Scan container appdata if enabled
if ($config['SHOULD_PROCESS_CONTAINERS'] === 'yes') {
    $appdata_path = "/mnt/{$config['SOURCE_POOL_WHERE_APPDATA_IS']}/{$config['SOURCE_DATASET_WHERE_APPDATA_IS']}";
    if (is_dir($appdata_path)) {
        $entries = glob($appdata_path . '/*', GLOB_ONLYDIR);
        foreach ($entries as $entry) {
            if (!is_zfs_dataset($entry)) {
                $size = get_folder_size($entry);
                $folders_to_convert[] = [
                    'path' => $entry,
                    'name' => basename($entry),
                    'size' => $size,
                    'size_formatted' => format_bytes($size),
                    'type' => 'Container AppData'
                ];
                $total_size += $size;
            }
        }
    }
}

// Generate results HTML
if (empty($folders_to_convert)) {
    echo '<div style="color: #28a745; font-weight: bold;"><i class="fa fa-check"></i> No folders found that need conversion - all are already datasets!</div>';
} else {
    echo '<div style="margin-bottom: 15px;"><strong>Found ' . count($folders_to_convert) . ' folders to convert (Total: ' . format_bytes($total_size) . ')</strong></div>';
    echo '<table style="width: 100%; border-collapse: collapse;">';
    echo '<tr style="background: #f0f0f0; font-weight: bold;">';
    echo '<td style="padding: 8px; border: 1px solid #ddd;">Folder Name</td>';
    echo '<td style="padding: 8px; border: 1px solid #ddd;">Type</td>';
    echo '<td style="padding: 8px; border: 1px solid #ddd;">Size</td>';
    echo '<td style="padding: 8px; border: 1px solid #ddd;">Path</td>';
    echo '</tr>';
    
    foreach ($folders_to_convert as $folder) {
        echo '<tr>';
        echo '<td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($folder['name']) . '</td>';
        echo '<td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($folder['type']) . '</td>';
        echo '<td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($folder['size_formatted']) . '</td>';
        echo '<td style="padding: 8px; border: 1px solid #ddd; font-family: monospace; font-size: 12px;">' . htmlspecialchars($folder['path']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}
?>