<?php

namespace App\Models;

use CodeIgniter\Model;

include_once("Utiles.php");
include_once("Validacion.php");

class GestorPensum extends Model
{
    // constantes y mensajes
    const HABILITADA_SI = "1";
    const HABILITADA_NO = "0";
    const APROBADA_SI = "1";
    const APROBADA_NO = "0";

    const INVALID_CARRERA_PENSUM = "Carrera de Pensum no soportada aún";
    const INVALID_MATERIAS_PENSUM = "Materias de Pensum inválidas";
    const INVALID_FORMATO_PENSUM = "Formato de Pensum inválido";
    const INVALID_FORMATO_CARRERA = "Formato de Pensum para Carrera no reconocido";
    const INVALID_UCA_FORMATO = "Formato de UC Acumuladas no reconocido";
    const INVALID_PROPIEDAD = "Propiedad de materia inválida";

    const YET_ESTUDIANTE = "Estudiante Ya Existe con un Pensum";
    const NOT_ESTUDIANTE = "Pensum del Estudiante no Existente";

    const CORRUPTED_PENSUM_1 = "Se encontraron (";
    const CORRUPTED_PENSUM_2 = ") discrepancias en la validación del Pensum";

    const UNEXPECTED_SQL = "Error Inesperado en la consulta a la base de datos";
    const UNEXPECTED_CREACION = "Error Inesperado en la Creación del Pensum";
    const UNEXPECTED_UPDATE = "Error Inesperado en la Actualización de Pensum";
    const UNEXPECTED_DELETE = "Error Inesperado en la Eliminación de Pensum";

    const UNEXPECTED_HAB_MAT_COMUN = "Error al intentar habilitar una materia comun del Pensum";
    const UNEXPECTED_FACTOR_REC = "Error de recursibidad en el cálculo de Factor 15";

    // Mensajes Exitosos
    const VALID = "Campo Válido";
    const SUCCESS = "Acción Realizada Exitosamente";
    //

    // Constructor
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * > Crear un pensum nuevo para un estudiante y registrar (si no posee uno)
     * 
     * > Obtener el pensum de un estudiante dado
     * < Generar y devolver un pensum plantilla de una carrera o carrera+mension (no registrado)
     * 
     * > Aplicar un cambio de materias en un pesum dado (para los de tipo INF => varias menciones)
     * > Cambiar campo "habilitada" de la materia indicada por estudiante+materia (tomando en cuenta las prelaciones)
     * > Cambiar parámetro "aprobada"
     *  
     * > Eliminar de la DB el pensum de un estudiate (si posee uno)
     */

    //----------------------------------------------------------------
    //
    //  Crear
    //
    //----------------------------------------------------------------

    // genera y registra un pesum de estados para el Estudiante indicado, su carrera y su mención (!)(testear)
    public function crearPensum(String $estudiante_, String $carrera_, string $mencion_)
    {
        // inicio validación

        $valides = array();

        $valides[0] = $this->validarEstudiante($estudiante_);
        $valides[1] = $this->validarCarrera($carrera_);
        $valides[2] = $this->validarMencion($mencion_);

        foreach ($valides as $val) {
            if ($val != GestorPensum::VALID) {
                return $val;
            }
        }

        // fin validación

        // consultor de los datos de las materias
        $gestorM = new GestorMaterias();

        //obtener las materias que son de la carrera+mención
        $materiasPensum = $gestorM->obtenMateriasPensum($carrera_, $mencion_);

        //si no es válido
        if (!is_array($materiasPensum)) {
            return GestorPensum::INVALID_MATERIAS_PENSUM;
        }

        //usar un INSERT VALUES múltiple, con concatenación recursiva:
        $sql = "INSERT INTO pensums (id_estudiante, codigo_materia, habilitada, aprobada) VALUES ";

        // agregar tantos registros como hayan en el array de materias (-1)
        $primero = true;
        $apro = GestorPensum::APROBADA_NO;

        foreach ($materiasPensum as $key => $value) {
            // si debe habilitarse por ser primer semestre (luego se usa función automática)
            $hab = ($value["semestre_materia"] == "1") ? GestorPensum::HABILITADA_SI : GestorPensum::HABILITADA_NO;

            // si es el primero, no se agrega una ", " al inicio
            if ($primero) {
                // primer registro
                $sql = $sql . "('{$estudiante_}', '{$key}', '{$hab}', '{$apro}')";
                $primero = false;
            } else {
                // segudo en adelante
                $sql = $sql . ", ('{$estudiante_}', '{$key}', '{$hab}', '{$apro}')";
            }
        }

        //inserción:
        // resultado
        $afectados = 0;
        try {
            $this->db->query($sql);
            $afectados = $this->db->affectedRows();
        } catch (\Throwable $th) {
            //throw $th;
        }

        // Comprobar si se insertaron todos
        if ($afectados == count($materiasPensum)) {
            return GestorPensum::SUCCESS;
        } else {
            return GestorPensum::UNEXPECTED_CREACION;
            // (!!!) need ROLLBACK => $this->eliminarPensum($estudiante_);
        }
    }

    //----------------------------------------------------------------
    //
    //   Obtener 
    //
    //----------------------------------------------------------------

    // Obtiene los estados de un pensum ya Registrado para un Estudiante dado
    public function obtenEstadosPensum(String $estudiante_)
    {
        // formateo
        $estudiante = darComillas($estudiante_);

        // sql
        $sql = "SELECT codigo_materia, habilitada, aprobada FROM pensums WHERE id_estudiante = $estudiante";

        // resultado
        $registros = [];

        try {
            $consulta = $this->db->query($sql);
            $registros = $consulta->getResultArray();
        } catch (\Throwable $th) {
            return GestorPrelacion::UNEXPECTED_SQL; // error en la consulta
        }

        if (count($registros) == 0) {
            return GestorPensum::NOT_ESTUDIANTE;
        } else {
            // hacer que los índices principales sean los código de materia
            return ajustarIndice($registros, 'codigo_materia');
        }
    }

    // obtiene los datos y los estados de las materias de un estudiante dado (!)(UNTESTED)
    //public function obtenPensumCompleto(String $estudiante_, $carrera_, $mencion_)
    public function obtenPensumCompleto(String $usuario_)
    {
        // consultor de los datos carrera-mención del estudiante
        $gestorE = new GestorEstudiante();

        $datosEstudiante = $gestorE->obtenDatosEstudiante($usuario_);

        if ($datosEstudiante == GestorEstudiante::NOT_ESTUDIANTE) {
            return GestorPensum::NOT_ESTUDIANTE;
        }

        // Existe el estudiante

        // datos necesarios del estudiante
        $estudiante = $datosEstudiante['id'];
        $carrera = $datosEstudiante['codigo_carrera'];
        $mencion = $datosEstudiante['codigo_mencion'];

        $gestorE = null;

        // consultor de los datos de las materias
        $gestorM = new GestorMaterias();

        // datos de materia
        $datosPensum = $gestorM->obtenMateriasPensum($carrera, $mencion);
        // datos de estado
        $estadosPensum = $this->obtenEstadosPensum($estudiante);

        $gestorM = null;

        // unir los datos con los estados, con llave principal el codigo_materia
        if (is_array($estadosPensum) and is_array($datosPensum)) {
            // Unión de los Arreglos de las Materias para Obtener el Arreglo con el Pensum Completo
            //$pensum = array_merge($estadosPensum, $datosPensum);

            $pensum = array();
            foreach ($estadosPensum as $codigo => $estados) {
                $pensum[$codigo] = array_merge($estadosPensum[$codigo], $datosPensum[$codigo]);
            }

            // añadir las prelaciones de cada materia
            $resultado = $this->agregarPrelaciones($pensum, $carrera, $mencion);

            if ($resultado == GestorPensum::SUCCESS) {
                //pensum completo
                return $pensum;
            } else {
                //error al agregar las prelaciones
                return $resultado;
            }
        } else {
            // algo salió mal
            if (!is_array($estadosPensum)) {
                // error al consultar estado
                return $estadosPensum;
            } else {
                // error al consultar datos
                return $datosPensum;
            }
        }
    }

    // Validación de Existencia de un Estudiate con Pensum por su ID
    public function existeEstudiante(string $estudiante_)
    {
        $estudiante = darComillas($estudiante_);

        // Consulta para Comprobar la Existencia de un Registro con un Determinado usuario
        $sql = "SELECT id_estudiante
                FROM pensums 
                WHERE id_estudiante = $estudiante 
                LIMIT 1";

        // resultado
        $registro = null;

        try {
            $consulta = $this->db->query($sql);
            $registro = $consulta->getRowArray();
        } catch (\Throwable $th) {
            //throw $th;
        }

        // Comprobación de la Existencia del Registro con ese usuario
        if ($registro == null) {
            return false;
        } else {
            return true;
        }
    }

