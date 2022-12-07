<?php

namespace App\Models;

use CodeIgniter\Model;

include_once("Utiles.php");
include_once("Validacion.php");

class GestorCarreraMencion extends Model
{
    // CONSTANTES NECESARIAS

    // Mensajes de Advertencia
    const INVALID_CARRERA = "Carrera Inválida";
    const INVALID_MENCION = "Mención Inválida";

    // Enumeradores
    const CARRERA_INGINF = "INGINF";
    const CARRERA_INGAMB = "INGAMB";
    const MENCION_REDES = "RYT";
    const MENCION_GESTION = "GDD";
    const MENCION_SEGURIDAD = "SGI";
    const MENCION_AUTOMATIZACION = "ADP";
    const MENCION_UNICA = "UNI";

    // Constructor
    public function __construct() {
        parent::__construct();
    }

    //----------------------------------------------------------------
    //
    //   Obtener datos de una Carrera y Mención
    //
    //----------------------------------------------------------------

    // obtiene los datos de la Carrera
    public function obtenDatosCarrera(String $carrera_)
    {
        $carrera = darComillas($carrera_); // esta por id o código?

        // consulta
        $sql = "SELECT id, codigo_carrera, nombre_carrera, uc_carrera
                FROM carreras
                WHERE codigo_carrera = $carrera
                LIMIT 1";

        // resultado
        $registro = null;

        try {
            $consulta = $this->db->query($sql);
            $registro = $consulta->getRowArray();
        } catch (\Throwable $th) {
            //throw $th;
        }

        // Comprobación de un Registro en la Consulta
        if ($registro == null) {
            return GestorCarreraMencion::INVALID_CARRERA;
        } else {
            return $registro;
        }
    }

    // obtiene los datos de la Mención
    public function obtenDatosMencion(String $mencion_)
    {
        $mencion = darComillas($mencion_); // esta por id o código?

        // consulta
        $sql = "SELECT id, codigo_mencion, nombre_mencion, codigo_carrera
                FROM menciones
                WHERE codigo_mencion = $mencion
                LIMIT 1";

        // resultado
        $registro = null;

        try {
            $consulta = $this->db->query($sql);
            $registro = $consulta->getRowArray();
        } catch (\Throwable $th) {
            //throw $th;
        }

        // Comprobación de un Registro en la Consulta
        if ($registro == null) {
            return GestorCarreraMencion::INVALID_CARRERA;
        } else {
            return $registro;
        }
    }

    //----------------------------------------------------------------
    //
    //   Existencia de una Carrera y Mención
    //
    //----------------------------------------------------------------

    // Comprobación de la Existencia de un Determinado codigo_carrera
    public function existeCarrera(string $codigoCarrera_)
    {
        $codigoCarrera = darComillas($codigoCarrera_);

        // Consulta para Comprobar la Existencia de un Registro con ese correo
        $sql = "SELECT codigo_carrera
                FROM carreras
                WHERE codigo_carrera = $codigoCarrera
                    LIMIT 1";

        // resultado
        $registro = null;
        
        try {
            $consulta = $this->db->query($sql);
            $registro = $consulta->getRowArray();
        } catch (\Throwable $th) {
            //throw $th;
        }

        // Comprobación de la Existencia del correo
        if ($registro == null) {
            return false;
        } else {
            return true;
        }
    }

    // Comprobación de la Existencia de un Determinado codigo_mencion
    public function existeMencion(string $codigoMencion_)
    {
        $codigoMencion = darComillas($codigoMencion_);

        // Consulta para Comprobar la Existencia de un Registro con ese correo
        $sql = "SELECT codigo_mencion
                FROM menciones
                WHERE codigo_mencion = $codigoMencion
                    LIMIT 1";

        // resultado
        $registro = null;
        
        try {
            $consulta = $this->db->query($sql);
            $registro = $consulta->getRowArray();
        } catch (\Throwable $th) {
            //throw $th;
        }

        // Comprobación de la Existencia del correo
        if ($registro == null) {
            return false;
        } else {
            return true;
        }
    }

    // Comprobación de la Existencia de un Determinado codigo_mencion para un Determinado codigo_carrera
    public function existeMencionEnCarrera(string $codigoMencion_, string $codigoCarrera_)
    {
        /*
        // Validación de los Datos Entrantes
        $valides = array();

        $valides[0] = $this->validarMencion($codigoMencion_);
        $valides[1] = $this->validarCarrera($codigoCarrera_);
        

        foreach ($valides as $val) {
            if ($val != GestorUsuario::VALID) {
                return $val;
            }
        }
        */

        // Consulta para Comprobar la Existencia de un Registro con codigo_mencion para el codigo_carrera Dada
        $sql = "SELECT codigo_mencion, codigo_carrera
                FROM menciones
                WHERE codigo_carrera = $codigoCarrera_
                    AND codigo_mencion = $codigoMencion_
                    LIMIT 1";

        // resultado
        $registro = null;
        
        try {
            $consulta = $this->db->query($sql);
            $registro = $consulta->getRowArray();
        } catch (\Throwable $th) {
            //throw $th;
        }

        // Comprobación de la Existencia del correo
        if ($registro == null) {
            return false;
        } else {
            return true;
        }
    }

    //----------------------------------------------------------------
    //
    //  Validar Datos de Usuario
    //
    //----------------------------------------------------------------

    // Validación de codigo_carrera (Enumerador) - Si es una de las opciones
    private function validarCodigoCarrera(string $codigoCarrera)
    {
        $codigosC = array(GestorCarreraMencion::CARRERA_INGINF, GestorCarreraMencion::CARRERA_INGINF);

        return validarEnumerador($codigoCarrera, $codigosC, GestorUsuario::VALID, GestorCarreraMencion::INVALID_CARRERA);
    }

    // Validación de codigo_mencion (Enumerador) - Si es una de las opciones
    private function validarCodigoMencion(string $codigoMencion)
    {
        $codigosM = array(GestorCarreraMencion::MENCION_REDES, GestorCarreraMencion::MENCION_GESTION, GestorCarreraMencion::MENCION_SEGURIDAD, GestorCarreraMencion::MENCION_AUTOMATIZACION, GestorCarreraMencion::MENCION_UNICA);

        return validarEnumerador($codigoMencion, $codigosM, GestorUsuario::VALID, GestorCarreraMencion::INVALID_MENCION);
    }
}
