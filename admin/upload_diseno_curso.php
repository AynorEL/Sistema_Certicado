<?php
require_once('config.php'); // Incluye tu conexión PDO como $pdo

// Validar el curso
if (!isset($_POST['idcurso']) || !is_numeric($_POST['idcurso'])) {
    die("ID de curso no válido.");
}

$idcurso = (int) $_POST['idcurso'];

// Validar archivo subido
if (!isset($_FILES['diseno']) || $_FILES['diseno']['error'] !== UPLOAD_ERR_OK) {
    die("No se pudo subir el archivo.");
}

$archivo = $_FILES['diseno'];
$extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
$tiposMimePermitidos = ['image/jpeg', 'image/png', 'image/gif'];
$nombreOriginal = basename($archivo['name']);
$extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

// Validar extensión
if (!in_array($extension, $extensionesPermitidas)) {
    die("Solo se permiten archivos JPG, JPEG, PNG y GIF.");
}

// Validar tipo MIME
if (!in_array($archivo['type'], $tiposMimePermitidos)) {
    die("Tipo de archivo no permitido. Solo se permiten imágenes.");
}

// Validar tamaño máximo (5MB)
$maxSize = 5 * 1024 * 1024; // 5MB
if ($archivo['size'] > $maxSize) {
    die("El archivo es demasiado grande. Máximo 5MB.");
}

// Generar nombre único
$nombreArchivo = 'curso_diseno_' . time() . '.' . $extension;
$rutaDestino = 'assets/uploads/cursos/' . $nombreArchivo;

// Mover archivo
if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
    die("Error al guardar el archivo.");
}

// Actualizar en base de datos
$stmt = $pdo->prepare("UPDATE curso SET diseño = :diseno WHERE idcurso = :id");
$stmt->execute([
    ':diseno' => $nombreArchivo,
    ':id' => $idcurso
]);

// Redirigir o confirmar
echo "Diseño actualizado correctamente.";
