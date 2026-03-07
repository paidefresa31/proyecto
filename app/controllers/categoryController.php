<?php

	namespace app\controllers;
	use app\models\mainModel;

	class categoryController extends mainModel{

		/*----------  Controlador registrar categoria  ----------*/
		public function registrarCategoriaControlador(){

			# Almacenando datos#
		    $nombre=$this->limpiarCadena($_POST['categoria_nombre']);
		    $ubicacion=$this->limpiarCadena($_POST['categoria_ubicacion']);

			# Almacenando datos de categoría padre #
			$padre_id=isset($_POST['categoria_padre_id']) ? $this->limpiarCadena($_POST['categoria_padre_id']) : "";

		    # Verificando campos obligatorios #
            if($nombre==""){
            	$alerta=["tipo"=>"simple","titulo"=>"Ocurrió un error inesperado","texto"=>"No has llenado todos los campos que son obligatorios","icono"=>"error"]; return json_encode($alerta); exit();
            }

            # Verificando integridad de los datos #
		    if($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{4,50}",$nombre)){
		    	$alerta=["tipo"=>"simple","titulo"=>"Ocurrió un error inesperado","texto"=>"El NOMBRE no coincide con el formato solicitado","icono"=>"error"]; return json_encode($alerta); exit();
		    }

		    if($ubicacion!=""){
		    	if($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{5,150}",$ubicacion)){
			    	$alerta=["tipo"=>"simple","titulo"=>"Ocurrió un error inesperado","texto"=>"La UBICACION no coincide con el formato solicitado","icono"=>"error"]; return json_encode($alerta); exit();
			    }
		    }

		    # Verificando nombre #
		    $check_nombre=$this->ejecutarConsulta("SELECT categoria_nombre FROM categoria WHERE categoria_nombre='$nombre'");
		    if($check_nombre->rowCount()>0){
		    	$alerta=["tipo"=>"simple","titulo"=>"Ocurrió un error inesperado","texto"=>"El NOMBRE ingresado ya se encuentra registrado, por favor elija otro","icono"=>"error"]; return json_encode($alerta); exit();
		    }


		    $categoria_datos_reg=[
				["campo_nombre"=>"categoria_nombre","campo_marcador"=>":Nombre","campo_valor"=>$nombre],
				["campo_nombre"=>"categoria_ubicacion","campo_marcador"=>":Ubicacion","campo_valor"=>$ubicacion]
			];

		// Si hay un padre seleccionado, lo agregamos al array de datos
		if ($padre_id != "") {
			$categoria_datos_reg[] = ["campo_nombre" => "categoria_padre_id", "campo_marcador" => ":Padre", "campo_valor" => $padre_id];
		}

			$registrar_categoria=$this->guardarDatos("categoria",$categoria_datos_reg);

			if($registrar_categoria->rowCount()==1){
                /*== AUDITORIA ==*/
                $this->guardarBitacora("Categorías", "Registro", "Se registró la categoría: ".$nombre);

				$alerta=["tipo"=>"limpiar","titulo"=>"Categoría registrada","texto"=>"La categoría ".$nombre." se registró con éxito","icono"=>"success"];
			}else{
				$alerta=["tipo"=>"simple","titulo"=>"Ocurrió un error inesperado","texto"=>"No se pudo registrar la categoría, por favor intente nuevamente","icono"=>"error"];
			}

			return json_encode($alerta);
		}


		/*----------  Controlador listar categoria (CON DISEÑO MEJORADO) ----------*/
		public function listarCategoriaControlador($pagina,$registros,$url,$busqueda){

			$pagina=$this->limpiarCadena($pagina);
			$registros=$this->limpiarCadena($registros);
			$url=$this->limpiarCadena($url);
			$url=APP_URL.$url."/";
			$busqueda=$this->limpiarCadena($busqueda);
			$tabla="";

			$pagina = (isset($pagina) && $pagina>0) ? (int) $pagina : 1;
			$inicio = ($pagina>0) ? (($pagina * $registros)-$registros) : 0;

		if (isset($busqueda) && $busqueda != "") {
			// Se agrega AND categoria_padre_id IS NULL para filtrar subcategorías en la búsqueda
			$consulta_datos = "SELECT * FROM categoria WHERE (categoria_nombre LIKE '%$busqueda%' OR categoria_ubicacion LIKE '%$busqueda%') AND categoria_padre_id IS NULL ORDER BY categoria_nombre ASC LIMIT $inicio,$registros";

			$consulta_total = "SELECT COUNT(categoria_id) FROM categoria WHERE (categoria_nombre LIKE '%$busqueda%' OR categoria_ubicacion LIKE '%$busqueda%') AND categoria_padre_id IS NULL";
		} else {
			// Se agrega WHERE categoria_padre_id IS NULL para mostrar solo categorías raíz
			$consulta_datos = "SELECT * FROM categoria WHERE categoria_padre_id IS NULL ORDER BY categoria_nombre ASC LIMIT $inicio,$registros";

			$consulta_total = "SELECT COUNT(categoria_id) FROM categoria WHERE categoria_padre_id IS NULL";
		}
			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			$total = $this->ejecutarConsulta($consulta_total);
			$total = (int) $total->fetchColumn();
			$numeroPaginas =ceil($total/$registros);

			$tabla.='
		        <div class="table-container">
		        <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
		            <thead>
		                <tr class="has-background-link-light">
		                    <th class="has-text-centered">#</th>
		                    <th class="has-text-left">Nombre de Categoría</th>
		                    <th class="has-text-left">Ubicación / Pasillo</th>
		                    <th class="has-text-centered" colspan="3">Opciones</th>
		                </tr>
		            </thead>
		            <tbody>
		    ';

		    if($total>=1 && $pagina<=$numeroPaginas){
				$contador=$inicio+1;
				$pag_inicio=$inicio+1;
				foreach($datos as $rows){

                    $ubicacion = ($rows['categoria_ubicacion'] != "") ? $rows['categoria_ubicacion'] : '<em class="has-text-grey-light">Sin ubicación registrada</em>';

				$tabla.='
					<tr>
						<td class="has-text-centered">'.$contador.'</td>
						<td class="has-text-left has-text-weight-bold">'.$rows['categoria_nombre'].'</td>
						<td class="has-text-left">'.$ubicacion.'</td>

						<td class="has-text-centered">
							<a href="'.APP_URL.'categoryUpdate/'.$rows['categoria_id'].'/" class="button is-success is-rounded is-small" title="Editar datos de categoría">
								<i class="fas fa-sync fa-fw"></i>
							</a>
						</td>
						<td class="has-text-centered">
							<form class="FormularioAjax" action="'.APP_URL.'app/ajax/categoriaAjax.php" method="POST" autocomplete="off" >
								<input type="hidden" name="modulo_categoria" value="eliminar">
								<input type="hidden" name="categoria_id" value="'.$rows['categoria_id'].'">
								<button type="submit" class="button is-danger is-rounded is-small" title="Eliminar categoría">
									<i class="far fa-trash-alt fa-fw"></i>
								</button>
							</form>
						</td>
					</tr>
				';
					$contador++;
				}
				$pag_final=$contador-1;
			}else{
				if($total>=1){
					$tabla.='<tr class="has-text-centered" ><td colspan="6"><a href="'.$url.'1/" class="button is-link is-rounded is-small mt-4 mb-4">Haga clic acá para recargar el listado</a></td></tr>';
				}else{
					$tabla.='<tr class="has-text-centered" ><td colspan="6">No hay registros en el sistema</td></tr>';
				}
			}

			$tabla.='</tbody></table></div>';

			if($total>0 && $pagina<=$numeroPaginas){
				$tabla.='<p class="has-text-right">Mostrando categorías <strong>'.$pag_inicio.'</strong> al <strong>'.$pag_final.'</strong> de un <strong>total de '.$total.'</strong></p>';
				$tabla.=$this->paginadorTablas($pagina,$numeroPaginas,$url,7);
			}

			return $tabla;
		}

		/*---------- Controlador listar subcategoría ----------*/
		public function listarSubcategoriaControlador($pagina,$registros,$url,$busqueda){

			$pagina=$this->limpiarCadena($pagina);
			$registros=$this->limpiarCadena($registros);
			$url=$this->limpiarCadena($url);
			$url=APP_URL.$url."/";
			$busqueda=$this->limpiarCadena($busqueda);
			$tabla="";

			$pagina = (isset($pagina) && $pagina>0) ? (int) $pagina : 1;
			$inicio = ($pagina>0) ? (($pagina * $registros)-$registros) : 0;

			if(isset($busqueda) && $busqueda!=""){
				// Filtramos por búsqueda y aseguramos que NO sea una categoría principal
				$consulta_datos="SELECT * FROM categoria WHERE (categoria_nombre LIKE '%$busqueda%') AND categoria_padre_id IS NOT NULL ORDER BY categoria_nombre ASC LIMIT $inicio,$registros";
				$consulta_total="SELECT COUNT(categoria_id) FROM categoria WHERE (categoria_nombre LIKE '%$busqueda%') AND categoria_padre_id IS NOT NULL";
			}else{
				// Solo traemos registros que tienen un padre (subcategorías)
				$consulta_datos="SELECT * FROM categoria WHERE categoria_padre_id IS NOT NULL ORDER BY categoria_nombre ASC LIMIT $inicio,$registros";
				$consulta_total="SELECT COUNT(categoria_id) FROM categoria WHERE categoria_padre_id IS NOT NULL";
			}

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			$total = $this->ejecutarConsulta($consulta_total);
			$total = (int) $total->fetchColumn();
			$numeroPaginas = ceil($total/$registros);

			$tabla.='
				<div class="table-container">
				<table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
					<thead>
						<tr>
							<th class="has-text-centered">#</th>
							<th class="has-text-centered">Nombre</th>
							<th class="has-text-centered">Ubicación</th>
							<th class="has-text-centered">Categoría Principal</th>
							<th class="has-text-centered" colspan="2">Opciones</th>
						</tr>
					</thead>
					<tbody>
			';

			if($total>=1 && $pagina<=$numeroPaginas){
				$contador=$inicio+1;
				$pag_inicio=$inicio+1;
				foreach($datos as $rows){
					// Consultamos el nombre del padre para mostrarlo en la tabla
					$check_padre=$this->ejecutarConsulta("SELECT categoria_nombre FROM categoria WHERE categoria_id='".$rows['categoria_padre_id']."'");
					$padre=$check_padre->fetch();

					$tabla.='
						<tr class="has-text-centered" >
							<td>'.$contador.'</td>
							<td>'.$rows['categoria_nombre'].'</td>
							<td>'.$rows['categoria_ubicacion'].'</td>
							<td><strong>'.$padre['categoria_nombre'].'</strong></td>
							<td>
								<a href="'.APP_URL.'categoryUpdate/'.$rows['categoria_id'].'/" class="button is-success is-rounded is-small">
									<i class="fas fa-sync fa-fw"></i> &nbsp; Actualizar
								</a>
							</td>
							<td>
								<form class="FormularioAjax" action="'.APP_URL.'app/ajax/categoriaAjax.php" method="POST" autocomplete="off" >
									<input type="hidden" name="modulo_categoria" value="eliminar">
									<input type="hidden" name="categoria_id" value="'.$rows['categoria_id'].'">
									<button type="submit" class="button is-danger is-rounded is-small">
										<i class="far fa-trash-alt fa-fw"></i> &nbsp; Eliminar
									</button>
								</form>
							</td>
						</tr>
					';
					$contador++;
				}
				$pag_final=$contador-1;
			}else{
				if($total>=1){
					$tabla.='<tr class="has-text-centered" ><td colspan="6"><a href="'.$url.'" class="button is-link is-rounded is-small mt-4 mb-4">Haga clic acá para recargar la lista</a></td></tr>';
				}else{
					$tabla.='<tr class="has-text-centered" ><td colspan="6">No hay registros en el sistema</td></tr>';
				}
			}

			$tabla.='</tbody></table></div>';

			if($total>0 && $pagina<=$numeroPaginas){
				$tabla.='<p class="has-text-right">Mostrando subcategorías <strong>'.$pag_inicio.'</strong> al <strong>'.$pag_final.'</strong> de un <strong>total de '.$total.'</strong></p>';
				$tabla.=$this->paginadorTablas($pagina,$numeroPaginas,$url,7);
			}

			return $tabla;
		}

		/*----------  Controlador eliminar categoria  ----------*/
		public function eliminarCategoriaControlador(){

			$id=$this->limpiarCadena($_POST['categoria_id']);

			# Verificando categoria #
		    $datos=$this->ejecutarConsulta("SELECT * FROM categoria WHERE categoria_id='$id'");
		    if($datos->rowCount()<=0){
		        $alerta=["tipo"=>"simple","titulo"=>"Ocurrió un error inesperado","texto"=>"No hemos encontrado la categoría en el sistema","icono"=>"error"]; return json_encode($alerta); exit();
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Verificando productos #
		    $check_productos=$this->ejecutarConsulta("SELECT categoria_id FROM producto WHERE categoria_id='$id' LIMIT 1");
		    if($check_productos->rowCount()>0){
		        $alerta=["tipo"=>"simple","titulo"=>"Error","texto"=>"No podemos eliminar la categoría del sistema ya que tiene productos asociados. Debe eliminar o cambiar de categoría los productos primero.","icono"=>"error"]; return json_encode($alerta); exit();
		    }

		    $eliminarCategoria=$this->eliminarRegistro("categoria","categoria_id",$id);

		    if($eliminarCategoria->rowCount()==1){
                /*== AUDITORIA ==*/
                $this->guardarBitacora("Categorías", "Eliminación", "Se eliminó la categoría: ".$datos['categoria_nombre']);

		        $alerta=["tipo"=>"recargar","titulo"=>"Categoría eliminada","texto"=>"La categoría ".$datos['categoria_nombre']." ha sido eliminada del sistema correctamente","icono"=>"success"];
		    }else{
		    	$alerta=["tipo"=>"simple","titulo"=>"Ocurrió un error inesperado","texto"=>"No hemos podido eliminar la categoría ".$datos['categoria_nombre']." del sistema, por favor intente nuevamente","icono"=>"error"];
		    }

		    return json_encode($alerta);
		}


		/*----------  Controlador actualizar categoria  ----------*/
		public function actualizarCategoriaControlador(){

			$id=$this->limpiarCadena($_POST['categoria_id']);

			# Verificando categoria #
		    $datos=$this->ejecutarConsulta("SELECT * FROM categoria WHERE categoria_id='$id'");
		    if($datos->rowCount()<=0){
		        $alerta=["tipo"=>"simple","titulo"=>"Ocurrió un error inesperado","texto"=>"No hemos encontrado la categoría en el sistema","icono"=>"error"]; return json_encode($alerta); exit();
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Almacenando datos#
		    $nombre=$this->limpiarCadena($_POST['categoria_nombre']);
		    $ubicacion=$this->limpiarCadena($_POST['categoria_ubicacion']);

		    # Verificando campos obligatorios #
            if($nombre==""){
            	$alerta=["tipo"=>"simple","titulo"=>"Ocurrió un error inesperado","texto"=>"No has llenado todos los campos que son obligatorios","icono"=>"error"]; return json_encode($alerta); exit();
            }

            # Verificando integridad de los datos #
		    if($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{4,50}",$nombre)){
		    	$alerta=["tipo"=>"simple","titulo"=>"Ocurrió un error inesperado","texto"=>"El NOMBRE no coincide con el formato solicitado","icono"=>"error"]; return json_encode($alerta); exit();
		    }

		    if($ubicacion!=""){
		    	if($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{5,150}",$ubicacion)){
			    	$alerta=["tipo"=>"simple","titulo"=>"Ocurrió un error inesperado","texto"=>"La UBICACION no coincide con el formato solicitado","icono"=>"error"]; return json_encode($alerta); exit();
			    }
		    }

		    # Verificando nombre #
		    if($datos['categoria_nombre']!=$nombre){
			    $check_nombre=$this->ejecutarConsulta("SELECT categoria_nombre FROM categoria WHERE categoria_nombre='$nombre'");
			    if($check_nombre->rowCount()>0){
			    	$alerta=["tipo"=>"simple","titulo"=>"Ocurrió un error inesperado","texto"=>"El NOMBRE ingresado ya se encuentra registrado, por favor elija otro","icono"=>"error"]; return json_encode($alerta); exit();
			    }
		    }


		    $categoria_datos_up=[
				["campo_nombre"=>"categoria_nombre","campo_marcador"=>":Nombre","campo_valor"=>$nombre],
				["campo_nombre"=>"categoria_ubicacion","campo_marcador"=>":Ubicacion","campo_valor"=>$ubicacion]
			];

			$condicion=["condicion_campo"=>"categoria_id","condicion_marcador"=>":ID","condicion_valor"=>$id];

			if($this->actualizarDatos("categoria",$categoria_datos_up,$condicion)){
                /*== AUDITORIA ==*/
                $this->guardarBitacora("Categorías", "Actualización", "Se actualizaron los datos de la categoría: ".$nombre);

				$alerta=["tipo"=>"recargar","titulo"=>"Categoría actualizada","texto"=>"Los datos de la categoría se actualizaron correctamente","icono"=>"success"];
			}else{
				$alerta=["tipo"=>"simple","titulo"=>"Ocurrió un error inesperado","texto"=>"No hemos podido actualizar los datos de la categoría, por favor intente nuevamente","icono"=>"error"];
			}

			return json_encode($alerta);
		}

	}