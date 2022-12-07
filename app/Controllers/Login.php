<?php

namespace App\Controllers;

use App\Models\GestorCarreraMencion;
use App\Models\GestorEstudiante;
use App\Models\GestorPensum;
use App\Models\GestorUsuario;

class Login extends BaseController
{
    // Método de la Vista de Login
    public function preLogin() {
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
        $titulo = [ 'titulo' => 'Pensum In - Login' ];

        $vistaLogin = $this->obtenerVistaLogin($mess, $titulo);

        return($vistaLogin);
    }

    // Método de Login de Usuarios
    public function login(){
        $usuario = $this->request->getPost('usuario');
        $clave = $this->request->getPost('clave');
        $modulo = new GestorUsuario();

        // Obtención de los Datos del Usuario (Si este existe)
        $datosUsuario = $modulo->obtenerDatosUsuario($usuario, $clave);

        // Comprobación de un Usuario Válido
        if ($datosUsuario == GestorUsuario::INVALID_LOGIN) {
            // Usuario Inválido
            return redirect()->to(base_url('/ingreso'))->with("mensaje", "(!) ".GestorUsuario::INVALID_LOGIN);
        } else {
            // Usuario Válido
            
            $data = [
                "usuario" => $datosUsuario['usuario'],
                "datosUsuario" => $datosUsuario,
                "rol" => $datosUsuario['rol']
            ];

            $session = session();

            $session->set($data);

            // Redirección del Usuario según su Rol
            switch (session('rol')) {
                case GestorUsuario::ROL_ADMINISTRADOR:
                    return redirect()->to(base_url('/admin'))->with("mensaje", Home::SUCCESS_LOGIN);

                case GestorUsuario::ROL_OPERADOR:
                    return redirect()->to(base_url('/operador'))->with("mensaje", Home::SUCCESS_LOGIN);

                case GestorUsuario::ROL_ESTUDIANTE:

                    $gestorE = new GestorEstudiante();
                    // datos del estudiante
                    $datosEstudiante = $gestorE->obtenDatosEstudiante(session("usuario"));

                    $gestorCM = new GestorCarreraMencion();
                    // datos de carrera
                    $datosCarrera = $gestorCM->obtenDatosCarrera($datosEstudiante['codigo_carrera']);
                    // datos de mención
                    $datosMencion = $gestorCM->obtenDatosMencion($datosEstudiante['codigo_mencion']);

                    // yes!

                    //formatear a salida
                    $data = [
                        "estudiante" => $datosEstudiante['id'],
                        "datosEstudiante" => $datosEstudiante,
                        "datosCarrera" => $datosCarrera, 
                        "datosMencion" => $datosMencion
                    ];

                    session()->set($data);

                    return redirect()->to(base_url('/pensum'))->with("mensaje", Home::SUCCESS_LOGIN);

                default:
                    return redirect()->to(base_url('/ingreso'))->with("mensaje", Home::UNEXPECTED);
            }
        }
    }

    //-----------------------------------------------------------------
    //
    //      Construcción de vistas
    //
    //------------------------------------------------------------------

    private function obtenerVistaLogin(array $mensaje, array $titulo){
        $vista = view('secciones/header', $titulo).
                view('secciones/mensaje', $mensaje).
                view('login').
                view('secciones/botonBack').
                view('secciones/footer');

        return $vista;
    }

    // NOTA: En la vista, se necesitan formulario y botón para el POST, con un mostrador de aviso y botón de volver (Inicio)
}
