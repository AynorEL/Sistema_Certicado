<?php
require_once('admin/inc/config.php');
require_once('admin/inc/functions.php');

// Obtener configuraciones
$statement = $pdo->prepare("SELECT * FROM configuraciones WHERE id=1");
$statement->execute();
$row = $statement->fetch(PDO::FETCH_ASSOC);

// Valores por defecto
$footer_copyright = 'Copyright © ' . date('Y');
$footer_about = 'Sistema de Certificados - Tu plataforma confiable para la gestión y emisión de certificados profesionales y académicos.';
$footer_address = 'Av. Principal 123, Ciudad';
$footer_email = 'contacto@sistema.com';
$footer_phone = '+123 456 7890';
$footer_newsletter = 'Suscríbete para recibir actualizaciones';
$before_body = '';
$boletin_activo = 1; // Valor por defecto

// Obtener valores de la base de datos
if($row) {
	$footer_copyright = isset($row['pie_pagina_derechos']) ? $row['pie_pagina_derechos'] : $footer_copyright;
	$footer_about = isset($row['pie_pagina_descripcion']) ? $row['pie_pagina_descripcion'] : $footer_about;
	$footer_address = isset($row['direccion_contacto']) ? $row['direccion_contacto'] : $footer_address;
	$footer_email = isset($row['correo_contacto']) ? $row['correo_contacto'] : $footer_email;
	$footer_phone = isset($row['telefono_contacto']) ? $row['telefono_contacto'] : $footer_phone;
	$footer_newsletter = isset($row['footer_newsletter']) ? $row['footer_newsletter'] : $footer_newsletter;
	$before_body = isset($row['before_body']) ? $row['before_body'] : $before_body;
	$boletin_activo = isset($row['boletin_activo']) ? $row['boletin_activo'] : $boletin_activo;
}

// Obtener redes sociales
$statement = $pdo->prepare("SELECT * FROM redes_sociales WHERE url_red != '' AND url_red IS NOT NULL");
$statement->execute();
$redes_sociales = $statement->fetchAll(PDO::FETCH_ASSOC);

// Si no hay redes sociales, usar valores por defecto
if (empty($redes_sociales)) {
	$redes_sociales = [
		['url' => '#', 'icono' => 'facebook'],
		['url' => '#', 'icono' => 'twitter'],
		['url' => '#', 'icono' => 'instagram'],
		['url' => '#', 'icono' => 'linkedin']
	];
}

// Función segura para htmlspecialchars
function safe_html($str) {
	return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Función para validar URL
function valid_url($url) {
	return filter_var($url, FILTER_VALIDATE_URL) ? $url : '#';
}

// Función para validar icono
function valid_icon($icon) {
	$valid_icons = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'whatsapp'];
	return in_array($icon, $valid_icons) ? $icon : 'link';
}
?>

<?php if($boletin_activo == 1): ?>
<section class="home-newsletter">
	<div class="container">
		<div class="row">
			<div class="col-md-6 col-md-offset-3">
				<div class="single">
					<div id="newsletter-message" class="alert" style="display: none;"></div>
					<form id="newsletter-form" onsubmit="return false;">
						<h2>Suscríbete para recibir actualizaciones</h2>
						<div class="input-group">
							<input type="email" class="form-control" placeholder="Ingresa tu dirección de correo electrónico" name="email" required>
							<span class="input-group-btn">
								<button class="btn btn-theme" type="submit">Suscribirse</button>
							</span>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const form = document.getElementById('newsletter-form');
	const messageDiv = document.getElementById('newsletter-message');

	form.addEventListener('submit', function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		const formData = new FormData(form);
		
		fetch('newsletter-subscribe.php', {
			method: 'POST',
			body: formData,
			headers: {
				'X-Requested-With': 'XMLHttpRequest'
			}
		})
		.then(response => response.json())
		.then(data => {
			// Configurar el mensaje
			messageDiv.className = `alert alert-${data.status === 'success' ? 'success' : 'danger'}`;
			messageDiv.textContent = data.message;
			messageDiv.style.display = 'block';
			
			// Limpiar el formulario si fue exitoso
			if (data.status === 'success') {
				form.reset();
			}
			
			// Ocultar el mensaje después de 5 segundos
			setTimeout(() => {
				messageDiv.style.opacity = '0';
				setTimeout(() => {
					messageDiv.style.display = 'none';
					messageDiv.style.opacity = '1';
				}, 500);
			}, 5000);
		})
		.catch(error => {
			console.error('Error:', error);
			messageDiv.className = 'alert alert-danger';
			messageDiv.textContent = 'Error al procesar la solicitud. Por favor, intente más tarde.';
			messageDiv.style.display = 'block';
			
			setTimeout(() => {
				messageDiv.style.opacity = '0';
				setTimeout(() => {
					messageDiv.style.display = 'none';
					messageDiv.style.opacity = '1';
				}, 500);
			}, 5000);
		});

		return false;
	});
});
</script>

