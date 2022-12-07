<?php

namespace App\Controllers;

use App\Models\GestorUsuario;

class Home extends BaseController
{
    // Constantes Necesarias
    const NULO = "Nulo";
    const NO_LOGEADO = "Usuario No Registrado.\n Por favor, inicie sesión primero";
    const INVALID_AUTORIDAD = "No tiene la Autoridad para Realizar esta Acción";
    const SUCCESS_LOGIN = "Sesión Iniciada Exitosamente";
    const SUCCESS_REGISTER = "Usuario Registrado Exitosamente";
    const SUCCESS_CLOSE = "Sesión Cerrada Exitosamete";
    const UNEXPECTED = "Error Inesperado";
    
    // De Estudiante
    const SUCCESS_APROBAR_1 = "Todas las Materias aprobadas exitosamente (";
    const MIXED_APROBAR_1 = "Materias aprobadas con errores (";
    const FAIL_APROBAR_1 = "No se aprobaron las Materias (";
    const SUCCESS_REPROBAR_1 = "Todas las Materias reprobadas exitosamente (";
    const MIXED_REPROBAR_1 = "Materias reprobadas con errores (";
    const FAIL_REPROBAR_1 = "No se reprobaron las Materias (";

    const APROBAR_2 = " de ";
    const APROBAR_3 = ")";

    const MODO_APROBAR = "Aprobar";
    const MODO_REPROBAR = "Reprobar";
    const NOT_MODO = "Modo de actualización de pensum no reconocido";

    // NOTA: Todos los mensajes se refieran desde esta clase

    // Método Principal
    public function index() {
        // Mensaje de la Acción Anterior (En caso de haber)
        $mensaje = session('mensaje');
        $mess = array();

        // Condicional para Mandar el Mensaje
        if ($mensaje == null) {
            $mess = [ 'mensaje' => Home::NULO ];
        } else {
            $mess = [ 'mensaje' => $mensaje ];
        }

        // Título de la Pestaña
        $titulo = [ 'titulo' => 'Pensum In' ];

        // Vista con el Inicio
        $vistaHome = $this->obtenerVistaHome($mess, $titulo);

        return($vistaHome);
    }

    // Método de Salida de Sesión
    public function salir() {
        $session = session();

        $session->destroy();

        return redirect()->to(base_url('/'))->with("mensaje", Home::SUCCESS_CLOSE);
    }

    //-----------------------------------------------------------------
    //
    //      Construcción de Vistas
    //
    //------------------------------------------------------------------

    private function obtenerVistaHome(array $mensaje, array $titulo) {
        $vista = view('secciones/header', $titulo).
                view('secciones/mensaje', $mensaje).
                view('inicio').
                view('secciones/footer');

        return $vista;
    }

    // NOTA: En la vista, se necesitan botón de "ingresar" y "registrar", con un mostrador de avisos ($mensaje)
}
