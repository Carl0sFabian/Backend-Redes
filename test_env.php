<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

$result = [
    'secrets_dir_exists' => is_dir('/etc/secrets'),
    'secrets_dir_files' => [],
    'env_file_status' => [],
    'database_keys_status' => [],
    'db_connection_test' => []
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
    '/etc/secrets/token.json',
    __DIR__ . '/config/ca.pem',
    __DIR__ . '/config/client_secret.json',
    __DIR__ . '/config/token.json'
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

// Test DB Connection
try {
    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT') ?: '3306';
    $dbname = getenv('DB_NAME');
    $user = getenv('DB_USER');
    $pass = getenv('DB_PASS');
    
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $caPath = __DIR__ . '/config/ca.pem';
    if (file_exists($caPath) && is_readable($caPath)) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $caPath;
        $result['db_connection_test']['ssl_ca_used'] = $caPath;
    } else {
        $result['db_connection_test']['ssl_ca_used'] = 'None (File not exists or not readable)';
    }
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    $result['db_connection_test']['success'] = true;
    
    $tables = [];
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    $result['db_connection_test']['tables'] = $tables;
    $result['db_connection_test']['message'] = 'Connected successfully!';
} catch (Exception $e) {
    $result['db_connection_test']['success'] = false;
    $result['db_connection_test']['message'] = $e->getMessage();
}

// Test Translation Service
$translateTest = [];
try {
    include_once __DIR__ . '/services/TranslationService.php';
    $translationService = new TranslationService();
    $testText = "Hello world. This is a translation test.";
    $resultTranslate = $translationService->translateToSpanish($testText);
    $translateTest['success'] = ($resultTranslate['translated_text'] !== null);
    $translateTest['result'] = $resultTranslate;
} catch (Exception $e) {
    $translateTest['success'] = false;
    $translateTest['error'] = $e->getMessage();
}

// Direct curl test
$directCurl = [];
try {
    $url = "https://translate.googleapis.com/translate_a/single?client=gtx&dt=t&sl=auto&tl=es&q=" . urlencode("Hello");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $directCurl['http_code'] = $code;
    $directCurl['curl_error'] = $err;
    $directCurl['response'] = $resp;
} catch (Exception $ex) {
    $directCurl['error'] = $ex->getMessage();
}
$result['translation_test'] = $translateTest;
$result['direct_translate_curl_test'] = $directCurl;

echo json_encode($result, JSON_PRETTY_PRINT);
?>
