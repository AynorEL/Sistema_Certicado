<?php
// guardar_firma_recortada.php
require_once 'inc/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['firma']) || $_FILES['firma']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No se recibió la imagen de la firma.']);
        exit;
    }
    if (!isset($_POST['idcurso']) || !is_numeric($_POST['idcurso'])) {
        echo json_encode(['success' => false, 'message' => 'ID de curso no proporcionado.']);
        exit;
    }
    $idcurso = (int)$_POST['idcurso'];
    $nombre_archivo = 'firma_recortada_' . $idcurso . '.png';
    $ruta_destino = '../assets/uploads/firmas/' . $nombre_archivo;
    if (move_uploaded_file($_FILES['firma']['tmp_name'], $ruta_destino)) {
        $url = 'assets/uploads/firmas/' . $nombre_archivo;
        echo json_encode(['success' => true, 'url' => $url]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la firma.']);
    }
    exit;
}
echo json_encode(['success' => false, 'message' => 'Método no permitido.']); 