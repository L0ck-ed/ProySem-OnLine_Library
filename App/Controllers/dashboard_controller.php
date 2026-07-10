<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\Auth;

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::check();
        $this->view('Admin/Dashboards/panel');
    }
}
