<?php
// Obtener configuraciones del footer desde la base de datos
$statement = $pdo->prepare("SELECT * FROM configuraciones WHERE id=1");
$statement->execute();
$config = $statement->fetch(PDO::FETCH_ASSOC);

// Obtener categorías para el menú de servicios
$statement = $pdo->prepare("SELECT * FROM categoria ORDER BY nombre_categoria ASC LIMIT 6");
$statement->execute();
$categorias = $statement->fetchAll(PDO::FETCH_ASSOC);

// Obtener cursos recientes para mostrar en el footer
$statement = $pdo->prepare("SELECT * FROM curso WHERE estado = 'Activo' ORDER BY idcurso DESC LIMIT 4");
$statement->execute();
$cursos_recientes = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Footer Moderno -->
<footer class="footer-modern">
    <!-- Sección principal del footer -->
    <div class="footer-main">
        <div class="container">
            <div class="row">
                <!-- Información de la empresa -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-widget">
                        <div class="footer-logo mb-3">
                            <?php if (file_exists('assets/uploads/' . $config['logo'])): ?>
                                <img src="assets/uploads/<?php echo $config['logo']; ?>" alt="Logo" class="img-fluid" style="max-height: 60px;">
                            <?php else: ?>
                                <h4 class="text-white mb-0">Sistema de Certificados</h4>
                            <?php endif; ?>
                        </div>
                        <p class="footer-description">
                            <?php echo $config['pie_pagina_descripcion'] ?? 'Plataforma especializada en la emisión y gestión de certificados profesionales. Ofrecemos soluciones digitales para validar y verificar la autenticidad de tus logros académicos.'; ?>
                        </p>
                        <div class="social-links">
                            <a href="#" class="social-link facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-link twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-link linkedin">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="#" class="social-link instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="social-link youtube">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Enlaces rápidos -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5 class="widget-title">Enlaces Rápidos</h5>
                        <ul class="footer-links">
                            <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
                            <li><a href="about.php"><i class="fas fa-info-circle"></i> Nosotros</a></li>
                            <li><a href="contact.php"><i class="fas fa-envelope"></i> Contacto</a></li>
                            <li><a href="faq.php"><i class="fas fa-question-circle"></i> FAQ</a></li>
                            <li><a href="verificar-certificado.php"><i class="fas fa-search"></i> Verificar</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Categorías de cursos -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5 class="widget-title">Categorías</h5>
                        <ul class="footer-links">
                            <?php foreach ($categorias as $categoria): ?>
                                <li><a href="curso.php?categoria=<?php echo $categoria['idcategoria']; ?>">
                                    <i class="fas fa-graduation-cap"></i> <?php echo $categoria['nombre_categoria']; ?>
                                </a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Cursos recientes -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5 class="widget-title">Cursos Recientes</h5>
                        <div class="recent-courses">
                            <?php foreach ($cursos_recientes as $curso): ?>
                                <div class="course-item">
                                    <div class="course-info">
                                        <h6 class="course-title">
                                            <a href="curso.php?id=<?php echo $curso['idcurso']; ?>">
                                                <?php echo $curso['nombre_curso']; ?>
                                            </a>
                                        </h6>
                                        <p class="course-meta">
                                            <span class="duration"><i class="fas fa-clock"></i> <?php echo $curso['duracion']; ?> horas</span>
                                            <span class="price"><i class="fas fa-tag"></i> S/ <?php echo number_format($curso['precio'], 2); ?></span>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de contacto -->
    <div class="footer-contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-info">
                            <h6>Dirección</h6>
                            <p><?php echo $config['direccion_contacto'] ?? 'Av. Principal 123, Lima, Perú'; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-info">
                            <h6>Teléfono</h6>
                            <p><?php echo $config['telefono_contacto'] ?? '+51 123 456 789'; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-info">
                            <h6>Email</h6>
                            <p><?php echo $config['correo_contacto'] ?? 'info@certificados.com'; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="contact-info">
                            <h6>Horario</h6>
                            <p>Lun - Vie: 9:00 AM - 6:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer inferior -->
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="copyright">
                        <?php echo $config['pie_pagina_derechos'] ?? '&copy; ' . date('Y') . ' Sistema de Certificados. Todos los derechos reservados.'; ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <ul class="footer-bottom-links">
                        <li><a href="#">Política de Privacidad</a></li>
                        <li><a href="#">Términos de Servicio</a></li>
                        <li><a href="#">Política de Cookies</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Estilos CSS para el footer moderno -->
<style>
.footer-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    position: relative;
    overflow: hidden;
}

.footer-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    pointer-events: none;
}

.footer-main {
    padding: 60px 0 40px;
    position: relative;
    z-index: 1;
}

.footer-widget {
    margin-bottom: 30px;
}

