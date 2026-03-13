<div class="container is-fluid mb-6">
    <h1 class="title">Compras</h1>
    <h2 class="subtitle">Lista de compras realizadas</h2>
</div>

<div class="container is-fluid pb-6">
    <?php
        /*---------- Bloque de seguridad: Solo Admin (1) y Supervisor (3) ----------*/
        if($_SESSION['rol'] != 1 && $_SESSION['rol'] != 3){
            echo '
            <div class="notification is-danger is-light has-text-centered">
                <i class="fas fa-ban fa-3x"></i><br>
                <h1 class="title">¡Acceso Denegado!</h1>
                <p>No tienes los permisos necesarios para acceder a este módulo.</p>
                <br>
                <a href="'.APP_URL.'dashboard/" class="button is-danger is-rounded">Regresar al Inicio</a>
            </div>';
            exit(); 
        }
    ?>
    <div class="columns">
        <div class="column">
            <h2 class="subtitle">Buscar compra</h2>
            <form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off" >
                <input type="hidden" name="modulo_buscador" value="buscar">
                <input type="hidden" name="modulo_url" value="purchaseList">
                <div class="field is-grouped">
                    <p class="control is-expanded">
                        <input class="input is-rounded" type="text" name="txt_buscador" placeholder="Código de compra o Proveedor" maxlength="30" autocomplete="off">
                    </p>
                    <p class="control">
                        <button class="button is-info" type="submit" >Buscar</button>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <div class="columns">
        <div class="column">
            <?php
                use app\controllers\purchaseController;
                $insCompra = new purchaseController();

                // Validación de página
                if(isset($url[1]) && $url[1] != ""){
                    $pagina_actual = $url[1];
                } else {
                    $pagina_actual = 1;
                }

                if(!isset($_SESSION['busqueda_purchaseList']) || empty($_SESSION['busqueda_purchaseList'])){
                    $busqueda = "";
                } else {
                    $busqueda = $_SESSION['busqueda_purchaseList'];
                }

                // Generar tabla de compras
                echo $insCompra->listarCompraControlador($pagina_actual, 15, $url[0], $busqueda);
            ?>
        </div>
    </div>
</div>
<?php include "./app/views/inc/print_invoice_script.php"; ?>