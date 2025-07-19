<?php
require_once('inc/config.php');
require_once('fpdf186/fpdf.php');

/**
 * Sistema Automático de Generación de Certificados
 * 
 * Este archivo se ejecuta automáticamente cuando un alumno finaliza un curso
 * Puede ser llamado desde:
 * - Un cron job
 * - Un webhook
 * - Una función que se ejecuta al cambiar el estado de inscripción
 */

class GeneradorCertificadosAutomatico {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Genera certificado automáticamente para un alumno que finalizó un curso
     */
    public function generarCertificadoAutomatico($idinscripcion) {
        try {
            // Obtener datos de la inscripción
            $stmt = $this->pdo->prepare("
                SELECT i.*, c.nombre as nombre_cliente, c.apellido as apellido_cliente, c.dni, c.email,
                       cur.nombre_curso, cur.diseño, cur.config_certificado, cur.idinstructor as curso_instructor
                FROM inscripcion i
                JOIN cliente c ON i.idcliente = c.idcliente
                JOIN curso cur ON i.idcurso = cur.idcurso
                WHERE i.idinscripcion = ?
            ");
            $stmt->execute([$idinscripcion]);
            $inscripcion = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$inscripcion) {
                throw new Exception("Inscripción no encontrada");
            }
            
            // Verificar que el alumno haya aprobado
            if ($inscripcion['estado'] !== 'Aprobado') {
                throw new Exception("El alumno no ha aprobado el curso");
            }
            // Verificar que la nota_final esté asignada y sea válida
            if (!isset($inscripcion['nota_final']) || $inscripcion['nota_final'] === null || $inscripcion['nota_final'] === '' || !is_numeric($inscripcion['nota_final'])) {
                throw new Exception("El alumno aprobado aún no tiene nota final asignada. No se puede generar el certificado.");
            }
            
            // Verificar que no se haya generado ya un certificado
            if ($this->certificadoYaGenerado($idinscripcion)) {
                throw new Exception("El certificado ya fue generado para esta inscripción");
            }
            
            // Obtener datos del instructor del curso
            $instructor = null;
            if ($inscripcion['curso_instructor']) {
                $stmt = $this->pdo->prepare("
                    SELECT idinstructor, nombre, apellido, especialidad, firma_digital
                    FROM instructor
                    WHERE idinstructor = ?
                ");
                $stmt->execute([$inscripcion['curso_instructor']]);
                $instructor = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            // Obtener especialista (si existe uno asignado al curso)
            $especialista = $this->obtenerEspecialistaCurso($inscripcion['idcurso']);
            
            // Generar el certificado
            $certificado_path = $this->generarCertificadoPDF(
                $inscripcion,
                $instructor,
                $especialista
            );
            
            // Guardar registro del certificado generado
            $this->guardarRegistroCertificado($idinscripcion, $certificado_path);
            
            // Enviar notificación por email (opcional)
            $this->enviarNotificacionCertificado($inscripcion, $certificado_path);
            
            return [
                'success' => true,
                'message' => 'Certificado generado correctamente',
                'path' => $certificado_path
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verifica si ya se generó un certificado para esta inscripción
     */
    private function certificadoYaGenerado($idinscripcion) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total
            FROM certificados_generados
            WHERE idinscripcion = ?
        ");
        $stmt->execute([$idinscripcion]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }
    
    /**
     * Obtiene el especialista asignado al curso (si existe)
     */
    private function obtenerEspecialistaCurso($idcurso) {
        // Por ahora retornamos null, pero aquí podrías implementar
        // la lógica para obtener el especialista asignado al curso
        return null;
    }
    
    /**
     * Genera el certificado PDF
     */
    private function generarCertificadoPDF($inscripcion, $instructor, $especialista) {
        $config_certificado = null;
        if (!empty($inscripcion['config_certificado'])) {
            $config_certificado = json_decode($inscripcion['config_certificado'], true);
        }
        
        if (!$config_certificado) {
            throw new Exception("No hay configuración de certificado para este curso");
        }
        
        // Crear directorio para certificados si no existe
        $certificados_dir = "../assets/certificados/";
        if (!is_dir($certificados_dir)) {
            mkdir($certificados_dir, 0755, true);
        }
        
        // Generar nombre único para el archivo
        $filename = "certificado_" . $inscripcion['idcurso'] . "_" . $inscripcion['idcliente'] . "_" . time() . ".pdf";
        $filepath = $certificados_dir . $filename;
        
        // Crear PDF
        $pdf = new CertificadoPDFAutomatico(
            $config_certificado,
            $inscripcion,
            $instructor,
            $especialista,
            $inscripcion['fecha_aprobacion'] ?? date('Y-m-d')
        );
        
        $pdf->generarCertificado();
        $pdf->Output('F', $filepath); // 'F' para guardar en archivo
        
        return $filepath;
    }
    
    /**
     * Guarda el registro del certificado generado
     */
    private function guardarRegistroCertificado($idinscripcion, $filepath) {
        // Crear tabla si no existe
        $this->crearTablaCertificados();
        
        $stmt = $this->pdo->prepare("
            INSERT INTO certificados_generados 
            (idinscripcion, fecha_generacion, archivo_path, estado)
            VALUES (?, NOW(), ?, 'Generado')
        ");
        $stmt->execute([$idinscripcion, $filepath]);
    }
    
    /**
     * Crea la tabla de certificados generados si no existe
     */
    private function crearTablaCertificados() {
        $sql = "
        CREATE TABLE IF NOT EXISTS certificados_generados (
            id INT AUTO_INCREMENT PRIMARY KEY,
            idinscripcion INT NOT NULL,
            fecha_generacion DATETIME NOT NULL,
            archivo_path VARCHAR(500) NOT NULL,
            estado ENUM('Generado', 'Enviado', 'Descargado') DEFAULT 'Generado',
            fecha_envio DATETIME NULL,
            fecha_descarga DATETIME NULL,
            FOREIGN KEY (idinscripcion) REFERENCES inscripcion(idinscripcion) ON DELETE CASCADE
        )";
        
        $this->pdo->exec($sql);
    }
    
    /**
     * Envía notificación por email (opcional)
     */
    private function enviarNotificacionCertificado($inscripcion, $filepath) {
        // Aquí puedes implementar el envío de email
        // Por ahora solo registramos en el log
        error_log("Certificado generado para: " . $inscripcion['nombre_cliente'] . " " . $inscripcion['apellido_cliente']);
    }
    
    /**
     * Procesa todas las inscripciones aprobadas pendientes de certificado
     */
    public function procesarCertificadosPendientes() {
        $stmt = $this->pdo->prepare("
            SELECT i.idinscripcion
            FROM inscripcion i
            LEFT JOIN certificados_generados cg ON i.idinscripcion = cg.idinscripcion
            WHERE i.estado = 'Aprobado' 
            AND cg.idinscripcion IS NULL
            AND i.fecha_aprobacion IS NOT NULL
        ");
        $stmt->execute();
        $inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $resultados = [];
        foreach ($inscripciones as $inscripcion) {
            $resultado = $this->generarCertificadoAutomatico($inscripcion['idinscripcion']);
            $resultados[] = [
                'idinscripcion' => $inscripcion['idinscripcion'],
                'resultado' => $resultado
            ];
        }
        
        return $resultados;
    }
}

/**
 * Clase PDF para certificados automáticos
 */
class CertificadoPDFAutomatico extends FPDF {
    private $config_certificado;
    private $inscripcion;
    private $instructor;
    private $especialista;
    private $fecha;
    
    public function __construct($config, $inscripcion, $instructor, $especialista, $fecha) {
        parent::__construct();
        $this->config_certificado = $config;
        $this->inscripcion = $inscripcion;
        $this->instructor = $instructor;
        $this->especialista = $especialista;
        $this->fecha = $fecha;
    }
    
    function Header() {
        // Fondo del certificado
        if (!empty($this->inscripcion['diseño'])) {
            $fondo_path = "../assets/uploads/cursos/" . $this->inscripcion['diseño'];
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
                        $this->Cell(0, 10, $this->inscripcion['nombre_cliente'] . ' ' . $this->inscripcion['apellido_cliente'], 0, 1);
                        break;
                    case 'fecha':
                        $this->Cell(0, 10, date('d/m/Y', strtotime($this->fecha)), 0, 1);
                        break;
                    case 'instructor':
                        if ($this->instructor) {
                            $this->Cell(0, 10, $this->instructor['nombre'] . ' ' . $this->instructor['apellido'], 0, 1);
                        } else {
                            $this->Cell(0, 10, 'INSTRUCTOR NO ASIGNADO', 0, 1);
                        }
                        break;
                    case 'especialista':
                        if ($this->especialista) {
                            $this->Cell(0, 10, $this->especialista['nombre'] . ' ' . $this->especialista['apellido'], 0, 1);
                        } else {
                            $this->Cell(0, 10, 'ESPECIALISTA NO ASIGNADO', 0, 1);
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
                        // Generar QR único para este certificado
                        $qr_data = $this->generarDatosQR();
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
        $left = str_replace('px', '', $left);
        $left = (int)$left;
        return ($left / 800) * $this->GetPageWidth();
    }
    
    private function convertirPosicionY($top) {
        $top = str_replace('px', '', $top);
        $top = (int)$top;
        return ($top / 600) * $this->GetPageHeight();
    }
    
    private function generarDatosQR() {
        // Generar código único para el certificado
        $codigo = 'CERT_' . $this->inscripcion['idcurso'] . '_' . $this->inscripcion['idcliente'] . '_' . time();
        
        // URL de verificación
        $url_verificacion = "https://tudominio.com/certificados/validar.php?codigo=" . $codigo;
        
        return $url_verificacion;
    }
    
    private function generarQR($data) {
        $qr_url = "generar_qr.php?certificado=1&idcurso=" . $this->inscripcion['idcurso'] . "&idalumno=" . $this->inscripcion['idcliente'] . "&fecha=" . $this->fecha;
        
        $temp_file = tempnam(sys_get_temp_dir(), 'qr_');
        $qr_content = file_get_contents($qr_url);
        
        if ($qr_content !== false) {
            file_put_contents($temp_file, $qr_content);
            return $temp_file;
        }
        
        return false;
    }
}

// Ejemplo de uso:
if (isset($_GET['action'])) {
    $generador = new GeneradorCertificadosAutomatico($pdo);
    
    switch ($_GET['action']) {
        case 'generar_uno':
            if (isset($_GET['idinscripcion'])) {
                $resultado = $generador->generarCertificadoAutomatico((int)$_GET['idinscripcion']);
                echo json_encode($resultado);
            }
            break;
            
        case 'procesar_pendientes':
            $resultados = $generador->procesarCertificadosPendientes();
            echo json_encode($resultados);
            break;
            
        default:
            echo json_encode(['error' => 'Acción no válida']);
    }
} else {
    echo "Sistema de Generación Automática de Certificados";
}
?> 