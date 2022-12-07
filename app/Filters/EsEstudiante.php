<?php

namespace App\Filters;

use App\Controllers\Home;
use App\Models\GestorUsuario;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class EsEstudiante implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Do something here
        if(session('rol') != GestorUsuario::ROL_ESTUDIANTE){
            // no es un estudiante
            return redirect()->to(base_url('/'))->with("mensaje", "(!) ".Home::INVALID_AUTORIDAD);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}