    // agregar para cada registro de materia las prelacioes respectivas
    private function agregarPrelaciones(array &$pensum, String $carrera, String $mencion)
    {
        // buscar las prelaciones de cada materia
        $gestorPrel = new GestorPrelacion();

        // añadir en cada registro de materias sus respectivas prelaciones
        foreach ($pensum as $codigo => $materia) {

            // datos de prelación materia
            if (true) {
                $prelMat = $gestorPrel->obtenPrelacionMateria($codigo, $carrera, $mencion);

                if (is_array($prelMat)) {
                    // consulta sin error

                    // caso de 0 materia
                    $pensum[$codigo]['prelacion_materia'] = array();

                    // caso de 1 a N materias
                    foreach ($prelMat as $pmat) {
                        // datos necesarios de la prelación
                        $codigoPrelacion = $pmat['codigo_prelacion'];
                        $opcional = $pmat['opcional'];

                        $pensum[$codigo]['prelacion_materia'][$codigoPrelacion] = $opcional;
                    }
                } else {
                    // consulta con error...

                    return $prelMat;
                }
            }

            // datos de prelación uc
            if (true) {
                $prelUC = $gestorPrel->obtenPrelacionUC($codigo, $carrera, $mencion);

                if (is_array($prelMat)) {
                    // consulta sin error

                    // caso de 0 prelación UC
                    $pensum[$codigo]['prelacion_uc'] = array();

                    // caso de 1 prelación UC
                    foreach ($prelUC as $puc) {
                        // datos necesarios de la prelación
                        $uc = $puc['uc_prelacion'];
                        $opcional = $puc['opcional'];

                        $pensum[$codigo]['prelacion_uc']['uc'] = $uc;
                        $pensum[$codigo]['prelacion_uc']['opcional'] = $opcional;
                    }
                } else {
                    // consulta con error...

                    return $prelUC;
                }
            }
        }

        return GestorPensum::SUCCESS;
    }

    //
    // formateados para las vistas
    //

    // genera y devuelve la versión formateada de un pensum según la carrera indicada
    public function generarPensumFormato(array $pensum, string $carrera)
    {
        switch ($carrera) {
            case GestorCarreraMencion::CARRERA_INGINF:
                // formatear como pensum de informática
                // pensum { semestre { tipo { codigo { datos } } } }
                return $this->generarFormatoINF($pensum);

            case GestorCarreraMencion::CARRERA_INGAMB:
                // formatear como pensum de ambietal
                // pensum["semestres"] { semestre { tipo { codigo { datos } } } }
                // pensum["electivas"] { tipo { codigo { datos } } }
                return $this->generarFormatoAMB($pensum);

            default:
                // carrera o contemplada entre estos formatos
                return GestorPensum::INVALID_FORMATO_CARRERA;
        }
    }

    // genera todo el pensum formateado para INF
    private function generarFormatoINF(array $pensum)
    {
        // formatear como pensum de informática
        // pensum { semestre { tipo { codigo { datos } } } }
        $pensumForm = $this->inicializarFormatoINF();

        try {
            foreach ($pensum as $codigo => $datos) {
                // se empieza a insertar los datos de materia desde su código
                $pensumForm[$datos['semestre_materia']][$datos['electiva']][$codigo] = array();
                // usar una referencia para facilitar el acceso
                $puntero = &$pensumForm[$datos['semestre_materia']][$datos['electiva']][$codigo];

                // agregar cada dato
                $puntero['nombre_materia'] = $datos['nombre_materia'];
                $puntero['uc_materia'] = $datos['uc_materia'];
                $puntero['prelacion_materia'] = $datos['prelacion_materia']; //array{array}
                $puntero['prelacion_uc'] = $datos['prelacion_uc']; //array

                // estado
                if ($datos['aprobada'] == GestorPensum::APROBADA_SI) {
                    $puntero['estado_materia'] = "Aprobada";
                } elseif ($datos['habilitada'] == GestorPensum::HABILITADA_SI) {
                    $puntero['estado_materia'] = "Habilitada";
                } else {
                    $puntero['estado_materia'] = "Deshabilitada";
                }
                //$puntero['habilitada'] = $datos['habilitada']; //
                //$puntero['aprobada'] = $datos['aprobada'];     //

                // el resto de datos los aprota su encapsulamiento (semestre, tipoElectiva, código)
            }

            // pensum formateado listo
            return $pensumForm;
        } catch (\Throwable $th) {
            return GestorPensum::INVALID_MATERIAS_PENSUM;
        }
    }

    // se inicia con [semestre] = array[tipo] = array[]
    private function inicializarFormatoINF()
    {
        $formato = array();

        // semestres
        for ($i = 1; $i <= 10; $i++) {
            $formato[strval($i)] = array();

            if ($i >= 5 and $i < 10) {
                //caso con tres tipos
                $formato[strval($i)][GestorMaterias::COMUN] = array();
                $formato[strval($i)][GestorMaterias::ELECTIVA_COMUN] = array();
                $formato[strval($i)][GestorMaterias::ELECTIVA_MENCION] = array();
            } else {
                //caso con un tipo
                $formato[strval($i)][GestorMaterias::COMUN] = array();
            }
        }

        return $formato;
    }

