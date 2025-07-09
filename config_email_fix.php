<?php
// Configuración temporal de correo
// Incluir este archivo en lugar de cargar .env si hay problemas

$_ENV['MAIL_HOST'] = 'smtp.gmail.com';
$_ENV['MAIL_USER'] = 'ronaldoramirez051@gmail.com';
$_ENV['MAIL_PASS'] = 'dixphxnnkiqavmdd';
$_ENV['MAIL_PORT'] = 587;
$_ENV['MAIL_FROM_NAME'] = 'Sistema de Certificados';

echo "Configuración de correo cargada temporalmente<br>";
echo "MAIL_USER: " . $_ENV['MAIL_USER'] . "<br>";
echo "MAIL_PASS: CONFIGURADO<br>";
?> 