.footer-logo img {
    filter: brightness(0) invert(1);
}

.footer-description {
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.6;
    margin-bottom: 25px;
}

.widget-title {
    color: #fff;
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 20px;
    position: relative;
    padding-bottom: 10px;
}

.widget-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 30px;
    height: 2px;
    background: #fff;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    font-size: 0.9rem;
}

.footer-links a i {
    margin-right: 8px;
    width: 16px;
    text-align: center;
}

.footer-links a:hover {
    color: #fff;
    transform: translateX(5px);
}

.social-links {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    border-radius: 50%;
    text-decoration: none;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.social-link:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
    transform: translateY(-3px);
}

.social-link.facebook:hover { background: #1877f2; }
.social-link.twitter:hover { background: #1da1f2; }
.social-link.linkedin:hover { background: #0077b5; }
.social-link.instagram:hover { background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); }
.social-link.youtube:hover { background: #ff0000; }

.recent-courses {
    max-height: 200px;
    overflow-y: auto;
}

.course-item {
    padding: 12px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.course-item:last-child {
    border-bottom: none;
}

.course-title {
    margin: 0 0 5px 0;
    font-size: 0.9rem;
}

.course-title a {
    color: #fff;
    text-decoration: none;
    font-weight: 500;
}

.course-title a:hover {
    color: rgba(255, 255, 255, 0.8);
}

.course-meta {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.7);
    margin: 0;
    display: flex;
    gap: 15px;
}

.course-meta span {
    display: flex;
    align-items: center;
}

.course-meta i {
    margin-right: 5px;
}

.footer-contact {
    background: rgba(255, 255, 255, 0.1);
    padding: 30px 0;
    position: relative;
    z-index: 1;
    backdrop-filter: blur(10px);
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 15px;
}

.contact-icon {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #fff;
}

.contact-info h6 {
    margin: 0 0 5px 0;
    font-size: 0.9rem;
    font-weight: 600;
    color: #fff;
}

.contact-info p {
    margin: 0;
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.8);
}

.footer-bottom {
    background: rgba(0, 0, 0, 0.2);
    padding: 20px 0;
    position: relative;
    z-index: 1;
}

.copyright {
    margin: 0;
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.8);
}

.footer-bottom-links {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    justify-content: flex-end;
    gap: 20px;
}

.footer-bottom-links a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    font-size: 0.85rem;
    transition: color 0.3s ease;
}

.footer-bottom-links a:hover {
    color: #fff;
}

/* Responsive */
@media (max-width: 768px) {
    .footer-main {
        padding: 40px 0 20px;
    }
    
    .footer-contact {
        padding: 20px 0;
    }
    
    .contact-item {
        margin-bottom: 20px;
    }
    
    .footer-bottom-links {
        justify-content: center;
        margin-top: 15px;
    }
    
    .social-links {
        justify-content: center;
    }
}

/* Scrollbar personalizado para cursos recientes */
.recent-courses::-webkit-scrollbar {
    width: 4px;
}

.recent-courses::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 2px;
}

.recent-courses::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 2px;
}

.recent-courses::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}
</style>

<!-- Scripts adicionales -->
<script src="js/jquery-3.7.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/dataTables.bootstrap.min.js"></script>
<script src="js/select2.full.min.js"></script>
<script src="js/jquery.inputmask.js"></script>
<script src="js/jquery.inputmask.date.extensions.js"></script>
<script src="js/jquery.inputmask.extensions.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/icheck.min.js"></script>
<script src="js/fastclick.js"></script>
<script src="js/jquery.sparkline.min.js"></script>
<script src="js/jquery.slimscroll.min.js"></script>
<script src="js/jquery.fancybox.pack.js"></script>
<script src="js/app.min.js"></script>
<script src="js/jscolor.js"></script>
<script src="js/on-off-switch.js"></script>
<script src="js/on-off-switch-onload.js"></script>
<script src="js/clipboard.min.js"></script>
<script src="js/demo.js"></script>
<script src="js/summernote.js"></script>

<script>
	$(document).ready(function() {
		$('#editor1').summernote({
			height: 300
		});
		$('#editor2').summernote({
			height: 300
		});
		$('#editor3').summernote({
			height: 300
		});
		$('#editor4').summernote({
			height: 300
		});
		$('#editor5').summernote({
			height: 300
		});
	});
	$(".top-cat").on('change', function() {
		var id = $(this).val();
		var dataString = 'id=' + id;
		$.ajax({
			type: "POST",
			url: "get-mid-category.php",
			data: dataString,
			cache: false,
			success: function(html) {
				$(".mid-cat").html(html);
			}
		});
	});
	$(".mid-cat").on('change', function() {
		var id = $(this).val();
		var dataString = 'id=' + id;
		$.ajax({
			type: "POST",
			url: "get-end-category.php",
			data: dataString,
			cache: false,
			success: function(html) {
				$(".end-cat").html(html);
			}
		});
	});