    // genera todo el pensum formateado para AMB
    private function generarFormatoAMB(array $pensum)
    {
        // formatear como pensum de ambietal
        // pensum["semestres"] { semestre { tipo { codigo { datos } } } }
        // pensum["electivas"] { tipo { codigo { datos } } }
        $pensumForm = $this->inicializarFormatoAMB();
        $semestres = &$pensumForm["semestres"];
        $electivas = &$pensumForm["electivas"];

        try {

            // PRIMERO: rellenar los Arrays de Semestres y Electivas Libres

            // conteo de aprobadas y/o habilitadas de ECH (5) y ECA (4) (para parte 2)
            $aprobadas = [
                GestorMaterias::ELECTIVA_HUMANISTICA => 0,
                GestorMaterias::ELECTIVA_AMBIENTAL => 0,
            ];
            $habilitadas = [
                GestorMaterias::ELECTIVA_HUMANISTICA => 0,
                GestorMaterias::ELECTIVA_AMBIENTAL => 0,
            ];

            // recorrer y traspasar cada materia del pensum
            foreach ($pensum as $codigo => $datos) {
                // puntero que referencia a un sector del array
                $puntero = array();

                // detectar si es una de las electivas o es común, mediante Semestre="0"
                if ($datos['semestre_materia'] == "0") {
                    // electiva de Humanística ("5") o Ambiental ("4"):

                    // se empieza a insertar los datos de materia desde su código
                    $electivas[$datos['electiva']][$codigo] = array();
                    // usar una referencia para facilitar el acceso
                    $puntero = &$electivas[$datos['electiva']][$codigo];

                    // sumar al contador de estados
                    $aprobadas[$datos['electiva']] += intval($datos['aprobada']);
                    $habilitadas[$datos['electiva']] += intval($datos['habilitada']);
                } else {
                    // materia común

                    // se empieza a insertar los datos de materia desde su código
                    $semestres[$datos['semestre_materia']][$datos['electiva']][$codigo] = array();
                    // usar una referencia para facilitar el acceso
                    $puntero = &$semestres[$datos['semestre_materia']][$datos['electiva']][$codigo];
                }

                // agregar cada dato
                $puntero['nombre_materia'] = $datos['nombre_materia'];
                $puntero['uc_materia'] = $datos['uc_materia'];
                $puntero['prelacion_materia'] = $datos['prelacion_materia']; //array{array}
                $puntero['prelacion_uc'] = $datos['prelacion_uc']; //array

                // estado
                if ($datos['aprobada'] == GestorPensum::APROBADA_SI) {
                    $puntero['estado_materia'] = "Aprobada";
                } elseif ($datos['habilitada'] == GestorPensum::HABILITADA_SI) {
                    $puntero['estado_materia'] = "Habilitada";
                } else {
                    $puntero['estado_materia'] = "Deshabilitada";
                }
                //$puntero['habilitada'] = $datos['habilitada']; //
                //$puntero['aprobada'] = $datos['aprobada'];     //

                // el resto de datos los aprota su encapsulamiento (semestre, tipoElectiva, código)

                // desligar el puntero, por si acaso
                unset($puntero);
            }

            // SEGUNDO: determinar los datos de presentación de las electivas en semestres 6, 8 y 9

            // obtener los datos actualizados de las electivas, en array para electivas H y A
            $codigos = [
                GestorMaterias::ECH_CODIGO => [
                    'nombre_materia' => GestorMaterias::ECH_NOMBRE,
                    'uc_materia' => $electivas[GestorMaterias::ELECTIVA_HUMANISTICA][array_keys($electivas[GestorMaterias::ELECTIVA_HUMANISTICA])[0]]['uc_materia'],
                    'prelacion_materia' => array(),
                    'prelacion_uc' => $electivas[GestorMaterias::ELECTIVA_HUMANISTICA][array_keys($electivas[GestorMaterias::ELECTIVA_HUMANISTICA])[0]]['prelacion_uc']
                ],
                GestorMaterias::ECA_CODIGO => [
                    'nombre_materia' => GestorMaterias::ECA_NOMBRE,
                    'uc_materia' => $electivas[GestorMaterias::ELECTIVA_AMBIENTAL][array_keys($electivas[GestorMaterias::ELECTIVA_AMBIENTAL])[0]]['uc_materia'],
                    'prelacion_materia' => array(),
                    'prelacion_uc' => $electivas[GestorMaterias::ELECTIVA_AMBIENTAL][array_keys($electivas[GestorMaterias::ELECTIVA_AMBIENTAL])[0]]['prelacion_uc']
                ]
            ];
            // indicador de la cantidad de semestre con electiva
            $n = 1;

            // ECH - Semestre 6
            if (true) {

                $datos = &$codigos[GestorMaterias::ECH_CODIGO];

                // iniciar array de la electiva de semestre
                $semestres["6"][GestorMaterias::ELECTIVA_COMUN][GestorMaterias::ECH_CODIGO] = array();
                // usar una referencia para facilitar el acceso
                $puntero = &$semestres["6"][GestorMaterias::ELECTIVA_COMUN][GestorMaterias::ECH_CODIGO];

                // agregar cada dato
                $puntero['nombre_materia'] = $datos['nombre_materia'];
                $puntero['uc_materia'] = $datos['uc_materia'];
                $puntero['prelacion_materia'] = $datos['prelacion_materia']; // []
                $puntero['prelacion_uc'] = $datos['prelacion_uc']; //array

                // estado de ECH
                if ($aprobadas[GestorMaterias::ELECTIVA_HUMANISTICA] >= $n) {
                    // ECH aprob
                    $puntero['estado_materia'] = "Aprobada";
                } elseif ($habilitadas[GestorMaterias::ELECTIVA_HUMANISTICA] >= 1 and $aprobadas[GestorMaterias::ELECTIVA_HUMANISTICA] >= ($n - 1)) {
                    // ECH hab
                    $puntero['estado_materia'] = "Habilitada";
                } else {
                    // ECH deshab
                    $puntero['estado_materia'] = "Deshabilitada";
                }

                unset($puntero);
                //$n++;
            }

            // ECA - Semestre 6, 8 y 9
            if (true) {

                $datos = &$codigos[GestorMaterias::ECA_CODIGO];
                $semestre = [
                    "6",
                    "8",
                    "9"
                ];

                foreach ($semestre as $sem) {
                    // iniciar array de la electiva de semestre
                    $semestres[$sem][GestorMaterias::ELECTIVA_COMUN][GestorMaterias::ECA_CODIGO] = array();
                    // usar una referencia para facilitar el acceso
                    $puntero = &$semestres[$sem][GestorMaterias::ELECTIVA_COMUN][GestorMaterias::ECA_CODIGO];

                    // agregar cada dato
                    $puntero['nombre_materia'] = $datos['nombre_materia'];
                    $puntero['uc_materia'] = $datos['uc_materia'];
                    $puntero['prelacion_materia'] = $datos['prelacion_materia']; // []
                    $puntero['prelacion_uc'] = $datos['prelacion_uc']; //array

                    // estado de ECA
                    if ($aprobadas[GestorMaterias::ELECTIVA_AMBIENTAL] >= $n) {
                        // ECA aprob
                        $puntero['estado_materia'] = "Aprobada";
                    } elseif ($habilitadas[GestorMaterias::ELECTIVA_AMBIENTAL] >= 1 and $aprobadas[GestorMaterias::ELECTIVA_AMBIENTAL] >= ($n - 1)) {
                        // ECA hab
                        $puntero['estado_materia'] = "Habilitada";
                    } else {
                        // ECA deshab
                        $puntero['estado_materia'] = "Deshabilitada";
                    }

                    unset($puntero);
                    $n++;
                }
            }

            // formato listo
            return $pensumForm;
        } catch (\Throwable $th) {
            return GestorPensum::INVALID_MATERIAS_PENSUM;
        }
    }

    // se inicia con [semestres] = array[semestre] = array[tipo] = array[]
    // . . . . . . . [electivas] = array[tipo] = array[]
    private function inicializarFormatoAMB()
    {
        $formato = [
            "semestres" => array(),
            "electivas" => array()
        ];

        // semestres
        $semestres = &$formato["semestres"];

        for ($i = 1; $i <= 10; $i++) {
            $semestres[strval($i)] = array();

            if ($i == 6 or $i == 8 or $i == 9) {
                //caso con tres tipos
                $semestres[strval($i)][GestorMaterias::COMUN] = array();
                $semestres[strval($i)][GestorMaterias::ELECTIVA_COMUN] = array(); //de algún tipo
            } else {
                //caso con un tipo
                $semestres[strval($i)][GestorMaterias::COMUN] = array();
            }
        }

        // electivas
        $electivas = &$formato["electivas"];

        $electivas[GestorMaterias::ELECTIVA_HUMANISTICA] = array();
        $electivas[GestorMaterias::ELECTIVA_AMBIENTAL] = array();

        return $formato;
    }

    //
    // PROGRESO
    //

    // devuelve como int la sumatoria de las UC aprobadas del estudiante dado
    public function recalcularUC(string $estudiante_)
    {
        // formato
        $estudiante = darComillas($estudiante_);
        $aprobada = darComillas(GestorPensum::APROBADA_SI);

        // sql
        $sql = "SELECT SUM(M.uc_materia) AS uc
                FROM materias AS M INNER JOIN pensums AS P
                ON M.codigo_materia = P.codigo_materia
                WHERE (P.id_estudiante = $estudiante)
                AND (P.aprobada = $aprobada)";

        // resultado
        $registro = null;

        try {
            $consulta = $this->db->query($sql);
            $registro = $consulta->getRowArray();
        } catch (\Throwable $th) {
            //throw $th;
        }

        if ($registro != null) {
            return intval($registro['uc']);
        } else {
            return GestorPensum::UNEXPECTED_UPDATE;
        }
    }

    //
    // FACTOR15
    //

