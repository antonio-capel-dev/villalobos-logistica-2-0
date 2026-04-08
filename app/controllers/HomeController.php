<?php
// app/controllers/HomeController.php

namespace Controllers;

use Core\Controller;

class HomeController extends Controller {

    public function index(): void {
        $this->render('public/home', [
            'pageTitle' => 'Inicio',
        ]);
    }
}
