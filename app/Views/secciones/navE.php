<?php
    function textoCarrera($carrera) {
        switch ($carrera) {
            case 'INGINF':
                return 'Ing. Informática';
                break;
            
            case 'INGAMB':
                return 'Ing. Ambiental';
                break;
    
            default:
                return '';
                break;
        }
    }

    function textoMencion($mencion) {
        switch ($mencion) {
            case 'UNI':
                return 'Única';
                break;
            
            case 'GDD':
                return 'Gestión de Datos';
                break;
    
            case 'RYT':
                return 'Redes y Telecomunicaciones';
                break;
            
            case 'ADP':
                return 'Automatización de Procesos';
                break;
            
            case 'SGI':
                return 'Seguridad Informática';
                break;
    
            default:
                return '';
                break;
        }
    }
?>

<!-- Barra de Navegación para los Usuarios -->
<nav class="navbar navbar-expand-lg bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo base_url().'/pensum' ?>"> <?php echo session()->get('datosUsuario')['usuario'] ?> </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
            <div class="navbar-nav">
                <a class="nav-link active disabled" aria-current="page" href="#"> <?php echo session()->get('datosUsuario')['nombre'].' '.session()->get('datosUsuario')['apellido'] ?> </a>
                <a class="nav-link active disabled" aria-current="page" href="#"> <?php $carrera = textoCarrera(session()->get('datosEstudiante')['codigo_carrera']); echo $carrera ?> </a>
                <a class="nav-link active disabled" aria-current="page" href="#"> <?php $mencion = textoMencion(session()->get('datosEstudiante')['codigo_mencion']); echo $mencion ?> </a>
                <a class="nav-link active disabled" aria-current="page" href="#"> <strong> UCA: </strong> <?php echo session()->get('datosEstudiante')['uc_acumulado'] ?> </a>
                <a class="nav-link active disabled" aria-current="page" href="#"> <strong> UCT: </strong> <?php echo session()->get('datosCarrera')['uc_carrera'] ?> </a>
                <a class="nav-link active disabled" aria-current="page" href="#"> <strong> Factor15: </strong> <?php echo session()->get('factor15')['factor15'] ?> </a>
                <a class="nav-link" href="<?php echo base_url().'/pensum/editar/'.session()->get('datosUsuario')['id'] ?>"> Editar </a>
            
                <a class="nav-link" href="<?php echo base_url() ?>/salir"> Salir </a>
            </div>
        </div>
    </div>
</nav>
