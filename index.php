<?php

require_once('header.php');

// Obtener sliders
$statement = $pdo->prepare("SELECT * FROM sliders ORDER BY posicion ASC");
$statement->execute();
$sliders = $statement->fetchAll(PDO::FETCH_ASSOC);

// Obtener configuraciones
$statement = $pdo->prepare("SELECT * FROM configuraciones WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

$cta_title = '';
$cta_content = '';
$cta_read_more_text = '';
$cta_read_more_url = '';
$cta_photo = '';
$home_service_on_off = 0;
$home_welcome_on_off = 0;
$bienvenida_activa = 0;
$certificados_activos = 0;
$servicios_activos = 0;
$boletin_activo = 0;
$total_certificados_recientes = 6;
$total_certificados_populares = 6;
$faq_activo = 0;

foreach ($result as $row) {
    $cta_title = $row['cta_title'] ?? '';
    $cta_content = $row['cta_content'] ?? '';
    $cta_read_more_text = $row['cta_read_more_text'] ?? '';
    $cta_read_more_url = $row['cta_read_more_url'] ?? '';
    $cta_photo = $row['cta_photo'] ?? '';
    $home_service_on_off = $row['home_service_on_off'] ?? 0;
    $home_welcome_on_off = $row['home_welcome_on_off'] ?? 0;
    $bienvenida_activa = $row['bienvenida_activa'] ?? 0;
    $certificados_activos = $row['certificados_activos'] ?? 0;
    $servicios_activos = $row['servicios_activos'] ?? 0;
    $boletin_activo = $row['boletin_activo'] ?? 0;
    $total_certificados_recientes = $row['total_certificados_recientes'] ?? 6;
    $total_certificados_populares = $row['total_certificados_populares'] ?? 6;
    $faq_activo = $row['faq_activo'] ?? 0;
}

// Obtener cursos recientes
$statement = $pdo->prepare(
    "SELECT c.*, cat.nombre_categoria
    FROM curso c
    LEFT JOIN categoria cat ON c.idcategoria = cat.idcategoria
    WHERE c.estado = 'Activo'
    ORDER BY c.idcurso DESC LIMIT :limit"
);
$statement->bindValue(':limit', $total_certificados_recientes, PDO::PARAM_INT);
$statement->execute();
$cursos_recientes = $statement->fetchAll(PDO::FETCH_ASSOC);

// Obtener cursos populares (basado en inscripciones)
$statement = $pdo->prepare(
    "SELECT c.*, cat.nombre_categoria, COUNT(i.idinscripcion) as total_inscripciones
    FROM curso c
    LEFT JOIN categoria cat ON c.idcategoria = cat.idcategoria
    LEFT JOIN inscripcion i ON c.idcurso = i.idcurso
    WHERE c.estado = 'Activo'
    GROUP BY c.idcurso
    ORDER BY total_inscripciones DESC, c.idcurso DESC
    LIMIT :limit"
);
$statement->bindValue(':limit', $total_certificados_populares, PDO::PARAM_INT);
$statement->execute();
$cursos_populares = $statement->fetchAll(PDO::FETCH_ASSOC);

// Obtener servicios
$statement = $pdo->prepare("SELECT * FROM servicios");
$statement->execute();
$servicios = $statement->fetchAll(PDO::FETCH_ASSOC);

// Obtener preguntas frecuentes
$statement = $pdo->prepare("SELECT * FROM preguntas_frecuentes ORDER BY orden_pregunta ASC");
$statement->execute();
$preguntas_frecuentes = $statement->fetchAll(PDO::FETCH_ASSOC);

?>

<section class="slider-section">
    <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-indicators">
            <?php foreach ($sliders as $key => $slider): ?>
                <button type="button"
                        data-bs-target="#carouselExampleIndicators"
                        data-bs-slide-to="<?php echo $key; ?>"
                        <?php echo $key == 0 ? 'class="active"' : ''; ?>
                        aria-current="<?php echo $key == 0 ? 'true' : 'false'; ?>"
                        aria-label="Slide <?php echo $key + 1; ?>">
                </button>
            <?php endforeach; ?>
        </div>

        <div class="carousel-inner">
            <?php foreach ($sliders as $key => $slider): ?>
                <div class="carousel-item <?php echo $key == 0 ? 'active' : ''; ?>">
                    <?php if (file_exists('assets/uploads/' . $slider['foto'])): ?>
                        <div class="carousel-image" style="background-image: url('assets/uploads/<?php echo $slider['foto']; ?>');"></div>
                    <?php endif; ?>
                    <div class="carousel-caption">
                        <h2 class="display-4 fw-bold"><?php echo $slider['titulo']; ?></h2>
                        <p class="lead"><?php echo $slider['contenido']; ?></p>
                        <?php if ($slider['texto_boton']): ?>
                            <a href="<?php echo $slider['url_boton']; ?>" class="btn btn-primary btn-lg">
                                <?php echo $slider['texto_boton']; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
        </button>
    </div>
</section>

<style>
.slider-section {
    margin-top: -20px;
}

.carousel {
    margin-bottom: 2rem;
}

.carousel-item {
    height: 600px;
    position: relative;
}

.carousel-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.carousel-caption {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    width: 80%;
    max-width: 800px;
    padding: 2rem;
    z-index: 2;
}

.carousel-caption h2 {
    font-size: 3.5rem;
    font-weight: 700;
    color: #ffffff;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    margin-bottom: 1.5rem;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.carousel-caption p {
    font-size: 1.5rem;
    color: #ffffff;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
    margin-bottom: 2rem;
    line-height: 1.6;
}

.carousel-caption .btn {
    padding: 1rem 2.5rem;
    font-size: 1.2rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    background: rgba(255, 255, 255, 0.9);
    color: #333;
    border: none;
    border-radius: 50px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.carousel-caption .btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    background: #ffffff;
}

.carousel-control-prev,
.carousel-control-next {
    width: 5%;
    opacity: 0;
    transition: all 0.3s ease;
}

.carousel:hover .carousel-control-prev,
.carousel:hover .carousel-control-next {
    opacity: 0.8;
}

.carousel-control-prev-icon,
.carousel-control-next-icon {
    width: 30px;
    height: 30px;
    background-color: rgba(0, 0, 0, 0.5);
    border-radius: 50%;
    background-size: 50%;
}

.carousel-indicators {
    margin-bottom: 1rem;
}

.carousel-indicators button {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin: 0 5px;
    background-color: rgba(255, 255, 255, 0.5);
    border: none;
    transition: all 0.3s ease;
}

.carousel-indicators button.active {
    background-color: #fff;
    transform: scale(1.2);
}

@media (max-width: 768px) {
    .carousel-item {
        height: 400px;
    }

    .carousel-caption {
        padding: 1.5rem;
        width: 90%;
    }

    .carousel-caption h2 {
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    .carousel-caption p {
        font-size: 1.1rem;
        margin-bottom: 1.5rem;
    }

    .carousel-caption .btn {
        padding: 0.8rem 2rem;
        font-size: 1rem;
    }
}
</style>

<script>
$(document).ready(function() {
    $('#bs-slider').carousel({
        interval: 5000
    });

    // Animaciones
    $('.carousel').on('slide.bs.carousel', function () {
        $(this).find('.animated').removeClass('animated');
    });

    $('.carousel').on('slid.bs.carousel', function () {
        var currentSlide = $(this).find('.active');
        currentSlide.find('[data-animation]').each(function() {
            var animation = $(this).data('animation');
            $(this).addClass(animation);
        });
    });
});
</script>

<?php if ($bienvenida_activa): ?>
<section class="welcome-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <h2>Bienvenido a nuestro Sistema de Certificados</h2>
                <p>Ofrecemos cursos de alta calidad con certificación oficial</p>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="verification-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="verification-title">
                    <i class="fa fa-shield"></i> Verificación de Certificados
                </h2>
                <p class="verification-subtitle">
                    Todos nuestros certificados incluyen códigos QR únicos para verificar su autenticidad en tiempo real.
                    Escanea el código QR de tu certificado y verifica su validez instantáneamente.
                </p>
                <div class="verification-features">
                    <div class="feature-item">
                        <i class="fa fa-check-circle"></i>
                        <span>Verificación instantánea</span>
                    </div>
                    <div class="feature-item">
                        <i class="fa fa-mobile"></i>
                        <span>Acceso desde cualquier dispositivo</span>
                    </div>
                    <div class="feature-item">
                        <i class="fa fa-lock"></i>
                        <span>Certificados seguros y auténticos</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="verification-actions">
                    <a href="verificar-qr.php" class="btn btn-light btn-lg mb-3">
                        <i class="fa fa-qrcode"></i> Verificar Certificado
                    </a>
                    <br>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.verification-section {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    position: relative;
    overflow: hidden;
}

.verification-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    pointer-events: none;
}

.verification-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    position: relative;
    z-index: 1;
}