</script>

<script>
	$(function() {

		// Inicializar los elementos Select2
		$(".select2").select2();

		// Mascarilla de fecha dd/mm/yyyy
		$("#datemask").inputmask("dd-mm-yyyy", {
			"placeholder": "dd-mm-yyyy"
		});
		// Mascarilla de fecha mm/dd/yyyy
		$("#datemask2").inputmask("mm-dd-yyyy", {
			"placeholder": "mm-dd-yyyy"
		});
		// Máscara de dinero en euros
		$("[data-mask]").inputmask();

		// Selector de fecha
		$('#datepicker').datepicker({
			autoclose: true,
			format: 'dd-mm-yyyy',
			todayBtn: 'linked',
		});

		$('#datepicker1').datepicker({
			autoclose: true,
			format: 'dd-mm-yyyy',
			todayBtn: 'linked',
		});

		// iCheck para entradas de checkbox y radio
		$('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
			checkboxClass: 'icheckbox_minimal-blue',
			radioClass: 'iradio_minimal-blue'
		});
		// Esquema de color rojo para iCheck
		$('input[type="checkbox"].minimal-red, input[type="radio"].minimal-red').iCheck({
			checkboxClass: 'icheckbox_minimal-red',
			radioClass: 'iradio_minimal-red'
		});
		// Esquema de color plano rojo para iCheck
		$('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
			checkboxClass: 'icheckbox_flat-green',
			radioClass: 'iradio_flat-green'
		});

		$("#example1").DataTable();
		$('#example2').DataTable({
			"paging": true,
			"lengthChange": false,
			"searching": false,
			"ordering": true,
			"info": true,
			"autoWidth": false
		});

		$('#confirm-delete').on('show.bs.modal', function(e) {
			$(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
		});

		$('#confirm-approve').on('show.bs.modal', function(e) {
			$(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
		});

	});

	function confirmDelete() {
		return confirm("¿Está seguro de que desea eliminar estos datos?");
	}

	function confirmActive() {
		return confirm("¿Está seguro de que desea activar esto?");
	}

	function confirmInactive() {
		return confirm("¿Está seguro de que desea desactivar esto?");
	}
</script>

<script type="text/javascript">
	function showDiv(elem) {
		var photoDiv = document.getElementById('photo_div');
		var photoDivExisting = document.getElementById('photo_div_existing');
		var iconDiv = document.getElementById('icon_div');
		
		if (elem.value == 0) {
			if (photoDiv) photoDiv.style.display = "none";
			if (iconDiv) iconDiv.style.display = "none";
		}
		if (elem.value == 1) {
			if (photoDiv) photoDiv.style.display = "block";
			if (photoDivExisting) photoDivExisting.style.display = "block";
			if (iconDiv) iconDiv.style.display = "none";
		}
		if (elem.value == 2) {
			if (photoDiv) photoDiv.style.display = "none";
			if (photoDivExisting) photoDivExisting.style.display = "none";
			if (iconDiv) iconDiv.style.display = "block";
		}
	}

	function showContentInputArea(elem) {
		var showPageContent = document.getElementById('showPageContent');
		if (elem.value == 'Diseño de página a ancho completo') {
			if (showPageContent) showPageContent.style.display = "block";
		} else {
			if (showPageContent) showPageContent.style.display = "none";
		}
	}
</script>

