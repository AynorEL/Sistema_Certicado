<?php
// Verificar si la sesión no está activa antes de configurar
if (session_status() === PHP_SESSION_NONE) {
    // Configuración de sesión (debe ir antes de session_start())
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS
    
    // Iniciar la sesión después de la configuración
    session_start();
} 