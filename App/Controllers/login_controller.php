<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;

class LoginController extends Controller
{
    public function index(): void
    {
        $this->view('login/login');
    }

    public function autenticar(): void
    {
        $auth = new AuthService();
        $auth->login();
    }

    public function logout(): void
    {
        $auth = new AuthService();
        $auth->logout();
    }
}
