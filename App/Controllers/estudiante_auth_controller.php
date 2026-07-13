<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\EstudianteAuthService;

class EstudianteAuthController extends Controller
{
    public function index(): void
    {
        $this->view('Portal/Auth/login');
    }

    public function autenticar(): void
    {
        $auth = new EstudianteAuthService();
        $auth->login($_POST['cip'] ?? '', $_POST['pin'] ?? '');
    }

    public function logout(): void
    {
        $auth = new EstudianteAuthService();
        $auth->logout();
    }
}