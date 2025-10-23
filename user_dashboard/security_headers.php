<?php
/**
 * Security Headers and Session Validation
 * Include this file at the top of all protected user dashboard pages
 * to prevent back button access after logout and ensure proper session validation
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prevent browser caching - CRITICAL for security after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Validate user session
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    // Clear any remaining session data
    session_unset();
    session_destroy();
    
    // Redirect to signin page
    header("Location: ../signin.php");
    exit();
}

// Session timeout check (30 minutes of inactivity)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: ../signin.php");
    exit();
}

// Update last activity time
$_SESSION['LAST_ACTIVITY'] = time();

// Regenerate session ID periodically to prevent session fixation
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > 1800) {
    // Session started more than 30 minutes ago
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
}
?>
