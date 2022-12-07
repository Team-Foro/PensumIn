<?php

namespace App\Models;

use CodeIgniter\Model;

include_once("Utiles.php");

class GestorMaterias extends Model
{
    // constantes y mensajes
    // enumerador de los tipos de materia
    const COMUN = "1";
    const ELECTIVA_COMUN = "2";
    const ELECTIVA_MENCION = "3";
    const ELECTIVA_AMBIENTAL = "5";
    const ELECTIVA_HUMANISTICA = "4";

    const ECH_CODIGO = "ECH00X";
    const ECH_NOMBRE = "Electivas de Ciencias Humanísticas";
    const ECH_CANTIDAD = 1;
    const ECA_CODIGO = "ECA0XX";
    const ECA_NOMBRE = "Electivas de Ciencias Ambientales";
    const ECA_CANTIDAD = 3;

    const SSC_INF_CODIGO = "SSC502";

    const UNEXPECTED_SQL = "Error Inesperado en la consulta a la base de datos";
    //

    // Constructor
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Tablas:
     * materias:
     *      id                  (indice)
     *      codigo_materia      (cod mat)
     *      nombre_materia      (varchar)
     *      semestre_materia    (cod sem)
     *      uc_materia          (num)
     *      electiva            (1,2,3,4,5)
     * materias_carreras:
     *      id
     *      codigo_materia
     *      codigo_carrera
     * materia_menciones:
     *      id
     *      codigo_materia
     *      codigo_carrera
     *      codigo_mencion
     */

    //----------------------------------------------------------------
    //
    //  Crear (no implementado)
    //
    //----------------------------------------------------------------



    //----------------------------------------------------------------
    //
    //   Obtener 
    //
    //----------------------------------------------------------------

    // obtiene los datos asociados a una materia dada (!)(quitar los AS ...)
    public function obtenDatosMateria(String $codigo_)
    {
        $codigo = darComillas($codigo_);

        // generar consulta
        $sql = "SELECT id, codigo_materia, nombre_materia, semestre_materia, uc_materia, electiva FROM materias WHERE (codigo_materia = $codigo)";

        // resultado
        $registros = [];

        try {
            $consulta = $this->db->query($sql);
            $registros = $consulta->getResultArray();
        } catch (\Throwable $th) {
            return GestorPrelacion::UNEXPECTED_SQL; // error en la consulta
        }

        // hacer que los índices principales sean los código de materia
        return ajustarIndice($registros, 'codigo_materia');
    }

    // obtiene las materias electivas comunes de un semestre, carrera (?)(mencion no se usa en INF)
    public function obtenElectivasComunes(String $semestre_, String $carrera_)
    {
        $semestre = darComillas($semestre_);
        $electiva = darComillas(GestorMaterias::ELECTIVA_COMUN);

        // consulta todas las electivas del semestre (no sólo de la carrera)
        $sql = "SELECT codigo_materia, nombre_materia, uc_materia FROM materias WHERE (semestre_materia = $semestre) AND (electiva = $electiva)";

        // resultado general
        $registros = [];
        $afectados = 0;

        try {
            $consulta = $this->db->query($sql);
            $registros = $consulta->getResultArray();
            $afectados = $this->db->affectedRows();
        } catch (\Throwable $th) {
            return GestorPrelacion::UNEXPECTED_SQL; // error en la consulta
        }

        // ahora se debe recorrer cada materia y descartar las que no sean de la carrera
        if ($afectados > 0) {
            // hay registros de materias electivas
            try {
                $regArreglado = $this->existenEnCarrera($registros, $carrera_);
                return $regArreglado;
            } catch (\Throwable $th) {
                return "ERROR DE PROGRAMACIÓN: GestorMaterias::existeEnCarrera";
            }
        }

        // no existen electivas de este semestre + carrera
        return []; // el if(afectados) parece ser innecesario si la función consiente vacíos
    }