.verification-subtitle {
    font-size: 1.2rem;
    margin-bottom: 2rem;
    opacity: 0.9;
    line-height: 1.6;
    position: relative;
    z-index: 1;
}

.verification-features {
    position: relative;
    z-index: 1;
}

.feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.feature-item i {
    margin-right: 10px;
    font-size: 1.2rem;
    color: #28a745;
}

.verification-actions {
    position: relative;
    z-index: 1;
}

.verification-actions .btn {
    transition: all 0.3s ease;
}

.verification-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

@media (max-width: 768px) {
    .verification-title {
        font-size: 2rem;
    }

    .verification-subtitle {
        font-size: 1rem;
    }

    .verification-actions {
        margin-top: 2rem;
    }
}
</style>

<?php if ($certificados_activos == 1): ?>
    <div class="courses-section pt_70 pb_70">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="headline">
                        <h2>Cursos Recientes</h2>
                        <h3>Descubre nuestros últimos cursos disponibles</h3>
                    </div>
                </div>
            </div>
            <div class="row">
                <?php foreach ($cursos_recientes as $curso): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($curso['nombre_curso']); ?></h5>
                                <p class="card-text text-muted mb-2"><?php echo htmlspecialchars($curso['nombre_categoria']); ?></p>
                                <p class="card-text"><?php echo substr(htmlspecialchars($curso['descripcion']), 0, 100) . '...'; ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($curso['duracion']); ?> horas</span>
                                    <a href="curso.php?id=<?php echo $curso['idcurso']; ?>" class="btn btn-outline-primary">Ver Detalles</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="courses-section bg-light pt_70 pb_70">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="headline">
                        <h2>Cursos Populares</h2>
                        <h3>Los cursos más solicitados por nuestros estudiantes</h3>
                    </div>
                </div>
            </div>
            <div class="row">
                <?php foreach ($cursos_populares as $curso): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($curso['nombre_curso']); ?></h5>
                                <p class="card-text text-muted mb-2"><?php echo htmlspecialchars($curso['nombre_categoria']); ?></p>
                                <p class="card-text"><?php echo substr(htmlspecialchars($curso['descripcion']), 0, 100) . '...'; ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($curso['duracion']); ?> horas</span>
                                    <a href="curso.php?id=<?php echo $curso['idcurso']; ?>" class="btn btn-outline-primary">Ver Detalles</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <section class="featured-courses py-5">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-12 text-center">
                    <h2 class="section-title">Cursos Destacados</h2>
                    <p class="section-subtitle">Explora nuestros cursos más populares</p>
                </div>
            </div>

            <div class="row">
                <?php foreach ($cursos_populares as $curso): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card course-card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $curso['nombre_curso']; ?></h5>
                                <p class="card-text"><?php echo substr($curso['descripcion'], 0, 100) . '...'; ?></p>
                                <div class="course-details">
                                    <span><i class="fas fa-clock"></i> <?php echo $curso['duracion']; ?> horas</span>
                                    <span><i class="fas fa-calendar"></i> <?php echo $curso['dias_semana']; ?></span>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <a href="curso.php?id=<?php echo $curso['idcurso']; ?>" class="btn btn-primary w-100">Ver Detalles</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <style>
    .featured-courses {
        background-color: #f8f9fa;
    }

    .section-title {
        color: #333;
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .section-subtitle {
        color: #666;
        font-size: 1.2rem;
        margin-bottom: 2rem;
    }

    .course-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
    }

    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .course-card .card-title {
        color: #333;
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .course-card .card-text {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    .course-details {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        font-size: 0.85rem;
        color: #666;
    }

    .course-details span {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .course-details i {
        color: #007bff;
    }

    .card-footer .btn {
        background-color: #007bff;
        border: none;
        padding: 0.75rem;
        font-weight: 500;
        transition: background-color 0.3s ease;
    }

    .card-footer .btn:hover {
        background-color: #0056b3;
    }
    </style>
<?php endif; ?>

<?php if ($servicios_activos == 1): ?>
    <section class="services-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center mb-5">
                    <h2 class="section-title">Nuestros Servicios</h2>
                    <p class="section-subtitle">Descubre todo lo que podemos ofrecerte</p>
                </div>
            </div>
            <div class="row">
                <?php foreach ($servicios as $servicio): ?>
                    <div class="col-md-4 mb-4">
                        <div class="service-card">
                            <?php if (file_exists('assets/uploads/' . $servicio['foto'])): ?>
                                <div class="service-image">
                                    <img src="assets/uploads/<?php echo $servicio['foto']; ?>" alt="<?php echo $servicio['titulo']; ?>" class="img-fluid">
                                </div>
                            <?php endif; ?>
                            <div class="service-content">
                                <h3 class="service-title"><?php echo $servicio['titulo']; ?></h3>
                                <p class="service-description"><?php echo $servicio['contenido']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <style>
    .services-section {
        background-color: #f8f9fa;
        padding: 80px 0;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 1rem;
        position: relative;
        padding-bottom: 15px;
    }

    .section-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background-color: #007bff;
    }

    .section-subtitle {
        font-size: 1.2rem;
        color: #6c757d;
        margin-bottom: 3rem;
    }

    .service-card {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
    }

    .service-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .service-image {
        height: 200px;
        overflow: hidden;
    }

    .service-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .service-card:hover .service-image img {
        transform: scale(1.1);
    }

    .service-content {
        padding: 25px;
    }

    .service-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
    }

    .service-description {
        color: #6c757d;
        line-height: 1.6;
        margin-bottom: 0;
    }

    @media (max-width: 768px) {
        .service-card {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 2rem;
        }
    }
    </style>
<?php endif; ?>

<?php if ($faq_activo == 1): ?>
    <section class="faq-button-section py-5 text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="section-title">¿Tienes dudas?</h2>
                    <p class="section-subtitle">Consulta nuestras preguntas frecuentes</p>
                    <a href="faq.php" class="btn btn-primary btn-lg mt-3">
                        Ver Preguntas Frecuentes
                        <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <style>
    .faq-button-section {
        background-color: #f8f9fa;
        padding: 60px 0;
    }

    .faq-button-section .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 1rem;
    }

    .faq-button-section .section-subtitle {
        font-size: 1.2rem;
        color: #6c757d;
        margin-bottom: 2rem;
    }

    .faq-button-section .btn {
        padding: 1rem 2rem;
        font-size: 1.1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-radius: 50px;
        transition: all 0.3s ease;
    }

    .faq-button-section .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    @media (max-width: 768px) {
        .faq-button-section .section-title {
            font-size: 2rem;
        }

        .faq-button-section .section-subtitle {
            font-size: 1rem;
        }

        .faq-button-section .btn {
            font-size: 1rem;
            padding: 0.8rem 1.5rem;
        }
    }
    </style>
<?php endif; ?>
<?php require_once('footer.php'); ?>