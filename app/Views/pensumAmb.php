<!-- Contenido de la Vista de Estudiante (Ambiental) -->

<?php
//session("estudiante") = id del esudiante
//session("datosEstudiante") = otros datos del estudiante
//session("pensum") = el pensum funcioal usado por el controlador
//$pensum = el pensum formateado para la vista
?>

<?php
function formatoPrelacion(array $datos)
{
    $prelaciones = "";

    $prelacionMateria = $datos['prelacion_materia']; //array['XXX000']['opcional'] = "0"/"1"
    $prelacionUC = $datos['prelacion_uc'];

    if (count($prelacionMateria) > 0) {
        //prelación por materias, y tal vez por uc

        $primero = true;
        foreach ($prelacionMateria as $codigo => $prelacion) {

            if ($primero) {
                // 1º materia:
                $prelaciones = $codigo;

                $primero = false;
            } else {
                // 2º ... Nº materias:

                //if ($prelacion['opcional'] == '1') {
                if ($prelacion == '1') {
                    $prelaciones = $prelaciones . " o " . $codigo;
                } else {
                    $prelaciones = $prelaciones . " / " . $codigo;
                }
            }
        }

        //ucs
        if (count($prelacionUC) > 0) {
            //prelación con uc
            $prelaciones = $prelaciones . " / " . $prelacionUC['uc'] . " UCA";
        }
    } else if (count($prelacionUC) > 0) {
        if ($prelacionUC['opcional'] == '1') {
            // la de SSC, con semestres I-IV o mitad de UC

            $prelaciones = "SEM I-IV" . " o " . $prelacionUC['uc'] . " UCA";
        } else {
            //prelación sólo por uc
            $prelaciones = $prelacionUC['uc'] . " UCA";
        }
    } else {
        //sin prelación
        $prelaciones = "";
    }

    return $prelaciones;
}

function estiloEstado(string $estado) {
    switch ($estado) {
        case 'Aprobada':
            return 'class="table-info"';
            break;
        
        case 'Habilitada':
            return 'class="table-secondary"';
            break;

        case 'Deshabilitada':
            return 'class="table-dark"';
            break;

        default:
            return '';
            break;
    }    
}
?>

