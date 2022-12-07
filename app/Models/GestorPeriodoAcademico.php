<?php

namespace App\Models;

use CodeIgniter\Model;

class GestorPeriodoAcademico extends Model {

    // Constantes Necesarias
    const INVALID_FECHA = "Fecha inválida";
    const INVALID_PERIODO = "Período Académico Inválido";
    const VALID_FECHA = "Fecha Válida";

    // NOTA: Formato de la fecha YYYY-MM-DD

    // Constructor
    public function __construct() {
        parent::__construct();
    }

    // Obtención de la Fecha Actual como String
    public function obtenerFechaActualString() {
        return "2022-12-07";
    }

    // Obtención de la Fecha Actual como Arreglo
    public function obtenerFechaActualArray() {
        return ['dia' => "07", 'mes' => "12", 'año' => "2022"];
    }

    // Obtención del Período Actual
    public function obtenerPeriodoActual() {
        return "2022-1";
    }

    // NOTA: Temporalmente, datos fijos para las pruebas

    // Obtención del Año Actual
    public function obtenerAñoActual() {
        return explode("-", $this->obtenerPeriodoActual())[0];
    }

    // Obtención del Grupo Actual
    public function obtenerGrupoActual() {
        return explode("-", $this->obtenerPeriodoActual())[1];
    }
}
