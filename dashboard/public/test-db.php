<?php
/**
 * Database Connection Test
 * Use this to debug connection issues
 */

echo "<h2>Trackveil Database Connection Test</h2>";

// Check if .env file exists
$envPath = __DIR__ . '/../.env';
echo "<p><strong>.env path:</strong> $envPath</p>";
echo "<p><strong>.env exists:</strong> " . (file_exists($envPath) ? 'YES' : 'NO') . "</p>";

if (file_exists($envPath)) {
    echo "<p><strong>.env contents:</strong></p>";
    echo "<pre>";
    $lines = file($envPath);
    foreach ($lines as $line) {
        // Don't show passwords
        if (strpos($line, 'PASSWORD') !== false) {
            echo "DB_PASSWORD=***hidden***\n";
        } else {
            echo htmlspecialchars($line);
        }
    }
    echo "</pre>";
}

// Try to load config
echo "<hr>";
echo "<h3>Loading configuration...</h3>";

try {
    $config = require __DIR__ . '/../src/config.php';
    echo "<p>✅ Config loaded successfully</p>";
    echo "<p><strong>DB Host:</strong> " . $config['db']['host'] . "</p>";
    echo "<p><strong>DB Port:</strong> " . $config['db']['port'] . "</p>";
    echo "<p><strong>DB Name:</strong> " . $config['db']['database'] . "</p>";
    echo "<p><strong>DB User:</strong> " . $config['db']['username'] . "</p>";
    echo "<p><strong>DB Password:</strong> " . (empty($config['db']['password']) ? 'EMPTY!' : 'Set (***hidden***)') . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Config error: " . $e->getMessage() . "</p>";
    exit;
}

// Check PDO PostgreSQL extension
echo "<hr>";
echo "<h3>Checking PHP Extensions...</h3>";
echo "<p><strong>PDO available:</strong> " . (extension_loaded('pdo') ? 'YES' : 'NO') . "</p>";
echo "<p><strong>PDO PostgreSQL available:</strong> " . (extension_loaded('pdo_pgsql') ? 'YES' : 'NO') . "</p>";

if (!extension_loaded('pdo_pgsql')) {
    echo "<p style='color: red;'><strong>ERROR:</strong> PDO PostgreSQL extension not installed!</p>";
    echo "<p>Install with: <code>sudo apt-get install php7.2-pgsql && sudo systemctl restart php7.2-fpm</code></p>";
    exit;
}

// Try to connect
echo "<hr>";
echo "<h3>Testing database connection...</h3>";

try {
    $dsn = sprintf(
        "pgsql:host=%s;port=%d;dbname=%s;sslmode=require",
        $config['db']['host'],
        $config['db']['port'],
        $config['db']['database']
    );
    
    echo "<p><strong>DSN:</strong> " . str_replace($config['db']['password'], '***', $dsn) . "</p>";
    
    $pdo = new PDO(
        $dsn,
        $config['db']['username'],
        $config['db']['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]
    );
    
    echo "<p style='color: green;'><strong>✅ Connection successful!</strong></p>";
    
    // Test query
    $result = $pdo->query("SELECT COUNT(*) as count FROM sites")->fetch();
    echo "<p><strong>Sites in database:</strong> " . $result['count'] . "</p>";
    
    echo "<hr>";
    echo "<h3 style='color: green;'>✅ All tests passed! Database connection is working.</h3>";
    echo "<p>You can delete this test file now: <code>rm public/test-db.php</code></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'><strong>❌ Connection failed:</strong></p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    
    echo "<hr>";
    echo "<h3>Common Issues:</h3>";
    echo "<ul>";
    echo "<li>Check .env file has correct credentials</li>";
    echo "<li>Check PostgreSQL is accessible from this server</li>";
    echo "<li>Check firewall allows connection to port 5432</li>";
    echo "<li>Try: <code>psql -h pg1.trackveil.net -U markedo -d trackveil</code></li>";
    echo "</ul>";
}
?>

<style>
    body { font-family: sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
    code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
    hr { margin: 20px 0; }
</style>

