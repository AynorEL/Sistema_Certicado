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

// Detectar si estamos en admin
$is_admin = strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false;
?>

<!-- Footer Moderno -->
<footer class="footer-modern">
    <!-- Sección principal del footer -->
    <div class="footer-main">
        <div class="container">
            <div class="row">
                <!-- Información de la empresa -->
                <div class="col-lg-4 col-md-6 col-sm-12" style="margin-bottom: 30px;">
                    <div class="footer-widget">
                        <div class="footer-logo" style="margin-bottom: 20px;">
                            <?php if (file_exists('assets/uploads/' . $config['logo'])): ?>
                                <img src="assets/uploads/<?php echo $config['logo']; ?>" alt="Logo" class="img-responsive" style="max-height: 60px;">
                            <?php else: ?>
                                <h4 class="text-white" style="margin-bottom: 0;">Sistema de Certificados</h4>
                            <?php endif; ?>
                        </div>
                        <p class="footer-description">
                            <?php echo $config['pie_pagina_descripcion'] ?? 'Plataforma especializada en la emisión y gestión de certificados profesionales. Ofrecemos soluciones digitales para validar y verificar la autenticidad de tus logros académicos.'; ?>
                        </p>
                        <?php if (!$is_admin && isset($redes_sociales) && is_array($redes_sociales)):
                            $hay_redes = false;
                            foreach ($redes_sociales as $red) {
                                if (!empty($red['url_red'])) {
                                    $hay_redes = true;
                                    break;
                                }
                            }
                            if ($hay_redes): ?>
                            <div class="social-links">
                                <?php foreach ($redes_sociales as $red): ?>
                                    <?php if (!empty($red['url_red'])): ?>
                                        <a href="<?php echo $red['url_red']; ?>" class="social-link <?php echo $red['nombre_red']; ?>" target="_blank">
                                            <i class="<?php echo $red['icono_red']; ?>"></i>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Enlaces rápidos -->
                <div class="col-lg-2 col-md-6 col-sm-12" style="margin-bottom: 30px;">
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
                <div class="col-lg-2 col-md-6 col-sm-12" style="margin-bottom: 30px;">
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
                <div class="col-lg-4 col-md-6 col-sm-12" style="margin-bottom: 30px;">
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
                <div class="col-lg-3 col-md-6 col-sm-12" style="margin-bottom: 20px;">
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
                <div class="col-lg-3 col-md-6 col-sm-12" style="margin-bottom: 20px;">
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
                <div class="col-lg-3 col-md-6 col-sm-12" style="margin-bottom: 20px;">
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
                <div class="col-lg-3 col-md-6 col-sm-12" style="margin-bottom: 20px;">
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
            <div class="row" style="display: flex; align-items: center;">
                <div class="col-md-6 col-sm-12">
                    <p class="copyright">
                        <?php echo $config['pie_pagina_derechos'] ?? '&copy; ' . date('Y') . ' Sistema de Certificados. Todos los derechos reservados.'; ?>
                    </p>
                </div>
                <div class="col-md-6 col-sm-12">
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
    background: linear-gradient(135deg, #1e40af 0%, #7c3aed 50%, #be185d 100%);
    color: #fff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 -4px 16px rgba(0,0,0,0.06);
    font-size: 16px;
    font-family: 'Roboto', Arial, sans-serif;
}

.footer-modern .footer-main {
    padding: 40px 0 20px 0;
}

.footer-modern .footer-widget {
    margin-bottom: 20px;
}

.footer-modern .footer-logo img {
    max-height: 60px;
}

.footer-modern .widget-title {
    color: #007bff;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 18px;
}

.footer-modern .footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-modern .footer-links li {
    margin-bottom: 10px;
}

.footer-modern .footer-links a {
    color: #222;
    text-decoration: none;
    transition: color 0.2s;
    font-weight: 500;
}

.footer-modern .footer-links a:hover {
    color: #007bff;
    text-decoration: underline;
}

.footer-modern .recent-courses .course-title a {
    color: #007bff;
    font-weight: 600;
    font-size: 15px;
}

.footer-modern .recent-courses .course-title a:hover {
    text-decoration: underline;
}

