<!-- Barra de Navegación para los Usuarios -->
<nav class="navbar navbar-expand-lg bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo base_url().'/operador' ?>"> <?php echo session()->get('datosUsuario')['usuario'] ?> </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
            <div class="navbar-nav">
                <a class="nav-link active disabled" aria-current="page" href="#"> <?php echo session()->get('datosUsuario')['nombre'].' '.session()->get('datosUsuario')['apellido'] ?> </a>
                <a class="nav-link" href="<?php echo base_url().'/operador/editar/'.session()->get('datosUsuario')['id'] ?>"> Editar </a>
            
                <a class="nav-link" href="<?php echo base_url() ?>/salir"> Salir </a>
            </div>
        </div>
    </div>
</nav>
