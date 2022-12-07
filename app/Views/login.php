<!-- Contenido de la Vista de Login -->
    <div class="bienvenida">
        <img class="logo-img" src="<?php echo base_url() ?>/public/img/android-icon-short.png" alt="Logo">

        <h1 class="mb-4 mt-4"> Pensum In - Login </h1>
    </div>

    <!-- Formulario de Inicio de Sesión -->
    <form method="post" action="<?php echo base_url('/login') ?>" class="longin">
        <input type="text" name="usuario" placeholder ="Usuario" required>
        <input type="password" name="clave" placeholder ="Clave" required>

        <br>

	    <button class="boton-login"> Iniciar Sesión </button>
    </form>

    <p class="mb-4 mt-4"> ¿No estás registrado? <a href="<?php echo base_url() ?>/registro"> Registrarte </a> </p>
