<?php

namespace App\Models;

//-------------------------------------------------
//
//  Funciones para calculos sobre el Pensum
//
//-------------------------------------------------

/**
 * Formato de Materias Pensum:    0/1           0/1
 * { [codigo] => { [codigo?], [habilidata], [aprobada] } }
 * Formato de Prelaciones Materia:              0/1
 * { [materia] => { [materia], [prelacion], [opcional] } }
 * Formato de Prelaciones UC:      int          0/1
 * { [materia] => { [materia], [prelacion], [opcional] } }
 */

// habilita las materias en el Array indicado directamente (!)(referencia)
function habilitaMaterias(array &$pensum, String $carrera, String $mencion, int $uca, GestorPrelacion $gestorPrelacion, GestorMaterias $gestorMaterias)
{
    // recorrer todas las materias
    foreach ($pensum as $materia) {

        if ($materia['habilitada'] == "0") { // (!!!) debe llamar al gestorPensum::NO_HABILITADA
            // no está habilitada aún -> se debe evaluar

            // el código de materia, para todos los que lo usen
            $codigo = $materia['materia']; // o codigo, o codigo_materia, o como esté designado

            //------ Para casos con ELECTIVAS COMUNES ------//

            // usar un bloque encapsula las variables en este bloque
            if (true) {
                // metadatos de la materia
                $datosMateria = $gestorMaterias->obtenDatosMateria($codigo);

                if ($datosMateria['electiva'] == GestorMaterias::ELECTIVA_COMUN) {
                    // es electiva común, por lo que se consulta el estado de sus opcionales
                    $opciones = $gestorMaterias->obtenElectivasComunes($datosMateria['semestre'], $carrera);

                    // si hay al menos 1 opción
                    if (count($opciones) > 0) {

                        $aprobada = false;

                        // por cada opción comprobar
                        foreach ($opciones as $m) {

                            // si el código de la opción actual existe en el Pensum
                            if (isset($pensum[$m['codigo']])) {

                                // si dicha materia está aprobada
                                if ($pensum[$m['codigo']]['aprobada'] == "1") { // (!!!) debe llamar al gestorPensum::APROBADA

                                    // se indica que se aprobó alguna
                                    $aprobada = true;
                                }
                            }
                        }

                        // una opción existe y está Aprobada => saltarse esta vuelta del loop:
                        if ($aprobada) {
                            // No debe habilitarse; ya se aprobó una de las electivas (sólo 1 válida)
                            break;
                        }
                    }
                }
            }

            //------ Para las de tipo MATERIA ------//

            // bloque encapsulador
            if (true) {
                // prelaciones de materia
                $prelaciones = $gestorPrelacion->obtenPrelacionMateria($codigo, $carrera, $mencion);

                // contador de las materias preladoras aprobadas
                $necesarias = count($prelaciones);
                $aprobadas = 0;
                $opcionales = 0;

                // revisar si las materias preladoras están aprobadas
                try { // consulta con llave foránea
                    foreach ($prelaciones as $prelacion) {
                        // por cada prelación
                        $materiaAprob = $pensum[$prelacion['prelacion']];
                        if ($materiaAprob['aprobada'] == "1") { // (!!!) debe llamar al gestorPrelacionPensum::APROBADA
                            // la materia preladora está aprobada
                            $aprobadas++;
                        }

                        //si la prelación es opcional (OR)
                        if ($prelacion['opcional'] == gestorPrelacion::OPCIONAL) {
                            $opcionales++;
                        }
                    }
                } catch (\Throwable $th) {
                    // caso imposible
                    return "ERROR DE PROGRAMACIÓN: MATERIA PENSUM FALTANTE / PRELACION NO EXISTENTE";
                }

                // confirmar si debe habilitarse o no
                if ($opcionales == 0) {
                    // sin opcional
                    if ($aprobadas >= $necesarias) {
                        $pensum[$codigo]['habilitada'] = "1"; //habilitar en el Array original
                    }
                } else {
                    // con al menos un par de opcionales (A or B)
                    if ($aprobadas >= ($necesarias - ($opcionales - 1))) {
                        $pensum[$codigo]['habilitada'] = "1"; //habilitar en el Array original
                    }
                }
            }

            //------ Para las de tipo UC ------//

            // bloque encapsulador
            if (true) {
                $prelaciones = $gestorPrelacion->obtenPrelacionUC($codigo, $carrera, $mencion);

                $necesarias = $prelaciones[$codigo]['prelacion'];

                // revisar si las UC son suficientes
                if ($uca >= $necesarias) {
                    // obligatoria
                    $pensum[$codigo]['habilitada'] = "1"; //habilitar en el Array original
                } else if ($prelaciones[$codigo]['opcional'] == gestorPrelacion::OPCIONAL) {
                    // opcional => SSC
                    if (comprobarSemestresHastaCuarto($pensum)) {
                        //tiene aprobadas hasta 4º semestre
                        $pensum[$codigo]['habilitada'] = "1"; //habilitar en el Array original
                    }
                }
            }

            //------ Técnicamente Listo, SIN TESTEAR ------//

        }
    }
}

// determina si las materias de los semestres I-IV están aprobadas
function comprobarSemestresHastaCuarto(array &$pensum)
{
    return false; //problema para después...
}
