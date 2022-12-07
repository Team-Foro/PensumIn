<?php

namespace App\Models;

use CodeIgniter\Model;

include_once("Utiles.php");
include_once("Validacion.php");

class GestorUsuario extends Model
{
    // CONSTANTES NECESARIAS

    // Clave de Encriptación/Decriptación
    //private const AES_KEY = "PensumIn2022*";
    private const AES_KEY = "Pensum2022*";

    //valores de control de la DB
    const SIZE_USUARIO = 20;
    const SIZE_CORREO = 60;
    const SIZE_CLAVE = 40;
    const SIZE_CEDULA = 9;
    const SIZE_NOMBRE = 40;
    const SIZE_APELLIDO = 40;

    // Enumeradores
    const ROL_ADMINISTRADOR = '1';
    const ROL_OPERADOR = '2';
    const ROL_ESTUDIANTE = '3';
    const GENERO_M = 'M';
    const GENERO_F = 'F';

    // Mensajes de Error y Advertencia
    const INVALID_LOGIN = "Usuario o Constraseña Incorrecto";
    const INVALID_ID = "ID de Usuario Inválido";
    const INVALID_USUARIO = "Usuario Inválido";
    const INVALID_CLAVE = "Clave Inválida";
    const INVALID_CEDULA = "Cédula Inválida";
    const INVALID_CORREO = "Correo Inválido";
    const INVALID_NOMBRE = "Nombre Inválido";
    const INVALID_APELLIDO = "Apellido Inválido";
    const INVALID_ROL = "Rol Inválido";
    const INVALID_GENERO = "Género Inválido";
    const INVALID_FECHA_NACIMIENTO = "Fecha de Nacimiento Inválida";
    const INVALID_FECHA_REGISTRO = "Fecha de Registro Inválida";
    const YET_USUARIO = "Usuario Existente con ese Nombre";
    const YET_CEDULA = "Usuario Existente con esa Cédula";
    const YET_CORREO = "Usuario Existente con ese Correo Electrónico";
    const NOT_USUARIO = "Usuario no Existente";
    const UNEXPECTED_CREACION = "Error Inesperado en la Creación de Usuario";
    const UNEXPECTED_UPDATE = "Error Inesperado en la Actualización de Usuario";
    const UNEXPECTED_DELETE = "Error Inesperado en la Eliminación de Usuario";

    // Mensajes Exitosos
    const VALID = "Campo Válido";
    const SUCCESS = "Acción Realizada Exitosamente";

    // Constructor
    public function __construct()
    {
        parent::__construct();
    }

    //----------------------------------------------------------------
    //
    //  Creación de un Usuario
    //
    //----------------------------------------------------------------

    // NOTA: Se crea al usuario, primero validando cada dato

