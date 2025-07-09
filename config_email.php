<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

return [
    'user' => $_ENV['MAIL_USER'],
    'pass' => $_ENV['MAIL_PASS'],
    'from_name' => $_ENV['MAIL_FROM_NAME'],
];
