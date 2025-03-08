<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set error log path
ini_set('error_log', __DIR__ . '/logs/error.log');

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline)
{
    $error_message = date('[Y-m-d H:i:s]') . " Error: [$errno] $errstr in $errfile on line $errline\n";
    error_log($error_message);

    // Log request details
    $request_details = [
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'N/A',
        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'N/A',
        'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
        'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
        'HTTP_REFERER' => $_SERVER['HTTP_REFERER'] ?? 'N/A'
    ];
    error_log("Request Details: " . print_r($request_details, true));

    return true;
}

// Set custom error handler
set_error_handler("customErrorHandler");

// Custom exception handler
function customExceptionHandler($exception)
{
    $error_message = date('[Y-m-d H:i:s]') . " Exception: " . $exception->getMessage() .
        " in " . $exception->getFile() .
        " on line " . $exception->getLine() . "\n";
    error_log($error_message);

    // Log stack trace
    error_log("Stack trace: " . $exception->getTraceAsString());

    // Show user-friendly message in production
    if (!isset($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] === 'localhost') {
        echo "<h1>Error</h1>";
        echo "<p>An error occurred. Details have been logged.</p>";
        echo "<pre>" . htmlspecialchars($error_message) . "</pre>";
    } else {
        echo "<h1>Error</h1>";
        echo "<p>Maaf, terjadi kesalahan. Tim teknis kami sudah diberitahu.</p>";
    }
}

// Set custom exception handler
set_exception_handler("customExceptionHandler");
