<!-- Contenido de la Vista de Registro -->
    <div class="bienvenida">
        <img class="logo-img" src="<?php echo base_url() ?>/public/img/android-icon-short.png" alt="Logo">

        <h1 class="mb-4 mt-4"> Pensum In - Registro de Estudiante </h1>
    </div>

    <!-- Formulario de Registro de Usuario Estudiante -->
    <form class="longin" method="post" action="<?php echo base_url('/register') ?>" >
        <h2 class="mb-5"> Datos de Usuario</h2>

        <input type="text" name="usuario" minlength="8" maxlength="20" placeholder="Usuario" required>
        <input type="password" name="clave" minlength="8" maxlength="40" placeholder="Clave" required>
        <input type="text" name="nombre" minlength="1" maxlength="40" placeholder="Nombre" required>
        <input type="text" name="apellido" minlength="1" maxlength="40" placeholder="Apellido" required>
        <input type="email" name="correo" minlength="20" maxlength="60" placeholder="Correo Electrónico" required>
        <input type="number" name="cedula" min="1" max="99999999" placeholder="Cédula" required class="cedula">

        <label> Género </label>

        <select class="form-select w-25 mx-auto" name="genero" required>
            <option value="F" selected> Femenino </option>
            <option value="M"> Masculino </option>
        </select>

        <br><br>

        <label> Fecha de Nacimiento </label>

        <br>

        <div class="mx-auto w-75 d-flex justify-content-evenly">
            <label> Día </label>

            <select class="form-select w-25" name="dia" required>
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

            <select class="form-select w-25" name="mes" required>
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
        </div>

        <h2 class="mb-5 mt-4"> Datos de Estudiante </h2>
        
        <label> Carrera </label>

        <select class="form-select w-25 mx-auto" name="codigo_carrera" required>
		    <option value="INGINF" selected> Ingeniería Informática </option>
            <option value="INGAMB"> Ingeniería Ambiental </option>
        </select>

        <br><br>
        
        <label> Mención </label>

        <select class="form-select w-25 mx-auto" name="codigo_mencion" required>
		    <option value="RYT" selected> Redes y Telecomunicaciones </option>
		    <option value="GDD"> Gestión de Datos </option>
            <option value="SGI"> Seguridad Informática </option>
            <option value="ADP"> Automatización de Procesos </option>
            <option value="UNI"> Única </option>
        </select>

        <br><br>

        <div class="mx-auto w-75 d-flex justify-content-evenly">
            <label> Semestre </label>

            <input type="number" name="semestre" min="0" max="20" required>

            <label> Año de la Cohorte </label>

            <input type="number" name="año_cohorte" min="2000" max="3000" required>

            <label> Grupo de la Cohorte </label>

            <select class="form-select w-25" name="grupo_cohorte">
                <option value="1" selected> I </option>
                <option value="2"> II </option>
            </select>
        </div>

        <input type="hidden" name="rol" value="3">

        <br><br>

	    <button class="boton-login"> Crear Estudiante </button>
    </form>

    <p class="mb-4 mt-4"> ¿Ya tienes usuario? <a href="<?php echo base_url() ?>/ingreso"> Inicia sesión </a> </p>
