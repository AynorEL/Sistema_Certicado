<?php
require_once('config.php'); // Tu conexión PDO como $pdo

// Validar que venga el id del curso y los datos del alumno
$idcurso = isset($_GET['idcurso']) ? (int) $_GET['idcurso'] : 0;
$nombreAlumno = $_GET['alumno'] ?? 'Nombre del Alumno';
$fecha = $_GET['fecha'] ?? date('d/m/Y');
$idalumno = isset($_GET['idalumno']) ? (int) $_GET['idalumno'] : 1;

// Obtener configuración del certificado
$stmt = $pdo->prepare("SELECT c.nombre_curso, c.diseño, c.config_certificado, 
                              i.nombre as nombre_instructor, i.apellido as apellido_instructor
                       FROM curso c
                       LEFT JOIN instructor i ON c.idinstructor = i.idinstructor
                       WHERE c.idcurso = :id");
$stmt->execute([':id' => $idcurso]);
$curso = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$curso) {
    die("Curso no encontrado.");
}

$config = json_decode($curso['config_certificado'], true);
$fondo = 'assets/uploads/cursos/' . $curso['diseño'];
$instructor = $curso['nombre_instructor'] . ' ' . $curso['apellido_instructor'];

// Generar QR con logo
function generarQRConLogo($idcurso, $idalumno, $fecha) {
    $base_url = "https://api.qrserver.com/v1/create-qr-code/";
    $datos = json_encode([
        'curso_id' => $idcurso,
        'alumno_id' => $idalumno,
        'fecha_emision' => $fecha,
        'verificacion_url' => 'http://localhost/certificado/verificar-certificado.php'
    ]);
    
    $logo_url = 'http://localhost/certificado/admin/img/logo.png';
    
    $params = [
        'size' => '150x150',
        'data' => urlencode($datos),
        'format' => 'png',
        'margin' => '2',
        'ecc' => 'M',
        'logo' => urlencode($logo_url),
        'logo_size' => '30%',
        'logo_bg' => 'FFFFFF',
        'logo_radius' => '10'
    ];
    
    return $base_url . '?' . http_build_query($params);
}

$qr_url = generarQRConLogo($idcurso, $idalumno, $fecha);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado Generado</title>
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        .certificado {
            position: relative;
            width: 1000px;
            height: 700px;
            background-image: url('<?php echo $fondo; ?>');
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .campo {
            position: absolute;
            white-space: nowrap;
            font-size: 20px;
        }
        .qr-code {
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            padding: 5px;
        }
    </style>
</head>
<body>
    <div class="certificado">
        <?php foreach ($config as $campo): 
            $texto = '';
            switch ($campo['tipo']) {
                case 'alumno':
                    $texto = htmlspecialchars($nombreAlumno);
                    break;
                case 'fecha':
                    $texto = htmlspecialchars($fecha);
                    break;
                case 'firma_instructor':
                    $texto = '<img src="assets/uploads/firmas/firma_instructor.png" width="120" onerror="this.style.display=\'none\'">';
                    break;
                case 'firma_especialista':
                    $texto = '<img src="assets/uploads/firmas/firma_especialista.png" width="120" onerror="this.style.display=\'none\'">';
                    break;
                case 'instructor':
                    $texto = htmlspecialchars($instructor);
                    break;
                case 'especialista':
                    $texto = htmlspecialchars($campo['texto'] ?? 'Especialista');
                    break;
                case 'qr':
                    $texto = '<img src="' . $qr_url . '" class="qr-code" width="120" height="120" alt="QR con Logo">';
                    break;
                default:
                    $texto = htmlspecialchars($campo['texto'] ?? '');
                    break;
            }
        ?>
            <div class="campo" style="top:<?php echo $campo['top']; ?>; left:<?php echo $campo['left']; ?>;">
                <?php echo $texto; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
