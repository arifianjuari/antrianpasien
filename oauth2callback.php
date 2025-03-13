<?php
require_once 'config/config.php';
require_once 'vendor/autoload.php';

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

$client = new Google\Client();
$client->setAuthConfig('config/credentials.json');
$client->setRedirectUri('http://localhost/antrian%20pasien/oauth2callback.php');
$client->addScope(Google\Service\Drive::DRIVE_FILE);

// Log untuk debugging
error_log('OAuth callback dipanggil. Request method: ' . $_SERVER['REQUEST_METHOD']);
error_log('Query string: ' . $_SERVER['QUERY_STRING']);

// Jika ada code dari Google
if (isset($_GET['code'])) {
    error_log('Code diterima dari Google: ' . $_GET['code']);
    try {
        // Tukar code dengan access token
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        error_log('Token diterima: ' . print_r($token, true));

        if (isset($token['access_token'])) {
            // Simpan token di session
            $_SESSION['upload_token'] = $token;
            error_log('Token berhasil disimpan di session');

            // Redirect ke halaman sebelumnya atau home
            $redirect_url = isset($_SESSION['redirect_after_auth'])
                ? $_SESSION['redirect_after_auth']
                : $base_url . '/admin_praktek/manajemen_edukasi.php';

            error_log('Redirecting ke: ' . $redirect_url);
            header('Location: ' . $redirect_url);
            exit;
        }
    } catch (Exception $e) {
        // Log error
        error_log('OAuth Error: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        echo 'Error during authentication: ' . htmlspecialchars($e->getMessage());
        exit;
    }
}

// Jika ada error
if (isset($_GET['error'])) {
    error_log('OAuth Error dari Google: ' . $_GET['error']);
    if (isset($_GET['error_description'])) {
        error_log('Error description: ' . $_GET['error_description']);
    }
    echo 'Error during authentication: ' . htmlspecialchars($_GET['error']);
    exit;
}

// Jika tidak ada code atau token, redirect ke auth URL
if (empty($_SESSION['upload_token'])) {
    $auth_url = $client->createAuthUrl();
    error_log('Redirecting ke Google auth URL: ' . $auth_url);
    header('Location: ' . $auth_url);
    exit;
}