    // obtiene las materias electivas humanísticas de un semestre, carrera (?)(mencion no se usa en INF)
    public function obtenElectivasHumanistica(String $carrera_)
    {
        $electiva = darComillas(GestorMaterias::ELECTIVA_HUMANISTICA);

        // consulta todas las electivas (no sólo de la carrera)
        $sql = "SELECT codigo_materia, nombre_materia, uc_materia FROM materias WHERE (electiva = $electiva)";

        // resultado general
        $registros = [];
        $afectados = 0;

        try {
            $consulta = $this->db->query($sql);
            $registros = $consulta->getResultArray();
            $afectados = $this->db->affectedRows();
        } catch (\Throwable $th) {
            return GestorPrelacion::UNEXPECTED_SQL; // error en la consulta
        }

        // ahora se debe recorrer cada materia y descartar las que no sean de la carrera
        if ($afectados > 0) {
            // hay registros de materias electivas
            try {
                $regArreglado = $this->existenEnCarrera($registros, $carrera_);
                return $regArreglado;
            } catch (\Throwable $th) {
                return "ERROR DE PROGRAMACIÓN: GestorMaterias::existeEnCarrera";
            }
        }

        // no existen electivas de este semestre + carrera
        return []; // el if(afectados) parece ser innecesario si la función consiente vacíos
    }

    // obtiene las materias electivas ambientales de un semestre, carrera (?)(mencion no se usa en INF)
    public function obtenElectivasAmbientales(String $carrera_)
    {
        $electiva = darComillas(GestorMaterias::ELECTIVA_AMBIENTAL);

        // consulta todas las electivas (no sólo de la carrera)
        $sql = "SELECT codigo_materia, nombre_materia, uc_materia FROM materias WHERE (electiva = $electiva)";

        // resultado general
        $registros = [];
        $afectados = 0;

        try {
            $consulta = $this->db->query($sql);
            $registros = $consulta->getResultArray();
            $afectados = $this->db->affectedRows();
        } catch (\Throwable $th) {
            return GestorPrelacion::UNEXPECTED_SQL; // error en la consulta
        }

        // ahora se debe recorrer cada materia y descartar las que no sean de la carrera
        if ($afectados > 0) {
            // hay registros de materias electivas
            try {
                $regArreglado = $this->existenEnCarrera($registros, $carrera_);
                return $regArreglado;
            } catch (\Throwable $th) {
                return "ERROR DE PROGRAMACIÓN: GestorMaterias::existeEnCarrera";
            }
        }

        // no existen electivas de este semestre + carrera
        return []; // el if(afectados) parece ser innecesario si la función consiente vacíos
    }

    // obtiene las materias de un semestre carrera (?)(mencion no se usa en INF)
    public function obtenMateriasSemestre(String $semestre_, String $carrera_)
    {
        $semestre = darComillas($semestre_);

        // consulta todas las electivas del semestre (no sólo de la carrera)
        $sql = "SELECT codigo_materia, nombre_materia, uc_materia FROM materias WHERE (semestre_materia = $semestre)";

        // resultado general
        $registros = [];
        $afectados = 0;

        try {
            $consulta = $this->db->query($sql);
            $registros = $consulta->getResultArray();
            $afectados = $this->db->affectedRows();
        } catch (\Throwable $th) {
            return GestorPrelacion::UNEXPECTED_SQL; // error en la consulta
        }

        // ahora se debe recorrer cada materia y descartar las que no sean de la carrera
        if ($afectados > 0) {
            // hay registros de materias electivas
            try {
                $regArreglado = $this->existenEnCarrera($registros, $carrera_);
                return $regArreglado;
            } catch (\Throwable $th) {
                return "ERROR DE PROGRAMACIÓN: GestorMaterias::existeEnCarrera";
            }
        }

        // no existen electivas de este semestre + carrera
        return []; // el if(afectados) parece ser innecesario si la función consiente vacíos
    }

