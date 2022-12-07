<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.

// Inicio
$routes->get('/', 'Home::index');

// INGRESO
$routes->get('/ingreso', 'Login::preLogin');       // Vista del Ingreso
$routes->post('/login', 'Login::login');           // Envío de los Datos para el Ingreso

// REGISTRO DE UN ESTUDIANTE
$routes->get('/registro', 'Register::preRegisterE');       // Vista del Registro
$routes->post('/register', 'Register::register');          // Envío de los Datos para el Registro

// ADMINISTRADOR
$routes->get('/admin', 'Administrador::inicio');                                // Vista de Inicio de Sesión
$routes->post('/admin/register', 'Register::register');                         // Envío de Datos para el Registro de un Operador
$routes->get('/admin/editar/(:any)', 'Administrador::obtenerUsuario/$1');       // Vista de Actualización de un Usuario (Cualquier Rol)
$routes->post('/admin/editar', 'Administrador::actualizarUsuario');             // Envío de Datos para la Actualización del Usuario
$routes->get('/admin/eliminar/(:any)', 'Administrador::eliminarUsuario/$1');    // Eliminación de un Usuario (Cualquier Rol)

// OPERADOR
$routes->get('/operador', 'Operador::inicio');                                  // Vista de Inicio de Sesión
$routes->get('/operador/editar/(:any)', 'Operador::obtenerUsuario/$1');         // Vista de Actualización de un Usuario (Operador / Estudiante)
$routes->post('/operador/editar', 'Operador::actualizarUsuario');               // Envío de Datos para la Actualización del Usuario
$routes->get('/operador/eliminar/(:any)', 'Operador::eliminarUsuario/$1');      // Eliminación de un Usuario (Estudiante)

// ESTUDIANTE
$routes->get('/pensum', 'Estudiante::inicio');                                  // Vista de Inicio de Sesión
$routes->get('/pensum/editar/(:any)', 'Estudiante::obtenerUsuario/$1');         // Vista de Actualización de un Usuario (Estudiante)
$routes->post('/pensum/editar', 'Estudiante::actualizarUsuario');               // Envío de Datos para la Actualización del Usuario
$routes->post('/pensum/actualizar', 'Estudiante::actualizar');                  // Envío de Datos para la Actualización del Pensum

// Salida de la Sesión
$routes->get('/salir', 'Home::salir');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
