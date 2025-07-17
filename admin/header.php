<?php
ob_start();
include("inc/session_config.php");
include_once("inc/config.php");
include_once("inc/functions.php");
include_once("inc/CSRF_Protect.php");
$csrf = new CSRF_Protect();
$error_message = '';
$success_message = '';

// Obtener configuraciones del sitio
$statement = $pdo->prepare("SELECT * FROM configuraciones WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $logo = $row['logo'] ?? '';
    $favicon = $row['favicon'] ?? '';
    $whatsapp_numero = $row['whatsapp_numero'] ?? '51999999999';
    $whatsapp_mensaje = $row['whatsapp_mensaje'] ?? 'Hola, me interesa saber más sobre sus cursos';
}

// Generar enlace de WhatsApp
$whatsapp_link = "https://wa.me/{$whatsapp_numero}?text=" . urlencode($whatsapp_mensaje);

// Check if the user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id_usuario'])) {
    header('location: login.php');
    exit;
}

// Obtener datos actualizados del usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios_admin WHERE id_usuario = ?");
$stmt->execute([$_SESSION['user']['id_usuario']]);
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Actualizar la sesión con los datos más recientes solo si se encontró el usuario
if ($current_user) {
    $_SESSION['user'] = array_merge($_SESSION['user'], $current_user);
} else {
    // Si no se encuentra el usuario, cerrar sesión
    session_destroy();
    header('location: login.php');
    exit;
}

// Safely handle user photo display
$userPhoto = !empty($current_user['foto']) && file_exists('img/' . $current_user['foto']) ? $current_user['foto'] : 'default.jpg';
$userName = isset($current_user['nombre_completo']) ? htmlspecialchars($current_user['nombre_completo']) : 'Usuario';
$userRole = isset($current_user['rol']) ? htmlspecialchars($current_user['rol']) : 'Usuario';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    
    <!-- Favicon dinámico -->
    <?php if (!empty($favicon)): ?>
    <link rel="icon" type="image/x-icon" href="../assets/uploads/<?php echo $favicon; ?>">
    <link rel="shortcut icon" type="image/x-icon" href="../assets/uploads/<?php echo $favicon; ?>">
    <?php endif; ?>
    
    <!-- CSS Locales para el panel admin -->
    <link rel="stylesheet" href="/certificado/admin/css/bootstrap.min.css">
    <link rel="stylesheet" href="/certificado/admin/fontawesome-free-6.7.2-web/css/all.min.css">
    <link rel="stylesheet" href="/certificado/admin/css/dataTables.bootstrap.css">
    <link rel="stylesheet" href="/certificado/admin/css/select2.min.css">
    <link rel="stylesheet" href="/certificado/admin/css/jquery.fancybox.css">
    <link rel="stylesheet" href="/certificado/admin/css/on-off-switch.css">
    <link rel="stylesheet" href="/certificado/admin/css/summernote.css">
    <link rel="stylesheet" href="/certificado/admin/css/AdminLTE.min.css">
    <link rel="stylesheet" href="/certificado/admin/css/_all-skins.min.css">
    <link rel="stylesheet" href="/certificado/admin/css/custom.css">
    <link rel="stylesheet" href="/certificado/admin/css/style.css">
</head>


