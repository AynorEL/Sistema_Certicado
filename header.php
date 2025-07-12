<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
require_once('admin/inc/config.php');
require_once('admin/inc/functions.php');
require_once('admin/inc/CSRF_Protect.php');
$csrf = new CSRF_Protect();

// Obtener variables de idioma
$statement = $pdo->prepare("SELECT * FROM idioma");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
$lang = array();
foreach ($result as $row) {
	$lang[$row['nombre_idioma']] = $row['valor_idioma'];
}

// Obtener configuraciones
$statement = $pdo->prepare("SELECT * FROM configuraciones WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
	$logo = $row['logo'] ?? '';
	$favicon = $row['favicon'] ?? '';
	$telefono_contacto = $row['telefono_contacto'];
	$correo_contacto = $row['correo_contacto'];
	$whatsapp_numero = $row['whatsapp_numero'] ?? '51999999999';
	$whatsapp_mensaje = $row['whatsapp_mensaje'] ?? 'Hola, me interesa saber más sobre sus cursos';
	$after_body = $row['after_body'] ?? '';
}

// Generar enlace de WhatsApp
$whatsapp_link = "https://wa.me/{$whatsapp_numero}?text=" . urlencode($whatsapp_mensaje);

// Obtener redes sociales
$statement = $pdo->prepare("SELECT * FROM redes_sociales");
$statement->execute();
$redes_sociales = $statement->fetchAll(PDO::FETCH_ASSOC);

// Calcular cantidad de items en el carrito
$clienteId = $_SESSION['customer']['idcliente'] ?? null;
$cart_count = 0;
if ($clienteId && isset($_SESSION['carritos'][$clienteId])) {
    $cart_count = count($_SESSION['carritos'][$clienteId]);
}

// Detectar si estamos en admin
$is_admin = strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false;
?>
<!DOCTYPE html>
<html lang="es">

