<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';

$statement = $pdo->prepare("SELECT * FROM paginas WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $titulo_pagina = $row['nombre_pagina'];
    $contenido_pagina = $row['contenido_pagina'];
    $banner_pagina = $row['banner_pagina'];
    $meta_titulo = $row['meta_titulo'];
    $meta_palabras_clave = $row['meta_palabras_clave'];
    $meta_descripcion = $row['meta_descripcion'];
}

$statement = $pdo->prepare("SELECT * FROM configuraciones WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $contact_map_iframe = $row['mapa_contacto'];
    $contact_email = $row['correo_contacto'];
    $contact_phone = $row['telefono_contacto'];
    $contact_address = $row['direccion_contacto'];
}
?>

<style>
.page-banner {
    background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/uploads/<?php echo $banner_pagina; ?>') no-repeat center center;
    background-size: cover;
    padding: 100px 0;
    color: white;
    text-align: center;
    border-radius: 0.5rem;
    margin-bottom: 40px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}
.page-banner h1 {
    font-weight: 700;
    font-size: 3rem;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
}
.page-title {
    font-weight: 600;
    margin-bottom: 30px;
    color: #222;
    text-align: center;
}
.page-content {
    font-size: 1.1rem;
    line-height: 1.7;
    color: #444;
    background: #fff;
    padding: 30px;
    border-radius: 0.5rem;
    box-shadow: 0 0 15px rgba(0,0,0,0.05);
    margin-bottom: 50px;
}
.contact-info .contact-item {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    transition: background-color 0.3s ease;
}
.contact-info .contact-item:hover {
    background-color: #e9ecef;
}
.contact-info .contact-item i {
    font-size: 2.5rem;
    color: #0d6efd;
    margin-bottom: 10px;
}
.contact-info .contact-item h3 {
    margin-bottom: 10px;
    font-weight: 600;
}
.contact-form h2 {
    margin-bottom: 30px;
    font-weight: 700;
    text-align: center;
    color: #0d6efd;
}
.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
}
.btn-primary {
    min-width: 150px;
    font-weight: 600;
}
@media (max-width: 767px) {
    .page-banner {
        padding: 60px 20px;
    }
    .page-banner h1 {
        font-size: 2rem;
    }
}
</style>


<div class="page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
                <div class="contact-info row mb-5 text-center g-4">
                    <div class="col-md-4">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <h3>Dirección</h3>
                            <p><?php echo htmlspecialchars($contact_address); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <h3>Teléfono</h3>
                            <p><?php echo htmlspecialchars($contact_phone); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <h3>Email</h3>
                            <p><?php echo htmlspecialchars($contact_email); ?></p>
                        </div>
                    </div>
                </div>

                <div class="contact-form">
                    <h2>Envíanos un mensaje</h2>

                    <?php
                    // Alertas con Bootstrap
                    if ($error_message != '') {
                        echo '<div class="alert alert-danger">'.nl2br(htmlspecialchars($error_message)).'</div>';
                    }
                    if ($success_message != '') {
                        echo '<div class="alert alert-success">'.htmlspecialchars($success_message).'</div>';
                    }
                    ?>

                    <form action="" method="post" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="visitor_name" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="visitor_name" name="visitor_name" placeholder="Ingrese su nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label for="visitor_email" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="visitor_email" name="visitor_email" placeholder="Ingrese su correo" required>
                            </div>
                            <div class="col-md-6">
                                <label for="visitor_phone" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="visitor_phone" name="visitor_phone" placeholder="Ingrese su teléfono" required>
                            </div>
                            <div class="col-md-6">
                                <label for="visitor_message" class="form-label">Mensaje</label>
                                <textarea class="form-control" id="visitor_message" name="visitor_message" rows="5" placeholder="Escribe tu mensaje aquí" required></textarea>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <button type="submit" name="form_contact" class="btn btn-primary btn-lg">Enviar Mensaje</button>
                        </div>
                    </form>
                </div>

                <h3 class="mt-5 text-center">Encuéntranos en el Mapa</h3>
                <div class="map-responsive mt-3 rounded shadow-sm overflow-hidden" style="min-height: 400px;">
                    <?php echo $contact_map_iframe; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Responsividad para iframe */
.map-responsive iframe {
    width: 100%;
    height: 100%;
    border: 0;
    min-height: 400px;
}
</style>

<?php require_once('footer.php'); ?>