<style>
/* Estilos para las alertas */
.alert {
	margin-bottom: 20px;
	padding: 15px;
	border-radius: 4px;
	opacity: 1;
	transition: opacity 0.5s ease;
	text-align: center;
	position: relative;
	z-index: 1000;
}

.alert-success {
	background-color: #d4edda;
	border-color: #c3e6cb;
	color: #155724;
}

.alert-danger {
	background-color: #f8d7da;
	border-color: #f5c6cb;
	color: #721c24;
}

#newsletter-message {
	margin-bottom: 20px;
	min-height: 50px;
	position: relative;
}

.home-newsletter {
	padding: 50px 0;
	background: #f5f5f5;
	text-align: center;
	position: relative;
	z-index: 1;
}

.home-newsletter .single {
	max-width: 650px;
	margin: 0 auto;
	text-align: center;
	position: relative;
	z-index: 2;
}

.home-newsletter .single h2 {
	font-size: 22px;
	color: #333;
	margin-bottom: 20px;
	text-align: center;
}

.home-newsletter .single .form-control {
	height: 50px;
	background: #fff;
	border-radius: 25px 0 0 25px;
	border: 1px solid #ddd;
	padding: 0 20px;
	text-align: center;
}

.home-newsletter .single .btn-theme {
	min-height: 50px;
	border-radius: 0 25px 25px 0;
	background: #007bff;
	color: #fff;
	border: none;
	padding: 0 25px;
	transition: all 0.3s ease;
}

.home-newsletter .single .btn-theme:hover {
	background: #0056b3;
}

.home-newsletter .input-group {
	display: flex;
	justify-content: center;
	margin: 0 auto;
	max-width: 500px;
}

@media (max-width: 768px) {
	.home-newsletter .single {
		padding: 0 20px;
	}
	
	.home-newsletter .single h2 {
		font-size: 20px;
	}
	
	.home-newsletter .input-group {
		max-width: 100%;
	}
}
</style>
<?php endif; ?>

<!-- Footer -->
<footer>
	<div class="container">
		<div class="row">
			<div class="col-md-4">
				<div class="footer-widget">
					<h3>Sobre Nosotros</h3>
					<p><?php echo safe_html($footer_about); ?></p>
					<div class="footer-social">
						<?php
						foreach($redes_sociales as $red) {
							if(!empty($red['url_red']) && filter_var($red['url_red'], FILTER_VALIDATE_URL)) {
								echo '<a href="' . safe_html($red['url_red']) . '" target="_blank"><i class="' . safe_html($red['icono_red']) . '"></i></a>';
							}
						}
						?>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="footer-widget">
					<h3>Enlaces Rápidos</h3>
					<ul>
						<li><a href="index.php">Inicio</a></li>
						<li><a href="about.php">Nosotros</a></li>
						<li><a href="contact.php">Contacto</a></li>
						<li><a href="faq.php">Preguntas Frecuentes</a></li>
					</ul>
				</div>
			</div>
			<div class="col-md-4">
				<div class="footer-widget">
					<h3>Información de Contacto</h3>
					<ul>
						<li><i class="fa fa-map-marker"></i> <?php echo safe_html($footer_address); ?></li>
						<li><i class="fa fa-phone"></i> <?php echo safe_html($footer_phone); ?></li>
						<li><i class="fa fa-envelope"></i> <?php echo safe_html($footer_email); ?></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</footer>

<!-- Footer Bottom -->
<div class="footer-bottom">
	<div class="container">
		<div class="row">
			<div class="col-md-12 text-center">
				<p><?php echo safe_html($footer_copyright); ?> | Sistema de Certificados</p>
			</div>
		</div>
	</div>
</div>

<!-- Scroll to Top Button -->
<a href="#" class="scrollup">
	<i class="fa fa-angle-up"></i>
</a>

