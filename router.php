<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';

$publicRoot = __DIR__ . '/public';
$file = realpath($publicRoot . $uri);

if (
    $uri !== '/'
    && $file !== false
    && str_starts_with($file, realpath($publicRoot))
    && is_file($file)
) {
    return false;
}

require $publicRoot . '/index.php';
