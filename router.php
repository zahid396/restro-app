<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (preg_match('#^/api#', $uri)) {
    require __DIR__ . '/api/index.php';
    exit;
}

if (preg_match('#^/admin(/.*)?$#', $uri)) {
    $adminPath = $uri === '/admin' || $uri === '/admin/' ? '/admin/index.php' : $uri;
    $adminFile = __DIR__ . $adminPath;
    
    if (is_file($adminFile) && pathinfo($adminFile, PATHINFO_EXTENSION) === 'php') {
        require $adminFile;
        exit;
    }
}

$file = __DIR__ . $uri;

if ($uri !== '/' && is_file($file)) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $mimeTypes = [
        'html' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf'
    ];
    
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
    }
    
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    readfile($file);
    exit;
}

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require __DIR__ . '/index.html';