    public function crearUsuario(string $usuario_, string $clave_, string $cedula_, string $correo_, string $rol_, string $nombre_, string $apellido_, string $genero_, array $fechaNacimiento_, array $fechaRegistro_)
    {
        // Validación de los Datos Entrantes
        $valides = array();

        $valides[0] = $this->validarUsuario($usuario_);
        $valides[1] = $this->validarCedula($cedula_);
        $valides[2] = $this->validarClave($clave_);
        $valides[3] = $this->validarNombre($nombre_);
        $valides[4] = $this->validarApellido($apellido_);
        $valides[5] = $this->validarRol($rol_);
        $valides[6] = $this->validarGenero($genero_);
        $valides[7] = $this->validarCorreo($correo_);

        foreach ($valides as $val) {
            if ($val != GestorUsuario::VALID) {
                return $val;
            }
        }

        $fechaNacimiento = formatearFecha($fechaNacimiento_['dia'], $fechaNacimiento_['mes'], $fechaNacimiento_['año']);

        if ($fechaNacimiento == GestorPeriodoAcademico::INVALID_FECHA) {
            return GestorUsuario::INVALID_FECHA_NACIMIENTO;
        }

        $fechaRegistro = formatearFecha($fechaRegistro_['dia'], $fechaRegistro_['mes'], $fechaRegistro_['año']);

        if ($fechaRegistro == GestorPeriodoAcademico::INVALID_FECHA) {
            return GestorUsuario::INVALID_FECHA_REGISTRO;
        }

        $key = GestorUsuario::AES_KEY;

        $id = $this->obtenIndice();

        // Consulta para la Inserción en la BD, dado que los Datos son Válidos
        /*
        $sql = "INSERT INTO usuarios
                SET id = '{$id}' usuario = '{$usuario_}', clave = aes_encrypt('{$clave_}', '{$key}'), cedula = '{$cedula_}', rol = '{$rol_}', nombre = '{$nombre_}', apellido = '{$apellido_}', genero = '{$genero_}', fecha_nacimiento = '{$fechaNacimiento}', fecha_registro = '{$fechaRegistro}'";
        */

        /*
        $sql = "INSERT INTO usuarios (id, usuario, fecha_registro, rol, nombre, apellido, genero, cedula, fecha_nacimiento, clave) VALUES ( '{$id}', '{$usuario_}', aes_encrypt('{$clave_}', '{$key}'), '{$cedula_}', '{$rol_}', '{$nombre_}', '{$apellido_}', '{$genero_}', '{$fechaNacimiento}', '{$fechaRegistro}' )";
        */

        $sql = "INSERT INTO usuarios
                    (id, usuario, clave, cedula, correo, rol, nombre, apellido, genero, fecha_nacimiento, fecha_registro) 
                VALUES 
                    ('{$id}', '{$usuario_}', aes_encrypt('{$clave_}', '{$key}'), '{$cedula_}', '{$correo_}', '{$rol_}', '{$nombre_}', '{$apellido_}', '{$genero_}', '{$fechaNacimiento}', '{$fechaRegistro}')";

        // resultado
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
            return GestorUsuario::UNEXPECTED_CREACION;
        }
    }

    //----------------------------------------------------------------
    //
    //   Obtener Datos de un Usuario
    //
    //----------------------------------------------------------------

    public function obtenerDatosUsuario(string $usuario_, string $clave_)
    {
        $usuario = darComillas((string)$usuario_);
        $clave = darComillas($clave_);
        $key = darComillas(GestorUsuario::AES_KEY);

        // Consulta de Datos para Cierto Usuario con su Clave
        $sql = "SELECT id, usuario, AES_DECRYPT(clave, $key) AS clave, cedula, rol, nombre, apellido, genero, fecha_nacimiento, fecha_registro 
                FROM usuarios 
                WHERE (clave = AES_ENCRYPT($clave, $key) AND usuario = $usuario)
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
            return GestorUsuario::INVALID_LOGIN;
        }

        unset($registro['clave']);

        // NOTA: Por seguridad, se quita la clave del usuario

        return $registro;
    }

    // Retorno del Registro de un usuario que Coincide con un id Dado
    public function obtenerUsuarioPorID($id_)
    {
        $id = darComillas($id_);

        // Consulta del Registro que Coincide con el id Dado
        $sql = "SELECT usuario 
                FROM usuarios 
                WHERE id = $id
                    LIMIT 1";

        // resultado
        $registro = null;

        try {
            $consulta = $this->db->query($sql);
            $registro = $consulta->getRowArray();
        } catch (\Throwable $th) {
            //throw $th;
        }

        if ($registro == null) {
            return GestorUsuario::INVALID_ID;
        }

        return $registro['usuario'];
    }

    // Validación de Existencia de un Usuario
    public function existeUsuario(string $usuario_)
    {
        $usuario = darComillas($usuario_);

        // Consulta para Comprobar la Existencia de un Registro con un Determinado usuario
        $sql = "SELECT usuario
                FROM usuarios 
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

        // Comprobación de la Existencia del Registro con ese usuario
        if ($registro == null) {
            return false;
        } else {
            return true;
        }
    }

    // Comprobación de la Existencia de un usuario con una Determinada cedula
    public function existeCedula(string $cedula_)
    {
        $cedula = darComillas($cedula_);

        // Consulta para Comprobar la Existencia de un Registro con esa cedula
        $sql = "SELECT cedula 
                FROM usuarios 
                WHERE cedula = $cedula 
                    LIMIT 1";

        // resultado
        $registro = null;

        try {
            $consulta = $this->db->query($sql);
            $registro = $consulta->getRowArray();
        } catch (\Throwable $th) {
            //throw $th;
        }

        // Comprobación de la Existencia de la Cedula
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

    // obtiene el siguiente índice desocupado, basado en el ID más alto existente
    private function obtenIndice()
    {
        // Consulta el último ID registrado
        $sql = "SELECT MAX(id) AS id FROM usuarios";

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
    //  Modificar Datos de un Usuario
    //
    //----------------------------------------------------------------



    //----------------------------------------------------------------
    //
    //  Eliminación de un Usuario
    //
    //----------------------------------------------------------------

    public function eliminarUsuario($usuario_)
    {
        $usuario = darComillas($usuario_);

        // Consulta de Eliminación del Registro del usuario Indicado
        $sql = "DELETE FROM usuarios 
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
            return GestorUsuario::SUCCESS;
        } else {
            return GestorUsuario::NOT_USUARIO;
        }
    }

    //----------------------------------------------------------------
    //
    //  Validar Datos de Usuario
    //
    //----------------------------------------------------------------

    // Validación de usuario (Índice) - Si tiene el tamaño correcto y no existe aún
    private function validarUsuario(string $usuario)
    {
        return validarIndice($usuario, GestorUsuario::SIZE_USUARIO, GestorUsuario::VALID, GestorUsuario::INVALID_USUARIO, $this->existeUsuario($usuario), GestorUsuario::YET_USUARIO);
    }

    // Validación de cedula (Índice) - Si tiene el tamaño correcto y no existe aún
    private function validarCedula(string $cedula)
    {
        return validarIndice($cedula, GestorUsuario::SIZE_CEDULA, GestorUsuario::VALID, GestorUsuario::INVALID_CEDULA, $this->existeCedula($cedula), GestorUsuario::YET_CEDULA);
    }

    // Validación de correo (Índice) - Si tiene el tamaño correcto y no existe aún
    private function validarCorreo(string $correo) {
        return validarIndice($correo, GestorUsuario::SIZE_CORREO, GestorUsuario::VALID, GestorUsuario::INVALID_CORREO, $this->existeCorreo($correo), GestorUsuario::YET_CORREO);
    }

    // Validación de clave (Campo) - Si tiene el tamaño correcto
    private function validarClave(string $clave)
    {
        return validarCampo($clave, GestorUsuario::SIZE_CLAVE, GestorUsuario::VALID, GestorUsuario::INVALID_CLAVE);
    }

    // Validación de nombre (Campo) - i tiene el tamaño correcto
    private function validarNombre(string $nombre)
    {
        return validarCampo($nombre, GestorUsuario::SIZE_NOMBRE, GestorUsuario::VALID, GestorUsuario::INVALID_NOMBRE);
    }

    // Validación de apellido (Campo) - Si tiene el tamaño correcto
    private function validarApellido(string $apellido)
    {
        return validarCampo($apellido, GestorUsuario::SIZE_APELLIDO, GestorUsuario::VALID, GestorUsuario::INVALID_APELLIDO);
    }

    // Validación de rol (Enumerador) - Si es una de las opciones
    private function validarRol(string $rol)
    {
        $roles = array(GestorUsuario::ROL_ESTUDIANTE, GestorUsuario::ROL_OPERADOR);

        // $roles = array(GestorUsuario::ROL_ESTUDIANTE, GestorUsuario::ROL_OPERADOR, GestorUsuario::ROL_ADMINISTRADOR);

        return validarEnumerador($rol, $roles, GestorUsuario::VALID, GestorUsuario::INVALID_ROL);
    }

    // Validación de genero (Enumerador) - Si es una de las opciones
    private function validarGenero(string $genero)
    {
        $generos = array(GestorUsuario::GENERO_F, GestorUsuario::GENERO_M);

        return validarEnumerador($genero, $generos, GestorUsuario::VALID, GestorUsuario::INVALID_GENERO);
    }
}
