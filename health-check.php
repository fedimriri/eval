<?php
/**
 * Health check script to verify the application is working correctly
 */

header('Content-Type: application/json');

$health = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'checks' => []
];

// Check PHP version
$health['checks']['php_version'] = [
    'status' => version_compare(PHP_VERSION, '8.2.0', '>=') ? 'ok' : 'error',
    'value' => PHP_VERSION,
    'required' => '>=8.2.0'
];

// Check required PHP extensions
$required_extensions = ['pdo', 'pdo_mysql', 'mysqli', 'gd', 'zip', 'mbstring', 'xml'];
foreach ($required_extensions as $ext) {
    $health['checks']['extension_' . $ext] = [
        'status' => extension_loaded($ext) ? 'ok' : 'error',
        'loaded' => extension_loaded($ext)
    ];
}

// Check if uploads directory is writable
$upload_dir = 'uploads/audio/';
$health['checks']['uploads_writable'] = [
    'status' => is_writable($upload_dir) ? 'ok' : 'error',
    'path' => $upload_dir,
    'writable' => is_writable($upload_dir)
];

// Check database connection
try {
    require_once 'app/Config/config.php';
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['database']};charset={$db_config['charset']};port={$db_config['port']}";
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $health['checks']['database'] = [
        'status' => 'ok',
        'host' => $db_config['host'],
        'database' => $db_config['database']
    ];
} catch (Exception $e) {
    $health['checks']['database'] = [
        'status' => 'error',
        'error' => $e->getMessage()
    ];
    $health['status'] = 'error';
}

// Check if Composer autoloader exists
$health['checks']['composer'] = [
    'status' => file_exists('vendor/autoload.php') ? 'ok' : 'error',
    'autoloader_exists' => file_exists('vendor/autoload.php')
];

// Overall status
$has_errors = false;
foreach ($health['checks'] as $check) {
    if ($check['status'] === 'error') {
        $has_errors = true;
        break;
    }
}

if ($has_errors) {
    $health['status'] = 'error';
    http_response_code(500);
}

echo json_encode($health, JSON_PRETTY_PRINT);
?>
