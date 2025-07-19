<?php
header('Content-Type: application/json');
$dir = __DIR__ . '/../assets/uploads/firmas/';
$archivos = array_values(array_filter(scandir($dir), function($f) {
    return !in_array($f, ['.', '..']) && preg_match('/\.(png|jpg|jpeg)$/i', $f);
}));
echo json_encode($archivos); 