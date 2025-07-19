<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('admin/inc/config.php');
require_once('admin/fpdf186/fpdf.php');
require_once('vendor/autoload.php');

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Color\Color;

if (isset($_GET['codigo'])) {
    // DESCARGA PÚBLICA POR CÓDIGO DE VALIDACIÓN
    $codigo_validacion = $_GET['codigo'];
    $stmt = $pdo->prepare("
        SELECT cg.*, 
               cl.nombre as nombre_cliente, cl.apellido as apellido_cliente, cl.dni, cl.email,
               cu.nombre_curso, cu.duracion, cu.diseño, cu.config_certificado,
               i.nombre as nombre_instructor, i.apellido as apellido_instructor, i.firma_digital as firma_instructor,
               e.nombre as nombre_especialista, e.apellido as apellido_especialista, e.firma_especialista as firma_especialista,
               ins.fecha_aprobacion, ins.nota_final
        FROM certificado_generado cg
        JOIN cliente cl ON cg.idcliente = cl.idcliente
        JOIN curso cu ON cg.idcurso = cu.idcurso
        LEFT JOIN instructor i ON cu.idinstructor = i.idinstructor
        LEFT JOIN especialista e ON cu.idespecialista = e.idespecialista
        LEFT JOIN inscripcion ins ON cg.idcliente = ins.idcliente AND cg.idcurso = ins.idcurso
        WHERE cg.codigo_validacion = ? AND cg.estado = 'Activo'
    ");
    $stmt->execute([$codigo_validacion]);
    $certificado = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$certificado) {
        die('Certificado no encontrado o inválido.');
    }
    $datos_certificado = [
        'nombre_cliente' => $certificado['nombre_cliente'] . ' ' . $certificado['apellido_cliente'],
        'nombre_curso' => $certificado['nombre_curso'],
        'duracion_curso' => $certificado['duracion'],
        'fecha_finalizacion' => $certificado['fecha_aprobacion'] ? date('d/m/Y', strtotime($certificado['fecha_aprobacion'])) : 'N/A',
        'nombre_archivo_qr' => $certificado['codigo_qr'],
        'codigo_validacion' => $certificado['codigo_validacion'],
        'nota_final' => $certificado['nota_final'] ?? 'N/A',
    ];
    if ($certificado['nombre_instructor']) {
        $datos_certificado['instructor'] = $certificado['nombre_instructor'] . ' ' . $certificado['apellido_instructor'];
        if ($certificado['firma_instructor']) {
            $datos_certificado['firma_instructor'] = 'assets/uploads/firmas/' . $certificado['firma_instructor'];
        }
    }
    if (!empty($certificado['nombre_especialista']) && !empty($certificado['apellido_especialista'])) {
        $datos_certificado['especialista'] = $certificado['nombre_especialista'] . ' ' . $certificado['apellido_especialista'];
        if (!empty($certificado['firma_especialista'])) {
            $datos_certificado['firma_especialista'] = 'assets/uploads/firmas/' . $certificado['firma_especialista'];
        }
    }
    $config_certificado = $certificado['config_certificado'];
    $diseño = $certificado['diseño'];
    // PDF igual que modo final
    class PDF extends FPDF {
        private $config_certificado;
        private $datos_certificado;
        public function setConfig($config, $datos) {
            $this->config_certificado = $config;
            $this->datos_certificado = $datos;
        }
        function Header() { if ($this->config_certificado) return; }
        function Footer() { if ($this->config_certificado) return; }
        function agregarCampoPersonalizado($campo, $datos) {
            $tipo = $campo['tipo'];
            $x = $campo['left'] * 0.264583;
            $y = $campo['top'] * 0.264583;
            $ancho = $campo['width'] * 0.264583;
            $alto = $campo['height'] * 0.264583;
            $x = max(0, min($x, 297 - $ancho));
            $y = max(0, min($y, 210 - $alto));
            $ancho = min($ancho, 297 - $x);
            $alto = min($alto, 210 - $y);
            $this->SetXY($x, $y);
            $this->SetFont($campo['fontFamily'] ?? 'Arial', $campo['fontWeight'] ?? '', $campo['fontSize'] ?? 12);
            switch ($tipo) {
                case 'alumno': $texto = $datos['nombre_cliente']; break;
                case 'fecha': $texto = $datos['fecha_finalizacion']; break;
                case 'instructor': $texto = $datos['instructor'] ?? 'NOMBRE DEL INSTRUCTOR'; break;
                case 'especialista': $texto = $datos['especialista'] ?? 'NOMBRE DEL ESPECIALISTA'; break;
                case 'qr':
                    $ruta_qr = 'admin/img/qr/' . $datos['nombre_archivo_qr'];
                    if (file_exists($ruta_qr)) {
                        $config = json_decode($this->config_certificado, true);
                        $qr_config = $config['qr_config'] ?? [ 'size' => 300 ];
                        $qr_size_mm = $qr_config['size'] * 0.264583;
                        $this->Image($ruta_qr, $x, $y, $qr_size_mm, $qr_size_mm);
                        // Logo en QR (solo PNG, no SVG)
                        if (!empty($qr_config['logoEnabled']) && $qr_config['logoEnabled'] && file_exists('admin/img/logo.png')) {
                            $logo_size_mm = $qr_size_mm * 0.2;
                            $logo_x = $x + ($qr_size_mm - $logo_size_mm) / 2;
                            $logo_y = $y + ($qr_size_mm - $logo_size_mm) / 2;
                            $this->Image('admin/img/logo.png', $logo_x, $logo_y, $logo_size_mm, $logo_size_mm);
                        }
                    }
                    return;
                case 'firma_instructor':
                    if (isset($datos['firma_instructor']) && file_exists($datos['firma_instructor'])) {
                        $this->Image($datos['firma_instructor'], $x, $y, $ancho, $alto);
                    }
                    return;
                case 'firma_especialista':
                    if (isset($datos['firma_especialista']) && file_exists($datos['firma_especialista'])) {
                        $this->Image($datos['firma_especialista'], $x, $y, $ancho, $alto);
                    }
                    return;
                default:
                    $texto = $campo['texto'] ?? '';
            }
            $this->Cell($ancho, $alto, utf8_decode($texto), 0, 0, $campo['textAlign'] ?? 'L');
        }
    }
    $pdf = new PDF('L', 'mm', 'A4');
    $pdf->AliasNbPages();
    $pdf->setConfig($config_certificado, $datos_certificado);
    $pdf->AddPage();
    $config = json_decode($config_certificado, true);
    if ($diseño && file_exists('assets/uploads/cursos/' . $diseño)) {
        $pdf->Image('assets/uploads/cursos/' . $diseño, 0, 0, 297, 210);
    }
    if (isset($config['campos'])) {
        foreach ($config['campos'] as $campo) {
            $pdf->agregarCampoPersonalizado($campo, $datos_certificado);
        }
    }
    $pdf->Output('I', 'certificado-' . urlencode($datos_certificado['nombre_curso']) . '.pdf');
    exit;
}

