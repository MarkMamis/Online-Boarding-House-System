<?php

/**
 * Laravel Wasmer / PHP built-in server router script.
 * 
 * Emulates front-controller URL rewriting without relying on Apache .htaccess.
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// Serve existing static files directly from public directory
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';