    // devuelve como int la cantidad de semestres que harían falta para completar el pensum del estudiante
    public function recalcularFactor15v1(string $usuario_)
    {
        // PARTE 1: establecer los datos virtuales necesarios

        // datos del estudiante
        $gestorE = new GestorEstudiante();

        $datosEstudiante = $gestorE->obtenDatosEstudiante($usuario_);

        // validar datos estudiante
        if ($datosEstudiante == GestorEstudiante::NOT_ESTUDIANTE) {
            return GestorPensum::NOT_ESTUDIANTE;
        }

        // datos de pensum (ya se encuentra actualizado, no?)
        $pensum = $this->obtenPensumCompleto($usuario_);

        // validar pensum (completo)
        $c = $this->habilitarMateriasv1($datosEstudiante, $pensum, false); //FALSE

        if ($c != 0) {
            // hubo algún cambio o algún error

            if (is_int($c)) {
                // hubo algún cambio
                return GestorPensum::CORRUPTED_PENSUM_1 . $c . GestorPensum::CORRUPTED_PENSUM_2;
            } else {
                // otro error
                return $c;
            }
        }
        //$e = 0 / null;


        $gestorCM = new GestorCarreraMencion();
        // datos de carrera
        $datosCarrera = $gestorCM->obtenDatosCarrera($datosEstudiante['codigo_carrera']);
        // las UC Totales de la carrera del estudiante
        $uct = intval($datosCarrera['uc_carrera']);



        // PARTE 2: empezar las iteraciones del Pensum Virtual

        // se tienen los arreglos virtuales
        // $pensum; $datosEstudiante; $uct

        // contador de los cambios
        $semestres = 0;

        // se establece la condición inicial
        $uca = intval($datosEstudiante['uc_acumulado']);
        $terminado = ($uca >= $uct) ? true : false;

        //$d = 0/null;

        // Iterador 1 - por semestres (mientras no se termine la carrera)
        while (!$terminado) {

            // lista de materias habilitadas en el Pensum Virtual
            $materias = $this->obtenCoincidenciasDoble($pensum, 'habilitada', GestorPensum::HABILITADA_SI, 'aprobada', GestorPensum::APROBADA_NO);

            // Iterador 2 - por cada materia habilitada
            foreach ($materias as $codigo) {

                // condición de aprobación -> estar habilitada
                if ($pensum[$codigo]['habilitada'] == GestorPensum::HABILITADA_SI) {
                    // la materia está habilitada

                    // se cambia en el Pensum Virtual
                    $pensum[$codigo]['aprobada'] = GestorPensum::APROBADA_SI;

                    // se suman las UC a las UCA del estudiante (Virtual)
                    $datosEstudiante['uc_acumulado'] = (string) (intval($datosEstudiante['uc_acumulado']) + intval($pensum[$codigo]['uc_materia']));

                    // se actualiza la Habilitación
                    $resultado = $this->habilitarMateriasv2($datosEstudiante, $pensum, $codigo, false);

                    if (is_int($resultado)) {
                        // se actualizó sin problema
                    } else {
                        // con error

                        // rollback
                        //$d = 0/0;

                        return $resultado;
                    }
                } //sino, no se aprueba
            }

            // ...

            // se modifican los contadores

            // recalculado de UC Pensum Virtual (?)
            $uca = $this->recalcularUCVirtual($pensum);
            if (!is_int($uca)) {
                // error al recalcular
                return $uca;
            }

            // en el Estudiante Virtual
            $datosEstudiante['uc_acumulado'] = strval($uca);

            // validar estados (completo)
            $resultado = $this->habilitarMateriasv1($datosEstudiante, $pensum, false);
            if (is_int($resultado)) {
                // se actualizó sin problema
            } else {
                // con error
                return $resultado;
            }

            // condición de terminación de Pensum
            $terminado = ($uca >= $uct) ? true : false;
            // conteo de semestres
            $semestres++;

            if ($semestres == 19) {
                //$d = 0/null;
            }

            // condición de seguridad
            if ($semestres >= 20) {
                return GestorPensum::UNEXPECTED_FACTOR_REC;
            }
            //$d = 0/null;
        }

        //$d = 0/null;

        // devolver la catidad de semestres faltantes
        return $semestres;
    }

    // devuelve los códigos de materias con el estado dado
    private function obtenCoincidencias(array &$pensum, string $propiedad, string $valor)
    {
        // por cada materia
        $materias = array();
        $i = 0;

        try {
            // por cada materia
            foreach ($pensum as $codigo => $datos) {

                // condición indicada por propiedad => valor
                if ($datos[$propiedad] == $valor) {
                    // añadir el código a la lista
                    $materias[$i] = $codigo;
                    $i++;
                }
            }
        } catch (\Throwable $th) {
            return GestorPensum::INVALID_PROPIEDAD;
        }

        // devolver la lista de materias
        return $materias;
    }

    // devuelve los códigos de materias con los dos estados dados
    private function obtenCoincidenciasDoble(array &$pensum, string $propiedad1, string $valor1, string $propiedad2, string $valor2)
    {
        // por cada materia
        $materias = array();
        $i = 0;

        try {
            // por cada materia
            foreach ($pensum as $codigo => $datos) {

                // condición indicada por propiedad => valor
                if ($datos[$propiedad1] == $valor1 and $datos[$propiedad2] == $valor2) {
                    // añadir el código a la lista
                    $materias[$i] = $codigo;
                    $i++;
                }
            }
        } catch (\Throwable $th) {
            return GestorPensum::INVALID_PROPIEDAD;
        }

        // devolver la lista de materias
        return $materias;
    }

    // devuelve el coteo (int) de las UC de materias con el estado APROBADO_SI
    private function recalcularUCVirtual(array &$pensum)
    {
        // contador de UC
        $uc = 0;

        try {
            // por cada materia
            foreach ($pensum as $codigo => $datos) {

                // condición indicada por propiedad => valor
                if ($datos['aprobada'] == GestorPensum::APROBADA_SI) {
                    // añadir el código a la lista
                    $uc += intval($datos['uc_materia']);
                }
            }
        } catch (\Throwable $th) {
            return GestorPensum::INVALID_PROPIEDAD;
        }

        // devolver la lista de materias
        return $uc;
    }

    //----------------------------------------------------------------
    //
    //  Modificar
    //
    //----------------------------------------------------------------

    //
    // APROBAR
    //

    // Actualizaciones permitidas desde afuera (controlador)

    // aprobar (si se puede) una materia por código
    public function aprobarMateria(string $usuario_, string $materia, bool $registrar)
    {
        return $this->aprobarMaterias($usuario_, [$materia], $registrar);
    }

    // aprobar (si se puede) una lista de materias por código
    public function aprobarMaterias(string $usuario_, array $materias, bool $registrar)
    {
        // ahorrarse los recursos en vano
        if (count($materias) == 0) {
            return 0; // 0 cambios
        }

        // datos del estudiante
        $gestorE = new GestorEstudiante();

        $datosEstudiante = $gestorE->obtenDatosEstudiante($usuario_);

        // validar datos estudiante
        if ($datosEstudiante == GestorEstudiante::NOT_ESTUDIANTE) {
            return GestorPensum::NOT_ESTUDIANTE;
        }

        // datos de pensum (ya se encuentra actualizado, no?)
        $pensum = $this->obtenPensumCompleto($usuario_);

        // validar pensum (completo)
        $c = $this->habilitarMateriasv1($datosEstudiante, $pensum, true); //FALSE
        /*
        if ($c != 0) {
            // hubo algún cambio o algún error

            if (is_int($c)) {
                // hubo algún cambio
                return GestorPensum::CORRUPTED_PENSUM_1 . $c . GestorPensum::CORRUPTED_PENSUM_2;
            } else {
                // otro error
                return $c;
            }
        }
        */

        // contador de los cambios
        $cambios = 0;

        // fase de aprobación individual
        foreach ($materias as $codigo) {

            // condición de aprobación -> estar habilitada
            if ($pensum[$codigo]['habilitada'] == GestorPensum::HABILITADA_SI) {
                // la materia está habilitada
                $resultado = $this->modificarAprobada($datosEstudiante['id'], $codigo, GestorPensum::APROBADA_SI);

                if ($resultado == GestorPensum::SUCCESS) {
                    // se actualizó con éxito


                    // se suman las UC a las UCA del estudiante (Virtual)
                    $datosEstudiante['uc_acumulado'] = (string) (intval($datosEstudiante['uc_acumulado']) + intval($pensum[$codigo]['uc_materia']));


                    // se cambia en el Pensum Virtual
                    $pensum[$codigo]['aprobada'] = GestorPensum::APROBADA_SI;

                    // se actualiza la Habilitación (específica)
                    $resultado = $this->habilitarMateriasv2($datosEstudiante, $pensum, $codigo, $registrar);

                    if (is_int($resultado)) {
                        // se actualizó sin problema
                    } else {
                        // con error

                        // rollback
                        //$d = 0/0;

                        $resultado2 = $this->modificarAprobada($datosEstudiante['id'], $codigo, GestorPensum::APROBADA_NO);

                        return $resultado;
                    }

                    // se suma al contador
                    $cambios++;
                } else {
                    // con errores
                    return $resultado;
                }
            } //sino, no se aprueba

        }

        // Actualizar las UC para que no hayan discrepancias ?

        $newUC = $this->recalcularUC($datosEstudiante['id']);

        if (is_int($newUC)) {
            // sin errores
            $datosEstudiante['uc_acumulado'] = (string) ($newUC);

            // se actualiza la Habilitación (completa)
            $resultado = $this->habilitarMateriasv1($datosEstudiante, $pensum, $registrar);

            if (is_int($resultado)) {
                // se actualizó sin problema
            } else {
                // con error

                return $resultado;
            }
        } else {
            // con error
            return $newUC;
        }

        return $cambios;
    }

    //
    // REPROBAR
    //

    // reprobar (si se puede) una materia por código
    public function reprobarMateria(string $usuario_, string $materia, bool $registrar)
    {
        return $this->reprobarMaterias($usuario_, [$materia], $registrar);
    }