<style>
	/* Newsletter Styles */
	.home-newsletter {
		padding: 40px 0;
		background: #f8f9fa;
	}

	.home-newsletter .single {
		max-width: 650px;
		margin: 0 auto;
		text-align: center;
	}

	.home-newsletter .single h2 {
		font-size: 22px;
		color: #333;
		margin-bottom: 20px;
	}

	.home-newsletter .input-group {
		margin: 0 auto;
		max-width: 500px;
	}

	.home-newsletter .form-control {
		height: 50px;
		border-radius: 25px 0 0 25px;
		border: 1px solid #ddd;
		padding: 0 20px;
	}

	.home-newsletter .btn-theme {
		height: 50px;
		border-radius: 0 25px 25px 0;
		background: var(--primary-color);
		color: #fff;
		border: none;
		padding: 0 25px;
	}

	.home-newsletter .btn-theme:hover {
		background: #0056b3;
	}

	/* Footer Styles */
	footer {
		background: #343a40;
		color: #fff;
		padding: 60px 0 30px;
	}

	.footer-widget {
		margin-bottom: 30px;
	}

	.footer-widget h3 {
		color: #fff;
		font-size: 18px;
		margin-bottom: 20px;
		position: relative;
		padding-bottom: 10px;
	}

	.footer-widget h3:after {
		content: '';
		position: absolute;
		left: 0;
		bottom: 0;
		width: 50px;
		height: 2px;
		background: var(--primary-color);
	}

	.footer-widget p {
		color: #ccc;
		line-height: 1.6;
	}

	.footer-widget ul {
		list-style: none;
		padding: 0;
		margin: 0;
	}

	.footer-widget ul li {
		margin-bottom: 10px;
	}

	.footer-widget ul li a {
		color: #ccc;
		text-decoration: none;
		transition: all 0.3s ease;
	}

	.footer-widget ul li a:hover {
		color: var(--primary-color);
		padding-left: 5px;
	}

	.footer-social {
		margin-top: 20px;
	}

	.footer-social a {
		display: inline-block;
		width: 35px;
		height: 35px;
		line-height: 35px;
		text-align: center;
		background: rgba(255,255,255,0.1);
		color: #fff;
		border-radius: 50%;
		margin-right: 5px;
		transition: all 0.3s ease;
	}

	.footer-social a:hover {
		background: var(--primary-color);
	}

	.contact-info li {
		color: #ccc;
		margin-bottom: 15px;
	}

	.contact-info li i {
		margin-right: 10px;
		color: var(--primary-color);
	}

	/* Footer Bottom */
	.footer-bottom {
		background: #2c3136;
		padding: 15px 0;
		color: #ccc;
		text-align: center;
	}

	/* Scroll to Top Button */
	.scrollup {
		position: fixed;
		right: 20px;
		bottom: 20px;
		width: 40px;
		height: 40px;
		background: var(--primary-color);
		color: #fff;
		text-align: center;
		line-height: 40px;
		border-radius: 50%;
		display: none;
		z-index: 999;
		transition: all 0.3s ease;
	}

	.scrollup:hover {
		background: #0056b3;
		color: #fff;
	}

	@media (max-width: 768px) {
		.home-newsletter .input-group {
			flex-direction: column;
		}

		.home-newsletter .form-control {
			border-radius: 25px;
			margin-bottom: 10px;
		}

		.home-newsletter .btn-theme {
			border-radius: 25px;
			width: 100%;
		}

		.footer-widget {
			text-align: center;
		}

		.footer-widget h3:after {
			left: 50%;
			transform: translateX(-50%);
		}

		.footer-social {
			justify-content: center;
		}
	}
</style>


<script>
document.addEventListener('DOMContentLoaded', function() {
	// Verificar si jQuery está disponible
	if (typeof jQuery !== 'undefined') {
		jQuery(document).ready(function($) {
			// Scroll to top
			$(window).scroll(function() {
				if ($(this).scrollTop() > 100) {
					$('.scrollup').fadeIn();
				} else {
					$('.scrollup').fadeOut();
				}
			});

			$('.scrollup').click(function() {
				$("html, body").animate({
					scrollTop: 0
				}, 600);
				return false;
			});

			// Newsletter form submission
			$('form[action="newsletter-subscribe.php"]').on('submit', function(e) {
				e.preventDefault();
				var form = $(this);
				var email = form.find('input[name="email"]').val();
				
				$.ajax({
					url: form.attr('action'),
					type: 'POST',
					data: { email: email },
					dataType: 'json',
					success: function(response) {
						if (response.success) {
							alert('¡Gracias por suscribirte!');
							form[0].reset();
						} else {
							alert(response.message || 'Error al suscribirse');
						}
					},
					error: function() {
						alert('Error al procesar la solicitud');
					}
				});
			});
		});
	} else {
		console.error('jQuery no está disponible');
	}
});
</script>

<?php echo $before_body; ?>
</body>
</html>