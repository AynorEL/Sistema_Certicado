<?php
require_once 'inc/config.php';
require_once 'inc/functions.php';

// Verificar que estamos logueados
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$idcurso = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idcurso <= 0) {
    echo "ID de curso inválido";
    exit;
}

// Obtener información del curso
$stmt = $conn->prepare("SELECT nombre_curso, diseño, config_certificado FROM curso WHERE idcurso = ?");
$stmt->bind_param("i", $idcurso);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Curso no encontrado";
    exit;
}

$curso = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Editor Ajustado - <?php echo htmlspecialchars($curso['nombre_curso']); ?></title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <style>
        .editor-container {
            width: 100%;
            border: 2px dashed #6c757d;
            background-color: white;
            position: relative;
            overflow: auto;
            text-align: center;
        }
        
        .campo-editable {
            position: absolute;
            cursor: move;
            user-select: none;
            z-index: 1000;
            background-color: rgba(255, 255, 0, 0.3);
            border: 1px dashed #666;
            padding: 2px;
            min-width: 50px;
            min-height: 20px;
        }
        
        .info-panel {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-12">
                <h2>Test Editor Ajustado - <?php echo htmlspecialchars($curso['nombre_curso']); ?></h2>
                
                <div class="info-panel">
                    <h4>Información del Diseño</h4>
                    <p><strong>Curso:</strong> <?php echo htmlspecialchars($curso['nombre_curso']); ?></p>
                    <p><strong>Archivo de diseño:</strong> <?php echo $curso['diseño'] ? htmlspecialchars($curso['diseño']) : 'No asignado'; ?></p>
                    <p><strong>Configuración guardada:</strong> <?php echo $curso['config_certificado'] ? 'Sí' : 'No'; ?></p>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Editor de Certificado (Ajustado al Tamaño Real)</h3>
                    </div>
                    <div class="card-body">
                        <div id="editor" class="editor-container">
                            <?php if (!empty($curso['diseño'])): ?>
                                <img id="fondoCertificado" src="../assets/uploads/cursos/<?php echo htmlspecialchars($curso['diseño']); ?>" alt="Diseño del Certificado" style="max-width: 100%; height: auto; display: block; margin: 0 auto;">
                            <?php else: ?>
                                <div class="alert alert-warning p-3">⚠️ Este curso aún no tiene fondo de certificado asignado.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <h4>Información de Tamaños</h4>
                    <div id="infoTamanios" class="info-panel">
                        <p>Cargando información...</p>
                    </div>
                </div>
                
                <div class="mt-3">
                    <button class="btn btn-primary" onclick="mostrarInformacion()">Mostrar Información Detallada</button>
                    <button class="btn btn-secondary" onclick="window.history.back()">Volver</button>
                </div>
            </div>
        </div>
    </div>

    <script src="js/jquery-2.2.3.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        let editorScaleX = 1;
        let editorScaleY = 1;
        
        document.addEventListener("DOMContentLoaded", function () {
            const fondoCertificado = document.getElementById('fondoCertificado');
            if (fondoCertificado) {
                fondoCertificado.onload = function() {
                    ajustarEditorAlTamanioReal();
                };
                
                if (fondoCertificado.complete) {
                    ajustarEditorAlTamanioReal();
                }
            }
        });
        
        function ajustarEditorAlTamanioReal() {
            const fondoCertificado = document.getElementById('fondoCertificado');
            const editor = document.getElementById('editor');
            
            if (fondoCertificado && editor) {
                const imgWidth = fondoCertificado.naturalWidth;
                const imgHeight = fondoCertificado.naturalHeight;
                const displayWidth = fondoCertificado.offsetWidth;
                const displayHeight = fondoCertificado.offsetHeight;
                
                console.log('Tamaño real de la imagen:', imgWidth, 'x', imgHeight);
                console.log('Tamaño mostrado:', displayWidth, 'x', displayHeight);
                
                // Guardar las proporciones para conversión de coordenadas
                editorScaleX = imgWidth / displayWidth;
                editorScaleY = imgHeight / displayHeight;
                
                // Ajustar el contenedor del editor
                editor.style.width = displayWidth + 'px';
                editor.style.height = displayHeight + 'px';
                editor.style.position = 'relative';
                
                // Posicionar la imagen absolutamente dentro del editor
                fondoCertificado.style.position = 'absolute';
                fondoCertificado.style.top = '0';
                fondoCertificado.style.left = '0';
                fondoCertificado.style.width = '100%';
                fondoCertificado.style.height = '100%';
                fondoCertificado.style.objectFit = 'contain';
                
                // Actualizar información
                actualizarInformacionTamanios(imgWidth, imgHeight, displayWidth, displayHeight);
            }
        }
        
        function actualizarInformacionTamanios(imgWidth, imgHeight, displayWidth, displayHeight) {
            const infoDiv = document.getElementById('infoTamanios');
            infoDiv.innerHTML = `
                <p><strong>Tamaño original de la imagen:</strong> ${imgWidth} x ${imgHeight} píxeles</p>
                <p><strong>Tamaño mostrado en el editor:</strong> ${displayWidth} x ${displayHeight} píxeles</p>
                <p><strong>Factor de escala X:</strong> ${editorScaleX.toFixed(4)}</p>
                <p><strong>Factor de escala Y:</strong> ${editorScaleY.toFixed(4)}</p>
                <p><strong>Proporción:</strong> ${(imgWidth/imgHeight).toFixed(2)}:1</p>
            `;
        }
        
        function mostrarInformacion() {
            const fondoCertificado = document.getElementById('fondoCertificado');
            if (fondoCertificado) {
                alert(`Información del diseño:\n\n` +
                      `Tamaño original: ${fondoCertificado.naturalWidth} x ${fondoCertificado.naturalHeight}\n` +
                      `Tamaño mostrado: ${fondoCertificado.offsetWidth} x ${fondoCertificado.offsetHeight}\n` +
                      `Factor escala X: ${editorScaleX.toFixed(4)}\n` +
                      `Factor escala Y: ${editorScaleY.toFixed(4)}`);
            }
        }
    </script>
</body>
</html> 