    // reprobar (si se puede) una lista de materias por código (!)(UNTESTED)
    public function reprobarMaterias(string $usuario_, array $materias, bool $registrar)
    {
        // ahorrarse los recursos en vano
        if (count($materias) == 0) {
            return 0; // 0 cambios
        }

        // datos del estudiante
        $gestorE = new GestorEstudiante();

        $datosEstudiante = $gestorE->obtenDatosEstudiante($usuario_);

        // validar datos estudiante
        if ($datosEstudiante == GestorEstudiante::NOT_ESTUDIANTE) {
            return GestorPensum::NOT_ESTUDIANTE;
        }

        // datos de pensum (ya se encuentra actualizado, no?)
        $pensum = $this->obtenPensumCompleto($usuario_);

        // validar pensum (completo)
        $c = $this->habilitarMateriasv1($datosEstudiante, $pensum, true); //false
        /*
        if ($c != 0) {
            // hubo algún cambio o algún error

            if (is_int($c)) {
                // hubo algún cambio
                return GestorPensum::CORRUPTED_PENSUM_1 . $c . GestorPensum::CORRUPTED_PENSUM_2;
            } else {
                // otro error
                return $c;
            }
        }
        */

        // contador de los cambios
        $cambios = 0;

        // fase de aprobación individual
        foreach ($materias as $codigo) {

            // condición de reprobación -> estar aprobada
            if ($pensum[$codigo]['aprobada'] == GestorPensum::APROBADA_SI) {
                // la materia está habilitada
                $resultado = $this->modificarAprobada($datosEstudiante['id'], $codigo, GestorPensum::APROBADA_NO);

                if ($resultado == GestorPensum::SUCCESS) {
                    // se actualizó con éxito


                    // se suman las UC a las UCA del estudiante (Virtual)
                    $datosEstudiante['uc_acumulado'] = (string) (intval($datosEstudiante['uc_acumulado']) - intval($pensum[$codigo]['uc_materia']));


                    // se cambia en el Pensum Virtual
                    $pensum[$codigo]['aprobada'] = GestorPensum::APROBADA_NO;

                    // se actualiza la Habilitación (específica)
                    $resultado = $this->habilitarMateriasv2($datosEstudiante, $pensum, $codigo, $registrar);

                    if (is_int($resultado)) {
                        // se actualizó sin problema
                    } else {
                        // con error

                        // rollback
                        //$d = 0/0;

                        $resultado2 = $this->modificarAprobada($datosEstudiante['id'], $codigo, GestorPensum::APROBADA_SI);

                        return $resultado;
                    }

                    // se suma al contador
                    $cambios++;
                } else {
                    // con errores
                    return $resultado;
                }
            } //sino, no se aprueba

        }

        // Actualizar las UC para que no hayan discrepancias ?

        $newUC = $this->recalcularUC($datosEstudiante['id']);

        if (is_int($newUC)) {
            // sin errores
            $datosEstudiante['uc_acumulado'] = (string) ($newUC);

            // se actualiza la Habilitación (completa)
            $resultado = $this->habilitarMateriasv1($datosEstudiante, $pensum, $registrar);

            if (is_int($resultado)) {
                // se actualizó sin problema
            } else {
                // con error

                return $resultado;
            }
        } else {
            // con error
            return $newUC;
        }

        return $cambios;
    }

    // Operaciones privadas requeridas al actualizar:

    //
    // HABILITAR
    //

    // Procesos del campo "habilitada", sea para Habilitar o Deshabilitar (hace las dos cosas)
    /**
     *   Listas:
     * habilitarMateriasv1() o (MÉTODO: MATERIA -> PRELACION)
     * habilitarMateriasINFv1() o
     * habilitarMateriasAMBv1() c?
     * 
     * habilitarMateriasv2() c? (MÉTODO: PRELACION -> MATERIA)
     * habilitarMateriasINFv2() c?
     * habilitarMateriasAMBv2() c?
     * 
     * habilitarMateriaComun() o
     * habilitarMateriaElectiva() o
     * habilitarMateriaConjunto() o
     * 
     * estaHabilitadaPorMaterias() o
     * estaHabilitadaPorUC() o
     * 
     *   Pendientes:
     *  */

    // habilitar, escogiendo la estrategia adecuada y para toda materia
    private function habilitarMateriasv1(array &$datosEstudiante, array &$pensum, bool $registrar)
    {
        // determinar la estrategia a usar
        $carrera = $datosEstudiante['codigo_carrera'];

        switch ($carrera) {
            case GestorCarreraMencion::CARRERA_INGINF:
                // aplicar sobre el pensum, para todas las materias
                return $this->habilitarMateriasINFv1($datosEstudiante, $pensum, $pensum, $registrar);

            case GestorCarreraMencion::CARRERA_INGAMB:
                // aplicar sobre el pensum, para todas las materias
                return $this->habilitarMateriasAMBv1($datosEstudiante, $pensum, $pensum, $registrar);

            default:
                return GestorPensum::INVALID_CARRERA_PENSUM;
        }
    }

    // habilitar con estrategia para las de INF (lento)
    private function habilitarMateriasINFv1(array &$datosEstudiante, array &$pensum, array &$actualizadas, bool $registrar)
    {
        // array que guarda las ediciones realizadas y su valor
        $editados = []; // codigo => habilitación  |  [XXX000] = "1"/"0";

        $gestorM = new GestorMaterias();

        //------ PRIMERO: iterar todo el pensum virtual

        foreach ($actualizadas as $codigo => $datos) {
            switch ($codigo) {
                    // casos especiales:
                case GestorMaterias::SSC_INF_CODIGO:
                    // SSC de informática:

                    // obtener la lista de materias del los Semestres I al IV
                    $materiasSemestres = array();

                    // para los semestres 1, 2, 3 y 4
                    for ($i = 1; $i <= 4; $i++) {
                        // obtener los códigos de dichas materias
                        $semestre = $gestorM->obtenMateriasSemestre(strval($i), $datosEstudiante['codigo_carrera']);

                        // rellenar con las claves, pero los valores no se usará así que omitir
                        foreach ($semestre as $cod => $cad) {
                            $materiasSemestres[$cod] = 0;
                        }
                    }

                    // mandar a habilitar, y obtener el resultado de control
                    $mod = $this->habilitarMateriaConjunto($datosEstudiante, $pensum, $codigo, $materiasSemestres, count($materiasSemestres));

                    if (is_bool($mod)) {
                        // acción exitosa
                        if ($mod) {
                            // si se modificó

                            // registrar el código y estado de la materia modificada
                            $editados[$codigo] = $pensum[$codigo]['habilitada'];
                        }
                    } else {
                        // algo salió mal
                        return $mod;
                    }

                    break;

                default:
                    // casos normales:

                    // según el tipo de materia
                    switch ($datos['electiva']) {
                        case GestorMaterias::COMUN:
                            // materia común:

                            // mandar a habilitar, y obtener el resultado de control
                            $mod = $this->habilitarMateriaComun($datosEstudiante, $pensum, $codigo);

                            if (is_bool($mod)) {
                                // acción exitosa
                                if ($mod) {
                                    // si se modificó

                                    // registrar el código y estado de la materia modificada
                                    $editados[$codigo] = $pensum[$codigo]['habilitada'];
                                }
                            } else {
                                // algo salió mal
                                return $mod;
                            }

                            break;

                        case GestorMaterias::ELECTIVA_MENCION:
                            // materia electiva mención:

                            // mandar a habilitar, y obtener el resultado de control
                            $mod = $this->habilitarMateriaComun($datosEstudiante, $pensum, $codigo);

                            if (is_bool($mod)) {
                                // acción exitosa
                                if ($mod) {
                                    // si se modificó

                                    // registrar el código y estado de la materia modificada
                                    $editados[$codigo] = $pensum[$codigo]['habilitada'];
                                }
                            } else {
                                // algo salió mal
                                return $mod;
                            }

                            break;

                        case GestorMaterias::ELECTIVA_COMUN:
                            // materia electiva común:

                            // obtener la lista de materias electivas del semestre indicado por la materia
                            $electivasSemestre = array();

                            // obtener los códigos de dichas materias
                            $semestre = $gestorM->obtenElectivasComunes($datos['semestre_materia'], $datosEstudiante['codigo_carrera']);

                            // rellenar con las claves, pero los valores no se usará así que omitir
                            foreach ($semestre as $cod => $cad) {
                                $electivasSemestre[$cod] = 0;
                            }

                            // mandar a habilitar, y obtener el resultado de control
                            $mod = $this->habilitarMateriaElectiva($datosEstudiante, $pensum, $codigo, $electivasSemestre, "1"); // 1 electiva por semestre

                            if (is_bool($mod)) {
                                // acción exitosa
                                if ($mod) {
                                    // si se modificó

                                    // registrar el código y estado de la materia modificada
                                    $editados[$codigo] = $pensum[$codigo]['habilitada'];
                                }
                            } else {
                                // algo salió mal
                                return $mod;
                            }

                            break;

                        default:
                            return GestorPensum::INVALID_MATERIAS_PENSUM;
                    }

                    break;
            }
        }

        //------ SEGUNDO: modificar en la BD el estado de las materias cambiadas

        // $editados; // codigo => habilitación  |  [XXX000] = "1"/"0";

        // si se deben registrar los cambios en Pensum Real (ya actualizados en Pensum Virtual)
        if ($registrar) {
            // para todos los editados:
            //$d = 0/0;

            foreach ($editados as $codigo => $estado) {
                // modificar en la BD
                $resultado = $this->modificarHabilitada($datosEstudiante['id'], $codigo, $estado);

                if ($resultado != GestorPensum::SUCCESS) {
                    // algún error pasó
                    return $resultado;
                }
            }
        }

        // devolver el número de editados/modificados (si $registrar=true, todos los actualizados)
        return count($editados);
    }

