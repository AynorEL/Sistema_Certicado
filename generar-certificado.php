<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('admin/inc/config.php');
require_once('admin/fpdf186/fpdf.php');

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
    SELECT c.duracion, i.fecha_finalizacion
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

$duracion_curso = $detalle_curso['duracion'];
$fecha_finalizacion = date("d/m/Y", strtotime($detalle_curso['fecha_finalizacion']));

// Crear el PDF
class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
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
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF('L', 'mm', 'A4'); // L para Landscape (horizontal)
$pdf->AliasNbPages();
$pdf->AddPage();
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


$pdf->Ln(30);
$pdf->Cell(135, 10, '____________________', 0, 0, 'C');
$pdf->Cell(135, 10, '____________________', 0, 1, 'C');
$pdf->Cell(135, 10, 'Firma del Instructor', 0, 0, 'C');
$pdf->Cell(135, 10, 'Firma del Director', 0, 1, 'C');


$pdf->Output('I', 'certificado-' . urlencode($nombre_curso) . '.pdf');
?> 