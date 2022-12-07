<?php

namespace App\Controllers;

use App\Models\GestorCarreraMencion;
use App\Models\GestorPensum;
use App\Models\GestorEstudiante;
use App\Models\GestorUsuario;

class Estudiante extends BaseController
{
    // Método Principal de Inicio del Estudiante
    public function inicio()
    {
        // Título de la Pestaña
        $titulo = ['titulo' => 'Pensum In - Estudiante'];

        // Mensaje
        $mensaje = session('mensaje');
        $mess = array();

        // Condicional para Mandar el Mensaje (En caso de aviso)
        if ($mensaje == null) {
            $mess = ['mensaje' => Home::NULO];
        } else {
            $mess = ['mensaje' => $mensaje];
        }

        // obtener y registrar los datos de sesión y vista

        $gestorPensum = new GestorPensum();

        $formatoPensum = $gestorPensum->generarPensumFormato($gestorPensum->obtenPensumCompleto(session('usuario')), session('datosEstudiante')['codigo_carrera']);

        $factor15 = (string) $gestorPensum->recalcularFactor15v1(session("usuario"));

        $data = [
            "factor15" => ["factor15" => $factor15]
        ];

        session()->set($data);

        // cargar la vista según la carrera

        if (session('datosEstudiante')['codigo_carrera'] == GestorCarreraMencion::CARRERA_INGINF) {
            // Vista con el Inicio de Informática
            $vistaINF = $this->obtenerVistaINF($titulo, $mess, ['pensum' => $formatoPensum]);
            return $vistaINF;

        } else if(session('datosEstudiante')['codigo_carrera'] == GestorCarreraMencion::CARRERA_INGAMB) {
            // vista con el Inicio de Ambiental
            $vistaINF = $this->obtenerVistaAMB($titulo, $mess, ['pensum' => $formatoPensum]);
            return $vistaINF;
        }

        // datos del estudiante
        //session("estudiante") = id de estudiante
        //session("datosEstudiante") = array{datos de estudiante}
        //session("pensum") = array completo (NO MAS)

    }

    //-----------------------------------------------------------------
    //
    //      Funciones de Actualización del Pensum
    //
    //------------------------------------------------------------------
    

    public function actualizar() {
        //obtener los datos y el tipo
        $post = $codigoCarrera = $this->request->getPost(null);

        if(isset($post['Modo'])) {
            // existe modo
            $modo = $post['Modo'];
            unset($post['Modo']);
            $materias = $post;

            switch ($modo) {
                case Home::MODO_APROBAR:
                    // modo actualizar
                    return  $this->aprobar($materias);

                case Home::MODO_REPROBAR:
                    // modo reprobar
                    return  $this->reprobar($materias);

                    break;
                
                default:
                    // modo no reconocido

                    // redirigir a pensum con mensaje de error
                    return redirect()->to(base_url('/pensum'))->with("mensaje", Home::NOT_MODO);
            }

        } else {
            // actualización no reconocida

            // redirigir a pensum con mensaje de error
            return redirect()->to(base_url('/pensum'))->with("mensaje", Home::NOT_MODO);
        }
    }