    // habilitar con estrategia para las de AMB (lento)
    private function habilitarMateriasAMBv1(array &$datosEstudiante, array &$pensum, array &$actualizadas, bool $registrar)
    {
        // array que guarda las ediciones realizadas y su valor
        $editados = []; // codigo => habilitación  |  [XXX000] = "1"/"0";

        $gestorM = new GestorMaterias();

        //------ PRIMERO: iterar todo el pensum virtual

        foreach ($actualizadas as $codigo => $datos) {
            switch ($codigo) {
                    // casos especiales:
                    /*
                case GestorMaterias::SSC_INF_CODIGO:
                    break;
                    */

                default:
                    // casos normales:

                    // según el tipo de materia
                    switch ($datos['electiva']) {
                        case GestorMaterias::COMUN:
                            // materia común:

                            // mandar a habilitar, y obtener el resultado de control
                            $mod = $this->habilitarMateriaComun($datosEstudiante, $pensum, $codigo);

                            if (is_bool($mod)) {
                                // acción exitosa
                                if ($mod) {
                                    // si se modificó

                                    // registrar el código y estado de la materia modificada
                                    $editados[$codigo] = $pensum[$codigo]['habilitada'];
                                }
                            } else {
                                // algo salió mal
                                return $mod;
                            }

                            break;

                        case GestorMaterias::ELECTIVA_HUMANISTICA:
                            // materia electiva de humanística (1):

                            // obtener la lista de materias electivas del semestre indicado por la materia
                            $electivasSemestre = array();

                            // códigos de todas las electivas en la carrera ambiental (H)
                            $semestre = $gestorM->obtenElectivasHumanistica($datosEstudiante['codigo_carrera']);

                            // rellenar con las claves, pero los valores no se usará así que omitir
                            foreach ($semestre as $cod => $cad) {
                                $electivasSemestre[$cod] = 0;
                            }

                            // mandar a habilitar, y obtener el resultado de control
                            $mod = $this->habilitarMateriaElectiva($datosEstudiante, $pensum, $codigo, $electivasSemestre, GestorMaterias::ECH_CANTIDAD); // 1 electiva tipo humanística

                            if (is_bool($mod)) {
                                // acción exitosa
                                if ($mod) {
                                    // si se modificó

                                    // registrar el código y estado de la materia modificada
                                    $editados[$codigo] = $pensum[$codigo]['habilitada'];
                                }
                            } else {
                                // algo salió mal
                                return $mod;
                            }

                            break;

                        case GestorMaterias::ELECTIVA_AMBIENTAL:
                            // materia electiva de ambiental (3):

                            // obtener la lista de materias electivas del semestre indicado por la materia
                            $electivasSemestre = array();

                            // códigos de todas las electivas en la carrera ambiental (A)
                            $semestre = $gestorM->obtenElectivasAmbientales($datosEstudiante['codigo_carrera']);

                            // rellenar con las claves, pero los valores no se usará así que omitir
                            foreach ($semestre as $cod => $cad) {
                                $electivasSemestre[$cod] = 0;
                            }

                            // mandar a habilitar, y obtener el resultado de control
                            $mod = $this->habilitarMateriaElectiva($datosEstudiante, $pensum, $codigo, $electivasSemestre, GestorMaterias::ECA_CANTIDAD); // 3 electivas 

                            if (is_bool($mod)) {
                                // acción exitosa
                                if ($mod) {
                                    // si se modificó

                                    // registrar el código y estado de la materia modificada
                                    $editados[$codigo] = $pensum[$codigo]['habilitada'];
                                }
                            } else {
                                // algo salió mal
                                return $mod;
                            }

                            break;

                        default:
                            return GestorPensum::INVALID_MATERIAS_PENSUM;
                    }

                    break;
            }
        }

        //------ SEGUNDO: modificar en la BD el estado de las materias cambiadas

        // $editados; // codigo => habilitación  |  [XXX000] = "1"/"0";

        // si se deben registrar los cambios en Pensum Real (ya actualizados en Pensum Virtual)
        if ($registrar) {
            // para todos los editados:
            //$d = 0/0;

            foreach ($editados as $codigo => $estado) {
                // modificar en la BD
                $resultado = $this->modificarHabilitada($datosEstudiante['id'], $codigo, $estado);

                if ($resultado != GestorPensum::SUCCESS) {
                    // algún error pasó
                    return $resultado;
                }
            }
        }

        // devolver el número de editados/modificados (si $registrar=true, todos los actualizados)
        return count($editados);
    }

    // habilitar, escogiendo la estrategia adecuada según la materia
    private function habilitarMateriasv2(array &$datosEstudiante, array &$pensum, string $actualizada, bool $registrar)
    {
        // determinar la estrategia a usar
        $carrera = $datosEstudiante['codigo_carrera'];

        switch ($carrera) {
            case GestorCarreraMencion::CARRERA_INGINF:
                return $this->habilitarMateriasINFv2($datosEstudiante, $pensum, $actualizada, $registrar);

            case GestorCarreraMencion::CARRERA_INGAMB:
                return $this->habilitarMateriasAMBv2($datosEstudiante, $pensum, $actualizada, $registrar);

            default:
                return GestorPensum::INVALID_CARRERA_PENSUM;
        }
    }

    // habilitar con estrategia para las de INF (rápido)
    private function habilitarMateriasINFv2(array &$datosEstudiante, array &$pensum, string $actualizada, bool $registrar)
    {
        // los gestores que se usarán para obtener las materias afectadas
        $gestorM = new GestorMaterias();
        $gestorPrel = new GestorPrelacion();

        //------ PRIMERO: determinar cuáles materias se deben evaluar (condiciones aditivas)

        // las materias que son afectadas por los cambios en la materia actualizada
        $porActualizar = array();

        // para todas, incluyendo COMUN
        if (true) {
            try {
                // las materias que son preladas por la actualizada
                $preladas = $gestorPrel->obtenMateriasPreladas($actualizada, $datosEstudiante['codigo_carrera'], $datosEstudiante['codigo_mencion']);

                // por cada materia prelada
                foreach ($preladas as $pre) {
                    $porActualizar[$pre['codigo_materia']] = $pensum[$pre['codigo_materia']];
                }
            } catch (\Throwable $th) {
                //$e = 0/0;
                return GestorPensum::INVALID_MATERIAS_PENSUM;
            }
        }

        // para las materias conjuntas de ELECTIVA_COMUN
        if ($pensum[$actualizada]['electiva'] == GestorMaterias::ELECTIVA_COMUN) {
            try {
                // obtener los códigos de dichas materias electivas
                $electivas = $gestorM->obtenElectivasComunes($pensum[$actualizada]['semestre_materia'], $datosEstudiante['codigo_carrera']);

                // rellenar con las claves, pero los valores no se usará así que omitir
                foreach ($electivas as $cod => $cad) {
                    $porActualizar[$cod] = $pensum[$cod];
                }
            } catch (\Throwable $th) {
                return GestorPensum::INVALID_MATERIAS_PENSUM;
            }
        }

        // para las materias de Semestre I-IV que afectan a SSC
        if (
            intval($pensum[$actualizada]['semestre_materia']) >= 1 and
            intval($pensum[$actualizada]['semestre_materia']) <= 4
        ) {
            // incluir SSC502 si la actualizada es de 1-4 semestre
            //SSC_INF_CODIGO
            $porActualizar[GestorMaterias::SSC_INF_CODIGO] = $pensum[GestorMaterias::SSC_INF_CODIGO];
        }

        //------ SEGUNDO: iterar las materias afectadas por la actualizada
        //$e = 0/0;
        return $this->habilitarMateriasINFv1($datosEstudiante, $pensum, $porActualizar, $registrar);
    }