<body class="hold-transition fixed skin-blue sidebar-mini">

    <div class="wrapper">

        <header class="main-header">

            <div class="logo">
                <a href="index.php" class="logo">
                    <?php if (!empty($logo)): ?>
                        <span class="logo-mini">
                            <img src="../assets/uploads/<?php echo $logo; ?>" alt="Logo" style="height: 30px; width: auto;">
                        </span>
                        <span class="logo-lg">
                            <img src="../assets/uploads/<?php echo $logo; ?>" alt="Logo" style="height: 40px; width: auto;">
                        </span>
                    <?php else: ?>
                        <span class="logo-mini"><b>S</b>C</span>
                        <span class="logo-lg"><b>Sistema</b> Certificados</span>
                    <?php endif; ?>
                </a>
            </div>

            <nav class="navbar navbar-static-top">

                <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                    <span class="sr-only">Alternar navegación</span>
                </a>

                <span style="float:left;line-height:50px;color:#fff;padding-left:15px;font-size:18px;">Panel de Administración</span>

                <!-- Barra superior ... Información del usuario ... Área de inicio/cierre de sesión -->
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <!-- Usuario -->
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                <img src="img/<?php echo $userPhoto; ?>" class="user-image" alt="Imagen de usuario">
                                <span class="hidden-xs"><?php echo $userName; ?></span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- Imagen de usuario -->
                                <li class="user-header">
                                    <img src="img/<?php echo $userPhoto; ?>" class="img-circle" alt="Imagen de usuario">
                                    <p>
                                        <?php echo $userName; ?>
                                        <small><?php echo $userRole; ?></small>
                                    </p>
                                </li>
                                <!-- Menú Footer-->
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="profile-edit.php" class="btn btn-default btn-flat">Editar Perfil</a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="logout.php" class="btn btn-default btn-flat">Cerrar Sesión</a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>

            </nav>
        </header>

        <?php $cur_page = substr($_SERVER["SCRIPT_NAME"], strrpos($_SERVER["SCRIPT_NAME"], "/") + 1); ?>
        <!-- Side Bar to Manage Shop Activities -->
        <aside class="main-sidebar">
            <section class="sidebar">

                <ul class="sidebar-menu">
                    <!-- Panel de Control -->
                    <li class="header">PANEL PRINCIPAL</li>
                    
                    <li class="<?php if ($cur_page == 'index.php') { echo 'active'; } ?>">
                        <a href="index.php">
                            <i class="fas fa-tachometer-alt"></i> <span>Panel de Control</span>
                        </a>
                    </li>

                    <!-- Configuración del Sistema -->
                    <li class="header">CONFIGURACIÓN DEL SISTEMA</li>
                    
                    <li class="<?php if ($cur_page == 'settings.php') { echo 'active'; } ?>">
                        <a href="settings.php">
                            <i class="fas fa-cogs"></i> <span>Ajustes del Sitio</span>
                        </a>
                    </li>

                    <!-- Gestión Académica -->
                    <li class="header">GESTIÓN ACADÉMICA</li>
                    
                    <li class="<?php if($cur_page == 'curso.php') { echo 'active'; } ?>">
                        <a href="curso.php"><i class="fa fa-book"></i> <span>Cursos</span></a>
                    </li>

                    <li class="<?php if($cur_page == 'modulo.php') { echo 'active'; } ?>">
                        <a href="modulo.php"><i class="fa fa-cubes"></i> <span>Módulos</span></a>
                    </li>

                    <!-- Configuración Institucional -->
                    <li class="header">CONFIGURACIÓN INSTITUCIONAL</li>
                    
                    <li class="<?php if($cur_page == 'entidad.php') { echo 'active'; } ?>">
                        <a href="entidad.php"><i class="fa fa-building"></i> <span>Entidades (Colegios)</span></a>
                    </li>

                    <li class="treeview <?php if(in_array($cur_page, ['cargo.php', 'categoria.php', 'genero.php', 'rol.php'])) { echo 'active'; } ?>">
                        <a href="#">
                            <i class="fa fa-cog"></i> <span>Parámetros Básicos</span>
                            <i class="fa fa-angle-left pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li class="<?php if($cur_page == 'cargo.php') { echo 'active'; } ?>">
                                <a href="cargo.php"><i class="fa fa-briefcase"></i> Cargos</a>
                            </li>
                            <li class="<?php if($cur_page == 'categoria.php') { echo 'active'; } ?>">
                                <a href="categoria.php"><i class="fa fa-tags"></i> Categorías</a>
                            </li>
                            <li class="<?php if($cur_page == 'genero.php') { echo 'active'; } ?>">
                                <a href="genero.php"><i class="fa fa-venus-mars"></i> Géneros</a>
                            </li>
                            <li class="<?php if($cur_page == 'rol.php') { echo 'active'; } ?>">
                                <a href="rol.php"><i class="fa fa-user-secret"></i> Roles</a>
                            </li>
                        </ul>
                    </li>

                    <!-- Gestión de Usuarios -->
                    <li class="header">GESTIÓN DE USUARIOS</li>
                    
                    <li class="treeview <?php if(in_array($cur_page, ['instructor.php', 'especialista.php', 'cliente.php', 'usuario.php'])) { echo 'active'; } ?>">
                        <a href="#">
                            <i class="fa fa-users"></i> <span>Usuarios del Sistema</span>
                            <i class="fa fa-angle-left pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li class="<?php if($cur_page == 'instructor.php') { echo 'active'; } ?>">
                                <a href="instructor.php"><i class="fa fa-user"></i> Instructores</a>
                            </li>
                            <li class="<?php if($cur_page == 'especialista.php') { echo 'active'; } ?>">
                                <a href="especialista.php"><i class="fa fa-user-md"></i> Especialistas</a>
                            </li>
                            <li class="<?php if($cur_page == 'cliente.php') { echo 'active'; } ?>">
                                <a href="cliente.php"><i class="fa fa-users"></i> Clientes</a>
                            </li>
                            <li class="<?php if($cur_page == 'usuario.php') { echo 'active'; } ?>">
                                <a href="usuario.php"><i class="fa fa-user-cog"></i> Administradores</a>
                            </li>
                        </ul>
                    </li>

                    <!-- Gestión Académica -->
                    <li class="header">GESTIÓN ACADÉMICA</li>
                    
                    <li class="<?php if($cur_page == 'inscripcion.php') { echo 'active'; } ?>">
                        <a href="inscripcion.php"><i class="fas fa-user-check"></i> <span>Inscripciones</span></a>
                    </li>

                    <li class="treeview <?php if(in_array($cur_page, ['aprobar_alumno.php', 'certificados_generados.php', 'previsualizar_certificado_final.php', 'gestionar_certificados.php', 'generar_certificado.php', 'auto_generar_certificado.php'])) { echo 'active'; } ?>">
                        <a href="#">
                            <i class="fa fa-certificate"></i> <span>Certificados</span>
                            <i class="fa fa-angle-left pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li class="<?php if($cur_page == 'aprobar_alumno.php') { echo 'active'; } ?>">
                                <a href="aprobar_alumno.php"><i class="fa fa-user-check"></i> Aprobar Alumnos</a>
                            </li>
                            <li class="<?php if($cur_page == 'gestionar_certificados.php') { echo 'active'; } ?>">
                                <a href="gestionar_certificados.php"><i class="fa fa-cog"></i> Configurar Certificados</a>
                            </li>
                        </ul>
                    </li>

                    <!-- Gestión Financiera -->
                    <li class="header">GESTIÓN FINANCIERA</li>
                    
                    <li class="treeview <?php if(in_array($cur_page, ['pago.php', 'pagos.php'])) { echo 'active'; } ?>">
                        <a href="#">
                            <i class="fas fa-credit-card"></i> <span>Pagos</span>
                            <i class="fa fa-angle-left pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li class="<?php if($cur_page == 'pago.php') { echo 'active'; } ?>">
                                <a href="pago.php"><i class="fa fa-list"></i> Ver Pagos</a>
                            </li>
                        </ul>
                    </li>

                    <!-- Contenido Web -->
                    <li class="header">CONTENIDO WEB</li>
                    
                    <li class="<?php if($cur_page == 'slider.php') { echo 'active'; } ?>">
                        <a href="slider.php"><i class="fa fa-image"></i> <span>Sliders</span></a>
                    </li>
                    <li class="treeview <?php if(in_array($cur_page, ['suscriptor.php', 'social-media.php'])) { echo 'active'; } ?>">
                        <a href="#">
                            <i class="fa fa-share-alt"></i> <span>Comunicación</span>
                            <i class="fa fa-angle-left pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li class="<?php if($cur_page == 'suscriptor.php') { echo 'active'; } ?>">
                                <a href="suscriptor.php"><i class="fa fa-envelope"></i> Suscriptores</a>
                            </li>
                            <li class="<?php if($cur_page == 'social-media.php') { echo 'active'; } ?>">
                                <a href="social-media.php"><i class="fa fa-share-alt"></i> Redes Sociales</a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?php if($cur_page == 'pagina.php') { echo 'active'; } ?>">
                        <a href="pagina.php"><i class="fa fa-file-text"></i> <span>Páginas</span></a>
                    </li>

                    <li class="<?php if($cur_page == 'faq.php') { echo 'active'; } ?>">
                        <a href="faq.php"><i class="fa fa-question-circle"></i> <span>Preguntas Frecuentes</span></a>
                    </li>

                    
                </ul>
            </section>
        </aside>

        <div class="content-wrapper">