<?php

namespace App\Models;

use CodeIgniter\Model;

include_once("Utiles.php");
include_once("Validacion.php");

class GestorEstudiante extends Model
{
    /* CONSTANTES NECESARIAS */

    // Valores de Control de la BD
    const DEFAULT_UC = 0;
    const MINIMO_UC = 0;
    const MAXIMO_UC = 999;

    // Variables para el Período Académico
    const MINIMO_SEMESTRE = 0;
    const MAXIMO_SEMESTRE = 20;
    const MINIMO_COHORTE_AÑO = 2000;
    const MAXIMO_COHORTE_AÑO = 3000;

    // NOTA: Deberían pasarse al GestorPeriodoAcademico

    //valores de control de la DB
    const SIZE_ESTUDIANTE = 10; //tratar como string y usar try/catch

    // Enumeradores
    const COHORTE_GRUPO_1 = '1';
    const COHORTE_GRUPO_2 = '2';
    const ESTADO_ACTIVO = '1';
    const ESTADO_INACTIVO = '4';

    // Mensajes de Error y Advertencia
    const INVALID_ESTUDIANTE = "Estudiante Inválido";
    const INVALID_SEMESTRE = "Semestre Inválido";
    const INVALID_COHORTE_AÑO = "Año de Cohorte Inválido";
    const INVALID_COHORTE_GRUPO = "Grupo de Cohorte Inválido";
    const INVALID_UC = "Cantidad de UC Acumulada Inválida";
    const INVALID_ESTADO = "Estado Inválido";
    const YET_USUARIO = "Estudiante ya existente para este Usuario";
    const NOT_ESTUDIANTE = "Estudiante no Existente";
    const UNEXPECTED_SQL = "Error Inesperado en la consulta a la base de datos";
    const UNEXPECTED_CREACION = "Error Inesperado en la Creación de Estudiante";
    const UNEXPECTED_UPDATE = "Error Inesperado en la Actualización de Pensum";

    // Mensajes Éxitosos
    const VALID = "Campo Válido";
    const SUCCESS = "Acción Realizada Exitosamente";

    // Constructor
    public function __construct()
    {
        parent::__construct();
    }

    //----------------------------------------------------------------
    //
    //  Creación de Estudiante
    //
    //----------------------------------------------------------------

    // NOTA: Por defecto, uc_acumalado = 0 y estado = 1 (Activo) 

    public function crearEstudiante(string $usuario_, string $codigoCarrera_, string $codigoMencion_, string $semestre_, string $añoCohorte_, string $grupoCohorte_, string $ucAcumulado_, string $estado_)
    {

        $valides = array();

        $valides[0] = $this->validarUsuario($usuario_);
        $valides[1] = $this->validarCarrera($codigoCarrera_);
        $valides[2] = $this->validarMencionEnCarrera($codigoCarrera_, $codigoMencion_);
        $valides[3] = $this->validarSemestre($semestre_);
        $valides[4] = $this->validarAñoCohorte($añoCohorte_);
        $valides[5] = $this->validarGrupoCohorte($grupoCohorte_);
        $valides[6] = $this->validarUCA($ucAcumulado_);
        $valides[7] = $this->validarEstado($estado_);

        foreach ($valides as $val) {
            if ($val != GestorEstudiante::VALID) {
                return $val;
            }
        }

        $id = $this->obtenIndice();

        // Consulta para la Inserción en la BD de los Datos Validados

        $sql = "INSERT INTO estudiantes
                    (id, usuario, codigo_carrera, codigo_mencion, semestre, año_cohorte, grupo_cohorte, uc_acumulado, estado) 
                VALUES 
                    ('{$id}', '{$usuario_}', '{$codigoCarrera_}', '{$codigoMencion_}', '{$semestre_}', '{$añoCohorte_}', '{$grupoCohorte_}', '{$ucAcumulado_}', '{$estado_}')";

        // resultado
        $afectados = 0;
        try {
            $this->db->query($sql);
            $afectados = $this->db->affectedRows();
        } catch (\Throwable $th) {
            //throw $th;
        }

        // Comprobación de una Inserción Exitosa
        if ($afectados > 0) {
            return GestorEstudiante::SUCCESS;
        } else {
            return GestorEstudiante::UNEXPECTED_CREACION;
        }
    }

