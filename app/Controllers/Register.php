<?php

namespace App\Controllers;

use App\Models\GestorUsuario;
use App\Models\GestorEstudiante;
use App\Models\GestorPensum;
use App\Models\GestorPeriodoAcademico;

class Register extends BaseController
{
    // Método de la Vista del Registro
    public function preRegisterE()
    {
        // Mensaje
        $mensaje = session('mensaje');
        $mess = array();

        // Condicional para Mandar el Mensaje (En caso de aviso)
        if ($mensaje == null) {
            $mess = ['mensaje' => Home::NULO];
        } else {
            $mess = ['mensaje' => $mensaje];
        }

        // Título de la Pestaña
        $titulo = ['titulo' => 'Pensum In - Registro'];

        $vistaRegister = $this->obtenerVistaRegisterE($mess, $titulo);

        return ($vistaRegister);
    }

    // Método de Registro de Usuarios
    public function register()
    {
        /* 
        NOTA: Se consideran los distintos tipos de registros:
        
        Usuario - Siempre
        Estudiante - rol = 3 y datos de estudiante
        Operador - rol = 2, si session('rol') = 1 -> admin
        */

        // Tipo de Usuario a Registrar
        $rol = $this->request->getPost('rol');

        // Condicional para Determinar el Proceso según el Rol
        if ($rol == GestorUsuario::ROL_ESTUDIANTE) {
            // Registro para un Usuario Estudiante

            $resultadoUsuario = $this->registrarUsuario($rol);

            if ($resultadoUsuario == GestorUsuario::SUCCESS) {
                // Usuario Registrado Exitosamente

                $usuario = $this->request->getPost('usuario');

                // Registros para Datos de Estudiante
                $resultadoEstudiante = $this->registrarEstudiante($usuario);

                if ($resultadoEstudiante == GestorEstudiante::SUCCESS) {

                    // Estudiante Registrado Exitosamente

                    // generar el pensum de este nuevo estudiante
                    $resultadoPensum = $this->generarPensum($usuario);

                    if ($resultadoPensum == GestorPensum::SUCCESS) {
                        // pensum registrado correctamente

                        // nothing more, rigth?

                        // Redirección al Login, con un Nuevo Usuario, Estudiante y Pensum Creado
                        return redirect()->to(base_url('/ingreso'))->with("mensaje", Home::SUCCESS_REGISTER);
                    } else {
                        // pensum fallido

                        // rollback:

                        // Eliminación del Usuario y Estudiante Creados
                        $this->descartarEstudianteUsuario($usuario);
                        $this->descartarUsuario($usuario);

                        // Rediredicción al Registro
                        return redirect()->to(base_url('/registro'))->with("mensaje", "(!) " . $resultadoPensum);
                    }
                } else {
                    // Falla al Registrar Estudiante

                    // Eliminación del Usuario Creado
                    $this->descartarUsuario($usuario);

                    // Rediredicción al Registro
                    return redirect()->to(base_url('/registro'))->with("mensaje", "(!) " . $resultadoEstudiante);
                }
            } else {
                // Falla al Crear Usuario

                // Rediredicción al Registro
                return redirect()->to(base_url('/registro'))->with("mensaje", "(!) " . $resultadoUsuario);
            }
        } elseif ($rol == GestorUsuario::ROL_OPERADOR and session('rol') == GestorUsuario::ROL_ADMINISTRADOR) {
            // Registro para un Usuario Operador por un Administrador
            $resultadoUsuario = $this->registrarUsuario($rol);

            if ($resultadoUsuario == GestorUsuario::SUCCESS) {
                // Usuario Registrado Exitosamente
                return redirect()->to(base_url('/admin'))->with("mensaje", Home::SUCCESS_REGISTER);
            } else {
                // Falla al Crear Usuario
                return redirect()->to(base_url('/admin'))->with("mensaje", "(!) " . $resultadoUsuario);
            }
        } else {
            // Otro tipo de Registro

            // Redirección al Inicio de la Página
            return redirect()->to(base_url('/'))->with("mensaje", "(!) " . Home::INVALID_AUTORIDAD);
        }
    }

