<?php
date_default_timezone_set('Asia/Jakarta');

// Deteksi Lingkungan: Local vs Production (cPanel)
$is_local = false;
if (isset($_SERVER['HTTP_HOST'])) {
    $host = $_SERVER['HTTP_HOST'];
    if ($host === 'localhost' || $host === '127.0.0.1' || $host === '[::1]' || preg_match('/^192\.168\./', $host) || preg_match('/\.local$/', $host)) {
        $is_local = true;
    }
} else {
    // Jalankan dari CLI (Command Line Interface)
    $is_local = true;
}

if ($is_local) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'parkir');
} else {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'sparking_philip');
    define('DB_PASS', 'philip0908');
    define('DB_NAME', 'sparking_parkir_db');
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Load Core & Business Logic Helpers
require_once __DIR__ . '/db_helper.php';
require_once __DIR__ . '/parking_helper.php';
require_once __DIR__ . '/user_helper.php';

session_start();

function appBasePath() {
  $scriptFilename = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
  $projectRoot = str_replace('\\', '/', realpath(dirname(__DIR__)));
  
  if ($scriptFilename && $projectRoot) {
    $relativeScriptPath = '';
    if (strpos($scriptFilename, $projectRoot) === 0) {
      $relativeScriptPath = substr($scriptFilename, strlen($projectRoot));
    }
    
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    
    if ($relativeScriptPath !== '' && substr($scriptName, -strlen($relativeScriptPath)) === $relativeScriptPath) {
      $basePath = substr($scriptName, 0, -strlen($relativeScriptPath));
      $basePath = '/' . trim($basePath, '/') . '/';
      if ($basePath === '//') {
        $basePath = '/';
      }
      return $basePath;
    }
  }

  // Fallback to original logic
  $documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__)));
  if ($documentRoot && strpos($projectRoot, $documentRoot) === 0) {
    $base = trim(substr($projectRoot, strlen($documentRoot)), '/');
    return $base === '' ? '/' : '/' . $base . '/';
  }

  return '/';
}

function appUrl($path = '') {
 return appBasePath() . ltrim($path, '/');
}

function isLoggedIn() {
 return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function requireLogin() {
 if (!isLoggedIn()) {
  header("Location: " . appUrl('login.php'));
  exit();
 }
}

function requireRole($roles) {
 requireLogin();
 if (!in_array($_SESSION['role'], (array)$roles)) {
 header("Location: " . appUrl('unauthorized.php'));
 exit();
 }
}

function redirect($url) {
 if (!preg_match('#^(https?://|/)#', $url)) {
 $url = appUrl($url);
 }
 header("Location: $url");
 exit();
}

function formatRupiah($amount) {
 return 'Rp ' . number_format($amount, 0, ',', '.');
}
?>