    // habilitar con estrategia para las de AMB (rápido)
    private function habilitarMateriasAMBv2(array &$datosEstudiante, array &$pensum, string $actualizada, bool $registrar)
    {
        // los gestores que se usarán para obtener las materias afectadas
        $gestorM = new GestorMaterias();
        $gestorPrel = new GestorPrelacion();

        //------ PRIMERO: determinar cuáles materias se deben evaluar (condiciones aditivas)

        // las materias que son afectadas por los cambios en la materia actualizada
        $porActualizar = array();

        // para todas, incluyendo COMUN
        if (true) {
            try {
                // las materias que son preladas por la actualizada
                $preladas = $gestorPrel->obtenMateriasPreladas($actualizada, $datosEstudiante['codigo_carrera'], $datosEstudiante['codigo_mencion']);

                // por cada materia prelada
                foreach ($preladas as $pre) {
                    $porActualizar[$pre['codigo_materia']] = $pensum[$pre['codigo_materia']];
                }
            } catch (\Throwable $th) {
                return GestorPensum::INVALID_MATERIAS_PENSUM;
            }
        }

        // para las materias conjuntas electivas
        switch ((string)$pensum[$actualizada]['electiva']) {
            case GestorMaterias::ELECTIVA_HUMANISTICA:
                // para Humanisticas
                try {
                    // obtener los códigos de dichas materias electivas
                    $electivas = $gestorM->obtenElectivasHumanistica($datosEstudiante['codigo_carrera']);
    
                    // rellenar con las claves, pero los valores no se usará así que omitir
                    foreach ($electivas as $cod => $cad) {
                        $porActualizar[$cod] = $pensum[$cod];
                    }
                } catch (\Throwable $th) {
                    return GestorPensum::INVALID_MATERIAS_PENSUM;
                }
                break;

            case GestorMaterias::ELECTIVA_AMBIENTAL:
                // para Ambientales
                try {
                    // obtener los códigos de dichas materias electivas
                    $electivas = $gestorM->obtenElectivasAmbientales($datosEstudiante['codigo_carrera']);
    
                    // rellenar con las claves, pero los valores no se usará así que omitir
                    foreach ($electivas as $cod => $cad) {
                        $porActualizar[$cod] = $pensum[$cod];
                    }
                } catch (\Throwable $th) {
                    return GestorPensum::INVALID_MATERIAS_PENSUM;
                }
                break;
            
            default:
                # code...
                break;
        }

        //------ SEGUNDO: iterar las materias afectadas por la actualizada

        return $this->habilitarMateriasAMBv1($datosEstudiante, $pensum, $porActualizar, $registrar);
    }

    //
    // Funciones usuales al Habilitar
    //

    // habilitar una materia común dada, según su prelaciones propias (materias y uc)
    // (#)(sólo funciona para mat{opcional '0'/'1' AND uc{opcional '0' }})
    private function habilitarMateriaComun(array &$datosEstudiante, array &$pensum, string $materia)
    {
        // habilitar si( materia{prelaciones} aprob )

        //------ Para las de tipo MATERIA ------//

        // determinar si se cumple la condición de prelación por materias.
        // ['prelacion_materia'] = array[codigo[opcional]] de la materia provista
        $prelMatAprob = $this->estaHabilitadaPorMaterias($pensum, $pensum[$materia]['prelacion_materia']);

        if (!is_bool($prelMatAprob)) {
            return $prelMatAprob;
        }

        //------ Para las de tipo UC ------//

        // determinar si se cumple la codición de prelación por UC.
        // puede ser opcional (mat or uc) u obligatorio (mat and uc)
        $prelUCAprob = $this->estaHabilitadaPorUC($datosEstudiante['uc_acumulado'], $pensum[$materia]['prelacion_uc'], $prelMatAprob);

        if (!is_bool($prelMatAprob)) {
            return $prelUCAprob;
        }

        // si por las prelaciones está aprobada
        $prelAprob = $prelUCAprob;

        //------ Resultado combinado ------//

        // estado anterior
        $old = $pensum[$materia]['habilitada'];

        // estado nuevo según las prelaciones
        $new = ($prelAprob) ? GestorPensum::HABILITADA_SI : GestorPensum::HABILITADA_NO;

        // aplicar nuevo estado en el pensum virtual
        $pensum[$materia]['habilitada'] = $new;

        // determinar si hubo cambio (delta)
        return ($old == $new) ? false : true;
    }

    // habilitar una materia de ente varias opciones, según su prelaciones propias (materias y uc) y
    // un máximo de aprobadas en conjunto. OPCIONES debe incluir a MATERIA
    private function habilitarMateriaElectiva(array &$datosEstudiante, array &$pensum, string $materia, array $opciones, int $maxOpciones)
    {
        // habilitar si(prelaciones ok) AND si(aprobadas < maxOpciones)

        //------ Para las prelaciones propias ------//

        // determinar si se cumple la condición de prelación por materias.
        // ['prelacion_materia'] = array[codigo[opcional]] de la materia provista
        $prelMatAprob = $this->estaHabilitadaPorMaterias($pensum, $pensum[$materia]['prelacion_materia']);

        if (!is_bool($prelMatAprob)) {
            //$d = 0/0;
            return $prelMatAprob;
        }

        // determinar si se cumple la codición de prelación por UC.
        // puede ser opcional (mat or uc) u obligatorio (mat and uc)
        $prelUCAprob = $this->estaHabilitadaPorUC($datosEstudiante['uc_acumulado'], $pensum[$materia]['prelacion_uc'], $prelMatAprob);

        if (!is_bool($prelMatAprob)) {
            //$d = 0/0;
            return $prelUCAprob;
        }

        // si por las prelaciones está aprobada
        $prelAprob = $prelUCAprob;

        //------ Para las prelaciones extra ------//

        $aprobadas = 0;

        // si hay al menos 1 opción
        if (count($opciones) > 0) {

            // por cada opción comprobar
            foreach ($opciones as $codigo => $datos) {

                // si el código de la opción actual existe en el Pensum
                if (isset($pensum[$codigo])) {

                    // si dicha materia está aprobada
                    if ($pensum[$codigo]['aprobada'] == GestorPensum::APROBADA_SI) {

                        // se suma una aprobada
                        $aprobadas++;
                    }
                } else {
                    return GestorPensum::INVALID_MATERIAS_PENSUM;
                }
            }
        }

        // si aún hay opciones disponibles según el máximo (actual < máximo)
        $opcAprob = ($aprobadas < $maxOpciones) ? true : false;

        //------ Resultado combinado ------//

        // estado anterior
        $old = $pensum[$materia]['habilitada'];

        // estado nuevo según la habilitación propia (prelacion) e impropia (opciones)
        $new = ($prelAprob and $opcAprob) ? GestorPensum::HABILITADA_SI : GestorPensum::HABILITADA_NO;

        // aplicar nuevo estado en el pensum virtual
        $pensum[$materia]['habilitada'] = $new;

        // determinar si hubo cambio (delta)
        return ($old == $new) ? false : true;
    }