    //----------------------------------------------------------------
    //
    //   Obtener Datos de Estudiante
    //
    //----------------------------------------------------------------

    // obtiee los datos de los estudios de us estudiante
    public function obtenDatosEstudiante(String $usuario_)
    {
        $usuario = darComillas($usuario_); // esta por id o código?

        // consulta
        $sql = "SELECT id, usuario, codigo_carrera, codigo_mencion, semestre, año_cohorte, grupo_cohorte, uc_acumulado, estado 
                FROM estudiantes 
                WHERE usuario = $usuario 
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
            return GestorEstudiante::NOT_ESTUDIANTE;
        } else {
            return $registro;
        }
    }

    // Comrpobación de la Existencia de un Usuario
    public function existeUsuarioEstudiante(string $usuario_)
    {
        $usuario = darComillas($usuario_);

        // Consulta para Obtener el Registro del Usuario
        $sql = "SELECT usuario
                FROM estudiantes 
                WHERE usuario = $usuario 
                    LIMIT 1";

        // resultado
        $registro = null;

        try {
            $consulta = $this->db->query($sql);
            $registro = $consulta->getRowArray();
        } catch (\Throwable $th) {
            //throw $th;
        }

        // Comprobación de Existencia de un Registro con ese Usuario
        if ($registro == null) {
            return false;
        } else {
            return true;
        }
    }

    // Validación de Existencia de un Estudiate por su ID
    public function existeEstudiante(string $estudiante_)
    {
        $estudiante = darComillas($estudiante_);

        // Consulta para Comprobar la Existencia de un Registro con un Determinado usuario
        $sql = "SELECT id
                FROM estudiantes 
                WHERE id = $estudiante 
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

    // obtiene el siguiente índice desocupado, basado en el ID más alto existente
    private function obtenIndice()
    {
        // Consulta el último ID registrado
        $sql = "SELECT MAX(id) AS id 
                FROM estudiantes";

        // resultado
        $registro = null;

        try {
            $consulta = $this->db->query($sql);
            $registro = $consulta->getRowArray();
        } catch (\Throwable $th) {
            //throw $th;
        }

        if ($registro != null) {
            return intval($registro['id']) + 1; //siguiente... (???)(tolera concurrencia con REGISTER ?)
        } else {
            return "IMPOSIBLE: GestorUsuario::obtenIndice";
        }
    }

    //----------------------------------------------------------------
    //
    //  Modificar Datos de Estudiante
    //
    //----------------------------------------------------------------

    public function modificarUCA(string $estudiante_, int $uca_) {
        // formateo
        $estudiante = darComillas($estudiante_);
        $uca = strval($uca_);

        // sql
        $sql = "UPDATE estudiantes
                SET uc_acumulado = '{$uca}'
                WHERE (id = $estudiante)";

        // query
        try {
            $this->db->query($sql);
        } catch (\Throwable $th) {
            GestorEstudiante::UNEXPECTED_SQL;
        }

        /*
        // resultado de control
        if ($this->db->affectedRows() > 0) {
            return GestorEstudiante::SUCCESS;
        } else {
            return GestorEstudiante::NOT_ESTUDIANTE;
        }
        */
        
        return GestorEstudiante::SUCCESS;
    }
    
    //----------------------------------------------------------------
    //
    //  Editar Datos de Estudiante
    //
    //----------------------------------------------------------------

    // Función para la Obtención de Datos del Estudiante
    public function obtenerEstudiante($id_) {
        // Consulta para la Obtención del Registro con el id Dado
        $sql = "SELECT *, DAY(fecha_nacimiento) AS dia, MONTH(fecha_nacimiento) AS mes, YEAR(fecha_nacimiento) AS año
                FROM usuarios
                WHERE id = $id_
                    LIMIT 1";

        // Resultado
        $registro = null;

        try {
            $consulta = $this->db->query($sql);
            $registro = $consulta->getRowArray();
        } catch (\Throwable $th) {
            //throw $th;
        }

        // Comprobación de un Registro en la Consulta
        if ($registro == null) {
            return 'ID no Encontrado';
        }

        unset($registro['clave']);

        return $registro;
    }

    // Función para la Actualización de Datos del Estudiante
    public function actualizarEstudiante(string $id_, string $usuario_, string $cedula_, string $correo_, string $nombre_, string $apellido_, string $genero_, array $fechaNacimiento_) {
        // Registro Actual del Usuario con el id Dado
        $registro = $this->obtenerEstudiante($id_);

        // Validación de los Datos Entrantes
        $valides = array();

        // CONDICIONAL: Comprobación de Cambio de usuario para la Validación de Unicidad
        if ($registro['usuario'] != $usuario_) {
            $valides[] = $this->validarUsuario($usuario_);
        }

        // CONDICIONAL: Comprobación de Cambio de cedula para la Validación de Unicidad
        if ($registro['cedula'] != $cedula_) {
            $valides[] = $this->validarUsuario($cedula_);
        }

        // CONDICIONAL: Comprobación de Cambio de correo para la Validación de Unicidad
        if ($registro['correo'] != $correo_) {
            $valides[] = $this->validarCorreo($correo_);
        }

        $valides[] = $this->validarNombre($nombre_);
        $valides[] = $this->validarApellido($apellido_);
        $valides[] = $this->validarGenero($genero_);

        foreach ($valides as $val) {
            if ($val != GestorUsuario::VALID) {
                return $val;
            }
        }

        $fechaNacimiento = formatearFecha($fechaNacimiento_['dia'], $fechaNacimiento_['mes'], $fechaNacimiento_['año']);

        if ($fechaNacimiento == GestorPeriodoAcademico::INVALID_FECHA) {
            return GestorUsuario::INVALID_FECHA_NACIMIENTO;
        }

        // Consulta para la Actualización en la BD, dado que los Datos son Válidos
        $sql = "UPDATE usuarios 
                SET usuario = '{$usuario_}', cedula = '{$cedula_}', correo = '{$correo_}', nombre = '{$nombre_}', apellido = '{$apellido_}', genero = '{$genero_}', fecha_nacimiento = '{$fechaNacimiento}'
                WHERE id = $id_";

        // Resultado
        $afectados = 0;

        try {
            $this->db->query($sql);
            $afectados = $this->db->affectedRows();
        } catch (\Throwable $th) {
            //throw $th;
        }

        // Comprobación de una Correcta Inserción
        if ($afectados > 0) {
            return GestorUsuario::SUCCESS;
        } else {
            return GestorUsuario::UNEXPECTED_UPDATE;
        }
    }

    //----------------------------------------------------------------
    //
    //  Eliminación de Estudiante
    //
    //----------------------------------------------------------------

    public function eliminarEstudiante($estudiante_)
    {
        $estudiante = darComillas($estudiante_);

        // Consulta de Eliminación del Registro del usuario Indicado
        $sql = "DELETE FROM estudiantes 
                WHERE id = $estudiante";

        // resultado
        $afectados = 0;
        try {
            $this->db->query($sql);
            $afectados = $this->db->affectedRows();
        } catch (\Throwable $th) {
            //throw $th;
        }

        // Comprobación de una Eliminación Exitosa
        if ($afectados) {
            return GestorEstudiante::SUCCESS;
        } else {
            return GestorEstudiante::NOT_ESTUDIANTE;
        }
    }

    public function eliminarUsuarioEstudiante($usuario_)
    {
        $usuario = darComillas($usuario_);

        // Consulta de Eliminación del Registro del usuario Indicado
        $sql = "DELETE FROM estudiantes 
                WHERE usuario = $usuario";

        // resultado
        $afectados = 0;
        try {
            $this->db->query($sql);
            $afectados = $this->db->affectedRows();
        } catch (\Throwable $th) {
            //throw $th;
        }

        // Comprobación de una Eliminación Exitosa
        if ($afectados) {
            return GestorEstudiante::SUCCESS;
        } else {
            return GestorEstudiante::NOT_ESTUDIANTE;
        }
    }

    //----------------------------------------------------------------
    //
    //  Validar Datos de Estudiante
    //
    //----------------------------------------------------------------

    // Validación de Usuario (Índice y Foráneo) - Si existe o no en la tabla correspondiente
    private function validarUsuario(string $usuario)
    {
        $gestorU = new GestorUsuario();

        if ($gestorU->existeUsuario($usuario)) {
            return validarIndice($usuario, GestorUsuario::SIZE_USUARIO, GestorEstudiante::VALID, GestorUsuario::INVALID_USUARIO, $this->existeUsuarioEstudiante($usuario), GestorEstudiante::YET_USUARIO);
        } else {
            return GestorUsuario::INVALID_USUARIO;
        }
    }

    // Validación del codigo_carrera (Foráneo) - Si existe en la tabla correspondiente
    private function validarCarrera(string $carrera)
    {
        $gestorC = new GestorCarreraMencion();

        if ($gestorC->existeCarrera($carrera)) {
            return GestorEstudiante::VALID;
        } else {
            return GestorCarreraMencion::INVALID_CARRERA;
        }
    }

    // Validación del codigo_mencion (Foráneo) - Si existe en la tabla correspondiente
    private function validarMencion(string $mencion)
    {
        $gestorC = new GestorCarreraMencion();

        // NOTA: Debe definirse un parámetro carrera

        if ($gestorC->existeMencion($mencion)) {
            return GestorEstudiante::VALID;
        } else {
            return GestorCarreraMencion::INVALID_MENCION;
        }
    }

    // Validación de del semestre (Número) - Si es un número y está en el rango
    private function validarSemestre(string $semestre)
    {
        return validarNumeroEntero($semestre, GestorEstudiante::MINIMO_SEMESTRE, GestorEstudiante::MAXIMO_SEMESTRE, GestorEstudiante::VALID, GestorEstudiante::INVALID_SEMESTRE);
    }

    // NOTA: Falta validación del máximo de semestres posibles

    // Validación de uc_acumulada (Número) - Si es un número y está en el rango
    private function validarUCA(string $ucAcumulado)
    {
        return validarNumeroEntero($ucAcumulado, GestorEstudiante::MINIMO_UC, GestorEstudiante::MAXIMO_UC, GestorEstudiante::VALID, GestorEstudiante::INVALID_UC);
    }

    // Validación del año_cohorte (Número) - Si es un número y está en el rango
    private function validarAñoCohorte(string $añoCohorte)
    {
        return validarNumeroEntero($añoCohorte, GestorEstudiante::MINIMO_COHORTE_AÑO, GestorEstudiante::MAXIMO_COHORTE_AÑO, GestorEstudiante::VALID, GestorEstudiante::INVALID_COHORTE_AÑO);
    }

    // Validación del grupo_cohorte (Enumerador) - Si es una de las opciones
    private function validarGrupoCohorte(string $grupoCohorte)
    {
        $grupos = array(GestorEstudiante::COHORTE_GRUPO_1, GestorEstudiante::COHORTE_GRUPO_2);

        return validarEnumerador($grupoCohorte, $grupos, GestorEstudiante::VALID, GestorEstudiante::INVALID_COHORTE_GRUPO);
    }

    // NOTA: Falta validación conjunta año-grupo

    // Validación del estado (Enumerador) - Si es una de las opciones
    private function validarEstado(string $estado)
    {
        $estados = array(GestorEstudiante::ESTADO_ACTIVO, GestorEstudiante::ESTADO_INACTIVO);

        return validarEnumerador($estado, $estados, GestorEstudiante::VALID, GestorEstudiante::INVALID_ESTADO);
    }

    // Validación del codigo_mencion (Foráneo)
    private function validarMencionEnCarrera(string $carrera, string $mencion) {
        $gestorC = new GestorCarreraMencion();

        if ($gestorC->existeMencionEnCarrera($mencion, $carrera)) {
            return GestorEstudiante::VALID;
        } else {
            return GestorCarreraMencion::INVALID_MENCION;
        }
    }

    //----------------------------------------------------------------
    //
    //  Validar Datos de Usuario del Estudiante
    //
    //----------------------------------------------------------------

    // Validación de Existencia de un Usuario
    public function existeUsuario(string $usuario_) {
        $usuario = darComillas($usuario_);

        // Consulta para Comprobar la Existencia de un Registro con un Determinado usuario
        $sql = "SELECT usuario
                FROM usuarios 
                WHERE usuario = $usuario 
                    LIMIT 1";

        // Resultado
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

    // Comprobación de la Existencia de un usuario con una Determinada cedula
    public function existeCedula(string $cedula_) {
        $cedula = darComillas($cedula_);

        // Consulta para Comprobar la Existencia de un Registro con esa cedula
        $sql = "SELECT cedula 
                FROM usuarios 
                WHERE cedula = $cedula 
                    LIMIT 1";

        // Resultado
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

    // Comprobación de la Existencia de un usuario con un Determinado correo
    public function existeCorreo(string $correo_) {
        $correo = darComillas($correo_);

        // Consulta para Comprobar la Existencia de un Registro con ese correo
        $sql = "SELECT correo
                FROM usuarios 
                WHERE correo = $correo
                    LIMIT 1";

        // Resultado
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

    // Validación de cedula (Índice) - Si tiene el tamaño correcto y no existe aún
    private function validarCedula(string $cedula) {
        return validarIndice($cedula, GestorUsuario::SIZE_CEDULA, GestorUsuario::VALID, GestorUsuario::INVALID_CEDULA, $this->existeCedula($cedula), GestorUsuario::YET_CEDULA);
    }

    // Validación de correo (Índice) - Si tiene el tamaño correcto y no existe aún
    private function validarCorreo(string $correo) {
        return validarIndice($correo, GestorUsuario::SIZE_CORREO, GestorUsuario::VALID, GestorUsuario::INVALID_CORREO, $this->existeCorreo($correo), GestorUsuario::YET_CORREO);
    }

    // Validación de clave (Campo) - Si tiene el tamaño correcto
    private function validarClave(string $clave) {
        return validarCampo($clave, GestorUsuario::SIZE_CLAVE, GestorUsuario::VALID, GestorUsuario::INVALID_CLAVE);
    }

    // Validación de nombre (Campo) - i tiene el tamaño correcto
    private function validarNombre(string $nombre) {
        return validarCampo($nombre, GestorUsuario::SIZE_NOMBRE, GestorUsuario::VALID, GestorUsuario::INVALID_NOMBRE);
    }

    // Validación de apellido (Campo) - Si tiene el tamaño correcto
    private function validarApellido(string $apellido) {
        return validarCampo($apellido, GestorUsuario::SIZE_APELLIDO, GestorUsuario::VALID, GestorUsuario::INVALID_APELLIDO);
    }

    // Validación de genero (Enumerador) - Si es una de las opciones
    private function validarGenero(string $genero)
    {
        $generos = array(GestorUsuario::GENERO_F, GestorUsuario::GENERO_M);

        return validarEnumerador($genero, $generos, GestorUsuario::VALID, GestorUsuario::INVALID_GENERO);
    }
}
