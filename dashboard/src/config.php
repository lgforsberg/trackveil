<?php
/**
 * Dashboard Configuration
 * PHP 7.2 compatible
 */

// Load environment variables from .env file
function loadEnv($file = __DIR__ . '/../.env') {
    if (!file_exists($file)) {
        return;
    }
    
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

loadEnv();

// Configuration array
return [
    'db' => [
        'host' => getenv('DB_HOST') ?: 'pg1.trackveil.net',
        'port' => getenv('DB_PORT') ?: 5432,
        'database' => getenv('DB_NAME') ?: 'trackveil',
        'username' => getenv('DB_USER') ?: 'markedo',
        'password' => getenv('DB_PASSWORD') ?: '',
    ],
    'session' => [
        'name' => getenv('SESSION_NAME') ?: 'trackveil_dashboard',
        'lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 86400),
        'secure' => true, // HTTPS only
        'httponly' => true, // No JavaScript access
    ],
    'app' => [
        'env' => getenv('APP_ENV') ?: 'production',
        'debug' => filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN),
    ]
];

