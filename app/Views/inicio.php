<!-- Contenido de la Vista de Inicio de la Página -->
<div class = "bienvenida">
    <img class="logo-img" src="<?php echo base_url() ?>/public/img/android-icon-short.png" alt="Logo">

    <h1> ¡Bienvenido a PensumIn! </h1>

    <!-- Descripción de la Página -->
    <div class="descripcion">
        <p> ¿No sabes cuales materias ya has aprobado? </p> 
        <p> ¿No conoces el total de unidades de créditos aprobadas que llevas acumulado? </p> 
        <p> ¿Eres estudiante de la Universidad Marítima del Caribe? </p>
        <p class="strong-p"> Pensum In  es una página que te ayuda a realizar un control de avances para las carreras de Ingeniería Informática y Ambiental, mostrando tu progreso y total de unidades de créditos acumulado. ¡Empieza a usar esta gran herramienta! </p>
    </div>
    
    <!-- Opciones del Usuario -->
    <div class="botones-de-seleccion">
        <form class="inicio">
            <!-- Inicio de Sesión -->    
            <a class="boton-enlace" href="<?php echo base_url() ?>/ingreso"> Iniciar Sesión </a>
            
            <br>

            <!-- Registro para un Nuevo Usuario -->
            <a class="boton-enlace" href="<?php echo base_url() ?>/registro"> Registrar </a>    
        </form>
    </div>
</div>
