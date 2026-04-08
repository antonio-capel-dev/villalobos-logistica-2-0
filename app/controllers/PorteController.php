<?php
// app/controllers/PorteController.php

namespace Controllers;

use Core\Controller;
use Models\PorteModel;

class PorteController extends Controller {

    public function index(): void {
        // Esta ruta requiere sesión activa
        $this->requiereAuth();

        $modelo = new PorteModel();
        $rol    = $_SESSION['rol'] ?? 'cliente';

        if ($rol === 'admin' || $rol === 'editor') {
            $portes = $modelo->getTodos();
        } else {
            $portes = $modelo->getPorCliente($_SESSION['user_id']);
        }

        $this->render('panel/portes_mvc', [
            'pageTitle' => 'Portes',
            'portes'    => $portes,
        ]);
    }
}
