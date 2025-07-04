<?php
ob_start();
include("inc/session_config.php");
include_once("inc/config.php");
include_once("inc/functions.php");
include_once("inc/CSRF_Protect.php");
$csrf = new CSRF_Protect();
$error_message = '';
$success_message = '';

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap.min.css">
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap.min.js"></script>
    
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/AdminLTE.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/skins/_all-skins.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/ionicons.min.css">
    <link rel="stylesheet" href="css/datepicker3.css">
    <link rel="stylesheet" href="css/all.css">
    <link rel="stylesheet" href="css/select2.min.css">
    <link rel="stylesheet" href="css/dataTables.bootstrap.css">
    <link rel="stylesheet" href="css/jquery.fancybox.css">
    <link rel="stylesheet" href="css/AdminLTE.min.css">
    <link rel="stylesheet" href="css/_all-skins.min.css">
    <link rel="stylesheet" href="css/on-off-switch.css" />
    <link rel="stylesheet" href="css/summernote.css">
    <link rel="stylesheet" href="css/header.css">

    <!-- AdminLTE JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/js/adminlte.min.js"></script>
    
    <!-- Custom JS -->
    <script>
    $(document).ready(function() {
        // Inicializar el menú desplegable
        $('.dropdown-toggle').dropdown();
        
        // Asegurar que el menú desplegable funcione en dispositivos móviles
        $('.dropdown').on('show.bs.dropdown', function () {
            $(this).find('.dropdown-menu').first().stop(true, true).slideDown(200);
        });
        
        $('.dropdown').on('hide.bs.dropdown', function () {
            $(this).find('.dropdown-menu').first().stop(true, true).slideUp(200);
        });
    });
    </script>

</head>

<body class="hold-transition fixed skin-blue sidebar-mini">

    <div class="wrapper">

        <header class="main-header">

            <div class="logo">
                <a href="index.php" class="logo">
                    <span class="logo-mini"><b>S</b>C</span>
                    <span class="logo-lg"><b>Sistema de Certificados</b></span>
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

                    <li class="treeview <?php if ($cur_page == 'index.php') {
                                            echo 'active';
                                        } ?>">
                        <a href="index.php">
                            <i class="fa fa-hand-o-right"></i> <span>Panel de Control</span>
                        </a>
                    </li>

                    <li class="treeview <?php if (($cur_page == 'settings.php')) {
                                            echo 'active';
                                        } ?>">
                        <a href="settings.php">
                            <i class="fa fa-hand-o-right"></i> <span>Ajustes del Sitio Web</span>
                        </a>
                    </li>

                    <li class="treeview <?php if(in_array($cur_page, ['cargo.php', 'categoria.php', 'genero.php', 'rol.php', 'hora-lectiva.php'])) { echo 'active'; } ?>">
                        <a href="#">
                            <i class="fa fa-cog"></i> <span>Configuración Simple</span>
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

                    <li class="<?php if($cur_page == 'entidad.php') { echo 'active'; } ?>">
                        <a href="entidad.php"><i class="fa fa-building"></i> Entidades</a>
                    </li>

                    <li class="<?php if($cur_page == 'modulo.php') { echo 'active'; } ?>">
                        <a href="modulo.php"><i class="fa fa-cubes"></i> <span>Módulos</span></a>
                    </li>

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
                        <a href="usuario.php"><i class="fa fa-user"></i> Usuarios</a>
                    </li>
                    
                    <li class="<?php if($cur_page == 'faq.php') { echo 'active'; } ?>">
                     <a href="faq.php"><i class="fa fa-question-circle"></i> Preguntas frecuentes</a>
                       </li>

                    <li class="<?php if($cur_page == 'curso.php') { echo 'active'; } ?>">
                        <a href="curso.php"><i class="fa fa-book"></i> Cursos</a>
                    </li>

                    <li class="<?php if($cur_page == 'inscripcion.php') { echo 'active'; } ?>">
                        <a href="inscripcion.php"><i class="fa fa-check-square-o"></i> Inscripciones</a>
                    </li>

                    <li class="<?php if($cur_page == 'gestionar_certificados.php') { echo 'active'; } ?>">
                        <a href="gestionar_certificados.php"><i class="fa fa-certificate"></i>Detalles Certificados</a>
                    </li>

                    <li class="<?php if($cur_page == 'pago.php') { echo 'active'; } ?>">
                        <a href="pago.php"><i class="fa fa-money"></i> Pagos</a>
                    </li>

                    <li class="<?php if($cur_page == 'slider.php') { echo 'active'; } ?>">
                        <a href="slider.php"><i class="fa fa-image"></i> <span>Sliders</span></a>
                    </li>

                    <li class="<?php if($cur_page == 'pagina.php') { echo 'active'; } ?>">
                        <a href="pagina.php"><i class="fa fa-file-text"></i> <span>Páginas</span></a>
                    </li>

                    <li class="<?php if($cur_page == 'suscriptor.php') { echo 'active'; } ?>">
                        <a href="suscriptor.php"><i class="fa fa-cog"></i> <span>Suscriptores</span></a>
                    </li>

                    <li class="<?php if($cur_page == 'social-media.php') { echo 'active'; } ?>">
                        <a href="social-media.php"><i class="fa fa-share-alt"></i> <span>Redes Sociales</span></a>
                    </li>

                    
                </ul>
            </section>
        </aside>


        <div class="content-wrapper">