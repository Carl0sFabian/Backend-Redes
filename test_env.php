<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

$result = [
    'secrets_dir_exists' => is_dir('/etc/secrets'),
    'secrets_dir_files' => [],
    'env_file_status' => [],
    'database_keys_status' => []
];

if ($result['secrets_dir_exists']) {
    $files = scandir('/etc/secrets');
    if ($files !== false) {
        $result['secrets_dir_files'] = array_values(array_filter($files, function($f) {
            return $f !== '.' && $f !== '..';
        }));
    }
}

$envFiles = [
    '/etc/secrets/.env', 
    __DIR__ . '/.env',
    '/etc/secrets/ca.pem',
    '/etc/secrets/client_secret.json',
    '/etc/secrets/token.json'
];
foreach ($envFiles as $file) {
    $exists = file_exists($file);
    $status = [
        'path' => $file,
        'exists' => $exists,
        'readable' => $exists ? is_readable($file) : false,
        'size' => $exists ? filesize($file) : 0,
        'keys' => []
    ];
    
    if ($exists && $status['readable'] && strpos($file, '.env') !== false) {
        $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (is_array($lines)) {
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $status['keys'][] = trim($parts[0]);
                }
            }
        }
    }
    $result['env_file_status'][] = $status;
}

$vars = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'];
foreach ($vars as $var) {
    $result['database_keys_status'][$var] = [
        'getenv' => getenv($var) !== false ? 'Set (len: ' . strlen(getenv($var)) . ')' : 'Not Set',
        '$_SERVER' => isset($_SERVER[$var]) ? 'Set (len: ' . strlen($_SERVER[$var]) . ')' : 'Not Set',
        '$_ENV' => isset($_ENV[$var]) ? 'Set (len: ' . strlen($_ENV[$var]) . ')' : 'Not Set'
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>
