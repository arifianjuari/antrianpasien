<?php
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fungsi untuk menangani error
function customErrorHandler($errno, $errstr, $errfile, $errline)
{
    $error_message = date('Y-m-d H:i:s') . " Error [$errno]: $errstr in $errfile on line $errline\n";
    error_log($error_message, 3, "error.log");

    if (!(error_reporting() & $errno)) {
        return false;
    }

    switch ($errno) {
        case E_USER_ERROR:
            echo "<b>FATAL ERROR</b><br />\n";
            echo "Error type: [$errno] $errstr<br />\n";
            echo "Fatal error on line $errline in file $errfile<br />\n";
            exit(1);
            break;

        case E_USER_WARNING:
            echo "<b>WARNING</b><br />\n";
            echo "Warning type: [$errno] $errstr<br />\n";
            break;

        case E_USER_NOTICE:
            echo "<b>NOTICE</b><br />\n";
            echo "Notice type: [$errno] $errstr<br />\n";
            break;

        default:
            echo "<b>Unknown error type</b><br />\n";
            echo "Error type: [$errno] $errstr<br />\n";
            break;
    }

    return true;
}

// Set custom error handler
set_error_handler("customErrorHandler");
