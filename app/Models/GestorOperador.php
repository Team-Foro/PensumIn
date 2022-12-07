<?php

namespace App\Models;

use CodeIgniter\Model;

include_once("Utiles.php");
include_once("Validacion.php");

class GestorOperador extends Model
{
    // Constructor
    public function __construct() {
        parent::__construct();
    }

    // Función para la Obtención de los Datos de los Usuarios Operadores y Estudiantes
    public function obtenerUsuarios () {
        // Consulta de los Registros en la Tabla usuarios
        $sql = "SELECT * 
                FROM usuarios
                WHERE rol = 3";

        $consulta = $this->db->query($sql);
        $registro = $consulta->getResult();

        // Resultado
        $registro = null;

        try {
            $consulta = $this->db->query($sql);
            $registro = $consulta->getResult();
        } catch (\Throwable $th) {
            //throw $th;
        }

        return $registro;
    }

    // Función para la Obtención de Datos de un Registro Específico
    public function obtenerUsuario($id_) {
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

    // Función para la Obtención de Datos de un Registro Específico de un Estudiante
    public function obtenerEstudiante($usuario_) {
        // Consulta para la Obtención del Registro con el id Dado
        $sql = "SELECT *
                FROM estudiantes
                WHERE usuario = $usuario_
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
            return 'Usuario Estudiante no Encontrado';
        }

        return $registro;
    }

    // Función para la Actualización de Datos de un Usuario
    public function actualizarUsuario(string $id_, string $usuario_, string $cedula_, string $correo_, string $nombre_, string $apellido_, string $genero_, array $fechaNacimiento_) {
        // Registro Actual del Usuario con el id Dado
        $registro = $this->obtenerUsuario($id_);

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

    // Función para la Eliminación de un Usuario
    public function eliminarUsuario($id_) {
        $id = darComillas($id_);

        // DATOS DEL USUARIO A ELIMINAR
        $registro = $this->obtenerUsuario($id_);            // Registro con el id Dado
        $usuario = darComillas($registro['usuario']);       // Campo usuario del Registro con el id Dado
        $rol = $registro['rol'];                            // Campo rol del Registro con el id Dado

        // CONDICIONAL: Eliminación de los Datos de Estudiante si el Usuario a Eliminar posee ese Rol
        if ($rol == '3') {
            // DATOS DEL ESTUDIANTE A ELIMINAR
            $registroE = $this->obtenerEstudiante($usuario);        // Registro con el usuario Dado
            $id_estudiante = $registroE['id'];                      // Campo id del Registro de Estudiante con el usuario Dado 

            // Consulta para la Eliminación del Pensum
            $sql1 = "DELETE FROM pensums
                    WHERE id_estudiante = $id_estudiante";

            // Resultado
            $afectados1 = 0;

            try {
                $this->db->query($sql1);
                $afectados1 = $this->db->affectedRows();
            } catch (\Throwable $th) {
                //throw $th;
            }

            // Comprobación de una Correcta Eliminación
            if ($afectados1 > 0) {
                // Consulta para la Eliminación de los Datos de Estudiante
                $sql2 = "DELETE FROM estudiantes
                        WHERE usuario = $usuario";

                // Resultado
                $afectados2 = 0;
                
                try {
                    $this->db->query($sql2);
                    $afectados2 = $this->db->affectedRows();
                } catch (\Throwable $th) {
                    //throw $th;
                }

                // Comprobación de una Correcta Eliminación
                if ($afectados2 > 0) {
                    // Consulta para la Eliminación de los Datos de Usuario
                    $sql3 = "DELETE FROM usuarios
                            WHERE id = $id";

                    // Resultado
                    $afectados3 = 0;
                        
                    try {
                        $this->db->query($sql3);
                        $afectados3 = $this->db->affectedRows();
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                    
                    // Comprobación de una Correcta Eliminación
                    if ($afectados3 > 0) {
                        return GestorUsuario::SUCCESS;
                    } else {
                        return GestorUsuario::UNEXPECTED_DELETE;
                    }
                } else {
                    return GestorUsuario::UNEXPECTED_DELETE;
                }
            } else {
                return GestorUsuario::UNEXPECTED_DELETE;
            }
        }

        // Consulta para la Eliminación del Registro con el id Dado
        $sql = "DELETE FROM usuarios
                WHERE id = $id";
        
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
            return GestorUsuario::UNEXPECTED_DELETE;
        }
    }

    //-----------------------------------------------------------------
    //
    //      Validaciones para la Actualización
    //
    //------------------------------------------------------------------

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

    // Validación de usuario (Índice) - Si tiene el tamaño correcto y no existe aún
    private function validarUsuario(string $usuario) {
        return validarIndice($usuario, GestorUsuario::SIZE_USUARIO, GestorUsuario::VALID, GestorUsuario::INVALID_USUARIO, $this->existeUsuario($usuario), GestorUsuario::YET_USUARIO);
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
