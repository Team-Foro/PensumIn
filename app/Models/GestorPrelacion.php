<?php

namespace App\Models;

use CodeIgniter\Model;

include_once("Utiles.php");

class GestorPrelacion extends Model
{
    // constantes y mensajes
    // materia/menció común
    const COMUN = "NULL";

    // si la prelación es sustitutiva (OR) o exclusiva (AND)
    const OPCIONAL = "1";
    const OBLIGATORIA = "0";

    const UNEXPECTED_SQL = "Error Inesperado en la consulta a la base de datos";
    //

    // Constructor
    public function __construct()
    {
        parent::__construct();
    }

    //----------------------------------------------------------------
    //
    //  Crear prelación (no implementado)
    //
    //----------------------------------------------------------------



    //----------------------------------------------------------------
    //
    //   Obtener prelaciones
    //
    //----------------------------------------------------------------

    // obtiene las materias que prelan a una materia dada (si existe)
    public function obtenPrelacionMateria(String $codigo_, String $carrera_, String $mencion_)
    {
        $codigo = darComillas($codigo_);
        $carrera = darComillas($carrera_);
        $mencion = darComillas($mencion_);

        // generar consulta
        $sql = "SELECT codigo_materia, codigo_prelacion, opcional
                FROM prelacion_materias
                WHERE (codigo_materia = $codigo)
                AND (codigo_carrera = $carrera OR codigo_carrera IS NULL)
                AND (codigo_mencion = $mencion OR codigo_mencion IS NULL)";

        // resultado
        $registros = [];

        try {
            $consulta = $this->db->query($sql);
            $registros = $consulta->getResultArray();
        } catch (\Throwable $th) {
            return GestorPrelacion::UNEXPECTED_SQL; // error en la consulta
        }

        // hacer que los índices principales sean los código de materia
        //return ajustarIndice($registros, 'codigo_materia');
        return $registros;
    }

    // obtiene el monto de uc que prela a una materia dada (si existe)
    public function obtenPrelacionUC(String $codigo_, String $carrera_, String $mencion_)
    {
        $codigo = darComillas($codigo_);
        $carrera = darComillas($carrera_);
        $mencion = darComillas($mencion_);

        // generar consulta
        $sql = "SELECT codigo_materia, uc_prelacion, opcional FROM prelacion_ucs WHERE (codigo_materia = $codigo) AND (codigo_carrera = $carrera OR codigo_carrera IS NULL) AND (codigo_mencion = $mencion OR codigo_mencion IS NULL)";

        // resultado
        $registros = [];

        try {
            $consulta = $this->db->query($sql);
            $registros = $consulta->getResultArray();

            // convertir a número las uc
            $size = count($registros);
            for ($i = 0; $i < $size; $i++) {

                $valor = $registros[$i];

                if (count($valor) > 0) {
                    // convertir a número las uc de prelacion
                    $uc = intval($valor['uc_prelacion']);
                    $registros[$i]['uc_prelacion'] = $uc;
                }
            }
        } catch (\Throwable $th) {
            return GestorPrelacion::UNEXPECTED_SQL; // error en la consulta
        }

        // hacer que el índice principal sea el código de materia
        //return ajustarIndice($registros, 'codigo_materia');
        return $registros;
    }

    // obtiene las materias que prelan a cada materia de una carrera y mención dadas
    public function obtenPrelacionesMateria(String $carrera_, String $mencion_)
    {
        $carrera = darComillas($carrera_);
        $mencion = darComillas($mencion_);

        // generar consulta
        $sql = "SELECT codigo_materia, codigo_prelacion, opcional FROM prelacion_materias WHERE (codigo_carrera = $carrera OR codigo_carrera IS NULL) AND (codigo_mencion = $mencion OR codigo_mencion IS NULL)";

        // resultado
        $registros = [];

        try {
            $consulta = $this->db->query($sql);
            $registros = $consulta->getResultArray();
        } catch (\Throwable $th) {
            return GestorPrelacion::UNEXPECTED_SQL; // error en la consulta
        }

        // hacer que los índices principales sean los código de materia
        //return ajustarIndice($registros, 'codigo_materia');
        return $registros;
    }

    // obtiene las UC que prelan a cada materia de una carrera y mención dadas
    public function obtenPrelacionesUC(String $carrera_, String $mencion_)
    {
        $carrera = darComillas($carrera_);
        $mencion = darComillas($mencion_);

        // generar consulta
        $sql = "SELECT codigo_materia, uc_prelacion, opcional FROM prelacion_ucs WHERE (codigo_carrera = $carrera OR codigo_carrera IS NULL) AND (codigo_mencion = $mencion OR codigo_mencion IS NULL)";

        // resultado
        $registros = [];

        try {
            $consulta = $this->db->query($sql);
            $registros = $consulta->getResultArray();

            if ($registros == null) {
                return [];
            }

            // convertir a número las uc
            $size = count($registros);
            for ($i = 0; $i < $size; $i++) {

                $valor = $registros[$i];

                if (count($valor) > 0) {
                    // convertir a número las uc de prelacion
                    $uc = intval($valor['uc_prelacion']);
                    $registros[$i]['uc_prelacion'] = $uc;
                }
            }
        } catch (\Throwable $th) {
            return GestorPrelacion::UNEXPECTED_SQL; // error en la consulta
        }

        // hacer que el índice principal sea el código de materia
        //return ajustarIndice($registros, 'codigo_materia'); //NO: se sobre-escribe
        return $registros;
    }

    // obtiene las materias que son preladas por una materia dada (si existe)
    public function obtenMateriasPreladas(String $codigo_, String $carrera_, String $mencion_)
    {
        $codigo = darComillas($codigo_);
        $carrera = darComillas($carrera_);
        $mencion = darComillas($mencion_);

        // generar consulta
        $sql = "SELECT codigo_materia, codigo_prelacion, opcional
                FROM prelacion_materias
                WHERE (codigo_prelacion = $codigo)
                AND (codigo_carrera = $carrera OR codigo_carrera IS NULL)
                AND (codigo_mencion = $mencion OR codigo_mencion IS NULL)";

        // resultado
        $registros = [];

        try {
            $consulta = $this->db->query($sql);
            $registros = $consulta->getResultArray();
        } catch (\Throwable $th) {
            return GestorPrelacion::UNEXPECTED_SQL; // error en la consulta
        }

        // hacer que los índices principales sean los código de materia
        //return ajustarIndice($registros, 'codigo_materia');
        return $registros;
    }

    //----------------------------------------------------------------
    //
    //  Modificar prelación (no implemetado)
    //
    //----------------------------------------------------------------



    //----------------------------------------------------------------
    //
    //  Eliminar prelación (no implemetado)
    //
    //----------------------------------------------------------------

}
