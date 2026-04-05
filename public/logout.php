<?php
/**
 * Logout entry — minimal session cleanup stub. Expand when auth is implemented.
 */
declare(strict_types=1);

session_start();

// TODO: clear auth-related session keys, invalidate remember-me cookie if used
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], (bool) $p['httponly']);
}

session_destroy();

// Redirect placeholder — adjust path if your virtual host differs
header('Location: index.php');
exit;