    // Registro de los Datos de un Usuario
    private function registrarUsuario(string $rol_)
    {
        // Instancias de los Gestores
        $gestorPA = new GestorPeriodoAcademico();
        $gestorU = new GestorUsuario();

        // Datos Obtenidos del POST
        $usuario = $this->request->getPost('usuario');
        $clave = $this->request->getPost('clave');
        $nombre = $this->request->getPost('nombre');
        $apellido = $this->request->getPost('apellido');
        $correo = $this->request->getPost('correo');
        $genero = $this->request->getPost('genero');
        $cedula = $this->request->getPost('cedula');
        $dia = $this->request->getPost('dia');
        $mes = $this->request->getPost('mes');
        $año = $this->request->getPost('año');
        $fechaNacimiento = ['dia' => $dia, 'mes' => $mes, 'año' => $año];

        // Datos Automáticos
        $fechaRegistro = $gestorPA->obtenerFechaActualArray();
        $rol = $rol_;

        // Creación del Usuario con el Rol Dado
        return $gestorU->crearUsuario($usuario, $clave, $cedula, $correo, $rol, $nombre, $apellido, $genero, $fechaNacimiento, $fechaRegistro);

        /*
        NOTA: Como mensaje, puede devolver:

        GestorUsuario::SUCCESS
        GestorUsuario::UNEXPECTED_CREACION
        GestorUsuario::INVALID_*
        */
    }

    // Registro de los Datos para un Usuario con Rol de Estudiante
    private function registrarEstudiante(string $usuario_)
    {
        // Instancia de los Gestores
        //$gestorPA = new GestorPeriodoAcademico();
        $gestorE = new GestorEstudiante();

        // Datos Obtenidos del POST
        $codigoCarrera = $this->request->getPost('codigo_carrera');
        $codigoMencion = $this->request->getPost('codigo_mencion');
        $semesre = $this->request->getPost('semestre');
        $añoCohorte = $this->request->getPost('año_cohorte');
        $grupoCohorte = $this->request->getPost('grupo_cohorte');

        // Datos Automáticos
        $uc = (string)GestorEstudiante::DEFAULT_UC;
        $estado = GestorEstudiante::ESTADO_ACTIVO;
        $usuario = $usuario_;

        // Creación del Estudiante para el Usuario Dado con Dicho Rol
        return $gestorE->crearEstudiante($usuario, $codigoCarrera, $codigoMencion, $semesre, $añoCohorte, $grupoCohorte, $uc, $estado);

        /*
        NOTA: Como mensaje, puede devolver:

        GestorEstudiante::SUCCESS;
        GestorEstudiante::UNEXPECTED_CREACION;
        GestorEstudiante::INVALID_*
        */
    }

    private function generarPensum(string $usuario_)
    {
        // se geeará el pesum para su estudiante+carrera+mencion

        $gestorE = new GestorEstudiante();

        $datosEstudiante = $gestorE->obtenDatosEstudiante($usuario_);

        $gestorP = new GestorPensum();

        // crear y registrar el pensum

        //echo $datosEstudiante['id'], $datosEstudiante['codigo_carrera'], $datosEstudiante['codigo_mencion'];

        return $gestorP->crearPensum($datosEstudiante['id'], $datosEstudiante['codigo_carrera'], $datosEstudiante['codigo_mencion']);
    }

    // Eliminar al Usuario
    private function descartarUsuario($usuario_)
    {
        // Intancia del Gestor Usuario
        $gestorU = new GestorUsuario();

        $gestorU->eliminarUsuario($usuario_);
    }

    // Eliminar al Usuario
    private function descartarEstudianteUsuario($usuario_)
    {
        // Intancia del Gestor Usuario
        $gestorU = new GestorEstudiante();

        $gestorU->eliminarUsuarioEstudiante($usuario_);
    }

    //-----------------------------------------------------------------
    //
    //      Construcción de vistas
    //
    //------------------------------------------------------------------

    // Vista para el Registro de un Estudiante
    private function obtenerVistaRegisterE(array $mensaje, array $titulo)
    {

        $vista = view('secciones/header', $titulo).
                view('secciones/mensaje', $mensaje).
                view('registro').
                view('secciones/botonBack').
                view('secciones/footer');

        return $vista;
    }

    // NOTA: En la vista, se necesitan formulario y botón para el POST, con un mostrador de aviso y botón de volver (Inicio)
}
