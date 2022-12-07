<!-- Formulario de Actualización de Usuarios -->
<form class="longin" method="post" action="<?php echo base_url('operador/editar') ?>" >
    <h2> Datos del Usuario</h2>

    <input type="hidden" name="id" id="id" value="<?php echo $id ?>">
    <input type="text" name="usuario" minlength="8" maxlength="20" placeholder="Usuario" value ="<?php echo $usuario ?>" required>
    <input type="text" name="nombre" minlength="1" maxlength="40" placeholder="Nombre" value ="<?php echo $nombre ?>" required>
    <input type="text" name="apellido" minlength="1" maxlength="40" placeholder="Apellido" value ="<?php echo $apellido ?>" required>
    <input type="email" name="correo" minlength="1" maxlength="60" placeholder="Correo Electrónico" value ="<?php echo $correo ?>" required>
    <input type="number" name="cedula" min="1" max="99999999" placeholder="Cédula" value ="<?php echo $cedula ?>" required class="cedula">

    <label> Género </label>

    <select name="genero" required>
        <option value="F" <?php if ($genero == 'F') { echo "selected"; } ?>> Femenino </option>
        <option value="M" <?php if ($genero == 'M') { echo "selected"; } ?>> Masculino </option>
    </select>

    <br><br>

    <label> Fecha de Nacimiento </label>

    <br>

    <label> Día </label>

    <select name="dia"required>
        <option value="01" <?php if ($dia == '01') { echo "selected"; } ?>> 1 </option>
        <option value="02" <?php if ($dia == '02') { echo "selected"; } ?>> 2 </option>
        <option value="03" <?php if ($dia == '03') { echo "selected"; } ?>> 3 </option>
        <option value="04" <?php if ($dia == '04') { echo "selected"; } ?>> 4 </option>
        <option value="05" <?php if ($dia == '05') { echo "selected"; } ?>> 5 </option>
        <option value="06" <?php if ($dia == '06') { echo "selected"; } ?>> 6 </option>
        <option value="07" <?php if ($dia == '07') { echo "selected"; } ?>> 7 </option>
        <option value="08" <?php if ($dia == '08') { echo "selected"; } ?>> 8 </option>
        <option value="09" <?php if ($dia == '09') { echo "selected"; } ?>> 9 </option>
        <option value="10" <?php if ($dia == '10') { echo "selected"; } ?>> 10 </option>
        <option value="11" <?php if ($dia == '11') { echo "selected"; } ?>> 11 </option>
        <option value="12" <?php if ($dia == '12') { echo "selected"; } ?>> 12 </option>
        <option value="13" <?php if ($dia == '13') { echo "selected"; } ?>> 13 </option>
        <option value="14" <?php if ($dia == '14') { echo "selected"; } ?>> 14 </option>
        <option value="15" <?php if ($dia == '15') { echo "selected"; } ?>> 15 </option>
        <option value="16" <?php if ($dia == '16') { echo "selected"; } ?>> 16 </option>
        <option value="17" <?php if ($dia == '17') { echo "selected"; } ?>> 17 </option>
        <option value="18" <?php if ($dia == '18') { echo "selected"; } ?>> 18 </option>
        <option value="19" <?php if ($dia == '19') { echo "selected"; } ?>> 19 </option>
        <option value="20" <?php if ($dia == '20') { echo "selected"; } ?>> 20 </option>
        <option value="21" <?php if ($dia == '21') { echo "selected"; } ?>> 21 </option>
        <option value="22" <?php if ($dia == '22') { echo "selected"; } ?>> 22 </option>
        <option value="23" <?php if ($dia == '23') { echo "selected"; } ?>> 23 </option>
        <option value="24" <?php if ($dia == '24') { echo "selected"; } ?>> 24 </option>
        <option value="25" <?php if ($dia == '25') { echo "selected"; } ?>> 25 </option>
        <option value="26" <?php if ($dia == '26') { echo "selected"; } ?>> 26 </option>
        <option value="27" <?php if ($dia == '27') { echo "selected"; } ?>> 27 </option>
        <option value="28" <?php if ($dia == '28') { echo "selected"; } ?>> 28 </option>
        <option value="29" <?php if ($dia == '29') { echo "selected"; } ?>> 29 </option>
        <option value="30" <?php if ($dia == '30') { echo "selected"; } ?>> 30 </option>
        <option value="31" <?php if ($dia == '31') { echo "selected"; } ?>> 31 </option>
	</select>

    <label> Mes </label>

    <select name="mes" required>
        <option value="01" <?php if ($mes == '01') { echo "selected"; } ?>> Enero </option>
        <option value="02" <?php if ($mes == '02') { echo "selected"; } ?>> Febrero </option>
        <option value="03" <?php if ($mes == '03') { echo "selected"; } ?>> Marzo </option>
        <option value="04" <?php if ($mes == '04') { echo "selected"; } ?>> Abril </option>
        <option value="05" <?php if ($mes == '05') { echo "selected"; } ?>> Mayo </option>
        <option value="06" <?php if ($mes == '06') { echo "selected"; } ?>> Junio </option>
        <option value="07" <?php if ($mes == '07') { echo "selected"; } ?>> Julio </option>
        <option value="08" <?php if ($mes == '08') { echo "selected"; } ?>> Agosto </option>
        <option value="09" <?php if ($mes == '09') { echo "selected"; } ?>> Septiembre </option>
        <option value="10" <?php if ($mes == '10') { echo "selected"; } ?>> Octubre </option>
        <option value="11" <?php if ($mes == '11') { echo "selected"; } ?>> Noviembre </option>
        <option value="12" <?php if ($mes == '12') { echo "selected"; } ?>> Diciembre </option>
    </select>

    <label> Año </label>

    <input type="number" name="año" min="1980" max="2022" value ="<?php echo $año ?>"  required >

    <br><br>

    <button class="boton-login"> Actualizar Usuario </button>
</form>