<script type="text/javascript">
	$(document).ready(function() {

		$("#btnAddNew").click(function() {

			var rowNumber = $("#ProductTable tbody tr").length;

			var trNew = "";

			var addLink = "<div class=\"upload-btn" + rowNumber + "\"><input type=\"file\" name=\"photo[]\"  style=\"margin-bottom:5px;\"></div>";

			var deleteRow = "<a href=\"javascript:void()\" class=\"Delete btn btn-danger btn-xs\">X</a>";

			trNew = trNew + "<tr> ";

			trNew += "<td>" + addLink + "</td>";
			trNew += "<td style=\"width:28px;\">" + deleteRow + "</td>";

			trNew = trNew + " </tr>";

			$("#ProductTable tbody").append(trNew);

		});

		$('#ProductTable').delegate('a.Delete', 'click', function() {
			$(this).parent().parent().fadeOut('slow').remove();
			return false;
		});

	});

	var items = [];
	for (i = 1; i <= 24; i++) {
		var element = document.getElementById("tabField" + i);
		if (element) {
			items[i] = element;
		}
	}

	// Solo aplicar estilos si los elementos existen
	if (items[1]) items[1].style.display = 'block';
	if (items[2]) items[2].style.display = 'block';
	if (items[3]) items[3].style.display = 'block';
	if (items[4]) items[4].style.display = 'none';

	if (items[5]) items[5].style.display = 'block';
	if (items[6]) items[6].style.display = 'block';
	if (items[7]) items[7].style.display = 'block';
	if (items[8]) items[8].style.display = 'none';

	if (items[9]) items[9].style.display = 'block';
	if (items[10]) items[10].style.display = 'block';
	if (items[11]) items[11].style.display = 'block';
	if (items[12]) items[12].style.display = 'none';

	if (items[13]) items[13].style.display = 'block';
	if (items[14]) items[14].style.display = 'block';
	if (items[15]) items[15].style.display = 'block';
	if (items[16]) items[16].style.display = 'none';

	if (items[17]) items[17].style.display = 'block';
	if (items[18]) items[18].style.display = 'block';
	if (items[19]) items[19].style.display = 'block';
	if (items[20]) items[20].style.display = 'none';

	if (items[21]) items[21].style.display = 'block';
	if (items[22]) items[22].style.display = 'block';
	if (items[23]) items[23].style.display = 'block';
	if (items[24]) items[24].style.display = 'none';

	function funcTab1(elem) {
		var txt = elem.value;
		if (txt == 'Publicidad en Imagen') {
			if (items[1]) items[1].style.display = 'block';
			if (items[2]) items[2].style.display = 'block';
			if (items[3]) items[3].style.display = 'block';
			if (items[4]) items[4].style.display = 'none';
		}
		if (txt == 'Código Adsense') {
			if (items[1]) items[1].style.display = 'none';
			if (items[2]) items[2].style.display = 'none';
			if (items[3]) items[3].style.display = 'none';
			if (items[4]) items[4].style.display = 'block';
		}
	};

	function funcTab2(elem) {
		var txt = elem.value;
		if (txt == 'Publicidad en Imagen') {
			if (items[5]) items[5].style.display = 'block';
			if (items[6]) items[6].style.display = 'block';
			if (items[7]) items[7].style.display = 'block';
			if (items[8]) items[8].style.display = 'none';
		}
		if (txt == 'Código Adsense') {
			if (items[5]) items[5].style.display = 'none';
			if (items[6]) items[6].style.display = 'none';
			if (items[7]) items[7].style.display = 'none';
			if (items[8]) items[8].style.display = 'block';
		}
	};

	function funcTab3(elem) {
		var txt = elem.value;
		if (txt == 'Publicidad en Imagen') {
			if (items[9]) items[9].style.display = 'block';
			if (items[10]) items[10].style.display = 'block';
			if (items[11]) items[11].style.display = 'block';
			if (items[12]) items[12].style.display = 'none';
		}
		if (txt == 'Código Adsense') {
			if (items[9]) items[9].style.display = 'none';
			if (items[10]) items[10].style.display = 'none';
			if (items[11]) items[11].style.display = 'none';
			if (items[12]) items[12].style.display = 'block';
		}
	};

	function funcTab4(elem) {
		var txt = elem.value;
		if (txt == 'Publicidad en Imagen') {
			if (items[13]) items[13].style.display = 'block';
			if (items[14]) items[14].style.display = 'block';
			if (items[15]) items[15].style.display = 'block';
			if (items[16]) items[16].style.display = 'none';
		}
		if (txt == 'Código Adsense') {
			if (items[13]) items[13].style.display = 'none';
			if (items[14]) items[14].style.display = 'none';
			if (items[15]) items[15].style.display = 'none';
			if (items[16]) items[16].style.display = 'block';
		}
	};

	function funcTab5(elem) {
		var txt = elem.value;
		if (txt == 'Publicidad en Imagen') {
			if (items[17]) items[17].style.display = 'block';
			if (items[18]) items[18].style.display = 'block';
			if (items[19]) items[19].style.display = 'block';
			if (items[20]) items[20].style.display = 'none';
		}
		if (txt == 'Código Adsense') {
			if (items[17]) items[17].style.display = 'none';
			if (items[18]) items[18].style.display = 'none';
			if (items[19]) items[19].style.display = 'none';
			if (items[20]) items[20].style.display = 'block';
		}
	};

	function funcTab6(elem) {
		var txt = elem.value;
		if (txt == 'Publicidad en Imagen') {
			if (items[21]) items[21].style.display = 'block';
			if (items[22]) items[22].style.display = 'block';
			if (items[23]) items[23].style.display = 'block';
			if (items[24]) items[24].style.display = 'none';
		}
		if (txt == 'Código Adsense') {
			if (items[21]) items[21].style.display = 'none';
			if (items[22]) items[22].style.display = 'none';
			if (items[23]) items[23].style.display = 'none';
			if (items[24]) items[24].style.display = 'block';
		}
	};
</script>

</body>
</html>