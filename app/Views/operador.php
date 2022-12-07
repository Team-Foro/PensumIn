<!-- Contenido de la Vista de Operador -->
<h1 class="H"> Pensum In - Operador </h1>

<div class="cabeza">
    <img src="<?php echo base_url() ?>/public/img/android-icon-192x192.png" alt="Logo">
</div>

<h2 class="H"> Usuarios </h2>

<!-- Tabla de Consulta de los Usuarios (Excepto Usuarios con Rol de Administrador) -->
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
            <td> <a href="<?php echo base_url().'/operador/editar/'.$registro->id ?>"> ✏ </a> </td>
            <td> <a href="<?php echo base_url().'/operador/eliminar/'.$registro->id ?>"> ❌ </a> </td>   
        </tr>
    <?php endforeach; ?>
</table>