    // para aprobar las materias seleccionadas en la vista
    public function aprobar(array $materias) {

        // PASO 0: recolectar y organizar los datos y objetos

        // el gestor del pensum personal
        $gestorPensum = new GestorPensum();
        $gestorEstudiante = new GestorEstudiante();

        // PASO 1: aprobar las materias

        // número inicial de cambios a hacer
        $modInicial = count($materias);
        // aprobar para el usuario actual, las materias seleccionadas y que se apliquen los cambios en BD
        $resultado1 = $gestorPensum->aprobarMaterias(session("usuario"), $materias, true);
        // número final de cambios realizados
        $modFinal = 0;
        
        if(is_int($resultado1)) {
            // sin errores
            $modFinal = $resultado1;
        } else {
            // con error

            // ..?
        }

        // PASO 2: recalcular las UC del estudiante y su progreso (UCA / UC_Carrera)

        // UCA inicial del estudiante
        $uca = (int)(((array) session("datosEstudiante"))['uc_acumulado']);
        // recalcular las UCA según las materias aprobadas del estudiante
        $resultado2_1 = $gestorPensum->recalcularUC(session("estudiante"));

        if(is_int($resultado2_1)) {
            // sin errores
            $uca = $resultado2_1;
        } else {
            // con error

            // ..?
        }

        // aplicar lo cambios en la tabla 'estudiantes' (!)
        $resultado2_2 = $gestorEstudiante->modificarUCA(session("estudiante"), $uca);

        if($resultado2_2 == GestorEstudiante::SUCCESS) {
            // sin errores
            $uca = $resultado2_2;
        } else {
            // con error

            // ..?
        }

        // PASO 3: recalcular los semestres para el Factor 15

        $resultado3 = $gestorPensum->recalcularFactor15v1(session("usuario"));
        $factor15 = "?";

        if(is_int($resultado3)) {
            // sin errores
            $factor15 = strval($resultado3);
        } else {
            // con error

            // ..?
        }

        // ...

        // PASO N: redirigir a inicio();

        // cargar la cookie con los datos actualizados del estudiante (sin validar)
        $data = [
            "datosEstudiante" => $gestorEstudiante->obtenDatosEstudiante(session("usuario")),
            "factor15" => $factor15
        ];

        session()->set($data);

        // mensaje según el caso
        $mensaje = "";

        if($modFinal == $modInicial) {
            $mensaje = Home::SUCCESS_APROBAR_1 . $modFinal . Home::APROBAR_2 . $modInicial . Home::APROBAR_3;
        } elseif($modFinal > 0) {
            $mensaje = Home::MIXED_APROBAR_1 . $modFinal . Home::APROBAR_2 . $modInicial . Home::APROBAR_3;
        } else {
            $mensaje = Home::FAIL_APROBAR_1 . $modFinal . Home::APROBAR_2 . $modInicial . Home::APROBAR_3;
        }

        ///*
        // redirección a la página de pensum
        return redirect()->to(base_url('/pensum'))->with("mensaje", $mensaje);
        //*/

        /*
        var_dump($materias);
        echo "<br>";
        var_dump($resultado1);
        echo "<br>";
        var_dump($resultado2_1);
        echo "<br>";
        var_dump($resultado2_2);
        echo "<br>";
        var_dump($resultado3);
        echo "<br>";
        //*/
    }

    // para reprobar las materias seleccionadas en la vista
    public function reprobar(array $materias) {
        // PASO 0: recolectar y organizar los datos y objetos

        // el gestor del pensum personal
        $gestorPensum = new GestorPensum();
        $gestorEstudiante = new GestorEstudiante();

        // PASO 1: aprobar las materias

        // número inicial de cambios a hacer
        $modInicial = count($materias);
        // aprobar para el usuario actual, las materias seleccionadas y que se apliquen los cambios en BD
        $resultado1 = $gestorPensum->reprobarMaterias(session("usuario"), $materias, true);
        // número final de cambios realizados
        $modFinal = 0;
        
        if(is_int($resultado1)) {
            // sin errores
            $modFinal = $resultado1;
        } else {
            // con error

            // ..?
        }

        // PASO 2: recalcular las UC del estudiante y su progreso (UCA / UC_Carrera)

        // UCA inicial del estudiante
        $uca = ((array) session("datosEstudiante"))['uc_acumulado'];
        // recalcular las UCA según las materias aprobadas del estudiante
        $resultado2_1 = $gestorPensum->recalcularUC(session("estudiante"));

        if(is_int($resultado2_1)) {
            // sin errores
            $uca = $resultado2_1;
        } else {
            // con error

            // ..?
        }

        // aplicar lo cambios en la tabla 'estudiantes' (!)
        $resultado2_2 = $gestorEstudiante->modificarUCA(session("estudiante"), $uca);

        if($resultado2_2 == GestorEstudiante::SUCCESS) {
            // sin errores
            $uca = $resultado2_2;
        } else {
            // con error

            // ..?
        }

        // PASO 3: recalcular los semestres para el Factor 15

        $resultado3 = $gestorPensum->recalcularFactor15v1(session("usuario"));
        $factor15 = "?";

        if(is_int($resultado3)) {
            // sin errores
            $factor15 = strval($resultado3);
        } else {
            // con error

            // ..?
        }

        // ...

        // PASO N: redirigir a inicio();

        // cargar la cookie con los datos actualizados del estudiante (sin validar)
        $data = [
            "datosEstudiante" => $gestorEstudiante->obtenDatosEstudiante(session("usuario")),
            "factor15" => $factor15
        ];

        session()->set($data);

        // mensaje según el caso
        $mensaje = "";

        if($modFinal == $modInicial) {
            $mensaje = Home::SUCCESS_REPROBAR_1 . $modFinal . Home::APROBAR_2 . $modInicial . Home::APROBAR_3;
        } elseif($modFinal > 0) {
            $mensaje = Home::MIXED_REPROBAR_1 . $modFinal . Home::APROBAR_2 . $modInicial . Home::APROBAR_3;
        } else {
            $mensaje = Home::FAIL_REPROBAR_1 . $modFinal . Home::APROBAR_2 . $modInicial . Home::APROBAR_3;
        }

        ///*
        // redirección a la página de pensum
        return redirect()->to(base_url('/pensum'))->with("mensaje", $mensaje);
        //*/

        /*
        var_dump($materias);
        echo "<br>";
        var_dump($resultado1);
        echo "<br>";
        var_dump($resultado2_1);
        echo "<br>";
        var_dump($resultado2_2);
        echo "<br>";
        var_dump($resultado3);
        echo "<br>";
        */
    }

