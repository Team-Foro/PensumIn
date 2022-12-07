<?php

namespace App\Models;

//-------------------------------------------------
//
//  Validar de Tipos de Datos
//
//-------------------------------------------------

// Validación de [ día, mes, año] y Dar el Formato utilizado en el Sistema y la BD (YYYY-MM-DD)
function formatearFecha(string $dia_, string $mes_, string $año_) {
    $dia = ajustarCadena($dia_, 2);
    $mes = ajustarCadena($mes_, 2);
    $año = ajustarCadena($año_, 4);

    if (($dia == GestorPeriodoAcademico::INVALID_FECHA) || ($mes == GestorPeriodoAcademico::INVALID_FECHA) || ($año == GestorPeriodoAcademico::INVALID_FECHA)) {
        return GestorPeriodoAcademico::INVALID_FECHA;
    }

    // Comprobación del Día con el Mes
    if (comprobarDia($dia, $mes)) {
        return $año . "-" . $mes . "-" . $dia;
    } else {
        return GestorPeriodoAcademico::INVALID_FECHA;
    }
}

// Dar la Longitud Adecuada a la Cadena - Si excede, retorna inválido
function ajustarCadena(string $cadena, int $caracteres) {
    $n = strlen($cadena);

    // Tamaño de la Cadena Igual al Número de Caracteres
    if ($n == $caracteres) {
        return $cadena;

    // Tamaño de la Cadena Menor al Número de Caracteres 
    } elseif ($n < $caracteres) {
        return str_repeat("0", $caracteres - $n) . $cadena;

    // Tamaño de la Cadena Mayor al Número de Caracteres 
    } else {
        return GestorPeriodoAcademico::INVALID_FECHA;
    }
}

// Comprobación del Valor de Día Corresponde con el Mes
function comprobarDia(string $dia, string $mes) {
    switch ($mes) {
        case '01':
        case '03':
        case '05':
        case '07':
        case '08':
        case '10':
        case '12':
            return numeroEnRango(1, 31, intval($dia));

        case '04':
        case '06':
        case '09':
        case '11':
            return numeroEnRango(1, 30, intval($dia));

        case '02':
            return numeroEnRango(1, 29, intval($dia));

        default:
            return false;
    }
}

// Método para un Rango de Números
function numeroEnRango(int $min, int $max, int $valor) {
    if ($valor >= $min || $valor <= $max) {
        return true;
    } else {
        return false;
    }
}

// Validación del Formato de la Fecha (YYYY-MM-DD) y Dar el Formato utilizado en la Vista y Cálculos [ dia, mes, año ]
function desformatearFecha(string $fechaCompleta) {
    $arrayNew = array();
    $arrayOld = explode("-", $fechaCompleta);

    // Comprobación de que el arrayOld Contiene Tres Partes
    if (count($arrayOld) == 3) {
        $arrayNew['año'] = ajustarCadena($arrayOld[0], 4);
        $arrayNew['mes'] = ajustarCadena($arrayOld[1], 2);
        $arrayNew['dia'] = ajustarCadena($arrayOld[2], 2);

        // Comprobación de una Fecha Válida en el arrayNew
        if (($arrayNew['dia'] == GestorPeriodoAcademico::INVALID_FECHA) || ($arrayNew['mes'] == GestorPeriodoAcademico::INVALID_FECHA) || ($arrayNew['año'] == GestorPeriodoAcademico::INVALID_FECHA)) {
            return GestorPeriodoAcademico::INVALID_FECHA;
        }

        // Comprobación del Día con el Mes
        if (comprobarDia($arrayNew['dia'], $arrayNew['mes'])) {
            return $arrayNew;
        } else {
            return GestorPeriodoAcademico::INVALID_FECHA;
        }
    } else {
        return GestorPeriodoAcademico::INVALID_FECHA;
    }
}

// Validación de Campo - Si tiene el tamaño correcto
function validarCampo(string $campo, int $size, string $mensajeValido, string $mensajeInvalido) {
    // Campo Vacío
    if (is_null($campo)) {
        return $mensajeInvalido;
    }

    // Campo que Excede el Tamaño
    if (strlen($campo) > $size) {
        return $mensajeInvalido;

    // Campo Válido
    } else {
        return $mensajeValido;
    }
}

// Validación de Enumerador - Si es una de las opciones
function validarEnumerador(string $campo, array $opciones, string $mensajeValido, string $mensajeInvalido) {
    // Comprobación de Enumerador Vacío
    if (is_null($campo)) {
        return $mensajeInvalido;
    }

    foreach ($opciones as $opcion) {
        if ($campo == $opcion) {
            return $mensajeValido;
        }
    }

    return $mensajeInvalido;
}

// Validación de Índice - Si tiene el tamaño correcto y no existe aún
function validarIndice(string $indice, string $size, string $mensajeValido, string $mensajeInvalido, bool $existe, string $mensajeExiste) {   // mensaje VALID o INVALID
    // Comprobación de índice Vacío
    if (is_null($indice)) {
        return $mensajeInvalido;
    }

    // Comprobación de Campo Válido
    if (validarCampo($indice, $size, $mensajeValido, $mensajeInvalido) == $mensajeValido) {
        if ($existe) {
            return $mensajeExiste;
        } else {
            return $mensajeValido;
        }
    } else {
        return $mensajeInvalido;
    }
}

// Validación de un Número dentro de un Rango Válido
function validarNumeroEntero(string $numero, int $min, int $max, string $mensajeValido, string $mensajeInvalido) {
    // Comprobación de Número Vacío
    if (is_null($numero)) {
        return $mensajeInvalido;
    }

    // Comprobación de que Todos los Caracteres son Numéricos
    $size = strlen($numero);

    for($i = 0; $i < $size; $i++){
        switch($numero[$i]) {
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
            case '9':
                break;

            default:
                return $mensajeInvalido;
        }
    }

    // Comprobación del Número dentro de un Rango Válido
    if(numeroEnRango($min, $max, intval($numero))){
        return $mensajeValido;
    } else {
        return $mensajeInvalido;
    }
}
