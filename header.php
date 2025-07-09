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
	$after_body = $row['after_body'] ?? '';
}

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
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.css" />
	<!-- ✅ JS: jQuery primero -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
		
		body {
			font-family: 'Roboto', sans-serif;
			line-height: 1.4;
		}

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
			background: #fff;
			box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
			color: var(--dark-color);
			text-decoration: none;
			font-weight: 500;
			font-size: 0.9rem;
		}

		.header .right ul li a:hover {
			color: var(--primary-color);
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
			background:rgb(34, 244, 6);
			padding: 0;
			margin-bottom: 15px;
		}

		.menu > ul > li > a {
			padding: 10px 15px;
			font-size: 0.95rem;
		}

		.menu ul ul li a {
			padding: 8px 15px;
			font-size: 0.9rem;
		}

		@media (max-width: 768px) {
			.top .left ul li,
			.top .right ul li {
				margin: 3px 0;
			}
			
			.header .right ul {
				text-align: center;
				margin-top: 10px;
			}
			
			.search-area {
				text-align: center;
				margin-top: 10px;
			}
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
						<ul>
							<?php foreach($redes_sociales as $red): ?>
								<?php if(!empty($red['url_red'])): ?>
									<li>
										<a href="<?php echo $red['url_red']; ?>" target="_blank">
											<i class="<?php echo $red['icono_red']; ?>"></i>
										</a>
									</li>
								<?php endif; ?>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Header -->
	<div class="header">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-4 logo">
        <a href="index.php">
          <img src="assets/uploads/<?php echo isset($logo) ? $logo : 'default-logo.png'; ?>" alt="logo image">
        </a>
      </div>

      <div class="col-md-5">
        <ul class="list-inline mb-0 d-flex justify-content-start gap-3">
          <?php if(isset($_SESSION['customer']) && isset($_SESSION['customer']['cust_name'])): ?>
            <li class="list-inline-item"><i class="fa fa-user"></i> Conectado como <?php echo htmlspecialchars($_SESSION['customer']['cust_name']); ?></li>
            <li class="list-inline-item"><a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a></li>
            <li class="list-inline-item"><a href="logout.php"><i class="fa fa-sign-out"></i> Cerrar Sesión</a></li>
          <?php else: ?>
            <li class="list-inline-item"><a href="login.php"><i class="fa fa-user"></i> Iniciar Sesión</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="col-md-3 d-flex align-items-center justify-content-end gap-3">
        <a href="cart.php" class="cart-icon position-relative me-3">
          <i class="fa fa-shopping-cart fa-lg"></i>
          <?php if($cart_count > 0): ?>
            <span class="cart-count position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              <?php echo $cart_count; ?>
            </span>
          <?php endif; ?>
        </a>

        <form class="d-flex" role="search" action="search.php" method="get">
          <?php $csrf->echoInputField(); ?>
          <input type="text" class="form-control form-control-sm" placeholder="Buscar curso..." name="search_text" style="min-width:150px;">
          <button type="submit" class="btn btn-primary btn-sm ms-2">Buscar</button>
        </form>
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
						<div class="menu">
							<ul>
								<li><a href="index.php">Inicio</a></li>
								<li><a href="curso.php">Cursos</a></li>
								<li><a href="verificar-qr.php"><i class="fa fa-qrcode"></i> Verificar Certificado</a></li>
								<li><a href="about.php">Sobre Nosotros</a></li>
								<li><a href="contact.php">Contáctanos</a></li>
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
			background:rgb(21, 213, 201);
			padding: 0;
			margin-bottom: 20px;
		}

		.menu-container {
			position: relative;
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
			background: #007bff;
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

		@media (max-width: 991px) {
			.menu-mobile {
				display: block;
			}

			.menu > ul {
				display: none;
				position: absolute;
				left: 0;
				top: 100%;
				width: 100%;
				background: #343a40;
				z-index: 1000;
			}

			.menu > ul > li {
				display: block;
			}

			.menu > ul > li > a {
				padding: 10px 20px;
			}

			.menu ul ul {
				position: static;
				background: #2c3136;
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
	</style>

	<script>
		$(document).ready(function() {
			$('.menu-mobile').click(function(e) {
				e.preventDefault();
				$('.menu > ul').slideToggle();
			});

			$('.menu-dropdown-icon > a').click(function(e) {
				if ($(window).width() <= 991) {
					e.preventDefault();
					$(this).parent().toggleClass('active');
				}
			});

			let searchTimeout;
			const searchInput = $('#searchInput');
			const searchResults = $('#searchResults');

			searchInput.on('input', function() {
				const query = $(this).val().trim();
				
				// Limpiar el timeout anterior
				clearTimeout(searchTimeout);
				
				// Ocultar resultados si el campo está vacío
				if (query === '') {
					searchResults.hide();
					return;
				}

				// Mostrar indicador de carga
				searchResults.html('<div class="loading">Buscando...</div>').show();

				// Esperar 300ms después de que el usuario deje de escribir
				searchTimeout = setTimeout(() => {
					$.ajax({
						url: 'search.php',
						method: 'GET',
						data: { query: query },
						success: function(response) {
							if (response.results && response.results.length > 0) {
								let html = '';
								response.results.forEach(result => {
									html += `<div class="result-item" data-id="${result.id}">
												<div class="result-title">${result.titulo}</div>
												<div class="result-category">${result.categoria}</div>
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
				window.location.href = 'certificado.php?id=' + id;
			});
		});
	</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<!-- Bootstrap 5 JS Bundle with Popper -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>