<?php
    require '../../includes/funciones.php';
    $auth = estaAutenticado();


    if(!$auth) {
        header('Location: /');
    }
    
    
    //BASE DE DATOS
    require "../../includes/config/database.php";

    $DB = conectarDB();

    //CONSULTAR PARA OBTENER LOS VENDEDORES
    $consulta = "SELECT * FROM vendedores";
    /*Primer parámetro la conexión y el segundo parámetro la variable de consulta*/
    $resultados = mysqli_query($DB, $consulta);
    
    //Arreglo con mensajes de errores
    $errores = [];

    $titulo = "";
    $precio = "";
    $descripcion = "";
    $habitaciones = "";
    $wc = "";
    $estacionamiento = "";
    $vendedorId = "";
    $creado = date('Y/m/d');

    //Ejecutar el código después de que el usuario envía el formulario
    if($_SERVER["REQUEST_METHOD"] === "POST") {
        
        //Para revisar que se manden correctamente los datos
        // echo "<pre>";
        // var_dump($_POST);
        // echo "</pre>";
        //Usamos SUPERGLOBAL FILES para leer imágenes
        // echo "<pre>";
        // var_dump($_FILES);
        // echo "</pre>";


        $titulo = mysqli_real_escape_string( $DB, $_POST["titulo"] );
        $precio = mysqli_real_escape_string( $DB, $_POST["precio"] );
        $descripcion = mysqli_real_escape_string( $DB, $_POST["descripcion"] );
        $habitaciones = mysqli_real_escape_string( $DB, $_POST["habitaciones"] );
        $wc = mysqli_real_escape_string( $DB, $_POST["wc"] );
        $estacionamiento = mysqli_real_escape_string( $DB, $_POST["estacionamiento"] );
        $vendedorId = mysqli_real_escape_string( $DB, $_POST["vendedor"] );


        //Asignar files hacia una variable
        $imagen = $_FILES['imagen']; //Accedemos con el name del file, en este caso imagen



        if(!$titulo){
            $errores[] = "Debes añadir un título";
        }
        
        if(!$precio){
            $errores[] = "Debes añadir un precio";
        }
        
        if(strlen( $descripcion ) < 50){
            $errores[] = "La descripción es obligatoria y debe de tener por lo menos 50 caracteres";
        }
        
        if(!$habitaciones){
            $errores[] = "El número de habitaciones es obligatorio";
        }
        
        if(!$wc){
            $errores[] = "El número de baños es obligatorio";
        }
        
        if(!$estacionamiento){
            $errores[] = "El número de lugares de estacionamiento es obligatorio";
        }
        
        if(!$vendedorId){
            $errores[] = "Elige un vendedor";
        }
        

        //validar una imagen
        if(!$imagen['name'] || $imagen['error']){
            $errores[] = "La imagen es obligatoria";
        }

        //Validar por tamaño (1 MB máximo)
        $medida = 1000 * 1000;

        if($imagen['size'] > $medida) {
            $errores[] ="La imagen es muy pesada"; 
        } 




        //Revisando que los errores se agreguen
        // echo "<pre>";
        // var_dump($errores);
        // echo "</pre>";
        // exit; //Exit evita la inserción a la base de datos


        //Revisar que el array de errores esté vacío
        if(empty($errores)){ //Si no hay errores...

            /* SUBIDA DE ARCHIVOS */
            //Crear carpeta
            $carpetaImagenes = "../../imagenes/";

            //Validar que no exista la carpeta para no crearla multiples veces cada que se ejecute el código
            if(!is_dir($carpetaImagenes)) {
                mkdir($carpetaImagenes);
            }

            //Generar un nombre único (para que no se sobreescriban las imágenes)
            //md5 NO ES SEGURO, no debe usarse para nada de seguridad
            $nombreImagen = md5( uniqid( rand(), true ) ) . ".jpg"; //md5 se uasaba antes para hashear ( no es lo mismo que encriptar )


            //Subir imagen ( las imágenes se guardan temporalmente en la memoria del servidor, para moverlas a la carpeta hacemos lo siguiente )
            move_uploaded_file($imagen['tmp_name'], $carpetaImagenes . $nombreImagen );


            //INSERTAR EN LA BASE DE DATOS
            //Esta parte podemos ponerla en table plus para ver si es correcto
            $query = " INSERT INTO propiedades (titulo, precio, imagen, descripcion, habitaciones, wc, estacionamiento, creado, vendedorId ) VALUES ('$titulo', '$precio', '$nombreImagen', '$descripcion', '$habitaciones', '$wc', '$estacionamiento', '$creado', '$vendedorId')";
            
            //si queremos ver si es correcto el query
            //echo $query;
    
            //para usar nuestra base de datos
            $resultado = mysqli_query($DB, $query);

            //Evitar registros duplicados
            if($resultado) {
                //Redireccionar a un usuario (Usamos query string, se pone después de un ?)
                header('Location: /admin?resultado=1'); //se va a agregar en el url de index el mensaje
                //Podemos poner mas query strings usando & por ejemplo: &registrado=1
            }
        }

    }

    
    incluirTemplate('header');
 ?>


    <main class="contenedor seccion">
        <h1>Crear</h1>
        <a href="/admin" class="boton boton-verde">Volver</a>

        <?php foreach($errores as $error): ?>
            <div class="alerta error">
                <?php echo $error; ?>
            </div>
        <?php endforeach; ?>

        <form class="formulario" method="POST" action="/admin/propiedades/crear.php" enctype="multipart/form-data">
            <fieldset>
                <legend>Información General</legend>

                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" placeholder="Título Propiedad" value="<?php echo $titulo; ?>">

                <label for="precio">Precio</label>
                <input type="number" id="precio" name="precio" placeholder="Precio Propiedad" value="<?php echo $precio; ?>">

                <label for="imagen">Imagen</label>
                <input type="file" id="imagen" accept="image/jpeg, image/png" name="imagen"> 
                <!-- accept nos ayuda a limitar los archivos que puede subir el usuario -->
                <!--cada navegador interpreta la interfaz de file-->

                <label for="descripcion">Descripción</label>
                <textarea  id="descripcion" name="descripcion" cols="30" rows="10"><?php echo $descripcion; ?></textarea>
            </fieldset>

            <fieldset>
                <legend for="habitaciones">Habitaciones</legend>
                <input type="number" id="habitaciones" name="habitaciones" placeholder="Ej: 3" min="1" max="9" value="<?php echo $habitaciones; ?>">

                <legend for="wc">Baños</legend>
                <input 
                type="number" 
                id="wc" 
                name="wc" 
                placeholder="Ej: 3" 
                min="1" 
                max="9" 
                value="<?php echo $wc; ?>">

                <legend for="estacionamiento">Estacionamiento</legend>
                <input type="number" id="estacionamiento" name="estacionamiento" placeholder="Ej: 3" min="1" max="9" value="<?php echo $estacionamiento; ?>">

            </fieldset>

            <fieldset>
                <legend>Vendedor</legend>
                <select name="vendedor" id="">
                    <option value="">-- Seleccione --</option>
                    
                    <?php while($row = mysqli_fetch_assoc($resultados)): ?>
                        <option <?php echo $vendedorId === $row['id'] ? 'selected' : ''; ?> value="<?php echo $row['id'];?>"><?php echo $row['nombre'] . " " . $row['apellido'] ?></option>
                        
                    <?php endwhile; ?>

                </select>
            </fieldset>

            <input type="submit" value="Crear Propiedad" class="boton boton-verde">
        </form>
    </main>


<?php
incluirTemplate('footer');
?>