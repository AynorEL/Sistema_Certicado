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
    
    <!-- Local CSS Assets -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/select2.min.css">
    <link rel="stylesheet" href="../admin/css/dataTables.bootstrap.css">
    <link rel="stylesheet" href="../admin/css/jquery.fancybox.css">
    <link rel="stylesheet" href="../admin/css/on-off-switch.css">
    <link rel="stylesheet" href="../admin/css/summernote.css">

    <!-- Local JavaScript Assets -->
    <script src="../assets/js/jquery-2.2.4.min.js"></script>

    <!-- External CDN Resources -->
    <!-- Bootstrap CSS + JS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <!-- DataTables CSS + JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap.min.css">
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap.min.js"></script>

    <!-- Select2 CSS + JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- FontAwesome -->
    <link rel="stylesheet" href="fontawesome-free-6.7.2-web/css/all.min.css">

    <!-- AdminLTE CSS + JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/AdminLTE.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/skins/_all-skins.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/js/adminlte.min.js"></script>

    <!-- Custom CSS para AdminLTE -->
    <style>
        /* Separadores mejorados - Más visibles y organizados */
        .sidebar-menu .header {
            color: #ffffff;
            background: linear-gradient(135deg, #3c8dbc 0%, #2c3e50 100%);
            font-size: 13px;
            font-weight: bold;
            padding: 12px 15px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            border-bottom: 2px solid #2980b9;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
            margin-top: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            white-space: normal !important;
            word-break: break-word !important;
        }

        /* Sidebar principal más claro */
        .main-sidebar {
            background-color: #2c3e50 !important;
        }

        /* Elementos del menú más claros */
        .sidebar-menu > li > a {
            color: #ecf0f1 !important;
            background-color: #34495e !important;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
        }

        /* Estilos para elementos activos - Más visibles */
        .sidebar-menu .treeview.active > a,
        .sidebar-menu .treeview-menu > li.active > a,
        .sidebar-menu > li.active > a {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%) !important;
            color: #fff !important;
            border-left: 4px solid #f39c12;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }

        /* Hover effects más claros */
        .sidebar-menu .treeview > a:hover,
        .sidebar-menu .treeview-menu > li > a:hover,
        .sidebar-menu > li > a:hover {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%) !important;
            color: #fff !important;
            border-left: 4px solid #f39c12;
            transform: translateX(3px);
        }

        /* Submenús más claros */
        .treeview-menu {
            background-color: #34495e !important;
            transition: all 0.3s ease;
            border-left: 2px solid #3498db;
        }

        .treeview-menu > li > a {
            color: #bdc3c7 !important;
            background-color: #2c3e50 !important;
            padding-left: 35px !important;
            border-left: 2px solid transparent;
        }

        .treeview-menu > li > a:hover {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%) !important;
            color: #fff !important;
            border-left: 2px solid #f39c12;
        }

        /* Submenús activos */
        .treeview-menu > li.active > a {
            background: linear-gradient(135deg, #e67e22 0%, #d35400 100%) !important;
            color: #fff !important;
            border-left: 2px solid #f39c12;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);
        }

        /* Iconos en el menú */
        .sidebar-menu .treeview > a > i,
        .sidebar-menu .treeview-menu > li > a > i,
        .sidebar-menu > li > a > i {
            width: 20px;
            text-align: center;
            margin-right: 8px;
            transition: all 0.3s ease;
        }

        /* Flecha de submenú */
        .sidebar-menu .treeview > a > .fa-angle-left {
            transition: transform 0.3s ease;
        }

        .sidebar-menu .treeview.menu-open > a > .fa-angle-left {
            transform: rotate(-90deg);
        }

        /* Logo mejorado */
        .logo-lg img {
            max-height: 40px;
            width: auto;
        }

        .logo-mini img {
            max-height: 30px;
            width: auto;
        }

        /* Header mejorado */
        .main-header .navbar {
            background: linear-gradient(135deg, #3c8dbc 0%, #2c3e50 100%);
        }

        /* Usuario dropdown mejorado */
        .user-header {
            background: linear-gradient(135deg, #3c8dbc 0%, #2c3e50 100%);
        }

        /* Toggle button mejorado para AdminLTE */
        .sidebar-toggle {
            color: #fff !important;
            background: transparent !important;
            border: none !important;
            padding: 15px !important;
            font-size: 18px !important;
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            background-color: rgba(255,255,255,0.1) !important;
            color: #f39c12 !important;
        }

        /* Indicador de página actual en el breadcrumb */
        .current-page-indicator {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-left: 10px;
        }
    </style>

    <!-- Custom JavaScript -->
    <script>
        $(document).ready(function () {
            $('.dropdown-toggle').dropdown();

            $('.dropdown').on('show.bs.dropdown', function () {
                $(this).find('.dropdown-menu').first().stop(true, true).slideDown(200);
            });

            $('.dropdown').on('hide.bs.dropdown', function () {
                $(this).find('.dropdown-menu').first().stop(true, true).slideUp(200);
            });

            // Restaurar estado del sidebar al cargar (usando AdminLTE)
            if (localStorage.getItem('sidebar-collapsed') === 'true') {
                $('body').addClass('sidebar-collapse');
            }

            // Activar automáticamente el menú padre cuando un submenú está activo
            $('.treeview-menu > li.active').parents('.treeview').addClass('active menu-open');
            
            // Funcionalidad mejorada para submenús
            $('.treeview > a').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var $parent = $(this).parent();
                var $submenu = $parent.find('> .treeview-menu');
                
                // Solo cerrar otros submenús si no estamos en el mismo grupo
                var currentGroup = $parent.closest('.sidebar-menu > li');
                $('.treeview-menu').not($submenu).slideUp(300);
                $('.treeview').not($parent).removeClass('menu-open');
                
                // Toggle del submenú actual
                if ($submenu.is(':visible')) {
                    $submenu.slideUp(300);
                    $parent.removeClass('menu-open');
                } else {
                    $submenu.slideDown(300);
                    $parent.addClass('menu-open');
                }
            });

            // Prevenir que los enlaces de submenú cierren el menú
            $('.treeview-menu > li > a').on('click', function(e) {
                e.stopPropagation();
                // No cerrar el submenú al hacer clic en un enlace
            });

            // Guardar estado del sidebar cuando AdminLTE lo cambie
            $(document).on('click', '.sidebar-toggle', function() {
                setTimeout(function() {
                    if ($('body').hasClass('sidebar-collapse')) {
                        localStorage.setItem('sidebar-collapsed', 'true');
                    } else {
                        localStorage.setItem('sidebar-collapsed', 'false');
                    }
                }, 100);
            });

            // Agregar indicador visual de página actual
            var currentPage = '<?php echo $cur_page; ?>';
            var currentPageName = '';
            
            // Mapeo de nombres de página
            var pageNames = {
                'index.php': 'Panel de Control',
                'settings.php': 'Ajustes del Sitio',
                'cargo.php': 'Cargos',
                'categoria.php': 'Categorías',
                'genero.php': 'Géneros',
                'rol.php': 'Roles',
                'entidad.php': 'Entidades',
                'modulo.php': 'Módulos del Sistema',
                'instructor.php': 'Instructores',
                'especialista.php': 'Especialistas',
                'cliente.php': 'Clientes',
                'usuario.php': 'Administradores',
                'curso.php': 'Cursos',
                'inscripcion.php': 'Inscripciones',
                'aprobar_alumno.php': 'Aprobar Alumnos',
                'gestionar_certificados.php': 'Configurar Certificados',
                'generar_certificado.php': 'Generar Certificados',
                'certificados_generados.php': 'Ver Certificados',
                'pago.php': 'Ver Pagos',
                'pagos.php': 'Reportes de Pagos',
                'slider.php': 'Sliders',
                'pagina.php': 'Páginas',
                'faq.php': 'Preguntas Frecuentes',
                'suscriptor.php': 'Suscriptores',
                'social-media.php': 'Redes Sociales',
                'report.php': 'Reportes Generales'
            };
            
            currentPageName = pageNames[currentPage] || currentPage;
            
            // Agregar indicador al breadcrumb si existe
            if ($('.breadcrumb').length > 0) {
                $('.breadcrumb').append('<span class="current-page-indicator">' + currentPageName + '</span>');
            }
        });
    </script>
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
                            <li class="<?php if($cur_page == 'generar_certificado.php') { echo 'active'; } ?>">
                                <a href="generar_certificado.php"><i class="fa fa-file-pdf"></i> Generar Certificados</a>
                            </li>
                            <li class="<?php if($cur_page == 'certificados_generados.php') { echo 'active'; } ?>">
                                <a href="certificados_generados.php"><i class="fa fa-list"></i> Ver Certificados</a>
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
                            <li class="<?php if($cur_page == 'pagos.php') { echo 'active'; } ?>">
                                <a href="pagos.php"><i class="fa fa-chart-bar"></i> Reportes</a>
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