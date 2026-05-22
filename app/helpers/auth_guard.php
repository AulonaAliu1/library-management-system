<?php
declare(strict_types=1);

/**
 * Route protection call after session_start() on protected pages.
 */
require_once __DIR__ . '/functions.php';

function require_login(): void
{
    if (is_logged_in()) {
        return;
    }

    flash_set('error', 'Please log in first.');
    $loginPath = defined('LMS_ENTRY') && LMS_ENTRY === 'public' ? 'login.php' : '../../public/login.php';
    redirect($loginPath);
}

function require_admin(): void
{
    require_login();

    if (is_admin()) {
        return;
    }

    flash_set('error', 'You do not have permission to access that page.');
    redirect('dashboard.php');
}

function require_member(): void
{
    require_login();

    if (is_member()) {
        return;
    }

    flash_set('error', 'You do not have permission to access that page.');
    redirect('dashboard.php');
}
