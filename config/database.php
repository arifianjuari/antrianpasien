<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Impor konfigurasi zona waktu
if (file_exists(__DIR__ . '/timezone.php')) {
    require_once __DIR__ . '/timezone.php';
}

// Impor konfigurasi base URL
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

// Database untuk aplikasi antrian pasien
$db2_host = 'auth-db1151.hstgr.io';
$db2_username = 'u609399718_adminpraktek';
$db2_password = 'Obgin@12345';
$db2_database = 'u609399718_praktekobgin';

// Improved error handling function
function handleDatabaseError($message, $exception = null)
{
    // Always log the error
    if ($exception) {
        error_log("Database Error: " . $exception->getMessage());
        $fullMessage = $exception->getMessage();
    } else {
        error_log("Database Error: " . $message);
        $fullMessage = $message;
    }

    // For AJAX requests
    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
    ) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit;
    }

    // For regular requests in development
    if (ini_get('display_errors')) {
        echo "<div style='color: red; padding: 20px; margin: 20px; border: 1px solid red;'>";
        echo "<h2>Database Error</h2>";
        echo "<p>" . htmlspecialchars($fullMessage) . "</p>";
        echo "</div>";
    } else {
        // For production
        echo "<div style='text-align: center; padding: 20px;'>";
        echo "<h2>System Temporarily Unavailable</h2>";
        echo "<p>Please try again later.</p>";
        echo "</div>";
    }
    return false;
}

// Database connection with improved error handling
try {
    // Log connection attempt
    error_log("Attempting database connection to: $db2_host");

    $conn = new PDO(
        "mysql:host=$db2_host;dbname=$db2_database;charset=utf8mb4",
        $db2_username,
        $db2_password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5, // 5 second timeout
            PDO::ATTR_PERSISTENT => false // Disable persistent connections
        ]
    );

    // Verify connection with simple query
    $conn->query("SELECT 1");
    error_log("Database connection successful");

    // Store in global scope
    $GLOBALS['conn'] = $conn;
} catch (PDOException $e) {
    handleDatabaseError("Database connection failed", $e);
    exit;
}

// Final connection check
if (!isset($GLOBALS['conn']) || !($GLOBALS['conn'] instanceof PDO)) {
    handleDatabaseError("Database connection not available");
    exit;
}

// Make connection available in local scope
$conn = $GLOBALS['conn'];
