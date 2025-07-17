<?php require_once('header.php'); ?>

<?php
// Obtener preguntas frecuentes
$statement = $pdo->prepare("SELECT * FROM preguntas_frecuentes ORDER BY orden_pregunta ASC");
$statement->execute();
$preguntas_frecuentes = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Banner Section -->
<section class="page-banner">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <h1 class="banner-title">Preguntas Frecuentes</h1>
                <p class="banner-subtitle">Resolvemos tus dudas más comunes</p>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <?php foreach($preguntas_frecuentes as $key => $faq): ?>
                    <div class="accordion-item" id="faq-<?php echo $faq['id']; ?>">
                        <h2 class="accordion-header" id="heading<?php echo $faq['id']; ?>">
                            <button class="accordion-button <?php echo $key == 0 ? '' : 'collapsed'; ?>" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapse<?php echo $faq['id']; ?>" 
                                    aria-expanded="<?php echo $key == 0 ? 'true' : 'false'; ?>" 
                                    aria-controls="collapse<?php echo $faq['id']; ?>">
                                <?php echo htmlspecialchars($faq['titulo_pregunta']); ?>
                            </button>
                        </h2>
                        <div id="collapse<?php echo $faq['id']; ?>" 
                             class="accordion-collapse collapse <?php echo $key == 0 ? 'show' : ''; ?>" 
                             aria-labelledby="heading<?php echo $faq['id']; ?>" 
                             data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <?php echo $faq['contenido_pregunta']; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Script de resaltado -->
<script src="assets/js/search-highlight.js"></script>

<!-- Styles -->
<style>
.page-banner {
    background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/img/banerpreguntas.jpg');
    background-size: cover;
    background-position: center;
    padding: 100px 0;
    color: #fff;
    text-align: center;
}

.banner-title {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.banner-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
}

.faq-section {
    background-color: #ffffff;
    padding: 80px 0;
}

.accordion-item {
    border: 1px solid rgba(0,0,0,.125);
    margin-bottom: 1rem;
    border-radius: 8px !important;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.accordion-button {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    padding: 1.25rem;
    background-color: #f8f9fa;
    border: none;
    transition: all 0.3s ease;
}

.accordion-button:not(.collapsed) {
    color: #007bff;
    background-color: #e7f1ff;
    box-shadow: none;
}

.accordion-button:focus {
    box-shadow: none;
    border-color: rgba(0,0,0,.125);
}

.accordion-button::after {
    background-size: 1.2rem;
    transition: all 0.3s ease;
}

.accordion-body {
    padding: 1.5rem;
    color: #6c757d;
    line-height: 1.6;
    background-color: #fff;
}

@media (max-width: 768px) {
    .banner-title {
        font-size: 2rem;
    }

    .banner-subtitle {
        font-size: 1rem;
    }

    .accordion-button {
        font-size: 1rem;
        padding: 1rem;
    }

    .accordion-body {
        padding: 1rem;
    }
}
</style>

<?php require_once('footer.php'); ?>
