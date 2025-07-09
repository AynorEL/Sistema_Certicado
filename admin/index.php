<?php

// Iniciar el buffer de salida
ob_start();

// Incluir archivos de configuración y autenticación
require_once 'inc/session_config.php';
require_once 'inc/config.php';
require_once 'auth.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Incluir el encabezado
require_once 'header.php';
?>

<!-- Contenido de la Página -->
<section class="content-header">
    <h1>Panel de Control</h1>
</section>

<?php

try {
    // Consulta para contar las Categorías
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM categoria");
    $stmt->execute();
    $total_categorias = $stmt->fetch()['total'];

    // Consulta para contar los Cursos
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM curso");
    $stmt->execute();
    $total_productos = $stmt->fetch()['total'];

    // Consulta para contar los Clientes
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM cliente");
    $stmt->execute();
    $total_clientes = $stmt->fetch()['total'];

    // Consulta para contar las Inscripciones Completadas
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM inscripcion WHERE estado = 'Activo'");
    $stmt->execute();
    $total_ordenes_completadas = $stmt->fetch()['total'];

    // Consulta para contar las Inscripciones Pendientes
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM inscripcion WHERE estado = 'Pendiente'");
    $stmt->execute();
    $total_ordenes_pendientes = $stmt->fetch()['total'];

    // Consulta para contar los Certificados Generados
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM certificado_generado");
    $stmt->execute();
    $total_certificados = $stmt->fetch()['total'];

    // Consulta para contar los Alumnos Pendientes de Aprobación
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM inscripcion WHERE estado = 'Pendiente'");
    $stmt->execute();
    $total_pendientes_aprobacion = $stmt->fetch()['total'];
} catch (PDOException $e) {
    // Manejo de errores de la base de datos
    echo '<div class="alert alert-danger">Error al obtener las estadísticas: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>

<section class="content">
    <div class="row">
        <!-- Info Box para Cursos -->
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-graduation-cap"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Cursos</span>
                    <span class="info-box-number"><?php echo htmlspecialchars($total_productos); ?></span>
                    <span class="progress-description">Total de Cursos</span>
                </div>
            </div>
        </div>

        <!-- Info Box para Inscripciones -->
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Inscripciones</span>
                    <span class="info-box-number"><?php echo htmlspecialchars($total_ordenes_completadas + $total_ordenes_pendientes); ?></span>
                    <span class="progress-description">Total de Inscripciones</span>
                </div>
            </div>
        </div>

        <!-- Info Box para Clientes -->
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fa fa-user"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Estudiante en proceso de certificación</span>
                    <span class="info-box-number"><?php echo htmlspecialchars($total_clientes); ?></span>
                    <span class="progress-description">Total de estudiantes en proceso de certificación</span>
                </div>
            </div>
        </div>

        <!-- Info Box para Categorías -->
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-purple"><i class="fa fa-tags"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Categorías</span>
                    <span class="info-box-number"><?php echo htmlspecialchars($total_categorias); ?></span>
                    <span class="progress-description">Total de Categorías</span>
                </div>
            </div>
        </div>

        <!-- Info Box para Inscripciones Pendientes -->
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-clock-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Inscripciones Pendientes</span>
                    <span class="info-box-number"><?php echo htmlspecialchars($total_ordenes_pendientes); ?></span>
                    <span class="progress-description">Total de Inscripciones Pendientes</span>
                </div>
            </div>
        </div>

        <!-- Info Box para Inscripciones Activas -->
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Certificados finalizados</span>
                    <span class="info-box-number"><?php echo htmlspecialchars($total_ordenes_completadas); ?></span>
                    <span class="progress-description">Total de certificados finalizados</span>
                </div>
            </div>
        </div>

        <!-- Info Box para Certificados Generados -->
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-blue"><i class="fa fa-certificate"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Certificados Generados</span>
                    <span class="info-box-number"><?php echo htmlspecialchars($total_certificados); ?></span>
                    <span class="progress-description">Total de certificados con QR</span>
                </div>
            </div>
        </div>

        <!-- Info Box para Pendientes de Aprobación -->
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-orange"><i class="fa fa-clock-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pendientes de Aprobación</span>
                    <span class="info-box-number"><?php echo htmlspecialchars($total_pendientes_aprobacion); ?></span>
                    <span class="progress-description">Alumnos esperando aprobación</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Acciones Rápidas -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bolt"></i> Acciones Rápidas</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6">
                            <a href="aprobar_alumno.php" class="btn btn-success btn-block">
                                <i class="fa fa-user-check"></i><br>
                                <strong>Aprobar Alumnos</strong><br>
                                <small>Gestionar aprobaciones</small>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="certificados_generados.php" class="btn btn-info btn-block">
                                <i class="fa fa-list"></i><br>
                                <strong>Ver Certificados</strong><br>
                                <small>Certificados generados</small>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="previsualizar_certificado_final.php" class="btn btn-warning btn-block">
                                <i class="fa fa-eye"></i><br>
                                <strong>Previsualizar</strong><br>
                                <small>Ver diseño certificado</small>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <?php
                            $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$ruta_cert = $protocolo . '://' . $host . '/certificado/verificar-certificado.php';
?>
                            <a href="<?php echo $ruta_cert; ?>" target="_blank" class="btn btn-primary btn-block">
                                <i class="fa fa-search"></i><br>
                                <strong>Verificar Certificado</strong><br>
                                <small>Página pública</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</section>
<?php require_once 'footer.php'; ?>

<script>
    $(document).ready(function () {
        // Inicializar AdminLTE
        $.AdminLTE.layout.activate();

        // Inicializar los tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Inicializar los popovers
        $('[data-toggle="popover"]').popover();
    });
</script>
</body>
</html>
