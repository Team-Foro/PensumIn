<?php

namespace App\Controllers;

use App\Models\GestorOperador;
use App\Models\GestorUsuario;

class Operador extends BaseController
{
    // Método Principal de Inicio del Operador
    public function inicio() {
        // Mensaje
        $mensaje = session('mensaje');
        $mess = array();

        // Condicional para Mandar el Mensaje (En caso de aviso)
        if ($mensaje == null) {
            $mess = ['mensaje' => Home::NULO];
        } else {
            $mess = ['mensaje' => $mensaje];
        }

        // Instancia del Gestor
        $modulo = new GestorOperador;
        // Obtención de los Datos de los Usuarios
        $registros = $modulo->obtenerUsuarios();

        // ARREGLOS NECESARIOS
        $titulo = [ 'titulo' => 'Pensum In - Operador' ];       // Título de la Pestaña
        $usuarios = [ 'registros' => $registros ];              // Registros en la Tabla usuario
        // Vista con el Inicio
        $vistaOp = $this->obtenerVistaOp($titulo, $mess, $usuarios);

        return($vistaOp);
    }

    // Función para la Obtención de Datos de un Registro Específico para Mostrarse en la Vista
    public function obtenerUsuario($id) {
        // Mensaje
        $mensaje = session('mensaje');
        $mess = array();

        // Condicional para Mandar el Mensaje (En caso de aviso)
        if ($mensaje == null) {
            $mess = ['mensaje' => Home::NULO];
        } else {
            $mess = ['mensaje' => $mensaje];
        }

        // Instancia del Gestor
        $modulo = new GestorOperador;
        // Obtención de los Datos del Usuario con el id Dado
        $registro = $modulo->obtenerUsuario($id);

        // Título de la Pestaña
        $titulo = [ 'titulo' => 'Pensum In - Operador' ];      

        $vistaActualizar = $this->obtenerVistaActualizar($titulo, $mess, $registro);
        
        return ($vistaActualizar);
    }

    // Función para la Actualización de Datos de un Usuario
    public function actualizarUsuario() {
        // Obtención de los Datos del Formulario (Parámetros)
        $id = $_POST['id'];
        $usuario = $_POST['usuario'];
        $cedula = $_POST['cedula'];
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $correo = $_POST['correo'];
        $genero = $_POST['genero'];
        $fechaNacimiento = [
            "dia" => $_POST['dia'],
            "mes" => $_POST['mes'],
            "año" => $_POST['año'],
        ];

        // Instancia del Gestor
        $modulo = new GestorOperador;
        // Actualización del Usuario con los Datos del Formulario (Parámetros)
        $respuesta = $modulo->actualizarUsuario($id, $usuario, $cedula, $correo, $nombre, $apellido, $genero, $fechaNacimiento);

        // CONDICIONAL: Redirreción según el Éxito de la Actualización
        if ($respuesta == GestorUsuario::SUCCESS) {
            $idSession = session()->get('datosUsuario')['id'];

            // CONDICIONAL: Actualización de los Datos del Administrador
            if ($id == $idSession) {
                $datosActualizados = $modulo->obtenerUsuario($id);

                // Arreglo con los Datos actualizados
                $data = [
                    "id" => $datosActualizados['id'],
                    "datosUsuario" => $datosActualizados,
                    'rol' => $datosActualizados['rol']
                ];

                // Asignación de los Datos Actualizados a la Sesión
                session()->set($data);
            }
            
            return redirect()->to(base_url().'/operador')->with('mensaje', $respuesta);
        } else {
            return redirect()->to(base_url().'/operador/editar/'.$id)->with('mensaje', $respuesta);
        }
    }

    // Función para la Eliminación de un Usuario
    public function eliminarUsuario($id) {
        // Instancia del Gestor
        $modulo = new GestorOperador;
        // Actualización del Usuario con los Datos del Formulario (Parámetros)
        $respuesta = $modulo->eliminarUsuario($id);

        // CONDICIONAL: Redirreción según el Éxito de la Actualización
        if ($respuesta == GestorUsuario::SUCCESS) {
            return redirect()->to(base_url().'/operador')->with('mensaje', $respuesta);
        } else {
            return redirect()->to(base_url().'/operador')->with('mensaje', $respuesta);
        }
    }
    
    //-----------------------------------------------------------------
    //
    //      Construcción de Vistas
    //
    //------------------------------------------------------------------
    
    private function obtenerVistaOp(array $titulo, array $mensaje, array $usuarios) {
        $vista = view('secciones/header', $titulo).
                view('secciones/navOp').
                view('secciones/mensaje', $mensaje).
                view('operador', $usuarios).
                view('secciones/footer');
    
        return $vista;
    }

    private function obtenerVistaActualizar(array $titulo, array $mensaje, array $data) {
        $vista = view('secciones/header', $titulo).
                view('secciones/navOp').
                view('secciones/mensaje', $mensaje).
                view('actualizarOp', $data).
                view('secciones/footer');

        return $vista;    
    }
}
