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
    $stmt = $pdo->prepare("SELECT COALESCE(COUNT(*), 0) AS total FROM categoria");
    $stmt->execute();
    $total_categorias = (int)$stmt->fetch()['total'];

    // Consulta para contar los Cursos
    $stmt = $pdo->prepare("SELECT COALESCE(COUNT(*), 0) AS total FROM curso");
    $stmt->execute();
    $total_cursos = (int)$stmt->fetch()['total'];

    // Consulta para contar los Clientes
    $stmt = $pdo->prepare("SELECT COALESCE(COUNT(*), 0) AS total FROM cliente");
    $stmt->execute();
    $total_clientes = (int)$stmt->fetch()['total'];

    // Consulta para contar las Inscripciones por Estado
    $stmt = $pdo->prepare("SELECT 
        COALESCE(SUM(CASE WHEN estado = 'Activo' THEN 1 ELSE 0 END), 0) as activas,
        COALESCE(SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END), 0) as pendientes,
        COALESCE(COUNT(*), 0) as total
        FROM inscripcion");
    $stmt->execute();
    $inscripciones = $stmt->fetch();
    $total_inscripciones_activas = (int)$inscripciones['activas'];
    $total_inscripciones_pendientes = (int)$inscripciones['pendientes'];
    $total_inscripciones = (int)$inscripciones['total'];

    // Consulta para contar los Certificados Generados
    $stmt = $pdo->prepare("SELECT COALESCE(COUNT(*), 0) AS total FROM certificado_generado");
    $stmt->execute();
    $total_certificados = (int)$stmt->fetch()['total'];

    // Consulta para contar Instructores
    $stmt = $pdo->prepare("SELECT COALESCE(COUNT(*), 0) AS total FROM instructor");
    $stmt->execute();
    $total_instructores = (int)$stmt->fetch()['total'];

    // Consulta para contar Especialistas
    $stmt = $pdo->prepare("SELECT COALESCE(COUNT(*), 0) AS total FROM especialista");
    $stmt->execute();
    $total_especialistas = (int)$stmt->fetch()['total'];

    // Consulta para contar Pagos
    $stmt = $pdo->prepare("SELECT COALESCE(COUNT(*), 0) AS total FROM pago");
    $stmt->execute();
    $total_pagos = (int)$stmt->fetch()['total'];

} catch (PDOException $e) {
    // Manejo de errores de la base de datos
    echo '<div class="alert alert-danger">Error al obtener las estadísticas: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>

<section class="content">
    <div class="row">
        <!-- Info Box para Cursos -->
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-graduation-cap"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Cursos</span>
                    <span class="info-box-number"><?php echo htmlspecialchars($total_cursos ?? '0'); ?></span>
                    <span class="progress-description">Total de Cursos Disponibles</span>
                </div>
            </div>
        </div>

        <!-- Info Box para Clientes -->
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fa fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Estudiantes</span>
                    <span class="info-box-number"><?php echo htmlspecialchars($total_clientes ?? '0'); ?></span>
                    <span class="progress-description">Total de Estudiantes Registrados</span>
                </div>
            </div>
        </div>

        <!-- Info Box para Inscripciones -->
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-user-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Inscripciones</span>
                    <span class="info-box-number"><?php echo htmlspecialchars($total_inscripciones ?? '0'); ?></span>
                    <span class="progress-description">Total de Inscripciones</span>
                </div>
            </div>
        </div>

        <!-- Info Box para Certificados -->
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-blue"><i class="fa fa-certificate"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Certificados</span>
                    <span class="info-box-number"><?php echo htmlspecialchars($total_certificados ?? '0'); ?></span>
                    <span class="progress-description">Certificados Generados con QR</span>
                </div>
            </div>
        </div>

        <!-- Info Box para Instructores -->
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-purple"><i class="fa fa-user-tie"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Instructores</span>
                    <span class="info-box-number"><?php echo htmlspecialchars($total_instructores ?? '0'); ?></span>
                    <span class="progress-description">Total de Instructores</span>
                </div>
            </div>
        </div>

        <!-- Info Box para Especialistas -->
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-maroon"><i class="fa fa-user-tie"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Especialistas</span>
                    <span class="info-box-number"><?php echo htmlspecialchars($total_especialistas ?? '0'); ?></span>
                    <span class="progress-description">Total de Especialistas</span>
                </div>
            </div>
        </div>

        <!-- Info Box para Categorías -->
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-teal"><i class="fa fa-tags"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Categorías</span>
                    <span class="info-box-number"><?php echo htmlspecialchars($total_categorias ?? '0'); ?></span>
                    <span class="progress-description">Total de Categorías</span>
                </div>
            </div>
        </div>

        <!-- Info Box para Pagos -->
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-olive"><i class="fa fa-credit-card"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pagos</span>
                    <span class="info-box-number"><?php echo htmlspecialchars($total_pagos ?? '0'); ?></span>
                    <span class="progress-description">Total de Pagos Registrados</span>
                </div>
            </div>
        </div>

        <!-- Info Box para Inscripciones Activas -->
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Inscripciones Activas</span>
                    <span class="info-box-number"><?php echo htmlspecialchars($total_inscripciones_activas ?? '0'); ?></span>
                    <span class="progress-description">Estudiantes Activos</span>
                </div>
            </div>
        </div>

        <!-- Info Box para Inscripciones Pendientes -->
        <div class="col-md-3 col-sm-6 col-xs-12">
    <div class="info-box">
        <span class="info-box-icon bg-orange">
            <i class="fa-solid fa-clock"></i> <!-- Ícono moderno -->
        </span>
        <div class="info-box-content">
            <span class="info-box-text">Pendientes de Aprobación</span>
                                <span class="info-box-number"><?php echo htmlspecialchars($total_inscripciones_pendientes ?? '0'); ?></span>
            <span class="progress-description">Esperando Aprobación</span>
        </div>
    </div>
</div>



</section>



<?php include('footer.php'); ?>
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