<form id="actualizar" method="post" action="<?php echo base_url('/pensum/actualizar') ?>">
    <!-- TABLA DEL PENSUM -->
    <table border="1" , bgcolor=666666 class="table table-dark table-striped">
        <!-- Cabecera de tabla (columnas: [1-6], fila: 1) -->
        <tr>
            <th>Semestre</th>
            <th>Código</th>
            <th>Materia</th>
            <th></th>
            <th>Estado</th>
            <th>UC</th>
            <th>Prelación</th>
        </tr>

        <!-- Inicio de la Iteración por Semestres -->
        <?php foreach ($pensum["semestres"] as $semestre => $tiposMaterias) : ?>

            <?php
            // determinar la cantidad de filas que abarca el renglón de semestre
            $filasSemestre = 0;
            if (count($tiposMaterias) == 2) {
                $filasSemestre += 1; //el reglón extra
            }
            foreach ($tiposMaterias as $tipo) {
                $filasSemestre += count($tipo); //sólo "comun"
            }
            $columnasTabla = 7;
            ?>

            <!-- Semestre (columna: 1, filas: [2-11] ) -->
            <tr>
                <td rowspan=<?php echo $filasSemestre ?>> <?php echo $semestre ?> </td>

                <?php if (count($tiposMaterias) == 1) : ?>
                    <!-- sólo comunes -->
                    <?php $comunes = $tiposMaterias['1']; //GestorMaterias::COMUN = "1";
                    ?>

                    <?php foreach ($comunes as $codigo => $datos) : ?>
                        <!-- datos de cada materia en este renglón -->
                        <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo $codigo; //Código 
                            ?></td>
                        <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo $datos['nombre_materia']; //Materia 
                            ?></td>
                        <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><input type="checkbox" name=<?php echo $codigo; ?> value=<?php echo $codigo; ?> <?php echo ($datos['estado_materia'] == "Deshabilitada") ? "disabled" : ""; ?>></td>
                        <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo $datos['estado_materia']; //Estado 
                            ?></td>
                        <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo $datos['uc_materia']; //UC 
                            ?></td>
                        <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo formatoPrelacion($datos); //prelaciones materias y uc
                            ?></td>
            </tr>
            <tr>

            <?php endforeach; ?>

        <?php else : ?>
            <!-- comunes, electivas comunes y electiva mención -->

            <!-- Comunes -->
            <?php $comunes = $tiposMaterias['1']; //GestorMaterias::COMUN = "1";
            ?>

            <?php foreach ($comunes as $codigo => $datos) : ?>
                <!-- datos de cada materia en este renglón -->
                <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo $codigo; //Código 
                    ?></td>
                <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo $datos['nombre_materia']; //Materia 
                    ?></td>
                <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><input type="checkbox" name=<?php echo $codigo; ?> value=<?php echo $codigo; ?> <?php echo ($datos['estado_materia'] == "Deshabilitada") ? "disabled" : ""; ?>></td>
                <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo $datos['estado_materia']; //Estado 
                    ?></td>
                <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo $datos['uc_materia']; //UC 
                    ?></td>
                <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo formatoPrelacion($datos); //prelaciones materias y uc
                    ?></td>

            </tr>
            <tr>

            <?php endforeach; ?>

            <!-- Electiva Comunes -->
            <td colspan=<?php echo $columnasTabla - 1; ?>> <strong> Electivas de Libre Elección </strong> </td>
            </tr>
            <tr>

                <?php $electivasComunes = $tiposMaterias['2']; //GestorMaterias::ELECTIVA_COMUN = "2";
                ?>

                <?php $primero = true; ?>
                <?php foreach ($electivasComunes as $codigo => $datos) : ?>
                    <!-- datos de cada materia en este renglón -->
                    <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo $codigo; //Código 
                        ?></td>
                    <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo $datos['nombre_materia']; //Materia 
                        ?></td>
                    <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php //Sin Checkbox
                        ?></td>
                    <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo $datos['estado_materia']; //Estado 
                        ?></td>
                    <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo $datos['uc_materia']; //UC
                        ?></td>
                    <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo formatoPrelacion($datos); //prelaciones materias y uc
                        ?></td>

            </tr>
            <tr>

            <?php endforeach; ?>

        <?php endif; ?>

    <?php endforeach; ?>



    <!-- Inicio de la Iteración por Electivas -->
    <?php
    $humanisticas = &$pensum["electivas"]["4"]; //GestorMaterias::ELECTIVA_HUMANISTICA = "5"
    $ambientales = &$pensum["electivas"]["5"]; //GestorMaterias::ELECTIVA_AMBIENTAL = "4"
    $electivas = [
        "ECH00X" => &$humanisticas, //GestorMaterias::ECH_CODIGO
        "ECA0XX" => &$ambientales //GestorMaterias::ECA_CODIGO
    ];
    $filasElectivas = count($humanisticas) + count($ambientales) + 2; //dos cabeceras, igual que en Semestre 6
    ?>

    <th colspan=<?php echo $columnasTabla; ?>> <strong> Electivas de Libre Elección </strong> </th>
            </tr>
            <tr>

                <!-- Semestre (columna: 1, fila: 12 ) -->
                <td rowspan=<?php echo $filasElectivas ?>> <?php echo "Semestres del 6 al 9"; ?> </td>

                <?php foreach ($pensum["semestres"]["6"]["2"] as $codigo => $datos) : ?>

                    <!-- datos generales de este renglón -->
                    <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo $codigo; //Código 
                        ?></td>
                    <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo $datos['nombre_materia']; //Materia 
                        ?></td>
                    <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php //Sin Checkbox 
                        ?></td>
                    <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php //Sin Estado 
                        ?></td>
                    <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo $datos['uc_materia']; //UC
                        ?></td>
                    <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo formatoPrelacion($datos); //prelaciones materias y uc
                        ?></td>
            </tr>
            <tr>

                <?php foreach ($electivas[$codigo] as $codigo2 => $datos2) : ?>

                    <!-- datos de cada materia en este renglón -->
                    <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo $codigo2; //Código 
                        ?></td>
                    <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo $datos2['nombre_materia']; //Materia 
                        ?></td>
                    <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><input type="checkbox" name=<?php echo $codigo2; ?> value=<?php echo $codigo2; ?> <?php echo ($datos2['estado_materia'] == "Deshabilitada") ? "disabled" : ""; ?>></td>
                    <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo $datos2['estado_materia']; //Estado 
                        ?></td>
                    <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo $datos2['uc_materia']; //UC 
                        ?></td>
                    <td <?php $estilo = estiloEstado($datos['estado_materia']); echo $estilo; ?>><?php echo formatoPrelacion($datos2); //prelaciones materias y uc
                        ?></td>

            </tr>
            <tr>
            <?php endforeach; ?>

        <?php endforeach; ?>

            </tr>

    </table>

    <!--prototipo de boton-->
    <input type="submit" class="boton-login" name="Modo" value="Aprobar" form="actualizar">
    <input type="submit" class="boton-login" name="Modo" value="Reprobar" form="actualizar">

</form>