    //-----------------------------------------------------------------
    //
    //      Actualización de Datos del Estudiante
    //
    //------------------------------------------------------------------
    

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
        $modulo = new GestorEstudiante;
        // Obtención de los Datos del Usuario con el id Dado
        $registro = $modulo->obtenerEstudiante($id);

        // Título de la Pestaña
        $titulo = [ 'titulo' => 'Pensum In - Estudiante' ];      

        if (session('datosEstudiante')['codigo_carrera'] == GestorCarreraMencion::CARRERA_INGINF) {
            // Vista con el Inicio de Informática
            $vistaActualizarINF = $this->obtenerVistaActualizarINF($titulo, $mess, $registro);
            
            return $vistaActualizarINF;
        } else if(session('datosEstudiante')['codigo_carrera'] == GestorCarreraMencion::CARRERA_INGAMB) {
            // Vista con el Inicio de Ambiental
            $vistaActualizarAMB = $this->obtenerVistaActualizarAMB($titulo, $mess, $registro);
            
            return $vistaActualizarAMB;
        }
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
        $modulo = new GestorEstudiante;
        // Actualización del Usuario con los Datos del Formulario (Parámetros)
        $respuesta = $modulo->actualizarEstudiante($id, $usuario, $cedula, $correo, $nombre, $apellido, $genero, $fechaNacimiento);

        // CONDICIONAL: Redirreción según el Éxito de la Actualización
        if ($respuesta == GestorUsuario::SUCCESS) {
            $idSession = session()->get('datosUsuario')['id'];

            $datosActualizados = $modulo->obtenerEstudiante($id);

            // Arreglo con los Datos actualizados
            $data = [
                "id" => $datosActualizados['id'],
                "datosUsuario" => $datosActualizados,
                'rol' => $datosActualizados['rol']
            ];

            // Asignación de los Datos Actualizados a la Sesión
            session()->set($data);
            
            return redirect()->to(base_url().'/pensum')->with('mensaje', $respuesta);
        } else {
            return redirect()->to(base_url().'/pensum/editar/'.$id)->with('mensaje', $respuesta);
        }
    }
    
    //-----------------------------------------------------------------
    //
    //      Construcción de Vistas
    //
    //------------------------------------------------------------------
    
    private function obtenerVistaINF(array $titulo, array $mensaje, array $pensum) {
        $vista = view('secciones/headerInf', $titulo).
                view('secciones/navE').
                view('secciones/mensaje', $mensaje).
                view('barraProgreso').
                view('pensumInf', $pensum).
                view('secciones/footer');

        return $vista;
    }

    private function obtenerVistaAMB(array $titulo, array $mensaje, array $pensum) {
        $vista = view('secciones/headerAmb', $titulo).
                view('secciones/navE').
                view('secciones/mensaje', $mensaje).
                view('barraProgreso').
                view('pensumAmb', $pensum).
                view('secciones/footer');

        return $vista;
    }

    private function obtenerVistaActualizarINF(array $titulo, array $mensaje, array $data) {
        $vista = view('secciones/headerInf', $titulo).
                view('secciones/navE').
                view('secciones/mensaje', $mensaje).
                view('actualizarE', $data).
                view('secciones/footer');

        return $vista;    
    }

    private function obtenerVistaActualizarAMB(array $titulo, array $mensaje, array $data) {
        $vista = view('secciones/headerAmb', $titulo).
                view('secciones/navE').
                view('secciones/mensaje', $mensaje).
                view('actualizarE', $data).
                view('secciones/footer');

        return $vista;    
    }
}
