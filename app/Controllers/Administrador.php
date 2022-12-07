<?php

namespace App\Controllers;

use App\Models\GestorAdmin;
use App\Models\GestorUsuario;

class Administrador extends BaseController
{
    // Método Principal de Inicio del Administrador
    public function inicio() {
        // Mensaje
        $mensaje = session('mensaje');
        $mess = array();

        // Condicional para Mandar el Mensaje (En caso de aviso)
        if ($mensaje == null) {
            $mess = [ 'mensaje' => Home::NULO ];
        } else {
            $mess = [ 'mensaje' => $mensaje ];
        }

        // Instancia del Gestor
        $modulo = new GestorAdmin;
        // Obtención de los Datos de los Usuarios
        $registros = $modulo->obtenerUsuarios();

        // ARREGLOS NECESARIOS
        $titulo = [ 'titulo' => 'Pensum In - Administrador' ];      // Título de la Pestaña
        $usuarios = [ 'registros' => $registros ];          // Registros en la Tabla usuario
        
        // Vista con el Inicio
        $vistaAdmin = $this->obtenerVistaAdmin($titulo, $mess, $usuarios);

        return($vistaAdmin);
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
        $modulo = new GestorAdmin;
        // Obtención de los Datos del Usuario con el id Dado
        $registro = $modulo->obtenerUsuario($id);

        // Título de la Pestaña
        $titulo = [ 'titulo' => 'Pensum In - Administrador' ];

        $vistaActualizar = $this->obtenerVistaActualizar($titulo, $mess, $registro);
        
        return ($vistaActualizar);
    }

    // Función para la Actualización de Datos de un Usuario
    public function actualizarUsuario() {
        // Obtención de los Datos del Formulario (Parámetros)
        $id = $_POST['id'];
        $usuario = $_POST['usuario'];
        $cedula = $_POST['cedula'];
        $correo = $_POST['correo'];
        $rol = $_POST['rol'];
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $genero = $_POST['genero'];
        $fechaNacimiento = [
            "dia" => $_POST['dia'],
            "mes" => $_POST['mes'],
            "año" => $_POST['año'],
        ];

        // Instancia del Gestor
        $modulo = new GestorAdmin;
        // Actualización del Usuario con los Datos del Formulario (Parámetros)
        $respuesta = $modulo->actualizarUsuario($id, $usuario, $cedula, $correo, $rol, $nombre, $apellido, $genero, $fechaNacimiento);

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

            return redirect()->to(base_url().'/admin')->with('mensaje', $respuesta);
        } else {
            return redirect()->to(base_url().'/admin/editar/'.$id)->with('mensaje', $respuesta);
        }
    }

    // Función para la Eliminación de un Usuario
    public function eliminarUsuario($id) {
        // Instancia del Gestor
        $modulo = new GestorAdmin;
        // Actualización del Usuario con los Datos del Formulario (Parámetros)
        $respuesta = $modulo->eliminarUsuario($id);

        // CONDICIONAL: Redirreción según el Éxito de la Actualización
        if ($respuesta == GestorUsuario::SUCCESS) {
            return redirect()->to(base_url().'/admin')->with('mensaje', $respuesta);
        } else {
            return redirect()->to(base_url().'/admin')->with('mensaje', $respuesta);
        }
    }

    //-----------------------------------------------------------------
    //
    //      Construcción de Vistas
    //
    //------------------------------------------------------------------

    private function obtenerVistaAdmin(array $titulo, array $mensaje, array $usuarios) {
        $vista = view('secciones/header', $titulo).
                view('secciones/navAdmin').
                view('secciones/mensaje', $mensaje).
                view('admin', $usuarios).
                view('secciones/footer');

        return $vista;
    }

    private function obtenerVistaActualizar(array $titulo, array $mensaje, array $data) {
        $vista = view('secciones/header', $titulo).
                view('secciones/navAdmin').
                view('secciones/mensaje', $mensaje).
                view('actualizarAdmin', $data).
                view('secciones/footer');

        return $vista;    
    }
}