    // habilitar una materia, según sus prelaciones propias (materias y uc) y
    // yun mínimo de aprobadas en conjunto. OPCIONES pueden ser las contenidas en semestres I-IV
    private function habilitarMateriaConjunto(array &$datosEstudiante, array &$pensum, string $materia, array $opciones, int $minOpciones)
    {
        // habilitar si( foreach semestre (getMaterias) -> habilitarMateriaConjunto(MateriasSemestres) )

        //------ Para las prelaciones propias ------//

        // determinar si se cumple la condición de prelación por materias.
        // ['prelacion_materia'] = array[codigo[opcional]] de la materia provista
        $prelMatAprob = $this->estaHabilitadaPorMaterias($pensum, $pensum[$materia]['prelacion_materia']);

        if (!is_bool($prelMatAprob)) {
            return $prelMatAprob;
        }

        // determinar si se cumple la codición de prelación por UC.
        // puede ser opcional (mat or uc) u obligatorio (mat and uc)
        $prelUCAprob = $this->estaHabilitadaPorUC($datosEstudiante['uc_acumulado'], $pensum[$materia]['prelacion_uc'], $prelMatAprob);

        if (!is_bool($prelMatAprob)) {
            return $prelUCAprob;
        }

        // si por las prelaciones está aprobada
        $prelAprob = $prelUCAprob;

        //------ Para las prelaciones extra ------//

        $aprobadas = 0;

        // si hay al menos 1 opción
        if (count($opciones) > 0) {

            // por cada opción comprobar
            foreach ($opciones as $codigo => $datos) {

                // si el código de la opción actual existe en el Pensum
                if (isset($pensum[$codigo])) {

                    // si dicha materia está aprobada
                    if ($pensum[$codigo]['aprobada'] == GestorPensum::APROBADA_SI) {

                        // se suma una aprobada
                        $aprobadas++;
                    }
                } else {
                    return GestorPensum::INVALID_MATERIAS_PENSUM;
                }
            }
        }

        // si aún hay opciones disponibles según el máximo (actual < máximo)
        $opcAprob = ($aprobadas >= $minOpciones) ? true : false;

        //------ Resultado combinado ------//

        // estado anterior
        $old = $pensum[$materia]['habilitada'];

        // estado nuevo según la habilitación propia (prelacion) e impropia (opciones)
        $new = ($prelAprob and $opcAprob) ? GestorPensum::HABILITADA_SI : GestorPensum::HABILITADA_NO;

        // aplicar nuevo estado en el pensum virtual
        $pensum[$materia]['habilitada'] = $new;

        // determinar si hubo cambio (delta)
        return ($old == $new) ? false : true;
    }

    // evualar habilitación según prelaciones de materia
    private function estaHabilitadaPorMaterias(array &$pensum, array $prelaciones)
    {
        // contador de las materias preladoras aprobadas
        $necesarias = count($prelaciones);
        $aprobadas = 0;
        $opcionales = 0;

        if ($necesarias == 0) {
            return true;
        }

        // revisar si las materias preladoras están aprobadas
        try {
            foreach ($prelaciones as $codigo => $opcional) {
                // por cada prelación

                // por defecto (si no está en el pensum)
                $materiaAprob = [
                    'aprobada' => GestorPensum::APROBADA_NO
                ];

                if (isset($pensum[$codigo])) {
                    // la materia está e este pensum
                    $materiaAprob = $pensum[$codigo];
                }

                // si la preladora está aprobada
                if ($materiaAprob['aprobada'] == GestorPensum::APROBADA_SI) {
                    $aprobadas++;
                }

                //si la prelación es opcional (OR)
                if ($opcional == gestorPrelacion::OPCIONAL) {
                    $opcionales++;
                }
            }
        } catch (\Throwable $th) {
            // caso imposible
            return GestorPensum::UNEXPECTED_HAB_MAT_COMUN;
        }

        // determinar si toda prelación por materia se cumple
        if ($opcionales == 0) {
            // sin opcional
            return ($aprobadas >= $necesarias) ? true : false;
        } else {
            // con al menos un par de opcionales (A or B)
            return ($aprobadas >= ($necesarias - ($opcionales - 1))) ? true : false;
        }
    }

    // evualar habilitación según prelacion de UC
    private function estaHabilitadaPorUC(string $uca, array $prelacion, bool $prelMatAprob)
    {
        $prelUCAprob = true;
        $opcional = GestorPrelacion::OBLIGATORIA;

        if (count($prelacion) > 0) {
            // si tiene prelación por UC
            $opcional = $prelacion['opcional'];

            // revisar si las UC son suficientes
            try {
                $necesarias = intval($prelacion['uc']);
                $acumuladas = intval($uca);

                $prelUCAprob = ($acumuladas >= $necesarias) ? true : false;
            } catch (\Throwable $th) {
                return GestorPensum::INVALID_UCA_FORMATO;
            }
        }

        // determinar si la combinación de prelaciones se cumplen, según el caso
        if ($opcional == gestorPrelacion::OPCIONAL) {
            // opcional (principalmente SSC en INF)
            return ($prelUCAprob or $prelMatAprob) ? true : false;
        } else {
            // obligatorio
            return ($prelUCAprob and $prelMatAprob) ? true : false;
        }
    }

    //
    // Registrar cambios en la BD:
    //

    // cambiar el campo "habilitada" de la materia+estudiante dados
    private function modificarHabilitada(string $estudiante_, string $materia_, string $habilitacion_)
    {
        // formateo
        $estudiante = darComillas($estudiante_);
        $materia = darComillas($materia_);

        // sql
        $sql = "UPDATE pensums
                SET habilitada = '{$habilitacion_}'
                WHERE (id_estudiante = $estudiante) AND (codigo_materia = $materia)";

        // query
        try {
            $this->db->query($sql);
        } catch (\Throwable $th) {
            return GestorPensum::UNEXPECTED_SQL;
        }

        // resultado de control
        /*
        if ($this->db->affectedRows() > 0) {
            return GestorPensum::SUCCESS;
        } else {
            return GestorPensum::UNEXPECTED_UPDATE;
        }
        */
        return GestorPensum::SUCCESS;
    }

    // cambiar el campo "aprobada" de la materia+estudiante dados
    private function modificarAprobada(string $estudiante_, string $materia_, string $aprobada_)
    {
        // formateo
        $estudiante = darComillas($estudiante_);
        $materia = darComillas($materia_);

        // sql
        $sql = "UPDATE pensums
                SET aprobada = '{$aprobada_}'
                WHERE (id_estudiante = $estudiante) AND (codigo_materia = $materia)";

        // query
        try {
            $this->db->query($sql);
        } catch (\Throwable $th) {
            return GestorPensum::UNEXPECTED_SQL;
        }

        // resultado de control
        /*
        if ($this->db->affectedRows() > 0) {
            return GestorPensum::SUCCESS;
        } else {
            return GestorPensum::UNEXPECTED_UPDATE;
        }
        */
        return GestorPensum::SUCCESS;
    }

    //----------------------------------------------------------------
    //
    //  Eliminar
    //
    //----------------------------------------------------------------

    // eliminar un estudiante dada su ID
    public function eliminarPensum(string $estudiante_)
    {
        $estudiante = darComillas($estudiante_);

        $sql = "DELETE FROM pensums WHERE id_estudiante = $estudiante";

        // consulta a la bd para eliminar
        try {
            $this->db->query($sql);
        } catch (\Throwable $th) {
            return GestorPensum::UNEXPECTED_SQL;
        }

        // Comprobación de una Correcta Eliminación
        /*
        if ($this->db->affectedRows() > 0) {
            return GestorPensum::SUCCESS;
        } else {
            return GestorPensum::UNEXPECTED_DELETE;
        }
        */
        return GestorPensum::SUCCESS;
    }

    //----------------------------------------------------------------
    //
    //  Validar
    //
    //----------------------------------------------------------------

    // Validación de Estudiante (Índice y Foráneo) - Si existe o no en la tabla correspondiente
    private function validarEstudiante(string $estudiante)
    {
        $gestorE = new GestorEstudiante();

        try {
            if ($gestorE->existeEstudiante($estudiante)) {
                return validarIndice($estudiante, GestorEstudiante::SIZE_ESTUDIANTE, GestorPensum::VALID, GestorEstudiante::INVALID_ESTUDIANTE, $this->existeEstudiante($estudiante), GestorPensum::YET_ESTUDIANTE);
            } else {
                return GestorEstudiante::INVALID_ESTUDIANTE;
            }
        } catch (\Throwable $th) {
            return GestorEstudiante::INVALID_ESTUDIANTE;
        }
    }

    // Validación del codigo_carrera (Foráneo) - Si existe en la tabla correspondiente
    private function validarCarrera(string $carrera)
    {
        $gestorC = new GestorCarreraMencion();

        if ($gestorC->existeCarrera($carrera)) {
            return GestorPensum::VALID;
        } else {
            return GestorCarreraMencion::INVALID_CARRERA;
        }
    }

    // Validación del codigo_mencion (Foráneo) - Si existe en la tabla correspondiente
    private function validarMencion(string $mencion)
    {
        $gestorC = new GestorCarreraMencion();

        // NOTA: Debe definirse un parámetro carrera (debe ser por pares Carr-Menc)

        if ($gestorC->existeMencion($mencion)) {
            return GestorPensum::VALID;
        } else {
            return GestorCarreraMencion::INVALID_MENCION;
        }
    }
}