.footer-modern .course-meta {
    font-size: 14px;
    color: #555;
}

.footer-modern .social-links {
    margin-top: 18px;
}

.footer-modern .social-link {
    display: inline-block;
    margin-right: 10px;
    color: #007bff;
    font-size: 20px;
    transition: color 0.2s;
}

.footer-modern .social-link:hover {
    color: #0056b3;
}

.footer-modern .footer-contact {
    background: rgba(255,255,255,0.13); /* Semitransparente para contraste sobre el degradado */
    padding: 25px 0 10px 0;
    color: #fff;
    font-size: 15px;
    border-top: 1px solid rgba(255,255,255,0.12);
}

.footer-modern .contact-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 10px;
}

.footer-modern .contact-icon {
    color: #60a5fa; /* Azul claro para resaltar iconos */
    font-size: 22px;
    margin-right: 12px;
    margin-top: 2px;
}

.footer-modern .contact-info h6 {
    margin: 0 0 2px 0;
    font-size: 15px;
    color: #60a5fa;
    font-weight: 600;
}

.footer-modern .contact-info p {
    margin: 0;
    color: #fff;
    font-size: 15px;
}

.footer-modern .footer-bottom {
    background: #181e2a; /* Sólido oscuro para resaltar el cierre */
    padding: 12px 0;
    font-size: 15px;
    color: #fff;
    border-top: 1px solid rgba(255,255,255,0.12);
}

.footer-modern .footer-bottom-links {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: flex-end;
}

.footer-modern .footer-bottom-links a {
    color: #60a5fa;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}

.footer-modern .footer-bottom-links a:hover {
    text-decoration: underline;
    color: #fff;
}

@media (max-width: 991px) {
    .footer-modern .footer-main .row > div {
        margin-bottom: 30px;
    }
    .footer-modern .footer-bottom-links {
        justify-content: center;
        margin-top: 10px;
    }
}
@media (max-width: 767px) {
    .footer-modern .footer-main {
        padding: 30px 0 10px 0;
    }
    .footer-modern .footer-bottom {
        text-align: center;
    }
    .footer-modern .footer-bottom-links {
        justify-content: center;
    }
}

.whatsapp-float {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #25d366;
    color: #fff;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.2rem;
    box-shadow: 0 4px 16px rgba(0,0,0,0.18);
    z-index: 9999;
    transition: background 0.2s;
    text-decoration: none;
}
.whatsapp-float:hover {
    background: #128c7e;
    color: #fff;
}
</style>

<!-- Scripts adicionales -->
<script src="admin/js/jquery-2.2.4.min.js"></script>
<script src="admin/js/bootstrap.min.js"></script>
<script src="admin/js/jquery.dataTables.min.js"></script>
<script src="admin/js/dataTables.bootstrap.min.js"></script>
<script src="admin/js/select2.full.min.js"></script>
<script src="admin/js/jquery.inputmask.js"></script>
<script src="admin/js/jquery.inputmask.date.extensions.js"></script>
<script src="admin/js/jquery.inputmask.extensions.js"></script>
<script src="admin/js/moment.min.js"></script>
<script src="admin/js/bootstrap-datepicker.js"></script>
<script src="admin/js/icheck.min.js"></script>
<script src="admin/js/fastclick.js"></script>
<script src="admin/js/jquery.sparkline.min.js"></script>
<script src="admin/js/jquery.slimscroll.min.js"></script>
<script src="admin/js/jquery.fancybox.pack.js"></script>
<script src="admin/js/app.min.js"></script>
<script src="admin/js/jscolor.js"></script>
<script src="admin/js/on-off-switch.js"></script>
<script src="admin/js/on-off-switch-onload.js"></script>
<script src="admin/js/clipboard.min.js"></script>
<script src="admin/js/demo.js"></script>
<script src="admin/js/summernote.js"></script>

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

<!-- Botón flotante de WhatsApp -->
<?php if (!$is_admin && isset($whatsapp_link) && !empty($whatsapp_link)): ?>
    <a href="<?php echo $whatsapp_link; ?>" class="whatsapp-float" target="_blank" title="Contáctanos por WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>
<?php endif; ?>

</body>
</html>