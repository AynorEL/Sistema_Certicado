<?php require_once('header.php'); ?>

<?php
if (isset($_POST['form1'])) {

    // actualizando en la base de datos
    foreach ($_POST['lang_value'] as $key => $val) {
        $arr[$key] = $val;
    }

    for ($i = 1; $i <= count($arr); $i++) {
        $statement = $pdo->prepare("UPDATE tbl_language SET lang_value=? WHERE lang_id=?");
        $statement->execute(array($arr[$i], $i));
    }
    $success_message = 'La configuración del idioma se ha actualizado correctamente.';
}

$i = 0;
$statement = $pdo->prepare("SELECT * FROM tbl_language");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $i++;
    $lang_ids[$i] = $row['lang_value'];
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Configurar Idioma</h1>
    </div>
</section>


<?php
$statement = $pdo->prepare("SELECT * FROM tbl_language");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
}
?>

<section class="content">

    <div class="row">
        <div class="col-md-12">

            <?php if ($error_message): ?>
                <div class="callout callout-danger">
                    <p>
                        <?php echo $error_message; ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="callout callout-success">
                    <p><?php echo $success_message; ?></p>
                </div>
            <?php endif; ?>

            <form class="form-horizontal" action="" method="post">

                <h3 style="font-size:20px;font-weight:500;">Básico</h3>
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Moneda <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[1]" value="<?php echo $lang_ids[1]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Buscar Producto <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[2]" value="<?php echo $lang_ids[2]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Buscar <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[3]" value="<?php echo $lang_ids[3]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Enviar <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[4]" value="<?php echo $lang_ids[4]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Actualizar <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[5]" value="<?php echo $lang_ids[5]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Leer Más <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[6]" value="<?php echo $lang_ids[6]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Serial <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[7]" value="<?php echo $lang_ids[7]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Foto <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[8]" value="<?php echo $lang_ids[8]; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <h3 style="font-size:20px;font-weight:500;">Iniciar sesión</h3>
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Iniciar sesión <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[9]" value="<?php echo $lang_ids[9]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Inicio de sesión del cliente <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[10]" value="<?php echo $lang_ids[10]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Haga clic aquí para iniciar sesión <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[11]" value="<?php echo $lang_ids[11]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Volver a la página de inicio de sesión <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[12]" value="<?php echo $lang_ids[12]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Iniciado como <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[13]" value="<?php echo $lang_ids[13]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Cerrar sesión <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[14]" value="<?php echo $lang_ids[14]; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <h3 style="font-size:20px;font-weight:500;">Registro</h3>
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Registrarse <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[15]" value="<?php echo $lang_ids[15]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Registro del cliente <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[16]" value="<?php echo $lang_ids[16]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Registro exitoso <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[17]" value="<?php echo $lang_ids[17]; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <h3 style="font-size:20px;font-weight:500;">Carrito y Pago</h3>
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Carrito <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[18]" value="<?php echo $lang_ids[18]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Ver carrito <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[19]" value="<?php echo $lang_ids[19]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Actualizar Carrito <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[20]" value="<?php echo $lang_ids[20]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Agregar al Carrito <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[154]" value="<?php echo $lang_ids[154]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Volver al Carrito <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[21]" value="<?php echo $lang_ids[21]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Finalizar Compra <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[22]" value="<?php echo $lang_ids[22]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Proceder a Finalizar Compra <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[23]" value="<?php echo $lang_ids[23]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Por favor, inicia sesión como cliente para finalizar la compra <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[160]" value="<?php echo $lang_ids[160]; ?>">
                            </div>
                        </div>

                    </div>
                </div>

                <h3 style="font-size:20px;font-weight:500;">Pago</h3>
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Pedidos <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[24]" value="<?php echo $lang_ids[24]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Historial de Pedidos <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[25]" value="<?php echo $lang_ids[25]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Detalles del Pedido <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[26]" value="<?php echo $lang_ids[26]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Fecha y Hora del Pago <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[27]" value="<?php echo $lang_ids[27]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">ID de la Transacción <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[28]" value="<?php echo $lang_ids[28]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Monto Pagado <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[29]" value="<?php echo $lang_ids[29]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Estado del Pago <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[30]" value="<?php echo $lang_ids[30]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Método de Pago <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[31]" value="<?php echo $lang_ids[31]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">ID de Pago <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[32]" value="<?php echo $lang_ids[32]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Sección de Pago <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[33]" value="<?php echo $lang_ids[33]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Seleccionar Método de Pago <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[34]" value="<?php echo $lang_ids[34]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Seleccionar un Método <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[35]" value="<?php echo $lang_ids[35]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">PayPal <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[36]" value="<?php echo $lang_ids[36]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Stripe <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[37]" value="<?php echo $lang_ids[37]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Depósito Bancario <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[38]" value="<?php echo $lang_ids[38]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Número de Tarjeta <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[39]" value="<?php echo $lang_ids[39]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">CVV <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[40]" value="<?php echo $lang_ids[40]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Mes <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[41]" value="<?php echo $lang_ids[41]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Año <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[42]" value="<?php echo $lang_ids[42]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Enviar a estos Detalles <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[43]" value="<?php echo $lang_ids[43]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Información de la Transacción <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[44]" value="<?php echo $lang_ids[44]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Incluir el ID de la transacción y otra información correctamente <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[45]" value="<?php echo $lang_ids[45]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Pagar Ahora <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[46]" value="<?php echo $lang_ids[46]; ?>">
                            </div>
                        </div>
                    </div>
                </div>


                <h3 style="font-size:20px;font-weight:500;">Productos</h3>
                <div class="box box-info">
                    <div class="box-body">

                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Nombre del Producto <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[47]" value="<?php echo $lang_ids[47]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Detalles del Producto <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[48]" value="<?php echo $lang_ids[48]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Productos Relacionados <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[155]" value="<?php echo $lang_ids[155]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Ver todos los productos relacionados a continuación <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[156]" value="<?php echo $lang_ids[156]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Categorías <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[49]" value="<?php echo $lang_ids[49]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Categoría: <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[50]" value="<?php echo $lang_ids[50]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Todos los productos bajo <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[51]" value="<?php echo $lang_ids[51]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Selecciona tamaño <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[52]" value="<?php echo $lang_ids[52]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Tamaño <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[157]" value="<?php echo $lang_ids[157]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Selecciona color <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[53]" value="<?php echo $lang_ids[53]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Color <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[158]" value="<?php echo $lang_ids[158]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Precio <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[159]" value="<?php echo $lang_ids[159]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Precio del Producto <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[54]" value="<?php echo $lang_ids[54]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Cantidad <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[55]" value="<?php echo $lang_ids[55]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Agotado <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[56]" value="<?php echo $lang_ids[56]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Compartir Esto <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[57]" value="<?php echo $lang_ids[57]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Compartir este Producto <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[58]" value="<?php echo $lang_ids[58]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Descripción del Producto <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[59]" value="<?php echo $lang_ids[59]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Producto No Encontrado <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[153]" value="<?php echo $lang_ids[153]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Características <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[60]" value="<?php echo $lang_ids[60]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Condiciones <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[61]" value="<?php echo $lang_ids[61]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Política de Devoluciones <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[62]" value="<?php echo $lang_ids[62]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Reseñas <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[63]" value="<?php echo $lang_ids[63]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Reseña <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[64]" value="<?php echo $lang_ids[64]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Deja una Reseña <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[65]" value="<?php echo $lang_ids[65]; ?>">
                            </div>
                        </div>
                    </div>
                </div>


                <h3 style="font-size:20px;font-weight:500;">Facturación y Envíos</h3>
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Costo de Envío <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[84]" value="<?php echo $lang_ids[84]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Continuar con el Envío <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[85]" value="<?php echo $lang_ids[85]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Dirección de Facturación <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[161]" value="<?php echo $lang_ids[161]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Actualizar Dirección de Facturación <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[86]" value="<?php echo $lang_ids[86]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Dirección de Envío <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[162]" value="<?php echo $lang_ids[162]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Actualizar Dirección de Envío <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[87]" value="<?php echo $lang_ids[87]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Actualizar Información de Facturación y Envío <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[88]" value="<?php echo $lang_ids[88]; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <h3 style="font-size:20px;font-weight:500;">Panel de Control</h3>
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Panel de Control <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[89]" value="<?php echo $lang_ids[89]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Bienvenido al Panel de Control <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[90]" value="<?php echo $lang_ids[90]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Volver al Panel de Control <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[91]" value="<?php echo $lang_ids[91]; ?>">
                            </div>
                        </div>
                    </div>
                </div>



                <h3 style="font-size:20px;font-weight:500;">Suscribirse</h3>
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Suscribirse <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[92]" value="<?php echo $lang_ids[92]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Suscríbete a nuestro boletín <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[93]" value="<?php echo $lang_ids[93]; ?>">
                            </div>
                        </div>
                    </div>
                </div>


                <h3 style="font-size:20px;font-weight:500;">Dirección de Correo Electrónico</h3>
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Dirección de Correo Electrónico <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[94]" value="<?php echo $lang_ids[94]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Ingresa tu Dirección de Correo Electrónico <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[95]" value="<?php echo $lang_ids[95]; ?>">
                            </div>
                        </div>
                    </div>
                </div>


                <h3 style="font-size:20px;font-weight:500;">Contraseña</h3>
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Contraseña <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[96]" value="<?php echo $lang_ids[96]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Olvidé mi Contraseña <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[97]" value="<?php echo $lang_ids[97]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Vuelve a Escribir la Contraseña <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[98]" value="<?php echo $lang_ids[98]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Actualizar Contraseña <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[99]" value="<?php echo $lang_ids[99]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Nueva Contraseña <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[100]" value="<?php echo $lang_ids[100]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Vuelve a Escribir la Nueva Contraseña <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[101]" value="<?php echo $lang_ids[101]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Cambiar Contraseña <span>*</span></label>
                            <div class="col-sm-6">
                                <textarea name="lang_value[149]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[149]; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>


                <h3 style="font-size:20px;font-weight:500;">Cliente</h3>
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Nombre Completo <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[102]" value="<?php echo $lang_ids[102]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Nombre de la Empresa <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[103]" value="<?php echo $lang_ids[103]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Número de Teléfono <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[104]" value="<?php echo $lang_ids[104]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Dirección <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[105]" value="<?php echo $lang_ids[105]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">País <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[106]" value="<?php echo $lang_ids[106]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Ciudad <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[107]" value="<?php echo $lang_ids[107]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Estado <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[108]" value="<?php echo $lang_ids[108]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Código Postal <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[109]" value="<?php echo $lang_ids[109]; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <h3 style="font-size:20px;font-weight:500;">Otra Información</h3>
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Acerca de Nosotros <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[110]" value="<?php echo $lang_ids[110]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Entradas Destacadas <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[111]" value="<?php echo $lang_ids[111]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Entradas Populares <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[112]" value="<?php echo $lang_ids[112]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Entradas Recientes <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[113]" value="<?php echo $lang_ids[113]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Información de Contacto <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[114]" value="<?php echo $lang_ids[114]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Formulario de Contacto <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[115]" value="<?php echo $lang_ids[115]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Nuestra Oficina <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[116]" value="<?php echo $lang_ids[116]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Actualizar Perfil <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[117]" value="<?php echo $lang_ids[117]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Enviar Mensaje <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[118]" value="<?php echo $lang_ids[118]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Mensaje <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[119]" value="<?php echo $lang_ids[119]; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Encuéntranos en el Mapa <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="lang_value[120]" value="<?php echo $lang_ids[120]; ?>">
                            </div>
                        </div>
                    </div>
                </div>





                <h3 style="font-size:20px;font-weight:500;">Mensajes de Error</h3>
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">¡Felicidades! El pago ha sido exitoso. <span>*</span></label>
                            <div class="col-sm-6">
                                <textarea name="lang_value[121]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[121]; ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">La información de facturación y envío se actualizó correctamente. <span>*</span></label>
                            <div class="col-sm-6">
                                <textarea name="lang_value[122]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[122]; ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">El nombre del cliente no puede estar vacío. <span>*</span></label>
                            <div class="col-sm-6">
                                <textarea name="lang_value[123]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[123]; ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">El número de teléfono no puede estar vacío. <span>*</span></label>
                            <div class="col-sm-6">
                                <textarea name="lang_value[124]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[124]; ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">La dirección no puede estar vacía. <span>*</span></label>
                            <div class="col-sm-6">
                                <textarea name="lang_value[125]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[125]; ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Debes seleccionar un país. <span>*</span></label>
                            <div class="col-sm-6">
                                <textarea name="lang_value[126]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[126]; ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">La ciudad no puede estar vacía. <span>*</span></label>
                            <div class="col-sm-6">
                                <textarea name="lang_value[127]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[127]; ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">El estado no puede estar vacío. <span>*</span></label>
                            <div class="col-sm-6">
                                <textarea name="lang_value[128]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[128]; ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">El código postal no puede estar vacío. <span>*</span></label>
                            <div class="col-sm-6">
                                <textarea name="lang_value[129]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[129]; ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">La información del perfil se actualizó correctamente. <span>*</span></label>
                            <div class="col-sm-6">
                                <textarea name="lang_value[130]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[130]; ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">La dirección de correo electrónico no puede estar vacía. <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[131]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[131]; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">El correo electrónico y/o la contraseña no pueden estar vacíos. <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[132]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[132]; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">La dirección de correo electrónico no coincide. <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[133]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[133]; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">La dirección de correo electrónico debe ser válida. <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[134]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[134]; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">La dirección de correo electrónico ya existe. <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[147]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[147]; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">No encontramos tu dirección de correo electrónico en nuestro sistema. <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[135]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[135]; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">Por favor, revisa tu correo electrónico y confirma tu suscripción. <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[136]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[136]; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">Tu correo electrónico ha sido verificado exitosamente. Ahora puedes iniciar sesión en nuestro sitio web. <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[137]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[137]; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">La contraseña no puede estar vacía. <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[138]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[138]; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">Las contraseñas no coinciden. <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[139]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[139]; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">Por favor, ingresa una nueva contraseña y vuelve a escribirla. <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[140]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[140]; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">La contraseña se ha actualizado con éxito. <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[141]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[141]; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">Para restablecer tu contraseña, por favor haz clic en el siguiente enlace. <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[142]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[142]; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">SOLICITUD DE RESTABLECIMIENTO DE CONTRASEÑA - TU SITIO WEB.COM <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[143]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[143]; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">El tiempo para restablecer la contraseña (24 horas) ha expirado. Por favor, intenta nuevamente restablecer tu contraseña. <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[144]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[144]; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">Se ha enviado un enlace de confirmación a tu dirección de correo electrónico. Allí recibirás la información para restablecer tu contraseña. <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[145]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[145]; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">La contraseña se ha restablecido con éxito. Ahora puedes iniciar sesión. <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[146]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[146]; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">¡Lo siento! Tu cuenta está inactiva. Por favor, contacta con el administrador. <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[148]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[148]; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">Confirmación de correo electrónico de registro para TU SITIO WEB. <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[150]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[150]; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">¡Gracias por tu registro! Tu cuenta ha sido creada. Para activar tu cuenta, haz clic en el siguiente enlace: <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[151]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[151]; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">Tu registro está completo. Por favor, revisa tu correo electrónico para seguir el proceso y confirmar tu registro. <span>*</span></label>
                                <div class="col-sm-6">
                                    <textarea name="lang_value[152]" class="form-control" cols="30" rows="10" style="height:70px;"><?php echo $lang_ids[152]; ?></textarea>
                                </div>
                            </div>




                            <div class="box box-info">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="" class="col-sm-4 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form1">Actualizar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

            </form>

        </div>
    </div>


</section>

<?php require_once('footer.php'); ?>