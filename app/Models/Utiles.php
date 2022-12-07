<?php

namespace App\Models;

//-------------------------------------------------
//
//  Funciones de Utilidad para los Modelos
//
//-------------------------------------------------

// Colocación de Comillas para WHERE
function darComillas(string $cadena)
{
    return "'" . $cadena . "'";
}

function ajustarIndice(array $arreglo, String $nuevoIndice)
{
    $size = count($arreglo);

    $nuevoArreglo = array();

    try {
        // se cambiaría el índice actual por un valor contenido en los sub-arrays
        foreach ($arreglo as $valor) {
            $registro = $valor;
            $nuevoArreglo[$registro[$nuevoIndice]] = $registro;
        }

        return $nuevoArreglo;
    } catch (\Throwable $th) {
        // el índice no existe, o el valor no es un array
        return "NO SE CONVIRTIÓ";
    }
}
