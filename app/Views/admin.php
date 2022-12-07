<!-- Contenido de la Vista de Administrador -->    
<h1 class="h"> Pensum In - Administrador </h1>

<div class="cabeza">
    <img src="<?php echo base_url() ?>/public/img/android-icon-192x192.png" alt="Logo">
</div>

<h2 class="H"> Usuarios </h2>

<!-- Tabla de Consulta de los Usuarios -->
<table class="table table-dark table-striped-columns">
    <tr>
        <th> Usuario </th>
        <th> Fecha de Registro </th>
        <th> Rol </th>
        <th> Nombre </th>
        <th> Apellido </th>
        <th> Correo Electrónico </th>
        <th> Género </th>
        <th> Cédula </th>
        <th> Fecha de Nacimiento </th>
        <th> Editar </th>
        <th> Eliminar </th>
    </tr>
                            
    <?php foreach ($registros as $registro): ?>
        <tr>
            <td><?php echo $registro->usuario ?></td>
            <td><?php echo $registro->fecha_registro ?></td>
            <td><?php echo $registro->rol ?></td>
            <td><?php echo $registro->nombre ?></td>
            <td><?php echo $registro->apellido ?></td>
            <td><?php echo $registro->correo ?></td>
            <td><?php echo $registro->genero ?></td>
            <td><?php echo $registro->cedula ?></td>
            <td><?php echo $registro->fecha_nacimiento ?></td>
            <td> <a href="<?php echo base_url().'/admin/editar/'.$registro->id ?>"> ✏ </a> </td>
            <td> <a href="<?php echo base_url().'/admin/eliminar/'.$registro->id ?>"> ❌ </a> </td>   
        </tr>
    <?php endforeach; ?>
</table>

<h2 class="H"> Registro de Operador </h2>

<!-- Formulario de Registro de Operadores -->
<form class="longin" method="post" action="<?php echo base_url() ?>/admin/register" >
    <h2> Datos de Usuario</h2>

    <input type="text" name="usuario" minlength="8" maxlength="20" placeholder="Usuario" required>
    <input type="password" name="clave" minlength="8" maxlength="40" placeholder="Clave" required>
    <input type="text" name="nombre" minlength="1" maxlength="40" placeholder="Nombre" required>
    <input type="text" name="apellido" minlength="1" maxlength="40" placeholder="Apellido" required>
    <input type="email" name="correo" minlength="20" maxlength="60" placeholder="Correo Electrónico" required>
    <input type="number" name="cedula" min="1" max="99999999" placeholder="Cedula" required class="cedula">

    <label> Género </label>

    <select name="genero" required>
        <option value="F" selected> Femenino </option>
        <option value="M"> Masculino </option>
    </select>

    <br><br>

    <label> Fecha de Nacimiento </label>

    <br>

    <label> Día </label>

    <select name="dia"required>
        <option value="01" selected> 1 </option>
        <option value="02"> 2 </option>
        <option value="03"> 3 </option>
        <option value="04"> 4 </option>
        <option value="05"> 5 </option>
        <option value="06"> 6 </option>
        <option value="07"> 7 </option>
        <option value="08"> 8 </option>
        <option value="09"> 9 </option>
        <option value="10"> 10 </option>
        <option value="11"> 11 </option>
        <option value="12"> 12 </option>
        <option value="13"> 13 </option>
        <option value="14"> 14 </option>
        <option value="15"> 15 </option>
        <option value="16"> 16 </option>
        <option value="17"> 17 </option>
        <option value="18"> 18 </option>
        <option value="19"> 19 </option>
        <option value="20"> 20 </option>
        <option value="21"> 21 </option>
        <option value="22"> 22 </option>
        <option value="23"> 23 </option>
        <option value="24"> 24 </option>
        <option value="25"> 25 </option>
        <option value="26"> 26 </option>
        <option value="27"> 27 </option>
        <option value="28"> 28 </option>
        <option value="29"> 29 </option>
        <option value="30"> 30 </option>
        <option value="31"> 31 </option>
	</select>

    <label> Mes </label>

    <select name="mes" required>
        <option value="01"> Enero </option>
        <option value="02"> Febrero </option>
        <option value="03"> Marzo </option>
        <option value="04"> Abril </option>
        <option value="05"> Mayo </option>
        <option value="06"> Junio </option>
        <option value="07"> Julio </option>
        <option value="08"> Agosto </option>
        <option value="09"> Septiembre </option>
        <option value="10"> Octubre </option>
        <option value="11"> Noviembre </option>
        <option value="12"> Diciembre </option>
    </select>

    <label> Año </label>

    <input type="number" name="año" min="1980" max="2022" required>

    <input type="hidden" name="rol" value="2">

    <br><br>

    <button class="boton-login"> Crear Operador </button>
</form>
