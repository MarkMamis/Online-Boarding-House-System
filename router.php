<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';

$publicRoot = __DIR__ . '/public';
$requestedFile = $publicRoot . $uri;

if ($uri !== '/' && is_file($requestedFile)) {
    return false;
}

require $publicRoot . '/index.php';
