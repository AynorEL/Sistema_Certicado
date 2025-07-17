<?php
// Configuración de la base de datos
$host = 'localhost';
$dbname = 'certficadobd';
$username = 'root';
$password = '';

// Intentar conectar a la base de datos
try {
	$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
	// Configurar el modo de error para que lance excepciones
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	// Configurar el modo de fetch para que devuelva arrays asociativos
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
	// Si hay un error de conexión, mostrar el mensaje de error y salir
	echo "Error de conexión: " . $e->getMessage();
	exit;
}
// Configuración de la zona horaria
date_default_timezone_set('America/Lima');

// Configuración de errores
// Mostrar todos los errores y advertencias
error_reporting(E_ALL);
// Mostrar los errores en el navegador
ini_set('display_errors', 1);

// Configuración de la URL base - Verificar si no está ya definida
if (!defined('BASE_URL')) {
	define("BASE_URL", "http://localhost/certificado/");
}

// Configuración de la URL del administrador - Verificar si no está ya definida
if (!defined('ADMIN_URL')) {
	define("ADMIN_URL", BASE_URL . "admin/");
}

// Constantes adicionales
// Configuración del directorio de subida - Verificar si no está ya definida
if (!defined('UPLOAD_PATH')) {
	define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/certificado/assets/uploads/');
}

// Configuración del directorio de firmas - Verificar si no está ya definida
if (!defined('FIRMAS_PATH')) {
	define('FIRMAS_PATH', $_SERVER['DOCUMENT_ROOT'] . '/certificado/assets/uploads/firmas/');
}

// Configuración del directorio de cursos - Verificar si no está ya definida
if (!defined('CURSOS_PATH')) {
	define('CURSOS_PATH', $_SERVER['DOCUMENT_ROOT'] . '/certificado/assets/uploads/cursos/');
}