    // devuelve los datos de materia para las de el array que esté en la carrera (!)(sustituíble con el INER JOIN)
    private function existenEnCarrera(array $materias, String $carrera_)
    {
        $carrera = darComillas($carrera_);

        $enCarrera = array();

        // se comprueba cada uno en la tabla 'materias_carreras'
        foreach ($materias as $materia) {
            $codigo = darComillas($materia['codigo_materia']);

            // consulta el código actual con las materias de esta carrera
            $sql = "SELECT id, codigo_materia FROM materias_carreras WHERE (codigo_materia = $codigo) AND (codigo_carrera = $carrera)";

            // resultado en la carrera
            $afectados = 0;

            try {
                $consulta = $this->db->query($sql);
                $consulta->getResultArray();
                $afectados = $this->db->affectedRows();
            } catch (\Throwable $th) {
                return GestorPrelacion::UNEXPECTED_SQL; // error en la consulta
            }

            if ($afectados > 0) {
                // la materia existe en esta carrera
                $enCarrera[$materia['codigo_materia']] = $materia;
            }
        }

        // un array que contiene sólo las materias en la carrera (o vacío)
        return $enCarrera;
    }

    // Obtención de las Materias de una Determinada Carrera
    public function obtenMateriasCarrera(string $carrera_)
    {
        $carrera = darComillas($carrera_);

        // Consulta para Obtener las Materias de una Carrera
        $sql = "SELECT M.id, M.codigo_materia, M.nombre_materia, M.semestre_materia, M.uc_materia, M.electiva
            FROM materias AS M INNER JOIN materias_carreras AS MC
                ON M.codigo_materia = MC.codigo_materia
            WHERE MC.codigo_carrera = $carrera ORDER BY M.id ASC";

        // resultado
        $registros = [];

        try {
            $consulta = $this->db->query($sql);
            $registros = $consulta->getResultArray();
        } catch (\Throwable $th) {
            return GestorPrelacion::UNEXPECTED_SQL; // error en la consulta
        }

        // hacer que los índices principales sean los código de materia
        return ajustarIndice($registros, 'codigo_materia');
        //return ajustarIndice($registros, 'id');
    }

    // Obtención de las Materias de una Determinada Mención
    public function obtenMateriasMencion(string $mencion_)
    {
        $mencion = darComillas($mencion_);

        // Consulta para Obtener las Materias de una Mención
        $sql = "SELECT M.id, M.codigo_materia, M.nombre_materia, M.semestre_materia, M.uc_materia, M.electiva
                FROM materias AS M INNER JOIN materias_menciones AS MM
	                ON M.codigo_materia = MM.codigo_materia
                WHERE MM.codigo_mencion = $mencion ORDER BY M.id ASC";

        // resultado
        $registros = [];

        try {
            $consulta = $this->db->query($sql);
            $registros = $consulta->getResultArray();
        } catch (\Throwable $th) {
            return GestorPrelacion::UNEXPECTED_SQL; // error en la consulta
        }

        // hacer que los índices principales sean los código de materia
        return ajustarIndice($registros, 'codigo_materia');
        //return ajustarIndice($registros, 'id');
    }

    // Obteción de las Materias para una Determinada Carrera y Mención (Pensum General)
    // sinonimo de: public function obtenMateriasCarreraMencion(string $carrera_, string $mencion_)
    public function obtenMateriasPensum(string $carrera_, string $mencion_)
    {
        // Llamado de las Funciones que Obtendrán las Materias
        $materiasCarrera = $this->obtenMateriasCarrera($carrera_);
        $materiasMencion = $this->obtenMateriasMencion($mencion_);

        if (is_array($materiasCarrera) and is_array($materiasMencion)) {
            // Unión de los Arreglos de las Materias para Obtener el Arreglo con el Pensum Completo
            $pensum = array_merge($materiasCarrera, $materiasMencion);

            /*
            // CONDICIONAL: Ordenamiento del Areglo con el Pensum Completo (???)
            if ($carrera_ == 'INGINF') {
                sort($pensum);
            }
            */

            //para hacer "sort" por ID, se debe ajustar el índice a ID primero
            // y luego se haría un:
            //return ajustarIndice($pensum, 'codigo_materia');

            return $pensum;
        } else {
            // se podría buscar el tipo de error aquí, pero por ahora así está bien

            return GestorMaterias::UNEXPECTED_SQL; //o de índice, pero principalmente SQL
        }
    }

    //----------------------------------------------------------------
    //
    //  Modificar
    //
    //----------------------------------------------------------------



    //----------------------------------------------------------------
    //
    //  Eliminar
    //
    //----------------------------------------------------------------



    //----------------------------------------------------------------
    //
    //  Validar
    //
    //----------------------------------------------------------------

}