<head>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Sistema de Certificados</title>
	<link rel="icon" type="image/png" href="assets/uploads/<?php echo $favicon; ?>">
	<!-- ✅ CSS -->
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
	<link href="admin/fontawesome-free-6.7.2-web/css/all.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.css" />
	<!-- ✅ JS: jQuery primero -->
	<script src="admin/js/jquery-2.2.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<!-- ✅ Plugin jQuery Toast DESPUÉS de jQuery -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>

	<style>
		:root {
			--primary-color: #007bff;
			--secondary-color: #6c757d;
			--success-color: #28a745;
			--dark-color: #343a40;
		}
		
		/* Estilos globales mejorados */
		* {
			box-sizing: border-box;
		}
		
		body {
			font-family: 'Roboto', sans-serif;
			line-height: 1.4;
			margin: 0;
			padding: 0;
		}
		
		/* Contenedor responsivo */
		.container {
			width: 100%;
			max-width: 1400px;
			margin: 0 auto;
			padding: 0 20px;
		}
		
		.container-fluid {
			width: 100%;
			padding: 0 20px;
		}
		
		/* Grid system mejorado */
		.row {
			margin-left: -15px;
			margin-right: -15px;
		}
		
		.col-md-1, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6,
		.col-md-7, .col-md-8, .col-md-9, .col-md-10, .col-md-11, .col-md-12,
		.col-sm-1, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6,
		.col-sm-7, .col-sm-8, .col-sm-9, .col-sm-10, .col-sm-11, .col-sm-12,
		.col-xs-1, .col-xs-2, .col-xs-3, .col-xs-4, .col-xs-5, .col-xs-6,
		.col-xs-7, .col-xs-8, .col-xs-9, .col-xs-10, .col-xs-11, .col-xs-12 {
			position: relative;
			min-height: 1px;
			padding-left: 15px;
			padding-right: 15px;
		}
		
		/* Responsive breakpoints */
		@media (min-width: 768px) {
			.col-sm-1, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6,
			.col-sm-7, .col-sm-8, .col-sm-9, .col-sm-10, .col-sm-11, .col-sm-12 {
				float: left;
			}
			.col-sm-12 { width: 100%; }
			.col-sm-11 { width: 91.66666667%; }
			.col-sm-10 { width: 83.33333333%; }
			.col-sm-9 { width: 75%; }
			.col-sm-8 { width: 66.66666667%; }
			.col-sm-7 { width: 58.33333333%; }
			.col-sm-6 { width: 50%; }
			.col-sm-5 { width: 41.66666667%; }
			.col-sm-4 { width: 33.33333333%; }
			.col-sm-3 { width: 25%; }
			.col-sm-2 { width: 16.66666667%; }
			.col-sm-1 { width: 8.33333333%; }
		}
		
		@media (min-width: 992px) {
			.col-md-1, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6,
			.col-md-7, .col-md-8, .col-md-9, .col-md-10, .col-md-11, .col-md-12 {
				float: left;
			}
			.col-md-12 { width: 100%; }
			.col-md-11 { width: 91.66666667%; }
			.col-md-10 { width: 83.33333333%; }
			.col-md-9 { width: 75%; }
			.col-md-8 { width: 66.66666667%; }
			.col-md-7 { width: 58.33333333%; }
			.col-md-6 { width: 50%; }
			.col-md-5 { width: 41.66666667%; }
			.col-md-4 { width: 33.33333333%; }
			.col-md-3 { width: 25%; }
			.col-md-2 { width: 16.66666667%; }
			.col-md-1 { width: 8.33333333%; }
		}
		
		@media (min-width: 1200px) {
			.col-lg-1, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6,
			.col-lg-7, .col-lg-8, .col-lg-9, .col-lg-10, .col-lg-11, .col-lg-12 {
				float: left;
			}
			.col-lg-12 { width: 100%; }
			.col-lg-11 { width: 91.66666667%; }
			.col-lg-10 { width: 83.33333333%; }
			.col-lg-9 { width: 75%; }
			.col-lg-8 { width: 66.66666667%; }
			.col-lg-7 { width: 58.33333333%; }
			.col-lg-6 { width: 50%; }
			.col-lg-5 { width: 41.66666667%; }
			.col-lg-4 { width: 33.33333333%; }
			.col-lg-3 { width: 25%; }
			.col-lg-2 { width: 16.66666667%; }
			.col-lg-1 { width: 8.33333333%; }
		}
		
		/* Clearfix */
		.row:before,
		.row:after {
			content: " ";
			display: table;
		}
		.row:after {
			clear: both;
		}
		
		body {
			font-family: 'Roboto', sans-serif;
			line-height: 1.4;
			font-size: 16px;
		}
		
		/* Aumentar tamaños de fuente generales */
		h1 { font-size: 2.5rem; }
		h2 { font-size: 2rem; }
		h3 { font-size: 1.75rem; }
		h4 { font-size: 1.5rem; }
		h5 { font-size: 1.25rem; }
		h6 { font-size: 1rem; }
		
		p { font-size: 1.1rem; }
		.btn { font-size: 1rem; }
		.form-control { font-size: 1rem; }

		.top {
			background: #f8f9fa;
			padding: 5px 0;
			border-bottom: 1px solid #eee;
			font-size: 0.9rem;
		}

		.top .left ul {
			list-style: none;
			margin: 0;
			padding: 0;
		}

		.top .left ul li {
			display: inline-block;
			margin-right: 15px;
			color: #666;
		}

		.top .right ul {
			list-style: none;
			margin: 0;
			padding: 0;
			text-align: right;
		}

		.top .right ul li {
			display: inline-block;
			margin-left: 10px;
		}

		.top .right ul li a {
			color: #666;
			text-decoration: none;
			transition: color 0.3s ease;
		}

		.top .right ul li a:hover {
			color: var(--primary-color);
		}

		.header {
			padding: 10px 0;
			background: linear-gradient(135deg, #3b82f6 0%, #9333ea 50%, #ec4899 100%);
			box-shadow: 0 4px 20px rgba(0,0,0,0.15);
			color: white;
		}

		.header .logo img {
			max-height: 40px;
		}

		.header .right ul {
			list-style: none;
			margin: 0;
			padding: 0;
			text-align: right;
		}

		.header .right ul li {
			display: inline-block;
			margin-left: 15px;
		}

		.header .right ul li a {
			color: white;
			text-decoration: none;
			font-weight: 500;
			font-size: 0.9rem;
		}

		.header .right ul li a:hover {
			color: #fbbf24;
		}

		.search-area {
			text-align: right;
		}

		.search-area .form-control {
			border-radius: 15px;
			padding: 5px 12px;
			font-size: 0.9rem;
			height: auto;
		}

		.search-area .btn {
			border-radius: 15px;
			padding: 5px 15px;
			background: var(--primary-color);
			color: #fff;
			font-size: 0.9rem;
		}

		.search-area .btn:hover {
			background: #0056b3;
		}

		.nav {
			background: linear-gradient(135deg, #1e40af 0%, #7c3aed 50%, #be185d 100%);
			padding: 0;
			margin-bottom: 15px;
			box-shadow: 0 2px 10px rgba(0,0,0,0.1);
		}

		.menu > ul > li > a {
			padding: 10px 15px;
			font-size: 0.95rem;
		}

		.menu ul ul li a {
			padding: 8px 15px;
			font-size: 0.9rem;
		}

		/* Estilos responsivos mejorados */
		@media (max-width: 768px) {
			.container {
				padding: 0 10px;
			}
			
			.top {
				padding: 5px 0;
			}
			
			.top .left ul li,
			.top .right ul li {
				margin: 2px 0;
				font-size: 0.8rem;
			}
			
			/* Header más compacto en móvil */
			.header {
				padding: 8px 0;
			}
			
			.header .row {
				display: flex;
				flex-direction: row;
				align-items: center;
				flex-wrap: wrap;
			}
			
			.header .col-md-3 {
				width: 50%;
				text-align: center;
				margin-bottom: 8px;
			}
			
			.header .col-md-6 {
				width: 100%;
				order: 3;
				margin-top: 8px;
			}
			
			.header .logo {
				margin-bottom: 0;
			}
			
			.header .logo img {
				max-height: 40px;
			}
			
			.header .list-inline {
				flex-direction: row;
				justify-content: center;
				gap: 15px !important;
				margin: 0;
			}
			
			.header .list-inline li {
				margin: 0 !important;
			}
			
			.search-container {
				width: 100%;
				max-width: none;
			}
			
			.search-container input {
				width: 100%;
				font-size: 16px;
				padding: 8px 12px;
			}
			
			.cart-icon {
				margin-right: 0 !important;
			}
			
			/* Navegación principal visible en móvil */
			.nav {
				margin-bottom: 10px;
			}
			
			.menu > ul > li > a {
				padding: 8px 12px;
				font-size: 0.9rem;
			}
		}
			
					/* Estilos responsivos generales */
		.container {
			padding: 0 15px;
		}
		}
		
		@media (max-width: 480px) {
			.container {
				padding: 0 8px;
			}
			
			.top {
				padding: 3px 0;
			}
			
			.top .left ul li,
			.top .right ul li {
				font-size: 0.75rem;
				margin: 1px 0;
			}
			
			.header {
				padding: 5px 0;
			}
			
			.header .col-md-3 {
				width: 100%;
				margin-bottom: 5px;
			}
			
			.header .logo img {
				max-height: 35px;
			}
			
			.header .list-inline {
				gap: 10px !important;
			}
			
			.header .list-inline li {
				font-size: 0.8rem;
			}
			
			.header .list-inline li a {
				font-size: 0.8rem;
			}
			
			.search-container {
				max-width: none;
			}
			
			.search-container input {
				font-size: 14px;
				padding: 6px 10px;
			}
			
			.cart-icon i {
				font-size: 1.1rem;
			}
			
			.cart-count {
				font-size: 9px !important;
				padding: 1px 3px !important;
			}
			
			/* Navegación más compacta */
			.nav {
				margin-bottom: 8px;
			}
			
			.menu > ul > li > a {
				padding: 6px 10px;
				font-size: 0.85rem;
			}
		}
		
		/* Estilos para verificar certificado centrado */
		.verification-section {
			text-align: center;
			padding: 50px 0;
		}
		
		.verification-title {
			font-size: 2.5rem;
			font-weight: 700;
			margin-bottom: 1.5rem;
		}
		
		.verification-description {
			font-size: 1.2rem;
			margin-bottom: 2rem;
			line-height: 1.6;
		}
		
		.verification-actions {
			text-align: center;
		}
		
		/* Paneles mejorados */
		.panel {
			border: 1px solid #ddd;
			border-radius: 4px;
			box-shadow: 0 1px 3px rgba(0,0,0,0.12);
			margin-bottom: 20px;
		}
		
		.panel-body {
			padding: 15px;
		}
		
		.panel-title {
			margin-top: 0;
			margin-bottom: 10px;
			font-size: 16px;
			font-weight: 600;
		}
		
		/* Botones mejorados */
		.btn {
			border-radius: 4px;
			transition: all 0.3s ease;
		}
		
		.btn:hover {
			transform: translateY(-1px);
			box-shadow: 0 2px 8px rgba(0,0,0,0.15);
		}
		
		/* Badges mejorados */
		.badge {
			padding: 4px 8px;
			font-size: 12px;
			border-radius: 3px;
		}
		
		/* Estilos adicionales para responsividad */
		.img-responsive {
			max-width: 100%;
			height: auto;
		}
		
		.visible-xs {
			display: none !important;
		}
		
		.visible-sm {
			display: none !important;
		}
		
		.visible-md {
			display: none !important;
		}
		
		.visible-lg {
			display: block !important;
		}
		
		@media (max-width: 767px) {
			.visible-xs {
				display: block !important;
			}
			.visible-sm, .visible-md, .visible-lg {
				display: none !important;
			}
		}
		
		@media (min-width: 768px) and (max-width: 991px) {
			.visible-sm {
				display: block !important;
			}
			.visible-xs, .visible-md, .visible-lg {
				display: none !important;
			}
		}
		
		@media (min-width: 992px) and (max-width: 1199px) {
			.visible-md {
				display: block !important;
			}
			.visible-xs, .visible-sm, .visible-lg {
				display: none !important;
			}
		}
		
		@media (min-width: 1200px) {
			.visible-lg {
				display: block !important;
			}
			.visible-xs, .visible-sm, .visible-md {
				display: none !important;
			}
		}
		
		/* Mejoras para formularios */
		.form-control {
			border-radius: 4px;
			border: 1px solid #ddd;
			padding: 8px 12px;
			font-size: 14px;
			transition: border-color 0.3s ease;
		}
		
		.form-control:focus {
			border-color: var(--primary-color);
			box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
		}
		
		/* Mejoras para tablas */
		.table {
			border-radius: 4px;
			overflow: hidden;
			box-shadow: 0 1px 3px rgba(0,0,0,0.1);
		}
		
		.table th {
			background-color: #f8f9fa;
			border-bottom: 2px solid #dee2e6;
			font-weight: 600;
		}
		
		/* Mejoras para alertas */
		.alert {
			border-radius: 4px;
			border: none;
			padding: 15px;
		}
		
		/* Mejoras para modales */
		.modal-content {
			border-radius: 8px;
			box-shadow: 0 4px 20px rgba(0,0,0,0.15);
		}
		
		.modal-header {
			border-bottom: 1px solid #eee;
			padding: 20px;
		}
		
		.modal-body {
			padding: 20px;
		}
		
		.modal-footer {
			border-top: 1px solid #eee;
			padding: 15px 20px;
		}
		
		/* Mejoras adicionales para el header */
		.header {
			background: #fff;
			box-shadow: 0 2px 10px rgba(0,0,0,0.1);
			position: relative;
			z-index: 1000;
		}
		
		.header .logo img {
			max-height: 60px;
			transition: transform 0.3s ease;
		}
		
		.header .logo img:hover {
			transform: scale(1.05);
		}
		
		.header .list-inline li a {
			color: #333;
			text-decoration: none;
			font-weight: 500;
			transition: color 0.3s ease;
			font-size: 1rem;
		}
		
		.header .list-inline li a:hover {
			color: #007bff;
		}
		
		.cart-icon {
			color: #333;
			text-decoration: none;
			font-size: 1.2rem;
			transition: color 0.3s ease;
		}
		
		.cart-icon:hover {
			color: #007bff;
		}
		
		.search-container input {
			border: 2px solid #eee;
			border-radius: 25px;
			padding: 8px 15px;
			transition: border-color 0.3s ease;
		}
		
		.search-container input:focus {
			border-color: #007bff;
			box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
		}

		.search-container {
			position: relative;
		}

		.search-results {
			position: absolute;
			top: 100%;
			left: 0;
			right: 0;
			background: white;
			border: 1px solid #ddd;
			border-radius: 4px;
			box-shadow: 0 2px 4px rgba(0,0,0,0.1);
			display: none;
			z-index: 1000;
			max-height: 300px;
			overflow-y: auto;
		}

		.search-results .result-item {
			padding: 8px 12px;
			border-bottom: 1px solid #eee;
			cursor: pointer;
		}

		.search-results .result-item:hover {
			background: #f8f9fa;
		}

		.search-results .result-item:last-child {
			border-bottom: none;
		}

		.search-results .no-results {
			padding: 10px;
			color: #666;
			text-align: center;
		}

		.search-results .loading {
			padding: 10px;
			text-align: center;
			color: #666;
		}

		.cart-count {
			position: absolute;
			top: -8px;
			right: -8px;
			background-color: #dc3545;
			color: white;
			border-radius: 50%;
			padding: 2px 6px;
			font-size: 12px;
			font-weight: bold;
		}
		.cart-icon {
			position: relative;
		}

		
		.header ul.list-inline li a {
    color: #333; /* Un gris oscuro, o el color que prefieras */
    text-decoration: none; /* Quitar el subrayado */
    font-weight: 500;
    transition: color 0.3s ease;
}

.header ul.list-inline li a:hover {
    color: #007bff; /* Color azul Bootstrap solo al pasar el mouse */
    text-decoration: underline; /* Opcional: subrayado en hover */
}

.header ul.list-inline li i {
    margin-right: 5px; /* Separar el icono del texto */
    color: #555; /* Color del icono */
}

	</style>
	
</head>

<body>
	<?php if(isset($after_body)) echo $after_body; ?>
	
	<!-- Top Bar -->
	<div class="top">
		<div class="container">
			<div class="row">
				<div class="col-md-6">
					<div class="left">
						<ul>
							<li><i class="fas fa-phone"></i> <?php echo $telefono_contacto; ?></li>
							<li><i class="fas fa-envelope"></i> <?php echo $correo_contacto; ?></li>
						</ul>
					</div>
				</div>
				<div class="col-md-6">
					<div class="right">
						<?php if (!$is_admin && isset($redes_sociales) && is_array($redes_sociales)):
							$hay_redes = false;
							foreach ($redes_sociales as $red) {
								if (!empty($red['url_red'])) {
									$hay_redes = true;
									break;
								}
							}
							if ($hay_redes): ?>
							<ul>
								<?php foreach ($redes_sociales as $red): ?>
									<?php if (!empty($red['url_red'])): ?>
										<li>
											<a href="<?php echo $red['url_red']; ?>" target="_blank">
												<i class="<?php echo $red['icono_red']; ?>"></i>
											</a>
										</li>
									<?php endif; ?>
								<?php endforeach; ?>
							</ul>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Header -->
	<div class="header">
		<div class="container">
			<div class="row" style="display: flex; align-items: center;">
				<div class="col-md-3 logo">
					<a href="index.php">
						<img src="assets/uploads/<?php echo isset($logo) ? $logo : 'default-logo.png'; ?>" alt="logo image" class="img-responsive">
					</a>
				</div>

				<div class="col-md-6">
					<ul class="list-inline" style="margin: 0; padding: 0; display: flex; align-items: center; gap: 20px;">
						<?php if(isset($_SESSION['customer']) && isset($_SESSION['customer']['cust_name'])): ?>
							<li style="display: inline-block; margin-right: 15px; color: #333; font-weight: 500;"><i class="fa fa-user" style="color: #333;"></i> Conectado como <?php echo htmlspecialchars($_SESSION['customer']['cust_name']); ?></li>
							<li style="display: inline-block; margin-right: 15px;"><a href="dashboard.php" style="color: #333; text-decoration: none;"><i class="fa fa-home"></i> Dashboard</a></li>
							<li style="display: inline-block; margin-right: 15px;"><a href="logout.php" style="color: #333; text-decoration: none;"><i class="fa fa-sign-out"></i> Cerrar Sesión</a></li>
						<?php else: ?>
							<li style="display: inline-block; margin-right: 15px;"><a href="login.php" style="color: #333; text-decoration: none;"><i class="fa fa-user"></i> Iniciar Sesión</a></li>
						<?php endif; ?>
					</ul>
				</div>

				<div class="col-md-3" style="display: flex; align-items: center; justify-content: flex-end; gap: 15px;">
					<a href="cart.php" class="cart-icon" style="position: relative; margin-right: 15px;">
						<i class="fa fa-shopping-cart fa-lg"></i>
						<?php if($cart_count > 0): ?>
							<span class="cart-count" style="position: absolute; top: -8px; right: -8px; background: #dc3545; color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; min-width: 18px; text-align: center;">
								<?php echo $cart_count; ?>
							</span>
						<?php endif; ?>
					</a>

					<div class="search-container search-predictive">
						<input type="text" id="searchInput" class="form-control" placeholder="Buscar cursos, FAQ..." style="min-width: 200px; font-size: 16px;">
						<div id="searchResults" class="search-results-dropdown"></div>
					</div>
        
        <style>
        .search-container {
            position: relative;
        }
        
        .search-results-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            max-height: 400px;
            overflow-y: auto;
            display: none;
        }
        
        .loading {
            padding: 15px;
            text-align: center;
            color: #666;
        }
        
        .no-results {
            padding: 15px;
            text-align: center;
            color: #666;
        }
        
        .result-item {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .result-item:hover {
            background-color: #f8f9fa;
        }
        
        .result-item:last-child {
            border-bottom: none;
        }
        
        .result-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .result-category {
            margin-bottom: 5px;
        }
        
        .result-description {
            font-size: 0.875rem;
            color: #666;
            line-height: 1.4;
        }
        
        .badge-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        </style>
        
        <script>
        // Búsqueda predictiva en el header
        let headerSearchTimeout;
        
        document.getElementById('searchInput').addEventListener('input', function() {
            const query = this.value.trim();
            
            clearTimeout(headerSearchTimeout);
            
            if (query.length < 1) {
                document.getElementById('searchResults').style.display = 'none';
                return;
            }
            
            // Mostrar indicador de carga
            document.getElementById('searchResults').style.display = 'block';
            document.getElementById('searchResults').innerHTML = `
                <div class="loading">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Buscando...</span>
                    </div>
                    <span class="ms-2">Buscando...</span>
                </div>
            `;
            
            // Realizar búsqueda con debounce
            headerSearchTimeout = setTimeout(() => {
                performHeaderSearch(query);
            }, 300);
        });
        
        // Cerrar resultados al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-container')) {
                document.getElementById('searchResults').style.display = 'none';
            }
        });
        
        function performHeaderSearch(query) {
            console.log('Buscando:', query); // Debug
            console.log('URL:', `search-predictive.php?query=${encodeURIComponent(query)}`); // Debug
            
            fetch(`search-predictive.php?query=${encodeURIComponent(query)}`)
                .then(response => {
                    console.log('Response status:', response.status); // Debug
                    console.log('Response headers:', response.headers); // Debug
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data); // Debug
                    
                    // Si hay error, mostrarlo
                    if (data.error) {
                        document.getElementById('searchResults').innerHTML = `
                            <div class="error-info" style="padding: 15px; font-size: 12px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;">
                                <strong>Error:</strong> ${data.error}<br>
                                ${data.error_code ? `Código: ${data.error_code}<br>` : ''}
                                ${data.error_file ? `Archivo: ${data.error_file}<br>` : ''}
                                ${data.error_line ? `Línea: ${data.error_line}` : ''}
                            </div>
                        `;
                    }
                    // Si hay resultados, mostrarlos normalmente
                    else if (data.results && data.results.length > 0) {
                        displayHeaderResults(data.results, query);
                    } else {
                        // Si no hay resultados, mostrar debug info
                        if (data.debug) {
                            document.getElementById('searchResults').innerHTML = `
                                <div class="debug-info" style="padding: 15px; font-size: 12px; background: #f8f9fa;">
                                    <strong>Debug Info:</strong><br>
                                    Query: ${data.debug.query_received}<br>
                                    Valid: ${data.debug.validation.is_valid ? 'YES' : 'NO'}<br>
                                    Cursos encontrados: ${data.debug.cursos_query.found}<br>
                                    FAQs encontradas: ${data.debug.faqs_query.found}<br>
                                    Total resultados: ${data.debug.final_results.total_results}<br>
                                    <hr>
                                    <strong>Resultados:</strong><br>
                                    ${JSON.stringify(data.results, null, 2)}
                                </div>
                            `;
                        } else {
                            displayHeaderResults([], query);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error en búsqueda:', error);
                    document.getElementById('searchResults').innerHTML = `
                        <div class="no-results">
                            <small>Error al buscar: ${error.message}</small>
                        </div>
                    `;
                });
        }
        
        function displayHeaderResults(results, query) {
            const container = document.getElementById('searchResults');
            
            if (results.length === 0) {
                container.innerHTML = `
                    <div class="no-results">
                        <small>No se encontraron resultados para "${query}"</small>
                    </div>
                `;
                return;
            }
            
            let html = '';
            results.slice(0, 5).forEach(result => { // Máximo 5 resultados en header
                const icon = getTypeIcon(result.tipo);
                const typeName = getTypeName(result.tipo);
                
                html += `
                    <div class="result-item" onclick="navigateToHeaderResult('${result.url || ''}', ${result.id}, '${result.tipo}')">
                        <div class="result-title">
                            <i class="${icon} me-1" style="color: #007bff;"></i>
                            ${result.titulo}
                        </div>
                        <div class="result-category">
                            <span class="badge bg-primary badge-sm">${typeName}</span>
                            ${result.categoria && result.categoria !== typeName ? `<span class="badge bg-secondary badge-sm ms-1">${result.categoria}</span>` : ''}
                        </div>
                        <div class="result-description">
                            ${result.descripcion.substring(0, 80)}...
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }
        
        function getTypeIcon(tipo) {
            const icons = {
                'curso': 'fas fa-graduation-cap',
                'servicio': 'fas fa-cogs',
                'faq': 'fas fa-question-circle',
                'categoria': 'fas fa-folder'
            };
            return icons[tipo] || 'fas fa-file';
        }
        
        function getTypeName(tipo) {
            const names = {
                'curso': 'Curso',
                'servicio': 'Servicio',
                'faq': 'FAQ',
                'categoria': 'Categoría'
            };
            return names[tipo] || 'Resultado';
        }
        
        function navigateToHeaderResult(url, id, tipo) {
            const BASE_URL = '/certificado/';
            if (url && url !== '#') {
                // Si la url ya contiene BASE_URL, usarla tal cual
                if (url.startsWith(BASE_URL)) {
                    window.location.href = url;
                } else {
                    window.location.href = BASE_URL + url.replace(/^\/+/, '');
                }
            } else {
                // Navegar según el tipo
                switch(tipo) {
                    case 'curso':
                        window.location.href = BASE_URL + `curso.php?id=${id}`;
                        break;
                    case 'faq':
                        window.location.href = BASE_URL + `faq.php#faq-${id}`;
                        break;
                    case 'servicio':
                        window.location.href = BASE_URL + `servicios.php?id=${id}`;
                        break;
                    case 'categoria':
                        window.location.href = BASE_URL + `categoria.php?id=${id}`;
                        break;
                    default:
                        window.location.href = BASE_URL;
                }
            }
            // Limpiar búsqueda
            document.getElementById('searchInput').value = '';
            document.getElementById('searchResults').style.display = 'none';
        }
        </script>
      </div>
    </div>
  </div>
</div>


	<!-- Navegación Principal -->
	<div class="nav">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<div class="menu-container">
						<!-- Botón hamburguesa para móviles -->
						<button class="navbar-toggle" type="button">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						
						<div class="menu">
							<ul class="nav-menu">
								<li><a href="index.php"><i class="fa fa-home"></i> Inicio</a></li>
								<li><a href="curso.php"><i class="fa fa-graduation-cap"></i> Cursos</a></li>
								<li><a href="verificar-qr.php"><i class="fa fa-qrcode"></i> Verificar Certificado</a></li>
								<li><a href="about.php"><i class="fa fa-info-circle"></i> Sobre Nosotros</a></li>
								<li><a href="contact.php"><i class="fa fa-envelope"></i> Contáctanos</a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<style>
		/* Navegación Principal */
		.nav {
			background: linear-gradient(135deg, #1e40af 0%, #7c3aed 50%, #be185d 100%);
			padding: 0;
			margin-bottom: 15px;
			box-shadow: 0 2px 10px rgba(0,0,0,0.1);
		}

		.menu-container {
			position: relative;
			display: flex;
			justify-content: space-between;
			align-items: center;
		}

		.menu {
			position: relative;
		}

		.menu ul {
			list-style: none;
			margin: 0;
			padding: 0;
		}

		.menu > ul > li {
			display: inline-block;
			position: relative;
		}

		.menu > ul > li > a {
			display: block;
			padding: 15px 20px;
			color: #fff;	
			text-decoration: none;
			font-weight: 500;
			transition: all 0.3s ease;
		}

		.menu > ul > li > a:hover {
			background: rgba(255, 255, 255, 0.1);
		}

		/* Estilos para iconos en desktop */
		.menu > ul > li > a i {
			margin-right: 8px;
			width: 16px;
			text-align: center;
			transition: all 0.3s ease;
		}

		.menu > ul > li > a:hover i {
			transform: scale(1.1);
		}

		/* Botón hamburguesa */
		.navbar-toggle {
			display: none;
			background: transparent;
			border: none;
			padding: 10px;
			cursor: pointer;
			transition: all 0.3s ease;
		}

		.navbar-toggle .icon-bar {
			display: block;
			width: 25px;
			height: 3px;
			background-color: white;
			margin: 5px 0;
			transition: all 0.3s ease;
			border-radius: 2px;
		}

		.navbar-toggle:hover .icon-bar {
			background-color: #fbbf24;
		}

		/* Animación del botón hamburguesa */
		.navbar-toggle.active .icon-bar:nth-child(2) {
			transform: rotate(45deg) translate(5px, 5px);
		}

		.navbar-toggle.active .icon-bar:nth-child(3) {
			opacity: 0;
		}

		.navbar-toggle.active .icon-bar:nth-child(4) {
			transform: rotate(-45deg) translate(7px, -6px);
		}

		.menu-dropdown-icon > a:after {
			content: '\f107';
			font-family: 'Font Awesome 5 Free';
			font-weight: 900;
			margin-left: 5px;
		}

		.menu ul ul {
			position: absolute;
			left: 0;
			top: 100%;
			background: #fff;
			min-width: 200px;
			box-shadow: 0 2px 5px rgba(0,0,0,0.1);
			display: none;
			z-index: 1000;
		}

		.menu ul ul li {
			position: relative;
		}

		.menu ul ul li a {
			display: block;
			padding: 10px 20px;
			color: #333;
			text-decoration: none;
			transition: all 0.3s ease;
		}

		.menu ul ul li a:hover {
			background: #f8f9fa;
			color: #007bff;
		}

		.menu ul ul ul {
			left: 100%;
			top: 0;
		}

		.menu > ul > li:hover > ul {
			display: block;
		}

		.menu ul ul li:hover > ul {
			display: block;
		}

		.menu-mobile {
			display: none;
			padding: 15px 20px;
			color: #fff;
			text-decoration: none;
			font-weight: 500;
		}

		/* Estilos para resultados de búsqueda */
		.search-container {
			position: relative;
		}
		
		#searchResults {
			position: absolute;
			top: 100%;
			left: 0;
			right: 0;
			background: white;
			border: 1px solid #ddd;
			border-radius: 8px;
			box-shadow: 0 6px 20px rgba(0,0,0,0.15);
			max-height: 400px;
			overflow-y: auto;
			z-index: 1000;
			display: none;
			margin-top: 5px;
		}
		
		.result-item {
			padding: 12px 15px;
			border-bottom: 1px solid #eee;
			cursor: pointer;
			transition: all 0.2s ease;
		}
		
		.result-item:hover {
			background-color: #f8f9fa;
			border-left: 3px solid #007bff;
		}
		
		.result-item:last-child {
			border-bottom: none;
		}
		
		.result-title {
			font-weight: 600;
			color: #333;
			margin-bottom: 4px;
			font-size: 14px;
		}
		
		.result-category {
			font-size: 11px;
			color: #666;
			margin-top: 4px;
		}
		
		.result-category .badge {
			font-size: 10px;
			padding: 2px 6px;
		}
		
		.result-description {
			font-size: 12px;
			color: #888;
			margin-top: 4px;
			line-height: 1.4;
		}
		
		.loading, .no-results {
			padding: 20px 15px;
			text-align: center;
			color: #666;
			font-size: 13px;
		}
		
		.loading .spinner-border {
			width: 1rem;
			height: 1rem;
		}

		@media (max-width: 991px) {
			/* Mostrar botón hamburguesa */
			.navbar-toggle {
				display: block;
			}

			/* Ocultar menú por defecto */
			.menu {
				position: absolute;
				top: 100%;
				left: 0;
				right: 0;
				background: linear-gradient(135deg, #1e40af 0%, #7c3aed 50%, #be185d 100%);
				box-shadow: 0 4px 15px rgba(0,0,0,0.2);
				z-index: 1000;
				display: none;
				border-radius: 0 0 10px 10px;
			}

			.menu.active {
				display: block;
			}

			.menu > ul {
				display: block !important;
				flex-direction: column;
				position: static;
				background: transparent;
				z-index: 1000;
			}

			.menu > ul > li {
				display: block;
				width: 100%;
				border-bottom: 1px solid rgba(255, 255, 255, 0.1);
			}

			.menu > ul > li:last-child {
				border-bottom: none;
			}

			.menu > ul > li > a {
				padding: 15px 20px;
				font-size: 1rem;
				text-align: left;
				display: flex;
				align-items: center;
			}

			.menu > ul > li > a:hover {
				background: rgba(255, 255, 255, 0.1);
			}

			/* Estilos para iconos en móviles */
			.menu > ul > li > a i {
				margin-right: 10px;
				width: 20px;
				text-align: center;
			}

			.menu ul ul {
				position: static;
				background: rgba(0,0,0,0.1);
				box-shadow: none;
				width: 100%;
			}

			.menu ul ul li a {
				padding-left: 40px;
				color: #fff;
			}

			.menu ul ul ul li a {
				padding-left: 60px;
			}

			.menu > ul > li:hover > ul {
				display: none;
			}

			.menu > ul > li.active > ul {
				display: block;
			}
		}
		
		@media (max-width: 480px) {
			.menu > ul > li > a {
				padding: 12px 15px;
				font-size: 0.9rem;
			}
			
			.menu ul ul li a {
				padding-left: 30px;
				font-size: 0.85rem;
			}
			
			.menu ul ul ul li a {
				padding-left: 45px;
			}
		}
	</style>

	<script>
		$(document).ready(function() {
			// Menú hamburguesa
			$('.navbar-toggle').click(function(e) {
				e.preventDefault();
				$('.menu').toggleClass('active');
				
				// Animación del botón hamburguesa
				$(this).toggleClass('active');
			});

			// Cerrar menú al hacer clic fuera
			$(document).on('click', function(e) {
				if (!$(e.target).closest('.nav').length) {
					$('.menu').removeClass('active');
					$('.navbar-toggle').removeClass('active');
				}
			});

			// Cerrar menú al hacer clic en un enlace
			$('.menu a').click(function() {
				$('.menu').removeClass('active');
				$('.navbar-toggle').removeClass('active');
			});

			$('.menu-dropdown-icon > a').click(function(e) {
				if ($(window).width() <= 991) {
					e.preventDefault();
					$(this).parent().toggleClass('active');
				}
			});

			let jquerySearchTimeout;
			const searchInput = $('#searchInput');
			const searchResults = $('#searchResults');

			searchInput.on('input', function() {
				const query = $(this).val().trim();
				
				// Limpiar el timeout anterior
				clearTimeout(jquerySearchTimeout);
				
				// Ocultar resultados si el campo está vacío
				if (query === '') {
					searchResults.hide();
					return;
				}

				// Mostrar indicador de carga
				searchResults.html('<div class="loading">Buscando...</div>').show();

				// Esperar 300ms después de que el usuario deje de escribir
				jquerySearchTimeout = setTimeout(() => {
					$.ajax({
						url: 'search.php',
						method: 'GET',
						data: { query: query },
						success: function(response) {
							if (response.results && response.results.length > 0) {
								let html = '';
								response.results.forEach(result => {
									html += `<div class="result-item" data-id="${result.id}" data-tipo="${result.tipo}" data-url="${result.url || ''}">
												<div class="result-title">${result.titulo}</div>
												<div class="result-category">${result.categoria}</div>
												${result.descripcion ? `<div class="result-description">${result.descripcion}</div>` : ''}
											</div>`;
								});
								searchResults.html(html);
							} else {
								searchResults.html('<div class="no-results">No se encontraron resultados</div>');
							}
						},
						error: function() {
							searchResults.html('<div class="no-results">Error al buscar</div>');
						}
					});
				}, 300);
			});

			// Cerrar resultados al hacer clic fuera
			$(document).on('click', function(e) {
				if (!$(e.target).closest('.search-container').length) {
					searchResults.hide();
				}
			});

			// Redirigir al hacer clic en un resultado
			searchResults.on('click', '.result-item', function() {
				const id = $(this).data('id');
				const tipo = $(this).data('tipo');
				const url = $(this).data('url');
				
				if (url) {
					window.location.href = url;
				} else {
					// Fallback para compatibilidad
					window.location.href = 'search-results.php?q=' + encodeURIComponent($('#searchInput').val());
				}
			});
		});
	</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<!-- Bootstrap 3 JS -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
	
	<!-- Scripts de búsqueda mejorada -->
	<script src="assets/js/search-highlight.js"></script>
	<script src="assets/js/search-predictive.js"></script>
	
	<!-- Estilos de búsqueda -->
	<link rel="stylesheet" href="assets/css/search-results.css">

	<!-- Botón flotante de WhatsApp -->
	<?php if (!$is_admin && isset($whatsapp_link) && !empty($whatsapp_link)): ?>
	<!-- Aquí va el botón flotante de WhatsApp -->
	<?php endif; ?>