// Redirigir si no está logueado o si no se especifica el curso
if (!isset($_SESSION['cliente']) || !isset($_GET['curso'])) {
    header('location: login.php');
    exit;
}

// Obtener datos
$nombre_curso = urldecode($_GET['curso']);
$cliente = $_SESSION['cliente'];
$nombre_cliente = $cliente['nombre'] . ' ' . $cliente['apellido'];
$idCliente = $cliente['idcliente'];

// Obtener datos de la inscripción y del curso
$statement = $pdo->prepare("
    SELECT c.idcurso, c.duracion, c.nombre_curso, c.diseño, c.config_certificado, i.fecha_finalizacion, i.estado
    FROM inscripcion i
    JOIN curso c ON i.idcurso = c.idcurso
    WHERE i.idcliente = ? AND c.nombre_curso = ? AND i.estado = 'Aprobado'
");
$statement->execute([$idCliente, $nombre_curso]);
$detalle_curso = $statement->fetch(PDO::FETCH_ASSOC);

if (!$detalle_curso) {
    echo "No se encontró un certificado válido para este curso.";
    exit;
}

$idcurso = $detalle_curso['idcurso'];
$duracion_curso = $detalle_curso['duracion'];
$fecha_finalizacion = date("d/m/Y", strtotime($detalle_curso['fecha_finalizacion']));
$diseño = $detalle_curso['diseño'];
$config_certificado = $detalle_curso['config_certificado'];

// Verificar si ya existe un código QR para este certificado
$stmt = $pdo->prepare("
    SELECT codigo_validacion, codigo_qr 
    FROM certificado_generado 
    WHERE idcliente = ? AND idcurso = ?
");
$stmt->execute([$idCliente, $idcurso]);
$certificado_existente = $stmt->fetch(PDO::FETCH_ASSOC);

$codigo_validacion = '';
$nombre_archivo_qr = '';

if ($certificado_existente) {
    // Usar código existente
    $codigo_validacion = $certificado_existente['codigo_validacion'];
    $nombre_archivo_qr = $certificado_existente['codigo_qr'];
} else {
    // Generar nuevo código único
    $codigo_validacion = uniqid('CERT-' . $idcurso . '-' . $idCliente . '-', true);
    $nombre_archivo_qr = $codigo_validacion . '.png';
    
    // Generar el código QR usando la configuración guardada
    try {
        $url_validacion = "https://" . $_SERVER['HTTP_HOST'] . "/certificado/verificar-certificado.php?codigo=" . $codigo_validacion;
        
        // Obtener configuración del QR del curso
        $config = json_decode($config_certificado, true);
        $qr_config = $config['qr_config'] ?? [
            'size' => 300,
            'color' => '#000000',
            'bgColor' => '#FFFFFF',
            'margin' => 0,
            'logoEnabled' => false
        ];
        
        // Generar QR con configuración personalizada (versión 6)
        $qrCode = new QrCode(
            $url_validacion,
            new Encoding('UTF-8'),
            ErrorCorrectionLevel::High,
            $qr_config['size'],
            $qr_config['margin'],
            RoundBlockSizeMode::Margin,
            new Color(
                hexdec(substr($qr_config['color'], 1, 2)),
                hexdec(substr($qr_config['color'], 3, 2)),
                hexdec(substr($qr_config['color'], 5, 2))
            ),
            new Color(
                hexdec(substr($qr_config['bgColor'], 1, 2)),
                hexdec(substr($qr_config['bgColor'], 3, 2)),
                hexdec(substr($qr_config['bgColor'], 5, 2))
            )
        );
        
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        // Guardar el QR en el servidor
        $ruta_qr = __DIR__ . '/admin/img/qr/' . $nombre_archivo_qr;
        $result->saveToFile($ruta_qr);
        
        // Guardar en la base de datos
        $stmt = $pdo->prepare("
            INSERT INTO certificado_generado (idcliente, idcurso, codigo_validacion, codigo_qr, fecha_generacion, estado)
            VALUES (?, ?, ?, ?, NOW(), 'Activo')
        ");
        $stmt->execute([$idCliente, $idcurso, $codigo_validacion, $nombre_archivo_qr]);
        
    } catch (Exception $e) {
        echo "Error al generar el código QR: " . $e->getMessage();
        exit;
    }
}

// Crear el PDF
class PDF extends FPDF
{
    private $config_certificado;
    private $datos_certificado;
    
    public function setConfig($config, $datos) {
        $this->config_certificado = $config;
        $this->datos_certificado = $datos;
    }
    
    // Cabecera de página
    function Header()
    {
        // Si hay configuración personalizada, no mostrar header estándar
        if ($this->config_certificado) {
            return;
        }
        
        // Logo
        $this->Image('assets/uploads/logo_1749757156.png', 10, 8, 33);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(30, 10, 'Certificado de Finalizacion', 0, 0, 'C');
        $this->Ln(20);
    }

    // Pie de página
    function Footer()
    {
        // Si hay configuración personalizada, no mostrar footer estándar
        if ($this->config_certificado) {
            return;
        }
        
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
    
    // Función para agregar campos personalizados
    function agregarCampoPersonalizado($campo, $datos) {
        $tipo = $campo['tipo'];
        $x = $campo['left'] * 0.264583; // px a mm
        $y = $campo['top'] * 0.264583;
        $ancho = $campo['width'] * 0.264583;
        $alto = $campo['height'] * 0.264583;
        // Limitar a los bordes de la página A4 landscape
        $x = max(0, min($x, 297 - $ancho));
        $y = max(0, min($y, 210 - $alto));
        $ancho = min($ancho, 297 - $x);
        $alto = min($alto, 210 - $y);
        $this->SetXY($x, $y);
        $this->SetFont($campo['fontFamily'] ?? 'Arial', $campo['fontWeight'] ?? '', $campo['fontSize'] ?? 12);
        switch ($tipo) {
            case 'alumno':
                $texto = $datos['nombre_cliente'];
                break;
            case 'fecha':
                $texto = $datos['fecha_finalizacion'];
                break;
            case 'instructor':
                $texto = $datos['instructor'] ?? 'NOMBRE DEL INSTRUCTOR';
                break;
            case 'especialista':
                $texto = $datos['especialista'] ?? 'NOMBRE DEL ESPECIALISTA';
                break;
            case 'qr':
                $ruta_qr = 'admin/img/qr/' . $datos['nombre_archivo_qr'];
                if (file_exists($ruta_qr)) {
                    // Obtener configuración del QR para el tamaño
                    $config = json_decode($this->config_certificado, true);
                    $qr_config = $config['qr_config'] ?? [
                        'size' => 300,
                        'color' => '#000000',
                        'bgColor' => '#FFFFFF',
                        'margin' => 0,
                        'logoEnabled' => false
                    ];
                    
                    // Usar el tamaño configurado del QR
                    $qr_size_mm = $qr_config['size'] * 0.264583; // Convertir px a mm
                    $this->Image($ruta_qr, $x, $y, $qr_size_mm, $qr_size_mm);
                }
                return;
            case 'firma_instructor':
                $img_firma = isset($campo['img_src']) && $campo['img_src'] ? $campo['img_src'] : ($datos['firma_instructor'] ?? null);
                if ($img_firma && file_exists($img_firma)) {
                    $this->Image($img_firma, $x, $y, $ancho, $alto);
                }
                return;
            case 'firma_especialista':
                $img_firma = isset($campo['img_src']) && $campo['img_src'] ? $campo['img_src'] : ($datos['firma_especialista'] ?? null);
                if ($img_firma && file_exists($img_firma)) {
                    $this->Image($img_firma, $x, $y, $ancho, $alto);
                }
                return;
            default:
                $texto = $campo['texto'] ?? '';
        }
        $this->Cell($ancho, $alto, utf8_decode($texto), 0, 0, $campo['textAlign'] ?? 'L');
    }
}

$pdf = new PDF('L', 'mm', 'A4'); // L para Landscape (horizontal)
$pdf->AliasNbPages();

// Preparar datos para el certificado
$datos_certificado = [
    'nombre_cliente' => $nombre_cliente,
    'nombre_curso' => $nombre_curso,
    'duracion_curso' => $duracion_curso,
    'fecha_finalizacion' => $fecha_finalizacion,
    'nombre_archivo_qr' => $nombre_archivo_qr,
    'codigo_validacion' => $codigo_validacion
];

// Obtener datos del instructor si existe
if ($detalle_curso['idinstructor']) {
    $stmt = $pdo->prepare("SELECT nombre, apellido, firma_digital FROM instructor WHERE idinstructor = ?");
    $stmt->execute([$detalle_curso['idinstructor']]);
    $instructor = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($instructor) {
        $datos_certificado['instructor'] = $instructor['nombre'] . ' ' . $instructor['apellido'];
        if ($instructor['firma_digital']) {
            $datos_certificado['firma_instructor'] = 'admin/assets/uploads/firmas/' . $instructor['firma_digital'];
        }
    }
}

// Obtener datos del especialista si existe
if ($detalle_curso['idespecialista']) {
    $stmt = $pdo->prepare("SELECT nombre, apellido, firma_especialista FROM especialista WHERE idespecialista = ?");
    $stmt->execute([$detalle_curso['idespecialista']]);
    $especialista = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($especialista) {
        $datos_certificado['especialista'] = $especialista['nombre'] . ' ' . $especialista['apellido'];
        if ($especialista['firma_especialista']) {
            $datos_certificado['firma_especialista'] = 'admin/assets/uploads/firmas/' . $especialista['firma_especialista'];
        }
    }
}

$pdf->setConfig($config_certificado, $datos_certificado);
$pdf->AddPage();

// Si hay configuración personalizada, usar diseño personalizado
if ($config_certificado) {
    $config = json_decode($config_certificado, true);
    
    // Agregar imagen de fondo si existe
    if ($diseño && file_exists('assets/uploads/cursos/' . $diseño)) {
        $pdf->Image('assets/uploads/cursos/' . $diseño, 0, 0, 297, 210); // A4 landscape
    }
    
    // Agregar campos personalizados
    if (isset($config['campos'])) {
        foreach ($config['campos'] as $campo) {
            $pdf->agregarCampoPersonalizado($campo, $datos_certificado);
        }
    }
} else {
    // Diseño estándar
    $pdf->SetFont('Arial', '', 16);
    
    // Contenido del certificado
    $pdf->Ln(30);
    $pdf->SetFont('Arial', '', 20);
    $pdf->Cell(0, 10, 'Otorgado a:', 0, 1, 'C');
    
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 28);
    $pdf->Cell(0, 10, utf8_decode($nombre_cliente), 0, 1, 'C');
    
    $pdf->Ln(10);
    $pdf->SetFont('Arial', '', 16);
    $pdf->Cell(0, 10, utf8_decode('Por haber completado exitosamente el curso de:'), 0, 1, 'C');
    
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 22);
    $pdf->Cell(0, 10, utf8_decode($nombre_curso), 0, 1, 'C');
    
    $pdf->Ln(10);
    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell(0, 10, utf8_decode("Con una duración de $duracion_curso horas."), 0, 1, 'C');
    $pdf->Cell(0, 10, utf8_decode("Fecha de finalización: $fecha_finalizacion"), 0, 1, 'C');
    
    // Agregar QR en la esquina inferior derecha con tamaño configurado
    $config = json_decode($config_certificado, true);
    $qr_config = $config['qr_config'] ?? [
        'size' => 300,
        'color' => '#000000',
        'bgColor' => '#FFFFFF',
        'margin' => 0,
        'logoEnabled' => false
    ];
    $qr_size_mm = $qr_config['size'] * 0.264583; // Convertir px a mm
    $pdf->Image('admin/img/qr/' . $nombre_archivo_qr, 250, 150, $qr_size_mm, $qr_size_mm);
    
    $pdf->Ln(30);
    $pdf->Cell(135, 10, '____________________', 0, 0, 'C');
    $pdf->Cell(135, 10, '____________________', 0, 1, 'C');
    $pdf->Cell(135, 10, 'Firma del Instructor', 0, 0, 'C');
    $pdf->Cell(135, 10, 'Firma del Director', 0, 1, 'C');
}

$pdf->Output('I', 'certificado-' . urlencode($nombre_curso) . '.pdf');
?> 