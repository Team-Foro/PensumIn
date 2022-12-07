<?php

namespace App\Filters;

use App\Controllers\Home;
use App\Models\GestorUsuario;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class EsUsuario implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Do something here
        if(session('rol') == GestorUsuario::ROL_ADMINISTRADOR){

        } elseif(session('rol') == GestorUsuario::ROL_OPERADOR){

        } elseif(session('rol') == GestorUsuario::ROL_ESTUDIANTE){

        } else {
            //usuario no regisrado
            return redirect()->to(base_url('/'))->with("mensaje", "(!) ".Home::NO_LOGEADO);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}