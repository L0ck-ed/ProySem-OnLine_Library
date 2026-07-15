<?php

namespace App\Controllers;

use App\Core\Controller;

class AccesoController extends Controller
{
    public function index(): void
    {
        $this->view('Acceso/index');
    }
}
