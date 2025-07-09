<?php
require_once('admin/inc/config.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Certificado - Escáner QR</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        .qr-scanner-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .scanner-box {
            border: 2px solid #007bff;
            border-radius: 10px;
            padding: 20px;
            background: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .manual-input {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .verification-steps {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .step-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            border-left: 4px solid #007bff;
            background: #f8f9fa;
        }
        .step-number {
            background: #007bff;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
        }
        .qr-result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            display: none;
        }
        .qr-result.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .qr-result.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="page-header text-center">
                    <h1><i class="fa fa-qrcode"></i> Verificar Certificado</h1>
                    <p class="lead">Escanea el código QR de tu certificado para verificar su autenticidad</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="qr-scanner-container">
                    <div class="verification-steps">
                        <h3><i class="fa fa-info-circle"></i> Cómo verificar tu certificado</h3>
                        <div class="step-item">
                            <div class="step-number">1</div>
                            <div>
                                <strong>Escanea el código QR</strong><br>
                                Usa la cámara de tu dispositivo para escanear el código QR del certificado
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-number">2</div>
                            <div>
                                <strong>Verificación automática</strong><br>
                                El sistema verificará automáticamente la autenticidad del certificado
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-number">3</div>
                            <div>
                                <strong>Visualiza el certificado</strong><br>
                                Podrás ver el certificado completo con toda la información verificada
                            </div>
                        </div>
                    </div>

                    <div class="scanner-box">
                        <h4><i class="fa fa-camera"></i> Escáner de Código QR</h4>
                        <div id="reader"></div>
                        <div id="qr-result" class="qr-result"></div>
                    </div>

                    <div class="manual-input">
                        <h4><i class="fa fa-keyboard-o"></i> Ingreso Manual</h4>
                        <p>Si no puedes escanear el código QR, puedes ingresar el código manualmente:</p>
                        <?php 
                        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
                        $host = $_SERVER['HTTP_HOST'];
                        $ruta_cert = $protocolo . '://' . $host . '/certificado/verificar-certificado.php';
                        ?>
                        <form id="manual-form" method="GET" action="<?php echo $ruta_cert; ?>">
                            <div class="form-group">
                                <label for="qr-code">Código QR del Certificado:</label>
                                <input type="text" class="form-control" id="qr-code" name="qr" 
                                       placeholder="Ejemplo: 1-25-2024-01-15" required>
                                <small class="form-text text-muted">
                                    Ingresa el código QR que aparece en tu certificado
                                </small>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-search"></i> Verificar Certificado
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-lightbulb-o"></i> Información Importante</h3>
                    </div>
                    <div class="panel-body">
                        <div class="alert alert-info">
                            <h4><i class="fa fa-shield"></i> Certificados Seguros</h4>
                            <p>Todos nuestros certificados incluyen códigos QR únicos que permiten verificar su autenticidad en tiempo real.</p>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h4><i class="fa fa-exclamation-triangle"></i> Verificación</h4>
                            <p>Los certificados verificados muestran una insignia de autenticidad y toda la información del estudiante.</p>
                        </div>

                        <div class="alert alert-success">
                            <h4><i class="fa fa-check-circle"></i> Beneficios</h4>
                            <ul>
                                <li>Verificación instantánea</li>
                                <li>Información completa del curso</li>
                                <li>Datos del estudiante verificados</li>
                                <li>Acceso desde cualquier dispositivo</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        function onScanSuccess(decodedText, decodedResult) {
            // Detener el escáner
            html5QrcodeScanner.clear();
            
            // Mostrar resultado exitoso
            const resultDiv = document.getElementById('qr-result');
            resultDiv.className = 'qr-result success';
            resultDiv.innerHTML = `
                <h4><i class="fa fa-check-circle"></i> Código QR Detectado</h4>
                <p><strong>Código:</strong> ${decodedText}</p>
                <p>Redirigiendo a la verificación...</p>
            `;
            resultDiv.style.display = 'block';
            
            // Redirigir a la página de verificación
            setTimeout(() => {
                window.location.href = `<?php echo $ruta_cert; ?>?qr=${encodeURIComponent(decodedText)}`;
            }, 2000);
        }

        function onScanFailure(error) {
            // Manejar errores de escaneo (opcional)
            console.warn(`Error de escaneo: ${error}`);
        }

        // Inicializar el escáner QR
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            { 
                fps: 10, 
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            },
            false
        );
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);

        // Validación del formulario manual
        document.getElementById('manual-form').addEventListener('submit', function(e) {
            const qrCode = document.getElementById('qr-code').value.trim();
            if (!qrCode) {
                e.preventDefault();
                alert('Por favor ingresa el código QR del certificado');
                return false;
            }
        });
    </script>
</body>
</html> 