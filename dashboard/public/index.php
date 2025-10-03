<?php
/**
 * Dashboard Entry Point
 * Redirects to login or app based on auth status
 */

require_once __DIR__ . '/../src/auth.php';

initSession();

if (isLoggedIn()) {
    header('Location: /app/');
} else {
    header('Location: /login.php');
}
exit;

