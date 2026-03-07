<div class="container is-fluid mb-6">
	<h1 class="title">Productos</h1>
	<h2 class="subtitle">Nuevo producto</h2>
</div>

<div class="container is-fluid pb-6">

	<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/productoAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data">

		<input type="hidden" name="modulo_producto" value="registrar">

		<div class="columns">
			<div class="column">
				<div class="control">
					<label>Código de barra <?php echo CAMPO_OBLIGATORIO; ?></label>
					<input class="input" type="text" name="producto_codigo"
						pattern="[0-9]{1,13}"
						maxlength="13"
						placeholder="Solo números (máx. 13)"
						required>
				</div>
			</div>
			<div class="column">
				<div class="control">
					<label>Nombre <?php echo CAMPO_OBLIGATORIO; ?></label>
					<input class="input" type="text" name="producto_nombre" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,$#\-\/ ]{1,70}" maxlength="70" required>
				</div>
			</div>
		</div>

		<div class="columns">
			<div class="column">
				<div class="control">
					<label>Marca</label>
					<input class="input" type="text" name="producto_marca" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,30}" maxlength="30">
				</div>
			</div>
			<div class="column">
				<div class="control">
					<label>Modelo</label>
					<input class="input" type="text" name="producto_modelo" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,30}" maxlength="30">
				</div>
			</div>
		</div>

		<div class="columns">
			<div class="column">
				<div class="control">
					<label>Costo de Compra (Neto) $</label>
					<input class="input" type="text" name="producto_costo" id="producto_costo" pattern="[0-9.]{1,25}" maxlength="25" required value="0.00">
					<p class="help is-info has-text-weight-bold" id="costo_bs_label">Bs. 0.00</p>
				</div>
			</div>
			<div class="column">
				<div class="control">
					<label>Precio de Venta (Costo + 20%) $</label>
					<input class="input" type="text" name="producto_precio" id="producto_precio" pattern="[0-9.]{1,25}" maxlength="25" required value="0.00" readonly style="background-color: #f0f0f0;">
					<p class="help is-link has-text-weight-bold" id="precio_bs_label">Bs. 0.00</p>
				</div>
			</div>
			<div class="column">
				<div class="control">
					<label>Tipo de Producto <?php echo CAMPO_OBLIGATORIO; ?></label>
					<div class="select is-fullwidth">
						<select name="producto_unidad" required>
							<option value="" selected="">Seleccione una opción</option>
							<?php
							echo $insLogin->generarSelect(PRODUCTO_UNIDAD, "VACIO");
							?>
						</select>
					</div>
				</div>
			</div>
		</div>

		<div class="columns">
			<div class="column">
				<div class="control">
					<label>Stock Inicial (Cantidad) <?php echo CAMPO_OBLIGATORIO; ?></label>
					<input class="input" type="text" name="producto_stock" pattern="[0-9]{1,25}" maxlength="25" required>
				</div>
			</div>
			<div class="column">
				<div class="control">
					<label>Stock Mínimo (Alerta) <?php echo CAMPO_OBLIGATORIO; ?></label>
					<input class="input" type="text" name="producto_stock_min" pattern="[0-9]{1,25}" maxlength="25" required value="5">
				</div>
			</div>
			<div class="column">
				<div class="control">
					<label>Stock Máximo (Límite) <?php echo CAMPO_OBLIGATORIO; ?></label>
					<input class="input" type="text" name="producto_stock_max" pattern="[0-9]{1,25}" maxlength="25" required value="100">
				</div>
			</div>
		</div>

<div class="control">
    <label>Categoría / Subcategoría</label><br>
    <div class="select is-fullwidth">
        <select name="producto_categoria" required>
            <option value="" selected="">Seleccione una opción</option>
            <?php
                use app\controllers\categoryController;
                $insCategory = new categoryController();

                // 1. Traemos TODOS los registros de la tabla
                $datos = $insCategory->seleccionarDatos("Normal", "categoria", "*", "ORDER BY categoria_nombre ASC");
                
                // Los guardamos en un array para poder usarlos varias veces sin errores
                $todas = $datos->fetchAll();

                // 2. BUSCAMOS SOLO LOS PADRES (Los que NO tienen padre asignado)
                foreach($todas as $p){
                    if($p['categoria_padre_id'] == NULL || $p['categoria_padre_id'] == "" || $p['categoria_padre_id'] == "0"){
                        
                        echo '<optgroup label="📂 '.$p['categoria_nombre'].'">';
                        
                        // 3. BUSCAMOS LOS HIJOS de este padre específico dentro del array
                        foreach($todas as $h){
                            if($h['categoria_padre_id'] == $p['categoria_id']){
                                echo '<option value="'.$h['categoria_id'].'">'.$h['categoria_nombre'].'</option>';
                            }
                        }
                        
                        echo '</optgroup>';
                    }
                }
            ?>
        </select>
    </div>
</div>
		<div class="columns">
			<div class="column">
				<label>Foto o imagen del producto</label><br>
				<div class="file is-small has-name">
					<label class="file-label">
						<input class="file-input" type="file" name="producto_foto" accept=".jpg, .png, .jpeg">
						<span class="file-cta">
							<span class="file-icon"><i class="fas fa-upload"></i></span>
							<span class="file-label">Seleccione una imagen</span>
						</span>
						<span class="file-name">JPG, JPEG, PNG. (MAX 3MB)</span>
					</label>
				</div>
			</div>
		</div>
		<p class="has-text-centered">
			<button type="reset" class="button is-link is-light is-rounded">Limpiar</button>
			<button type="submit" class="button is-info is-rounded">Guardar</button>
		</p>
	</form>

	<script>
		const inputCosto = document.getElementById('producto_costo');
		const inputPrecio = document.getElementById('producto_precio');
		const costoBsLabel = document.getElementById('costo_bs_label');
		const precioBsLabel = document.getElementById('precio_bs_label');
		let tasa_bcv = parseFloat(localStorage.getItem('tasa_bcv')) || 0;

		inputCosto.addEventListener('input', function() {
			let costo = parseFloat(this.value);
			if (!isNaN(costo)) {
				let ganancia = costo * 0.20;
				let precioFinal = costo + ganancia;
				inputPrecio.value = precioFinal.toFixed(2);

				if (tasa_bcv > 0) {
					let formatBs = new Intl.NumberFormat('es-VE', {
						minimumFractionDigits: 2,
						maximumFractionDigits: 2
					});
					costoBsLabel.innerText = "Bs. " + formatBs.format(costo * tasa_bcv);
					precioBsLabel.innerText = "Bs. " + formatBs.format(precioFinal * tasa_bcv);
				} else {
					costoBsLabel.innerText = "Sin conexión BCV";
					precioBsLabel.innerText = "Sin conexión BCV";
				}
			} else {
				inputPrecio.value = "0.00";
				costoBsLabel.innerText = "Bs. 0.00";
				precioBsLabel.innerText = "Bs. 0.00";
			}
		});
	</script>
</div>