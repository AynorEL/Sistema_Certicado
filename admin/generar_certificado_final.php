<?php
require_once('inc/config.php');
require_once('fpdf186/fpdf.php');

if (!isset($_GET['idcurso']) || !isset($_GET['idalumno'])) {
    http_response_code(400);
    echo "Parámetros requeridos no proporcionados";
    exit;
}

$idcurso = (int) $_GET['idcurso'];
$idalumno = (int) $_GET['idalumno'];
$idinstructor = isset($_GET['idinstructor']) ? (int) $_GET['idinstructor'] : null;
$idespecialista = isset($_GET['idespecialista']) ? (int) $_GET['idespecialista'] : null;
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

try {
    // Obtener datos del curso y configuración
    $stmt = $pdo->prepare("
        SELECT c.*, i.nombre as nombre_instructor, i.apellido as apellido_instructor,
               i.firma_digital as firma_instructor
        FROM curso c
        LEFT JOIN instructor i ON c.idinstructor = i.idinstructor
        WHERE c.idcurso = ?
    ");
    $stmt->execute([$idcurso]);
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$curso) {
        http_response_code(404);
        echo "Curso no encontrado";
        exit;
    }

    // Obtener datos del alumno
    $stmt = $pdo->prepare("
        SELECT c.idcliente, c.nombre, c.apellido, c.dni, c.email
        FROM cliente c
        WHERE c.idcliente = ?
    ");
    $stmt->execute([$idalumno]);
    $alumno = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$alumno) {
        http_response_code(404);
        echo "Alumno no encontrado";
        exit;
    }

    // Obtener datos del instructor seleccionado
    $instructor = null;
    if ($idinstructor) {
        $stmt = $pdo->prepare("
            SELECT idinstructor, nombre, apellido, especialidad, firma_digital
            FROM instructor
            WHERE idinstructor = ?
        ");
        $stmt->execute([$idinstructor]);
        $instructor = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener datos del especialista seleccionado
    $especialista = null;
    if ($idespecialista) {
        $stmt = $pdo->prepare("
            SELECT idespecialista, nombre, apellido, especialidad, firma_especialista
            FROM especialista
            WHERE idespecialista = ?
        ");
        $stmt->execute([$idespecialista]);
        $especialista = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener configuración guardada del certificado
    $config_certificado = null;
    if (!empty($curso['config_certificado'])) {
        $config_certificado = json_decode($curso['config_certificado'], true);
    }

    // Crear PDF
    class CertificadoPDF extends FPDF {
        private $config_certificado;
        private $alumno;
        private $instructor;
        private $especialista;
        private $fecha;
        private $curso;
        
        public function __construct($config, $alumno, $instructor, $especialista, $fecha, $curso) {
            parent::__construct();
            $this->config_certificado = $config;
            $this->alumno = $alumno;
            $this->instructor = $instructor;
            $this->especialista = $especialista;
            $this->fecha = $fecha;
            $this->curso = $curso;
        }
        
        function Header() {
            // Fondo del certificado
            if (!empty($this->curso['diseño'])) {
                $fondo_path = "../assets/uploads/cursos/" . $this->curso['diseño'];
                if (file_exists($fondo_path)) {
                    $this->Image($fondo_path, 0, 0, $this->GetPageWidth(), $this->GetPageHeight());
                }
            }
        }
        
        function Footer() {
            // No footer
        }
        
        function generarCertificado() {
            $this->AddPage('L', 'A4'); // Página horizontal
            
            if ($this->config_certificado) {
                foreach ($this->config_certificado as $campo) {
                    $this->SetFont('Arial', '', 12);
                    
                    // Convertir posiciones relativas a coordenadas PDF
                    $x = $this->convertirPosicionX($campo['left']);
                    $y = $this->convertirPosicionY($campo['top']);
                    
                    $this->SetXY($x, $y);
                    
                    switch ($campo['tipo']) {
                        case 'alumno':
                            $this->Cell(0, 10, $this->alumno['nombre'] . ' ' . $this->alumno['apellido'], 0, 1);
                            break;
                        case 'fecha':
                            $this->Cell(0, 10, date('d/m/Y', strtotime($this->fecha)), 0, 1);
                            break;
                        case 'instructor':
                            if ($this->instructor) {
                                $this->Cell(0, 10, $this->instructor['nombre'] . ' ' . $this->instructor['apellido'], 0, 1);
                            } else {
                                $this->Cell(0, 10, 'INSTRUCTOR NO SELECCIONADO', 0, 1);
                            }
                            break;
                        case 'especialista':
                            if ($this->especialista) {
                                $this->Cell(0, 10, $this->especialista['nombre'] . ' ' . $this->especialista['apellido'], 0, 1);
                            } else {
                                $this->Cell(0, 10, 'ESPECIALISTA NO SELECCIONADO', 0, 1);
                            }
                            break;
                        case 'firma_instructor':
                            if ($this->instructor && $this->instructor['firma_digital']) {
                                $firma_path = "../assets/uploads/firmas/" . $this->instructor['firma_digital'];
                                if (file_exists($firma_path)) {
                                    $this->Image($firma_path, $x, $y, 40, 20);
                                }
                            }
                            break;
                        case 'firma_especialista':
                            if ($this->especialista && $this->especialista['firma_especialista']) {
                                $firma_path = "../assets/uploads/firmas/" . $this->especialista['firma_especialista'];
                                if (file_exists($firma_path)) {
                                    $this->Image($firma_path, $x, $y, 40, 20);
                                }
                            }
                            break;
                        case 'qr':
                            // Generar QR
                            $qr_data = "Certificado: " . $this->curso['nombre_curso'] . "\n";
                            $qr_data .= "Alumno: " . $this->alumno['nombre'] . ' ' . $this->alumno['apellido'] . "\n";
                            $qr_data .= "DNI: " . $this->alumno['dni'] . "\n";
                            $qr_data .= "Fecha: " . date('d/m/Y', strtotime($this->fecha));
                            
                            // Crear QR temporal
                            $qr_file = $this->generarQR($qr_data);
                            if ($qr_file) {
                                $this->Image($qr_file, $x, $y, 30, 30);
                                unlink($qr_file); // Eliminar archivo temporal
                            }
                            break;
                        default:
                            $this->Cell(0, 10, $campo['texto'] ?? '', 0, 1);
                    }
                }
            }
        }
        
        private function convertirPosicionX($left) {
            // Convertir posición CSS a coordenada PDF
            $left = str_replace('px', '', $left);
            $left = (int)$left;
            return ($left / 800) * $this->GetPageWidth(); // 800 es el ancho del editor
        }
        
        private function convertirPosicionY($top) {
            // Convertir posición CSS a coordenada PDF
            $top = str_replace('px', '', $top);
            $top = (int)$top;
            return ($top / 600) * $this->GetPageHeight(); // 600 es el alto del editor
        }
        
        private function generarQR($data) {
            // Generar QR usando la librería existente
            $qr_url = "generar_qr.php?certificado=1&idcurso=" . $this->curso['idcurso'] . "&idalumno=" . $this->alumno['idcliente'] . "&fecha=" . $this->fecha;
            
            // Crear archivo temporal
            $temp_file = tempnam(sys_get_temp_dir(), 'qr_');
            $qr_content = file_get_contents($qr_url);
            
            if ($qr_content !== false) {
                file_put_contents($temp_file, $qr_content);
                return $temp_file;
            }
            
            return false;
        }
    }
    
    // Generar PDF
    $pdf = new CertificadoPDF($config_certificado, $alumno, $instructor, $especialista, $fecha, $curso);
    $pdf->generarCertificado();
    
    // Enviar PDF al navegador
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="certificado_' . $curso['idcurso'] . '_' . $alumno['idcliente'] . '.pdf"');
    
    $pdf->Output('S'); // 'S' para enviar al navegador
    
} catch (Exception $e) {
    http_response_code(500);
    echo "Error al generar el certificado: " . $e->getMessage();
}
?> 