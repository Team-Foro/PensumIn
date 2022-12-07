<!-- Contenido de la Vista de Estudiante -->
<div class="bienvenida">
    <img class="logo-img" src="<?php echo base_url() ?>/public/img/android-icon-short.png" alt="Logo">

    <h1 class="mb-2 mt-4"> Pensum In - Estudiante </h1>
</div>

<br>

<h2 class="H"> Progreso </h2>

<br>

<!-- Cálculo del Progreso-->
<?php
    $uca = (int)(((array) session("datosEstudiante"))['uc_acumulado']);
    $uct = (int)(((array) session("datosCarrera"))['uc_carrera']);
    $ucp = floor(($uca / $uct) * 100);
?>

<!-- Barra de Progreso para el Pensum-->
<div class="w-50 m-auto">
    <div class="progress" style="height: 30px;">
        <div class="progress-bar progress-bar-striped" role="progressbar" aria-label="Progreso de la Carrera" style="width: <?php echo $ucp.'%' ?>" aria-valuenow="<?php echo $ucp?>" aria-valuemin="0" aria-valuemax="100">
            <p style="margin-top: 1rem;"> <strong> <?php echo $ucp.'%' ?> </strong> </p>
        </div>
    </div>
</div>
